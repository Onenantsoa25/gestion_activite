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
