USE lab4;

INSERT INTO topics (title, author, created_at, is_pinned)
SELECT
    CONCAT('Тестова тема №', numbers.n) AS title,
    CONCAT('Автор ', MOD(numbers.n - 1, 20) + 1) AS author,
    DATE_ADD('2026-01-01 12:00:00', INTERVAL MOD(numbers.n - 1, 300) DAY) AS created_at,
    IF(MOD(numbers.n, 10) = 0, 1, 0) AS is_pinned
FROM (
    SELECT hundreds.d * 100 + tens.d * 10 + units.d + 1 AS n
    FROM
      (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
       UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
       UNION ALL SELECT 8 UNION ALL SELECT 9) AS hundreds
    CROSS JOIN
      (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
       UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
       UNION ALL SELECT 8 UNION ALL SELECT 9) AS tens
    CROSS JOIN
      (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
       UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
       UNION ALL SELECT 8 UNION ALL SELECT 9) AS units
) AS numbers
WHERE NOT EXISTS (
    SELECT 1 FROM topics AS existing
    WHERE existing.title = CONCAT('Тестова тема №', numbers.n)
);

SELECT COUNT(*) AS total_topics FROM topics;
SELECT COUNT(*) AS seeded_topics FROM topics WHERE title LIKE 'Тестова тема №%';
SELECT COUNT(*) AS pinned_topics FROM topics WHERE is_pinned = 1;
SELECT revision FROM lab7_pinned_cache_state WHERE id = 1;
