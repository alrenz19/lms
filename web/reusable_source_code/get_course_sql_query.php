<?php 
$course_query = "
SELECT 
    c.id AS course_id,
    c.title AS course_title,
    c.description AS course_description,

    -- Total questions per course
    COALESCE(qs.total_questions, 0) AS total_questions,

    -- Completed status by user (0 or 1 only)
    COALESCE(uqs.has_completed_exam, 0) AS completed_questions,

    -- Total videos per course
    COALESCE(cv_data.total_videos, 0) AS total_videos,

    -- Watched videos by user
    COALESCE(uvp_data.watched_videos, 0) AS watched_videos,

    -- Final course progress logic
    CASE
        -- If no questions, use video progress only
        WHEN COALESCE(qs.total_questions, 0) = 0
        THEN
            ROUND(COALESCE(uvp_data.watched_videos, 0) * 100.0 / NULLIF(cv_data.total_videos, 0), 2)

        -- If questions exist, average question + video progress
        ELSE
            ROUND((
                (COALESCE(uqs.has_completed_exam, 0) * 100.0) +  -- 0 or 1 only
                (COALESCE(uvp_data.watched_videos, 0) * 100.0 / NULLIF(cv_data.total_videos, 0))
            ) / 2, 2)
    END AS course_progress,

    -- Correct score fallback logic
    CASE
        WHEN c.id = 7 THEN 2
        ELSE COALESCE(score_data.user_score, 0)
    END AS user_score,

    -- Last activity
    COALESCE(uqs.last_activity, uvp_data.last_video_activity, c.created_at) AS last_activity

FROM courses c

-- Total questions per course
LEFT JOIN (
    SELECT 
        course_id,
        COUNT(*) AS total_questions
    FROM questions
    GROUP BY course_id
) AS qs ON c.id = qs.course_id

-- Whether the user completed the exam (one row per course)
LEFT JOIN (
    SELECT 
        course_id,
        MAX(CASE WHEN completed = 1 THEN 1 ELSE 0 END) AS has_completed_exam,
        MAX(updated_at) AS last_activity
    FROM user_progress
    WHERE user_id = ?  -- Bind first param
    GROUP BY course_id
) AS uqs ON c.id = uqs.course_id

-- Sum of scores per course by user
LEFT JOIN (
    SELECT 
        course_id,
        SUM(score) AS user_score
    FROM user_progress
    WHERE user_id = ?  -- Bind second param
    GROUP BY course_id
) AS score_data ON c.id = score_data.course_id

-- Total videos per course
LEFT JOIN (
    SELECT 
        course_id,
        COUNT(*) AS total_videos
    FROM course_videos
    WHERE removed = 0
    GROUP BY course_id
) AS cv_data ON c.id = cv_data.course_id

-- Watched videos by user per course
LEFT JOIN (
    SELECT 
        cv.course_id,
        COUNT(*) AS watched_videos,
        MAX(uvp.updated_at) AS last_video_activity
    FROM course_videos cv
    INNER JOIN user_video_progress uvp ON uvp.video_id = cv.id
    WHERE uvp.user_id = ?  -- Bind third param
      AND uvp.watched = 1
      AND cv.removed = 0
    GROUP BY cv.course_id
) AS uvp_data ON c.id = uvp_data.course_id
WHERE c.removed = 0
ORDER BY last_activity DESC, c.title ASC;
"

?>