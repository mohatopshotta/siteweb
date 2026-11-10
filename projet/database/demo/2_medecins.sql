-- ============================================================================
-- Données de démonstration : 15 MÉDECINS (fichier de Younes)
-- À importer APRÈS hsp_gdh.sql et APRÈS 1_etudiants.sql (les établissements 1 à 5 doivent exister).
--
-- Comptes FICTIFS, pour tester en local. NE PAS importer sur le site mis en ligne.
-- Mot de passe commun de tous les comptes de démo : Demo-Hsp2026!x
-- Numéros utilisés : utilisateur 200 à 214 (un médecin = une ligne utilisateur + une ligne medecin).
-- On peut réimporter ce fichier : il efface d'abord les anciens médecins de démo.
-- ============================================================================

SET NAMES utf8mb4;

DELETE FROM `utilisateur` WHERE `id_utilisateur` BETWEEN 200 AND 214;

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `valide`, `id_validateur`, `date_inscription`, `date_derniere_connexion`, `date_consentement`) VALUES
(200, 'Lefèvre', 'Camille', 'camille.lefevre@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-01 09:00:00', NULL, '2026-09-01 09:00:00'),
(201, 'Moreau', 'Antoine', 'antoine.moreau@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-02 09:07:00', NULL, '2026-09-02 09:07:00'),
(202, 'Bernard', 'Sophie', 'sophie.bernard@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-03 09:14:00', NULL, '2026-09-03 09:14:00'),
(203, 'Dubois', 'Thomas', 'thomas.dubois@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-04 09:21:00', NULL, '2026-09-04 09:21:00'),
(204, 'Laurent', 'Élodie', 'elodie.laurent@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-05 09:28:00', NULL, '2026-09-05 09:28:00'),
(205, 'Girard', 'Nicolas', 'nicolas.girard@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-06 09:35:00', NULL, '2026-09-06 09:35:00'),
(206, 'Roux', 'Amélie', 'amelie.roux@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-07 09:42:00', NULL, '2026-09-07 09:42:00'),
(207, 'Fournier', 'Julien', 'julien.fournier@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-08 09:49:00', NULL, '2026-09-08 09:49:00'),
(208, 'Mercier', 'Laura', 'laura.mercier@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-09 09:56:00', NULL, '2026-09-09 09:56:00'),
(209, 'Blanc', 'Hugo', 'hugo.blanc@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-10 09:03:00', NULL, '2026-09-10 09:03:00'),
(210, 'Chevalier', 'Inès', 'ines.chevalier@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-11 09:10:00', NULL, '2026-09-11 09:10:00'),
(211, 'Perrin', 'Maxime', 'maxime.perrin@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-12 09:17:00', NULL, '2026-09-12 09:17:00'),
(212, 'Lambert', 'Clara', 'clara.lambert@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-13 09:24:00', NULL, '2026-09-13 09:24:00'),
(213, 'Faure', 'Pierre', 'pierre.faure@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-14 09:31:00', NULL, '2026-09-14 09:31:00'),
(214, 'Garnier', 'Manon', 'manon.garnier@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-15 09:38:00', NULL, '2026-09-15 09:38:00');

INSERT INTO `medecin` (`id_utilisateur`, `id_specialite`, `id_hopital`, `id_etablissement`) VALUES
(200, 1, 1, 1),
(201, 2, 1, NULL),
(202, 5, 2, 2),
(203, 4, 3, NULL),
(204, 6, 4, 3),
(205, 12, 5, NULL),
(206, 20, 6, 4),
(207, 17, 7, NULL),
(208, 11, 8, 5),
(209, 18, 9, NULL),
(210, 3, 1, 1),
(211, 7, 2, NULL),
(212, 10, 3, 2),
(213, 14, 4, NULL),
(214, 9, 5, 3);
