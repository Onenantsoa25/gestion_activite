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
