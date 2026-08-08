<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= public_asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/complaint-stages/' . (int) ($record['etape_plainte_id'] ?? 0))
    : site_url('backoffice/complaint-stages');
$val = static function (array $record, string $key) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }
    return $record[$key] ?? '';
};
$selectedProfiles = old('profil_ids');
if (! is_array($selectedProfiles)) {
    $selectedProfiles = $record['profil_ids'] ?? [];
}
$selectedProfiles = array_map('strval', (array) $selectedProfiles);
$isConvocation = old('is_convocation') !== null ? (bool) old('is_convocation') : filter_var($record['is_convocation'] ?? false, FILTER_VALIDATE_BOOLEAN);
$isAudience = old('is_audience') !== null ? (bool) old('is_audience') : filter_var($record['is_audience'] ?? false, FILTER_VALIDATE_BOOLEAN);
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaint_stages')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.cs_edit_title') : lang('Backoffice.cs_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cs_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cs_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" novalidate data-bo-cs-form>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="description_etape_plainte"><?= esc(lang('Backoffice.cs_field_description')) ?> *</label>
                <input class="form-control" type="text" id="description_etape_plainte" name="description_etape_plainte" value="<?= esc($val($record, 'description_etape_plainte')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cs_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="profil_ids"><?= esc(lang('Backoffice.cs_field_profiles')) ?> *</label>
                <select
                    class="form-select"
                    id="profil_ids"
                    name="profil_ids[]"
                    multiple
                    required
                    data-bo-profile-multiselect
                    data-placeholder="<?= esc(lang('Backoffice.cs_profiles_placeholder'), 'attr') ?>"
                    data-no-results="<?= esc(lang('Backoffice.cs_profiles_no_results'), 'attr') ?>"
                >
                    <?php foreach ($profiles as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $selectedProfiles, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.cs_hint_profiles')) ?></div>
                <div class="invalid-feedback" data-bo-profiles-feedback><?= esc(lang('Backoffice.cs_err_profiles')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_convocation" name="is_convocation" value="1" <?= $isConvocation ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_convocation"><?= esc(lang('Backoffice.cs_field_convocation')) ?></label>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_audience" name="is_audience" value="1" <?= $isAudience ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_audience"><?= esc(lang('Backoffice.cs_field_audience')) ?></label>
                </div>
            </div>

            <?php if (! $isEdit): ?>
                <?php
                $actionRows = old('actions');
                if (! is_array($actionRows) || $actionRows === []) {
                    $actionRows = [['desc_etape_plainte_action' => '', 'is_active' => '1']];
                }
                ?>
                <div class="col-12">
                    <hr class="bo-form-divider">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h2 class="h6 mb-0"><?= esc(lang('Backoffice.cs_actions_title')) ?></h2>
                        <button class="btn btn-bo-secondary btn-sm" type="button" data-cs-action-add>
                            <i class="bi bi-plus-lg" aria-hidden="true"></i>
                            <?= esc(lang('Backoffice.cs_action_add')) ?>
                        </button>
                    </div>
                    <p class="form-text mb-3"><?= esc(lang('Backoffice.cs_actions_create_hint')) ?></p>
                    <div class="bo-cs-action-rows" data-cs-action-rows>
                        <?php foreach ($actionRows as $index => $actionRow): ?>
                            <?php
                            $actionDesc = is_array($actionRow)
                                ? (string) ($actionRow['desc_etape_plainte_action'] ?? '')
                                : '';
                            $actionActive = is_array($actionRow)
                                ? (($actionRow['is_active'] ?? '1') === '1'
                                    || ($actionRow['is_active'] ?? true) === true
                                    || ($actionRow['is_active'] ?? '') === 'true'
                                    || ($actionRow['is_active'] ?? '') === 'on')
                                : true;
                            ?>
                            <div class="bo-cs-action-row row g-2 align-items-end mb-2" data-cs-action-row>
                                <div class="col-12 col-md-7">
                                    <label class="form-label"><?= esc(lang('Backoffice.cs_action_field_description')) ?></label>
                                    <input class="form-control" type="text" name="actions[<?= (int) $index ?>][desc_etape_plainte_action]"
                                           value="<?= esc($actionDesc) ?>" maxlength="255"
                                           placeholder="<?= esc(lang('Backoffice.cs_action_field_description'), 'attr') ?>">
                                </div>
                                <div class="col-8 col-md-3">
                                    <label class="form-label"><?= esc(lang('Backoffice.cs_action_field_status')) ?></label>
                                    <select class="form-select" name="actions[<?= (int) $index ?>][is_active]">
                                        <option value="1" <?= $actionActive ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                                        <option value="0" <?= ! $actionActive ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <button class="btn btn-bo-icon is-danger w-100" type="button" data-cs-action-remove
                                            data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cs_action_remove'), 'attr') ?>">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('Backoffice.cs_action_remove')) ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="bo-form-actions mt-4">
            <button class="btn btn-bo-primary" type="submit">
                <i class="bi bi-check-lg"></i> <?= esc($isEdit ? lang('Backoffice.cs_save') : lang('Backoffice.cs_create')) ?>
            </button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= public_asset('assets/vendor/tom-select/tom-select.complete.min.js') ?>"></script>
<script>
(() => {
  const form = document.querySelector("[data-bo-cs-form]");
  const select = document.querySelector("[data-bo-profile-multiselect]");
  if (!form || !select || typeof TomSelect === "undefined") {
    return;
  }

  const ts = new TomSelect(select, {
    plugins: ["remove_button", "clear_button"],
    maxItems: null,
    create: false,
    persist: false,
    hideSelected: true,
    closeAfterSelect: false,
    placeholder: select.dataset.placeholder || "",
    render: {
      no_results: (_data, escape) =>
        `<div class="no-results">${escape(select.dataset.noResults || "No results")}</div>`,
    },
  });

  const markProfilesValidity = () => {
    const hasSelection = (ts.getValue() || []).length > 0;
    select.setCustomValidity(hasSelection ? "" : (select.dataset.placeholder ? "required" : "required"));
    const wrap = select.closest(".col-12");
    const control = wrap?.querySelector(".ts-wrapper");
    if (control) {
      control.classList.toggle("is-invalid", !hasSelection && form.classList.contains("was-validated"));
      control.classList.toggle("is-valid", hasSelection && form.classList.contains("was-validated"));
    }
    return hasSelection;
  };

  ts.on("change", markProfilesValidity);
  ts.on("item_add", markProfilesValidity);
  ts.on("item_remove", markProfilesValidity);

  form.addEventListener("submit", (event) => {
    const profilesOk = markProfilesValidity();
    if (!form.checkValidity() || !profilesOk) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add("was-validated");
    markProfilesValidity();
  });

  const actionRows = form.querySelector("[data-cs-action-rows]");
  if (actionRows) {
    const reindexActions = () => {
      actionRows.querySelectorAll("[data-cs-action-row]").forEach((row, index) => {
        row.querySelectorAll("input, select").forEach((el) => {
          if (!el.name) {
            return;
          }
          el.name = el.name.replace(/actions\[\d+]/, `actions[${index}]`);
        });
      });
    };

    const bindActionRow = (row) => {
      row.querySelector("[data-cs-action-remove]")?.addEventListener("click", () => {
        const rows = actionRows.querySelectorAll("[data-cs-action-row]");
        if (rows.length <= 1) {
          row.querySelectorAll("input").forEach((el) => {
            el.value = "";
          });
          const status = row.querySelector("select");
          if (status) {
            status.value = "1";
          }
          return;
        }
        row.remove();
        reindexActions();
      });
    };

    actionRows.querySelectorAll("[data-cs-action-row]").forEach(bindActionRow);

    form.querySelector("[data-cs-action-add]")?.addEventListener("click", () => {
      const first = actionRows.querySelector("[data-cs-action-row]");
      if (!first) {
        return;
      }
      const clone = first.cloneNode(true);
      clone.querySelectorAll("input").forEach((el) => {
        el.value = "";
      });
      const status = clone.querySelector("select");
      if (status) {
        status.value = "1";
      }
      actionRows.appendChild(clone);
      bindActionRow(clone);
      reindexActions();
    });
  }
})();
</script>
<?= $this->endSection() ?>
