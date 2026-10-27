-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 13 oct. 2026 à 07:53
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `hsp_gdh`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidature`
--

DROP TABLE IF EXISTS `candidature`;
CREATE TABLE IF NOT EXISTS `candidature` (
                                             `id_utilisateur` int UNSIGNED NOT NULL,
                                             `id_offre` int UNSIGNED NOT NULL,
                                             `motivation` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `date_candidature` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                             `statut` enum('en_attente','retenue','non_retenue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
    PRIMARY KEY (`id_utilisateur`,`id_offre`),
    KEY `fk_candidature_offre` (`id_offre`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
CREATE TABLE IF NOT EXISTS `entreprise` (
                                            `id_entreprise` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `site_web` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_entreprise`),
    UNIQUE KEY `uq_entreprise_site_web` (`site_web`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etablissement`
--

DROP TABLE IF EXISTS `etablissement`;
CREATE TABLE IF NOT EXISTS `etablissement` (
                                               `id_etablissement` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                               `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `site_web` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_etablissement`),
    UNIQUE KEY `uq_etablissement_site_web` (`site_web`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
                                          `id_utilisateur` int UNSIGNED NOT NULL,
                                          `id_etablissement` int UNSIGNED NOT NULL,
                                          `cv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `formation` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_utilisateur`),
    KEY `fk_etudiant_etablissement` (`id_etablissement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

DROP TABLE IF EXISTS `evenement`;
CREATE TABLE IF NOT EXISTS `evenement` (
                                           `id_evenement` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `type_evenement` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `lieu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `elements_requis` text COLLATE utf8mb4_unicode_ci,
    `nombre_places` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id_evenement`),
    KEY `idx_evenement_type` (`type_evenement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `gestionnaire`
--

DROP TABLE IF EXISTS `gestionnaire`;
CREATE TABLE IF NOT EXISTS `gestionnaire` (
                                              `id_utilisateur` int UNSIGNED NOT NULL,
                                              PRIMARY KEY (`id_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hopital`
--

DROP TABLE IF EXISTS `hopital`;
CREATE TABLE IF NOT EXISTS `hopital` (
                                         `id_hopital` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                         `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `commune` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_hopital`)
    ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `hopital`
--

INSERT INTO `hopital` (`id_hopital`, `nom`, `commune`) VALUES
                                                           (1, 'Hôpital Sud Paris', 'Marreuil-sur-Seine'),
                                                           (2, 'Hôpital Nord Paris', 'Saint-Maximin'),
                                                           (3, 'Hôpital Général de Lyon', 'Limonest'),
                                                           (4, 'Hôpital Général de Marseille', 'Peypin'),
                                                           (5, 'Hôpital Général de Bordeaux', 'Beychac-et-Caillau'),
                                                           (6, 'Hôpital Général de Nantes', 'Mauves-sur-Loire'),
                                                           (7, 'Hôpital Général de Toulouse', 'Muret'),
                                                           (8, 'Hôpital Général de Strasbourg', 'Vendenheim'),
                                                           (9, 'Hôpital Général de Rennes', 'Pont-Péan');

-- --------------------------------------------------------

--
-- Structure de la table `inscription_evenement`
--

DROP TABLE IF EXISTS `inscription_evenement`;
CREATE TABLE IF NOT EXISTS `inscription_evenement` (
                                                       `id_utilisateur` int UNSIGNED NOT NULL,
                                                       `id_evenement` int UNSIGNED NOT NULL,
                                                       `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                       `organiser` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_utilisateur`,`id_evenement`),
    KEY `fk_inscription_evenement_evenement` (`id_evenement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `medecin`
--

DROP TABLE IF EXISTS `medecin`;
CREATE TABLE IF NOT EXISTS `medecin` (
                                         `id_utilisateur` int UNSIGNED NOT NULL,
                                         `id_specialite` int UNSIGNED NOT NULL,
                                         `id_hopital` int UNSIGNED NOT NULL,
                                         `id_etablissement` int UNSIGNED DEFAULT NULL,
                                         PRIMARY KEY (`id_utilisateur`),
    KEY `fk_medecin_specialite` (`id_specialite`),
    KEY `fk_medecin_hopital` (`id_hopital`),
    KEY `fk_medecin_etablissement` (`id_etablissement`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offre`
--

DROP TABLE IF EXISTS `offre`;
CREATE TABLE IF NOT EXISTS `offre` (
                                       `id_offre` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                       `id_auteur` int UNSIGNED NOT NULL,
                                       `id_entreprise` int UNSIGNED NOT NULL,
                                       `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `missions` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `salaire` decimal(10,2) DEFAULT NULL,
    `type_offre` enum('stage','alternance','cdd','cdi') COLLATE utf8mb4_unicode_ci NOT NULL,
    `etat` enum('ouvert','cloture') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
    `date_publication` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_offre`),
    KEY `fk_offre_auteur` (`id_auteur`),
    KEY `fk_offre_entreprise` (`id_entreprise`),
    KEY `idx_offre_etat` (`etat`),
    KEY `idx_offre_type` (`type_offre`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `partenaire`
--

DROP TABLE IF EXISTS `partenaire`;
CREATE TABLE IF NOT EXISTS `partenaire` (
                                            `id_utilisateur` int UNSIGNED NOT NULL,
                                            `id_entreprise` int UNSIGNED NOT NULL,
                                            `poste` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_utilisateur`),
    KEY `fk_partenaire_entreprise` (`id_entreprise`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
                                      `id_post` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `id_section` int UNSIGNED NOT NULL,
                                      `id_auteur` int UNSIGNED NOT NULL,
                                      `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_post`),
    KEY `fk_post_auteur` (`id_auteur`),
    KEY `idx_post_section` (`id_section`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

DROP TABLE IF EXISTS `reponse`;
CREATE TABLE IF NOT EXISTS `reponse` (
                                         `id_reponse` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                         `id_post` int UNSIGNED NOT NULL,
                                         `id_auteur` int UNSIGNED NOT NULL,
                                         `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                         PRIMARY KEY (`id_reponse`),
    KEY `fk_reponse_post` (`id_post`),
    KEY `fk_reponse_auteur` (`id_auteur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `section_forum`
--

DROP TABLE IF EXISTS `section_forum`;
CREATE TABLE IF NOT EXISTS `section_forum` (
                                               `id_section` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                               `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_section`),
    UNIQUE KEY `uq_section_forum_nom` (`nom`)
    ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `section_forum`
--

INSERT INTO `section_forum` (`id_section`, `nom`) VALUES
                                                      (3, 'Étudiants'),
                                                      (1, 'Générale'),
                                                      (2, 'Médecins et partenaires');

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

DROP TABLE IF EXISTS `specialite`;
CREATE TABLE IF NOT EXISTS `specialite` (
                                            `id_specialite` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `libelle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_specialite`),
    UNIQUE KEY `uq_specialite_libelle` (`libelle`)
    ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `specialite`
--

INSERT INTO `specialite` (`id_specialite`, `libelle`) VALUES
                                                          (18, 'Anesthésiologue'),
                                                          (1, 'Cardiologue'),
                                                          (17, 'Chirurgien orthopédique'),
                                                          (4, 'Dermatologue'),
                                                          (9, 'Endocrinologue'),
                                                          (3, 'Gastroentérologue'),
                                                          (11, 'Gynécologue'),
                                                          (15, 'Hématologue'),
                                                          (20, 'Médecin généraliste'),
                                                          (19, 'Médecin interne'),
                                                          (14, 'Néphrologue'),
                                                          (2, 'Neurologue'),
                                                          (6, 'Oncologue'),
                                                          (10, 'Ophtalmologue'),
                                                          (16, 'Oto-rhino-laryngologue'),
                                                          (5, 'Pédiatrie'),
                                                          (7, 'Pneumologue'),
                                                          (12, 'Psychiatre'),
                                                          (8, 'Rhumatologue'),
                                                          (13, 'Urologue');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
                                             `id_utilisateur` int UNSIGNED NOT NULL AUTO_INCREMENT,
                                             `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `valide` tinyint(1) NOT NULL DEFAULT '0',
    `id_validateur` int UNSIGNED DEFAULT NULL,
    `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_derniere_connexion` datetime DEFAULT NULL,
    PRIMARY KEY (`id_utilisateur`),
    UNIQUE KEY `uq_utilisateur_email` (`email`),
    KEY `fk_utilisateur_validateur` (`id_validateur`),
    KEY `idx_utilisateur_valide` (`valide`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Compte gestionnaire DE TEST : admin@test.com / admin1234
-- À SUPPRIMER ou à changer avant la mise en ligne (le dépôt Git est public).
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `valide`, `id_validateur`) VALUES
(1, 'Test', 'Admin', 'admin@test.com', '$2y$10$81q.hxEmnvwigDVCHaGjF.m8RFNOohmjnJcOHidQGKDgC.PYCbFhm', 1, NULL);

INSERT INTO `gestionnaire` (`id_utilisateur`) VALUES (1);

-- --------------------------------------------------------

--
-- Structure de la table `reinitialisation_mdp` (mot de passe oublié)
-- On stocke l'empreinte SHA-256 du jeton, jamais le jeton lui-même.
--

DROP TABLE IF EXISTS `reinitialisation_mdp`;
CREATE TABLE IF NOT EXISTS `reinitialisation_mdp` (
    `id_reinitialisation` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_utilisateur` int UNSIGNED NOT NULL,
    `jeton_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `date_expiration` datetime NOT NULL,
    PRIMARY KEY (`id_reinitialisation`),
    UNIQUE KEY `uq_reinitialisation_jeton` (`jeton_hash`),
    KEY `fk_reinitialisation_utilisateur` (`id_utilisateur`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidature`
--
ALTER TABLE `candidature`
    ADD CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_candidature_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
    ADD CONSTRAINT `fk_etudiant_etablissement` FOREIGN KEY (`id_etablissement`) REFERENCES `etablissement` (`id_etablissement`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `gestionnaire`
--
ALTER TABLE `gestionnaire`
    ADD CONSTRAINT `fk_gestionnaire_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
    ADD CONSTRAINT `fk_inscription_evenement_evenement` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscription_evenement_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `medecin`
--
ALTER TABLE `medecin`
    ADD CONSTRAINT `fk_medecin_etablissement` FOREIGN KEY (`id_etablissement`) REFERENCES `etablissement` (`id_etablissement`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medecin_hopital` FOREIGN KEY (`id_hopital`) REFERENCES `hopital` (`id_hopital`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medecin_specialite` FOREIGN KEY (`id_specialite`) REFERENCES `specialite` (`id_specialite`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medecin_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `offre`
--
ALTER TABLE `offre`
    ADD CONSTRAINT `fk_offre_auteur` FOREIGN KEY (`id_auteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_offre_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `partenaire`
--
ALTER TABLE `partenaire`
    ADD CONSTRAINT `fk_partenaire_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partenaire_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
    ADD CONSTRAINT `fk_post_auteur` FOREIGN KEY (`id_auteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_section` FOREIGN KEY (`id_section`) REFERENCES `section_forum` (`id_section`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
    ADD CONSTRAINT `fk_reponse_auteur` FOREIGN KEY (`id_auteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reponse_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reinitialisation_mdp`
--
ALTER TABLE `reinitialisation_mdp`
    ADD CONSTRAINT `fk_reinitialisation_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
    ADD CONSTRAINT `fk_utilisateur_validateur` FOREIGN KEY (`id_validateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;