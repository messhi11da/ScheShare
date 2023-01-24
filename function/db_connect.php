<?php

function dbConnect(){
    $dsn = "mysql:host=localhost;dbname=scheshare;charset=utf8mb4";
    $user = "root";
    $password = "root";
    
    /*
    $dsn = "mysql:host=localhost;dbname=mi221114_scheshare;charset=utf8";
    $user = "mi221114_admin";
    $password = "scheshare.admin";
    */
    try {
        $dbh = new PDO($dsn, $user, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo '接続エラー: ' . $e->getMessage();
        exit;
    }
    return $dbh;
}

?>