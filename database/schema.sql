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

-- Sample movies
INSERT INTO movies (title, release_year, director, genre, description) VALUES
('Tamsos riteris', 2008, 'Christopher Nolan', 'Veiksmo', 'Betmeno kova su Džokeriu. Vienas geriausių superherojų filmų.'),
('Žiedų valdovas: Karaliaus sugrįžimas', 2003, 'Peter Jackson', 'Fantastika', 'Epinis fantastinis nuotykis, paskutinė trilogijos dalis.'),
('Inception', 2010, 'Christopher Nolan', 'Mokslinė fantastika', 'Sapnų vagystės. Galvosūkis apie realybę ir sapnus.'),
('Pulp Fiction', 1994, 'Quentin Tarantino', 'Kriminalinis', 'Ne chronologinė gangsterių istorija su nepamirštamais dialogais.'),
('The Shawshank Redemption', 1994, 'Frank Darabont', 'Drama', 'Istorija apie viltį ir išsilaisvinimą kalėjime.'),
('Fight Club', 1999, 'David Fincher', 'Drama', 'Psichologinė drama apie tapatybę, vartotojiškumą ir anarchiją.'),
('Forrest Gump', 1994, 'Robert Zemeckis', 'Drama', 'Šilta istorija apie paprastą vyrą, tapusį daugelio istorinių įvykių liudininku.'),
('Matrix', 1999, 'The Wachowskis', 'Mokslinė fantastika', 'Realybės iliuzija, dirbtinis intelektas ir žmonijos kova už laisvę.'),
('Gladiatorius', 2000, 'Ridley Scott', 'Veiksmo', 'Romėnų generolo keršto istorija Koliziejaus arenoje.'),
('Interstellar', 2014, 'Christopher Nolan', 'Mokslinė fantastika', 'Kelionė per kosmosą ir laiką siekiant išgelbėti žmoniją.'),
('Titanic', 1997, 'James Cameron', 'Romantinis', 'Tragiška meilės istorija legendinio laivo katastrofos fone.'),
('Se7en', 1995, 'David Fincher', 'Trileris', 'Du detektyvai medžioja serijinį žudiką, paremtą septyniomis mirtinomis nuodėmėmis.'),
('Žvaigždžių karai: Nauja viltis', 1977, 'George Lucas', 'Fantastika', 'Epinės kosminės sagos pradžia apie gėrio ir blogio kovą.');


-- Sample actors
INSERT INTO actors (name, birth_year) VALUES
('Christian Bale', 1974),
('Heath Ledger', 1979),
('Elijah Wood', 1981),
('Ian McKellen', 1939),
('Leonardo DiCaprio', 1974),
('John Travolta', 1954),
('Tim Robbins', 1958),
('Morgan Freeman', 1937),
('Brad Pitt', 1963),
('Edward Norton', 1969),
('Keanu Reeves', 1964),
('Russell Crowe', 1964),
('Matthew McConaughey', 1969),
('Kate Winslet', 1975),
('Brad Pitt (Se7en)', 1963),
('Harrison Ford', 1942);


INSERT INTO movie_actors (movie_id, actor_id) VALUES
-- 1. Tamsos riteris
(1, 1), (1, 2),

-- 2. Žiedų valdovas: Karaliaus sugrįžimas
(2, 3), (2, 4),

-- 3. Inception
(3, 5),

-- 4. Pulp Fiction
(4, 6),

-- 5. The Shawshank Redemption
(5, 7), (5, 8),

-- 6. Fight Club
(6, 9), (6, 10),

-- 7. Forrest Gump
(7, 8),

-- 8. Matrix
(8, 11),

-- 9. Gladiatorius
(9, 12),

-- 10. Interstellar
(10, 13),

-- 11. Titanic
(11, 5), (11, 14),

-- 12. Se7en
(12, 15), (12, 9),

-- 13. Žvaigždžių karai: Nauja viltis
(13, 16);


