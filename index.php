<?php
session_start();
require_once(dirname(__FILE__) . '/function/functions.php');
require_once(dirname(__FILE__) . '/login_check.php');

/* ログインユーザーをスケジュール表示用変数に格納 */
if (!isset($_SESSION['display_emp']) || $_SESSION['display_emp'] === "" || !in_array($user, $_SESSION['display_emp'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
}

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

/* 部署リストを取得 */
$deptList = selectDept();

/* 表示する社員情報を取得　*/
if (isset($_POST['submit_display'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $_SESSION['display_emp'][] = selectEmp($checkedEmpId);
  }
}

/* 非同期通信用 */
foreach ($_SESSION['display_emp'] as $displayEmp) {
  $displayEmpList[] = $displayEmp['emp_id'];
}
require_once(dirname(__FILE__) . '/form.php');
require_once(dirname(__FILE__) . '/freetime.php');
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ScheShare</title>
  <link href="./css/style.css" rel="stylesheet">
</head>

<body>
  <header>
    <h1><a href="index.php">ScheShare</a></h1>
    <div>
      <p><?= escape($userId) ?>さんログイン中</p>
      <p><a href="login.php">ログアウト</a></p>
    </div>
  </header>

  <div id="top-wrapper">
    <!-- 社員検索画面エリア -->
    <form class="search-area" action="" method="post">
      <select class="select-dept">
        <option class="dept-option" value="0">全部署</option>
        <?php foreach ($deptList as $dept) : ?>
          <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= escape($dept['dept_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <input class="input-keyword" type="text" placeholder="(例)社員番号,名前,部署名">
      <button class="search-btn" type="submit" data-checked='<?= json_encode($displayEmpList); ?>'>検索</button>

      <div class="emp-table" style="display: none;">
      </div>

      <button class="display-btn" type="submit" name="submit_display" value="1">スケジュールを表示</button>
    </form>

    <!-- カレンダー表示エリア -->
    <div id="calendar-area">
      <h2 style="margin:0;">
        <a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . ($m - 1) . "-1" : ($y - 1) . "-12-1" ?>">◁</a>
        <span><?= $y ?>年<?= (int)$m ?>月</span>
        <a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . ($m + 1) . "-1" : ($y + 1) . "-1-1" ?>">▷</a>
      </h2>

      <!-- カレンダー -->
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
                <?= ($m2 < $m || $m2 > $m) ? (int)$m . "/" . (int)$d2 : (int)$d2 ?>
              </a>
            </td>
            <?php if (($day) % 7 == 6) : ?>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </table>
    </div>
  </div>


  <!-- スケジュール表示エリア -->
  <div id="schedule-wrapper">
    <div class="schedule-header">
      <div>
        <?php if (!empty($_SESSION['display_emp'])) : ?>
          <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
            <?= escape($displayEmp['emp_name']) . "(" . escape($displayEmp['emp_id']) . ")さん " ?>
          <?php endforeach; ?>
          のスケジュールを表示中
        <?php endif; ?>
      </div>

      <div style="margin: 1rem 0">
        <button id="add-btn" type="button">新規スケジュール追加</button>
        <button id="free-time-btn" type="button" data-id='<?= json_encode($displayEmpList); ?>' data-week='<?= json_encode($week) ?>'>空き時間を表示</button>
      </div>
    </div>

    <?php
    if (!empty($_SESSION['display_emp'])) :
      foreach ($_SESSION['display_emp'] as $key => $displayEmp) :
    ?>
        <div class="schedule-container">

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

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="js/script.js"></script>

</html>