Database Structure

The database is organized into multiple schemas, each responsible for a specific functional area of the application.

A. Schema: administration

1. Users Management : The administration.utilisateur table stores all Back Office user accounts.

SELECT utilisateur_id, nom_utilisateur, prenom_utilisateur, numero_cni, numero_matricule, telephone, email, date_naissance, profil_id, statut_compte_id, juridiction_id, sexe_id, province_naissance_id, commune_naissance_id, zone_naissance_id, colline_naissance_id, user_name, mot_de_passe_hash, derniere_connexion, created_at, updated_at, code_authentification FROM administration.utilisateur;

This table manages:User accounts,Personal information,Authentication credentials,Assigned profile (role),Assigned court jurisdiction,Account status,Two-Factor Authentication (2FA),Audit timestamps

2. Profiles (Roles) : The administration.profil table manages user profiles (roles).

SELECT profil_id, code_profil, libelle_profil, description_profil, created_at, is_active FROM administration.profil;


Each profile defines a set of permissions assigned to users.

3. Permissions : The administration.permission table stores all application permissions.

SELECT permission_id, description_permission, url_route, is_active FROM administration.permission;

Each permission is associated with one application routes.

4. Profile–Permission Mapping : The administration.profil_permission table links profiles (roles) with permissions.

SELECT profil_permission_id, profil_id, permission_id, is_active FROM administration.profil_permission;

This table defines which permissions belong to each profile.

5. Account Status : The administration.statut_compte table stores the available account statuses.

SELECT statut_compte_id, desc_statut_compte FROM administration.statut_compte;


Examples include: Active, Inactive, Suspended, Locked

---
B. Schema: audit_log

1. Back Office User Audit Logs : The audit_log.audit_log table records every action performed by Back Office users.

SELECT audit_log_id, utilisateur_id, action, table_cible, enregistrement_id, anciennes_valeurs, nouvelles_valeurs, adresse_ip, user_agent, created_at FROM audit_log.audit_log;


Each audit record includes: User, Action performed, Target table, Record identifier, Previous values, New values, IP address, Browser/User Agent, Timestamp

2. Complainant Audit Logs : The audit_log.audit_log_personne table records actions performed by complainants.

SELECT audit_log_personne_id, personne_id, action, table_cible, enregistrement_id, anciennes_valeurs, nouvelles_valeurs, adresse_ip, user_agent, created_at FROM audit_log.audit_log_personne;

This table provides a complete audit trail of complainant activities.

---
C. Schema: juridiction

1. Court Jurisdiction Levels : The juridiction.niveau_juridiction table manages the different jurisdiction levels.

SELECT niveau_juridiction_id, desc_niveau_juridiction, is_active, is_recours FROM juridiction.niveau_juridiction;


Examples include: Communal Court, Provincial Court, Regional Court, Ministry of Justice

The is_recours field indicates whether the jurisdiction level corresponds to an appeal level.

2. Court Jurisdictions : The juridiction.juridiction table stores all courts and judicial institutions.

SELECT juridiction_id, code_juridiction, nom_juridiction, niveau_juridiction_id, adresse, telephone, email, province_id, commune_id, zone_id, colline_id, is_active, created_at, est_dernier FROM juridiction.juridiction;


Each jurisdiction contains: Court code, Court name, Jurisdiction level, Contact information, Geographic location, Active status, Creation date

Indicator showing whether it is the final jurisdiction in the judicial hierarchy (est_dernier)

3. Jurisdiction Level Configuration : The juridiction.configuration_niveau_juridiction table defines the hierarchy between jurisdiction levels.

SELECT configuration_niveau_juridiction_id, niveau_juridiction_id, niveau_juridiction_parent_id, is_active FROM juridiction.configuration_niveau_juridiction;

This configuration specifies the parent-child relationships between jurisdiction levels and defines the appeal flow.

4. Court Jurisdiction Configuration : The juridiction.configuration_juridiction table defines the hierarchical relationships between individual courts.

SELECT configuration_juridiction_id, juridiction_id, juridiction_parent_id, is_active FROM juridiction.configuration_juridiction;

This table determines the appeal path between specific courts, enabling the system to identify the appropriate parent jurisdiction when a complaint is appealed.


D. Schema: localite

1. Provinces Management : The localite.localite_province table stores all provinces.

SELECT province_id, province_name, province_latitude, province_longitude, is_active FROM localite.localite_province;

This table contains the list of all provinces along with their geographic coordinates and active status.

2. Communes Management : The localite.localite_commune table stores all communes.

SELECT commune_id, commune_name, province_id, commune_latitude, commune_longitude FROM localite.localite_commune;

Each commune belongs to a province through the province_id foreign key.

3. Zones Management : The localite.localite_zone table stores all administrative zones.

