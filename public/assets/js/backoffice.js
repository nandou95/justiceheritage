(() => {
  const sidebar = document.querySelector("[data-bo-nav]");
  const toggle = document.querySelector("[data-bo-toggle]");
  const closeBtn = document.querySelector("[data-bo-close]");
  const backdrop = document.querySelector("[data-bo-backdrop]");
  const mq = window.matchMedia("(max-width: 980px)");

  const setDrawer = (open) => {
    if (!sidebar) {
      return;
    }
    sidebar.classList.toggle("is-open", open);
    document.body.classList.toggle("bo-nav-locked", open && mq.matches);
    if (toggle) {
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    }
    if (backdrop) {
      backdrop.classList.toggle("is-visible", open);
      backdrop.hidden = !open;
    }
  };

  const closeDrawer = () => setDrawer(false);

  if (toggle && sidebar) {
    toggle.addEventListener("click", () => {
      setDrawer(!sidebar.classList.contains("is-open"));
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", closeDrawer);
  }

  if (backdrop) {
    backdrop.addEventListener("click", closeDrawer);
  }

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeDrawer();
    }
  });

  if (sidebar) {
    sidebar.querySelectorAll("a[href]").forEach((link) => {
      link.addEventListener("click", () => {
        if (mq.matches) {
          closeDrawer();
        }
      });
    });
  }

  const onBreakpointChange = () => {
    if (!mq.matches) {
      closeDrawer();
    }
  };
  if (typeof mq.addEventListener === "function") {
    mq.addEventListener("change", onBreakpointChange);
  } else if (typeof mq.addListener === "function") {
    mq.addListener(onBreakpointChange);
  }

  document.querySelectorAll("[data-bo-nav-item]").forEach((item) => {
    const button = item.querySelector("[data-bo-nav-toggle]");
    const panel = item.querySelector(".bo-nav-sub");
    if (!button || !panel) {
      return;
    }

    button.addEventListener("click", () => {
      const open = !item.classList.contains("is-open");
      item.classList.toggle("is-open", open);
      button.setAttribute("aria-expanded", open ? "true" : "false");
      panel.hidden = !open;
    });
  });

  const dtLang = window.JH_DT?.language || {};
  const dataTables = new Map();

  const destroyDataTable = (table) => {
    if (!table || typeof DataTable === "undefined") {
      return;
    }
    if (typeof DataTable.isDataTable === "function" && DataTable.isDataTable(table)) {
      try {
        new DataTable.Api(table).destroy();
      } catch (_err) {
        const existing = dataTables.get(table.id) || dataTables.get(table);
        existing?.destroy?.();
      }
    }
    if (table.id) {
      dataTables.delete(table.id);
    }
    dataTables.delete(table);
  };

  const initDataTable = (table) => {
    if (!table || typeof DataTable === "undefined") {
      return null;
    }
    if (typeof DataTable.isDataTable === "function" && DataTable.isDataTable(table)) {
      return dataTables.get(table.id) || dataTables.get(table) || null;
    }

    const pageLength = Number(table.dataset.pageLength || 10);
    const orderCol = Number(table.dataset.orderCol ?? -1);
    const orderDir = table.dataset.orderDir || "desc";

    const orderableTargets = [];
    const searchableTargets = [];
    table.querySelectorAll("thead th").forEach((th, i) => {
      if (th.dataset.orderable === "false") {
        orderableTargets.push(i);
      }
      if (th.dataset.searchable === "false") {
        searchableTargets.push(i);
      }
    });

    const columnDefs = [];
    if (orderableTargets.length) {
      columnDefs.push({ orderable: false, targets: orderableTargets });
    }
    if (searchableTargets.length) {
      columnDefs.push({ searchable: false, targets: searchableTargets });
    }

    const dt = new DataTable(table, {
      language: dtLang,
      pageLength,
      autoWidth: false,
      order: orderCol >= 0 ? [[orderCol, orderDir]] : [],
      columnDefs,
      dom: table.dataset.dom || "lfrtip",
    });

    dataTables.set(table.id || table, dt);
    if (table.id) {
      dataTables.set(table, dt);
    }
    return dt;
  };

  document.querySelectorAll("table.jh-datatable").forEach((table) => {
    initDataTable(table);
  });

  const bindTableSearch = (inputId, tableId) => {
    const input = document.getElementById(inputId);
    if (!input) {
      return;
    }
    input.addEventListener("input", () => {
      const table = document.getElementById(tableId);
      const dt = dataTables.get(tableId) || (table ? dataTables.get(table) : null);
      if (!dt) {
        return;
      }
      dt.search(input.value).draw();
    });
  };

  const refreshTooltips = (scope) => {
    if (!window.bootstrap?.Tooltip) {
      return;
    }
    (scope || document).querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      bootstrap.Tooltip.getOrCreateInstance(el);
    });
  };

  /**
   * Live AJAX list filters for every GET .bo-filters form.
   * Removes Filter buttons, reloads the table wrap via Fetch, keeps cascades intact.
   */
  const initLiveListFilters = () => {
    document.querySelectorAll("form.bo-filters").forEach((form) => {
      if (form.dataset.boLiveBound === "1") {
        return;
      }
      if ((form.getAttribute("method") || "get").toLowerCase() !== "get") {
        return;
      }
      if (form.closest(".modal")) {
        return;
      }

      const panel = form.closest(".bo-panel, .bo-crud-panel") || form.parentElement;
      if (!panel || panel.hasAttribute("data-bo-perm-live")) {
        return;
      }

      form.dataset.boLiveBound = "1";
      panel.setAttribute("data-bo-live-list", "");

      form.querySelectorAll('button[type="submit"]').forEach((btn) => {
        const col = btn.closest('[class*="col-"]');
        (col || btn).remove();
      });

      let timer = null;
      let requestSeq = 0;

      const buildUrl = () => {
        const action = form.getAttribute("action") || window.location.pathname;
        const params = new URLSearchParams();
        const data = new FormData(form);
        data.forEach((value, key) => {
          const trimmed = String(value ?? "").trim();
          if (trimmed !== "") {
            params.append(key, trimmed);
          }
        });
        const qs = params.toString();
        return qs ? `${action}?${qs}` : action;
      };

      const reloadList = async () => {
        const wrap = panel.querySelector(".bo-table-wrap");
        if (!wrap) {
          return;
        }
        const currentTable = wrap.querySelector("table");
        const tableId = currentTable?.id || "";
        const seq = ++requestSeq;
        const url = buildUrl();

        panel.classList.add("is-filtering");
        try {
          const response = await fetch(url, {
            headers: {
              Accept: "text/html",
              "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
            cache: "no-store",
          });
          if (!response.ok) {
            return;
          }
          const html = await response.text();
          if (seq !== requestSeq) {
            return;
          }

          const doc = new DOMParser().parseFromString(html, "text/html");
          const nextTable = tableId ? doc.getElementById(tableId) : doc.querySelector(".bo-table-wrap table");
          const nextWrap = nextTable?.closest(".bo-table-wrap") || doc.querySelector(".bo-table-wrap");
          if (!nextWrap) {
            return;
          }

          if (currentTable) {
            destroyDataTable(currentTable);
          }
          wrap.replaceWith(nextWrap);
          nextWrap.querySelectorAll("table.jh-datatable").forEach((table) => {
            const dt = initDataTable(table);
            const searchInput = panel.querySelector(".bo-table-search input[type='search'], .bo-table-search input");
            if (dt && searchInput && searchInput.value) {
              dt.search(searchInput.value).draw();
            }
          });
          refreshTooltips(nextWrap);
          history.replaceState(history.state, "", url);
          document.dispatchEvent(new CustomEvent("bo:list-reloaded", { detail: { panel, form, url } }));
        } catch (_err) {
          // Keep current rows on network errors.
        } finally {
          if (seq === requestSeq) {
            panel.classList.remove("is-filtering");
          }
        }
      };

      const scheduleReload = (delay = 280) => {
        clearTimeout(timer);
        timer = setTimeout(reloadList, delay);
      };

      form.addEventListener("submit", (event) => {
        event.preventDefault();
        scheduleReload(0);
      });

      form.querySelectorAll("select, input").forEach((field) => {
        const eventName = field.tagName === "SELECT" || field.type === "date" || field.type === "datetime-local"
          ? "change"
          : "input";
        field.addEventListener(eventName, () => {
          // Wait for cascade option refreshes before reading FormData.
          scheduleReload(field.tagName === "SELECT" ? 550 : 280);
        });
      });

      form._boLiveReload = reloadList;
    });
  };

  initLiveListFilters();

  bindTableSearch("users-table-search", "users-table");
  // Permissions search is handled by live AJAX filters (data-bo-perm-live).
  bindTableSearch("profiles-table-search", "profiles-table");
  bindTableSearch("profile-permissions-search", "profile-permissions-table");
  bindTableSearch("people-table-search", "people-table");
  bindTableSearch("people-complaints-search", "people-complaints-table");
  bindTableSearch("cs-table-search", "cs-table");
  bindTableSearch("cs-actions-table-search", "cs-actions-table");
  bindTableSearch("csc-table-search", "csc-table");
  bindTableSearch("cst-table-search", "cst-table");
  bindTableSearch("dt-table-search", "dt-table");
  bindTableSearch("cmp-table-search", "cmp-table");
  bindTableSearch("apl-table-search", "apl-table");
  bindTableSearch("sum-table-search", "sum-table");
  bindTableSearch("sum-pending-table-search", "sum-pending-table");
  bindTableSearch("sum-st-table-search", "sum-st-table");
  bindTableSearch("hrg-table-search", "hrg-table");
  bindTableSearch("hrg-st-table-search", "hrg-st-table");
  bindTableSearch("hrg-assign-table-search", "hrg-assign-table");
  bindTableSearch("vrd-table-search", "vrd-table");
  bindTableSearch("vrd-type-table-search", "vrd-type-table");
  bindTableSearch("trf-st-table-search", "trf-st-table");
  bindTableSearch("trf-table-search", "trf-table");
  bindTableSearch("cj-table-search", "cj-table");
  bindTableSearch("cjc-table-search", "cjc-table");
  bindTableSearch("jl-table-search", "jl-table");
  bindTableSearch("jlc-table-search", "jlc-table");
  bindTableSearch("logs-c-table-search", "logs-c-table");
  bindTableSearch("logs-u-table-search", "logs-u-table");
  bindTableSearch("ntf-c-table-search", "ntf-c-table");
  bindTableSearch("ntf-u-table-search", "ntf-u-table");

  const exportTable = (tableId, mode, filename) => {
    const table = document.getElementById(tableId);
    if (!table) {
      return;
    }
    const dt = dataTables.get(tableId) || dataTables.get(table);
    const headers = Array.from(table.querySelectorAll("thead th"))
      .map((th) => th.textContent.trim())
      .filter((text, index, arr) => index < arr.length - 1 || !/actions?/i.test(text));
    const actionCol = Array.from(table.querySelectorAll("thead th")).findIndex((th) =>
      /actions?/i.test(th.textContent.trim())
    );

    let rows = [];
    if (dt) {
      const data = dt.rows({ search: "applied" }).data().toArray();
      rows = data.map((row) => {
        const cells = Array.isArray(row) ? row : Object.values(row);
        return cells
          .map((cell, idx) => {
            if (actionCol >= 0 && idx === actionCol) {
              return null;
            }
            const tmp = document.createElement("div");
            tmp.innerHTML = typeof cell === "string" ? cell : String(cell ?? "");
            return (tmp.textContent || "").trim();
          })
          .filter((v) => v !== null);
      });
    } else {
      rows = Array.from(table.querySelectorAll("tbody tr")).map((tr) =>
        Array.from(tr.children)
          .map((td, idx) => {
            if (actionCol >= 0 && idx === actionCol) {
              return null;
            }
            return td.textContent.trim();
          })
          .filter((v) => v !== null)
      );
    }

    const title = filename || tableId || "export";

    if (mode === "excel") {
      const escapeCsv = (value) => `"${String(value).replace(/"/g, '""')}"`;
      const lines = [headers.map(escapeCsv).join(",")].concat(
        rows.map((row) => row.map(escapeCsv).join(","))
      );
      const blob = new Blob(["\ufeff" + lines.join("\r\n")], {
        type: "application/vnd.ms-excel;charset=utf-8;",
      });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `${title}.xls`;
      link.click();
      URL.revokeObjectURL(link.href);
      return;
    }

    const htmlRows = rows
      .map(
        (row) =>
          `<tr>${row.map((cell) => `<td>${String(cell).replace(/</g, "&lt;")}</td>`).join("")}</tr>`
      )
      .join("");
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${title}</title>
      <style>
        body{font-family:Arial,sans-serif;padding:16px;color:#122018}
        h1{font-size:18px;margin:0 0 12px}
        table{border-collapse:collapse;width:100%;font-size:12px}
        th,td{border:1px solid #cbd5e1;padding:6px 8px;text-align:left;vertical-align:top}
        th{background:#eef5f1}
      </style></head><body>
      <h1>${title}</h1>
      <table><thead><tr>${headers.map((h) => `<th>${h}</th>`).join("")}</tr></thead>
      <tbody>${htmlRows}</tbody></table>
      </body></html>`;
    const win = window.open("", "_blank");
    if (!win) {
      return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    win.print();
  };

  document.querySelectorAll("[data-bo-export]").forEach((toolbar) => {
    toolbar.querySelectorAll("[data-export]").forEach((btn) => {
      btn.addEventListener("click", () => {
        exportTable(toolbar.dataset.table || "", btn.dataset.export || "print", toolbar.dataset.filename || "export");
      });
    });
  });

  document.querySelectorAll("[data-bo-log-filters]").forEach((form) => {
    const province = form.querySelector('[data-filter="province"]');
    const commune = form.querySelector('[data-filter="commune"]');
    const apiCommunes = form.dataset.apiCommunes || "";
    province?.addEventListener("change", async () => {
      if (!commune || !apiCommunes) {
        return;
      }
      fillSelect(commune, [], commune.options[0]?.textContent || "");
      const options = province.value
        ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`)
        : [];
      fillSelect(commune, options, commune.options[0]?.textContent || "");
    });
  });

  if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      // eslint-disable-next-line no-new
      new bootstrap.Tooltip(el);
    });
  }

  const fillSelect = (select, options, placeholder) => {
    if (!select) {
      return;
    }
    const current = select.value;
    select.innerHTML = "";
    const empty = document.createElement("option");
    empty.value = "";
    empty.textContent = placeholder || "";
    select.appendChild(empty);
    options.forEach((opt) => {
      const option = document.createElement("option");
      option.value = String(opt.id);
      option.textContent = opt.label;
      select.appendChild(option);
    });
    if (current && Array.from(select.options).some((o) => o.value === current)) {
      select.value = current;
    }
  };

  const fetchOptions = async (url) => {
    const response = await fetch(url, { headers: { Accept: "application/json" } });
    if (!response.ok) {
      throw new Error("Request failed");
    }
    const data = await response.json();
    return data.options || [];
  };

  const userFilters = document.querySelector("[data-bo-user-filters]");
  if (userFilters) {
    const province = userFilters.querySelector('[data-filter="province"]');
    const commune = userFilters.querySelector('[data-filter="commune"]');
    const apiCommunes = userFilters.dataset.apiCommunes || "";

    province?.addEventListener("change", async () => {
      if (!commune) {
        return;
      }
      const allLabel = commune.options[0]?.textContent || "";
      fillSelect(commune, [], allLabel);
      if (province.value && apiCommunes) {
        const options = await fetchOptions(
          `${apiCommunes}?province_id=${encodeURIComponent(province.value)}`
        );
        fillSelect(commune, options, allLabel);
      }
    });
  }

  const userForm = document.querySelector("[data-bo-user-form]");
  if (userForm) {
    const province = userForm.querySelector('[data-loc="province"]');
    const commune = userForm.querySelector('[data-loc="commune"]');
    const zone = userForm.querySelector('[data-loc="zone"]');
    const colline = userForm.querySelector('[data-loc="colline"]');
    const apiCommunes = userForm.dataset.apiCommunes || "";
    const apiZones = userForm.dataset.apiZones || "";
    const apiCollines = userForm.dataset.apiCollines || "";

    province?.addEventListener("change", async () => {
      const options = province.value
        ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`)
        : [];
      fillSelect(commune, options, commune?.options[0]?.textContent || "");
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });

    commune?.addEventListener("change", async () => {
      const options = commune.value
        ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`)
        : [];
      fillSelect(zone, options, zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });

    zone?.addEventListener("change", async () => {
      const options = zone.value
        ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`)
        : [];
      fillSelect(colline, options, colline?.options[0]?.textContent || "");
    });

    const wizardRoot = userForm.querySelector("[data-wizard]");
    if (userForm.hasAttribute("data-bo-user-wizard") && wizardRoot) {
      const panes = Array.from(userForm.querySelectorAll("[data-wizard-step]"));
      const indicators = Array.from(userForm.querySelectorAll("[data-wizard-indicator]"));
      const statusEl = userForm.querySelector("[data-wizard-status]");
      const prevBtn = userForm.querySelector("[data-wizard-prev]");
      const nextBtn = userForm.querySelector("[data-wizard-next]");
      const submitBtn = userForm.querySelector("[data-wizard-submit]");
      const totalSteps = panes.length || 2;
      let currentStep = 1;
      const progressTpl = window.JH_USER_WIZARD_I18N?.progress || "Step {0} of {1}";

      const formatProgress = (step) =>
        String(progressTpl)
          .replaceAll("{0}", String(step))
          .replaceAll("{1}", String(totalSteps));

      const paneFields = (pane) =>
        Array.from(pane.querySelectorAll("input, select, textarea")).filter(
          (el) => !el.disabled && el.type !== "hidden" && el.type !== "submit" && el.type !== "button"
        );

      const msgRequired = userForm.dataset.msgRequired || "";
      const msgEmail = userForm.dataset.msgEmail || "";
      const msgMinAge = userForm.dataset.msgMinAge || "";

      const syncFeedback = (field) => {
        const feedback = field.parentElement?.querySelector(".invalid-feedback");
        if (!feedback) {
          return;
        }
        if (field.validity.valid) {
          feedback.textContent = msgRequired;
          return;
        }
        if (field.validationMessage) {
          feedback.textContent = field.validationMessage;
        } else if (field.validity.valueMissing) {
          feedback.textContent = msgRequired;
        }
      };

      const applyFieldRules = (field) => {
        if (typeof field.value === "string" && field.type !== "date" && field.tagName !== "SELECT") {
          field.value = field.value.trim();
        }

        field.setCustomValidity("");

        const maxLen = Number(field.dataset.maxLength || 0);
        if (maxLen > 0 && typeof field.value === "string" && field.value.length > maxLen) {
          field.setCustomValidity(field.dataset.maxMsg || msgRequired);
        }

        if (field.type === "email" && field.value) {
          const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
          if (!emailOk) {
            field.setCustomValidity(msgEmail || field.validationMessage || msgRequired);
          }
        }

        if (field.id === "date_naissance" && field.value) {
          const max = field.getAttribute("max");
          if (max && field.value > max) {
            field.setCustomValidity(msgMinAge || msgRequired);
          }
        }

        syncFeedback(field);
      };

      const validateStep = (step) => {
        const pane = panes.find((p) => Number(p.dataset.wizardStep) === step);
        if (!pane) {
          return true;
        }
        let valid = true;
        paneFields(pane).forEach((field) => {
          applyFieldRules(field);
          if (!field.checkValidity()) {
            valid = false;
            field.classList.add("is-invalid");
          } else {
            field.classList.remove("is-invalid");
          }
          syncFeedback(field);
        });
        userForm.classList.add("was-validated");
        if (!valid) {
          const firstInvalid = pane.querySelector(":invalid, .is-invalid");
          firstInvalid?.focus?.();
        }
        return valid;
      };

      userForm.querySelectorAll("input, select, textarea").forEach((field) => {
        field.addEventListener("input", () => {
          applyFieldRules(field);
          if (field.checkValidity()) {
            field.classList.remove("is-invalid");
          }
          syncFeedback(field);
        });
        field.addEventListener("blur", () => {
          applyFieldRules(field);
          if (!field.checkValidity()) {
            field.classList.add("is-invalid");
          } else {
            field.classList.remove("is-invalid");
          }
          syncFeedback(field);
        });
      });

      const showStep = (step) => {
        currentStep = Math.min(Math.max(step, 1), totalSteps);
        panes.forEach((pane) => {
          const isActive = Number(pane.dataset.wizardStep) === currentStep;
          pane.classList.toggle("is-active", isActive);
          pane.hidden = !isActive;
        });
        indicators.forEach((indicator) => {
          const idx = Number(indicator.dataset.wizardIndicator);
          indicator.classList.toggle("is-active", idx === currentStep);
          indicator.classList.toggle("is-complete", idx < currentStep);
        });
        if (statusEl) {
          statusEl.textContent = formatProgress(currentStep);
        }
        if (prevBtn) {
          prevBtn.hidden = currentStep <= 1;
        }
        if (nextBtn) {
          nextBtn.hidden = currentStep >= totalSteps;
        }
        if (submitBtn) {
          submitBtn.hidden = currentStep < totalSteps;
        }
      };

      prevBtn?.addEventListener("click", () => {
        showStep(currentStep - 1);
      });

      nextBtn?.addEventListener("click", () => {
        if (!validateStep(currentStep)) {
          return;
        }
        showStep(currentStep + 1);
      });

      userForm.addEventListener("submit", (event) => {
        if (currentStep < totalSteps) {
          event.preventDefault();
          event.stopPropagation();
          if (validateStep(currentStep)) {
            showStep(currentStep + 1);
          }
          return;
        }
        for (let step = 1; step <= totalSteps; step += 1) {
          if (!validateStep(step)) {
            event.preventDefault();
            event.stopPropagation();
            showStep(step);
            return;
          }
        }
      });

      showStep(1);
    } else {
      userForm.addEventListener("submit", (event) => {
        if (!userForm.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        userForm.classList.add("was-validated");
      });
    }
  }

  const userStatusModalEl = document.getElementById("userStatusModal");
  if (userStatusModalEl && window.JH_USERS_I18N) {
    const form = document.getElementById("userStatusForm");
    const title = document.getElementById("userStatusModalTitle");
    const message = document.getElementById("userStatusModalMessage");
    const confirmBtn = document.getElementById("userStatusModalConfirm");
    const modal = bootstrap.Modal.getOrCreateInstance(userStatusModalEl);
    const i18n = window.JH_USERS_I18N;

    document.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-bo-toggle-user]");
      if (!btn) {
        return;
      }
      const id = btn.getAttribute("data-user-id");
      const name = btn.getAttribute("data-user-name") || "";
      const activate = btn.getAttribute("data-activate") === "1";
      form.action = i18n.toggleUrl.replace("__ID__", id);
      title.textContent = activate ? i18n.activateTitle : i18n.deactivateTitle;
      message.textContent = `${activate ? i18n.activateMessage : i18n.deactivateMessage}${name ? ` (${name})` : ""}`;
      confirmBtn.textContent = activate ? i18n.activateBtn : i18n.deactivateBtn;
      confirmBtn.className = `btn ${activate ? "btn-success" : "btn-danger"}`;
      modal.show();
    });
  }

  const permFormModalEl = document.getElementById("permissionFormModal");
  const permStatusModalEl = document.getElementById("permissionStatusModal");
  const permLiveRoot = document.querySelector("[data-bo-perm-live]");

  const escapeHtml = (value) => String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");

  const syncCsrfTokens = (name, hash) => {
    if (!name || !hash) {
      return;
    }
    document.querySelectorAll(`input[name="${CSS.escape(name)}"]`).forEach((input) => {
      input.value = hash;
    });
  };

  const refreshCsrfTokens = async () => {
    const url = window.JH_PERM_I18N?.csrfUrl;
    if (!url) {
      return;
    }
    try {
      const response = await fetch(url, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
        cache: "no-store",
      });
      if (!response.ok) {
        return;
      }
      const payload = await response.json();
      if (payload.csrfName && payload.csrfHash) {
        syncCsrfTokens(payload.csrfName, payload.csrfHash);
      }
    } catch (_err) {
      // Keep existing tokens if refresh fails.
    }
  };

  const getCsrfToken = () => {
    const input = document.querySelector(
      "#permissionStatusForm input[type='hidden'][name], #permissionForm input[type='hidden'][name]"
    );
    if (input && input.name && input.value) {
      return { name: input.name, value: input.value };
    }
    return null;
  };

  const refreshPermissionTooltips = (scope) => {
    if (typeof bootstrap === "undefined" || !bootstrap.Tooltip) {
      return;
    }
    (scope || document).querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      bootstrap.Tooltip.getOrCreateInstance(el);
    });
  };

  const rebuildPermissionsTable = (items) => {
    const table = document.getElementById("permissions-table");
    if (!table || !window.JH_PERM_I18N) {
      return;
    }
    const i18n = window.JH_PERM_I18N;
    const tbody = table.querySelector("tbody");
    if (!tbody) {
      return;
    }

    if (typeof DataTable !== "undefined" && typeof DataTable.isDataTable === "function" && DataTable.isDataTable(table)) {
      try {
        new DataTable.Api(table).destroy();
      } catch (_err) {
        const existing = dataTables.get("permissions-table") || dataTables.get(table);
        existing?.destroy?.();
      }
      dataTables.delete("permissions-table");
      dataTables.delete(table);
    }

    if (!items.length) {
      tbody.innerHTML = `<tr><td colspan="4">${escapeHtml(i18n.empty || "")}</td></tr>`;
    } else {
      tbody.innerHTML = items.map((row) => {
        const active = !!row.is_active;
        const activate = active ? "0" : "1";
        const toggleTitle = active ? i18n.deactivateAction : i18n.activateAction;
        const toggleClass = active ? "is-danger" : "is-success";
        const toggleIcon = active ? "bi-toggle-off" : "bi-toggle-on";
        const statusClass = active ? "is-active" : "is-inactive";
        return `<tr>
          <td>${escapeHtml(row.description)}</td>
          <td><code class="bo-route-code">${escapeHtml(row.url_route)}</code></td>
          <td><span class="bo-status-pill ${statusClass}">${escapeHtml(row.status)}</span></td>
          <td>
            <div class="bo-action-group">
              <button class="btn btn-bo-icon" type="button" data-bo-perm-edit data-id="${escapeHtml(row.id)}" data-description="${escapeHtml(row.description)}" data-route="${escapeHtml(row.url_route)}" data-bs-toggle="tooltip" title="${escapeHtml(i18n.editAction)}">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <span class="visually-hidden">${escapeHtml(i18n.editAction)}</span>
              </button>
              <button class="btn btn-bo-icon ${toggleClass}" type="button" data-bo-toggle-perm data-id="${escapeHtml(row.id)}" data-description="${escapeHtml(row.description)}" data-activate="${activate}" data-bs-toggle="tooltip" title="${escapeHtml(toggleTitle)}">
                <i class="bi ${toggleIcon}" aria-hidden="true"></i>
                <span class="visually-hidden">${escapeHtml(toggleTitle)}</span>
              </button>
            </div>
          </td>
        </tr>`;
      }).join("");
    }

    if (typeof DataTable !== "undefined") {
      const pageLength = Number(table.dataset.pageLength || 10);
      const orderCol = Number(table.dataset.orderCol ?? 0);
      const orderDir = table.dataset.orderDir || "asc";
      const dt = new DataTable(table, {
        language: dtLang,
        pageLength,
        autoWidth: false,
        order: orderCol >= 0 ? [[orderCol, orderDir]] : [],
        columnDefs: [
          { orderable: false, targets: [3] },
          { searchable: false, targets: [3] },
        ],
        dom: table.dataset.dom || "lrtip",
      });
      dataTables.set("permissions-table", dt);
      dataTables.set(table, dt);
    }

    refreshPermissionTooltips(tbody);
  };

  if (permLiveRoot && window.JH_PERM_I18N?.listUrl) {
    const statusSelect = permLiveRoot.querySelector('[data-perm-filter="status"]');
    const searchInput = permLiveRoot.querySelector('[data-perm-filter="q"]');
    let searchTimer = null;
    let requestSeq = 0;

    const loadPermissions = async () => {
      const seq = ++requestSeq;
      const params = new URLSearchParams();
      const status = statusSelect?.value || "";
      const q = (searchInput?.value || "").trim();
      if (status) {
        params.set("status", status);
      }
      if (q) {
        params.set("q", q);
      }

      try {
        const response = await fetch(`${window.JH_PERM_I18N.listUrl}?${params.toString()}`, {
          headers: { Accept: "application/json" },
          credentials: "same-origin",
        });
        if (!response.ok) {
          return;
        }
        const payload = await response.json();
        if (seq !== requestSeq) {
          return;
        }
        rebuildPermissionsTable(Array.isArray(payload.items) ? payload.items : []);
      } catch (_err) {
        // Keep current rows on network errors.
      }
    };

    statusSelect?.addEventListener("change", () => {
      loadPermissions();
    });

    searchInput?.addEventListener("input", () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(loadPermissions, 250);
    });

    window.JH_PERM_reload = loadPermissions;
  }

  if (permFormModalEl && window.JH_PERM_I18N) {
    const i18n = window.JH_PERM_I18N;
    const form = document.getElementById("permissionForm");
    const title = document.getElementById("permissionFormModalTitle");
    const submitBtn = document.getElementById("permissionFormSubmit");
    const description = document.getElementById("description_permission");
    const route = document.getElementById("url_route");
    const formModal = bootstrap.Modal.getOrCreateInstance(permFormModalEl);

    const openCreate = () => {
      form.action = i18n.storeUrl;
      form.classList.remove("was-validated");
      title.textContent = i18n.createTitle;
      submitBtn.textContent = i18n.saveCreate;
      description.value = "";
      route.value = "";
      refreshCsrfTokens();
    };

    document.querySelectorAll("[data-bo-perm-create]").forEach((btn) => {
      btn.addEventListener("click", openCreate);
    });

    document.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-bo-perm-edit]");
      if (!btn || !document.getElementById("permissions-table")?.contains(btn)) {
        return;
      }
      form.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
      form.classList.remove("was-validated");
      title.textContent = i18n.editTitle;
      submitBtn.textContent = i18n.saveEdit;
      description.value = btn.getAttribute("data-description") || "";
      route.value = btn.getAttribute("data-route") || "";
      refreshCsrfTokens();
      formModal.show();
    });

    form?.addEventListener("submit", async (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add("was-validated");
        return;
      }
      form.classList.add("was-validated");
      event.preventDefault();
      await refreshCsrfTokens();
      form.submit();
    });
  }

  if (permStatusModalEl && window.JH_PERM_I18N) {
    const i18n = window.JH_PERM_I18N;
    const form = document.getElementById("permissionStatusForm");
    const title = document.getElementById("permissionStatusModalTitle");
    const message = document.getElementById("permissionStatusModalMessage");
    const confirmBtn = document.getElementById("permissionStatusModalConfirm");
    const modal = bootstrap.Modal.getOrCreateInstance(permStatusModalEl);

    document.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-bo-toggle-perm]");
      if (!btn || !document.getElementById("permissions-table")?.contains(btn)) {
        return;
      }
      const id = btn.getAttribute("data-id");
      const label = btn.getAttribute("data-description") || "";
      const activate = btn.getAttribute("data-activate") === "1";
      form.action = i18n.toggleUrl.replace("__ID__", id);
      title.textContent = activate ? i18n.activateTitle : i18n.deactivateTitle;
      message.textContent = `${activate ? i18n.activateMessage : i18n.deactivateMessage}${label ? ` (${label})` : ""}`;
      confirmBtn.textContent = activate ? i18n.activateBtn : i18n.deactivateBtn;
      confirmBtn.className = `btn ${activate ? "btn-success" : "btn-danger"}`;
      modal.show();
    });

    form?.addEventListener("submit", async (event) => {
      if (!window.JH_PERM_reload) {
        return;
      }
      event.preventDefault();
      const csrf = getCsrfToken();
      const body = new FormData(form);
      if (csrf) {
        body.set(csrf.name, csrf.value);
      }
      try {
        const response = await fetch(form.action, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body,
          credentials: "same-origin",
        });
        const payload = await response.json().catch(() => ({}));
        if (payload.csrfName && payload.csrfHash) {
          syncCsrfTokens(payload.csrfName, payload.csrfHash);
        }
        if (!response.ok || !payload.ok) {
          return;
        }
        modal.hide();
        await window.JH_PERM_reload();
      } catch (_err) {
        form.submit();
      }
    });
  }

  const profileStatusModalEl = document.getElementById("profileStatusModal");
  if (profileStatusModalEl && window.JH_PROFILES_I18N) {
    const i18n = window.JH_PROFILES_I18N;
    const form = document.getElementById("profileStatusForm");
    const title = document.getElementById("profileStatusModalTitle");
    const message = document.getElementById("profileStatusModalMessage");
    const confirmBtn = document.getElementById("profileStatusModalConfirm");
    const modal = bootstrap.Modal.getOrCreateInstance(profileStatusModalEl);

    document.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-bo-toggle-profile]");
      if (!btn) {
        return;
      }
      const id = btn.getAttribute("data-id");
      const name = btn.getAttribute("data-name") || "";
      const activate = btn.getAttribute("data-activate") === "1";
      form.action = i18n.toggleUrl.replace("__ID__", id);
      title.textContent = activate ? i18n.activateTitle : i18n.deactivateTitle;
      message.textContent = `${activate ? i18n.activateMessage : i18n.deactivateMessage}${name ? ` (${name})` : ""}`;
      confirmBtn.textContent = activate ? i18n.activateBtn : i18n.deactivateBtn;
      confirmBtn.className = `btn ${activate ? "btn-success" : "btn-danger"}`;
      modal.show();
    });
  }

  const profileForm = document.querySelector("[data-bo-profile-form]");
  if (profileForm) {
    profileForm.addEventListener("submit", (event) => {
      if (!profileForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      profileForm.classList.add("was-validated");
    });
  }

  const permAssign = document.querySelector("[data-bo-perm-assign]");
  if (permAssign) {
    const searchInput = permAssign.querySelector("[data-perm-search]");
    const selectAllBtn = permAssign.querySelector("[data-perm-select-all]");
    const deselectAllBtn = permAssign.querySelector("[data-perm-deselect-all]");
    const countEl = permAssign.querySelector("[data-perm-selected-count]");
    const items = Array.from(permAssign.querySelectorAll("[data-perm-item]"));
    const groups = Array.from(permAssign.querySelectorAll("[data-perm-group]"));

    const itemCheckbox = (item) => item.querySelector("[data-perm-checkbox]");

    const visibleItems = (scope = permAssign) =>
      Array.from(scope.querySelectorAll("[data-perm-item]")).filter(
        (item) => !item.classList.contains("is-hidden")
      );

    const visibleCheckboxes = (scope = permAssign) =>
      visibleItems(scope)
        .map((item) => itemCheckbox(item))
        .filter((cb) => cb && !cb.disabled);

    const refreshSelectionUI = () => {
      items.forEach((item) => {
        const checkbox = itemCheckbox(item);
        const label = item.querySelector(".bo-perm-item") || item;
        label.classList.toggle("is-selected", Boolean(checkbox?.checked));
      });

      const checkedCount = items.filter((item) => itemCheckbox(item)?.checked).length;
      if (countEl) {
        countEl.textContent = String(checkedCount);
      }
    };

    const setGroupVisibility = () => {
      groups.forEach((group) => {
        const visibleInGroup = visibleItems(group).length;
        group.classList.toggle("is-hidden", visibleInGroup === 0);
      });
    };

    searchInput?.addEventListener("input", () => {
      const query = (searchInput.value || "").trim().toLowerCase();
      items.forEach((item) => {
        const haystack = item.getAttribute("data-search") || "";
        item.classList.toggle("is-hidden", query !== "" && !haystack.includes(query));
      });
      setGroupVisibility();
      refreshSelectionUI();
    });

    selectAllBtn?.addEventListener("click", () => {
      visibleCheckboxes().forEach((checkbox) => {
        checkbox.checked = true;
      });
      refreshSelectionUI();
    });

    deselectAllBtn?.addEventListener("click", () => {
      visibleCheckboxes().forEach((checkbox) => {
        checkbox.checked = false;
      });
      refreshSelectionUI();
    });

    permAssign.querySelectorAll("[data-perm-group-select]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const group = btn.closest("[data-perm-group]");
        if (!group) {
          return;
        }
        visibleCheckboxes(group).forEach((checkbox) => {
          checkbox.checked = true;
        });
        refreshSelectionUI();
      });
    });

    permAssign.querySelectorAll("[data-perm-group-deselect]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const group = btn.closest("[data-perm-group]");
        if (!group) {
          return;
        }
        visibleCheckboxes(group).forEach((checkbox) => {
          checkbox.checked = false;
        });
        refreshSelectionUI();
      });
    });

    items.forEach((item) => {
      const checkbox = itemCheckbox(item);
      checkbox?.addEventListener("change", refreshSelectionUI);
    });

    refreshSelectionUI();
  }

  // Court jurisdiction list filters
  document.querySelectorAll("[data-bo-cj-filters], [data-bo-cjc-filters]").forEach((form) => {
    const province = form.querySelector('[data-filter="province"]');
    const commune = form.querySelector('[data-filter="commune"]');
    const apiCommunes = form.dataset.apiCommunes || "";
    province?.addEventListener("change", async () => {
      if (!commune || !apiCommunes) {
        return;
      }
      fillSelect(commune, [], commune.options[0]?.textContent || "");
      const options = province.value
        ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`)
        : [];
      fillSelect(commune, options, commune.options[0]?.textContent || "");
    });
  });

  const cjForm = document.querySelector("[data-bo-cj-form]");
  if (cjForm) {
    const province = cjForm.querySelector('[data-loc="province"]');
    const commune = cjForm.querySelector('[data-loc="commune"]');
    const zone = cjForm.querySelector('[data-loc="zone"]');
    const colline = cjForm.querySelector('[data-loc="colline"]');
    const apiCommunes = cjForm.dataset.apiCommunes || "";
    const apiZones = cjForm.dataset.apiZones || "";
    const apiCollines = cjForm.dataset.apiCollines || "";

    province?.addEventListener("change", async () => {
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });
    commune?.addEventListener("change", async () => {
      fillSelect(zone, commune.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`) : [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });
    zone?.addEventListener("change", async () => {
      fillSelect(colline, zone.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`) : [], colline?.options[0]?.textContent || "");
    });
    cjForm.addEventListener("submit", (event) => {
      if (!cjForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      cjForm.classList.add("was-validated");
    });
  }

  const bindStatusModal = (modalId, formId, titleId, messageId, confirmId, toggleSelector, i18n) => {
    const modalEl = document.getElementById(modalId);
    if (!modalEl || !i18n) {
      return;
    }
    const form = document.getElementById(formId);
    const title = document.getElementById(titleId);
    const message = document.getElementById(messageId);
    const confirmBtn = document.getElementById(confirmId);
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    document.querySelectorAll(toggleSelector).forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        const name = btn.getAttribute("data-name") || "";
        const activate = btn.getAttribute("data-activate") === "1";
        form.action = i18n.toggleUrl.replace("__ID__", id);
        title.textContent = activate ? i18n.activateTitle : i18n.deactivateTitle;
        message.textContent = `${activate ? i18n.activateMessage : i18n.deactivateMessage}${name ? ` (${name})` : ""}`;
        confirmBtn.textContent = activate ? i18n.activateBtn : i18n.deactivateBtn;
        confirmBtn.className = `btn ${activate ? "btn-success" : "btn-danger"}`;
        modal.show();
      });
    });
  };

  bindStatusModal("cjStatusModal", "cjStatusForm", "cjStatusModalTitle", "cjStatusModalMessage", "cjStatusModalConfirm", "[data-bo-toggle-cj]", window.JH_CJ_I18N);
  bindStatusModal("cjcStatusModal", "cjcStatusForm", "cjcStatusModalTitle", "cjcStatusModalMessage", "cjcStatusModalConfirm", "[data-bo-toggle-cjc]", window.JH_CJC_I18N);
  bindStatusModal("jlStatusModal", "jlStatusForm", "jlStatusModalTitle", "jlStatusModalMessage", "jlStatusModalConfirm", "[data-bo-toggle-jl]", window.JH_JL_I18N);
  bindStatusModal("jlcStatusModal", "jlcStatusForm", "jlcStatusModalTitle", "jlcStatusModalMessage", "jlcStatusModalConfirm", "[data-bo-toggle-jlc]", window.JH_JLC_I18N);

  // Court jurisdiction configuration modal
  const cjcForm = document.getElementById("cjcForm");
  const cjcFormModalEl = document.getElementById("cjcFormModal");
  if (cjcForm && cjcFormModalEl && window.JH_CJC_I18N) {
    const i18n = window.JH_CJC_I18N;
    const modal = bootstrap.Modal.getOrCreateInstance(cjcFormModalEl);
    const title = document.getElementById("cjcFormTitle");
    const submitBtn = document.getElementById("cjcFormSubmit");
    const province = cjcForm.querySelector('[data-cjc="province"]');
    const commune = cjcForm.querySelector('[data-cjc="commune"]');
    const niveau = cjcForm.querySelector('[data-cjc="niveau"]');
    const court = cjcForm.querySelector('[data-cjc="court"]');
    const parentProvince = cjcForm.querySelector('[data-cjc="parent-province"]');
    const parentCommune = cjcForm.querySelector('[data-cjc="parent-commune"]');
    const parentNiveau = cjcForm.querySelector('[data-cjc="parent-niveau"]');
    const parentNiveauLabel = document.getElementById("cjc_parent_niveau_label");
    const parentCourt = cjcForm.querySelector('[data-cjc="parent-court"]');
    const apiCommunes = cjcForm.dataset.apiCommunes || "";
    const apiCourts = cjcForm.dataset.apiCourts || "";
    const apiParentLevel = cjcForm.dataset.apiParentLevel || "";

    const loadCommunes = async (provinceEl, communeEl, selected) => {
      const options = provinceEl?.value
        ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(provinceEl.value)}`)
        : [];
      fillSelect(communeEl, options, communeEl?.options[0]?.textContent || "");
      if (selected) {
        communeEl.value = String(selected);
      }
    };

    const loadCourts = async (targetSelect, filters, selected) => {
      const params = new URLSearchParams();
      Object.entries(filters).forEach(([key, value]) => {
        if (value) {
          params.set(key, value);
        }
      });
      const options = await fetchOptions(`${apiCourts}?${params.toString()}`);
      fillSelect(targetSelect, options, targetSelect?.options[0]?.textContent || "");
      if (selected) {
        targetSelect.value = String(selected);
      }
    };

    const refreshParentLevel = async () => {
      if (!niveau?.value || !apiParentLevel) {
        if (parentNiveau) parentNiveau.value = "";
        if (parentNiveauLabel) parentNiveauLabel.value = "";
        return null;
      }
      const response = await fetch(`${apiParentLevel}?niveau_juridiction_id=${encodeURIComponent(niveau.value)}`);
      const data = await response.json();
      const parentId = data.niveau_juridiction_parent_id;
      if (parentNiveau) parentNiveau.value = parentId ? String(parentId) : "";
      const label = (i18n.niveaux || []).find((n) => String(n.id) === String(parentId))?.label || "";
      if (parentNiveauLabel) parentNiveauLabel.value = label;
      return parentId;
    };

    const refreshChildCourts = async (selected) => {
      await loadCourts(court, {
        province_id: province?.value,
        commune_id: commune?.value,
        niveau_juridiction_id: niveau?.value,
      }, selected);
    };

    const refreshParentCourts = async (selected) => {
      const parentLevelId = parentNiveau?.value || (await refreshParentLevel());
      await loadCourts(parentCourt, {
        province_id: parentProvince?.value,
        commune_id: parentCommune?.value,
        niveau_juridiction_id: parentLevelId,
      }, selected);
    };

    const resetForm = async () => {
      cjcForm.classList.remove("was-validated");
      cjcForm.reset();
      fillSelect(commune, [], commune.options[0]?.textContent || "");
      fillSelect(court, [], court.options[0]?.textContent || "");
      fillSelect(parentCommune, [], parentCommune.options[0]?.textContent || "");
      fillSelect(parentCourt, [], parentCourt.options[0]?.textContent || "");
      if (parentNiveau) parentNiveau.value = "";
      if (parentNiveauLabel) parentNiveauLabel.value = "";
    };

    document.querySelectorAll("[data-bo-cjc-create]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await resetForm();
        cjcForm.action = i18n.storeUrl;
        title.textContent = i18n.createTitle;
        submitBtn.textContent = i18n.saveCreate;
      });
    });

    document.querySelectorAll("[data-bo-cjc-edit]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await resetForm();
        cjcForm.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        title.textContent = i18n.editTitle;
        submitBtn.textContent = i18n.saveEdit;
        if (province) province.value = btn.getAttribute("data-province-id") || "";
        if (niveau) niveau.value = btn.getAttribute("data-niveau-id") || "";
        if (parentProvince) parentProvince.value = btn.getAttribute("data-parent-province-id") || "";
        await loadCommunes(province, commune, btn.getAttribute("data-commune-id"));
        await refreshChildCourts(btn.getAttribute("data-juridiction-id"));
        await refreshParentLevel();
        if (parentNiveau && btn.getAttribute("data-parent-niveau-id")) {
          parentNiveau.value = btn.getAttribute("data-parent-niveau-id");
          const label = (i18n.niveaux || []).find((n) => String(n.id) === parentNiveau.value)?.label || "";
          if (parentNiveauLabel) parentNiveauLabel.value = label;
        }
        await loadCommunes(parentProvince, parentCommune, btn.getAttribute("data-parent-commune-id"));
        await refreshParentCourts(btn.getAttribute("data-parent-id"));
        modal.show();
      });
    });

    province?.addEventListener("change", async () => {
      await loadCommunes(province, commune);
      await refreshChildCourts();
    });
    commune?.addEventListener("change", () => refreshChildCourts());
    niveau?.addEventListener("change", async () => {
      await refreshChildCourts();
      await refreshParentLevel();
      await refreshParentCourts();
    });
    parentProvince?.addEventListener("change", async () => {
      await loadCommunes(parentProvince, parentCommune);
      await refreshParentCourts();
    });
    parentCommune?.addEventListener("change", () => refreshParentCourts());

    cjcForm.addEventListener("submit", (event) => {
      if (!cjcForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      cjcForm.classList.add("was-validated");
    });
  }

  // Jurisdiction levels modal
  const jlFormModalEl = document.getElementById("jlFormModal");
  if (jlFormModalEl && window.JH_JL_I18N) {
    const i18n = window.JH_JL_I18N;
    const form = document.getElementById("jlForm");
    const title = document.getElementById("jlFormTitle");
    const submitBtn = document.getElementById("jlFormSubmit");
    const description = document.getElementById("desc_niveau_juridiction");
    const recours = document.getElementById("is_recours");
    const modal = bootstrap.Modal.getOrCreateInstance(jlFormModalEl);

    document.querySelectorAll("[data-bo-jl-create]").forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.storeUrl;
        form.classList.remove("was-validated");
        title.textContent = i18n.createTitle;
        submitBtn.textContent = i18n.saveCreate;
        description.value = "";
        recours.checked = false;
      });
    });
    document.querySelectorAll("[data-bo-jl-edit]").forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        form.classList.remove("was-validated");
        title.textContent = i18n.editTitle;
        submitBtn.textContent = i18n.saveEdit;
        description.value = btn.getAttribute("data-description") || "";
        recours.checked = btn.getAttribute("data-recours") === "1";
        modal.show();
      });
    });
    form?.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  }

  // Jurisdiction level configs modal
  const jlcFormModalEl = document.getElementById("jlcFormModal");
  if (jlcFormModalEl && window.JH_JLC_I18N) {
    const i18n = window.JH_JLC_I18N;
    const form = document.getElementById("jlcForm");
    const title = document.getElementById("jlcFormTitle");
    const submitBtn = document.getElementById("jlcFormSubmit");
    const niveau = document.getElementById("niveau_juridiction_id");
    const parent = document.getElementById("niveau_juridiction_parent_id");
    const modal = bootstrap.Modal.getOrCreateInstance(jlcFormModalEl);

    document.querySelectorAll("[data-bo-jlc-create]").forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.storeUrl;
        form.classList.remove("was-validated");
        title.textContent = i18n.createTitle;
        submitBtn.textContent = i18n.saveCreate;
        niveau.value = "";
        parent.value = "";
      });
    });
    document.querySelectorAll("[data-bo-jlc-edit]").forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        form.classList.remove("was-validated");
        title.textContent = i18n.editTitle;
        submitBtn.textContent = i18n.saveEdit;
        niveau.value = btn.getAttribute("data-niveau") || "";
        parent.value = btn.getAttribute("data-parent") || "";
        modal.show();
      });
    });
    form?.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  }

  // People filters (Province → Commune → Zone → Colline)
  const peopleFilters = document.querySelector("[data-bo-people-filters]");
  if (peopleFilters) {
    const province = peopleFilters.querySelector('[data-filter="province"]');
    const commune = peopleFilters.querySelector('[data-filter="commune"]');
    const zone = peopleFilters.querySelector('[data-filter="zone"]');
    const colline = peopleFilters.querySelector('[data-filter="colline"]');
    const apiCommunes = peopleFilters.dataset.apiCommunes || "";
    const apiZones = peopleFilters.dataset.apiZones || "";
    const apiCollines = peopleFilters.dataset.apiCollines || "";

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      fillSelect(
        commune,
        province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [],
        commune?.options[0]?.textContent || ""
      );
    });

    commune?.addEventListener("change", async () => {
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      fillSelect(
        zone,
        commune.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`) : [],
        zone?.options[0]?.textContent || ""
      );
    });

    zone?.addEventListener("change", async () => {
      fillSelect(
        colline,
        zone.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`) : [],
        colline?.options[0]?.textContent || ""
      );
    });
  }

  const peopleForm = document.querySelector("[data-bo-people-form]");
  if (peopleForm) {
    const province = peopleForm.querySelector('[data-loc="province"]');
    const commune = peopleForm.querySelector('[data-loc="commune"]');
    const zone = peopleForm.querySelector('[data-loc="zone"]');
    const colline = peopleForm.querySelector('[data-loc="colline"]');
    const apiCommunes = peopleForm.dataset.apiCommunes || "";
    const apiZones = peopleForm.dataset.apiZones || "";
    const apiCollines = peopleForm.dataset.apiCollines || "";

    province?.addEventListener("change", async () => {
      fillSelect(
        commune,
        province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [],
        commune?.options[0]?.textContent || ""
      );
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });

    commune?.addEventListener("change", async () => {
      fillSelect(
        zone,
        commune.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`) : [],
        zone?.options[0]?.textContent || ""
      );
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
    });

    zone?.addEventListener("change", async () => {
      fillSelect(
        colline,
        zone.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`) : [],
        colline?.options[0]?.textContent || ""
      );
    });

    peopleForm.addEventListener("submit", (event) => {
      if (!peopleForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      peopleForm.classList.add("was-validated");
    });
  }

  // ---- Complaint module UI ----
  bindStatusModal("csStatusModal", "csStatusForm", "csStatusModalTitle", "csStatusModalMessage", "csStatusModalConfirm", "[data-bo-toggle-cs]", window.JH_CS_I18N);
  bindStatusModal("cscStatusModal", "cscStatusForm", "cscStatusModalTitle", "cscStatusModalMessage", "cscStatusModalConfirm", "[data-bo-toggle-csc]", window.JH_CSC_I18N);
  bindStatusModal("cstStatusModal", "cstStatusForm", "cstStatusModalTitle", "cstStatusModalMessage", "cstStatusModalConfirm", "[data-bo-toggle-cst]", window.JH_CST_I18N);
  bindStatusModal("dtStatusModal", "dtStatusForm", "dtStatusModalTitle", "dtStatusModalMessage", "dtStatusModalConfirm", "[data-bo-toggle-dt]", window.JH_DT_I18N);

  const openProfilesModal = async (urlTemplate, etapeId, titleSuffix) => {
    const modalEl = document.getElementById("csProfilesModal") || document.getElementById("cscProfilesModal");
    if (!modalEl || !urlTemplate) {
      return;
    }
    const tbody = modalEl.querySelector("tbody");
    const empty = modalEl.querySelector("#csProfilesEmpty, #cscProfilesEmpty");
    const title = modalEl.querySelector(".modal-title");
    const profilesTitle = window.JH_CS_I18N?.profilesTitle || window.JH_CSC_I18N?.profilesTitle || "Profiles";
    if (title && titleSuffix) {
      title.textContent = `${profilesTitle} — ${titleSuffix}`;
    }
    tbody.innerHTML = "";
    if (empty) {
      empty.classList.add("d-none");
    }
    try {
      const response = await fetch(urlTemplate.replace("__ID__", String(etapeId)), { headers: { Accept: "application/json" } });
      const data = await response.json();
      const profiles = data.profiles || [];
      if (!profiles.length) {
        empty?.classList.remove("d-none");
      } else {
        profiles.forEach((p) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `<td>${p.code || ""}</td><td>${p.name || ""}</td><td>${p.description || ""}</td><td>${p.status || ""}</td>`;
          tbody.appendChild(tr);
        });
      }
    } catch (e) {
      empty?.classList.remove("d-none");
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  document.querySelectorAll("[data-bo-cs-profiles]").forEach((btn) => {
    btn.addEventListener("click", () => {
      openProfilesModal(window.JH_CS_I18N?.profilesUrl, btn.getAttribute("data-id"), btn.getAttribute("data-name") || "");
    });
  });
  document.querySelectorAll("[data-bo-csc-profiles]").forEach((btn) => {
    btn.addEventListener("click", () => {
      openProfilesModal(window.JH_CSC_I18N?.profilesUrl, btn.getAttribute("data-etape-id") || btn.getAttribute("data-id"), btn.getAttribute("data-name") || "");
    });
  });

  const openStageActionsModal = async (etapeId, titleSuffix) => {
    const modalEl = document.getElementById("csActionsModal");
    const i18n = window.JH_CS_I18N || {};
    if (!modalEl || !i18n.actionsUrl) {
      return;
    }
    const title = modalEl.querySelector(".modal-title");
    const empty = document.getElementById("csActionsEmpty");
    const table = document.getElementById("csActionsModalTable");
    const tbody = table?.querySelector("tbody");
    if (!tbody) {
      return;
    }
    if (title) {
      title.textContent = titleSuffix ? `${i18n.actionsTitle || "Actions"} — ${titleSuffix}` : (i18n.actionsTitle || "Actions");
    }
    if ($.fn.DataTable?.isDataTable(table)) {
      $(table).DataTable().clear().destroy();
    }
    tbody.innerHTML = "";
    empty?.classList.add("d-none");

    try {
      const response = await fetch(i18n.actionsUrl.replace("__ID__", String(etapeId)), {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      const data = await response.json();
      const actions = data.actions || [];
      if (!actions.length) {
        empty?.classList.remove("d-none");
      } else {
        actions.forEach((action) => {
          const tr = document.createElement("tr");
          const toggleUrl = (i18n.actionToggleUrl || "")
            .replace("__ETAPE__", String(etapeId))
            .replace("__ID__", String(action.id));
          const activate = action.is_active ? "0" : "1";
          const label = action.is_active ? (i18n.actionDeactivate || "Deactivate") : (i18n.actionActivate || "Activate");
          const icon = action.is_active ? "bi-toggle-off" : "bi-toggle-on";
          const danger = action.is_active ? "is-danger" : "is-success";
          tr.innerHTML = `
            <td>${action.description || ""}</td>
            <td><span class="bo-status-pill ${action.is_active ? "is-active" : "is-inactive"}">${action.status || ""}</span></td>
            <td>
              <form method="post" action="${toggleUrl}">
                <input type="hidden" name="${i18n.csrfName || ""}" value="${i18n.csrfHash || ""}">
                <button class="btn btn-bo-icon ${danger}" type="submit" title="${label}">
                  <i class="bi ${icon}"></i>
                </button>
              </form>
            </td>`;
          tbody.appendChild(tr);
        });
      }
    } catch (_err) {
      empty?.classList.remove("d-none");
    }

    if (table && !$.fn.DataTable?.isDataTable(table)) {
      const dt = initDataTable(table);
      const searchInput = document.getElementById("cs-actions-modal-search");
      if (dt && searchInput) {
        searchInput.value = "";
        searchInput.oninput = () => dt.search(searchInput.value).draw();
      }
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  document.querySelectorAll("[data-bo-cs-actions]").forEach((btn) => {
    btn.addEventListener("click", () => {
      openStageActionsModal(btn.getAttribute("data-id"), btn.getAttribute("data-name") || "");
    });
  });

  const bindSimpleCrudModal = (formId, modalId, createSelector, editSelector, i18n, fillFn) => {
    const form = document.getElementById(formId);
    const modalEl = document.getElementById(modalId);
    if (!form || !modalEl || !i18n) {
      return;
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const title = document.getElementById(modalId.replace("Modal", "Title").replace("Form", "Form"));
    const titleEl = document.getElementById(formId.replace("Form", "FormTitle")) || modalEl.querySelector(".modal-title");
    const submitBtn = document.getElementById(formId.replace("Form", "FormSubmit")) || form.querySelector('[type="submit"]');

    document.querySelectorAll(createSelector).forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.storeUrl;
        form.reset();
        form.classList.remove("was-validated");
        if (titleEl) titleEl.textContent = i18n.createTitle;
        if (submitBtn) submitBtn.textContent = i18n.saveCreate;
        fillFn?.(null);
        modal.show();
      });
    });

    document.querySelectorAll(editSelector).forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        form.classList.remove("was-validated");
        if (titleEl) titleEl.textContent = i18n.editTitle;
        if (submitBtn) submitBtn.textContent = i18n.saveEdit;
        fillFn?.(btn);
        modal.show();
      });
    });

    form.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  };

  bindSimpleCrudModal("cstForm", "cstFormModal", "[data-bo-cst-create]", "[data-bo-cst-edit]", window.JH_CST_I18N, (btn) => {
    const input = document.getElementById("description_statut_plainte");
    if (input) input.value = btn ? btn.getAttribute("data-description") || "" : "";
  });

  bindSimpleCrudModal("sumStForm", "sumStFormModal", "[data-bo-sum-st-create]", "[data-bo-sum-st-edit]", window.JH_SUM_ST_I18N, (btn) => {
    const input = document.getElementById("description_statut_convocation");
    if (input) input.value = btn ? btn.getAttribute("data-description") || "" : "";
  });

  bindSimpleCrudModal("hrgStForm", "hrgStFormModal", "[data-bo-hrg-st-create]", "[data-bo-hrg-st-edit]", window.JH_HRG_ST_I18N, (btn) => {
    const input = document.getElementById("description_statut_audience");
    if (input) input.value = btn ? btn.getAttribute("data-description") || "" : "";
  });

  bindSimpleCrudModal("vrdTypeForm", "vrdTypeFormModal", "[data-bo-vrd-type-create]", "[data-bo-vrd-type-edit]", window.JH_VRD_TYPE_I18N, (btn) => {
    const input = document.getElementById("description_type_verdict");
    if (input) input.value = btn ? btn.getAttribute("data-description") || "" : "";
  });

  bindSimpleCrudModal("trfStForm", "trfStFormModal", "[data-bo-trf-st-create]", "[data-bo-trf-st-edit]", window.JH_TRF_ST_I18N, (btn) => {
    const input = document.getElementById("description_statut_transfert_dossier");
    if (input) input.value = btn ? btn.getAttribute("data-description") || "" : "";
  });

  bindSimpleCrudModal("hrgAssignForm", "hrgAssignModal", "[data-bo-hrg-assign-create]", "[data-bo-hrg-assign-edit]", window.JH_HRG_ASSIGN_I18N, (btn) => {
    const user = document.getElementById("utilisateur_affecte_id");
    const profil = document.getElementById("profil_id");
    const active = document.getElementById("is_active");
    if (user) user.value = btn ? btn.getAttribute("data-user") || "" : "";
    if (profil) profil.value = btn ? btn.getAttribute("data-profil") || "" : "";
    if (active) active.checked = btn ? btn.getAttribute("data-active") === "1" : true;
  });

  bindStatusModal("hrgAssignStatusModal", "hrgAssignStatusForm", "hrgAssignStatusModalTitle", "hrgAssignStatusModalMessage", "hrgAssignStatusModalConfirm", "[data-bo-toggle-hrg-assign]", window.JH_HRG_ASSIGN_I18N);

  bindSimpleCrudModal("dtForm", "dtFormModal", "[data-bo-dt-create]", "[data-bo-dt-edit]", window.JH_DT_I18N, (btn) => {
    const code = document.getElementById("code_type_document");
    const label = document.getElementById("libelle_type_document");
    const niveau = document.getElementById("niveau_juridiction_id");
    const obligatoire = document.getElementById("is_obligatoire");
    if (code) code.value = btn ? btn.getAttribute("data-code") || "" : "";
    if (label) label.value = btn ? btn.getAttribute("data-description") || "" : "";
    if (niveau) niveau.value = btn ? btn.getAttribute("data-niveau") || "" : "";
    if (obligatoire) obligatoire.checked = btn ? btn.getAttribute("data-obligatoire") === "1" : false;
  });

  const cscForm = document.getElementById("cscForm");
  const cscFormModalEl = document.getElementById("cscFormModal");
  if (cscForm && cscFormModalEl && window.JH_CSC_I18N) {
    const i18n = window.JH_CSC_I18N;
    const modal = bootstrap.Modal.getOrCreateInstance(cscFormModalEl);
    const title = document.getElementById("cscFormTitle");
    const submitBtn = document.getElementById("cscFormSubmit");
    const niveauActuel = document.getElementById("niveau_juridiction_actuel_id");
    const etapeActuel = document.getElementById("etape_plainte_actuel_id");
    const actionSelect = document.getElementById("etape_plainte_action_id");
    const niveauSuivant = document.getElementById("niveau_juridiction_suivant_id");
    const etapeSuivant = document.getElementById("etape_plainte_suivant_id");
    const urlRoute = document.getElementById("url_route");
    const apiStages = cscForm.dataset.apiStages || i18n.stagesUrl || "";
    const apiActions = cscForm.dataset.apiActions || i18n.actionsUrl || "";

    const loadStages = async (niveauEl, etapeEl, selected) => {
      const options = niveauEl?.value
        ? await fetchOptions(`${apiStages}?niveau_juridiction_id=${encodeURIComponent(niveauEl.value)}`)
        : [];
      fillSelect(etapeEl, options, etapeEl?.options[0]?.textContent || "");
      if (selected) etapeEl.value = String(selected);
    };

    const loadActions = async (selected, includeId) => {
      if (!actionSelect) return;
      const etapeId = etapeActuel?.value || "";
      const params = new URLSearchParams();
      if (etapeId) params.set("etape_plainte_id", etapeId);
      if (includeId) params.set("include_id", String(includeId));
      const options = etapeId && apiActions
        ? await fetchOptions(`${apiActions}?${params.toString()}`)
        : [];
      fillSelect(actionSelect, options, actionSelect.options[0]?.textContent || "");
      if (selected) actionSelect.value = String(selected);
    };

    niveauActuel?.addEventListener("change", async () => {
      await loadStages(niveauActuel, etapeActuel);
      await loadActions();
    });
    etapeActuel?.addEventListener("change", () => loadActions());
    niveauSuivant?.addEventListener("change", () => loadStages(niveauSuivant, etapeSuivant));

    document.querySelectorAll("[data-bo-csc-create]").forEach((btn) => {
      btn.addEventListener("click", () => {
        cscForm.action = i18n.storeUrl;
        cscForm.reset();
        cscForm.classList.remove("was-validated");
        fillSelect(etapeActuel, [], etapeActuel?.options[0]?.textContent || "");
        fillSelect(etapeSuivant, [], etapeSuivant?.options[0]?.textContent || "");
        fillSelect(actionSelect, [], actionSelect?.options[0]?.textContent || "");
        if (title) title.textContent = i18n.createTitle;
        if (submitBtn) submitBtn.textContent = i18n.saveCreate;
        modal.show();
      });
    });

    document.querySelectorAll("[data-bo-csc-edit]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        cscForm.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        cscForm.classList.remove("was-validated");
        if (title) title.textContent = i18n.editTitle;
        if (submitBtn) submitBtn.textContent = i18n.saveEdit;
        if (niveauActuel) niveauActuel.value = btn.getAttribute("data-niveau-actuel") || "";
        if (niveauSuivant) niveauSuivant.value = btn.getAttribute("data-niveau-suivant") || "";
        if (urlRoute) urlRoute.value = btn.getAttribute("data-url-route") || btn.getAttribute("data-route") || "";
        await loadStages(niveauActuel, etapeActuel, btn.getAttribute("data-etape-actuel"));
        await loadActions(btn.getAttribute("data-action-id"), btn.getAttribute("data-action-id"));
        await loadStages(niveauSuivant, etapeSuivant, btn.getAttribute("data-etape-suivant"));
        modal.show();
      });
    });

    cscForm.addEventListener("submit", (event) => {
      if (!cscForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      cscForm.classList.add("was-validated");
    });
  }

  const cmpFilters = document.querySelector("[data-bo-cmp-filters]");
  if (cmpFilters) {
    const province = cmpFilters.querySelector('[data-filter="province"]');
    const commune = cmpFilters.querySelector('[data-filter="commune"]');
    const niveau = cmpFilters.querySelector('[data-filter="niveau"]');
    const juridiction = cmpFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = cmpFilters.dataset.apiCommunes || "";
    const apiJurisdictions = cmpFilters.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      const options = await fetchOptions(`${apiJurisdictions}?${params.toString()}`);
      fillSelect(juridiction, options, juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);
  }

  const cmpForm = document.querySelector("[data-bo-cmp-form]");
  if (cmpForm) {
    const niveau = cmpForm.querySelector('[data-cmp="niveau"]');
    const province = cmpForm.querySelector('[data-cmp="province"]');
    const commune = cmpForm.querySelector('[data-cmp="commune"]');
    const juridiction = cmpForm.querySelector('[data-cmp="juridiction"]');
    const docsBox = cmpForm.querySelector("[data-doc-types]");
    const apiCommunes = cmpForm.dataset.apiCommunes || "";
    const apiZones = cmpForm.dataset.apiZones || "";
    const apiCollines = cmpForm.dataset.apiCollines || "";
    const apiCourts = cmpForm.dataset.apiJurisdictions || "";
    const apiDocs = cmpForm.dataset.apiDocTypes || "";

    const refreshCourts = async () => {
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      const options = await fetchOptions(`${apiCourts}?${params.toString()}`);
      fillSelect(juridiction, options, juridiction?.options[0]?.textContent || "");
    };

    const refreshDocs = async () => {
      if (!docsBox || !apiDocs) return;
      if (!niveau?.value) {
        docsBox.innerHTML = `<p class="text-muted">${docsBox.dataset.empty || ""}</p>`;
        return;
      }
      const response = await fetch(`${apiDocs}?niveau_juridiction_id=${encodeURIComponent(niveau.value)}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      const types = data.types || [];
      docsBox.innerHTML = types.map((t) => {
        const required = t.is_obligatoire === true || t.is_obligatoire === "t" || t.is_obligatoire === 1 || t.is_obligatoire === "1";
        return `<div class="mb-3"><label class="form-label">${t.libelle_type_document || t.code_type_document}${required ? " *" : ""}</label>
          <input class="form-control" type="file" name="documents[${t.type_document_id}][]" accept=".pdf,.jpg,.jpeg,.png" ${required ? "required" : ""}></div>`;
      }).join("") || `<p class="text-muted">—</p>`;
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", async () => {
      await refreshCourts();
      await refreshDocs();
    });

    const bindParcelRow = (row) => {
      const p = row.querySelector("[data-parcel-province]");
      const c = row.querySelector("[data-parcel-commune]");
      const z = row.querySelector("[data-parcel-zone]");
      const col = row.querySelector("[data-parcel-colline]");
      p?.addEventListener("change", async () => {
        fillSelect(c, p.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(p.value)}`) : [], c?.options[0]?.textContent || "");
        fillSelect(z, [], z?.options[0]?.textContent || "");
        fillSelect(col, [], col?.options[0]?.textContent || "");
      });
      c?.addEventListener("change", async () => {
        fillSelect(z, c.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(c.value)}`) : [], z?.options[0]?.textContent || "");
        fillSelect(col, [], col?.options[0]?.textContent || "");
      });
      z?.addEventListener("change", async () => {
        fillSelect(col, z.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(z.value)}`) : [], col?.options[0]?.textContent || "");
      });
      row.querySelector("[data-parcel-remove]")?.addEventListener("click", () => {
        const container = document.querySelector("[data-parcel-rows]");
        if (container && container.querySelectorAll("[data-parcel-row]").length > 1) {
          row.remove();
        }
      });
    };

    document.querySelectorAll("[data-parcel-row]").forEach(bindParcelRow);
    document.querySelector("[data-parcel-add]")?.addEventListener("click", () => {
      const container = document.querySelector("[data-parcel-rows]");
      const first = container?.querySelector("[data-parcel-row]");
      if (!container || !first) return;
      const clone = first.cloneNode(true);
      const index = container.querySelectorAll("[data-parcel-row]").length;
      clone.querySelectorAll("textarea, input, select").forEach((el) => {
        if (el.name) el.name = el.name.replace(/parcels\[\d+]/, `parcels[${index}]`);
        if (el.tagName === "TEXTAREA" || el.tagName === "INPUT") el.value = "";
        if (el.tagName === "SELECT" && !el.dataset.parcelProvince) el.innerHTML = el.options[0] ? `<option value="">${el.options[0].textContent}</option>` : "";
      });
      container.appendChild(clone);
      bindParcelRow(clone);
    });

    cmpForm.addEventListener("submit", (event) => {
      if (!cmpForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      cmpForm.classList.add("was-validated");
    });
  }

  // ---- Appeals module ----
  const aplFilters = document.querySelector("[data-bo-apl-filters]");
  if (aplFilters) {
    const province = aplFilters.querySelector('[data-filter="province"]');
    const commune = aplFilters.querySelector('[data-filter="commune"]');
    const niveau = aplFilters.querySelector('[data-filter="niveau"]');
    const juridiction = aplFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = aplFilters.dataset.apiCommunes || "";
    const apiJurisdictions = aplFilters.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(juridiction, await fetchOptions(`${apiJurisdictions}?${params.toString()}`), juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);
  }

  const aplForm = document.querySelector("[data-bo-apl-form]");
  if (aplForm) {
    const parent = aplForm.querySelector('[data-apl="parent"]');
    const niveau = aplForm.querySelector('[data-apl="niveau"]');
    const province = aplForm.querySelector('[data-apl="province"]');
    const commune = aplForm.querySelector('[data-apl="commune"]');
    const juridiction = aplForm.querySelector('[data-apl="juridiction"]');
    const deadlineDateEl = aplForm.querySelector("[data-apl-deadline-date]");
    const deadlineWithinEl = aplForm.querySelector("[data-apl-deadline-within]");
    const docsBox = aplForm.querySelector("[data-doc-types]");
    const apiCommunes = aplForm.dataset.apiCommunes || "";
    const apiZones = aplForm.dataset.apiZones || "";
    const apiCollines = aplForm.dataset.apiCollines || "";
    const apiCourts = aplForm.dataset.apiJurisdictions || "";
    const apiDocs = aplForm.dataset.apiDocTypes || "";
    const yesLabel = "Yes";
    const noLabel = "No";

    const refreshCourts = async () => {
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(juridiction, await fetchOptions(`${apiCourts}?${params.toString()}`), juridiction?.options[0]?.textContent || "");
    };

    const refreshDocs = async () => {
      if (!docsBox || !apiDocs) return;
      if (!niveau?.value) {
        docsBox.innerHTML = `<p class="text-muted">${aplForm.dataset.emptyDocs || ""}</p>`;
        return;
      }
      const response = await fetch(`${apiDocs}?niveau_juridiction_id=${encodeURIComponent(niveau.value)}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      const types = data.types || [];
      docsBox.innerHTML = types.map((t) => {
        const required = t.is_obligatoire === true || t.is_obligatoire === "t" || t.is_obligatoire === 1 || t.is_obligatoire === "1";
        return `<div class="mb-3"><label class="form-label">${t.libelle_type_document || t.code_type_document}${required ? " *" : ""}</label>
          <input class="form-control" type="file" name="documents[${t.type_document_id}][]" accept=".pdf,.jpg,.jpeg,.png" ${required ? "required" : ""}></div>`;
      }).join("") || `<p class="text-muted">—</p>`;
    };

    const updateDeadlinePreview = () => {
      const opt = parent?.selectedOptions?.[0];
      const deadline = opt?.getAttribute("data-deadline") || "";
      if (deadlineDateEl) deadlineDateEl.textContent = deadline || "—";
      if (deadlineWithinEl) {
        if (!deadline) {
          deadlineWithinEl.textContent = "—";
          return;
        }
        const limit = new Date(deadline.slice(0, 10));
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        deadlineWithinEl.textContent = today <= limit ? yesLabel : noLabel;
      }
    };

    parent?.addEventListener("change", async () => {
      const opt = parent.selectedOptions?.[0];
      const nextNiveau = opt?.getAttribute("data-next-niveau") || "";
      if (niveau && nextNiveau) {
        niveau.value = String(nextNiveau);
      }
      updateDeadlinePreview();
      await refreshCourts();
      await refreshDocs();
    });

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", async () => {
      await refreshCourts();
      await refreshDocs();
    });

    const bindParcelRow = (row) => {
      const p = row.querySelector("[data-parcel-province]");
      const c = row.querySelector("[data-parcel-commune]");
      const z = row.querySelector("[data-parcel-zone]");
      const col = row.querySelector("[data-parcel-colline]");
      p?.addEventListener("change", async () => {
        fillSelect(c, p.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(p.value)}`) : [], c?.options[0]?.textContent || "");
        fillSelect(z, [], z?.options[0]?.textContent || "");
        fillSelect(col, [], col?.options[0]?.textContent || "");
      });
      c?.addEventListener("change", async () => {
        fillSelect(z, c.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(c.value)}`) : [], z?.options[0]?.textContent || "");
        fillSelect(col, [], col?.options[0]?.textContent || "");
      });
      z?.addEventListener("change", async () => {
        fillSelect(col, z.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(z.value)}`) : [], col?.options[0]?.textContent || "");
      });
      row.querySelector("[data-parcel-remove]")?.addEventListener("click", () => {
        const container = aplForm.querySelector("[data-parcel-rows]");
        if (container && container.querySelectorAll("[data-parcel-row]").length > 1) row.remove();
      });
    };

    aplForm.querySelectorAll("[data-parcel-row]").forEach(bindParcelRow);
    aplForm.querySelector("[data-parcel-add]")?.addEventListener("click", () => {
      const container = aplForm.querySelector("[data-parcel-rows]");
      const first = container?.querySelector("[data-parcel-row]");
      if (!container || !first) return;
      const clone = first.cloneNode(true);
      const index = container.querySelectorAll("[data-parcel-row]").length;
      clone.querySelectorAll("textarea, input, select").forEach((el) => {
        if (el.name) el.name = el.name.replace(/parcels\[\d+]/, `parcels[${index}]`);
        if (el.tagName === "TEXTAREA" || el.tagName === "INPUT") el.value = "";
        if (el.tagName === "SELECT" && !el.dataset.parcelProvince) {
          el.innerHTML = el.options[0] ? `<option value="">${el.options[0].textContent}</option>` : "";
        }
      });
      container.appendChild(clone);
      bindParcelRow(clone);
    });

    updateDeadlinePreview();
    aplForm.addEventListener("submit", (event) => {
      if (!aplForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      aplForm.classList.add("was-validated");
    });
  }

  // ---- Summons module ----
  const sumFilters = document.querySelector("[data-bo-sum-filters]");
  if (sumFilters) {
    const province = sumFilters.querySelector('[data-filter="province"]');
    const commune = sumFilters.querySelector('[data-filter="commune"]');
    const niveau = sumFilters.querySelector('[data-filter="niveau"]');
    const juridiction = sumFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = sumFilters.dataset.apiCommunes || "";
    const apiJurisdictions = sumFilters.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(juridiction, await fetchOptions(`${apiJurisdictions}?${params.toString()}`), juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);
  }

  const sumForm = document.querySelector("[data-bo-sum-form]");
  if (sumForm) {
    const province = sumForm.querySelector('[data-sum="province"]');
    const commune = sumForm.querySelector('[data-sum="commune"]');
    const zone = sumForm.querySelector('[data-sum="zone"]');
    const colline = sumForm.querySelector('[data-sum="colline"]');
    const court = sumForm.querySelector('[data-sum="court"]');
    const apiCommunes = sumForm.dataset.apiCommunes || "";
    const apiZones = sumForm.dataset.apiZones || "";
    const apiCollines = sumForm.dataset.apiCollines || "";
    const apiCourts = sumForm.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!court || !apiCourts) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      fillSelect(court, await fetchOptions(`${apiCourts}?${params.toString()}`), court.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", async () => {
      fillSelect(zone, commune.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`) : [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      await refreshCourts();
    });
    zone?.addEventListener("change", async () => {
      fillSelect(colline, zone.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`) : [], colline?.options[0]?.textContent || "");
    });

    sumForm.addEventListener("submit", (event) => {
      if (!sumForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      sumForm.classList.add("was-validated");
    });
  }

  // ---- Hearings module ----
  document.querySelectorAll("[data-bo-hrg-complaints]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const title = document.getElementById("hrgComplaintsModalTitle");
      const body = document.getElementById("hrgComplaintsModalBody");
      if (title) title.textContent = btn.getAttribute("data-title") || "";
      if (!body) return;
      let rows = [];
      try {
        rows = JSON.parse(btn.getAttribute("data-rows") || "[]");
      } catch (e) {
        rows = [];
      }
      body.innerHTML = rows.length
        ? rows.map((r) => `<tr>
            <td><code>${r.numero_dossier || "—"}</code></td>
            <td>${r.objet || "—"}</td>
            <td>${[r.desc_niveau_juridiction, r.nom_juridiction].filter(Boolean).join(" / ") || "—"}</td>
            <td>${r.description_etape_plainte || "—"}</td>
            <td>${r.description_statut_plainte || "—"}</td>
            <td>${r.date_depot || "—"}</td>
          </tr>`).join("")
        : `<tr><td colspan="6" class="text-muted">—</td></tr>`;
    });
  });

  const hrgFilters = document.querySelector("[data-bo-hrg-filters]");
  if (hrgFilters) {
    const province = hrgFilters.querySelector('[data-filter="province"]');
    const commune = hrgFilters.querySelector('[data-filter="commune"]');
    const niveau = hrgFilters.querySelector('[data-filter="niveau"]');
    const juridiction = hrgFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = hrgFilters.dataset.apiCommunes || "";
    const apiJurisdictions = hrgFilters.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(juridiction, await fetchOptions(`${apiJurisdictions}?${params.toString()}`), juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);
  }

  const hrgForm = document.querySelector("[data-bo-hrg-form]");
  if (hrgForm) {
    const niveau = hrgForm.querySelector('[data-hrg="niveau"]');
    const province = hrgForm.querySelector('[data-hrg="province"]');
    const commune = hrgForm.querySelector('[data-hrg="commune"]');
    const zone = hrgForm.querySelector('[data-hrg="zone"]');
    const colline = hrgForm.querySelector('[data-hrg="colline"]');
    const court = hrgForm.querySelector('[data-hrg="court"]');
    const complaints = hrgForm.querySelector('[data-hrg="complaints"]');
    const apiCommunes = hrgForm.dataset.apiCommunes || "";
    const apiZones = hrgForm.dataset.apiZones || "";
    const apiCollines = hrgForm.dataset.apiCollines || "";
    const apiCourts = hrgForm.dataset.apiJurisdictions || "";
    const apiComplaints = hrgForm.dataset.apiComplaints || "";

    const refreshCourts = async () => {
      if (!court || !apiCourts) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(court, await fetchOptions(`${apiCourts}?${params.toString()}`), court.options[0]?.textContent || "");
    };

    const refreshComplaints = async () => {
      if (!complaints || !apiComplaints) return;
      if (!court?.value) {
        complaints.innerHTML = "";
        return;
      }
      const response = await fetch(`${apiComplaints}?juridiction_id=${encodeURIComponent(court.value)}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      const options = data.options || [];
      complaints.innerHTML = options.map((o) => `<option value="${o.id}">${o.label}${o.court ? ` (${o.court})` : ""}</option>`).join("");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      fillSelect(zone, [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      await refreshCourts();
      await refreshComplaints();
    });
    commune?.addEventListener("change", async () => {
      fillSelect(zone, commune.value ? await fetchOptions(`${apiZones}?commune_id=${encodeURIComponent(commune.value)}`) : [], zone?.options[0]?.textContent || "");
      fillSelect(colline, [], colline?.options[0]?.textContent || "");
      await refreshCourts();
      await refreshComplaints();
    });
    zone?.addEventListener("change", async () => {
      fillSelect(colline, zone.value ? await fetchOptions(`${apiCollines}?zone_id=${encodeURIComponent(zone.value)}`) : [], colline?.options[0]?.textContent || "");
    });
    niveau?.addEventListener("change", async () => {
      await refreshCourts();
      await refreshComplaints();
    });
    court?.addEventListener("change", refreshComplaints);

    hrgForm.addEventListener("submit", (event) => {
      if (!hrgForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      hrgForm.classList.add("was-validated");
    });
  }

  const hrgProcess = document.querySelector("[data-bo-hrg-process]");
  if (hrgProcess) {
    const held = hrgProcess.querySelector("[data-hrg-held]");
    const yesBox = hrgProcess.querySelector("[data-hrg-held-yes]");
    const noBox = hrgProcess.querySelector("[data-hrg-held-no]");
    const syncHeld = () => {
      const isHeld = held?.value !== "0";
      yesBox?.classList.toggle("d-none", !isHeld);
      noBox?.classList.toggle("d-none", isHeld);
      hrgProcess.querySelectorAll("#date_tenue, #heure_debut, #heure_fin, #rapport").forEach((el) => {
        if (isHeld) el.setAttribute("required", "required");
        else el.removeAttribute("required");
      });
      const motif = hrgProcess.querySelector("#motif_report");
      if (motif) {
        if (!isHeld) motif.setAttribute("required", "required");
        else motif.removeAttribute("required");
      }
    };
    held?.addEventListener("change", syncHeld);
    syncHeld();
    hrgProcess.addEventListener("submit", (event) => {
      if (!hrgProcess.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      hrgProcess.classList.add("was-validated");
    });
  }

  // ---- Verdicts module ----
  const vrdFilters = document.querySelector("[data-bo-vrd-filters]");
  if (vrdFilters) {
    const province = vrdFilters.querySelector('[data-filter="province"]');
    const commune = vrdFilters.querySelector('[data-filter="commune"]');
    const niveau = vrdFilters.querySelector('[data-filter="niveau"]');
    const juridiction = vrdFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = vrdFilters.dataset.apiCommunes || "";
    const apiJurisdictions = vrdFilters.dataset.apiJurisdictions || "";

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(juridiction, await fetchOptions(`${apiJurisdictions}?${params.toString()}`), juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, [], commune?.options[0]?.textContent || "");
      if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);
  }

  const vrdForm = document.querySelector("[data-bo-vrd-form]");
  if (vrdForm) {
    const niveau = vrdForm.querySelector('[data-vrd="niveau"]');
    const province = vrdForm.querySelector('[data-vrd="province"]');
    const commune = vrdForm.querySelector('[data-vrd="commune"]');
    const court = vrdForm.querySelector('[data-vrd="court"]');
    const hearing = vrdForm.querySelector('[data-vrd="hearing"]');
    const judges = vrdForm.querySelector('[data-vrd="judges"]');
    const verdictDate = vrdForm.querySelector('[data-vrd="verdict-date"]');
    const deadline = vrdForm.querySelector('[data-vrd="deadline"]');
    const apiCommunes = vrdForm.dataset.apiCommunes || "";
    const apiCourts = vrdForm.dataset.apiJurisdictions || "";
    const apiHearings = vrdForm.dataset.apiHearings || "";
    const apiJudges = vrdForm.dataset.apiJudges || "";
    const apiDeadline = vrdForm.dataset.apiDeadline || "";

    const refreshCourts = async () => {
      if (!court || !apiCourts) return;
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      fillSelect(court, await fetchOptions(`${apiCourts}?${params.toString()}`), court.options[0]?.textContent || "");
    };

    const refreshHearings = async () => {
      if (!hearing || !apiHearings) return;
      const params = new URLSearchParams();
      if (court?.value) params.set("juridiction_id", court.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      const response = await fetch(`${apiHearings}?${params.toString()}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      const options = data.options || [];
      hearing.innerHTML = `<option value="">${hearing.options[0]?.textContent || ""}</option>` +
        options.map((o) => `<option value="${o.id}" data-hearing-date="${o.hearing_date || ""}">${o.label}</option>`).join("");
      if (judges) judges.innerHTML = "";
    };

    const refreshJudges = async () => {
      if (!judges || !apiJudges || !hearing?.value) {
        if (judges) judges.innerHTML = "";
        return;
      }
      const response = await fetch(`${apiJudges}?audience_plainte_id=${encodeURIComponent(hearing.value)}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      const options = data.options || [];
      judges.innerHTML = options.map((o) => `<option value="${o.id}">${o.label}</option>`).join("");
      const hd = data.hearing_date || hearing.selectedOptions?.[0]?.getAttribute("data-hearing-date") || "";
      if (verdictDate && hd) verdictDate.min = hd;
    };

    const refreshDeadline = async () => {
      if (!deadline || !apiDeadline || !verdictDate?.value) return;
      const response = await fetch(`${apiDeadline}?date_verdict=${encodeURIComponent(verdictDate.value)}`, { headers: { Accept: "application/json" } });
      const data = await response.json();
      if (data.deadline) deadline.value = data.deadline;
    };

    province?.addEventListener("change", async () => {
      fillSelect(commune, province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [], commune?.options[0]?.textContent || "");
      await refreshCourts();
      await refreshHearings();
    });
    commune?.addEventListener("change", async () => {
      await refreshCourts();
      await refreshHearings();
    });
    niveau?.addEventListener("change", async () => {
      await refreshCourts();
      await refreshHearings();
    });
    court?.addEventListener("change", refreshHearings);
    hearing?.addEventListener("change", refreshJudges);
    verdictDate?.addEventListener("change", refreshDeadline);

    vrdForm.addEventListener("submit", (event) => {
      if (!vrdForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      vrdForm.classList.add("was-validated");
    });
  }

  // ---- Case file transfers ----
  const bindCourtCascade = (root, prefix, apiCommunes, apiJurisdictions) => {
    const province = root.querySelector(`[data-filter="${prefix}-province"]`);
    const commune = root.querySelector(`[data-filter="${prefix}-commune"]`);
    const niveau = root.querySelector(`[data-filter="${prefix}-niveau"]`);
    const juridiction = root.querySelector(`[data-filter="${prefix}-juridiction"]`);

    const refreshCourts = async () => {
      if (!juridiction || !apiJurisdictions) {
        return;
      }
      const params = new URLSearchParams();
      if (province?.value) params.set("province_id", province.value);
      if (commune?.value) params.set("commune_id", commune.value);
      if (niveau?.value) params.set("niveau_juridiction_id", niveau.value);
      const qs = params.toString();
      const options = await fetchOptions(`${apiJurisdictions}${qs ? `?${qs}` : ""}`);
      fillSelect(juridiction, options, juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      if (commune && apiCommunes) {
        fillSelect(commune, [], commune.options[0]?.textContent || "");
        if (juridiction) {
          fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
        }
        const options = province.value
          ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`)
          : [];
        fillSelect(commune, options, commune.options[0]?.textContent || "");
      } else if (juridiction) {
        fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
      }
      await refreshCourts();
    });
    commune?.addEventListener("change", refreshCourts);
    niveau?.addEventListener("change", refreshCourts);

    return { province, commune, niveau, juridiction, refreshCourts };
  };

  document.querySelectorAll("[data-bo-trf-filters]").forEach((form) => {
    const apiCommunes = form.dataset.apiCommunes || "";
    const apiJurisdictions = form.dataset.apiJurisdictions || "";
    bindCourtCascade(form, "src", apiCommunes, apiJurisdictions);
    bindCourtCascade(form, "dst", apiCommunes, apiJurisdictions);
  });

  const trfCreate = document.querySelector("[data-bo-trf-create]");
  if (trfCreate) {
    const apiCommunes = trfCreate.dataset.apiCommunes || "";
    const apiJurisdictions = trfCreate.dataset.apiJurisdictions || "";
    const apiComplaints = trfCreate.dataset.apiComplaints || "";
    const apiDestinations = trfCreate.dataset.apiDestinations || "";
    const src = bindCourtCascade(trfCreate, "src", apiCommunes, apiJurisdictions);
    const dst = bindCourtCascade(trfCreate, "dst", apiCommunes, apiJurisdictions);
    const complaint = trfCreate.querySelector("#plainte_id");

    const refreshComplaints = async () => {
      if (!complaint || !apiComplaints) return;
      const courtId = src.juridiction?.value || "";
      const options = courtId
        ? await fetchOptions(`${apiComplaints}?juridiction_id=${encodeURIComponent(courtId)}`)
        : [];
      fillSelect(complaint, options, complaint.options[0]?.textContent || "");
    };

    const refreshDestinations = async () => {
      if (!apiDestinations || !src.juridiction) return;
      const courtId = src.juridiction.value || "";
      if (!courtId) {
        if (dst.niveau) dst.niveau.value = "";
        fillSelect(dst.juridiction, [], dst.juridiction?.options[0]?.textContent || "");
        return;
      }
      const params = new URLSearchParams({ juridiction_source_id: courtId });
      if (dst.province?.value) params.set("province_id", dst.province.value);
      if (dst.commune?.value) params.set("commune_id", dst.commune.value);
      const response = await fetch(`${apiDestinations}?${params.toString()}`, {
        headers: { Accept: "application/json" },
      });
      const data = await response.json();
      if (dst.niveau && data.next_niveau_id) {
        dst.niveau.value = String(data.next_niveau_id);
      }
      fillSelect(dst.juridiction, data.options || [], dst.juridiction?.options[0]?.textContent || "");
    };

    src.juridiction?.addEventListener("change", async () => {
      await refreshComplaints();
      await refreshDestinations();
    });
    dst.province?.addEventListener("change", refreshDestinations);
    dst.commune?.addEventListener("change", refreshDestinations);

    trfCreate.addEventListener("submit", (event) => {
      if (!trfCreate.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      trfCreate.classList.add("was-validated");
    });
  }

  // ---- Confirm before save/update (all Add & Edit forms) ----
  (() => {
    const i18n = window.JH_CONFIRM_I18N || {};
    const modalEl = document.getElementById("boConfirmSaveModal");
    if (!modalEl || typeof bootstrap === "undefined") {
      return;
    }

    const titleEl = document.getElementById("boConfirmSaveModalTitle");
    const leadEl = document.getElementById("boConfirmSaveModalLead");
    const bodyEl = document.getElementById("boConfirmSaveModalBody");
    const submitBtn = document.getElementById("boConfirmSaveSubmit");
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    let pendingForm = null;
    const previewUrls = [];

    const escapeHtml = (value) => String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");

    const revokePreviews = () => {
      while (previewUrls.length) {
        URL.revokeObjectURL(previewUrls.pop());
      }
    };

    const isUpdateForm = (form) => {
      if ((form.dataset.boConfirmMode || "").toLowerCase() === "update") {
        return true;
      }
      if ((form.dataset.boConfirmMode || "").toLowerCase() === "create") {
        return false;
      }
      const action = String(form.getAttribute("action") || window.location.pathname || "");
      if (/\/edit(\/|$)/i.test(action) || /\/update(\/|$)/i.test(action)) {
        return true;
      }
      // POST /resource/123 style updates
      if (/\/\d+(\/)?$/.test(action.replace(/\/+$/, ""))) {
        return true;
      }
      const submit = form.querySelector('[data-wizard-submit], button[type="submit"]');
      const text = (submit?.textContent || "").toLowerCase();
      return /update|edit|save changes|mettre à jour|enregistrer les modifications/.test(text);
    };

    const shouldSkipForm = (form) => {
      if (!form || form.method?.toLowerCase() === "get") {
        return true;
      }
      if (form.classList.contains("bo-filters") || form.hasAttribute("data-bo-skip-confirm")) {
        return true;
      }
      const id = form.id || "";
      if (/StatusForm$/i.test(id) || /status-form$/i.test(id)) {
        return true;
      }
      return false;
    };

    const shouldBindForm = (form) => {
      if (shouldSkipForm(form)) {
        return false;
      }
      if (form.classList.contains("bo-form") || form.hasAttribute("data-bo-confirm-save")) {
        return true;
      }
      // Modal create/edit forms
      if (form.classList.contains("needs-validation") && form.closest(".modal")) {
        return true;
      }
      return false;
    };

    const cleanLabel = (text) => String(text || "")
      .replace(/\*/g, "")
      .replace(/\s+/g, " ")
      .trim();

    const fieldLabel = (field) => {
      if (field.id) {
        const byFor = document.querySelector(`label[for="${CSS.escape(field.id)}"]`);
        if (byFor) {
          return cleanLabel(byFor.textContent);
        }
      }
      const wrapLabel = field.closest("label");
      if (wrapLabel) {
        const clone = wrapLabel.cloneNode(true);
        clone.querySelectorAll("input, select, textarea, .form-text, .invalid-feedback, code").forEach((n) => n.remove());
        const text = cleanLabel(clone.textContent);
        if (text) {
          return text;
        }
      }
      const group = field.closest(".mb-3, .col-12, [class*='col-']");
      const nearby = group?.querySelector(".form-label, legend, h2, h3, h6");
      if (nearby) {
        return cleanLabel(nearby.textContent);
      }
      return cleanLabel(field.getAttribute("aria-label") || field.name || field.id || "Field");
    };

    const sectionTitleFor = (field) => {
      const fieldset = field.closest("fieldset");
      const legend = fieldset?.querySelector("legend");
      if (legend) {
        return cleanLabel(legend.textContent) || (i18n.sectionDetails || "Details");
      }

      let node = field.closest(".col-12, .mb-3, .row, .bo-form-section, .bo-perm-assign") || field.parentElement;
      while (node && node !== field.form) {
        let prev = node.previousElementSibling;
        while (prev) {
          if (
            prev.matches?.("h2, h3, h6, .bo-form-section-title, legend")
            || prev.querySelector?.(".bo-form-section-title, h2, h3, h6")
          ) {
            const titleEl = prev.matches?.("h2, h3, h6, .bo-form-section-title, legend")
              ? prev
              : prev.querySelector(".bo-form-section-title, h2, h3, h6");
            const title = cleanLabel(titleEl?.textContent || "");
            if (title) {
              return title;
            }
          }
          prev = prev.previousElementSibling;
        }
        node = node.parentElement;
      }
      return i18n.sectionDetails || "Details";
    };

    const formatBytes = (size) => {
      if (!Number.isFinite(size) || size < 0) {
        return "";
      }
      if (size < 1024) {
        return `${size} B`;
      }
      if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
      }
      return `${(size / (1024 * 1024)).toFixed(1)} MB`;
    };

    const collectSections = (form) => {
      const sections = new Map();
      const ensure = (title) => {
        if (!sections.has(title)) {
          sections.set(title, []);
        }
        return sections.get(title);
      };

      const processedCheckboxNames = new Set();
      const fields = Array.from(form.querySelectorAll("input, select, textarea"));

      fields.forEach((field) => {
        if (field.disabled || field.type === "hidden" || field.type === "submit" || field.type === "button" || field.type === "reset") {
          return;
        }
        if (field.matches("[data-perm-search], [data-wizard-prev], [data-wizard-next]")) {
          return;
        }
        if (field.name && /csrf/i.test(field.name)) {
          return;
        }

        const section = sectionTitleFor(field);
        const label = fieldLabel(field);

        if (field.type === "checkbox") {
          const name = field.name || field.id || label;
          if (processedCheckboxNames.has(name)) {
            return;
          }
          processedCheckboxNames.add(name);
          const group = name
            ? Array.from(form.querySelectorAll(`input[type="checkbox"][name="${CSS.escape(name)}"]`))
            : [field];
          const checked = group.filter((el) => el.checked);
          if (!checked.length && group.length > 1) {
            ensure(section).push({
              label: label || (i18n.sectionDetails || "Details"),
              valueHtml: escapeHtml(i18n.emptyValue || "—"),
              wide: true,
            });
            return;
          }
          if (group.length === 1) {
            ensure(section).push({
              label,
              valueHtml: escapeHtml(field.checked ? (field.value || "Yes") : (i18n.emptyValue || "—")),
            });
            return;
          }
          const chips = checked.map((el) => {
            const item = el.closest("[data-perm-item], .form-check, label");
            const text = cleanLabel(
              item?.querySelector(".bo-perm-item-title, .form-check-label, span:not(.visually-hidden)")?.textContent
              || el.value
              || fieldLabel(el)
            );
            return `<span class="bo-confirm-chip">${escapeHtml(text)}</span>`;
          }).join("");
          const countTpl = i18n.checkedCount || "{0} selected";
          ensure(section).push({
            label,
            valueHtml: `<div class="bo-confirm-chips">${chips}</div><div class="small text-muted mt-1">${escapeHtml(countTpl.replace("{0}", String(checked.length)))}</div>`,
            wide: true,
          });
          return;
        }

        if (field.type === "radio") {
          if (!field.checked) {
            return;
          }
          const text = field.labels?.[0] ? cleanLabel(field.labels[0].textContent) : field.value;
          ensure(section).push({ label, valueHtml: escapeHtml(text || field.value || (i18n.emptyValue || "—")) });
          return;
        }

        if (field.type === "file") {
          const files = Array.from(field.files || []);
          if (!files.length) {
            ensure(section).push({
              label,
              valueHtml: escapeHtml(i18n.noFile || "No file selected"),
              wide: true,
            });
            return;
          }
          const cards = files.map((file) => {
            const isImage = /^image\//i.test(file.type);
            let preview = `<span class="bo-confirm-file-icon"><i class="bi bi-file-earmark" aria-hidden="true"></i></span>`;
            if (isImage) {
              const url = URL.createObjectURL(file);
              previewUrls.push(url);
              preview = `<img class="bo-confirm-file-preview" src="${url}" alt="">`;
            } else if (/pdf/i.test(file.type) || /\.pdf$/i.test(file.name)) {
              preview = `<span class="bo-confirm-file-icon"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span>`;
            }
            return `<div class="bo-confirm-file">${preview}<div class="bo-confirm-file-meta"><strong>${escapeHtml(file.name)}</strong><span>${escapeHtml(formatBytes(file.size))}</span></div></div>`;
          }).join("");
          ensure(section).push({
            label,
            valueHtml: `<div class="bo-confirm-files">${cards}</div>`,
            wide: true,
          });
          return;
        }

        if (field.tagName === "SELECT") {
          const selected = Array.from(field.selectedOptions || []).map((opt) => cleanLabel(opt.textContent)).filter(Boolean);
          ensure(section).push({
            label,
            valueHtml: escapeHtml(selected.join(", ") || (i18n.emptyValue || "—")),
            wide: selected.length > 2,
          });
          return;
        }

        const value = String(field.value || "").trim();
        ensure(section).push({
          label,
          valueHtml: escapeHtml(value || (i18n.emptyValue || "—")),
          wide: field.tagName === "TEXTAREA" || value.length > 80,
        });
      });

      return sections;
    };

    const renderReview = (form) => {
      revokePreviews();
      const sections = collectSections(form);
      const html = [];
      sections.forEach((items, title) => {
        if (!items.length) {
          return;
        }
        const rows = items.map((item) => `
          <div class="${item.wide ? "is-wide" : ""}">
            <dt>${escapeHtml(item.label)}</dt>
            <dd>${item.valueHtml}</dd>
          </div>
        `).join("");
        html.push(`
          <section class="bo-confirm-section">
            <h3>${escapeHtml(title)}</h3>
            <dl class="bo-confirm-list">${rows}</dl>
          </section>
        `);
      });
      bodyEl.innerHTML = html.length
        ? `<div class="bo-confirm-sections">${html.join("")}</div>`
        : `<p class="mb-0">${escapeHtml(i18n.emptyValue || "—")}</p>`;
    };

    const openConfirm = (form) => {
      pendingForm = form;
      const updating = isUpdateForm(form);
      titleEl.textContent = updating ? (i18n.updateTitle || "Confirm and update") : (i18n.saveTitle || "Confirm and save");
      leadEl.textContent = updating ? (i18n.updateLead || "") : (i18n.saveLead || "");
      submitBtn.textContent = updating ? (i18n.confirmUpdate || "Confirm and Update") : (i18n.confirmSave || "Confirm and Save");
      renderReview(form);
      modal.show();
    };

    submitBtn?.addEventListener("click", () => {
      if (!pendingForm) {
        return;
      }
      const form = pendingForm;
      pendingForm = null;
      form.dataset.boConfirmOk = "1";
      modal.hide();
      if (typeof form.requestSubmit === "function") {
        form.requestSubmit();
      } else {
        form.submit();
      }
    });

    modalEl.addEventListener("hidden.bs.modal", () => {
      revokePreviews();
      pendingForm = null;
    });

    const bindForm = (form) => {
      if (!shouldBindForm(form) || form.dataset.boConfirmBound === "1") {
        return;
      }
      form.dataset.boConfirmBound = "1";
      form.addEventListener("submit", (event) => {
        if (form.dataset.boConfirmOk === "1") {
          delete form.dataset.boConfirmOk;
          return;
        }
        if (event.defaultPrevented) {
          return;
        }

        // Client-side required validation before opening confirm.
        const invalid = Array.from(form.querySelectorAll("input, select, textarea")).find((el) => {
          if (el.disabled || el.type === "hidden" || el.type === "submit" || el.type === "button") {
            return false;
          }
          return typeof el.checkValidity === "function" && !el.checkValidity();
        });
        if (invalid) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add("was-validated");
          // Reveal wizard step containing the invalid field.
          const pane = invalid.closest("[data-wizard-step]");
          if (pane && pane.hidden) {
            const step = Number(pane.dataset.wizardStep || 0);
            const indicator = form.querySelector(`[data-wizard-indicator="${step}"]`);
            form.querySelectorAll("[data-wizard-step]").forEach((p) => {
              const active = p === pane;
              p.hidden = !active;
              p.classList.toggle("is-active", active);
            });
            form.querySelectorAll("[data-wizard-indicator]").forEach((ind) => {
              const idx = Number(ind.dataset.wizardIndicator);
              ind.classList.toggle("is-active", idx === step);
              ind.classList.toggle("is-complete", idx < step);
            });
            const prevBtn = form.querySelector("[data-wizard-prev]");
            const nextBtn = form.querySelector("[data-wizard-next]");
            const submitWizard = form.querySelector("[data-wizard-submit]");
            if (prevBtn) prevBtn.hidden = step <= 1;
            if (nextBtn) nextBtn.hidden = true;
            if (submitWizard) submitWizard.hidden = false;
          }
          invalid.focus?.();
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        openConfirm(form);
      });
    };

    document.querySelectorAll("form").forEach(bindForm);

    // Forms injected later (rare) — observe panel content swaps.
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (!(node instanceof HTMLElement)) {
            return;
          }
          if (node.matches?.("form")) {
            bindForm(node);
          }
          node.querySelectorAll?.("form").forEach(bindForm);
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  })();
})();
