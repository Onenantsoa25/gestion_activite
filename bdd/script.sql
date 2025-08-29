INSERT INTO role (role) VALUES 
('manager'),
('collaborateur');

INSERT INTO utilisateur (matricule, mot_de_passe, id_role) VALUES 
(1001, 'mdpmanager', 1),   -- utilisateur manager
(1002, 'mdpcollab', 2);    -- utilisateur collaborateur


UPDATE utilisateur SET mot_de_passe = '$2y$13$zCg/hp7FToXwlayQdJ9sueEVNshIWuUBB4UqV27r9yqm2ePjc1URO' WHERE matricule = 1002;

INSERT INTO type_activite (type_activite) VALUES  
('Client'),  
('Interne'),  
('Support'),  
('Administratif');  


INSERT INTO utilisateur (matricule, mot_de_passe, id_role)
VALUES (1003, '$2y$13$u9M8K1d7vK3HgIhK5eO8qeRj6YbI9ZrO9plhQJ0BfW7qzPqQ6tF3a', 2);
