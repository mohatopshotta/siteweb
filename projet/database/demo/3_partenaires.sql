-- ============================================================================
-- Données de démonstration : 3 ENTREPRISES PARTENAIRES + 4 PARTENAIRES + 7 OFFRES + candidatures (fichier 3, Ilyes)
-- À importer APRÈS hsp_gdh.sql, 1_etudiants.sql et 2_medecins.sql.
--
-- Entreprises et comptes FICTIFS, pour tester en local. NE PAS importer sur le site mis en ligne.
-- Mot de passe commun de tous les comptes de démo : Demo-Hsp2026!x
-- Numéros utilisés : entreprises 100 à 102, utilisateurs 300 à 303, offres 300 à 306.
-- On peut réimporter ce fichier : il efface d'abord les anciens partenaires de démo
-- (leurs offres et les candidatures à ces offres partent avec, grâce aux ON DELETE CASCADE).
-- Les candidatures utilisent les étudiants de démo 100 à 105 (fichier 1) :
-- si ce fichier n'a pas été importé, elles sont simplement ignorées (INSERT IGNORE).
-- ============================================================================

SET NAMES utf8mb4;

DELETE FROM `utilisateur` WHERE `id_utilisateur` BETWEEN 300 AND 303;

-- ---------- Entreprises partenaires ----------
INSERT INTO `entreprise` (`id_entreprise`, `nom`, `adresse`, `site_web`) VALUES
(100, 'MédiTech Solutions', '18 rue de l''Innovation, 92100 Boulogne-Billancourt', 'https://www.meditech-solutions.test'),
(101, 'PharmaNova', '5 avenue des Laboratoires, 69007 Lyon', 'https://www.pharmanova.test'),
(102, 'SantéConnect', '27 boulevard du Numérique, 75013 Paris', 'https://www.santeconnect.test')
ON DUPLICATE KEY UPDATE `nom` = VALUES(`nom`), `adresse` = VALUES(`adresse`), `site_web` = VALUES(`site_web`);

-- ---------- Comptes partenaires (déjà validés par l'admin n°1) ----------
INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `valide`, `id_validateur`, `date_inscription`, `date_derniere_connexion`, `date_consentement`) VALUES
(300, 'Benali', 'Karim', 'karim.benali@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-20 10:00:00', NULL, '2026-09-20 10:00:00'),
(301, 'Marchand', 'Julie', 'julie.marchand@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-21 10:15:00', NULL, '2026-09-21 10:15:00'),
(302, 'Nguyen', 'Paul', 'paul.nguyen@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-22 10:30:00', NULL, '2026-09-22 10:30:00'),
(303, 'Robert', 'Sarah', 'sarah.robert@demo-hsp.test', '$2y$10$3QfPmJ1dpksNmr9gheU5G.5BeRFWYUSfg8f69BGLzk9NxKSphS.9K', 1, 1, '2026-09-23 10:45:00', NULL, '2026-09-23 10:45:00');

INSERT INTO `partenaire` (`id_utilisateur`, `id_entreprise`, `poste`) VALUES
(300, 100, 'Responsable des ressources humaines'),
(301, 100, 'Chargée de recrutement'),
(302, 101, 'Directeur de la recherche'),
(303, 102, 'Cheffe de projet e-santé');

-- ---------- Offres publiées par les partenaires ----------
INSERT INTO `offre` (`id_offre`, `id_auteur`, `id_entreprise`, `titre`, `description`, `missions`, `salaire`, `type_offre`, `etat`, `date_publication`) VALUES
(300, 300, 100, 'Stage assistant ingénieur biomédical',
 'MédiTech Solutions conçoit du matériel de surveillance pour les services de réanimation. Nous cherchons un étudiant curieux pour rejoindre l''équipe technique.',
 'Participer aux tests des moniteurs patients. Rédiger les comptes rendus de tests. Accompagner les techniciens lors des installations à l''hôpital.',
 650.00, 'stage', 'ouvert', '2026-10-01 09:00:00'),
(301, 301, 100, 'Alternance chargé(e) de formation des soignants',
 'Formez les équipes soignantes à l''utilisation de nos équipements dans les hôpitaux du groupe GDH.',
 'Préparer les supports de formation. Animer des sessions auprès des infirmiers et des médecins. Recueillir les retours des utilisateurs.',
 1100.00, 'alternance', 'ouvert', '2026-10-05 09:00:00'),
(302, 302, 101, 'Stage en recherche clinique',
 'PharmaNova mène des essais cliniques en partenariat avec l''hôpital Sud Paris. Stage de 6 mois au sein de l''équipe recherche.',
 'Aider au suivi des patients inclus dans les essais. Saisir et contrôler les données. Préparer les dossiers pour le comité d''éthique.',
 700.00, 'stage', 'ouvert', '2026-10-10 09:00:00'),
(303, 302, 101, 'CDD attaché(e) de recherche clinique',
 'CDD de 12 mois pour renforcer le suivi d''un essai en cardiologie.',
 'Organiser les visites de suivi dans les centres. Vérifier la qualité des données recueillies. Faire le lien entre médecins investigateurs et laboratoire.',
 2400.00, 'cdd', 'ouvert', '2026-10-15 09:00:00'),
(304, 303, 102, 'Stage développeur application e-santé',
 'SantéConnect développe une application de suivi des patients après une hospitalisation.',
 'Participer au développement de nouvelles fonctionnalités. Tester l''application avec des soignants. Corriger les bugs remontés.',
 800.00, 'stage', 'ouvert', '2026-10-20 09:00:00'),
(305, 303, 102, 'CDI référent(e) médical(e) produit',
 'Poste pour un médecin ou un interne souhaitant accompagner la conception de nos outils numériques.',
 'Valider le contenu médical de l''application. Conseiller l''équipe produit. Représenter SantéConnect auprès des hôpitaux partenaires.',
 4200.00, 'cdi', 'ouvert', '2026-10-25 09:00:00'),
(306, 300, 100, 'Stage technicien de maintenance (pourvu)',
 'Offre clôturée, gardée pour tester l''affichage des offres fermées.',
 'Maintenance préventive des équipements. Suivi des interventions.',
 600.00, 'stage', 'cloture', '2026-09-25 09:00:00');

-- ---------- Candidatures des étudiants de démo (fichier 1) ----------
INSERT IGNORE INTO `candidature` (`id_utilisateur`, `id_offre`, `motivation`, `date_candidature`, `statut`) VALUES
(100, 300, 'Étudiante en 3e année, je suis passionnée par le matériel médical et je souhaite découvrir le métier d''ingénieur biomédical.', '2026-10-03 18:20:00', 'en_attente'),
(101, 300, 'J''ai déjà fait un stage d''observation en réanimation et j''aimerais comprendre le fonctionnement des moniteurs.', '2026-10-04 12:05:00', 'retenue'),
(102, 302, 'La recherche clinique m''intéresse beaucoup, notamment en cardiologie. Je suis rigoureuse et à l''aise avec les données.', '2026-10-12 20:40:00', 'en_attente'),
(103, 302, 'Je souhaite me spécialiser dans la recherche et ce stage serait une première expérience idéale.', '2026-10-13 09:15:00', 'non_retenue'),
(104, 304, 'Étudiant en médecine qui code en parallèle, je serais ravi d''apporter un regard de terrain à votre application.', '2026-10-22 17:30:00', 'en_attente'),
(105, 301, 'Je veux allier santé et pédagogie : former les soignants est exactement ce que je recherche en alternance.', '2026-10-07 11:00:00', 'retenue');
