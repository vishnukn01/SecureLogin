<?php
require_once('config.php');
require_once('includes/class-db.php');

$db->db->query("
	CREATE TABLE IF NOT EXISTS users(
	ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30),
    email VARCHAR(255),
	password VARCHAR(255),
	fullname VARCHAR(30)
	)"
);

$users_info = $db->db->query('DESCRIBE users');

echo '<pre>' , print_r($users_info,1) , '</pre>';

$db->db->query( "CREATE TABLE IF NOT EXISTS auth_tokens (
        ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        selector CHAR(16),
        token CHAR(64),
        expires DATETIME
    )");

$db->db->query( "CREATE TABLE IF NOT EXISTS password_reset (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    selector CHAR(16),
    token CHAR(64),
    expires BIGINT(20)
)");

?>