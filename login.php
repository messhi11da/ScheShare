<?php
session_start();
require dirname(__FILE__) . '/header.php';
$_SESSION = array();

// ログイン処理
if (isset($_POST['submit_login'])) {
    $error = loginErrCheck($_POST['emp_id'], $_POST['password']);
}

// ゲスト用ログイン処理
if(isset($_POST['submit_guest'])){
    $_SESSION['user_id'] = '1111'; // ゲスト用ID
    header('Location: index.php');
    exit();    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ScheShare - ログイン画面</title>
</head>

<body>
    <form class="login-form" action="" method="post">
        <!-- 入力エラー表示 -->
        <?php if (!empty($error)) : ?>
            <p>入力エラー：</p>
            <ul class="error-message">
                <?php foreach ($error as $err) : ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h3>ログイン画面</h3>
        <!-- ログインフォーム -->
        <div>
            <table align="center">
                <tr height="50">
                    <td>ID(社員番号)：</td>
                    <td><input for="emp_id" type="text" name="emp_id"></td>
                </tr>
                <tr height="50">
                    <td>パスワード：</td>
                    <td><input for="password" type="password" name="password"></td>
                </tr>
            </table>
            <br>
            <div class="container">
                <button type="submit" name="submit_login" value="1">ログイン</button>
                <a class="a-tag" href="register.php">新規登録</a>
            </div>
            <br>
            <div style="text-align: center;">
                <button type="submit" name="submit_guest" value="1">ゲスト用ログイン</button>
                <br>
                <small>(こちらからゲスト用ID:1111でログインできます。）</small>
            </div>
        </div>

    </form>

    <?php
    // できること
    require_once dirname(__FILE__) . '/guide.php';
    // フッター
    require dirname(__FILE__) . '/footer.php';
    ?>

    <script>
        // 「できること」を表示
        var displayGuide = document.getElementById("display-guide");
        var guideBtn = document.getElementById("guide-btn");
        guideBtn.addEventListener("click", function(e) {
            displayGuide.style.display = "block";
        });
        // ×ボタンを押すと閉じる
        var closeBtnList = document.querySelectorAll(".close-btn");
        for (var closeBtn of closeBtnList) {
            closeBtn.addEventListener("click", function() {
                var parent = this.closest(".display-elem");
                parent.style.display = "none";
            });
        }
    </script>
</body>

</html>