-- DELIMITER $$

-- CREATE TRIGGER trg_notification_apres_attribution_tache
-- AFTER UPDATE ON tache
-- FOR EACH ROW
-- BEGIN
--     -- Vérifier si id_utilisateur a changé et n'est pas NULL
--     IF NEW.id_utilisateur IS NOT NULL AND NEW.id_utilisateur <> OLD.id_utilisateur THEN
--         INSERT INTO notification (
--             id_utilisateur,
--             type_notif,
--             message,
--             date_creation,
--             est_lue
--         ) VALUES (
--             NEW.id_utilisateur,
--             'mission',
--             CONCAT('nouvelle tache ', NEW.tache),
--             NOW(),
--             FALSE
--         );
--     END IF;
-- END $$

-- DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_notification_apres_attribution_tache
AFTER UPDATE ON tache
FOR EACH ROW
BEGIN
    -- Vérifier si la tâche vient d'être attribuée (avant NULL, après non NULL)
    IF OLD.id_utilisateur IS NULL 
       AND NEW.id_utilisateur IS NOT NULL THEN
       
        INSERT INTO notification (
            id_utilisateur,
            type_notif,
            message,
            date_creation,
            est_lue,
            id_tache
        ) VALUES (
            NEW.id_utilisateur,
            'mission',
            CONCAT(
                'Nouvelle tâche : "', NEW.tache, 
                '", date d\'échéance : ', NEW.date_echeance
            ),
            NOW(),
            FALSE,
            NEW.id_tache
        );
    END IF;
END $$

DELIMITER ;
'

DELIMITER $$

CREATE TRIGGER after_insert_historique
AFTER INSERT ON historique_tache
FOR EACH ROW
BEGIN
    DECLARE v_old_date DATETIME;
    DECLARE v_old_estim FLOAT;
    DECLARE v_user INT;
    DECLARE v_msg TEXT DEFAULT '';
    DECLARE v_count INT;

    -- Vérifier s'il existe déjà un historique pour cette tâche (hors celui qu'on vient d'insérer)
    SELECT COUNT(*) INTO v_count
    FROM historique_tache
    WHERE id_tache = NEW.id_tache
      AND id_changement < NEW.id_changement;

    IF v_count = 0 THEN
        -- Cas 1 : Pas encore d'historique → comparer avec la table tache
        SELECT date_echeance, estimation, id_utilisateur
        INTO v_old_date, v_old_estim, v_user
        FROM tache
        WHERE id_tache = NEW.id_tache;
    ELSE
        -- Cas 2 : Déjà au moins un historique → comparer avec le dernier historique
        SELECT h.date_echeance, h.estimation, t.id_utilisateur
        INTO v_old_date, v_old_estim, v_user
        FROM historique_tache h
        JOIN tache t ON t.id_tache = h.id_tache
        WHERE h.id_tache = NEW.id_tache
          AND h.id_changement < NEW.id_changement
        ORDER BY h.id_changement DESC
        LIMIT 1;
    END IF;

    -- Vérifier les changements
    IF (NEW.date_echeance <> v_old_date) THEN
        SET v_msg = CONCAT(v_msg, 'Date échéance : ', v_old_date, ' → ', NEW.date_echeance, '. ');
    END IF;

    IF (NEW.estimation <> v_old_estim) THEN
        SET v_msg = CONCAT(v_msg, 'Estimation : ', v_old_estim, ' → ', NEW.estimation, '. ');
    END IF;

    -- Si au moins un changement détecté, insérer la notification
    IF v_msg <> '' THEN
        INSERT INTO notification (id_utilisateur, type_notif, message, date_creation, est_lue, id_tache)
        VALUES (v_user, 'changement', v_msg, NOW(), 0, NEW.id_tache);
    END IF;
END$$

DELIMITER ;

