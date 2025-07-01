CREATE OR REPLACE VIEW top_attending_clients AS
SELECT 
    c.client_id,
    c.surname,
    c.name,
    c.patronymic,
    COUNT(*) AS count
FROM visits v
JOIN clients c ON v.client_id = c.client_id
JOIN workouts w ON v.workout_id = w.workout_id
WHERE v.is_attended = 'посещена'
  AND w.start_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY c.client_id
ORDER BY count DESC;

CREATE OR REPLACE VIEW top_skipping_clients AS
SELECT 
    c.client_id,
    c.surname,
    c.name,
    c.patronymic,
    COUNT(*) AS count
FROM visits v
JOIN clients c ON v.client_id = c.client_id
JOIN workouts w ON v.workout_id = w.workout_id
WHERE v.is_attended = 'не посещена'
  AND w.start_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY c.client_id
ORDER BY count DESC;

CREATE OR REPLACE VIEW top_coaches_view AS
SELECT 
    ch.coach_id,
    ch.surname,
    ch.name,
    ch.patronymic,
    COUNT(*) AS count
FROM workouts w
JOIN coaches ch ON w.coach_id = ch.coach_id
WHERE w.start_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY ch.coach_id
ORDER BY count DESC;

CREATE OR REPLACE VIEW top_templates_view AS
SELECT 
    t.template_id,
    t.name,
    COUNT(*) AS count
FROM visits v
JOIN workouts w ON v.workout_id = w.workout_id
JOIN templates t ON w.template_id = t.template_id
WHERE w.start_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY t.template_id
ORDER BY count DESC;
