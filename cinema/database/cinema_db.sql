CREATE DATABASE IF NOT EXISTS cinema_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cinema_db;

-- 1. USERS – GROUPS – SERVICES

CREATE TABLE users (
    id       INT          NOT NULL AUTO_INCREMENT,
    username VARCHAR(50)  NOT NULL UNIQUE,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE groups (
    id   INT         NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE services (
    username    VARCHAR(50)  NOT NULL,
    descrizione VARCHAR(255) NOT NULL,
    PRIMARY KEY (username)
);

CREATE TABLE users_has_groups (
    users_id  INT NOT NULL,
    groups_id INT NOT NULL,
    PRIMARY KEY (users_id, groups_id),
    FOREIGN KEY (users_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (groups_id) REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE services_has_groups (
    services_username VARCHAR(50) NOT NULL,
    groups_id         INT         NOT NULL,
    PRIMARY KEY (services_username, groups_id),
    FOREIGN KEY (services_username) REFERENCES services(username) ON DELETE CASCADE,
    FOREIGN KEY (groups_id)         REFERENCES groups(id)         ON DELETE CASCADE
);

-- 2. FILM

CREATE TABLE movies (
    id     INT               NOT NULL AUTO_INCREMENT,
    titolo VARCHAR(150)      NOT NULL,
    trama  TEXT,
    durata INT               NOT NULL,
    anno   SMALLINT UNSIGNED NOT NULL,
    poster VARCHAR(255),
    PRIMARY KEY (id)
);

CREATE TABLE genres (
    id   INT         NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE movies_has_genre (
    movie_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

CREATE TABLE directors (
    id      INT         NOT NULL AUTO_INCREMENT,
    name    VARCHAR(80) NOT NULL,
    surname VARCHAR(80) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE movie_has_director (
    movie_id    INT NOT NULL,
    director_id INT NOT NULL,
    PRIMARY KEY (movie_id, director_id),
    FOREIGN KEY (movie_id)    REFERENCES movies(id)    ON DELETE CASCADE,
    FOREIGN KEY (director_id) REFERENCES directors(id) ON DELETE CASCADE
);

CREATE TABLE actors (
    id      INT         NOT NULL AUTO_INCREMENT,
    name    VARCHAR(80) NOT NULL,
    surname VARCHAR(80) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE movie_has_actor (
    movie_id INT NOT NULL,
    actor_id INT NOT NULL,
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);


-- 3. SALE E POSTI

CREATE TABLE classrooms (
    id        INT         NOT NULL AUTO_INCREMENT,
    nome_sala VARCHAR(80) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE seats (
    id           INT        NOT NULL AUTO_INCREMENT,
    classroom_id INT        NOT NULL,
    fila         VARCHAR(5) NOT NULL,
    numero       INT        NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_seat (classroom_id, fila, numero),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
);


-- 4. PROIEZIONI


CREATE TABLE projections (
    id           INT          NOT NULL AUTO_INCREMENT,
    movie_id     INT          NOT NULL,
    classroom_id INT          NOT NULL,
    data         DATE         NOT NULL,
    ora          TIME         NOT NULL,
    prezzo       DECIMAL(5,2) NOT NULL,
    lingua       VARCHAR(30)  NOT NULL DEFAULT 'Italiano',
    formato      VARCHAR(20)  NOT NULL DEFAULT '2D',
    PRIMARY KEY (id),
    FOREIGN KEY (movie_id)     REFERENCES movies(id)     ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
);


-- 5. PRENOTAZIONI


CREATE TABLE bookings (
    id                INT          NOT NULL AUTO_INCREMENT,
    user_id           INT          NOT NULL,
    data_prenotazione DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    totale            DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE booked_seats (
    id            INT NOT NULL AUTO_INCREMENT,
    booking_id    INT NOT NULL,
    projection_id INT NOT NULL,
    seat_id       INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_booked (projection_id, seat_id),
    FOREIGN KEY (booking_id)    REFERENCES bookings(id)    ON DELETE CASCADE,
    FOREIGN KEY (projection_id) REFERENCES projections(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id)       REFERENCES seats(id)       ON DELETE CASCADE
);