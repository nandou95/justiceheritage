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

  document.querySelectorAll("table.jh-datatable").forEach((table) => {
    if (typeof DataTable === "undefined") {
      return;
    }
    if (typeof DataTable.isDataTable === "function" && DataTable.isDataTable(table)) {
      return;
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
  });

  const bindTableSearch = (inputId, tableId) => {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) {
      return;
    }
    const dt = dataTables.get(tableId) || dataTables.get(table);
    if (!dt) {
      return;
    }
    input.addEventListener("input", () => {
      dt.search(input.value).draw();
    });
  };

  bindTableSearch("users-table-search", "users-table");
  bindTableSearch("permissions-table-search", "permissions-table");
  bindTableSearch("profiles-table-search", "profiles-table");
  bindTableSearch("profile-permissions-search", "profile-permissions-table");
  bindTableSearch("people-table-search", "people-table");
  bindTableSearch("people-complaints-search", "people-complaints-table");
  bindTableSearch("cs-table-search", "cs-table");
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
    const niveau = userFilters.querySelector('[data-filter="niveau"]');
    const juridiction = userFilters.querySelector('[data-filter="juridiction"]');
    const apiCommunes = userFilters.dataset.apiCommunes || "";
    const apiJurisdictions = userFilters.dataset.apiJurisdictions || "";

    const refreshJurisdictions = async () => {
      if (!juridiction || !apiJurisdictions) {
        return;
      }
      const params = new URLSearchParams();
      if (province?.value) {
        params.set("province_id", province.value);
      }
      if (commune?.value) {
        params.set("commune_id", commune.value);
      }
      if (niveau?.value) {
        params.set("niveau_juridiction_id", niveau.value);
      }
      const qs = params.toString();
      const options = await fetchOptions(`${apiJurisdictions}${qs ? `?${qs}` : ""}`);
      fillSelect(juridiction, options, juridiction.options[0]?.textContent || "");
    };

    province?.addEventListener("change", async () => {
      if (commune && apiCommunes) {
        const options = province.value
          ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`)
          : [];
        fillSelect(commune, options, commune.options[0]?.textContent || "");
      }
      await refreshJurisdictions();
    });

    commune?.addEventListener("change", () => {
      refreshJurisdictions();
    });

    niveau?.addEventListener("change", () => {
      refreshJurisdictions();
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

    userForm.addEventListener("submit", (event) => {
      if (!userForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      userForm.classList.add("was-validated");
    });
  }

  const userStatusModalEl = document.getElementById("userStatusModal");
  if (userStatusModalEl && window.JH_USERS_I18N) {
    const form = document.getElementById("userStatusForm");
    const title = document.getElementById("userStatusModalTitle");
    const message = document.getElementById("userStatusModalMessage");
    const confirmBtn = document.getElementById("userStatusModalConfirm");
    const modal = bootstrap.Modal.getOrCreateInstance(userStatusModalEl);
    const i18n = window.JH_USERS_I18N;

    document.querySelectorAll("[data-bo-toggle-user]").forEach((btn) => {
      btn.addEventListener("click", () => {
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
    });
  }

  const permFormModalEl = document.getElementById("permissionFormModal");
  const permStatusModalEl = document.getElementById("permissionStatusModal");
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
    };

    document.querySelectorAll("[data-bo-perm-create]").forEach((btn) => {
      btn.addEventListener("click", openCreate);
    });

    document.querySelectorAll("[data-bo-perm-edit]").forEach((btn) => {
      btn.addEventListener("click", () => {
        form.action = i18n.updateUrl.replace("__ID__", btn.getAttribute("data-id"));
        form.classList.remove("was-validated");
        title.textContent = i18n.editTitle;
        submitBtn.textContent = i18n.saveEdit;
        description.value = btn.getAttribute("data-description") || "";
        route.value = btn.getAttribute("data-route") || "";
        formModal.show();
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

  if (permStatusModalEl && window.JH_PERM_I18N) {
    const i18n = window.JH_PERM_I18N;
    const form = document.getElementById("permissionStatusForm");
    const title = document.getElementById("permissionStatusModalTitle");
    const message = document.getElementById("permissionStatusModalMessage");
    const confirmBtn = document.getElementById("permissionStatusModalConfirm");
    const modal = bootstrap.Modal.getOrCreateInstance(permStatusModalEl);

    document.querySelectorAll("[data-bo-toggle-perm]").forEach((btn) => {
      btn.addEventListener("click", () => {
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

    document.querySelectorAll("[data-bo-toggle-profile]").forEach((btn) => {
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
    const selectAll = permAssign.querySelector("[data-perm-select-all]");
    const countEl = permAssign.querySelector("[data-perm-selected-count]");
    const items = Array.from(permAssign.querySelectorAll("[data-perm-item]"));

    const visibleCheckboxes = () =>
      items
        .filter((item) => !item.classList.contains("is-hidden"))
        .map((item) => item.querySelector("[data-perm-checkbox]"))
        .filter(Boolean);

    const refreshSelectionUI = () => {
      items.forEach((item) => {
        const checkbox = item.querySelector("[data-perm-checkbox]");
        item.classList.toggle("is-selected", Boolean(checkbox?.checked));
      });

      const checkedCount = items.filter((item) => item.querySelector("[data-perm-checkbox]")?.checked).length;
      if (countEl) {
        countEl.textContent = String(checkedCount);
      }

      const visible = visibleCheckboxes();
      if (selectAll) {
        selectAll.checked = visible.length > 0 && visible.every((cb) => cb.checked);
        selectAll.indeterminate = visible.some((cb) => cb.checked) && !selectAll.checked;
      }
    };

    searchInput?.addEventListener("input", () => {
      const query = (searchInput.value || "").trim().toLowerCase();
      items.forEach((item) => {
        const haystack = item.getAttribute("data-search") || "";
        item.classList.toggle("is-hidden", query !== "" && !haystack.includes(query));
      });
      refreshSelectionUI();
    });

    selectAll?.addEventListener("change", () => {
      visibleCheckboxes().forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
      refreshSelectionUI();
    });

    permAssign.querySelectorAll("[data-perm-group-toggle]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const group = btn.closest("[data-perm-group]");
        if (!group) {
          return;
        }
        const checkboxes = Array.from(group.querySelectorAll("[data-perm-item]:not(.is-hidden) [data-perm-checkbox]"));
        const allChecked = checkboxes.length > 0 && checkboxes.every((cb) => cb.checked);
        checkboxes.forEach((checkbox) => {
          checkbox.checked = !allChecked;
        });
        refreshSelectionUI();
      });
    });

    items.forEach((item) => {
      const checkbox = item.querySelector("[data-perm-checkbox]");
      checkbox?.addEventListener("change", refreshSelectionUI);
    });

    refreshSelectionUI();
  }

  bindTableSearch("cj-table-search", "cj-table");
  bindTableSearch("cjc-table-search", "cjc-table");
  bindTableSearch("jl-table-search", "jl-table");
  bindTableSearch("jlc-table-search", "jlc-table");

  // Court jurisdiction list filters
  document.querySelectorAll("[data-bo-cj-filters], [data-bo-cjc-filters]").forEach((form) => {
    const province = form.querySelector('[data-filter="province"]');
    const commune = form.querySelector('[data-filter="commune"]');
    const apiCommunes = form.dataset.apiCommunes || "";
    province?.addEventListener("change", async () => {
      if (!commune || !apiCommunes) {
        return;
      }
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
    const niveauSuivant = document.getElementById("niveau_juridiction_suivant_id");
    const etapeSuivant = document.getElementById("etape_plainte_suivant_id");
    const urlRoute = document.getElementById("url_route");
    const apiStages = cscForm.dataset.apiStages || i18n.stagesUrl || "";

    const loadStages = async (niveauEl, etapeEl, selected) => {
      const options = niveauEl?.value
        ? await fetchOptions(`${apiStages}?niveau_juridiction_id=${encodeURIComponent(niveauEl.value)}`)
        : [];
      fillSelect(etapeEl, options, etapeEl?.options[0]?.textContent || "");
      if (selected) etapeEl.value = String(selected);
    };

    niveauActuel?.addEventListener("change", () => loadStages(niveauActuel, etapeActuel));
    niveauSuivant?.addEventListener("change", () => loadStages(niveauSuivant, etapeSuivant));

    document.querySelectorAll("[data-bo-csc-create]").forEach((btn) => {
      btn.addEventListener("click", () => {
        cscForm.action = i18n.storeUrl;
        cscForm.reset();
        cscForm.classList.remove("was-validated");
        fillSelect(etapeActuel, [], etapeActuel?.options[0]?.textContent || "");
        fillSelect(etapeSuivant, [], etapeSuivant?.options[0]?.textContent || "");
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
})();
