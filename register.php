<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();

$deptList = selectDept(); // 部署リストを取得


if (isset($_POST['submit_register'])) {
    $empId = preg_replace('/( |　)/', '', escape($_POST['emp_id']));
    $empName = preg_replace('/( |　)/', '', escape($_POST['emp_name']));
    $password = preg_replace('/( |　)/', '', escape($_POST['password']));
    $deptId = escape($_POST['dept_id']);

    if (empty($empId)) $error[] = '社員番号を入力してください。';
    elseif (!is_numeric($empId)) $error[] = '社員番号は数字で入力してください。';
    else {
        $res = selectEmp($empId);
        //  var_dump($res);
        if ($res != false) $error[] = 'この社員番号は既に登録されています。';
    }
    if (empty($password)) $error[] = 'パスワードを入力してください。';
    elseif (mb_strlen($password) < 8) $error[] = 'パスワードは8文字以上を入力してください。';
    if (empty($empName) || is_numeric($empName)) $error[] = '名前を正しく入力してください';
    if (empty($deptId)) $error[] = '所属部署を選択してください。';

    if (empty($error)) {
        $res = insertEmp($empId, $empName, $deptId, $password);
        if ($res) {
            $_SESSION['user_id'] = $empId;
            header('Location: http://localhost/ScheShare/index.php');
            exit;
        } else {
            $error[] = 'エラー：登録できませんでした。';
        }
    }
    var_dump($error);
    //  exit;

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
    <div style="text-align: center;">
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