SELECT zone_id, zone_name, commune_id, zone_latitude, zone_longitude FROM localite.localite_zone;

Each zone belongs to a commune through the commune_id foreign key.

4. Collines Management : The localite.localite_colline table stores all collines (hills).

SELECT colline_id, colline_name, zone_id, colline_latitude, colline_longitude FROM localite.localite_colline;

Each colline belongs to a zone through the zone_id foreign key.

---
E. Schema: notification

1. Notification Channels : The notification.canal_notification table stores all available notification channels.

SELECT canal_notification_id, description_canal_notification, is_active FROM notification.canal_notification;

Examples of notification channels include: Email

2. Notification Statuses : The notification.statut_notification table stores the available notification statuses.

SELECT statut_notification_id, description_statut_notification FROM notification.statut_notification;

3. Complainant Notifications : The notification.notification_personne table stores notifications sent to complainants.

SELECT notification_personne_id, personne_id, canal_notification_id, sujet, corps, plainte_id, statut_notification_id, envoye_le, lu_le, created_at FROM notification.notification_personne;


Each notification includes: Recipient (Complainant), Notification Channel, Subject, Message Body, Related Complaint, Notification Status, Sent Date, Read Date, Creation Date

4. Back Office User Notifications : The notification.notification_utilisateur table stores notifications sent to Back Office users.

SELECT notification_utilisateur_id, utilisateur_id, canal_notification_id, sujet, corps, statut_notification_id, envoye_le, lu_le, created_at FROM notification.notification_utilisateur;

This table records all notifications delivered to system users.

---
F. Schema: plaignant

1. Complainants and Defendants Management :The plaignant.personne table stores all individuals involved in judicial cases, including complainants and defendants.

SELECT personne_id, nom_personne, prenom_personne, date_naissance, email, telephone, user_name, mot_de_passe_hash, code_authentification, province_naissance_id, commune_naissance_id, zone_naissance_id, colline_naissance_id, numero_cni, upload_cni, create_at, sexe_id, adresse_residence, code_authentification_expire_at FROM plaignant.personne;


This table manages: Personal information, Contact information, Login credentials, Two-Factor Authentication (2FA), National ID information, Place of birth, Residential address, Identity document upload

2. Complaint Participant Management : The plaignant.plainte_role_personne table defines the relationship between a person and a complaint.

SELECT plainte_role_personne_id, plainte_id, personne_id, role_personne_id, est_recourant, utilisateur_id, date_ajout, created_at FROM plaignant.plainte_role_personne;


This table specifies: Which person is associated with a complaint, The person's role in the complaint, Whether the person is the appellant (est_recourant), The user who registered the relationship, The assignment date, The creation timestamp

3. Person Roles : The plaignant.role_personne table defines the possible roles a person can have in a complaint.

SELECT role_personne_id, description_role_personne FROM plaignant.role_personne;

Typical roles include: Complainant, Defendant, Witness, Legal Representative, Appellant


G. Schema: plainte (Complaints)

1. Complaints Management : The plainte.plainte table stores all complaints registered in the system.

SELECT plainte_id, numero_dossier, juridiction_id, niveau_juridiction_id, statut_plainte_id, objet, description, date_depot, enregistre_par, created_at, updated_at, etape_plainte_id, est_cree_par_plaigant, is_recours FROM plainte.plainte;


This table manages: Complaint registration, Case number, Assigned court jurisdiction, Jurisdiction level, Complaint status, Complaint stage, Complaint subject, Complaint description, Submission date, User who registered the complaint, Creation and update timestamps, Whether the complaint was created by a complainant, Whether the record represents an appeal

2. Complaint Documents : The plainte.document_plainte table stores all documents associated with a complaint.

SELECT document_plainte_id, plainte_id, type_document_id, plainte_role_personne_id, nom_fichier, fichier_chemin_stockage, taille_octets, hash_sha256, niveau_juridiction_id, date_depot, depose_par_utilisateur, description, created_at FROM plainte.document_plainte;


Each document contains: Complaint, Document type, Related complainant/defendant, File name, Storage path, File size, SHA-256 hash, Jurisdiction level, Upload date, Uploaded by, Description, Creation date

3. Document Types : The plainte.type_document table defines the different document types required for complaints.

SELECT type_document_id, code_type_document, libelle_type_document, niveau_juridiction_id, is_obligatoire, is_actif, created_at FROM plainte.type_document;


Each document type specifies: Document code, Document name, Applicable jurisdiction level, Whether the document is mandatory, Active status

4. Complaint Statuses : The plainte.statut_plainte table stores the available complaint statuses.

SELECT statut_plainte_id, description_statut_plainte, is_active FROM plainte.statut_plainte;

5. Complaint Land Parcels : The plainte.plainte_parcelle table stores the land parcel(s) associated with each complaint.

