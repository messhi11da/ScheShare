<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();
$_SESSION = array();

$deptList = selectDept(); // 部署リストを取得

if(isset($_POST['submit_login'])){
    $empId = preg_replace('/( |　)/', '', escape($_POST['emp_id']));
    $password = preg_replace('/( |　)/', '', escape($_POST['password']));


    if (empty($empId)) $error[] = '社員番号を入力してください。';
    elseif (!is_numeric($empId)) $error[] = '社員番号は数字で入力してください。';
    if (empty($password)) $error[] = 'パスワードを入力してください。';
    elseif (mb_strlen($password) < 8) $error[] = 'パスワードは8文字以上を入力してください。';

    if(empty($error)){
        $emp = selectEmpPass($empId);
        if($emp != false){
            if(password_verify($password, $emp['password'])){
                $_SESSION['user_id'] = $emp['emp_id'];
                header('Location: http://localhost/ScheShare/index.php');
                exit();            
            }else $error[] = 'パスワードが間違っています。';
        }else $error[] = 'IDかパスワードが間違っています。';
    }
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
    <div style="text-align: center;">
        <h1><a href="index.php">ScheShare</a></h1>
        <p>複数ユーザーでスケジュールを共有できるアプリです。</p>
    </div>

  
    
    <form class="login-form" action="" method="post">
            <!-- 入力エラー表示 -->
            <?php if(!empty($error)): ?>
                <ul>
                    <?php foreach($error as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- ログインフォーム -->
            <table align="center">
                <tr height="50">
                    <td>ID(社員番号):</td>
                    <td><input for="emp_id" type="text" name="emp_id"></td>
                </tr>
                <tr height="50">
                    <td>パスワード:</td>
                    <td><input for="password" type="password" name="password"></td>
                </tr>
            </table>
            <br>
            <div style="text-align: center;">
                <button type="submit" name="submit_login" value="1">ログイン</button>
                <a href="register.php">新規登録</a>
            </div>
        </form>

</body>

</html>