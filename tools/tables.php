<?php
    $dsn = "mysql:dbname=scheshare;host=localhost;charset=utf8mb4";
    $user = "root";
    $password = "root";

    try{
        $dbh = new PDO($dsn, $user, $password);
        $dbh -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE "
        $sql = "INSERT INTO emp (name, dept_id) VALUES ('山田', '営業', '経理', '人事')";
        $sql = "INSERT INTO dept (name) VALUES ('総務', '営業', '経理', '人事')";

//        $sql = "INSERT INTO emp (name, dept_id) VALUES ('山田', '')";

    }catch(PDOException $e){
        echo "DB接続エラー：".$e->getMessage();
    }
        exit;