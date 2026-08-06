-- Run as a PostgreSQL role that owns administration.utilisateur
-- (required for back-office 2FA code expiry persistence).

ALTER TABLE administration.utilisateur
    ADD COLUMN IF NOT EXISTS code_authentification_expire_at TIMESTAMPTZ NULL;

COMMENT ON COLUMN administration.utilisateur.code_authentification_expire_at IS
    'Expiration timestamp for the one-time back-office 2FA authentication code.';
