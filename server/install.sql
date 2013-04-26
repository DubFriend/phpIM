USE phpIM;

CREATE TABLE IF NOT EXISTS Message (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user CHAR(1),
    message VARCHAR(4096),
    conversation_id CHAR(65) NOT NULL,
    time_stamp DATETIME NOT NULL,
    INDEX(conversation_id),
    INDEX(time_stamp)
);

CREATE TABLE IF NOT EXISTS Conversation (
    id CHAR(65) PRIMARY KEY,
    manager_id INT UNSIGNED NOT NULL,
    last_edit DATETIME NOT NULL,
    INDEX(manager_id),
    INDEX(last_edit)
);

CREATE TABLE IF NOT EXISTS Manager (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    password CHAR(128) NOT NULL,
    access_level INT UNSIGNED,
    failed_attempts INT NOT NULL,
    INDEX(username)
);

CREATE TABLE IF NOT EXISTS Ip_Check (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip CHAR(45) NOT NULL UNIQUE,
    failed_attempts INT NOT NULL,
    INDEX(ip)
);