-- Seed all Back Office permissions for JusticeHeritage (descriptions in French).
-- Idempotent: skips routes that already exist (case-insensitive).
-- Also assigns every active permission to profil_id = 1 (Administrateur Système).

BEGIN;

CREATE TEMP TABLE tmp_bo_permissions (
    description_permission varchar(255) NOT NULL,
    url_route              varchar(100) NOT NULL
) ON COMMIT DROP;

INSERT INTO tmp_bo_permissions (description_permission, url_route) VALUES
-- Accueil
('Consulter l''accueil du back-office', 'backoffice'),

-- Tableaux de bord
('Consulter le tableau de bord exécutif', 'backoffice/dashboards/executive'),
('Consulter le tableau de bord des plaintes', 'backoffice/dashboards/complaints'),
('Consulter le tableau de bord des plaignants', 'backoffice/dashboards/complainants'),
('Consulter le tableau de bord des recours', 'backoffice/dashboards/appeals'),
('Consulter le tableau de bord des convocations', 'backoffice/dashboards/summons'),
('Consulter le tableau de bord des audiences', 'backoffice/dashboards/hearings'),
('Consulter le tableau de bord des notifications', 'backoffice/dashboards/notifications'),
('Consulter le tableau de bord des juridictions', 'backoffice/dashboards/courts'),

-- Utilisateurs
('Consulter les utilisateurs', 'backoffice/users'),
('Créer un utilisateur', 'backoffice/users/create'),
('Consulter le détail d''un utilisateur', 'backoffice/users/show'),
('Modifier un utilisateur', 'backoffice/users/edit'),
('Activer/désactiver un utilisateur', 'backoffice/users/toggle-status'),
('Gérer les utilisateurs', 'backoffice/users/manage'),
('Supprimer un utilisateur', 'backoffice/users/delete'),

-- Profils
('Consulter les profils', 'backoffice/profiles'),
('Créer un profil', 'backoffice/profiles/create'),
('Consulter le détail d''un profil', 'backoffice/profiles/show'),
('Modifier un profil', 'backoffice/profiles/edit'),
('Activer/désactiver un profil', 'backoffice/profiles/toggle-status'),
('Affecter des permissions à un profil', 'backoffice/profiles/assign'),
('Gérer les profils', 'backoffice/profiles/manage'),
('Supprimer un profil', 'backoffice/profiles/delete'),

-- Permissions
('Consulter les permissions', 'backoffice/permissions'),
('Créer une permission', 'backoffice/permissions/create'),
('Modifier une permission', 'backoffice/permissions/edit'),
('Activer/désactiver une permission', 'backoffice/permissions/toggle-status'),
('Gérer les permissions', 'backoffice/permissions/manage'),
('Supprimer une permission', 'backoffice/permissions/delete'),

-- Juridictions
('Consulter les juridictions', 'backoffice/court-jurisdictions'),
('Créer une juridiction', 'backoffice/court-jurisdictions/create'),
('Consulter le détail d''une juridiction', 'backoffice/court-jurisdictions/show'),
('Modifier une juridiction', 'backoffice/court-jurisdictions/edit'),
('Activer/désactiver une juridiction', 'backoffice/court-jurisdictions/toggle-status'),
('Gérer les juridictions', 'backoffice/court-jurisdictions/manage'),
('Supprimer une juridiction', 'backoffice/court-jurisdictions/delete'),

-- Configuration des juridictions
('Consulter les configurations de juridictions', 'backoffice/court-jurisdiction-configs'),
('Créer une configuration de juridiction', 'backoffice/court-jurisdiction-configs/create'),
('Modifier une configuration de juridiction', 'backoffice/court-jurisdiction-configs/edit'),
('Activer/désactiver une configuration de juridiction', 'backoffice/court-jurisdiction-configs/toggle-status'),
('Gérer les configurations de juridictions', 'backoffice/court-jurisdiction-configs/manage'),
('Supprimer une configuration de juridiction', 'backoffice/court-jurisdiction-configs/delete'),

