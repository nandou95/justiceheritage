(() => {
  const toggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");
  const header = document.querySelector("[data-header]");

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  if (header) {
    const onScroll = () => {
      header.classList.toggle("is-scrolled", window.scrollY > 12);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  const revealItems = document.querySelectorAll(".jh-process-step, .jh-reveal, [data-flow]");
  if (revealItems.length && "IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.14, rootMargin: "0px 0px -6% 0px" }
    );
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
  }

  document.querySelectorAll("[data-flow]").forEach((flow) => {
    flow.querySelectorAll(".jh-flow-connector").forEach((connector, index) => {
      connector.style.setProperty("--step-i", String(index + 1));
    });
  });

  const year = document.querySelector("[data-year]");
  if (year) {
    year.textContent = String(new Date().getFullYear());
  }

  const passwordToggles = document.querySelectorAll("[data-password-toggle]");
  passwordToggles.forEach((passwordToggle) => {
    passwordToggle.addEventListener("click", () => {
      const targetId = passwordToggle.getAttribute("data-target") || "password";
      const input = document.getElementById(targetId);
      if (!input) {
        return;
      }
      const showing = input.type === "text";
      input.type = showing ? "password" : "text";
      const label = showing
        ? passwordToggle.getAttribute("data-show-label")
        : passwordToggle.getAttribute("data-hide-label");
      passwordToggle.textContent = label || passwordToggle.textContent;
      passwordToggle.setAttribute("aria-label", label || "");
    });
  });

  const regForm = document.querySelector("[data-register-wizard]");
  if (regForm) {
    const steps = Array.from(regForm.querySelectorAll("[data-reg-step]"));
    const navItems = Array.from(document.querySelectorAll("[data-reg-progress] [data-reg-nav]"));
    const btnPrev = regForm.querySelector("[data-reg-prev]");
    const btnNext = regForm.querySelector("[data-reg-next]");
    const btnSubmit = regForm.querySelector("[data-reg-submit]");
    const stepError = regForm.querySelector("[data-reg-error]");
    const msgRequired = regForm.dataset.msgRequired || "This field is required.";
    const msgStep = regForm.dataset.msgStep || "";
    let current = 1;
    const total = steps.length;

    const provinceEl = regForm.querySelector('[data-loc="province"]');
    const communeEl = regForm.querySelector('[data-loc="commune"]');
    const zoneEl = regForm.querySelector('[data-loc="zone"]');
    const collineEl = regForm.querySelector('[data-loc="colline"]');
    const apiCommunes = regForm.dataset.apiCommunes || "";
    const apiZones = regForm.dataset.apiZones || "";
    const apiCollines = regForm.dataset.apiCollines || "";
    const phCommune = regForm.dataset.phCommune || "Please select a province first.";
    const phZone = regForm.dataset.phZone || "Please select a commune first.";
    const phColline = regForm.dataset.phColline || "Please select a zone first.";
    const msgLoad = regForm.dataset.msgLoad || "Unable to load location options.";
    let cascadeSeq = 0;

    const fieldWrap = (el) => el.closest(".jh-field, .jh-auth-consent") || el.parentElement;
    const errorBox = (el) => fieldWrap(el)?.querySelector("[data-field-error]");

    const clearFieldError = (el) => {
      el.classList.remove("is-invalid");
      const box = errorBox(el);
      if (box) {
        box.hidden = true;
        box.textContent = "";
      }
    };

    const showFieldError = (el, message) => {
      el.classList.add("is-invalid");
      const box = errorBox(el);
      if (box) {
        box.hidden = false;
        box.textContent = message;
      }
    };

    const fillSelect = (select, options, enabled, disabledPlaceholder) => {
      if (!select) return;
      const emptyLabel = enabled
        ? "—"
        : (disabledPlaceholder || "—");
      select.innerHTML = "";
      const empty = document.createElement("option");
      empty.value = "";
      empty.textContent = emptyLabel;
      select.append(empty);
      (options || []).forEach((item) => {
        const opt = document.createElement("option");
        opt.value = String(item.id);
        opt.textContent = item.label;
        select.append(opt);
      });
      select.disabled = !enabled;
      select.value = "";
    };

    const fetchOptions = async (url) => {
      const response = await fetch(url, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      if (!response.ok) {
        throw new Error("load_failed");
      }
      const payload = await response.json();
      return Array.isArray(payload.options) ? payload.options : [];
    };

    const setLoading = (select, loading) => {
      if (!select) return;
      select.classList.toggle("is-loading", Boolean(loading));
      select.setAttribute("aria-busy", loading ? "true" : "false");
    };

    const loadDependent = async (select, url, enabled, disabledPlaceholder, selectedId = "") => {
      if (!select) return;
      if (!enabled) {
        fillSelect(select, [], false, disabledPlaceholder);
        return;
      }

      setLoading(select, true);
      select.disabled = true;
      try {
        const options = await fetchOptions(url);
        fillSelect(select, options, true);
        if (selectedId) {
          select.value = String(selectedId);
        }
      } catch (_err) {
        fillSelect(select, [], false, msgLoad);
        showFieldError(select, msgLoad);
      } finally {
        setLoading(select, false);
      }
    };

    provinceEl?.addEventListener("change", async () => {
      const seq = ++cascadeSeq;
      clearFieldError(provinceEl);
      fillSelect(zoneEl, [], false, phZone);
      fillSelect(collineEl, [], false, phColline);
      await loadDependent(
        communeEl,
        `${apiCommunes}?province_id=${encodeURIComponent(provinceEl.value)}`,
        Boolean(provinceEl.value),
        phCommune
      );
      if (seq !== cascadeSeq) return;
    });

    communeEl?.addEventListener("change", async () => {
      const seq = ++cascadeSeq;
      clearFieldError(communeEl);
      fillSelect(collineEl, [], false, phColline);
      await loadDependent(
        zoneEl,
        `${apiZones}?commune_id=${encodeURIComponent(communeEl.value)}`,
        Boolean(communeEl.value),
        phZone
      );
      if (seq !== cascadeSeq) return;
    });

    zoneEl?.addEventListener("change", async () => {
      const seq = ++cascadeSeq;
      clearFieldError(zoneEl);
      await loadDependent(
        collineEl,
        `${apiCollines}?zone_id=${encodeURIComponent(zoneEl.value)}`,
        Boolean(zoneEl.value),
        phColline
      );
      if (seq !== cascadeSeq) return;
    });

    collineEl?.addEventListener("change", () => clearFieldError(collineEl));

    const valueOf = (el) => {
      if (el.type === "checkbox") return el.checked ? el.value : "";
      if (el.type === "file") return el.files && el.files.length ? el.files[0].name : "";
      return String(el.value || "").trim();
    };

    const isAtLeastMinAge = (isoDate) => {
      if (!isoDate) return false;
      const minAge = Number(regForm.dataset.minAge || 16);
      const dob = new Date(`${isoDate}T00:00:00`);
      if (Number.isNaN(dob.getTime())) return false;
      const today = new Date();
      const cutoff = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
      return dob <= cutoff;
    };

    const isAllowedCniFile = (file) => {
      if (!file) return false;
      const maxBytes = Number(regForm.dataset.cniMaxBytes || 2 * 1024 * 1024);
      const allowed = ["application/pdf", "image/jpeg", "image/png", "image/jpg"];
      const extOk = /\.(pdf|jpe?g|png)$/i.test(file.name || "");
      const typeOk = !file.type || allowed.includes(file.type);
      if (!extOk || !typeOk) return "type";
      if (file.size > maxBytes) return "size";
      return true;
    };

    const validateField = (el) => {
      if (el.disabled) {
        clearFieldError(el);
        return true;
      }

      if (el.type === "file") {
        const file = el.files && el.files[0] ? el.files[0] : null;
        if (!file) {
          showFieldError(el, el.dataset.errorEmpty || msgRequired);
          return false;
        }
        const fileCheck = isAllowedCniFile(file);
        if (fileCheck === "type") {
          showFieldError(el, el.dataset.errorType || regForm.dataset.msgCniType || msgRequired);
          return false;
        }
        if (fileCheck === "size") {
          showFieldError(el, el.dataset.errorSize || regForm.dataset.msgCniSize || msgRequired);
          return false;
        }
        clearFieldError(el);
        return true;
      }

      const value = valueOf(el);
      if (!value) {
        showFieldError(el, el.dataset.errorEmpty || msgRequired);
        return false;
      }

      if (el.id === "date_of_birth" && !isAtLeastMinAge(value)) {
        showFieldError(el, el.dataset.errorMinAge || regForm.dataset.msgMinAge || msgRequired);
        return false;
      }

      if (el.type === "email") {
        const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        if (!ok) {
          showFieldError(el, el.dataset.errorInvalid || regForm.dataset.msgEmail || msgRequired);
          return false;
        }
      }

      if (el.id === "password" && value.length < 8) {
        showFieldError(el, el.dataset.errorInvalid || regForm.dataset.msgPassword || msgRequired);
        return false;
      }

      if (el.id === "password_confirm") {
        const password = regForm.querySelector("#password");
        if (password && value !== password.value) {
          showFieldError(el, el.dataset.errorMatch || regForm.dataset.msgPasswordMatch || msgRequired);
          return false;
        }
      }

      clearFieldError(el);
      return true;
    };

    const fieldsInStep = (n) => {
      const panel = steps.find((s) => Number(s.dataset.regStep) === n);
      if (!panel) return [];
      return Array.from(panel.querySelectorAll("input, select, textarea")).filter(
        (el) => el.hasAttribute("required") || el.name === "consent"
      );
    };

    const validateFieldSilent = (el) => {
      if (el.disabled) return true;
      if (el.type === "file") {
        const file = el.files && el.files[0] ? el.files[0] : null;
        return Boolean(file) && isAllowedCniFile(file) === true;
      }
      const value = valueOf(el);
      if (!value) return false;
      if (el.id === "date_of_birth" && !isAtLeastMinAge(value)) return false;
      if (el.type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return false;
      if (el.id === "password" && value.length < 8) return false;
      if (el.id === "password_confirm") {
        const password = regForm.querySelector("#password");
        if (password && value !== password.value) return false;
      }
      return true;
    };

    const validateStep = (n) => {
      const fields = fieldsInStep(n);
      let ok = true;
      let firstInvalid = null;
      fields.forEach((field) => {
        if (!validateField(field)) {
          ok = false;
          if (!firstInvalid) firstInvalid = field;
        }
      });
      if (firstInvalid) firstInvalid.focus();
      return ok;
    };

    const isStepComplete = (n) => fieldsInStep(n).every((field) => validateFieldSilent(field));

    const updateActionState = () => {
      if (btnNext && !btnNext.hidden) {
        btnNext.disabled = !isStepComplete(current);
      }
      if (btnSubmit) {
        const consent = regForm.querySelector("#consent");
        btnSubmit.disabled = !(consent && consent.checked);
      }
    };

    const populateReview = () => {
      const gender = regForm.querySelector("#gender");
      const genderLabel =
        gender?.selectedOptions?.[0]?.textContent?.trim() || gender?.value || "—";
      const map = {
        first_name: "#first_name",
        last_name: "#last_name",
        date_of_birth: "#date_of_birth",
        national_id: "#national_id",
        national_id_file: "#national_id_file",
        phone: "#phone",
        email: "#email",
        address: "#address",
        birth_province: "#birth_province",
        birth_commune: "#birth_commune",
        birth_zone: "#birth_zone",
        birth_colline: "#birth_colline",
        username: "#username",
      };
      const selectedLabel = (selector) => {
        const source = regForm.querySelector(selector);
        if (!source) return "—";
        if (source.tagName === "SELECT") {
          const opt = source.options[source.selectedIndex];
          return opt && opt.value ? opt.textContent.trim() : "—";
        }
        return valueOf(source) || "—";
      };

      Object.entries(map).forEach(([key, selector]) => {
        const target = regForm.querySelector(`[data-review="${key}"]`);
        if (target) target.textContent = selectedLabel(selector);
      });
      const genderReview = regForm.querySelector('[data-review="gender_label"]');
      if (genderReview) genderReview.textContent = genderLabel || "—";
    };

    const showStepError = (msg) => {
      if (!stepError) return;
      stepError.hidden = !msg;
      stepError.textContent = msg || "";
    };

    const setStep = (n) => {
      current = n;
      steps.forEach((step) => {
        const active = Number(step.dataset.regStep) === n;
        step.hidden = !active;
        step.classList.toggle("is-active", active);
      });
      navItems.forEach((item) => {
        const num = Number(item.dataset.regNav);
        item.classList.toggle("is-active", num === n);
        item.classList.toggle("is-done", num < n);
      });
      if (btnPrev) btnPrev.hidden = n === 1;
      if (btnNext) btnNext.hidden = n === total;
      if (btnSubmit) btnSubmit.hidden = n !== total;
      showStepError("");
      if (n === total) populateReview();
      updateActionState();
      steps
        .find((s) => Number(s.dataset.regStep) === n)
        ?.scrollIntoView({ behavior: "smooth", block: "nearest" });
    };

    btnNext?.addEventListener("click", () => {
      if (!validateStep(current)) {
        showStepError(msgStep);
        updateActionState();
        return;
      }
      if (current < total) setStep(current + 1);
    });

    btnPrev?.addEventListener("click", () => {
      if (current > 1) setStep(current - 1);
    });

    navItems.forEach((item) => {
      item.addEventListener("click", () => {
        const target = Number(item.dataset.regNav);
        if (target === current) return;
        if (target < current) {
          setStep(target);
          return;
        }
        for (let i = current; i < target; i += 1) {
          if (!validateStep(i)) {
            setStep(i);
            showStepError(msgStep);
            return;
          }
        }
        setStep(target);
      });
    });

    regForm.querySelectorAll("[data-reg-edit]").forEach((btn) => {
      btn.addEventListener("click", () => setStep(Number(btn.dataset.regEdit)));
    });

    regForm.querySelectorAll("input, select, textarea").forEach((el) => {
      const onChange = () => {
        if (el.classList.contains("is-invalid") || valueOf(el)) {
          validateField(el);
        }
        if (el.id === "password") {
          const confirm = regForm.querySelector("#password_confirm");
          if (confirm?.value) validateField(confirm);
        }
        updateActionState();
      };
      el.addEventListener("input", onChange);
      el.addEventListener("change", onChange);
      el.addEventListener("blur", () => {
        if (valueOf(el) || el.classList.contains("is-invalid")) validateField(el);
        updateActionState();
      });
    });

    regForm.addEventListener("submit", (e) => {
      const consent = regForm.querySelector("#consent");
      if (!consent?.checked) {
        e.preventDefault();
        setStep(total);
        if (consent) {
          showFieldError(consent, consent.dataset.errorEmpty || msgRequired);
          consent.focus();
        }
        showStepError(msgStep);
        updateActionState();
        return;
      }

      for (let i = 1; i <= total; i += 1) {
        if (!validateStep(i)) {
          e.preventDefault();
          setStep(i);
          showStepError(msgStep);
          return;
        }
      }
    });

    setStep(1);
  }
})();
