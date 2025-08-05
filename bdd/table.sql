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
   id_activite VARCHAR(50) ,
   activite VARCHAR(100)  NOT NULL,
   date_debut DATE,
   date_echeance DATE NOT NULL,
   est_valide BOOLEAN NOT NULL,
   id_type_activite INT NOT NULL,
   id_utilisateur_auteur INT,
--    matricule INT,
   PRIMARY KEY(id_activite),
   FOREIGN KEY(id_type_activite) REFERENCES type_activite(id_type_activite),
--    FOREIGN KEY(id_utilisateur_auteur, matricule) REFERENCES utilisateur(id_utilisateur, matricule)
);

CREATE TABLE tache(
   id_tache INT AUTO_INCREMENT,
   tache VARCHAR(150) ,
   debut DATETIME,
   date_echeance VARCHAR(50)  NOT NULL,
   id_utilisateur INT NOT NULL,
   matricule INT NOT NULL,
   id_activite VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_tache),
   FOREIGN KEY(id_utilisateur, matricule) REFERENCES utilisateur(id_utilisateur, matricule),
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
   FOREIGN KEY(id_utilisateur, matricule) REFERENCES utilisateur(id_utilisateur, matricule),
   FOREIGN KEY(id_type_anomalie) REFERENCES type_anomalie(id_type_anomalie)
);

CREATE TABLE activite_terminee(
   id_activite_terminee INT AUTO_INCREMENT,
   date_terminee DATETIME NOT NULL,
   temps_passe DECIMAL(15,2)   NOT NULL,
   id_activite VARCHAR(50)  NOT NULL,
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
   id_changement VARCHAR(50) ,
   debut DATETIME NOT NULL,
   date_echeance DATETIME NOT NULL,
   id_tache INT NOT NULL,
   PRIMARY KEY(id_changement),
   FOREIGN KEY(id_tache) REFERENCES tache(id_tache)
);

CREATE TABLE modification_activite(
   id_modif VARCHAR(50) ,
   activite VARCHAR(100)  NOT NULL,
   date_debut DATE,
   date_echeance DATE NOT NULL,
   id_activite VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_modif),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);

CREATE TABLE activite_supprimees(
   id_suppression INT AUTO_INCREMENT,
   date_suppression DATETIME NOT NULL,
   id_activite VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_suppression),
   UNIQUE(id_activite),
   FOREIGN KEY(id_activite) REFERENCES activite(id_activite)
);