SELECT plainte_parcelle_id, superficie_maitre_carreau, localisation_parcelle, plainte_id, province_parcelle_id, commune_parcelle_id, zone_parcelle_id, colline_parcelle_id, created_at FROM plainte.plainte_parcelle;


Each record contains: Complaint, Parcel area, Parcel location, Province, Commune, Zone, Colline, Creation date

6. Complaint Stages : The plainte.etape_plainte table defines the workflow stages of a complaint.

SELECT etape_plainte_id, description_etape_plainte, niveau_juridiction_id, is_active, is_convocation, is_audience FROM plainte.etape_plainte;


Each stage defines: Stage description, Jurisdiction level, Whether the stage is active, Whether it requires issuing a summons, Whether it requires scheduling a hearing

7. Complaint Stage Configuration : The plainte.configuration_etape_plainte table defines the complaint workflow.

SELECT configuration_etape_plainte_id, etape_plainte_actuel_id, etape_plainte_suivant_id, url_route, is_active FROM plainte.configuration_etape_plainte;


This table specifies: Current stage, Next stage, Route to execute, Active status

It controls the progression of complaints through the judicial process.

8. Complaint Stage–Profile Mapping : The plainte.etape_plainte_profil table defines which user profiles are authorized to perform each complaint stage.

SELECT etape_plainte_profil_id, etape_plainte_id, profil_id, is_active FROM plainte.etape_plainte_profil;

This table implements role-based access control for complaint workflow actions.

9. Complaint Types : The plainte.type_plainte table stores the available complaint categories.

SELECT type_plainte_id, description_type_plainte, is_active FROM plainte.type_plainte;

---
H. Schema: recours (Appeals)

1. Appeals Management : The recours.recours table stores all appeals submitted after a court decision.

SELECT recours_id, verdict_conteste_id, nouvelle_plainte_id, date_recours, dans_les_delais, enregistre_par, created_at, niveau_juridiction_id, plainte_parent_id, juridiction_id FROM recours.recours;

This table manages: Appealed verdict, New appeal case, Appeal submission date, Whether the appeal was filed within the legal deadline, User who registered the appeal, Jurisdiction level, Parent complaint, Court handling the appeal

2. Appeal Participants : The recours.recours_partie table links appeals to the individuals involved.

SELECT recours_partie_id, recours_id, role_personne_id, created_at FROM recours.recours_partie;

This table identifies the parties participating in each appeal and their respective roles.

---
I. Schema: transfert (Case Transfer)

1. Case Transfer Statuses : The transfert.statut_transfert_dossier table stores the available case transfer statuses.

SELECT statut_transfert_dossier_id, description_statut_transfert_dossier FROM transfert.statut_transfert_dossier;


Examples include: Pending, Sent, Received, Rejected, Completed

2. Case Transfers: The transfert.transfert_dossier table records all transfers of case files between court jurisdictions.

SELECT transfert_dossier_id, plainte_id, juridiction_source_id, juridiction_dest_id, numero_dossier_dest, date_transfert, transfere_par, recu_par, date_reception, statut_transfert_dossier_id, observations, created_at FROM transfert.transfert_dossier;


Each transfer record includes: Complaint being transferred, Source court jurisdiction, Destination court jurisdiction, Destination case number, Transfer date, User who initiated the transfer, User who received the case, Reception date, Transfer status, Remarks, Creation timestamp

This module ensures the complete traceability of case transfers between different court jurisdictions throughout the judicial process.

J. Schema: verdict

1. Verdict Types : The verdict.type_verdict table stores all available verdict types.

SELECT type_verdict_id, description_type_verdict FROM verdict.type_verdict;

This table defines the different categories of judicial decisions issued by the courts.

2. Verdict Management : The verdict.verdict table stores all judicial verdicts issued for complaints.

SELECT verdict_id, audience_plainte_id, niveau_juridiction_id, type_verdict_id, date_verdict, resume, dispositif, date_limite_recours, recours_exerce, created_at, juridiction_id, upload_rapport_verdict FROM verdict.verdict;


This table manages: Complaint associated with the verdict, Hearing during which the verdict was delivered, Jurisdiction level, Verdict type, Verdict date, Verdict summary, Court order (operative part of the judgment), Appeal deadline, Whether an appeal has been filed, Creation date

3. Verdict–Judge Assignment : The verdict.verdict_affectation_juge table links verdicts to the magistrates responsible for issuing them.

SELECT verdict_affectation_juge_id, verdict_id, utilisateur_id, profil_id FROM verdict.verdict_affectation_juge;

This table records: The verdict, The assigned magistrate, The magistrate's profile (role)

---
K. Schema: audience (Hearings)

1. Hearings Management : The audience.audience table stores all scheduled hearings.

