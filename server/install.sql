USE phpIM;

CREATE TABLE IF NOT EXISTS Message (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user CHAR(1),
    message VARCHAR(4096),
    conversation_id CHAR(65),
    time_stamp DATETIME,
    INDEX(conversation_id),
    INDEX(time_stamp)
);

CREATE TABLE IF NOT EXISTS Conversation (
    id CHAR(65) PRIMARY KEY,
    manager_id INT UNSIGNED,
    last_edit DATETIME,
    INDEX(manager_id),
    INDEX(last_edit)
);

CREATE TABLE IF NOT EXISTS Manager (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) UNIQUE,
    password CHAR(128),
    access_level INT UNSIGNED,
    failed_attempts INT,
    INDEX(username)
);

CREATE TABLE IF NOT EXISTS Ip_Check (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip CHAR(45) UNIQUE,
    failed_attempts INT,
    INDEX(ip)
);
