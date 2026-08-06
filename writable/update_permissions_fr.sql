-- Update all permission descriptions to French (idempotent by url_route).

BEGIN;

UPDATE administration.permission AS p
SET description_permission = v.description_permission
FROM (VALUES
    ('backoffice', 'Consulter l''accueil du back-office'),

    -- Tableaux de bord
    ('backoffice/dashboards/executive', 'Consulter le tableau de bord exécutif'),
    ('backoffice/dashboards/complaints', 'Consulter le tableau de bord des plaintes'),
    ('backoffice/dashboards/complainants', 'Consulter le tableau de bord des plaignants'),
    ('backoffice/dashboards/appeals', 'Consulter le tableau de bord des recours'),
    ('backoffice/dashboards/summons', 'Consulter le tableau de bord des convocations'),
    ('backoffice/dashboards/hearings', 'Consulter le tableau de bord des audiences'),
    ('backoffice/dashboards/notifications', 'Consulter le tableau de bord des notifications'),
    ('backoffice/dashboards/courts', 'Consulter le tableau de bord des juridictions'),

    -- Utilisateurs
    ('backoffice/users', 'Consulter les utilisateurs'),
    ('backoffice/users/create', 'Créer un utilisateur'),
    ('backoffice/users/show', 'Consulter le détail d''un utilisateur'),
    ('backoffice/users/edit', 'Modifier un utilisateur'),
    ('backoffice/users/toggle-status', 'Activer/désactiver un utilisateur'),
    ('backoffice/users/delete', 'Supprimer un utilisateur'),
    ('backoffice/users/manage', 'Gérer les utilisateurs'),

    -- Profils
    ('backoffice/profiles', 'Consulter les profils'),
    ('backoffice/profiles/create', 'Créer un profil'),
    ('backoffice/profiles/show', 'Consulter le détail d''un profil'),
    ('backoffice/profiles/edit', 'Modifier un profil'),
    ('backoffice/profiles/toggle-status', 'Activer/désactiver un profil'),
    ('backoffice/profiles/assign', 'Affecter des permissions à un profil'),
    ('backoffice/profiles/delete', 'Supprimer un profil'),
    ('backoffice/profiles/manage', 'Gérer les profils'),

    -- Permissions
    ('backoffice/permissions', 'Consulter les permissions'),
    ('backoffice/permissions/create', 'Créer une permission'),
    ('backoffice/permissions/edit', 'Modifier une permission'),
    ('backoffice/permissions/toggle-status', 'Activer/désactiver une permission'),
    ('backoffice/permissions/delete', 'Supprimer une permission'),
    ('backoffice/permissions/manage', 'Gérer les permissions'),

    -- Juridictions
    ('backoffice/court-jurisdictions', 'Consulter les juridictions'),
    ('backoffice/court-jurisdictions/create', 'Créer une juridiction'),
    ('backoffice/court-jurisdictions/show', 'Consulter le détail d''une juridiction'),
    ('backoffice/court-jurisdictions/edit', 'Modifier une juridiction'),
    ('backoffice/court-jurisdictions/toggle-status', 'Activer/désactiver une juridiction'),
    ('backoffice/court-jurisdictions/delete', 'Supprimer une juridiction'),
    ('backoffice/court-jurisdictions/manage', 'Gérer les juridictions'),

    -- Configuration des juridictions
    ('backoffice/court-jurisdiction-configs', 'Consulter les configurations de juridictions'),
    ('backoffice/court-jurisdiction-configs/create', 'Créer une configuration de juridiction'),
    ('backoffice/court-jurisdiction-configs/edit', 'Modifier une configuration de juridiction'),
    ('backoffice/court-jurisdiction-configs/toggle-status', 'Activer/désactiver une configuration de juridiction'),
    ('backoffice/court-jurisdiction-configs/delete', 'Supprimer une configuration de juridiction'),
    ('backoffice/court-jurisdiction-configs/manage', 'Gérer les configurations de juridictions'),

    -- Niveaux de juridiction
    ('backoffice/jurisdiction-levels', 'Consulter les niveaux de juridiction'),
    ('backoffice/jurisdiction-levels/create', 'Créer un niveau de juridiction'),
    ('backoffice/jurisdiction-levels/edit', 'Modifier un niveau de juridiction'),
    ('backoffice/jurisdiction-levels/toggle-status', 'Activer/désactiver un niveau de juridiction'),
    ('backoffice/jurisdiction-levels/delete', 'Supprimer un niveau de juridiction'),
    ('backoffice/jurisdiction-levels/manage', 'Gérer les niveaux de juridiction'),

    -- Configuration des niveaux
    ('backoffice/jurisdiction-level-configs', 'Consulter les configurations de niveaux de juridiction'),
    ('backoffice/jurisdiction-level-configs/create', 'Créer une configuration de niveau de juridiction'),
    ('backoffice/jurisdiction-level-configs/edit', 'Modifier une configuration de niveau de juridiction'),
    ('backoffice/jurisdiction-level-configs/toggle-status', 'Activer/désactiver une configuration de niveau de juridiction'),
    ('backoffice/jurisdiction-level-configs/delete', 'Supprimer une configuration de niveau de juridiction'),
    ('backoffice/jurisdiction-level-configs/manage', 'Gérer les configurations de niveaux de juridiction'),

    -- Personnes (plaignants)
    ('backoffice/people', 'Consulter les personnes'),
    ('backoffice/people/create', 'Créer une personne'),
    ('backoffice/people/show', 'Consulter le détail d''une personne'),
    ('backoffice/people/edit', 'Modifier une personne'),
    ('backoffice/people/delete', 'Supprimer une personne'),
    ('backoffice/people/manage', 'Gérer les personnes'),

    -- Étapes de plainte
    ('backoffice/complaint-stages', 'Consulter les étapes de plainte'),
    ('backoffice/complaint-stages/create', 'Créer une étape de plainte'),
    ('backoffice/complaint-stages/edit', 'Modifier une étape de plainte'),
    ('backoffice/complaint-stages/toggle-status', 'Activer/désactiver une étape de plainte'),
    ('backoffice/complaint-stages/assign', 'Affecter des profils à une étape de plainte'),
    ('backoffice/complaint-stages/delete', 'Supprimer une étape de plainte'),
    ('backoffice/complaint-stages/manage', 'Gérer les étapes de plainte'),

    -- Configuration des étapes
    ('backoffice/complaint-stage-configs', 'Consulter les configurations d''étapes de plainte'),
    ('backoffice/complaint-stage-configs/create', 'Créer une configuration d''étape de plainte'),
    ('backoffice/complaint-stage-configs/edit', 'Modifier une configuration d''étape de plainte'),
    ('backoffice/complaint-stage-configs/toggle-status', 'Activer/désactiver une configuration d''étape de plainte'),
    ('backoffice/complaint-stage-configs/delete', 'Supprimer une configuration d''étape de plainte'),
    ('backoffice/complaint-stage-configs/manage', 'Gérer les configurations d''étapes de plainte'),

    -- Statuts de plainte
    ('backoffice/complaint-statuses', 'Consulter les statuts de plainte'),
    ('backoffice/complaint-statuses/create', 'Créer un statut de plainte'),
    ('backoffice/complaint-statuses/edit', 'Modifier un statut de plainte'),
    ('backoffice/complaint-statuses/toggle-status', 'Activer/désactiver un statut de plainte'),
    ('backoffice/complaint-statuses/delete', 'Supprimer un statut de plainte'),
    ('backoffice/complaint-statuses/manage', 'Gérer les statuts de plainte'),

    -- Types de documents
    ('backoffice/document-types', 'Consulter les types de documents'),
    ('backoffice/document-types/create', 'Créer un type de document'),
    ('backoffice/document-types/edit', 'Modifier un type de document'),
    ('backoffice/document-types/toggle-status', 'Activer/désactiver un type de document'),
    ('backoffice/document-types/delete', 'Supprimer un type de document'),
    ('backoffice/document-types/manage', 'Gérer les types de documents'),

    -- Plaintes
    ('backoffice/complaints', 'Consulter les plaintes'),
    ('backoffice/complaints/create', 'Créer une plainte'),
    ('backoffice/complaints/show', 'Consulter le détail d''une plainte'),
    ('backoffice/complaints/edit', 'Modifier une plainte'),
    ('backoffice/complaints/transfer', 'Transférer une plainte'),
    ('backoffice/complaints/process', 'Traiter une plainte'),
    ('backoffice/complaints/delete', 'Supprimer une plainte'),
    ('backoffice/complaints/manage', 'Gérer les plaintes'),

    -- Recours
    ('backoffice/appeals', 'Consulter les recours'),
    ('backoffice/appeals/create', 'Créer un recours'),
    ('backoffice/appeals/show', 'Consulter le détail d''un recours'),
    ('backoffice/appeals/edit', 'Modifier un recours'),
    ('backoffice/appeals/process', 'Traiter un recours'),
    ('backoffice/appeals/delete', 'Supprimer un recours'),
    ('backoffice/appeals/manage', 'Gérer les recours'),

    -- Convocations
    ('backoffice/summons', 'Consulter les convocations'),
    ('backoffice/summons/pending', 'Consulter les convocations en attente'),
    ('backoffice/summons/create', 'Créer une convocation'),
    ('backoffice/summons/show', 'Consulter le détail d''une convocation'),
    ('backoffice/summons/generate', 'Générer une convocation'),
    ('backoffice/summons/process', 'Traiter une convocation'),
    ('backoffice/summons/delete', 'Supprimer une convocation'),
    ('backoffice/summons/manage', 'Gérer les convocations'),

    -- Statuts de convocation
    ('backoffice/summons-statuses', 'Consulter les statuts de convocation'),
    ('backoffice/summons-statuses/create', 'Créer un statut de convocation'),
    ('backoffice/summons-statuses/edit', 'Modifier un statut de convocation'),
    ('backoffice/summons-statuses/delete', 'Supprimer un statut de convocation'),
    ('backoffice/summons-statuses/manage', 'Gérer les statuts de convocation'),

    -- Audiences
    ('backoffice/hearings', 'Consulter les audiences'),
    ('backoffice/hearings/create', 'Créer une audience'),
    ('backoffice/hearings/show', 'Consulter le détail d''une audience'),
    ('backoffice/hearings/assign', 'Affecter le personnel à une audience'),
    ('backoffice/hearings/assignments/toggle-status', 'Activer/désactiver une affectation d''audience'),
    ('backoffice/hearings/process', 'Traiter une audience'),
    ('backoffice/hearings/delete', 'Supprimer une audience'),
    ('backoffice/hearings/manage', 'Gérer les audiences'),

    -- Statuts d'audience
    ('backoffice/hearing-statuses', 'Consulter les statuts d''audience'),
    ('backoffice/hearing-statuses/create', 'Créer un statut d''audience'),
    ('backoffice/hearing-statuses/edit', 'Modifier un statut d''audience'),
    ('backoffice/hearing-statuses/delete', 'Supprimer un statut d''audience'),
    ('backoffice/hearing-statuses/manage', 'Gérer les statuts d''audience'),

    -- Types de verdict
    ('backoffice/verdict-types', 'Consulter les types de verdict'),
    ('backoffice/verdict-types/create', 'Créer un type de verdict'),
    ('backoffice/verdict-types/edit', 'Modifier un type de verdict'),
    ('backoffice/verdict-types/delete', 'Supprimer un type de verdict'),
    ('backoffice/verdict-types/manage', 'Gérer les types de verdict'),

    -- Verdicts
    ('backoffice/verdicts', 'Consulter les verdicts'),
    ('backoffice/verdicts/create', 'Créer un verdict'),
    ('backoffice/verdicts/show', 'Consulter le détail d''un verdict'),
    ('backoffice/verdicts/generate', 'Générer un verdict'),
    ('backoffice/verdicts/process', 'Traiter un verdict'),
    ('backoffice/verdicts/delete', 'Supprimer un verdict'),
    ('backoffice/verdicts/manage', 'Gérer les verdicts'),

    -- Statuts de transfert
    ('backoffice/transfer-statuses', 'Consulter les statuts de transfert'),
    ('backoffice/transfer-statuses/create', 'Créer un statut de transfert'),
    ('backoffice/transfer-statuses/edit', 'Modifier un statut de transfert'),
    ('backoffice/transfer-statuses/delete', 'Supprimer un statut de transfert'),
    ('backoffice/transfer-statuses/manage', 'Gérer les statuts de transfert'),

    -- Transferts de dossiers
    ('backoffice/transfers', 'Consulter les transferts de dossiers'),
    ('backoffice/transfers/create', 'Créer un transfert de dossier'),
    ('backoffice/transfers/show', 'Consulter le détail d''un transfert de dossier'),
    ('backoffice/transfers/process', 'Traiter un transfert de dossier'),
    ('backoffice/transfers/receive', 'Réceptionner un transfert de dossier'),
    ('backoffice/transfers/transfer', 'Transférer un dossier'),
    ('backoffice/transfers/delete', 'Supprimer un transfert de dossier'),
    ('backoffice/transfers/manage', 'Gérer les transferts de dossiers'),

    -- Notifications plaignants
    ('backoffice/notifications/complainants', 'Consulter les notifications des plaignants'),
    ('backoffice/notifications/complainants/show', 'Consulter le détail d''une notification plaignant'),
    ('backoffice/notifications/complainants/resend', 'Renvoyer une notification plaignant'),
    ('backoffice/notifications/complainants/delete', 'Supprimer une notification plaignant'),
    ('backoffice/notifications/complainants/manage', 'Gérer les notifications des plaignants'),

    -- Notifications utilisateurs
    ('backoffice/notifications/users', 'Consulter les notifications des utilisateurs'),
    ('backoffice/notifications/users/show', 'Consulter le détail d''une notification utilisateur'),
    ('backoffice/notifications/users/resend', 'Renvoyer une notification utilisateur'),
    ('backoffice/notifications/users/delete', 'Supprimer une notification utilisateur'),
    ('backoffice/notifications/users/manage', 'Gérer les notifications des utilisateurs'),

    -- Journaux système plaignants
    ('backoffice/system-logs/complainants', 'Consulter les journaux système des plaignants'),
    ('backoffice/system-logs/complainants/show', 'Consulter le détail d''un journal plaignant'),
    ('backoffice/system-logs/complainants/delete', 'Supprimer un journal système plaignant'),
    ('backoffice/system-logs/complainants/manage', 'Gérer les journaux système des plaignants'),

    -- Journaux système utilisateurs
    ('backoffice/system-logs/users', 'Consulter les journaux système des utilisateurs'),
    ('backoffice/system-logs/users/show', 'Consulter le détail d''un journal utilisateur'),
    ('backoffice/system-logs/users/delete', 'Supprimer un journal système utilisateur'),
    ('backoffice/system-logs/users/manage', 'Gérer les journaux système des utilisateurs')
) AS v(url_route, description_permission)
WHERE LOWER(p.url_route) = LOWER(v.url_route);

COMMIT;

SELECT COUNT(*) AS total,
       COUNT(*) FILTER (WHERE description_permission ~ '[A-Za-z].*[àâäéèêëïîôùûüçÀÂÄÉÈÊËÏÎÔÙÛÜÇ]|Consulter|Créer|Modifier|Supprimer|Gérer|Traiter|Affecter|Transférer|Générer|Réceptionner|Activer|Renvoyer') AS french_like
FROM administration.permission;

SELECT permission_id, description_permission, url_route
FROM administration.permission
ORDER BY url_route
LIMIT 20;
