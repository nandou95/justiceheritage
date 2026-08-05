<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class StatutCompteModel extends Model
{
    protected $table         = 'administration.statut_compte';
    protected $primaryKey    = 'statut_compte_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [];

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('statut_compte_id, desc_statut_compte')
            ->orderBy('statut_compte_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['statut_compte_id'],
            'label' => $row['desc_statut_compte'],
        ], $rows);
    }

    public function findActiveId(): ?int
    {
        foreach ($this->findAll() as $row) {
            $label = mb_strtolower((string) ($row['desc_statut_compte'] ?? ''));
            if (preg_match('/inactif|inactive/', $label)) {
                continue;
            }
            if (preg_match('/actif|active/', $label)) {
                return (int) $row['statut_compte_id'];
            }
        }

        return null;
    }

    public function findInactiveId(): ?int
    {
        foreach ($this->findAll() as $row) {
            $label = mb_strtolower((string) ($row['desc_statut_compte'] ?? ''));
            if (preg_match('/inactif|inactive/', $label)) {
                return (int) $row['statut_compte_id'];
            }
        }

        return null;
    }

    public function isActiveStatus(?int $statutCompteId): bool
    {
        if (! $statutCompteId) {
            return false;
        }

        $row = $this->find($statutCompteId);
        if (! $row) {
            return false;
        }

        $label = mb_strtolower((string) ($row['desc_statut_compte'] ?? ''));
        if (preg_match('/inactif|inactive/', $label)) {
            return false;
        }

        return (bool) preg_match('/actif|active/', $label);
    }
}
