-- Movie Ticket Booking System Database
CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

-- Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Movies
CREATE TABLE movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  genre VARCHAR(100),
  language VARCHAR(50),
  duration INT COMMENT 'minutes',
  rating DECIMAL(3,1) DEFAULT 0,
  poster VARCHAR(255),
  trailer_url VARCHAR(255),
  release_date DATE,
  status ENUM('now_showing','upcoming') DEFAULT 'now_showing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Theaters / Screens
CREATE TABLE theaters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(200),
  total_seats INT DEFAULT 60
);

-- Shows (a movie at a theater on a date/time)
CREATE TABLE shows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movie_id INT NOT NULL,
  theater_id INT NOT NULL,
  show_date DATE NOT NULL,
  show_time TIME NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 200,
  FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
  FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE CASCADE
);

-- Bookings
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  show_id INT NOT NULL,
  seats VARCHAR(255) NOT NULL COMMENT 'comma separated seat numbers like A1,A2',
  total_seats INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  booking_code VARCHAR(20) NOT NULL UNIQUE,
  status ENUM('confirmed','cancelled') DEFAULT 'confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
);

-- Default admin (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@cinema.com', '$2y$10$e0NRz0YQ0o0L0Q3K6c1q0e1zJq3l5m4F2Q0xJX2eMfQ8yJrA6c1qC', 'admin');
-- NOTE: regenerate the admin password hash by registering or running the install.php script.

-- Sample theaters
INSERT INTO theaters (name, location, total_seats) VALUES
('PVR Cinemas - Screen 1', 'Downtown Mall', 60),
('INOX - Screen 2', 'City Center', 60),
('Cinepolis - IMAX', 'West Plaza', 60);

-- Sample movies
INSERT INTO movies (title, description, genre, language, duration, rating, poster, release_date, status) VALUES
('Inception', 'A thief who steals corporate secrets through dream-sharing technology is given the inverse task of planting an idea.', 'Sci-Fi, Action', 'English', 148, 8.8, 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', '2010-07-16', 'now_showing'),
('Interstellar', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity''s survival.', 'Adventure, Sci-Fi', 'English', 169, 8.6, 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', '2014-11-07', 'now_showing'),
('The Dark Knight', 'Batman raises the stakes in his war on crime with the help of Lt. Jim Gordon and Harvey Dent.', 'Action, Crime', 'English', 152, 9.0, 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg', '2008-07-18', 'now_showing'),
('Dune Part Two', 'Paul Atreides unites with the Fremen to wage war against the conspirators who destroyed his family.', 'Adventure, Sci-Fi', 'English', 166, 8.5, 'https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg', '2024-03-01', 'now_showing'),
('Avatar 3', 'The next chapter of the Avatar saga.', 'Adventure, Fantasy', 'English', 180, 0, 'https://image.tmdb.org/t/p/w500/kyeqWdyUXW608qlYkRqosgbbJyK.jpg', '2025-12-19', 'upcoming');

-- Sample shows (today and next 2 days)
INSERT INTO shows (movie_id, theater_id, show_date, show_time, price) VALUES
(1, 1, CURDATE(), '10:00:00', 250),
(1, 1, CURDATE(), '14:00:00', 300),
(1, 2, CURDATE(), '19:00:00', 350),
(2, 2, CURDATE(), '11:00:00', 280),
(2, 3, CURDATE(), '20:00:00', 400),
(3, 1, CURDATE(), '17:00:00', 320),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 300),
(4, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 350),
(4, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '21:00:00', 400);
