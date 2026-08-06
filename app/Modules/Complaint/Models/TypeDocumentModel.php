<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class TypeDocumentModel extends Model
{
    protected $table            = 'plainte.type_document';
    protected $primaryKey       = 'type_document_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'code_type_document',
        'libelle_type_document',
        'niveau_juridiction_id',
        'is_obligatoire',
        'is_actif',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?int $niveauId = null, ?bool $isActive = null): array
    {
        $sql = <<<'SQL'
            SELECT
                t.type_document_id,
                t.code_type_document,
                t.libelle_type_document,
                t.niveau_juridiction_id,
                t.is_obligatoire,
                t.is_actif,
                nj.desc_niveau_juridiction
            FROM plainte.type_document AS t
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = t.niveau_juridiction_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if ($niveauId) {
            $sql .= ' AND t.niveau_juridiction_id = ?';
            $params[] = $niveauId;
        }
        if ($isActive === true) {
            $sql .= ' AND t.is_actif = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (t.is_actif = FALSE OR t.is_actif IS NULL)';
        }

        $sql .= ' ORDER BY nj.desc_niveau_juridiction ASC NULLS LAST, t.code_type_document ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByNiveau(int $niveauId, bool $activeOnly = true): array
    {
        $builder = $this->builder()
            ->select('type_document_id, code_type_document, libelle_type_document, is_obligatoire, is_actif')
            ->where('niveau_juridiction_id', $niveauId)
            ->orderBy('is_obligatoire', 'DESC')
            ->orderBy('libelle_type_document', 'ASC');

        if ($activeOnly) {
            $builder->where('is_actif', true);
        }

        return $builder->get()->getResultArray();
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM plainte.type_document WHERE LOWER(code_type_document) = LOWER(?)';
        $params = [$code];
        if ($ignoreId) {
            $sql .= ' AND type_document_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }
}
