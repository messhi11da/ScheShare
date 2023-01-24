<?php
require_once(dirname(__FILE__) . '/function/functions.php');

/* ログインしてなかったら強制ログアウト */
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === "") {
    header('Location: login.php');
    exit();
}

/* ログインしていたらユーザー情報を取得 */
$userId = $_SESSION['user_id'];
$user = selectEmp($userId);

/* ログインユーザーをスケジュール表示用変数に格納 */
if (!isset($_SESSION['display_emp']) || $_SESSION['display_emp'] === "" || !in_array($user, $_SESSION['display_emp'])) {
    $_SESSION['display_emp'] = array();
    $_SESSION['display_emp'][] = $user;
}
?>