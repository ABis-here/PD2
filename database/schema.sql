DROP DATABASE IF EXISTS kino_duomenys;
CREATE DATABASE kino_duomenys;
USE kino_duomenys;

-- 1. Users table (with encrypted passwords)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE
);

-- 2. Movies table
CREATE TABLE movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    release_year INT,
    director VARCHAR(100),
    description TEXT,
    genre VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Reviews table
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

-- 4. Actors table
CREATE TABLE actors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    birth_year INT
);

-- 5. Movie-Actors relationship (many-to-many)
CREATE TABLE movie_actors (
    movie_id INT,
    actor_id INT,
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);

-- 6. Watchlist table (for requirement #7: calculation)
CREATE TABLE watchlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    movie_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_watchlist (user_id, movie_id)
);

-- 8. Files table (for requirement #9: file handling)
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255),
    file_path VARCHAR(255),
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_size INT,
    file_type VARCHAR(50),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Sample movies
INSERT INTO movies (title, release_year, director, genre, description) VALUES
('Tamsos riteris', 2008, 'Christopher Nolan', 'Veiksmo', 'Betmeno kova su Džokeriu. Vienas geriausių superherojų filmų.'),
('Žiedų valdovas: Karaliaus sugrįžimas', 2003, 'Peter Jackson', 'Fantastika', 'Epinis fantastinis nuotykis, paskutinė trilogijos dalis.'),
('Inception', 2010, 'Christopher Nolan', 'Mokslinė fantastika', 'Sapnų vagystės. Galvosūkis apie realybę ir sapnus.'),
('Pulp Fiction', 1994, 'Quentin Tarantino', 'Kriminalinis', 'Ne chronologinė gangsterių istorija su nepamirštamais dialogais.'),
('The Shawshank Redemption', 1994, 'Frank Darabont', 'Drama', 'Istorija apie viltį ir išsilaisvinimą kalėjime.');


-- Sample actors
INSERT INTO actors (name, birth_year) VALUES
('Christian Bale', 1974),
('Heath Ledger', 1979),
('Elijah Wood', 1981),
('Ian McKellen', 1939),
('Leonardo DiCaprio', 1974),
('John Travolta', 1954),
('Tim Robbins', 1958);

-- Connect movies with actors
INSERT INTO movie_actors (movie_id, actor_id) VALUES
(1, 1), (1, 2),  -- Tamsos riteris: Christian Bale, Heath Ledger
(2, 3), (2, 4),  -- Žiedų valdovas: Elijah Wood, Ian McKellen
(3, 5),          -- Inception: Leonardo DiCaprio
(4, 6),          -- Pulp Fiction: John Travolta
(5, 7);          -- Shawshank: Tim Robbins

