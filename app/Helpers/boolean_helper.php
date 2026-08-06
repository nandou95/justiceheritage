<?php

if (! function_exists('db_bool')) {
    /**
     * Normalize database boolean values (PostgreSQL returns 't'/'f' strings).
     */
    function db_bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 't', 'true', 'yes', 'y', 'on'], true);
    }
}