-- Niveaux de juridiction
('Consulter les niveaux de juridiction', 'backoffice/jurisdiction-levels'),
('Créer un niveau de juridiction', 'backoffice/jurisdiction-levels/create'),
('Modifier un niveau de juridiction', 'backoffice/jurisdiction-levels/edit'),
('Activer/désactiver un niveau de juridiction', 'backoffice/jurisdiction-levels/toggle-status'),
('Gérer les niveaux de juridiction', 'backoffice/jurisdiction-levels/manage'),
('Supprimer un niveau de juridiction', 'backoffice/jurisdiction-levels/delete'),

-- Configuration des niveaux
('Consulter les configurations de niveaux de juridiction', 'backoffice/jurisdiction-level-configs'),
('Créer une configuration de niveau de juridiction', 'backoffice/jurisdiction-level-configs/create'),
('Modifier une configuration de niveau de juridiction', 'backoffice/jurisdiction-level-configs/edit'),
('Activer/désactiver une configuration de niveau de juridiction', 'backoffice/jurisdiction-level-configs/toggle-status'),
('Gérer les configurations de niveaux de juridiction', 'backoffice/jurisdiction-level-configs/manage'),
('Supprimer une configuration de niveau de juridiction', 'backoffice/jurisdiction-level-configs/delete'),

-- Personnes
('Consulter les personnes', 'backoffice/people'),
('Créer une personne', 'backoffice/people/create'),
('Consulter le détail d''une personne', 'backoffice/people/show'),
('Modifier une personne', 'backoffice/people/edit'),
('Gérer les personnes', 'backoffice/people/manage'),
('Supprimer une personne', 'backoffice/people/delete'),

-- Étapes de plainte
('Consulter les étapes de plainte', 'backoffice/complaint-stages'),
('Créer une étape de plainte', 'backoffice/complaint-stages/create'),
('Modifier une étape de plainte', 'backoffice/complaint-stages/edit'),
('Activer/désactiver une étape de plainte', 'backoffice/complaint-stages/toggle-status'),
('Affecter des profils à une étape de plainte', 'backoffice/complaint-stages/assign'),
('Gérer les étapes de plainte', 'backoffice/complaint-stages/manage'),
('Supprimer une étape de plainte', 'backoffice/complaint-stages/delete'),

-- Configuration des étapes
('Consulter les configurations d''étapes de plainte', 'backoffice/complaint-stage-configs'),
('Créer une configuration d''étape de plainte', 'backoffice/complaint-stage-configs/create'),
('Modifier une configuration d''étape de plainte', 'backoffice/complaint-stage-configs/edit'),
('Activer/désactiver une configuration d''étape de plainte', 'backoffice/complaint-stage-configs/toggle-status'),
('Gérer les configurations d''étapes de plainte', 'backoffice/complaint-stage-configs/manage'),
('Supprimer une configuration d''étape de plainte', 'backoffice/complaint-stage-configs/delete'),

-- Statuts de plainte
('Consulter les statuts de plainte', 'backoffice/complaint-statuses'),
('Créer un statut de plainte', 'backoffice/complaint-statuses/create'),
('Modifier un statut de plainte', 'backoffice/complaint-statuses/edit'),
('Activer/désactiver un statut de plainte', 'backoffice/complaint-statuses/toggle-status'),
('Gérer les statuts de plainte', 'backoffice/complaint-statuses/manage'),
('Supprimer un statut de plainte', 'backoffice/complaint-statuses/delete'),

-- Types de documents
('Consulter les types de documents', 'backoffice/document-types'),
('Créer un type de document', 'backoffice/document-types/create'),
('Modifier un type de document', 'backoffice/document-types/edit'),
('Activer/désactiver un type de document', 'backoffice/document-types/toggle-status'),
('Gérer les types de documents', 'backoffice/document-types/manage'),
('Supprimer un type de document', 'backoffice/document-types/delete'),

