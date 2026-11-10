-- ============================================================================
-- Données de démonstration : 5 ÉTABLISSEMENTS + 15 ÉTUDIANTS (fichier 1, repris par Younes)
-- À importer APRÈS hsp_gdh.sql, et AVANT 2_medecins.sql (les médecins utilisent les établissements 1 à 5).
--
-- Comptes FICTIFS, pour tester en local. NE PAS importer sur le site mis en ligne.
-- Mot de passe commun de tous les comptes de démo : Demo-Hsp2026!x
-- Numéros utilisés : établissements 1 à 5, utilisateurs 100 à 114 (un étudiant = une ligne utilisateur + une ligne etudiant).
-- On peut réimporter ce fichier : il efface d'abord les anciens étudiants de démo et met à jour les établissements 1 à 5.
-- Les CV sont vides (aucun fichier PDF n'existe pour ces comptes).
-- ============================================================================

SET NAMES utf8mb4;

DELETE FROM `utilisateur` WHERE `id_utilisateur` BETWEEN 100 AND 114;

INSERT INTO `etablissement` (`id_etablissement`, `nom`, `adresse`, `site_web`) VALUES
(1, 'Université Santé Île-de-France', '12 avenue de la Santé, 94000 Créteil', 'https://www.univ-sante-idf.test'),
(2, 'École de Médecine de Lyon-Est', '45 rue des Hôpitaux, 69008 Lyon', 'https://www.medecine-lyon-est.test'),
(3, 'Faculté des Sciences Médicales de Bordeaux', '8 cours de la Faculté, 33000 Bordeaux', 'https://www.fsm-bordeaux.test'),
(4, 'Institut de Formation en Soins de Nantes', '3 quai Jean Monnet, 44000 Nantes', 'https://www.ifs-nantes.test'),
(5, 'Université de Médecine de Strasbourg-Rhin', '21 boulevard de l''Université, 67000 Strasbourg', 'https://www.medecine-strasbourg-rhin.test')
ON DUPLICATE KEY UPDATE `nom` = VALUES(`nom`), `adresse` = VALUES(`adresse`), `site_web` = VALUES(`site_web`);

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `valide`, `id_validateur`, `date_inscription`, `date_derniere_connexion`, `date_consentement`) VALUES
(100, 'Martin', 'Léa', 'lea.martin@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-01 14:00:00', NULL, '2026-09-01 14:00:00'),
(101, 'Petit', 'Lucas', 'lucas.petit@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-02 14:11:00', NULL, '2026-09-02 14:11:00'),
(102, 'Durand', 'Emma', 'emma.durand@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-03 14:22:00', NULL, '2026-09-03 14:22:00'),
(103, 'Leroy', 'Hugo', 'hugo.leroy@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-04 14:33:00', NULL, '2026-09-04 14:33:00'),
(104, 'Simon', 'Chloé', 'chloe.simon@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-05 14:44:00', NULL, '2026-09-05 14:44:00'),
(105, 'Michel', 'Louis', 'louis.michel@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-06 14:55:00', NULL, '2026-09-06 14:55:00'),
(106, 'Garcia', 'Jade', 'jade.garcia@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-07 14:06:00', NULL, '2026-09-07 14:06:00'),
(107, 'David', 'Arthur', 'arthur.david@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-08 14:17:00', NULL, '2026-09-08 14:17:00'),
(108, 'Bertrand', 'Alice', 'alice.bertrand@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-09 14:28:00', NULL, '2026-09-09 14:28:00'),
(109, 'Morel', 'Nathan', 'nathan.morel@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-10 14:39:00', NULL, '2026-09-10 14:39:00'),
(110, 'Fontaine', 'Zoé', 'zoe.fontaine@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-11 14:50:00', NULL, '2026-09-11 14:50:00'),
(111, 'Rousseau', 'Adam', 'adam.rousseau@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-12 14:01:00', NULL, '2026-09-12 14:01:00'),
(112, 'Vincent', 'Lina', 'lina.vincent@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-13 14:12:00', NULL, '2026-09-13 14:12:00'),
(113, 'Muller', 'Théo', 'theo.muller@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-14 14:23:00', NULL, '2026-09-14 14:23:00'),
(114, 'Lopez', 'Sarah', 'sarah.lopez@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-15 14:34:00', NULL, '2026-09-15 14:34:00');

INSERT INTO `etudiant` (`id_utilisateur`, `id_etablissement`, `cv`, `formation`) VALUES
(100, 1, NULL, 'Médecine – 2e année'),
(101, 2, NULL, 'Médecine – 4e année'),
(102, 3, NULL, 'Médecine – 6e année'),
(103, 4, NULL, 'Soins infirmiers – 3e année'),
(104, 5, NULL, 'Pharmacie – 3e année'),
(105, 1, NULL, 'Kinésithérapie – 2e année'),
(106, 2, NULL, 'Maïeutique (sage-femme) – 1re année'),
(107, 3, NULL, 'Biologie médicale – Master 1'),
(108, 4, NULL, 'Médecine générale – Internat'),
(109, 5, NULL, 'Médecine – 2e année'),
(110, 1, NULL, 'Médecine – 4e année'),
(111, 2, NULL, 'Médecine – 6e année'),
(112, 3, NULL, 'Soins infirmiers – 3e année'),
(113, 4, NULL, 'Pharmacie – 3e année'),
(114, 5, NULL, 'Kinésithérapie – 2e année');
