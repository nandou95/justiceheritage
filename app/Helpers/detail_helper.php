<?php

if (! function_exists('bo_dash')) {
    /**
     * Display a value or an em dash when empty.
     */
    function bo_dash(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? lang('Backoffice.yes') : lang('Backoffice.no');
        }

        $text = trim((string) $value);

        return $text === '' ? '—' : $text;
    }
}

if (! function_exists('bo_format_date')) {
    /**
     * Format a date/datetime using the application standard (d/m/Y[+ H:i]).
     */
    function bo_format_date(mixed $value, bool $withTime = false): string
    {
        if ($value === null) {
            return '—';
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '—') {
            return '—';
        }

        // Date-only values without time should not force midnight display.
        $isDateOnly = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw);

        try {
            $dt = new DateTimeImmutable($raw);
        } catch (Throwable $e) {
            return esc($raw);
        }

        if ($withTime && ! $isDateOnly) {
            return esc($dt->format('d/m/Y H:i'));
        }

        return esc($dt->format('d/m/Y'));
    }
}

if (! function_exists('bo_status_tone')) {
    /**
     * Map a status label to a visual tone.
     */
    function bo_status_tone(string $label): string
    {
        $l = mb_strtolower(trim($label));

        if ($l === '') {
            return 'neutral';
        }

        if (
            str_contains($l, 'actif') || str_contains($l, 'active')
            || str_contains($l, 'envoy') || str_contains($l, 'sent')
            || str_contains($l, 'succès') || str_contains($l, 'success')
            || str_contains($l, 'valid') || str_contains($l, 'reçu')
            || str_contains($l, 'receiv') || str_contains($l, 'oui')
            || $l === 'yes'
        ) {
            return 'success';
        }

        if (
            str_contains($l, 'inactif') || str_contains($l, 'inactive')
            || str_contains($l, 'échec') || str_contains($l, 'echec')
            || str_contains($l, 'fail') || str_contains($l, 'annul')
            || str_contains($l, 'refus') || str_contains($l, 'reject')
            || str_contains($l, 'non') || $l === 'no'
        ) {
            return 'danger';
        }

        if (
            str_contains($l, 'attente') || str_contains($l, 'pending')
            || str_contains($l, 'transit') || str_contains($l, 'cours')
            || str_contains($l, 'progress') || str_contains($l, 'report')
        ) {
            return 'warning';
        }

        if (str_contains($l, 'info') || str_contains($l, 'nouveau') || str_contains($l, 'new')) {
            return 'info';
        }

        return 'neutral';
    }
}

if (! function_exists('bo_badge')) {
    /**
     * Render a colored Bootstrap-style badge.
     */
    function bo_badge(string $label, ?string $tone = null): string
    {
        $text = trim($label);
        if ($text === '') {
            return '<span class="text-muted">—</span>';
        }

        $tone = $tone ?: bo_status_tone($text);

        return '<span class="badge rounded-pill bo-detail-badge bo-detail-badge--' . esc($tone, 'attr') . '">'
            . esc($text)
            . '</span>';
    }
}

if (! function_exists('bo_bool_badge')) {
    /**
     * Render Active/Inactive or Yes/No as a colored badge.
     */
    function bo_bool_badge(mixed $value, ?string $trueLabel = null, ?string $falseLabel = null): string
    {
        $active = function_exists('db_bool') ? db_bool($value) : (bool) $value;
        $label  = $active
            ? ($trueLabel ?? lang('Backoffice.status_active'))
            : ($falseLabel ?? lang('Backoffice.status_inactive'));

        return bo_badge($label, $active ? 'success' : 'danger');
    }
}

if (! function_exists('bo_code')) {
    /**
     * Render a monospace code chip.
     */
    function bo_code(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '—';
        }

        return '<code class="bo-route-code">' . esc($text) . '</code>';
    }
}
