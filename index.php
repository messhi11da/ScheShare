<?php
require_once(dirname(__FILE__) . '/function.php');


$empId = '154';

$today = getToday();
if (isset($_GET['date'])) {
  $date = escape($_GET['date']);
  list($y, $m, $d) = explode('-', $date);
} else {
  $date = $today;
  list($y, $m, $d) = explode('-', $date);
}

$deptList = selectDept();
//var_dump($deptList);
$dateArray = createCalendar($m, $y);
// var_dump($date);
// echo "<br>";
// var_dump($dateArray);
$weekday = getWeekday($date, $dateArray);

$empList = selectAllEmp($empId);

//var_dump($empList);

$selectedDeptId = '0';
//var_dump($selectedDeptId);
if (isset($_POST['submit_search'])) {
  $keyword = escape($_POST['keyword']);
  $selectedDeptId = $_POST['dept_id'];
  var_dump($selectedDeptId);
  //  $keywordCheck = preg_replace('/( |　)/', '', $keyword);
  //  var_dump($keyword);
  var_dump($_POST['dept_id']);
  $searchedEmpList = searchEmp($keyword, $selectedDeptId);
}


//var_dump($_POST);
$displayEmpList[] = selectEmp($empId);

if (isset($_POST['submit_checked'])) {
  $keyword = $_POST['keyword'];
  $selectedDeptId = $_POST['dept_id'];
  $searchedEmpList = searchEmp($keyword, $selectedDeptId);
  $checkedEmpIdList = $_POST['checked_emp'];
  foreach ($checkedEmpIdList as $checkedEmpId) {
    $displayEmpList[] = selectEmp($checkedEmpId);
  }
  var_dump($displayEmpList);
  //exit;
}




// 新規スケジュール登録処理
if (isset($_POST['submit_add'])) {
  var_dump($_POST);

  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);
  
  insertSchedule($empId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $_POST['attendees']);
}

// スケジュール編集処理
if(isset($_POST['submit_edit'])){
  var_dump($_POST);
  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);
  updateSchedule($empId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $_POST['attendees'], $_POST['schedule_id']);
}



?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ScheShare</title>
  <link href="./css/style.css" rel="stylesheet">
</head>

<h1><a href="">ScheShare</a></h1>

<form action="" method="post">
  <select name="dept_id">
    <option value="0">全体</option>
    <?php foreach ($deptList as $dept) : ?>
      <option value="<?= $dept['dept_id'] ?>" <?= $dept['dept_id'] ===  $selectedDeptId ? "selected" : "" ?>><?= $dept['dept_name'] ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="keyword" value="<?= isset($keyword) ? $keyword : '' ?>" placeholder="社員を探す">
  <button type="submit" name="submit_search" value="1">検索</button>
</form>

<?php if (isset($searchedEmpList)) : ?>
  <p>検索結果: <?= count($searchedEmpList); ?>件</p>
  <?php if (count($searchedEmpList) > 0) : ?>
    <form action="" method="post">
      <input name="dept_id" value="<?= $selectedDeptId ?>" type="hidden">
      <input name="keyword" value="<?= $keyword ?>" type="hidden">
      <table>
        <tr>
          <th>社員番号</th>
          <th>部署</th>
          <th>名前</th>
          <th>
            <button id="display-btn" type="submit" name="submit_checked" value="1">スケジュール表示</button>
          </th>
        </tr>
        <?php foreach ($searchedEmpList as $key => $searchedEmp) : ?>
          <tr>
            <td><?= $searchedEmp['emp_id'] ?></td>
            <td><?= $searchedEmp['dept_name'] ?></td>
            <td><?= $searchedEmp['emp_name'] ?></td>
            <td>
              <?php if ($searchedEmp['emp_id'] != $searchedEmpId) : ?>
                <input type='checkbox' name="checked_emp[]" value="<?= $searchedEmp['emp_id'] ?>" <?= in_array($searchedEmp['emp_id'], $checkedEmpIdList) ? 'checked' : '' ?>>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </form>
  <?php endif; ?>
<?php endif; ?>



<a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . ($m - 1) . "-1" : ($y - 1) . "-12-1" ?>">◁</a>
<span><?= $y ?>年<?= $m ?>月</span>
<a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . ($m + 1) . "-1" : ($y + 1) . "-1-1" ?>">▷</a>
<!-- 月毎のカレンダー -->
<table id="calendar-table" border="1">
  <tr>
    <!-- 曜日の表示 -->
    <?php for ($i = 0; $i < 7; $i++) : ?>
      <th><?= getWeekName($i); ?></th>
    <?php endfor; ?>
  </tr>
  <?php foreach ($dateArray as $key => $date2) : ?>
    <?php list($y, $m, $d) = explode('-', $date2); ?>
    <?php if (($key) % 7 == 0) : ?>
      <tr>
      <?php endif; ?>
      <td>
        <a href="index.php?date=<?= $date2 ?>"><?= (int)$d ?></a>
      </td>
      <?php if (($key) % 7 == 6) : ?>
      </tr>
    <?php endif; ?>
  <?php endforeach; ?>