-- Plaintes
('Consulter les plaintes', 'backoffice/complaints'),
('Créer une plainte', 'backoffice/complaints/create'),
('Consulter le détail d''une plainte', 'backoffice/complaints/show'),
('Modifier une plainte', 'backoffice/complaints/edit'),
('Transférer une plainte', 'backoffice/complaints/transfer'),
('Traiter une plainte', 'backoffice/complaints/process'),
('Gérer les plaintes', 'backoffice/complaints/manage'),
('Supprimer une plainte', 'backoffice/complaints/delete'),

-- Recours
('Consulter les recours', 'backoffice/appeals'),
('Créer un recours', 'backoffice/appeals/create'),
('Consulter le détail d''un recours', 'backoffice/appeals/show'),
('Modifier un recours', 'backoffice/appeals/edit'),
('Traiter un recours', 'backoffice/appeals/process'),
('Gérer les recours', 'backoffice/appeals/manage'),
('Supprimer un recours', 'backoffice/appeals/delete'),

-- Convocations
('Consulter les convocations', 'backoffice/summons'),
('Consulter les convocations en attente', 'backoffice/summons/pending'),
('Créer une convocation', 'backoffice/summons/create'),
('Consulter le détail d''une convocation', 'backoffice/summons/show'),
('Générer une convocation', 'backoffice/summons/generate'),
('Traiter une convocation', 'backoffice/summons/process'),
('Gérer les convocations', 'backoffice/summons/manage'),
('Supprimer une convocation', 'backoffice/summons/delete'),

-- Statuts de convocation
('Consulter les statuts de convocation', 'backoffice/summons-statuses'),
('Créer un statut de convocation', 'backoffice/summons-statuses/create'),
('Modifier un statut de convocation', 'backoffice/summons-statuses/edit'),
('Gérer les statuts de convocation', 'backoffice/summons-statuses/manage'),
('Supprimer un statut de convocation', 'backoffice/summons-statuses/delete'),

-- Audiences
('Consulter les audiences', 'backoffice/hearings'),
('Créer une audience', 'backoffice/hearings/create'),
('Consulter le détail d''une audience', 'backoffice/hearings/show'),
('Affecter le personnel à une audience', 'backoffice/hearings/assign'),
('Activer/désactiver une affectation d''audience', 'backoffice/hearings/assignments/toggle-status'),
('Traiter une audience', 'backoffice/hearings/process'),
('Gérer les audiences', 'backoffice/hearings/manage'),
('Supprimer une audience', 'backoffice/hearings/delete'),

-- Statuts d'audience
('Consulter les statuts d''audience', 'backoffice/hearing-statuses'),
('Créer un statut d''audience', 'backoffice/hearing-statuses/create'),
('Modifier un statut d''audience', 'backoffice/hearing-statuses/edit'),
('Gérer les statuts d''audience', 'backoffice/hearing-statuses/manage'),
('Supprimer un statut d''audience', 'backoffice/hearing-statuses/delete'),

-- Types de verdict
('Consulter les types de verdict', 'backoffice/verdict-types'),
('Créer un type de verdict', 'backoffice/verdict-types/create'),
('Modifier un type de verdict', 'backoffice/verdict-types/edit'),
('Gérer les types de verdict', 'backoffice/verdict-types/manage'),
('Supprimer un type de verdict', 'backoffice/verdict-types/delete'),

-- Verdicts
('Consulter les verdicts', 'backoffice/verdicts'),
('Créer un verdict', 'backoffice/verdicts/create'),
('Consulter le détail d''un verdict', 'backoffice/verdicts/show'),
('Générer un verdict', 'backoffice/verdicts/generate'),
('Traiter un verdict', 'backoffice/verdicts/process'),
('Gérer les verdicts', 'backoffice/verdicts/manage'),
('Supprimer un verdict', 'backoffice/verdicts/delete'),

