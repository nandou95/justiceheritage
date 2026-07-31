<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;
use DateTimeZone;

class PersonneModel extends Model
{
    protected $table            = 'plaignant.personne';
    protected $primaryKey       = 'personne_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'nom_personne',
        'prenom_personne',
        'date_naissance',
        'sexe_id',
        'numero_cni',
        'upload_cni',
        'telephone',
        'email',
        'adresse_residence',
        'province_naissance_id',
        'commune_naissance_id',
        'zone_naissance_id',
        'colline_naissance_id',
        'user_name',
        'mot_de_passe_hash',
        'code_authentification',
        'code_authentification_expire_at',
    ];

    public function usernameExists(string $username): bool
    {
        return $this->where('user_name', $username)->countAllResults() > 0;
    }

    public function emailExists(string $email): bool
    {
        return $this->where('email', $email)->countAllResults() > 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $row = $this->where('user_name', $username)->first();

        return is_array($row) ? $row : null;
    }

    public function setAuthenticationCode(int $personneId, string $code, int $expiresAtUnix): bool
    {
        $expiresAt = (new DateTimeImmutable('@' . $expiresAtUnix))
            ->setTimezone(new DateTimeZone(date_default_timezone_get()))
            ->format('Y-m-d H:i:sP');

        return $this->update($personneId, [
            'code_authentification'            => $code,
            'code_authentification_expire_at'  => $expiresAt,
        ]);
    }

    public function clearAuthenticationCode(int $personneId): bool
    {
        return $this->update($personneId, [
            'code_authentification'           => null,
            'code_authentification_expire_at' => null,
        ]);
    }

    /**
     * If the stored code has expired, set it to NULL and return true.
     */
    public function purgeExpiredAuthenticationCode(int $personneId): bool
    {
        $person = $this->find($personneId);
        if (! is_array($person) || empty($person['code_authentification'])) {
            return false;
        }

        if (empty($person['code_authentification_expire_at'])) {
            $this->clearAuthenticationCode($personneId);

            return true;
        }

        $expires = strtotime((string) $person['code_authentification_expire_at']);
        if ($expires === false || time() > $expires) {
            $this->clearAuthenticationCode($personneId);

            return true;
        }

        return false;
    }
}
