<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php $etapeId = (int) ($record['etape_plainte_id'] ?? 0); ?>

<div id="cs-sa-alerts" aria-live="polite"></div>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaint_stages')) ?></p>
        <h1><?= esc(lang('Backoffice.cs_actions_manage_title')) ?></h1>
        <p><?= esc($record['description_etape_plainte'] ?? '') ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cs_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel mb-3">
    <form method="post" action="<?= site_url('backoffice/complaint-stages/' . $etapeId . '/actions') ?>" class="needs-validation" novalidate data-bo-skip-confirm>
        <?= csrf_field() ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="desc_etape_plainte_action"><?= esc(lang('Backoffice.cs_action_field_description')) ?> *</label>
                <input class="form-control" type="text" id="desc_etape_plainte_action" name="desc_etape_plainte_action" value="<?= esc(old('desc_etape_plainte_action') ?? '') ?>" required maxlength="255">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-bo-primary w-100" type="submit">
                    <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cs_action_add')) ?>
                </button>
            </div>
        </div>
    </form>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="cs-actions-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cs-actions-table" data-page-length="10" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cs_action_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actions as $row): ?>
                <tr data-action-id="<?= (int) $row['id'] ?>">
                    <td data-col="description"><?= esc($row['description']) ?></td>
                    <td data-col="status">
                        <span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span>
                    </td>
                    <td>
                        <div class="bo-action-group">
                            <button
                                class="btn btn-bo-icon"
                                type="button"
                                data-bo-cs-sa-edit
                                data-id="<?= (int) $row['id'] ?>"
                                data-bs-toggle="tooltip"
                                title="<?= esc(lang('Backoffice.cs_sa_edit'), 'attr') ?>"
                            >
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </button>
                            <form method="post" action="<?= site_url('backoffice/complaint-stages/' . $etapeId . '/actions/' . (int) $row['id'] . '/toggle-status') ?>" data-bo-skip-confirm>
                                <?= csrf_field() ?>
                                <button
                                    class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>"
                                    type="submit"
                                    data-bs-toggle="tooltip"
                                    data-col="toggle"
                                    title="<?= esc($row['is_active'] ? lang('Backoffice.cs_sa_deactivate') : lang('Backoffice.cs_sa_activate'), 'attr') ?>"
                                >
                                    <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="csSaEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="csSaEditForm" class="needs-validation" novalidate data-bo-skip-confirm>
                <?= csrf_field() ?>
                <input type="hidden" id="cs_sa_edit_id" value="">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="csSaEditTitle"><?= esc(lang('Backoffice.cs_sa_edit_title')) ?></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.btn_cancel'), 'attr') ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="csSaEditErrors" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label" for="cs_sa_edit_description"><?= esc(lang('Backoffice.cs_action_field_description')) ?> *</label>
                        <input class="form-control" type="text" id="cs_sa_edit_description" name="desc_etape_plainte_action" required maxlength="255">
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="cs_sa_edit_status"><?= esc(lang('Backoffice.cs_action_field_status')) ?> *</label>
                        <select class="form-select" id="cs_sa_edit_status" name="is_active" required>
                            <option value="1"><?= esc(lang('Backoffice.status_active')) ?></option>
                            <option value="0"><?= esc(lang('Backoffice.status_inactive')) ?></option>
                        </select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                    <button type="submit" class="btn btn-bo-primary" id="csSaEditSubmit"><?= esc(lang('Backoffice.cs_sa_save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.JH_CS_SA_I18N = {
    etapeId: <?= (int) $etapeId ?>,
    updateUrl: <?= json_encode(site_url('backoffice/complaint-stages/' . $etapeId . '/actions/__ID__')) ?>,
    showUrl: <?= json_encode(site_url('backoffice/complaint-stages/' . $etapeId . '/actions/__ID__/json')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.cs_sa_edit_title')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.cs_sa_save')) ?>,
    activate: <?= json_encode(lang('Backoffice.cs_sa_activate')) ?>,
    deactivate: <?= json_encode(lang('Backoffice.cs_sa_deactivate')) ?>,
    loadError: <?= json_encode(lang('Backoffice.cs_action_err_not_found')) ?>,
    saveError: <?= json_encode(lang('Backoffice.cs_action_err_save')) ?>,
    csrfName: <?= json_encode(csrf_token()) ?>,
    csrfHash: <?= json_encode(csrf_hash()) ?>
};

