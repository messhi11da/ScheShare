<?php
session_start();
require_once(dirname(__FILE__) . '/login_check.php');
require dirname(__FILE__) . '/header.php';

/* 表示するカレンダー情報を取得 */
if (isset($_GET['date'])) {
  $date = escape($_GET['date']);
  list($y, $m, $d) = explode('-', $date);
} else {
  $date = getToday();
  list($y, $m, $d) = explode('-', $date);
}
$dateArray = createCalendar($m, $y); // 一ヶ月分の日付データ
$week = getWeek($date, $dateArray); // 一週間分の日付データ

/* 表示する社員情報を取得　*/
if (isset($_POST['submit_display'], $_POST['checked_emp'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $_SESSION['display_emp'][] = selectEmp($checkedEmpId);
  }
}
/* 選択中の社員を解除 */
if (isset($_POST['submit_reset'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
}

/* 新規スケジュール登録 */
if (isset($_POST['submit_add'])) {
  insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp']);
  header("Location: index.php?date={$date}");
  exit();
}

/* スケジュール編集 */
if (isset($_POST['submit_edit'])) {
  updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp'], $_POST['schedule_id']);
  header("Location: index.php?date={$date}");
  exit();
}

/* スケジュール削除 */
if (isset($_POST['submit_delete'])) {
  deleteSchedule($_POST['schedule_id']);
  header("Location: index.php?date={$date}");
  exit();
}

/* 非同期通信用変数 */
foreach ($_SESSION['display_emp'] as $displayEmp) {
  $displayEmpList[] = $displayEmp['emp_id'];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <title>ScheShare</title>
</head>

<body>

  <div id="wrapper">
    <div class="container">
      <!-- 社員検索画面エリア -->
      <?php require_once dirname(__FILE__) . '/search.php' ?>
      <!-- カレンダー表示エリア -->
      <?php require_once dirname(__FILE__) . '/calendar.php' ?>
    </div>
    <br>

    <!-- スケジュール表示エリア -->
    <div id="schedule-area">
      <div class="header">
        <form action="" method="post" style="flex-basis: 70%;">
          以下の社員のスケジュールを表示中
          <button type="submit" name="submit_reset" value="1">選択解除</button>
          <br>
          <?php if (!empty($_SESSION['display_emp'])) : ?>
            <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
              「<?= escape($displayEmp['emp_name']) . "(" . escape($displayEmp['emp_id']) . ")」" ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </form>

        <div>
          <button id="add-btn" class="blue" type="button">新規スケジュール追加</button>
          <button id="free-time-btn" class="green" type="button" data-id='<?= json_encode($displayEmpList); ?>' data-week='<?= json_encode($week) ?>'>空き時間を表示</button>
        </div>
      </div>

      <?php
      if (!empty($_SESSION['display_emp'])) :
        foreach ($_SESSION['display_emp'] as $key => $displayEmp) :
      ?>
          <div class="container">
            <!-- スケジュールテーブルの左側に社員情報を表示 -->
            <div class="emp-profile">
              <div>
                <?= escape($displayEmp['dept_name']) ?>
                <?= escape($displayEmp['emp_name']) ?>
              </div>
              (<?= escape($displayEmp['emp_id']) ?>)
            </div>

            <!-- 一週間のスケジュールテーブル -->
            <table class="schedule-table" border="1">

              <?php if ($key === 0) : ?>
                <tr height="20">
                  <?php foreach ($week as $day => $date2) : ?>
                    <?php list($y, $m, $d) = explode('-', $date2); ?>
                    <th><?= (int)$m . "/" . (int)$d . "(" . getWeekName($day) . ")" ?></th>
                  <?php endforeach; ?>
                </tr>
              <?php endif; ?>

              <?php
              $scheduleFlag = 0; // スケジュール検出用フラグ
              for ($startTime = 0; $startTime < 24; $startTime++) :
                $scheduleList = selectWeekSchedule($displayEmp['emp_id'], $week, $startTime);
                if (!empty($scheduleList)) :
              ?>
                  <tr>
                    <?php
                    $scheduleFlag = 1;
                    for ($day = 0; $day < 7; $day++) :
                    ?>
                      <td bgcolor="<?= $day % 2 != 1 ? "#FFFFFF" : "#EEEEEE" ?>">
                        <?php if (!empty($scheduleList[$day])) : ?>
                          <ul>
                            <?php foreach ($scheduleList[$day] as $schedule) : ?>
                              <li class="schedule-item" data-id="<?= escape($schedule['id']) ?>">
                                <?= escape($schedule['start_time']) ?>
                                ~
                                <?= escape($schedule['end_time']) ?>
                                <br>
                                <?= escape($schedule['title']) ?>
                              </li>

                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </td>

                    <?php endfor; ?>
                  </tr>
              <?php
                endif;
              endfor;
              ?>

              <!-- 一週間の予定が何もないとき -->
              <?php if ($scheduleFlag === 0) : ?>
                <tr>
                  <?php for ($day = 0; $day < 7; $day++) : ?>
                    <td bgcolor="<?= $day % 2 != 1 ? "#FFFFFF" : "#EEEEEE" ?>"></td>
                  <?php endfor; ?>
                </tr>
              <?php endif; ?>
            </table>
          </div>
        <?php endforeach; ?>
    </div>
  <?php endif; ?>

  </div>
  <br>

  <?php
  // スケジュール詳細、登録・編集フォーム
  require_once dirname(__FILE__) . '/form.php';
  // 空き時間表示テーブル
  require_once dirname(__FILE__) . '/freetime.php';
  // 使い方
  require_once dirname(__FILE__) . '/guide.php';
  // フッター
  require dirname(__FILE__) . '/footer.php';
  ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <script src="js/script.js"></script>
</body>


</html>