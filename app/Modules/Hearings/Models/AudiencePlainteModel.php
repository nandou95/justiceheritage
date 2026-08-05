<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class AudiencePlainteModel extends Model
{
    protected $table            = 'audience.audience_plainte';
    protected $primaryKey       = 'audience_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'audience_id',
        'plainte_id',
        'convocation_id',
        'motif_report',
        'rapport',
        'rapport_valide',
        'statut_audience_id',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByAudience(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                ap.audience_plainte_id,
                ap.audience_id,
                ap.plainte_id,
                ap.convocation_id,
                ap.motif_report,
                ap.rapport,
                ap.rapport_valide,
                ap.statut_audience_id,
                p.numero_dossier,
                p.objet,
                p.date_depot,
                p.juridiction_id,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                sa.description_statut_audience
            FROM audience.audience_plainte AS ap
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
            LEFT JOIN audience.statut_audience AS sa ON sa.statut_audience_id = ap.statut_audience_id
            WHERE ap.audience_id = ?
            ORDER BY p.numero_dossier ASC
        SQL;

        return $this->db->query($sql, [$audienceId])->getResultArray();
    }

    public function deleteByAudience(int $audienceId): void
    {
        $this->where('audience_id', $audienceId)->delete();
    }
}
