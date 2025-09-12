-- Disable foreign key checks to allow inserting data in any order
SET FOREIGN_KEY_CHECKS = 0;

--
-- Data for table `ville`
--

INSERT INTO ville (id, nom, code_postal) VALUES
(1, 'Nantes', '44000'),
(2, 'Rennes', '35000'),
(3, 'Niort', '79000');

--
-- Data for table `campus`
--

INSERT INTO campus (id, nom) VALUES
(1, 'Saint-Herblain'),
(2, 'Rennes'),
(3, 'Niort');

--
-- Data for table `etat`
--

INSERT INTO etat (id, libelle) VALUES
(1, 'Créée'),
(2, 'Ouverte'),
(3, 'Clôturée'),
(4, 'Activité en cours'),
(5, 'Activité terminée'),
(6, 'Activité archivée'),
(7, 'Annulée');

--
-- Data for table `lieu`
--

INSERT INTO lieu (id, nom, rue, latitude, longitude, ville_id) VALUES
(1, 'Parc de Procé', 'Rue de Procé', 47.228, -1.576, 1),
(2, 'Place de la Mairie', 'Place de la Mairie', 48.111, -1.675, 2),
(3, 'Le Moulin du Roc', 'Rue du Moulin', 46.325, -0.463, 3);

--
-- Data for table `participant`
-- (Note: motPasse is a placeholder. In a real scenario, it should be hashed.)
--

INSERT INTO participant (id, campus_id, nom, prenom, pseudo, telephone, mail, mot_passe, actif, organisateur, roles, is_verified, activation_token, token_expires_at, avatar) VALUES
(1, 1, 'Dupont', 'Jean', 'jeand', '0612345678', 'jean.dupont@campus-eni.fr', '$2y$13$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 1, 1, '["ROLE_USER", "ROLE_ADMIN"]', 1, NULL, NULL, NULL),
(2, 1, 'Durand', 'Marie', 'maried', '0687654321', 'marie.durand@campus-eni.fr', '$2y$13$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 1, 0, '["ROLE_USER"]', 1, NULL, NULL, NULL),
(3, 2, 'Petit', 'Pierre', 'pierrep', '0711223344', 'pierre.petit@eni-ecole.fr', '$2y$13$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 1, 0, '["ROLE_USER"]', 1, NULL, NULL, NULL);

--
-- Data for table `sortie`
--

INSERT INTO sortie (id, organisateur_id, campus_id, etat_id, lieu_id, nom, date_heure_debut, duree, date_limite_inscription, nb_inscription_max, infos_sortie, motif) VALUES
(1, 1, 1, 2, 1, 'Pique-nique au parc', '2025-09-20 12:00:00', 180, '2025-09-18 23:59:59', 10, 'Venez partager un moment convivial au parc de Procé.', NULL),
(2, 2, 1, 1, 1, 'Course d''orientation', '2025-10-05 09:00:00', 240, '2025-10-01 23:59:59', 15, 'Découvrez les joies de la course d''orientation.', NULL),
(3, 1, 2, 2, 2, 'Visite du centre ville', '2025-09-25 14:00:00', 120, '2025-09-23 23:59:59', 5, 'Découverte des monuments historiques de Rennes.', NULL);

--
-- Data for table `participant_sortie` (Many-to-Many relationship)
--

INSERT INTO participant_sortie (participant_id, sortie_id) VALUES
(1, 1), -- Jean Dupont participe au Pique-nique
(2, 1), -- Marie Durand participe au Pique-nique
(1, 2), -- Jean Dupont participe à la Course d''orientation
(3, 3); -- Pierre Petit participe à la Visite du centre ville

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;