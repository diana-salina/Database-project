CREATE PROCEDURE `get_workouts_by_date`(
    IN p_date DATE
)
proc_end: BEGIN
    SELECT 
        w.workout_id,
        w.start_time,
        t.name AS template_name,
        t.duration,
        (SELECT COUNT(*) FROM visits v WHERE v.workout_id = w.workout_id) AS booked,
        w.visit_limit,
        c.name AS coach_name,
        c.surname AS coach_surname,
        c.patronymic AS coach_patronymic
    FROM workouts w
    JOIN templates t ON w.template_id = t.template_id
    JOIN coaches c ON w.coach_id = c.coach_id
    WHERE DATE(w.start_time) = p_date
    ORDER BY w.start_time ASC;
END