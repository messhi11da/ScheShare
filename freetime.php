<!DOCTYPE html>
<html lang="ja">

<body>

    <!-- 空き時間テーブル -->
    <div id="display-free-time" class="display-schedule" style="display: none;">
        <div class="schedule-header">
            <?php if (!empty($_SESSION['display_emp'])) : ?>
                <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
                    <?= escape($displayEmp['emp_name']) . "(" . escape($displayEmp['emp_id']) . ")さん " ?>
                <?php endforeach; ?>
                の空いてる時間帯
            <?php endif; ?>

            <button class="close-btn" type="button">×</button>
        </div>

        <table class="free-time-table" border="1" bgcolor="#FFFFFF" width="700">
        </table>
    </div>

</body>

</html>