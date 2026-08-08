<?php

namespace Modules\People\Models;

use CodeIgniter\Model;

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
    ];

    /**
     * @param array{
     *   province_naissance_id?:int|null,
     *   commune_naissance_id?:int|null,
     *   zone_naissance_id?:int|null,
     *   colline_naissance_id?:int|null,
     *   sexe_id?:int|null,
     *   date_naissance?:string|null
     * } $filters
     * @return list<array<string, mixed>>
     */
    public function listWithRelations(array $filters = []): array
    {
        $builder = $this->db->table('plaignant.personne AS p')
            ->select('
                p.personne_id,
                p.nom_personne,
                p.prenom_personne,
                p.date_naissance,
                p.email,
                p.telephone,
                p.numero_cni,
                p.upload_cni,
                p.adresse_residence,
                p.sexe_id,
                p.province_naissance_id,
                p.commune_naissance_id,
                p.zone_naissance_id,
                p.colline_naissance_id,
                p.create_at,
                s.description_sexe,
                prov.province_name AS province_naissance_name,
                com.commune_name AS commune_naissance_name,
                z.zone_name AS zone_naissance_name,
                col.colline_name AS colline_naissance_name
            ')
            ->join('plaignant.sexe AS s', 's.sexe_id = p.sexe_id', 'left')
            ->join('localite.localite_province AS prov', 'prov.province_id = p.province_naissance_id', 'left')
            ->join('localite.localite_commune AS com', 'com.commune_id = p.commune_naissance_id', 'left')
            ->join('localite.localite_zone AS z', 'z.zone_id = p.zone_naissance_id', 'left')
            ->join('localite.localite_colline AS col', 'col.colline_id = p.colline_naissance_id', 'left');

        if (! empty($filters['province_naissance_id'])) {
            $builder->where('p.province_naissance_id', (int) $filters['province_naissance_id']);
        }
        if (! empty($filters['commune_naissance_id'])) {
            $builder->where('p.commune_naissance_id', (int) $filters['commune_naissance_id']);
        }
        if (! empty($filters['zone_naissance_id'])) {
            $builder->where('p.zone_naissance_id', (int) $filters['zone_naissance_id']);
        }
        if (! empty($filters['colline_naissance_id'])) {
            $builder->where('p.colline_naissance_id', (int) $filters['colline_naissance_id']);
        }
        if (! empty($filters['sexe_id'])) {
            $builder->where('p.sexe_id', (int) $filters['sexe_id']);
        }
        if (! empty($filters['date_naissance'])) {
            $builder->where('p.date_naissance', $filters['date_naissance']);
        }

        return $builder
            ->orderBy('p.nom_personne', 'ASC')
            ->orderBy('p.prenom_personne', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithRelations(int $id): ?array
    {
        $row = $this->db->table('plaignant.personne AS p')
            ->select('
                p.personne_id,
                p.nom_personne,
                p.prenom_personne,
                p.date_naissance,
                p.email,
                p.telephone,
                p.numero_cni,
                p.upload_cni,
                p.adresse_residence,
                p.sexe_id,
                p.province_naissance_id,
                p.commune_naissance_id,
                p.zone_naissance_id,
                p.colline_naissance_id,
                p.create_at,
                s.description_sexe,
                prov.province_name AS province_naissance_name,
                com.commune_name AS commune_naissance_name,
                z.zone_name AS zone_naissance_name,
                col.colline_name AS colline_naissance_name
            ')
            ->join('plaignant.sexe AS s', 's.sexe_id = p.sexe_id', 'left')
            ->join('localite.localite_province AS prov', 'prov.province_id = p.province_naissance_id', 'left')
            ->join('localite.localite_commune AS com', 'com.commune_id = p.commune_naissance_id', 'left')
            ->join('localite.localite_zone AS z', 'z.zone_id = p.zone_naissance_id', 'left')
            ->join('localite.localite_colline AS col', 'col.colline_id = p.colline_naissance_id', 'left')
            ->where('p.personne_id', $id)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listComplaintsForPerson(int $personneId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                rp.description_role_personne,
                j.nom_juridiction,
                nj.desc_niveau_juridiction,
                ep.description_etape_plainte,
                sp.description_statut_plainte,
                p.date_depot,
                p.is_recours
            FROM plaignant.plainte_role_personne AS prp
            JOIN plainte.plainte AS p
                ON p.plainte_id = prp.plainte_id
            JOIN plaignant.role_personne AS rp
                ON rp.role_personne_id = prp.role_personne_id
            LEFT JOIN juridiction.juridiction AS j
                ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS ep
                ON ep.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS sp
                ON sp.statut_plainte_id = p.statut_plainte_id
            WHERE prp.personne_id = ?
            ORDER BY p.date_depot DESC NULLS LAST, p.created_at DESC NULLS LAST
        SQL;

        return $this->db->query($sql, [$personneId])->getResultArray();
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('email', $email);
        if ($ignoreId !== null) {
            $builder->where('personne_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function cniExists(string $cni, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('numero_cni', $cni);
        if ($ignoreId !== null) {
            $builder->where('personne_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function usernameExists(string $username, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('user_name', $username);
        if ($ignoreId !== null) {
            $builder->where('personne_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('personne_id, prenom_personne, nom_personne, numero_cni')
            ->orderBy('nom_personne', 'ASC')
            ->orderBy('prenom_personne', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['personne_id'],
            'label' => trim(($row['prenom_personne'] ?? '') . ' ' . ($row['nom_personne'] ?? ''))
                . (! empty($row['numero_cni']) ? ' (' . $row['numero_cni'] . ')' : ''),
        ], $rows);
    }
}
