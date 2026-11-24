-- ============================================================================
-- Données de démonstration : ÉVÉNEMENTS (avec inscriptions) et FORUM (posts et réponses) (fichier 4, Suli)
-- À importer APRÈS hsp_gdh.sql, 1_etudiants.sql, 2_medecins.sql et 3_partenaires.sql
-- (il utilise les étudiants 100-106, les médecins 200-203, le partenaire 300 et le gestionnaire 1).
--
-- Contenu FICTIF, pour que le site ne soit pas vide à la première ouverture. NE PAS importer en ligne.
-- Les dates des événements sont calculées à partir de maintenant : ils sont toujours « à venir »
-- (sauf un, volontairement terminé, et un autre, volontairement annulé).
-- On peut réimporter ce fichier : il efface d'abord son ancien contenu (numéros 1001 à 1099).
-- ============================================================================

SET NAMES utf8mb4;

DELETE FROM `reponse`               WHERE `id_post` BETWEEN 1001 AND 1099;
DELETE FROM `post`                  WHERE `id_post` BETWEEN 1001 AND 1099;
DELETE FROM `inscription_evenement` WHERE `id_evenement` BETWEEN 1001 AND 1099;
DELETE FROM `evenement`             WHERE `id_evenement` BETWEEN 1001 AND 1099;

-- ---------------------------------------------------------------- Événements
INSERT INTO `evenement` (`id_evenement`, `type_evenement`, `titre`, `description`, `lieu`, `date_evenement`, `date_fin`, `elements_requis`, `nombre_places`, `annule`) VALUES
                                                                                                                                                                           (1001, 'Visite', 'Visite du bloc opératoire', 'Visite guidée du bloc opératoire par un chirurgien : circuit du patient, matériel, règles d''hygiène.', 'Bloc B, Hôpital Sud Paris',
                                                                                                                                                                            DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 2 HOUR), 'Pièce d''identité', 8, 0),
                                                                                                                                                                           (1002, 'Conférence', 'Conférence : l''IA au service du diagnostic', 'Présentation des outils d''aide au diagnostic par imagerie, suivie d''un temps de questions.', 'Amphithéâtre A',
                                                                                                                                                                            DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 7 DAY), INTERVAL 90 MINUTE), NULL, 40, 0),
                                                                                                                                                                           (1003, 'Atelier', 'Atelier gestes de premiers secours', 'Atelier pratique : massage cardiaque, défibrillateur, position latérale de sécurité.', 'Salle de simulation',
                                                                                                                                                                            DATE_ADD(NOW(), INTERVAL 10 DAY), NULL, 'Tenue confortable', 6, 0),
                                                                                                                                                                           (1004, 'Forum', 'Forum des métiers de la santé', 'Deux jours pour rencontrer des médecins, des infirmiers et des recruteurs.', 'Hall principal',
                                                                                                                                                                            DATE_ADD(NOW(), INTERVAL 21 DAY), DATE_ADD(NOW(), INTERVAL 22 DAY), NULL, 120, 0),
                                                                                                                                                                           (1005, 'Rencontre', 'Rencontre avec les partenaires', 'Échanges entre étudiants et entreprises partenaires du groupe hospitalier.', 'Salle de réunion 2',
                                                                                                                                                                            DATE_ADD(NOW(), INTERVAL 14 DAY), NULL, NULL, 12, 1),
                                                                                                                                                                           (1006, 'Conférence', 'Journée portes ouvertes', 'Découverte des services de l''hôpital (événement terminé, visible avec « Voir aussi les événements passés »).', 'Hôpital Sud Paris',
                                                                                                                                                                            DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 20 DAY), INTERVAL 4 HOUR), NULL, 50, 0);

-- Organisateurs (organiser = 1) : médecins 200 à 203 et partenaire 300
INSERT INTO `inscription_evenement` (`id_utilisateur`, `id_evenement`, `organiser`) VALUES
                                                                                        (200, 1001, 1), (201, 1002, 1), (202, 1003, 1), (300, 1004, 1), (300, 1005, 1), (203, 1006, 1);

-- Participants (organiser = 0) : l'événement 1003 est presque complet (5 sur 6 places)
INSERT INTO `inscription_evenement` (`id_utilisateur`, `id_evenement`, `organiser`) VALUES
                                                                                        (100, 1001, 0), (101, 1001, 0), (102, 1001, 0),
                                                                                        (100, 1002, 0), (103, 1002, 0), (104, 1002, 0), (105, 1002, 0),
                                                                                        (100, 1003, 0), (101, 1003, 0), (102, 1003, 0), (103, 1003, 0), (104, 1003, 0),
                                                                                        (101, 1004, 0), (106, 1004, 0),
                                                                                        (100, 1005, 0),
                                                                                        (100, 1006, 0), (101, 1006, 0);

-- ---------------------------------------------------------------- Forum (sections 1 Générale, 2 Médecins et partenaires, 3 Étudiants)
INSERT INTO `post` (`id_post`, `id_section`, `id_auteur`, `titre`, `contenu`, `date_heure`) VALUES
                                                                                                (1001, 1, 1, 'Bienvenue sur le forum du groupe hospitalier', 'Ce forum sert à échanger entre étudiants, médecins et partenaires. Merci de rester courtois et de respecter la confidentialité des patients.', DATE_SUB(NOW(), INTERVAL 15 DAY)),
                                                                                                (1002, 3, 100, 'Conseils pour réussir son stage à l''hôpital ?', 'Je commence un stage dans deux semaines. Avez-vous des conseils pour bien s''intégrer dans l''équipe ?', DATE_SUB(NOW(), INTERVAL 8 DAY)),
                                                                                                (1003, 3, 101, 'Quelqu''un connaît la visite du bloc opératoire ?', 'Je me suis inscrit à la visite du bloc. Est-ce que ça vaut le coup, et faut-il préparer quelque chose ?', DATE_SUB(NOW(), INTERVAL 2 DAY)),
                                                                                                (1004, 2, 200, 'Accueillir des étudiants en stage : retours d''expérience', 'Je prends régulièrement des stagiaires dans mon service. Comment organisez-vous leur accueil la première semaine ?', DATE_SUB(NOW(), INTERVAL 6 DAY)),
                                                                                                (1005, 1, 300, 'Partenaires : nouvelles offres de stage', 'Nos entreprises publient plusieurs offres de stage et d''alternance. N''hésitez pas à postuler depuis la page Offres.', DATE_SUB(NOW(), INTERVAL 4 DAY));

INSERT INTO `reponse` (`id_reponse`, `id_post`, `id_auteur`, `contenu`, `date_heure`) VALUES
                                                                                          (1001, 1002, 200, 'Arrive en avance le premier jour, pose beaucoup de questions et note les consignes d''hygiène : c''est ce qui est le plus apprécié.', DATE_SUB(NOW(), INTERVAL 7 DAY)),
                                                                                          (1002, 1002, 102, 'Pense à prendre une tenue adaptée et une pièce d''identité pour le badge.', DATE_SUB(NOW(), INTERVAL 7 DAY)),
                                                                                          (1003, 1003, 100, 'Je m''y suis inscrite aussi ! Il faut juste une pièce d''identité, c''est indiqué sur la fiche.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
                                                                                          (1004, 1004, 201, 'Je fais un tour des services le premier jour et je leur donne un référent. Ça change tout.', DATE_SUB(NOW(), INTERVAL 5 DAY));