SELECT audience_id, niveau_juridiction_id, date_audience, heure_audience, juridiction_audience_id, province_audience_id, commune_audience_id, zone_audience_id, colline_audience_id, lieu_audience, date_tenue, heure_debut, heure_fin,  statut_audience_id, motif_report, rapport, rapport_valide, created_at, updated_at FROM audience.audience;


This table manages: Jurisdiction level, Hearing date and time, Court jurisdiction, Hearing location, Actual hearing date, Start and end times, Presiding magistrate, Court clerk, Hearing status, Postponement reason, Hearing report, Report validation, Creation and update timestamps

2. Hearing–Complaint Relationship : The audience.audience_plainte table links hearings to complaints.

SELECT audience_plainte_id, audience_id, plainte_id, convocation_id, motif_report, rapport, rapport_valide, statut_audience_id, created_at FROM audience.audience_plainte;


Important Business Rule : A single hearing may involve multiple complaints. This table establishes the many-to-many relationship between hearings and complaints.

Each record contains: Hearing, Complaint, Related summons, Postponement reason, Hearing report, Report validation, Hearing status, Creation date

3. Hearing Documents : The audience.document_audience table stores documents submitted during a hearing.

SELECT document_audience_id, observation, audience_plainte_id, apporte_par_partie, enregistre_par, enregistre_le FROM audience.document_audience;


Each document records: Related hearing, Observations, Person who submitted the document, User who registered the document, Registration date

4. Attendance Management : The audience.presence_audience table records the attendance of all participants during a hearing.

SELECT presence_audience_id, audience_plainte_id, plainte_role_personne_id, utilisateur_id, present, observations, created_at, personne_id FROM audience.presence_audience;


This table records: Hearing, Complaint participant, Back Office user, Attendance status, Observations, Creation date, Person

5. Hearing Statuses : The audience.statut_audience table stores the available hearing statuses.

SELECT statut_audience_id, description_statut_audience FROM audience.statut_audience;

6. Hearing Assignments : The audience.audience_affection table manages the assignment of magistrates and court clerks to hearings.

SELECT audience_affection_id, audience_id, profil_id, utilisateur_affecte_id, utilisateur_id, is_active, create_at FROM audience.audience_affection;


This table manages: Hearing, Assigned profile (Magistrate or Court Clerk), Assigned user, User who performed the assignment, Assignment status, Assignment date

Business Rules : 
A hearing may have one or more assigned magistrates.
A hearing may have one or more assigned court clerks.
Assignments are made at the hearing level, not at the complaint level.
Since one hearing may involve multiple complaints, the assigned magistrates and court clerks are responsible for all complaints linked to that hearing.

---
L. Schema: convocation (Summons)

1. Summons Management : The convocation.convocation table stores all summons issued for hearings.

sql
SELECT convocation_id, plainte_id, niveau_juridiction_id, date_audience, heure_audience, province_lieu_audience_id, commune_lieu_audience_id, zone_lieu_audience_id, colline_lieu_audience_id, lieu_audience, emise_le, emise_par, statut_convocation_id, observations, created_at, juridiction_lieu_audience_id FROM convocation.convocation;


This table manages: Related complaint, Jurisdiction level, Hearing date and time, Hearing location, Issuing court, Date issued, Issued by, Summons status, Remarks, Creation date

2. Summons Recipients : The convocation.convocation_destinataire table links summons to the recipients concerned by the hearing.

SELECT convocation_destinataire_id, convocation_id, plainte_role_personne_id, date_remise, remis_par, statut_convocation_id, created_at FROM convocation.convocation_destinataire;


This table records: Summons, Complaint participant (complainant, defendant, witness, etc.), Date of delivery, User who delivered the summons, Summons status, Creation date

3. Summons Statuses : The convocation.statut_convocation table stores the available summons statuses.

SELECT statut_convocation_id, description_statut_convocation FROM convocation.statut_convocation;

Overall Database Architecture

The database is organized into 12 functional schemas, each responsible for a specific business domain:

1. administration – User, profile, permission, and account management.
2. audit_log – Audit trails for Back Office users and complainants.
3. juridiction – Court jurisdictions and judicial hierarchy.
4. localite – Administrative geographic data (Province, Commune, Zone, and Colline).
5. notification – Email, SMS, and in-app notification management.
6. plaignant – Complainants, defendants, and complaint participants.
7. plainte – Complaint lifecycle management.
8. recours – Appeals management.
9. transfert – Case transfer management between jurisdictions.
10. verdict – Judicial decisions and verdict management.
11. audience – Hearings, attendance, assignments, and hearing reports.
12. convocation – Summons management and recipient tracking.

This schema-based design provides a clean separation of concerns, supports a modular architecture in CodeIgniter 4, and ensures scalability, maintainability, security, and clear ownership of each business domain.
