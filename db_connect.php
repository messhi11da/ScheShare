<?php

$dsn = "mysql:host=localhost;dbname=scheshare;charset=utf8mb4";
$user = "root";
$password = "root";

try {
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'DB接続エラー: ' . $e->getMessage();
    exit;
}

?>