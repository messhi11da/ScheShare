<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();
$_SESSION = array();

$deptList = selectDept(); // 部署リストを取得

var_dump($_POST);
if(isset($_POST['submit_login'])){
    $empId = preg_replace('/( |　)/', '', escape($_POST['emp_id']));
    $password = preg_replace('/( |　)/', '', escape($_POST['password']));


    if (empty($empId)) $error[] = '社員番号を入力してください。';
    elseif (!is_numeric($empId)) $error[] = '社員番号は数字で入力してください。';
    if (empty($password)) $error[] = 'パスワードを入力してください。';
    elseif (mb_strlen($password) < 8) $error[] = 'パスワードは8文字以上を入力してください。';

    if(empty($error)){
        $tempEmp = selectEmp($empId);
        if($tempEmp != false){
            if(password_verify($password, $tempEmp['password'])){
                $_SESSION['user_id'] = $tempEmp['emp_id'];
                header('Location: http://localhost/ScheShare/index.php');
                exit();            
            }else $error[] = 'パスワードが間違っています。';
        }else $error[] = 'IDかパスワードが間違っています。';
    }
    var_dump($error);

}


?>

<!DOCTYPE html>
<html>


<head>
    <meta charset="utf-8">
    <title>ScheShare - ログイン画面</title>
    <link href="./css/style.css" rel="stylesheet">
</head>

<body>
    <h2>ScheShare</h2>
    <p>複数ユーザーでスケジュールを共有できるアプリです。</p>

    <div class="display-elem" >
        <?php if(!empty($error)): ?>
            <ul>
                <?php foreach($error as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form id="login-form" action="" method="post" style="display: block;">
            <label id="emp_id">ID(社員番号):</label>
            <br>
            <input for="emp_id" type="text" name="emp_id">
            <br>
            <label id="password">パスワード:</label>
            <br>
            <input for="password" type="password" name="password">
            <br>
            <button type="submit" name="submit_login" value="1">ログイン</button>
            <a href="register.php">新規登録</a>
        </form>
    </div>



    <script>
        /*
        var loginForm = document.getElementById("login-form");
        var registerForm = document.getElementById("register-form");
        var addEmpBtn = document.getElementById("add-emp-btn");
        addEmpBtn.addEventListener("click", function(event) {
            event.preventDefault();
            console.log(loginForm);
            loginForm.style.display = "none";
            registerForm.style.display = "block";
        })
        */
    </script>
</body>

</html>