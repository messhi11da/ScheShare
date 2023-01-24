<?php
session_start();
require dirname(__FILE__) . '/header.php';

// 新規ユーザー登録処理
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

        <h3>新規登録画面</h3>
        <div>
            <!-- 登録フォーム -->
            <table align="center">
                <tr>
                    <td>ID(社員番号)：</td>
                    <td><input type="text" name="emp_id"></td>
                </tr>
                <tr>
                    <td>名前：</td>
                    <td><input type="text" name="emp_name"></td>
                </tr>
                <tr>
                    <td>パスワード：</td>
                    <td><input type="password" name="password"></td>
                </tr>
                <tr>
                    <td>所属部署：</td>
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
            <div class="container">
                <button type="submit" name="submit_register" value="1">登録</button>
                <a class="a-tag" href="login.php">ログイン画面へ</a>
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