</table>

<br>

<button id="add-btn" type="button">新規スケジュール追加</button>




<!-- 週ごとのスケジュール -->
<div id="schedule-wrapper">
  <input id="close-check" type="hidden" value="valid">
  <table id="schedule-table" border="1">
    <tr>
      <th></th>
      <?php foreach ($weekday as $key => $date3) : ?>
        <?php list($y, $m, $d) = explode('-', $date3); ?>
        <th><?= "$m/" . (int)$d . "(" . getWeekName($key) . ")" ?></th>
      <?php endforeach; ?>
    </tr>

    <!-- 一週間のスケジュール（一人分） -->


    <?php
    foreach ($displayEmpList as $displayEmp) :
      $endTime = 0;
      for ($startTime = 0; $startTime < 24; $startTime++) :
        var_dump($displayEmp['emp_name']);
        $weekSchedule = selectSchedule($displayEmp['emp_id'], $weekday, $startTime);
        // var_dump($weekSchedule);
        if (!empty($weekSchedule)) : // 一週間の中でスケジュールが入っている時刻リストを取得  
          $freeTime = $startTime - $endTime; // 前回スケジュールがあった時刻(hour)との差
          var_dump(($freeTime));
    ?>
          <tr>
            <?php if ($endTime === 0) : ?>
              <td rowspan="24">
                <?= $displayEmp['emp_id'] ?>
                <br>
                <?= $displayEmp['emp_name'] ?>
                <br>
                <?= $displayEmp['dept_name'] ?>
              </td>
            <?php endif; ?>

            <?php if ($freeTime > 0) : ?>

              <?php for ($col = 0; $col < 7; $col++) : ?>
                <td rowspan="<?= $freeTime ?>"><?= $freeTime ?></td>
              <?php endfor; ?>
          </tr>
          <?php for ($row = 0; $row < ($freeTime - 1); $row++) : ?>
            <tr></tr>
          <?php endfor; ?>
          <tr>
          <?php endif; ?>

          <?php for ($day = 0; $day < 7; $day++) : ?>
            <td>
              <ul>
                <?php foreach ($weekSchedule[$day] as $key => $dateSchedule) : ?>
                  <li class="schedule-item">
                    <?= substr($dateSchedule['start_time'], 0, 5); ?>~
                    <?= substr($dateSchedule['end_time'], 0, 5); ?>
                    <?= $dateSchedule['title'] ?>
                    <?= $startTime ?>
                  </li>

                  <!-- スケジュール詳細画面 -->
                  <div class="schedule-desc" style="display: none;">
                    <div class="schedule-header">
                      <h4>スケジュール詳細</h4>
                      <button class="edit-btn" type="button">編集</button>
                      <button class="close-btn" type="button">×</button>
                    </div>
                    <p class="schedule-datetime">
                      <?php list($y, $m, $d) = explode('-', $dateSchedule['date']); ?>
                      <?= $y ?>/<?= $m ?>/<?= $d ?>/(<?= getWeekName($col); ?>)
                      <?= substr($dateSchedule['start_time'], 0, 5); ?>~
                      <?= substr($dateSchedule['end_time'], 0, 5); ?>
                    </p>
                    <p><?= $dateSchedule['title'] ?></p>
                    <p>
                      説明：<br>
                      <?= $dateSchedule['memo'] ?>
                    </p>
                    <p>
                      参加者：<?= fetchAttendees($dateSchedule['attendees_id']); ?>
                    </p>
                  </div>

                  <!-- スケジュール編集画面 -->
                  <form class="schedule-form edit-form" action="" method="post" style="display: none;">
                    <div class="schedule-header">
                      <h4>スケジュール編集</h4>
                      <button class="close-btn" type="button">×</button>
                    </div>

                    <input class="input-title" id="title" type="text" name="title" value="<?= $dateSchedule['title'] ?>" placeholder="タイトルを追加">
                    <br>
                    日時：
                    <input class="input-date" type="date" name="date" value="<?= $dateSchedule['date'] ?>">
                    <br>
                    時刻：
                    <input class="input-starttime" id="begin" type="time" name="start" value="<?= $dateSchedule['start_time'] ?>">
                    <span>～</span>
                    <input class="input-endtime" id="end" type="time" name="end" value="<?= $dateSchedule['end_time'] ?>">
                    <br>
                    参加者：
                    <?php foreach ($displayEmpList as $displayEmp2) : ?>
                      <?= $displayEmp2['emp_name'] ?>
                      <input type=<?= $displayEmp2['emp_id'] === $empId ? 'hidden' : 'checkbox' ?> name="attendees[]" value="<?= $displayEmp2['emp_id'] ?>" <?= strpos($dateSchedule['attendees_id'], 'X' . $displayEmp2['emp_id']) ? 'checked' : '' ?>>
                    <?php endforeach; ?>
                    <br>
                    <textarea name="memo" placeholder="説明"></textarea>
                    <br>
                    <input type="hidden" name="schedule_id" value="<?= $dateSchedule['id'] ?>">
                    <button type="submit" name="submit_edit" value="1">更新</button>
                  </form>







                <?php endforeach; ?>
              </ul>
            </td>
          <?php endfor; ?>
          </tr>
          <?php $endTime = $startTime + 1; ?>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($endTime < 24) : ?>
        <tr>
          <?php if ($endTime === 0) : ?>

            <td rowspan="24">
              <?= $displayEmp['emp_id'] ?>
              <br>
              <?= $displayEmp['emp_name'] ?>
              <br>
              <?= $displayEmp['dept_name'] ?>
            </td>
          <?php endif; ?>
          <?php for ($col = 0; $col < 7; $col++) : ?>
            <td rowspan="<?= 24 - $endTime ?>"><?= 24 - $endTime ?></td>
          <?php endfor; ?>
        </tr>
        <?php for ($row = 0; $row < (23 - $endTime); $row++) : ?>
          <tr></tr>

        <?php endfor; ?>
      <?php endif; ?>
    <?php endforeach; ?>

  </table>

