(() => {
  const toggle = document.querySelector("[data-bo-toggle]");
  const nav = document.querySelector("[data-bo-nav]");
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
