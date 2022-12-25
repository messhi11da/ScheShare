<?php
require_once(dirname(__FILE__) . '/function/functions.php');
session_start();

$deptList = selectDept(); // 部署リストを取得

if (isset($_POST['submit_register'])) {
    $error = registerErrCheck($_POST['emp_id'], $_POST['emp_name'], $_POST['password'], $_POST['dept_id']);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ScheShare - ユーザー登録画面</title>
    <link href="./css/style.css" rel="stylesheet">
</head>

<body>
    <div class="login-header">
        <h1><a href="index.php">ScheShare</a></h1>
        <p>複数ユーザーでスケジュールを共有できるアプリです。</p>
    </div>

    <form class="login-form" action="" method="post">
        
        <!-- 入力エラー表示 -->
        <?php if (!empty($error)) : ?>
            <ul>
                <?php foreach ($error as $err) : ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- 登録フォーム -->
        <table align="center">
            <tr>
                <td>ID(社員番号):</td>
                <td><input type="text" name="emp_id"></td>
            </tr>
            <tr>
                <td>名前:</td>
                <td><input type="text" name="emp_name"></td>
            </tr>
            <tr>
                <td>パスワード:</td>
                <td><input type="password" name="password"></td>
            </tr>
            <tr>
                <td>所属部署:</td>
                <td>
                    <select name="dept_id">
                        <option value="0">所属部署</option>
                        <?php foreach ($deptList as $dept) : ?>
                            <option value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <br>
        <div style="text-align: center;">
            <button type="submit" name="submit_register" value="1">登録</button>
            <a href="login.php">ログイン画面へ</a>
        </div>


    </form>


</body>

</html>