-- Statuts de transfert
('Consulter les statuts de transfert', 'backoffice/transfer-statuses'),
('Créer un statut de transfert', 'backoffice/transfer-statuses/create'),
('Modifier un statut de transfert', 'backoffice/transfer-statuses/edit'),
('Gérer les statuts de transfert', 'backoffice/transfer-statuses/manage'),
('Supprimer un statut de transfert', 'backoffice/transfer-statuses/delete'),

-- Transferts de dossiers
('Consulter les transferts de dossiers', 'backoffice/transfers'),
('Créer un transfert de dossier', 'backoffice/transfers/create'),
('Consulter le détail d''un transfert de dossier', 'backoffice/transfers/show'),
('Traiter un transfert de dossier', 'backoffice/transfers/process'),
('Réceptionner un transfert de dossier', 'backoffice/transfers/receive'),
('Transférer un dossier', 'backoffice/transfers/transfer'),
('Gérer les transferts de dossiers', 'backoffice/transfers/manage'),
('Supprimer un transfert de dossier', 'backoffice/transfers/delete'),

-- Notifications
('Consulter les notifications des plaignants', 'backoffice/notifications/complainants'),
('Consulter le détail d''une notification plaignant', 'backoffice/notifications/complainants/show'),
('Renvoyer une notification plaignant', 'backoffice/notifications/complainants/resend'),
('Gérer les notifications des plaignants', 'backoffice/notifications/complainants/manage'),
('Supprimer une notification plaignant', 'backoffice/notifications/complainants/delete'),
('Consulter les notifications des utilisateurs', 'backoffice/notifications/users'),
('Consulter le détail d''une notification utilisateur', 'backoffice/notifications/users/show'),
('Renvoyer une notification utilisateur', 'backoffice/notifications/users/resend'),
('Gérer les notifications des utilisateurs', 'backoffice/notifications/users/manage'),
('Supprimer une notification utilisateur', 'backoffice/notifications/users/delete'),

-- Journaux système
('Consulter les journaux système des plaignants', 'backoffice/system-logs/complainants'),
('Consulter le détail d''un journal plaignant', 'backoffice/system-logs/complainants/show'),
('Gérer les journaux système des plaignants', 'backoffice/system-logs/complainants/manage'),
('Supprimer un journal système plaignant', 'backoffice/system-logs/complainants/delete'),
('Consulter les journaux système des utilisateurs', 'backoffice/system-logs/users'),
('Consulter le détail d''un journal utilisateur', 'backoffice/system-logs/users/show'),
('Gérer les journaux système des utilisateurs', 'backoffice/system-logs/users/manage'),
('Supprimer un journal système utilisateur', 'backoffice/system-logs/users/delete');

INSERT INTO administration.permission (description_permission, url_route, is_active)
SELECT t.description_permission, t.url_route, TRUE
FROM tmp_bo_permissions t
WHERE NOT EXISTS (
    SELECT 1
    FROM administration.permission p
    WHERE LOWER(p.url_route) = LOWER(t.url_route)
);

UPDATE administration.permission p
SET description_permission = t.description_permission,
    is_active = TRUE
FROM tmp_bo_permissions t
WHERE LOWER(p.url_route) = LOWER(t.url_route)
  AND (
      p.description_permission IS DISTINCT FROM t.description_permission
      OR COALESCE(p.is_active, FALSE) = FALSE
  );

INSERT INTO administration.profil_permission (profil_id, permission_id, is_active)
SELECT 1, p.permission_id, TRUE
FROM administration.permission p
WHERE NOT EXISTS (
    SELECT 1
    FROM administration.profil_permission pp
    WHERE pp.profil_id = 1
      AND pp.permission_id = p.permission_id
);

UPDATE administration.profil_permission
SET is_active = TRUE
WHERE profil_id = 1
  AND COALESCE(is_active, FALSE) = FALSE;

COMMIT;

SELECT COUNT(*) AS permission_total FROM administration.permission;
SELECT COUNT(*) AS admin_assignments
FROM administration.profil_permission
WHERE profil_id = 1 AND is_active = TRUE;
