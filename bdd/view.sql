-- CREATE OR REPLACE VIEW v_activite_tache_utilisateur AS 
-- SELECT a.id_activite, t.id_tache, t.id_utilisateur
-- FROM activite a
-- JOIN tache t ON t.id_activite = a.id_activite
-- LEFT JOIN tache_terminee tt ON t.id_tache = tt.id_tache
-- WHERE tt.id_tache IS NULL;


CREATE OR REPLACE VIEW v_activite_tache_utilisateur AS 
SELECT a.id_activite, t.id_tache, t.id_utilisateur
FROM activite a
JOIN tache t ON t.id_activite = a.id_activite
LEFT JOIN tache_terminee tt ON t.id_tache = tt.id_tache
WHERE tt.id_tache IS NULL AND a.est_valide IS TRUE;


CREATE OR REPLACE VIEW v_tache_non_terminee AS 
SELECT DISTINCT
    t.id_tache, t.tache, COALESCE(h.debut, t.debut) AS debut, COALESCE(h.date_echeance, t.date_echeance) AS date_echeance, t.id_utilisateur, t.id_activite, COALESCE(h.estimation, t.estimation) AS estimation  
FROM tache t LEFT JOIN historique_tache h ON h.id_changement = (
                            SELECT MAX(h2.id_changement) 
                            FROM historique_tache h2 
                            WHERE h2.id_tache = t.id_tache
                    ) 


CREATE OR REPLACE VIEW v_tache_non_terminee_utilisateur AS
SELECT 
    t.id_tache,
    t.id_utilisateur,
    COALESCE(h.estimation, t.estimation) AS estimation,
    COALESCE(h.debut, t.debut) AS debut
FROM tache t
LEFT JOIN (
    SELECT h1.id_tache, h1.estimation, h1.debut
    FROM historique_tache h1
    INNER JOIN (
        SELECT id_tache, MAX(id_changement) AS max_id
        FROM historique_tache
        GROUP BY id_tache
    ) h2 ON h1.id_tache = h2.id_tache AND h1.id_changement = h2.max_id
) h ON h.id_tache = t.id_tache
WHERE NOT EXISTS (
    SELECT 1 FROM tache_terminee tt WHERE tt.id_tache = t.id_tache
)
AND NOT EXISTS (
    SELECT 1 FROM tache_supprimee ts WHERE ts.id_tache = t.id_tache
) AND
DATE(COALESCE(h.debut, t.debut)) = CURDATE()
;


CREATE OR REPLACE VIEW v_echeance_taches AS 
SELECT 
    t.id_tache,
    COALESCE(h.date_echeance, t.date_echeance) AS date_echeance,
    t.tache
FROM tache t
LEFT JOIN (
    SELECT h1.id_tache, h1.date_echeance
    FROM historique_tache h1
    INNER JOIN (
        SELECT id_tache, MAX(id_changement) AS max_id
        FROM historique_tache
        GROUP BY id_tache
    ) h2 ON h1.id_tache = h2.id_tache AND h1.id_changement = h2.max_id
) h ON t.id_tache = h.id_tache;
