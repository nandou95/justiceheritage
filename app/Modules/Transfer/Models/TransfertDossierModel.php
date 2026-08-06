<?php

namespace Modules\Transfer\Models;

use CodeIgniter\Model;

class TransfertDossierModel extends Model
{
    protected $table            = 'transfert.transfert_dossier';
    protected $primaryKey       = 'transfert_dossier_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'plainte_id',
        'juridiction_source_id',
        'juridiction_dest_id',
        'numero_dossier_dest',
        'date_transfert',
        'transfere_par',
        'recu_par',
        'date_reception',
        'statut_transfert_dossier_id',
        'observations',
        'created_at',
    ];

    public function hasPendingTransfer(int $plainteId, ?int $ignoreId = null): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(*)::int AS total
            FROM transfert.transfert_dossier t
            INNER JOIN transfert.statut_transfert_dossier s
                ON s.statut_transfert_dossier_id = t.statut_transfert_dossier_id
            WHERE t.plainte_id = ?
              AND (
                    LOWER(s.description_statut_transfert_dossier) LIKE '%transit%'
                 OR LOWER(s.description_statut_transfert_dossier) LIKE '%attente%'
                 OR LOWER(s.description_statut_transfert_dossier) LIKE '%pending%'
              )
        SQL;
        $params = [$plainteId];

        if ($ignoreId) {
            $sql .= ' AND t.transfert_dossier_id <> ?';
            $params[] = $ignoreId;
        }

        $row = $this->db->query($sql, $params)->getRowArray();

        return ((int) ($row['total'] ?? 0)) > 0;
    }
}
