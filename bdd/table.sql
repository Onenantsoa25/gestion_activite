CREATE TABLE role(
   id INT AUTO_INCREMENT,
   role VARCHAR(50) ,
   PRIMARY KEY(id)
);

CREATE TABLE type_anomalie(
   id_type_anomalie INT AUTO_INCREMENT,
   type_anomalie VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_type_anomalie)
);

CREATE TABLE type_activite(
   id_type_activite INT AUTO_INCREMENT,
   type_activite VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_type_activite)
);

CREATE TABLE utilisateur(
   id_utilisateur INT AUTO_INCREMENT,
   matricule INT,
   mot_de_passe VARCHAR(255)  NOT NULL,
   id_role INT NOT NULL,
   PRIMARY KEY(id_utilisateur, matricule),
   FOREIGN KEY(id_role) REFERENCES role(id)
);

CREATE TABLE activite(
   id_activite INT AUTO_INCREMENT,
   activite VARCHAR(100)  NOT NULL,
   date_debut DATE,
   date_echeance DATE NOT NULL,
   est_valide BOOLEAN NOT NULL,
   id_type_activite INT NOT NULL,
   id_utilisateur_auteur INT,
--    matricule INT,
   PRIMARY KEY(id_activite),
   FOREIGN KEY(id_type_activite) REFERENCES type_activite(id_type_activite),
   FOREIGN KEY(id_utilisateur_auteur) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE tache(
   id_tache INT AUTO_INCREMENT,
   tache VARCHAR(150) ,
   debut DATETIME,
   date_echeance VARCHAR(50)  NOT NULL,
   id_utilisateur INT,
   -- matricule INT NOT NULL,
   id_activite INT  NOT NULL,
   PRIMARY KEY(id_tache),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);

CREATE TABLE anomalie(
   id_anomalie INT AUTO_INCREMENT,
   est_resolue BOOLEAN,
   date_anomalie DATETIME NOT NULL,
   id_utilisateur INT NOT NULL,
   matricule INT NOT NULL,
   id_type_anomalie INT NOT NULL,
   PRIMARY KEY(id_anomalie),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur),
   FOREIGN KEY(id_type_anomalie) REFERENCES type_anomalie(id_type_anomalie)
);

CREATE TABLE activite_terminee(
   id_activite_terminee INT AUTO_INCREMENT,
   date_terminee DATETIME NOT NULL,
   -- temps_passe DECIMAL(15,2)   NOT NULL,
   id_activite INT  NOT NULL,
   PRIMARY KEY(id_activite_terminee),
   UNIQUE(id_activite),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);

CREATE TABLE tache_terminee(
   id_tache_terminee INT AUTO_INCREMENT,
   date_terminee DATETIME NOT NULL,
   temps_passe DECIMAL(15,2)   NOT NULL,
   id_tache INT NOT NULL,
   PRIMARY KEY(id_tache_terminee),
   UNIQUE(id_tache),
   FOREIGN KEY(id_tache) REFERENCES tache(id_tache)
);

CREATE TABLE historique_tache(
   id_changement INT AUTO_INCREMENT, 
   debut DATETIME NOT NULL,
   date_echeance DATETIME NOT NULL,
   id_tache INT NOT NULL,
   PRIMARY KEY(id_changement),
   FOREIGN KEY(id_tache) REFERENCES tache(id_tache)
);

CREATE TABLE modification_activite(
   id_modif INT AUTO_INCREMENT, 
   activite VARCHAR(100)  NOT NULL,
   date_debut DATE,
   date_echeance DATE NOT NULL,
   id_activite INT  NOT NULL,
   PRIMARY KEY(id_modif),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);

CREATE TABLE activite_supprimees(
   id_suppression INT AUTO_INCREMENT,
   date_suppression DATETIME NOT NULL,
   id_activite INT  NOT NULL,
   PRIMARY KEY(id_suppression),
   UNIQUE(id_activite),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);




CREATE TABLE auth_token(
   id INT AUTO_INCREMENT,
   utilisateur_id INT NOT NULL,
   value VARCHAR(255) NOT NULL,
   created_at DATETIME NOT NULL,
   expires_at DATETIME NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(utilisateur_id) REFERENCES utilisateur(id_utilisateur)
);


ALTER TABLE tache ADD estimation FLOAT;
ALTER TABLE historique_tache ADD COLUMN estimation FLOAT;

CREATE TABLE notification (
   id_notification INT AUTO_INCREMENT,
   id_utilisateur INT NOT NULL,
   type_notif VARCHAR(100) NOT NULL,
   message TEXT NOT NULL,
   date_creation DATETIME NOT NULL,
   est_lue BOOLEAN DEFAULT FALSE,
   PRIMARY KEY(id_notification),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur),
   CHECK (type_notif IN ('mission', 'changement'))
);


alter table notification add column id_tache INT REFERENCES tache(id_tache);

alter table tache_terminee add column commentaire text;
alter table tache_terminee add column est_validee BOOLEAN;

ALTER TABLE tache_terminee 
ADD COLUMN justificatif VARCHAR(255); -- LONGBLO


ALTER TABLE tache 
MODIFY COLUMN date_echeance DATETIME NOT NULL;

ALTER TABLE historique_tache 
MODIFY debut DATETIME NULL;

CREATE TABLE tache_supprimee (
   id_tache_supprimee int primary key AUTO_INCREMENT,
   id_tache int REFERENCES tache(id_tache),
   date_suppression DATETIME
);

ALTER TABLE tache_supprimee
ADD CONSTRAINT unique_id_tache UNIQUE (id_tache);