(() => {
  const i18n = window.JH_CS_SA_I18N || {};
  const form = document.getElementById("csSaEditForm");
  const modalEl = document.getElementById("csSaEditModal");
  const alertsEl = document.getElementById("cs-sa-alerts");
  if (!form || !modalEl || typeof bootstrap === "undefined") {
    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  const titleEl = document.getElementById("csSaEditTitle");
  const idEl = document.getElementById("cs_sa_edit_id");
  const descriptionEl = document.getElementById("cs_sa_edit_description");
  const statusEl = document.getElementById("cs_sa_edit_status");
  const submitBtn = document.getElementById("csSaEditSubmit");
  const errorsEl = document.getElementById("csSaEditErrors");
  const table = document.getElementById("cs-actions-table");
  const escapeHtml = (value) => String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");

  const showPageAlert = (type, message) => {
    if (!alertsEl || !message) {
      return;
    }
    alertsEl.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show bo-alert" role="alert">
        ${escapeHtml(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
    alertsEl.scrollIntoView({ behavior: "smooth", block: "nearest" });
  };

  const showModalErrors = (errors) => {
    if (!errorsEl) {
      return;
    }
    const list = Array.isArray(errors) ? errors.filter(Boolean) : [errors].filter(Boolean);
    if (!list.length) {
      errorsEl.classList.add("d-none");
      errorsEl.innerHTML = "";
      return;
    }
    errorsEl.classList.remove("d-none");
    errorsEl.innerHTML = `<ul class="mb-0 ps-3">${list.map((msg) => `<li>${escapeHtml(msg)}</li>`).join("")}</ul>`;
  };

  const csrfPayload = () => {
    const input = form.querySelector(`input[name="${i18n.csrfName}"]`);
    return {
      name: i18n.csrfName || input?.name || "",
      hash: input?.value || i18n.csrfHash || "",
    };
  };

  const applyActionToRow = (action) => {
    if (!action || !table) {
      return;
    }
    const row = table.querySelector(`tr[data-action-id="${action.id}"]`);
    if (!row) {
      return;
    }
    const descCell = row.querySelector('[data-col="description"]');
    const statusCell = row.querySelector('[data-col="status"]');
    const toggleBtn = row.querySelector('[data-col="toggle"]');
    if (descCell) {
      descCell.textContent = action.description || "";
    }
    if (statusCell) {
      statusCell.innerHTML = `<span class="bo-status-pill ${action.is_active ? "is-active" : "is-inactive"}">${escapeHtml(action.status || "")}</span>`;
    }
    if (toggleBtn) {
      toggleBtn.classList.toggle("is-danger", !!action.is_active);
      toggleBtn.classList.toggle("is-success", !action.is_active);
      toggleBtn.title = action.is_active ? (i18n.deactivate || "") : (i18n.activate || "");
      const icon = toggleBtn.querySelector("i");
      if (icon) {
        icon.className = `bi ${action.is_active ? "bi-toggle-off" : "bi-toggle-on"}`;
      }
    }
  };

  const openEditor = async (actionId) => {
    showModalErrors([]);
    form.classList.remove("was-validated");
    if (titleEl) titleEl.textContent = i18n.editTitle || "";
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = i18n.saveEdit || "";
    }
    if (descriptionEl) descriptionEl.value = "";
    if (statusEl) statusEl.value = "1";
    if (idEl) idEl.value = String(actionId || "");
    form.action = String(i18n.updateUrl || "").replace("__ID__", String(actionId));
    modal.show();

    try {
      const response = await fetch(String(i18n.showUrl || "").replace("__ID__", String(actionId)), {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      const data = await response.json();
      if (!response.ok || !data.ok || !data.action) {
        showModalErrors(data.errors || [i18n.loadError || "Action not found"]);
        return;
      }
      if (descriptionEl) descriptionEl.value = data.action.description || "";
      if (statusEl) statusEl.value = data.action.is_active ? "1" : "0";
    } catch (error) {
      showModalErrors([i18n.loadError || "Action not found"]);
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  };

  document.addEventListener("click", (event) => {
    const btn = event.target.closest("[data-bo-cs-sa-edit]");
    if (!btn || !table?.contains(btn)) {
      return;
    }
    event.preventDefault();
    const id = btn.getAttribute("data-id");
    if (id) {
      openEditor(id);
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    event.stopPropagation();
    showModalErrors([]);

    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }
    form.classList.add("was-validated");

    const actionId = idEl?.value || "";
    if (!actionId) {
      showModalErrors([i18n.loadError || "Action not found"]);
      return;
    }

    const csrf = csrfPayload();
    const body = new FormData(form);
    if (csrf.name && csrf.hash) {
      body.set(csrf.name, csrf.hash);
    }

    if (submitBtn) {
      submitBtn.disabled = true;
    }

    try {
      const response = await fetch(String(i18n.updateUrl || "").replace("__ID__", String(actionId)), {
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body,
        credentials: "same-origin",
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.ok) {
        showModalErrors(data.errors || [data.message || i18n.saveError || "Unable to save"]);
        return;
      }

      if (data.csrfHash && csrf.name) {
        const csrfInput = form.querySelector(`input[name="${csrf.name}"]`);
        if (csrfInput) {
          csrfInput.value = data.csrfHash;
        }
        i18n.csrfHash = data.csrfHash;
      }

      applyActionToRow(data.action);
      modal.hide();
      showPageAlert("success", data.message || "");
    } catch (error) {
      showModalErrors([error?.message || i18n.saveError || "Unable to save"]);
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  document.querySelector("section.bo-crud-panel.mb-3 form.needs-validation")?.addEventListener("submit", function (event) {
    if (!this.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    this.classList.add("was-validated");
  });
})();
</script>
<?= $this->endSection() ?>
