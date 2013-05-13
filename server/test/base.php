<?php
function build_test_database($Database) {
    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Message (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user CHAR(1),
            message VARCHAR(4096),
            conversation_id CHAR(65),
            time_stamp DATETIME
        )"
    );

    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Conversation (
            id CHAR(65) PRIMARY KEY,
            manager_id INT UNSIGNED,
            username VARCHAR(32),
            last_update_check DATETIME,
            last_id INT UNSIGNED
        )"
    );

    $Database->exec(
        "CREATE TABLE IF NOT EXISTS Manager (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(32) UNIQUE,
            password CHAR(128),
            access_level INT UNSIGNED,
            failed_attempts INT
        )"
    ); 
}


function insert_default_rows($Database) {
    $Database->query(
        "INSERT INTO Conversation (id, last_update_check, last_id)
         VALUES ('conv_id', '2013-01-01 10:10:10', 2)"
    );

    $Database->query(
        "INSERT INTO Message (id, conversation_id, user, message, time_stamp)
         VALUES (1, 'conv_id', 'M', 'manager message', '2013-01-01 10:10:09')"
    );

    $Database->query(
        "INSERT INTO Message (id, conversation_id, user, message, time_stamp)
         VALUES (2, 'conv_id', 'C', 'client message', '2013-01-01 10:10:10')"
    );
}
?>
