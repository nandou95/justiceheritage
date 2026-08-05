<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class StatutAudienceModel extends Model
{
    protected $table            = 'audience.statut_audience';
    protected $primaryKey       = 'statut_audience_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['description_statut_audience'];

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->builder()
            ->orderBy('description_statut_audience', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findDefaultId(): ?int
    {
        $row = $this->builder()
            ->select('statut_audience_id')
            ->orderBy('statut_audience_id', 'ASC')
            ->get(1)
            ->getRowArray();

        return $row ? (int) $row['statut_audience_id'] : null;
    }

    public function findByLabelNeedles(array $needles): ?int
    {
        foreach ($this->listAll() as $row) {
            $label = mb_strtolower((string) ($row['description_statut_audience'] ?? ''));
            foreach ($needles as $needle) {
                if ($label !== '' && str_contains($label, mb_strtolower($needle))) {
                    return (int) $row['statut_audience_id'];
                }
            }
        }

        return $this->findDefaultId();
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['statut_audience_id'],
            'label' => (string) ($row['description_statut_audience'] ?? ''),
        ], $this->listAll());
    }
}
