<?php

/* ログインチェック */
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === "") {
    header('Location: http://localhost/ScheShare/login.php');
    exit();
}

/* ログインしていたらユーザー情報を取得 */
$userId = $_SESSION['user_id'];
$user = selectEmp($userId);

?>