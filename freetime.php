<!-- 空き時間テーブル -->
<div id="display-free-time" class="display-elem" style="display: none;">
    <div class="header">
        <h3>空いてる時間帯</h3>
        <button class="close-btn" type="button">×</button>
    </div>
    <?php if (!empty($_SESSION['display_emp'])) : ?>
        <h4>対象者：
            <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
                「<?= escape($displayEmp['emp_name']) . "(" . escape($displayEmp['emp_id']) . ")" ?>」
            <?php endforeach; ?>
        </h4>
    <?php endif; ?>

    <table class="free-time-table" border="1" bgcolor="#FFFFFF" width="700">
    </table>
</div>
