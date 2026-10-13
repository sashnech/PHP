
EXPLAIN SELECT * FROM topics WHERE author = 'Олександр';

CREATE INDEX idx_topics_author ON topics (author);

EXPLAIN SELECT * FROM topics WHERE author = 'Олександр';
