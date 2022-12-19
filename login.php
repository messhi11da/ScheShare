<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();

$deptList = selectDept(); // 部署リストを取得

/*
if (!isset($_POST['submit_register'])) {
    $res = insertEmp($_POST['emp_id'], $_POST['dept_id'], $_POST['password']);
    if ($res) {
        $_SESSION['user_id'] = $_POST['emp_id'];
        header('Location: http://localhost/ScheShare/index.php');
        exit;
    }
}
*/
?>

<!DOCTYPE html>
<html>


<head>
    <meta charset="utf-8">
    <title>ScheShare - ログイン画面</title>
    <link href="./css/style.css" rel="stylesheet">Ï
</head>

<body>
    <h2>ScheShare</h2>
    <p>複数ユーザーでスケジュールを共有できるアプリです。</p>

    <form id="login-form" class="display-elem" action="login_check.php" method="post" style="display: block;">
        <label id="emp_id">ID(社員番号):</label>
        <br>
        <input for="emp_id" type="">
        <br>
        <label id="password">パスワード:</label>
        <br>
        <input type="password">
        <br>
        <button type="submit" name="submit_login" value="1">ログイン</button>
        <a id="add-emp-btn" href="">新規登録</a>
    </form>

    <form id="register-form" class="display-elem" action="" method="post" style="display: none;">
        <label id="emp_id">ID(社員番号):</label>
        <br>
        <input for="emp_id" type="text" name="emp_id">
        <br>
        <label id="emp_name">名前:</label>
        <br>
        <input for="emp_name" type="text" name="emp_name">
        <br>
        <label id="password">パスワード:</label>
        <br>
        <input type="password" name="password">
        <br>
        <label id="dept">所属部署名:</label>
        <br>
        <select name="dept_id">
            <option>所属部署</option>
            <?php foreach ($deptList as $dept) : ?>
                <option value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit" name="submit_register" value="1">登録</button>


    </form>

    <script>
        var loginForm = document.getElementById("login-form");
        var registerForm = document.getElementById("register-form");
        var addEmpBtn = document.getElementById("add-emp-btn");
        addEmpBtn.addEventListener("click", function(event) {
            event.preventDefault();
            console.log(loginForm);
            loginForm.style.display = "none";
            registerForm.style.display = "block";
        })
    </script>
</body>

</html>