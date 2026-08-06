<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUtilisateurAuthCodeExpiry extends Migration
{
    public function up()
    {
        // PostgreSQL: add expiry column used by back-office 2FA (mirrors plaignant.personne).
        $this->db->query(
            'ALTER TABLE administration.utilisateur
             ADD COLUMN IF NOT EXISTS code_authentification_expire_at TIMESTAMPTZ NULL'
        );
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE administration.utilisateur
             DROP COLUMN IF EXISTS code_authentification_expire_at'
        );
    }
}
