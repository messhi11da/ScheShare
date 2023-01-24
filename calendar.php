<div id="calendar-area">
    <h2 style="margin:0;">
        <a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . substr('00' . ($m - 1), -2) . "-01" : ($y - 1) . "-12-01" ?>">◁</a>
        <span><?= $y ?>年<?= (int)$m ?>月</span>
        <a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . substr('00' . ($m + 1), -2) . "-01" : ($y + 1) . "-01-01" ?>">▷</a>
    </h2>

    <table id="calendar-table" border="1">
        <tr style="color:#FFFFFF;">
            <!-- 曜日の表示 -->
            <?php for ($day = 0; $day < 7; $day++) : ?>
                <th bgcolor=<?= $day === 0 ? "#FF0000" : ($day === 6 ? "#0000FF" : "#000000") ?>>
                    <?= getWeekName($day); ?>
                </th>
            <?php endfor; ?>
        </tr>
        <!-- 日付の表示 -->
        <?php foreach ($dateArray as $day => $date2) : ?>
            <?php list($y2, $m2, $d2) = explode('-', $date2); ?>
            <?php if (($day) % 7 == 0) : ?>
                <tr>
                <?php endif; ?>
                <td style="opacity: <?= ($m2 < $m || $m2 > $m) ? "0.5" : "1" ?>">
                    <a href="index.php?date=<?= $date2 ?>">
                        <?= ($m2 < $m || $m2 > $m) ? (int)$m2 . "/" . (int)$d2 : (int)$d2 ?>
                    </a>
                </td>
                <?php if (($day) % 7 == 6) : ?>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
</div>