-- Create database
CREATE DATABASE IF NOT EXISTS kino_duomenys;
USE kino_duomenys;

-- Users table (encrypted passwords)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE
);

-- Movies table
CREATE TABLE movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    release_year INT,
    director VARCHAR(100),
    description TEXT,
    genre VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 10),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Actors table (for complex search)
CREATE TABLE actors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    birth_year INT
);

-- Movie-Actors relationship (many-to-many)
CREATE TABLE movie_actors (
    movie_id INT,
    actor_id INT,
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);

-- Files table (for file handling requirement)
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255),
    file_path VARCHAR(255),
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO movies (title, release_year, director, genre, description) VALUES
('Tamsos riteris', 2008, 'Christopher Nolan', 'Action', 'Betmeno kova su Džokeriu'),
('Žiedų valdovas: Karaliaus sugrįžimas', 2003, 'Peter Jackson', 'Fantasy', 'Epinis fantastinis nuotykis'),
('Inception', 2010, 'Christopher Nolan', 'Sci-Fi', 'Sapnų vagystės'),
('Pulp Fiction', 1994, 'Quentin Tarantino', 'Crime', 'Ne chronologinė gangsterių istorija');

INSERT INTO actors (name, birth_year) VALUES
('Christian Bale', 1974),
('Heath Ledger', 1979),
('Elijah Wood', 1981),
('Ian McKellen', 1939);

-- Connect movies with actors
INSERT INTO movie_actors (movie_id, actor_id) VALUES
(1, 1), (1, 2), (2, 3), (2, 4);