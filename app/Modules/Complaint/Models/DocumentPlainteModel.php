<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class DocumentPlainteModel extends Model
{
    protected $table            = 'plainte.document_plainte';
    protected $primaryKey       = 'document_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'plainte_id',
        'type_document_id',
        'plainte_role_personne_id',
        'nom_fichier',
        'fichier_chemin_stockage',
        'taille_octets',
        'hash_sha256',
        'niveau_juridiction_id',
        'date_depot',
        'depose_par_utilisateur',
        'description',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByPlainte(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                d.document_plainte_id,
                d.nom_fichier,
                d.fichier_chemin_stockage,
                d.taille_octets,
                d.date_depot,
                d.description,
                d.depose_par_utilisateur,
                t.code_type_document,
                t.libelle_type_document,
                t.is_obligatoire,
                TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS uploaded_by_name
            FROM plainte.document_plainte AS d
            LEFT JOIN plainte.type_document AS t ON t.type_document_id = d.type_document_id
            LEFT JOIN administration.utilisateur AS u ON u.utilisateur_id = d.depose_par_utilisateur
            WHERE d.plainte_id = ?
            ORDER BY d.date_depot DESC NULLS LAST, d.document_plainte_id DESC
        SQL;

        return $this->db->query($sql, [$plainteId])->getResultArray();
    }
}
