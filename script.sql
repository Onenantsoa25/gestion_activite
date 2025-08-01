SELECT
     u.id,
     COALESCE(
         (
             SELECT h.name
             FROM historique_util h
             WHERE h.id_util = u.id
             ORDER BY h.id DESC
             LIMIT 1
         ),
         u.name
     ) AS nom_actif
 FROM util u
 ORDER BY u.id;