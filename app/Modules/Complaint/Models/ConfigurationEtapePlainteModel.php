<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class ConfigurationEtapePlainteModel extends Model
{
    protected $table            = 'plainte.configuration_etape_plainte';
    protected $primaryKey       = 'configuration_etape_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'etape_plainte_actuel_id',
        'etape_plainte_suivant_id',
        'url_route',
        'is_active',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?int $niveauActuelId = null, ?bool $isActive = null): array
    {
        $sql = <<<'SQL'
            SELECT
                c.configuration_etape_plainte_id,
                c.etape_plainte_actuel_id,
                c.etape_plainte_suivant_id,
                c.url_route,
                c.is_active,
                ea.description_etape_plainte AS etape_actuel,
                ea.niveau_juridiction_id AS niveau_actuel_id,
                nja.desc_niveau_juridiction AS niveau_actuel,
                es.description_etape_plainte AS etape_suivant,
                es.niveau_juridiction_id AS niveau_suivant_id,
                njs.desc_niveau_juridiction AS niveau_suivant,
                (
                    SELECT COUNT(*)
                    FROM plainte.etape_plainte_profil epp
                    WHERE epp.etape_plainte_id = c.etape_plainte_actuel_id
                      AND (epp.is_active IS NULL OR epp.is_active = TRUE)
                ) AS profiles_actuel_count,
                (
                    SELECT COUNT(*)
                    FROM plainte.etape_plainte_profil epp2
                    WHERE epp2.etape_plainte_id = c.etape_plainte_suivant_id
                      AND (epp2.is_active IS NULL OR epp2.is_active = TRUE)
                ) AS profiles_suivant_count
            FROM plainte.configuration_etape_plainte AS c
            JOIN plainte.etape_plainte AS ea
                ON ea.etape_plainte_id = c.etape_plainte_actuel_id
            LEFT JOIN juridiction.niveau_juridiction AS nja
                ON nja.niveau_juridiction_id = ea.niveau_juridiction_id
            JOIN plainte.etape_plainte AS es
                ON es.etape_plainte_id = c.etape_plainte_suivant_id
            LEFT JOIN juridiction.niveau_juridiction AS njs
                ON njs.niveau_juridiction_id = es.niveau_juridiction_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if ($niveauActuelId) {
            $sql .= ' AND ea.niveau_juridiction_id = ?';
            $params[] = $niveauActuelId;
        }
        if ($isActive === true) {
            $sql .= ' AND c.is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (c.is_active = FALSE OR c.is_active IS NULL)';
        }

        $sql .= ' ORDER BY nja.desc_niveau_juridiction ASC NULLS LAST, ea.description_etape_plainte ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function pairExists(int $actuelId, int $suivantId, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()
            ->where('etape_plainte_actuel_id', $actuelId)
            ->where('etape_plainte_suivant_id', $suivantId);
        if ($ignoreId) {
            $builder->where('configuration_etape_plainte_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
