<form action="" method="post">
    <h3>社員を検索</h3>
    <div class="search-area">
        <select class="select-dept">
            <option class="dept-option" value="0">全部署</option>
            <?php foreach ($deptList as $dept) : ?>
                <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= escape($dept['dept_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input-keyword" type="text" placeholder="社員を検索" style="width: 50%;">
        <button class="search-btn" type="submit" data-checked='<?= json_encode($displayEmpList); ?>'><i class="fas fa-search"></i></button>
        <div class="emp-table" style="display: none;">
        </div>

        <button class="display-btn" type="submit" name="submit_display" value="1">スケジュールを表示</button>
    </div>
</form>