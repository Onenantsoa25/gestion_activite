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

INSERT INTO type_anomalie (type_anomalie) VALUES 
("Oublie de saisie"), 
("surcharge"),
("sous-activite");

SET @date_limite   = '2025-09-19';

SELECT count(*) from v_echeance_taches where DATE(date_echeance) < @date_limite AND id_tache NOT IN (SELECT id_tache FROM tache_terminee);

SELECT count(*) FROM v_echeance_taches t JOIN tache_terminee tt ON t.id_tache = tt.id_tache WHERE DATE(t.date_echeance) < DATE(tt.date_terminee) AND t.date_echeance = @date_limite;