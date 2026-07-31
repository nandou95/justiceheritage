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

    // eslint-disable-next-line no-new
    new DataTable(table, {
      language: dtLang,
      pageLength,
      autoWidth: false,
      order: orderCol >= 0 ? [[orderCol, orderDir]] : [],
      columnDefs,
    });
  });
})();
