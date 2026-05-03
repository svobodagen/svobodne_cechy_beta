<?php
// db.php
$host = 'db.r4.active24.cz';
$db = 'M5wlyXhy';
$user = 'BETA_SC';
$pass = '21-BetaSCDB';
$port = 3306;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$options = [
     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // V produkci byste neměli vypisovat detaily chyby uživateli
     // die("Chyba připojení k databázi.");
     throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
?>