(() => {
  const toggle = document.querySelector("[data-portal-toggle]");
  const nav = document.querySelector("[data-portal-nav]");

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

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

    // eslint-disable-next-line no-new
    new DataTable(table, {
      language: dtLang,
      pageLength,
      paging,
      searching,
      info,
      lengthChange,
      autoWidth: false,
      order: orderCol >= 0 ? [[orderCol, orderDir]] : [],
      columnDefs,
    });
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
})();
