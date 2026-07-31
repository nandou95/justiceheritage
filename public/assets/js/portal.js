(() => {
  const toggle = document.querySelector("[data-portal-toggle]");
  const nav = document.querySelector("[data-portal-nav]");
  const backdrop = document.querySelector("[data-portal-backdrop]");
  const closeBtn = document.querySelector("[data-portal-close]");
  const MOBILE_BREAKPOINT = 992;

  const isMobileNav = () => window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;

  const openNav = () => {
    if (!nav) return;
    nav.classList.add("is-open");
    document.body.classList.add("jh-portal-nav-open");
    toggle?.setAttribute("aria-expanded", "true");
    if (backdrop) backdrop.hidden = false;
  };

  const closeNav = () => {
    if (!nav) return;
    nav.classList.remove("is-open");
    document.body.classList.remove("jh-portal-nav-open");
    toggle?.setAttribute("aria-expanded", "false");
    if (backdrop) backdrop.hidden = true;
  };

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      if (nav.classList.contains("is-open")) {
        closeNav();
      } else {
        openNav();
      }
    });
  }

  closeBtn?.addEventListener("click", closeNav);
  backdrop?.addEventListener("click", closeNav);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && nav?.classList.contains("is-open")) {
      closeNav();
    }
  });

  nav?.querySelectorAll(".jh-portal-nav a").forEach((link) => {
    link.addEventListener("click", () => {
      if (isMobileNav()) {
        closeNav();
      }
    });
  });

  window.addEventListener("resize", () => {
    if (!isMobileNav()) {
      closeNav();
    }
  });

  const initTooltips = (root = document) => {
    if (typeof bootstrap === "undefined" || !bootstrap.Tooltip) {
      return;
    }
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      bootstrap.Tooltip.getOrCreateInstance(el);
    });
  };

  initTooltips();

  const dtLang = window.JH_DT?.language || {};

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
    const paging = table.dataset.paging !== "false";
    const searching = table.dataset.searching !== "false";
    const info = table.dataset.info !== "false";
    const lengthChange = table.dataset.lengthChange !== "false";

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

    const language = { ...dtLang };
    if (table.dataset.emptyTable) {
      language.emptyTable = table.dataset.emptyTable;
    }
    if (table.dataset.zeroRecords) {
      language.zeroRecords = table.dataset.zeroRecords;
    }

    // eslint-disable-next-line no-new
    const dt = new DataTable(table, {
      language,
      pageLength,
      paging,
      searching,
      info,
      lengthChange,
      autoWidth: false,
      order: orderCol >= 0 ? [[orderCol, orderDir]] : [],
      columnDefs,
    });

    dt.on("draw", () => initTooltips(table));
  });

  document.querySelectorAll("[data-dt-select]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = document.querySelector(btn.getAttribute("data-dt-select"));
      const value = btn.getAttribute("data-value");
      if (target && value != null) {
        target.value = value;
        target.dispatchEvent(new Event("change", { bubbles: true }));
        target.scrollIntoView({ behavior: "smooth", block: "center" });
        target.focus();
      }
    });
  });

  const complaintForm = document.querySelector("[data-new-complaint]");
  if (complaintForm) {
    const steps = Array.from(document.querySelectorAll("[data-wizard-steps] [data-step-nav]"));
    const panels = Array.from(complaintForm.querySelectorAll("[data-step-panel]"));
    const btnPrev = complaintForm.querySelector("[data-wizard-prev]");
    const btnNext = complaintForm.querySelector("[data-wizard-next]");
    const btnSubmit = complaintForm.querySelector("[data-wizard-submit]");
    const btnCancel = complaintForm.querySelector("[data-wizard-cancel]");
    const errorEl = complaintForm.querySelector("[data-wizard-error]");
    const stepError = complaintForm.dataset.stepError || "";
    const docLabel = complaintForm.dataset.docLabel || "Document";
    let currentStep = 1;
    const totalSteps = panels.length;

    let locations = {};
    try {
      locations = JSON.parse(complaintForm.dataset.locations || "{}");
    } catch (_err) {
      locations = {};
    }

    const provinceEl = complaintForm.querySelector('[data-loc="province"]');
    const communeEl = complaintForm.querySelector('[data-loc="commune"]');
    const zoneEl = complaintForm.querySelector('[data-loc="zone"]');
    const collineEl = complaintForm.querySelector('[data-loc="colline"]');

    const fillSelect = (select, options, enabled) => {
      if (!select) return;
      const placeholder = select.options[0]?.textContent || "—";
      select.innerHTML = "";
      const empty = document.createElement("option");
      empty.value = "";
      empty.textContent = placeholder;
      select.append(empty);
      (options || []).forEach((value) => {
        const opt = document.createElement("option");
        opt.value = value;
        opt.textContent = value;
        select.append(opt);
      });
      select.disabled = !enabled;
      select.value = "";
    };

    const onProvinceChange = () => {
      const communes = Object.keys(locations[provinceEl.value] || {});
      fillSelect(communeEl, communes, Boolean(provinceEl.value));
      fillSelect(zoneEl, [], false);
      fillSelect(collineEl, [], false);
    };

    const onCommuneChange = () => {
      const zones = Object.keys((locations[provinceEl.value] || {})[communeEl.value] || {});
      fillSelect(zoneEl, zones, Boolean(communeEl.value));
      fillSelect(collineEl, [], false);
    };

    const onZoneChange = () => {
      const hills =
        ((locations[provinceEl.value] || {})[communeEl.value] || {})[zoneEl.value] || [];
      fillSelect(collineEl, hills, Boolean(zoneEl.value));
    };

    provinceEl?.addEventListener("change", onProvinceChange);
    communeEl?.addEventListener("change", onCommuneChange);
    zoneEl?.addEventListener("change", onZoneChange);

    const showError = (msg) => {
      if (!errorEl) return;
      errorEl.hidden = !msg;
      errorEl.textContent = msg || "";
    };

    const setStep = (n) => {
      currentStep = n;
      panels.forEach((panel) => {
        const active = Number(panel.dataset.stepPanel) === n;
        panel.hidden = !active;
        panel.classList.toggle("is-active", active);
        panel.classList.toggle("is-focused", active);
      });
      steps.forEach((step) => {
        const stepNum = Number(step.dataset.stepNav);
        step.classList.toggle("is-active", stepNum === n);
        step.classList.toggle("is-done", stepNum < n);
      });
      if (btnPrev) btnPrev.hidden = n === 1;
      if (btnCancel) btnCancel.hidden = n !== 1;
      if (btnNext) btnNext.hidden = n === totalSteps;
      if (btnSubmit) btnSubmit.hidden = n !== totalSteps;
      showError("");
      panels.find((p) => Number(p.dataset.stepPanel) === n)?.scrollIntoView({ behavior: "smooth", block: "start" });
    };

    const validateStep = (n) => {
      const panel = panels.find((p) => Number(p.dataset.stepPanel) === n);
      if (!panel) return true;
      const fields = Array.from(panel.querySelectorAll("input, select, textarea")).filter(
        (el) => el.willValidate !== false && !el.disabled
      );
      for (const field of fields) {
        if (!field.checkValidity()) {
          field.reportValidity();
          return false;
        }
      }
      return true;
    };

    btnNext?.addEventListener("click", () => {
      if (!validateStep(currentStep)) {
        showError(stepError);
        return;
      }
      if (currentStep < totalSteps) setStep(currentStep + 1);
    });

    btnPrev?.addEventListener("click", () => {
      if (currentStep > 1) setStep(currentStep - 1);
    });

    steps.forEach((step) => {
      step.style.cursor = "pointer";
      step.addEventListener("click", () => {
        const target = Number(step.dataset.stepNav);
        if (target < currentStep) {
          setStep(target);
          return;
        }
        for (let i = currentStep; i < target; i += 1) {
          if (!validateStep(i)) {
            setStep(i);
            showError(stepError);
            return;
          }
        }
        setStep(target);
      });
    });

    complaintForm.addEventListener("submit", (e) => {
      for (let i = 1; i <= totalSteps; i += 1) {
        if (!validateStep(i)) {
          e.preventDefault();
          setStep(i);
          showError(stepError);
          return;
        }
      }
    });

    const rowsHost = complaintForm.querySelector("[data-doc-rows]");
    const rowTemplate = complaintForm.querySelector("[data-doc-row-template]");
    const addDocBtn = complaintForm.querySelector("[data-doc-add]");

    const renumberDocs = () => {
      const rows = Array.from(rowsHost?.querySelectorAll("[data-doc-row]") || []);
      rows.forEach((row, index) => {
        const label = row.querySelector("[data-doc-label]");
        if (label) label.textContent = `${docLabel} ${index + 1}`;
        const removeBtn = row.querySelector("[data-doc-remove]");
        if (removeBtn) removeBtn.hidden = rows.length <= 1;
      });
    };

    const addDocRow = () => {
      if (!rowsHost || !rowTemplate) return;
      const node = rowTemplate.content.cloneNode(true);
      const row = node.querySelector("[data-doc-row]");
      row?.querySelector("[data-doc-remove]")?.addEventListener("click", () => {
        const count = rowsHost.querySelectorAll("[data-doc-row]").length;
        if (count <= 1) return;
        row.remove();
        renumberDocs();
      });
      rowsHost.append(node);
      renumberDocs();
    };

    addDocBtn?.addEventListener("click", addDocRow);
    addDocRow();
    setStep(1);
  }

  const appealPage = document.querySelector("[data-appeal-page]");
  if (appealPage) {
    let casesMap = {};
    try {
      casesMap = JSON.parse(appealPage.getAttribute("data-cases") || "{}");
    } catch (e) {
      casesMap = {};
    }

    const select = appealPage.querySelector("[data-appeal-select]");
    const preview = appealPage.querySelector("[data-appeal-preview]");
    const emptyState = appealPage.querySelector("[data-appeal-empty]");
    const selectedState = appealPage.querySelector("[data-appeal-selected]");
    const grounds = appealPage.querySelector("[data-appeal-grounds]");
    const countEl = appealPage.querySelector("[data-appeal-count]");
    const steps = Array.from(document.querySelectorAll(".jh-appeal-steps li"));
    const daysTemplate = appealPage.getAttribute("data-days-template") || "{0}";
    const noDeadline = appealPage.getAttribute("data-no-deadline") || "—";

    const setText = (sel, value) => {
      const el = appealPage.querySelector(sel);
      if (el) el.textContent = value || "—";
    };

    const setPreview = (caseId) => {
      const data = casesMap[caseId];
      appealPage.querySelectorAll("[data-case-row]").forEach((row) => {
        row.classList.toggle("is-appeal-selected", row.getAttribute("data-case-row") === caseId);
      });

      if (!data) {
        preview?.classList.remove("is-filled");
        if (emptyState) emptyState.hidden = false;
        if (selectedState) selectedState.hidden = true;
        steps.forEach((step, i) => step.classList.toggle("is-active", i === 0));
        return;
      }

      preview?.classList.add("is-filled");
      if (emptyState) emptyState.hidden = true;
      if (selectedState) selectedState.hidden = false;

      setText("[data-preview-id]", data.id);
      setText("[data-preview-subject]", data.subject);
      setText("[data-preview-court]", data.court_label);
      setText("[data-preview-location]", data.location);
      setText("[data-preview-status]", data.status_label);

      if (data.appeal_days != null && data.appeal_days !== "") {
        setText("[data-preview-deadline]", daysTemplate.replace("{0}", String(data.appeal_days)));
      } else {
        setText("[data-preview-deadline]", noDeadline);
      }

      const filledGrounds = Boolean(grounds?.value.trim());
      steps.forEach((step, i) => step.classList.toggle("is-active", i <= (filledGrounds ? 2 : 1)));
    };

    select?.addEventListener("change", () => {
      setPreview(select.value);
      if (select.value) {
        grounds?.focus();
      }
    });

    grounds?.addEventListener("input", () => {
      if (countEl) countEl.textContent = String(grounds.value.length);
      if (!select?.value) {
        steps.forEach((step, i) => step.classList.toggle("is-active", i === 0));
        return;
      }
      const filledGrounds = grounds.value.trim().length > 0;
      steps.forEach((step, i) => step.classList.toggle("is-active", i <= (filledGrounds ? 2 : 1)));
    });

    if (select?.value) {
      setPreview(select.value);
    }
  }
})();
