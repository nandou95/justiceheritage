<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class PlainteParcelleModel extends Model
{
    protected $table            = 'plainte.plainte_parcelle';
    protected $primaryKey       = 'plainte_parcelle_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'superficie_maitre_carreau',
        'localisation_parcelle',
        'plainte_id',
        'province_parcelle_id',
        'commune_parcelle_id',
        'zone_parcelle_id',
        'colline_parcelle_id',
        'created_at',
    ];

    public function deleteByPlainte(int $plainteId): void
    {
        $this->where('plainte_id', $plainteId)->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByPlainte(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                pp.*,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name
            FROM plainte.plainte_parcelle AS pp
            LEFT JOIN localite.localite_province AS lp ON lp.province_id = pp.province_parcelle_id
            LEFT JOIN localite.localite_commune AS lc ON lc.commune_id = pp.commune_parcelle_id
            LEFT JOIN localite.localite_zone AS lz ON lz.zone_id = pp.zone_parcelle_id
            LEFT JOIN localite.localite_colline AS lcol ON lcol.colline_id = pp.colline_parcelle_id
            WHERE pp.plainte_id = ?
            ORDER BY pp.plainte_parcelle_id ASC
        SQL;

        return $this->db->query($sql, [$plainteId])->getResultArray();
    }
}
