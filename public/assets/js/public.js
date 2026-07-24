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

  const revealItems = document.querySelectorAll(".jh-process-step, .jh-reveal");
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
      { threshold: 0.18, rootMargin: "0px 0px -8% 0px" }
    );
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
  }

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
})();
