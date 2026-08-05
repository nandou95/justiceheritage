<?php

namespace Modules\Transfer\Models;

use CodeIgniter\Model;

class StatutTransfertDossierModel extends Model
{
    protected $table            = 'transfert.statut_transfert_dossier';
    protected $primaryKey       = 'statut_transfert_dossier_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'description_statut_transfert_dossier',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->builder()
            ->orderBy('description_statut_transfert_dossier', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['statut_transfert_dossier_id'],
            'label' => (string) ($row['description_statut_transfert_dossier'] ?? ''),
        ], $this->listAll());
    }
}
