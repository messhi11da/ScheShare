<?php
require_once(dirname(__FILE__) . '/function/functions.php');
/* 部署リストを取得 */
$deptList = selectDept();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <link href="./css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="固有のコード" crossorigin="anonymous">
</head>

<body>
    <header class="header">
        <div>
            <button id="guide-btn"><i class="far fa-question-circle"></i>できること</button>
        </div>
        <div>
            <h1><a style="color: #FFFFFF;" href="index.php">ScheShare</a></h1>
            <p>複数人でスケジュールを共有できるWEBアプリです。</p>
            <span>(*社内利用を想定しています。)</span>
        </div>
        <div>
            <?php if (isset($userId) && $user != false) : ?>
                <p><?= $user['emp_name'].'('.escape($userId).')' ?>さんログイン中</p>
                <p><a class="a-tag" href="login.php">ログアウト</a></p>
            <?php endif; ?>
        </div>
    </header>
    <br>
</body>

</html>