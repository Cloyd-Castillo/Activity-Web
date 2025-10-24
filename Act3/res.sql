-- This file sets up the database schema for a resume builder application

CREATE DATABASE resume_db;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal_info (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    full_name VARCHAR(100) NOT NULL,
    title VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    location VARCHAR(200),
    github_url VARCHAR(200),
    UNIQUE(user_id)
);

CREATE TABLE skills (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL,
    skill_name VARCHAR(50) NOT NULL
);

CREATE TABLE education (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    level VARCHAR(100) NOT NULL,
    school VARCHAR(200) NOT NULL,
    years VARCHAR(50) NOT NULL,
    display_order INTEGER DEFAULT 0
);

CREATE TABLE projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    technologies TEXT,
    link VARCHAR(300),
    display_order INTEGER DEFAULT 0
);

INSERT INTO users (username, password)
VALUES ('admin', '$2y$12$gkR37Yd4xpd.pWeg5RJnfeWjdZMaNH8b2ufM2OElIGS3tp.pACEWC' -- hashed '1234');

INSERT INTO personal_info (user_id, full_name, title, email, phone, location, github_url) 
VALUES (
    1, 
    'Cloyd Robin C. Castillo', 
    'CS-3102', 
    '23-04780@g.batstate-u.edu.ph',
    '+63 966 455 8529',
    'San Antonio, San Pascual, Batangas',
    'https://github.com/Cloyd-Castillo'
);

INSERT INTO skills (user_id, category, skill_name) VALUES
(1, 'Languages', 'PHP'),
(1, 'Languages', 'Python'),
(1, 'Languages', 'C++'),
(1, 'Languages', 'Java'),
(1, 'Databases', 'MySQL'),
(1, 'Databases', 'PostgreSQL');

INSERT INTO education (user_id, level, school, years, display_order) VALUES
(1, 'College', 'Batangas State University', '2023 – Present', 1),
(1, 'Senior High School', 'San Pascual Senior High School 1', '2021-2023', 2),
(1, 'High School', 'San Pascual National High School', '2017-2021', 3);

INSERT INTO projects (user_id, name, description, technologies, link, display_order) VALUES
(1, 'Votify', 
 'Votify is a console-based Java application designed to provide a secure, efficient, and user-friendly online voting system.',
 'Java,MySQL', 
 'https://github.com/Cloyd-Castillo/Votify-DBMS-', 1),
(1, 'ECO-MAP (Group Project)', 
 'EcoMap is a web-based platform designed to make waste management smarter and more efficient for communities.',
 'HTML5,CSS3,JavaScript,Bootstrap 5.3.0,PHP,MySQL', 
 'https://github.com/Andaljc1218/ECO-MAP', 2);
