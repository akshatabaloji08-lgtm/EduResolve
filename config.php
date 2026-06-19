<?php
define('DB_HOST', 'sql313.infinityfree.com');
define('DB_USER', 'if0_41933829');
define('DB_PASS', 'AkshataBaloji');
define('DB_NAME', 'if0_41933829_campuscare');

define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'campuscareproject01@gmail.com');
define('SMTP_PASS',     'cczbzjcsvhfywsfd');
define('SMTP_FROM',     'campuscareproject01@gmail.com');
define('SMTP_FROM_NAME','CampusCare Management System');

define('ADMIN_EMAIL', 'campuscareproject01@gmail.com');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('Connection Failed: ' . mysqli_connect_error());
}