</div>


<!-- スケジュールの新規追加・編集フォーム  -->

<form id="add-form" class="schedule-form" action="" method="post" style="display: none;">
  <h4>新規スケジュール登録</h4>

  <input class="input-title" id="title" type="text" name="title" placeholder="タイトルを追加">
  <br>
  日時：
  <input class="input-date" type="date" name="date" value="<?= $date ?>">
  <br>
  時刻：
  <input class="input-starttime" id="begin" type="time" name="start" value="09:00">
  <span>～</span>
  <input class="input-endtime" id="end" type="time" name="end">
  <br>
  参加者：
  <?php foreach ($displayEmpList as $displayEmp) : ?>
    <?= $displayEmp['emp_name'] ?>
    <input type=<?= $displayEmp['emp_id'] === $empId ? 'hidden' : 'checkbox' ?> name="attendees[]" value="<?= $displayEmp['emp_id'] ?>">
  <?php endforeach; ?>
  <br>
  <textarea name="memo" placeholder="説明"></textarea>
  <br>

  <!--
    <div class="emp-list">
      <input type="text" placeholder="名前">
      <input type='hidden' name="attendees[]" value="<?= $empId ?>">
      <ul>
        <?php foreach ($empList as $emp) : ?>
          <li>
            <?= $emp['emp_id'] ?>
            <?= $emp['dept_name'] ?>
            <?= $emp['emp_name'] ?>
            <input class="checkbox-emp" data-name="<?= $emp['emp_name'] ?>" type='checkbox' name="attendees[]" value="<?= $emp['emp_id'] ?>">
          </li>
        <?php endforeach; ?>
      </ul>
      <button id="add-attendees-btn" type="button">追加</button>
    </div>
  </div>
        -->


  <button type="submit" name="submit_add" value="1">登録</button>
</form>



<form class="schedule-info" action="" method="post" style="display: none;">
  <div class="info-header">
    <h4>スケジュール詳細(編集中)</h4>
    <button class="delete-btn" type="button">削除</button>
  </div>
  <p class="schedule-datetime">
    <?php list($y, $m, $d) = explode('-', $dateSchedule['date']); ?>
    <?= $y ?>/<?= $m ?>/<?= $d ?>/(<?= getWeekName($col); ?>)
    <?= substr($dateSchedule['start_time'], 0, 5); ?>~
    <?= substr($dateSchedule['end_time'], 0, 5); ?>
  </p>
  <p><?= $dateSchedule['title'] ?></p>
  <p>
    説明：<br>
    <?= $dateSchedule['memo'] ?>
  </p>
  <p>参加者：</p>
</form>







<script src="js/index.js"></script>

</body>

</html>