/*
 Sergi Ye
 Akashdeep Singh
 Jinnan Chen
 Eric Huang
 
 01/12/2025
 We generated the initial database structure for our transversal project.
 
 18/03/2026
 We updated the database with the necessary tables and relationships.
 
 14/04/2026
 Major refactor:
 - Unified the old 'Mangas' and 'Animes' tables into a single 'Works' table.
 - Added the 'Type' field (Manga/Anime) to classify each work.
 - Updated the structure to avoid duplicated schemas and improve scalability.
 - Created a universal 'Chapters' table linked to 'Works' via ID_Work.
 - Updated stored procedures, including error handling with SIGNAL and ROLLBACK.
 - Migrated old INSERT data from 'Animes' to the new 'Works' format.
 - Improved login and email-check procedures for better security and consistency.
 
 16/04/2026
 Bug fixes and schema cleanup:
 - Removed duplicate 'Events' table definition (stub with VARCHAR PK was shadowing
   the full definition, leaving Capacity, Image, Schedule and other columns missing).
   and Events.ID_Event (INT); now both are plain INT.
 - Removed redundant 'Chapters' count column from 'Works' to avoid data inconsistency.
 - Renamed 'Works.Gender' to 'Works.Genre'.
 - Normalised FK column casing: 'Email' in Events renamed to 'email' to match Users PK.
 - Made Transcription_Video and Transcription_Audio nullable in Event_Media to allow
   inserts before transcriptions are available.
   
18/04/2026

19/04/2026

 */
 
USE Monogatarya;
 
CREATE TABLE IF NOT EXISTS Users (
    ID_User INT AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    status BOOLEAN DEFAULT FALSE,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,

    CONSTRAINT PK_Users PRIMARY KEY (ID_User)
);
 
CREATE TABLE IF NOT EXISTS Works (
    ID_Work INT AUTO_INCREMENT,
    Type ENUM('Manga', 'Anime'),
    Title VARCHAR(50),
    Subtitle VARCHAR(100),
    Image VARCHAR(500),
    Trailer VARCHAR(500),
    Date_premiere DATE,
    Studio VARCHAR(25),
    Gender VARCHAR(50),
    Description VARCHAR(500),
    Chapters INT NULL,
    Active BOOLEAN DEFAULT FALSE,

    CONSTRAINT PK_Works PRIMARY KEY (ID_Work)
);
 
CREATE TABLE IF NOT EXISTS Chapters (
    ID_Chapter INT AUTO_INCREMENT,
    Title VARCHAR(50),
    Description VARCHAR(100),
    Chapter_Number INT UNIQUE NOT NULL,
    File VARCHAR(500),
    ID_Work INT,

    CONSTRAINT PK_Chapters PRIMARY KEY (ID_Chapter),
    FOREIGN KEY (ID_Work) REFERENCES Works(ID_Work) ON DELETE CASCADE
);
 
CREATE TABLE IF NOT EXISTS Events (
    ID_Event INT AUTO_INCREMENT,
    Title VARCHAR(100) NOT NULL,
    Subtitle VARCHAR(150),
    Description TEXT,
    Date_event DATE NOT NULL,
    Location VARCHAR(150),
    Capacity INT,
    Active BOOLEAN DEFAULT FALSE,

    CONSTRAINT PK_Events PRIMARY KEY (ID_Event)
);
 
CREATE TABLE IF NOT EXISTS Event_Media (
    ID_Media INT AUTO_INCREMENT,
    ID_Event INT NOT NULL,
    Image VARCHAR(500),
    Video VARCHAR(500),
    Audio VARCHAR(500),
    Transcription_Video TEXT,
    Transcription_Audio TEXT
    
    CONSTRAINT PK_Event_Media PRIMARY KEY (ID_Media),
    FOREIGN KEY (ID_Event) REFERENCES Events(ID_Event) ON DELETE CASCADE
);
 
-- PROCEDURES
DELIMITER //
CREATE PROCEDURE sp_comprove_email(
    IN emailP VARCHAR(50),
    OUT exist BOOLEAN
) BEGIN
SELECT
    EXISTS(SELECT 1 FROM Users WHERE email = emailP) INTO exist;
END // 

DELIMITER //
CREATE PROCEDURE sp_update_user(
    IN nameP VARCHAR(50),
    IN surnameP VARCHAR(50),    
    IN emailP VARCHAR(50),
    IN passwordP VARCHAR(100),
    IN bioP TEXT

) BEGIN
SELECT
UPDATE Users
    SET
    name = nameP,
    surname = surnameP,
    password = passwordP,
    bio = bioP
WHERE email = emailP;
END // 

CREATE PROCEDURE sp_login(
    IN emailP VARCHAR(50),
    IN passwordP VARCHAR(100),
    OUT valido BOOLEAN
) BEGIN
SELECT
    EXISTS(SELECT 1 FROM Users WHERE email = emailP AND password = passwordP) INTO valido;
END //
 
DELIMITER //
CREATE PROCEDURE sp_add_Work(
    IN p_Type ENUM('Manga', 'Anime'),
    IN p_Title VARCHAR(25),
    IN p_Subtitle VARCHAR(100),
    IN p_Studio VARCHAR(25),
    IN p_Date_premiere DATE,
    IN p_Gender VARCHAR(50),
    IN p_Description VARCHAR(500), 
    OUT p_ID_Work INT
) 
BEGIN -- Control de errores
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error al insertar la obra en Works';
END;
 
START TRANSACTION;
 
INSERT INTO
    Works (
        Type,
        Title,
        Subtitle,
        Studio,
        Date_premiere,
        Gender,
        Description
    )
VALUES
    (
        p_Type,
        p_Title,
        p_Subtitle,
        p_Studio,
        p_Date_premiere,
        p_Gender,
        p_Description
    );

SELECT LAST_INSERT_ID() INTO p_ID_Work;
 
COMMIT;
END //

DELIMITER //
CREATE PROCEDURE sp_add_Event(
    IN p_Title VARCHAR(100),
    IN p_Subtitle VARCHAR(150),
    IN p_Description TEXT,
    IN p_Image VARCHAR(500),
    IN p_Date_event DATE,
    IN p_Location VARCHAR(150),
    IN p_Capacity INT
)
BEGIN
    -- Control de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error al insertar Evento';
    END;

    START TRANSACTION;

    INSERT INTO Events (
        Title,
        Subtitle,
        Description,
        Image,
        Date_event,
        Location,
        Capacity
    )
    VALUES (
        p_Title,
        p_Subtitle,
        p_Description,
        p_Image,
        p_Date_event,
        p_Location,
        p_Capacity
    );

    COMMIT;
END //

DELIMITER ;