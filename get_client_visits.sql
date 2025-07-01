CREATE PROCEDURE `get_client_visits`(
    IN p_client_id INT
)
proc_end: BEGIN
    SELECT 
        v.visit_id,
        w.workout_id,
        w.start_time,
        t.name AS template_name,
        CONCAT(c.surname, ' ', c.name) AS coach_name,
        v.is_attended,
        w.visit_limit,
        (SELECT COUNT(*) FROM visits WHERE workout_id = w.workout_id) AS booked_count
    FROM visits v
    JOIN workouts w ON v.workout_id = w.workout_id
    JOIN templates t ON w.template_id = t.template_id
    JOIN coaches c ON w.coach_id = c.coach_id
    WHERE v.client_id = p_client_id
    ORDER BY w.start_time DESC;
END