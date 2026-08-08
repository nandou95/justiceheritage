(() => {
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

  const cmpForm = document.querySelector("[data-bo-cmp-form]");
  if (!cmpForm) {
    return;
  }

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
  const isEdit = cmpForm.dataset.isEdit === "1";
  const msgRequired = cmpForm.dataset.msgRequired || "";
  const requiredBadge = cmpForm.dataset.docRequiredLabel || "Required";
  const optionalBadge = cmpForm.dataset.docOptionalLabel || "Optional";
  const acceptHint = cmpForm.dataset.docAcceptHint || "";
  const i18n = window.JH_CMP_WIZARD_I18N || {};

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;");

  const initMultiSelect = (root) => {
    if (!root || root.dataset.boMultiReady === "1") {
      return;
    }
    const select = root.querySelector("[data-multi-source]");
    const chips = root.querySelector("[data-multi-chips]");
    const search = root.querySelector("[data-multi-search]");
    const dropdown = root.querySelector("[data-multi-dropdown]");
    const box = root.querySelector(".bo-multi-select-box");
    if (!select || !chips || !search || !dropdown || !box) {
      return;
    }
    root.dataset.boMultiReady = "1";

    const syncValidity = () => {
      const msg = select.dataset.msgRequired || msgRequired;
      if (select.required && select.selectedOptions.length === 0) {
        select.setCustomValidity(msg);
        root.classList.add("is-invalid");
        select.classList.add("is-invalid");
      } else {
        select.setCustomValidity("");
        root.classList.remove("is-invalid");
        select.classList.remove("is-invalid");
      }
    };

    const renderChips = () => {
      chips.innerHTML = Array.from(select.selectedOptions)
        .map(
          (opt) => `<span class="bo-multi-chip" data-value="${escapeHtml(opt.value)}">
              <span>${escapeHtml(opt.textContent)}</span>
              <button type="button" aria-label="Remove">&times;</button>
            </span>`
        )
        .join("");
      syncValidity();
    };

    const renderDropdown = () => {
      const query = search.value.trim().toLowerCase();
      const options = Array.from(select.options).filter((opt) => {
        if (!query) {
          return true;
        }
        return String(opt.textContent || "").toLowerCase().includes(query);
      });
      dropdown.innerHTML =
        options
          .map((opt) => {
            const selected = opt.selected;
            return `<button type="button" class="bo-multi-option${selected ? " is-selected" : ""}" role="option" data-value="${escapeHtml(opt.value)}" aria-selected="${selected ? "true" : "false"}">
              <span class="bo-multi-option-check">${selected ? "✓" : ""}</span>
              <span>${escapeHtml(opt.textContent)}</span>
            </button>`;
          })
          .join("") || `<p class="text-muted small mb-0 px-2 py-1">—</p>`;
    };

    const openDropdown = () => {
      box.classList.add("is-open");
      dropdown.hidden = false;
      renderDropdown();
    };

    const closeDropdown = () => {
      box.classList.remove("is-open");
      dropdown.hidden = true;
    };

    chips.addEventListener("click", (event) => {
      const btn = event.target.closest("button");
      const chip = event.target.closest(".bo-multi-chip");
      if (!btn || !chip) {
        return;
      }
      const value = chip.getAttribute("data-value");
      const option = Array.from(select.options).find((opt) => opt.value === value);
      if (option) {
        option.selected = false;
        renderChips();
        renderDropdown();
        select.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });

    dropdown.addEventListener("click", (event) => {
      const optionBtn = event.target.closest(".bo-multi-option");
      if (!optionBtn) {
        return;
      }
      const value = optionBtn.getAttribute("data-value");
      const option = Array.from(select.options).find((opt) => opt.value === value);
      if (!option) {
        return;
      }
      option.selected = !option.selected;
      renderChips();
      renderDropdown();
      select.dispatchEvent(new Event("change", { bubbles: true }));
    });

    search.addEventListener("focus", openDropdown);
    search.addEventListener("input", () => {
      openDropdown();
      renderDropdown();
    });
    search.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        closeDropdown();
        search.blur();
      }
    });

    document.addEventListener("click", (event) => {
      if (!root.contains(event.target)) {
        closeDropdown();
      }
    });

    renderChips();
    syncValidity();
    root._boMultiSync = syncValidity;
    root._boMultiLabels = () =>
      Array.from(select.selectedOptions)
        .map((opt) => opt.textContent.trim())
        .filter(Boolean);
  };

  cmpForm.querySelectorAll("[data-bo-multi-select]").forEach(initMultiSelect);

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
      docsBox.innerHTML = `<p class="text-muted mb-0">${escapeHtml(docsBox.dataset.empty || "")}</p>`;
      return;
    }
    const response = await fetch(`${apiDocs}?niveau_juridiction_id=${encodeURIComponent(niveau.value)}`, {
      headers: { Accept: "application/json" },
    });
    const data = await response.json();
    const types = data.types || [];
    docsBox.innerHTML =
      types
        .map((t) => {
          const mandatory =
            t.is_obligatoire === true ||
            t.is_obligatoire === "t" ||
            t.is_obligatoire === 1 ||
            t.is_obligatoire === "1";
          const required = mandatory && !isEdit;
          const label = t.libelle_type_document || t.code_type_document || "";
          return `<div class="bo-doc-upload-card">
              <div class="bo-doc-upload-head">
                <strong>${escapeHtml(label)}${required ? " *" : ""}</strong>
                <span class="bo-doc-upload-badge ${required ? "is-required" : "is-optional"}">${escapeHtml(required ? requiredBadge : optionalBadge)}</span>
              </div>
              <input class="form-control" type="file" name="documents[${t.type_document_id}][]"
                     accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                     multiple ${required ? "required" : ""}>
              <p class="form-text mb-0">${escapeHtml(acceptHint)}</p>
              <div class="invalid-feedback">${escapeHtml(msgRequired)}</div>
            </div>`;
        })
        .join("") || `<p class="text-muted mb-0">${escapeHtml(docsBox.dataset.empty || "—")}</p>`;
  };

  province?.addEventListener("change", async () => {
    fillSelect(commune, [], commune?.options[0]?.textContent || "");
    if (juridiction) {
      fillSelect(juridiction, [], juridiction.options[0]?.textContent || "");
    }
    fillSelect(
      commune,
      province.value ? await fetchOptions(`${apiCommunes}?province_id=${encodeURIComponent(province.value)}`) : [],
      commune?.options[0]?.textContent || ""
    );
    await refreshCourts();
  });
  commune?.addEventListener("change", refreshCourts);
  niveau?.addEventListener("change", async () => {
    await refreshCourts();
    await refreshDocs();
  });
  if (niveau?.value) {
    refreshDocs();
  }

  const renumberParcels = () => {
    cmpForm.querySelectorAll("[data-parcel-row]").forEach((row, index) => {
      const label = row.querySelector("[data-parcel-index]");
      if (label) {
        label.textContent = String(index + 1);
      }
      row.querySelectorAll("textarea, input, select").forEach((el) => {
        if (el.name) {
          el.name = el.name.replace(/parcels\[\d+]/, `parcels[${index}]`);
        }
      });
    });
  };

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
      const container = cmpForm.querySelector("[data-parcel-rows]");
      if (container && container.querySelectorAll("[data-parcel-row]").length > 1) {
        row.remove();
        renumberParcels();
      }
    });
  };

  cmpForm.querySelectorAll("[data-parcel-row]").forEach(bindParcelRow);
  cmpForm.querySelector("[data-parcel-add]")?.addEventListener("click", () => {
    const container = cmpForm.querySelector("[data-parcel-rows]");
    const first = container?.querySelector("[data-parcel-row]");
    if (!container || !first) return;
    const clone = first.cloneNode(true);
    clone.querySelectorAll("textarea, input, select").forEach((el) => {
      if (el.tagName === "TEXTAREA" || el.tagName === "INPUT") el.value = "";
      if (el.tagName === "SELECT" && !el.dataset.parcelProvince) {
        el.innerHTML = el.options[0] ? `<option value="">${el.options[0].textContent}</option>` : "";
      }
      if (el.dataset.parcelProvince) {
        el.selectedIndex = 0;
      }
    });
    container.appendChild(clone);
    renumberParcels();
    bindParcelRow(clone);
  });

  const wizardRoot = cmpForm.querySelector("[data-wizard]");
  if (cmpForm.hasAttribute("data-bo-cmp-wizard") && wizardRoot) {
    const panes = Array.from(cmpForm.querySelectorAll("[data-wizard-step]"));
    const indicators = Array.from(cmpForm.querySelectorAll("[data-wizard-indicator]"));
    const statusEl = cmpForm.querySelector("[data-wizard-status]");
    const prevBtn = cmpForm.querySelector("[data-wizard-prev]");
    const nextBtn = cmpForm.querySelector("[data-wizard-next]");
    const submitBtn = cmpForm.querySelector("[data-wizard-submit]");
    const totalSteps = panes.length || 1;
    let currentStep = 1;
    const progressTpl = i18n.progress || "Step {0} of {1}";

    const formatProgress = (step) =>
      String(progressTpl)
        .replaceAll("{0}", String(step))
        .replaceAll("{1}", String(totalSteps));

    const paneFields = (pane) =>
      Array.from(pane.querySelectorAll("input, select, textarea")).filter(
        (el) =>
          !el.disabled &&
          el.type !== "hidden" &&
          el.type !== "submit" &&
          el.type !== "button" &&
          el.type !== "search" &&
          !el.classList.contains("bo-multi-select-search")
      );

    const syncFeedback = (field) => {
      const feedback =
        field.closest(".bo-doc-upload-card, .bo-multi-select, .col-12, .col-md-6, .col-md-3, .mb-3")?.querySelector(".invalid-feedback") ||
        field.parentElement?.querySelector(".invalid-feedback");
      if (!feedback) {
        return;
      }
      if (field.validity.valid) {
        feedback.textContent = msgRequired;
        return;
      }
      feedback.textContent = field.validationMessage || msgRequired;
    };

    const applyFieldRules = (field) => {
      if (typeof field.value === "string" && field.type !== "date" && field.tagName !== "SELECT" && field.type !== "file") {
        field.value = field.value.trim();
      }
      if (field.multiple && field.tagName === "SELECT") {
        const root = field.closest("[data-bo-multi-select]");
        root?._boMultiSync?.();
        return;
      }
      field.setCustomValidity("");
      if (field.required && field.type !== "file" && field.tagName !== "SELECT" && !String(field.value || "").trim()) {
        field.setCustomValidity(msgRequired);
      }
      if (field.required && field.type === "file" && !(field.files && field.files.length)) {
        field.setCustomValidity(msgRequired);
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
          field.closest("[data-bo-multi-select]")?.classList.add("is-invalid");
        } else {
          field.classList.remove("is-invalid");
          field.closest("[data-bo-multi-select]")?.classList.remove("is-invalid");
        }
        syncFeedback(field);
      });
      cmpForm.classList.add("was-validated");
      if (!valid) {
        const firstInvalid =
          pane.querySelector(".bo-multi-select.is-invalid .bo-multi-select-search") ||
          pane.querySelector(":invalid, .is-invalid");
        firstInvalid?.focus?.();
      }
      return valid;
    };

    const selectLabel = (id) => {
      const el = cmpForm.querySelector(`#${CSS.escape(id)}`);
      if (!el) {
        return "—";
      }
      if (el.multiple) {
        const labels = Array.from(el.selectedOptions)
          .map((opt) => opt.textContent.trim())
          .filter(Boolean);
        return labels.length ? labels.join(", ") : i18n.noneSelected || "—";
      }
      const opt = el.options?.[el.selectedIndex];
      return opt && opt.value ? opt.textContent.trim() : "—";
    };

    const refreshReview = () => {
      const setReview = (key, value) => {
        const target = cmpForm.querySelector(`[data-review="${key}"]`);
        if (target) {
          target.textContent = value || "—";
        }
      };
      setReview("objet", cmpForm.querySelector("#objet")?.value?.trim() || "—");
      setReview("description", cmpForm.querySelector("#description")?.value?.trim() || "—");
      setReview("niveau_juridiction_id", selectLabel("niveau_juridiction_id"));
      setReview("province_id", selectLabel("province_id"));
      setReview("commune_id", selectLabel("commune_id"));
      setReview("juridiction_id", selectLabel("juridiction_id"));
      setReview("complainant_ids", selectLabel("complainant_ids"));
      setReview("defendant_ids", selectLabel("defendant_ids"));
      if (cmpForm.querySelector("#witness_ids")) {
        setReview("witness_ids", selectLabel("witness_ids"));
      }
      const parcelCount = cmpForm.querySelectorAll("[data-parcel-row]").length;
      setReview(
        "parcels_summary",
        String(i18n.parcelCount || "{0} parcel(s)").replaceAll("{0}", String(parcelCount))
      );
      let fileCount = 0;
      cmpForm.querySelectorAll('[data-doc-types] input[type="file"]').forEach((input) => {
        fileCount += input.files ? input.files.length : 0;
      });
      setReview(
        "documents_summary",
        fileCount > 0
          ? String(i18n.docsCount || "{0} file(s)").replaceAll("{0}", String(fileCount))
          : i18n.docsNone || "—"
      );
    };

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
      if (currentStep === totalSteps) {
        refreshReview();
      }
    };

    prevBtn?.addEventListener("click", () => showStep(currentStep - 1));
    nextBtn?.addEventListener("click", () => {
      if (!validateStep(currentStep)) {
        return;
      }
      showStep(currentStep + 1);
    });

    cmpForm.querySelectorAll("[data-wizard-edit]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const step = Number(btn.getAttribute("data-wizard-edit") || 1);
        showStep(step);
      });
    });

    indicators.forEach((indicator) => {
      indicator.addEventListener("click", () => {
        const target = Number(indicator.dataset.wizardIndicator || 1);
        if (target === currentStep) {
          return;
        }
        if (target < currentStep) {
          showStep(target);
          return;
        }
        for (let step = currentStep; step < target; step += 1) {
          if (!validateStep(step)) {
            showStep(step);
            return;
          }
        }
        showStep(target);
      });
    });

    cmpForm.addEventListener("submit", (event) => {
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
      cmpForm.classList.add("was-validated");
    });

    showStep(1);
  } else {
    cmpForm.addEventListener("submit", (event) => {
      if (!cmpForm.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      cmpForm.classList.add("was-validated");
    });
  }
})();
