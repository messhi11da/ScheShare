<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();
$_SESSION['display_emp'] = array();

$userId = '154';

if (isset($_GET['date'])) {
  $date = escape($_GET['date']);
  list($y, $m, $d) = explode('-', $date);
} else {
  $date = getToday();
  list($y, $m, $d) = explode('-', $date);
}

$deptList = selectDept(); // 部署リストを取得
$dateArray = createCalendar($m, $y);  // 指定した年月のカレンダー(配列)を生成
$week = getWeek($date, $dateArray); // 指定した日付を含む一週間を生成




//$empList = selectAllEmp();

//var_dump($empList);

$selectedDeptId = '0';

$empList = selectAllEmp();
$jsonEmpList = json_encode($empList);

//var_dump($empList);

if (isset($_POST['submit_search'])) {
  $keyword = escape($_POST['keyword']);
  $selectedDeptId = $_POST['dept_id'];
  var_dump($selectedDeptId);

  var_dump($_POST['dept_id']);
  $searchedEmpList = searchEmp($keyword, $selectedDeptId);
}


//var_dump($_POST);
$user = selectEmp($userId);
$displayEmpList[] = $user;

// 表示する社員の取得

if (isset($_POST['submit_display'])) {
  // var_dump($_POST);
  /*
//  $keyword = $_POST['keyword'];
  $selectedDeptId = $_POST['dept_id'];
 // $searchedEmpList = searchEmp($keyword, $selectedDeptId);
  $searchedEmpList = $_POST['searched_emp'];
*/
  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $checkedEmp = selectEmp($checkedEmpId);
    $displayEmpList[] = $checkedEmp;
    $_SESSION['display_emp'][] = $checkedEmp['emp_name'];
  }
  var_dump($_SESSION);

  var_dump($displayEmpList);
  //exit;
}



// 新規スケジュール登録処理
if (isset($_POST['submit_add'])) {
  var_dump($_POST);

  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);

  insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $_POST['checked_emp']);
}

// スケジュール編集処理
if (isset($_POST['submit_update'])) {
  var_dump($_POST);
  // exit;
  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);
  updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $_POST['attendees'], $_POST['schedule_id']);
}

// スケジュール削除
if (isset($_POST['submit_delete'])) {
  var_dump($_POST);
  //exit;
  deleteSchedule($_POST['schedule_id']);
}



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
    <h1><a href="http://localhost/ScheShare/index.php">ScheShare</a></h1>
    <div>
      <p><?= $userId ?>さんログイン中</p>
      <p><a href="">ログアウト</a></p>
    </div>
  </header>
  <?php if (!empty($_SESSION['display_emp'])) : ?>
    <p><?= implode('さん, ', $_SESSION['display_emp']) . "さんのスケジュールを表示中"  ?></p>
  <?php endif; ?>


  <div id="top-wrapper">


      <form id="search-area" action="" method="post">
        <select class="dept-select">
          <option class="dept-option" value="0">全部署</option>
          <?php foreach ($deptList as $dept) : ?>
            <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
          <?php endforeach; ?>
        </select>
        <input class="input-keyword" type="text">


        <div class="emp-list" style="display: none;">
          <table>
            <tr>
              <th>社員番号</th>
              <th>部署</th>
              <th>名前</th>
            </tr>
            <?php foreach ($empList as $emp) : ?>
              <tr class="emp-item id-<?= $emp['emp_id'] ?>" style="display:none;">
                <td><?= $emp['emp_id'] ?></td>
                <td><?= $emp['dept_name'] ?></td>
                <td><?= $emp['emp_name'] ?></td>
                <td>
                  <input type="checkbox" name="checked_emp[]" value="<?= $emp['emp_id'] ?>" <?= in_array($emp['emp_name'], $_SESSION['display_emp']) ? "checked" : "" ?>>
                </td>
              </tr>

            <?php endforeach; ?>
          </table>
          
        </div>
        <button type="submit" name="submit_display" value="1">スケジュールを表示</button>
      </form>







    <div id="calendar-area">

      <a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . ($m - 1) . "-1" : ($y - 1) . "-12-1" ?>">◁</a>
      <span><?= $y ?>年<?= $m ?>月</span>
      <a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . ($m + 1) . "-1" : ($y + 1) . "-1-1" ?>">▷</a>
      <!-- 月毎のカレンダー -->
      <table id="calendar-table" border="1" width="850" style="table-layout:fixed; min-height:200px;">
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

    </div>


  </div>


  <br>

  <button id="add-btn" type="button">新規スケジュール追加</button>
  <button type="button">クイック招集モード</button>






  <!-- 週ごとのスケジュール -->
  <input id="display-elem" type="hidden" value="enable">
  <div id="schedule-wrapper">
    <?php
    $json = [];
    $jsonIndex = 0;
    foreach ($displayEmpList as $key2 => $displayEmp) :
      //   $lastEndTime = 0; 
    ?>
      <div style="display:flex; align-items:center">
        <div style="background-color:lightblue;">ここに社員の情報</div>
        <table id="schedule-table" border="1" width="850" style="table-layout:fixed; min-height:200px;">

          <?php if ($key2 === 0) : ?>
            <tr style="height: 30px;">

              <?php foreach ($week as $key => $date3) : ?>
                <?php list($y, $m, $d) = explode('-', $date3); ?>
                <th ><?= "$m/" . (int)$d . "(" . getWeekName($key) . ")" ?></th>
              <?php endforeach; ?>

            </tr>
          <?php endif; ?>

          <!-- 一週間のスケジュール（一人分） -->


          <?php


          $flag = 0;

          for ($startTime = 0; $startTime < 24; $startTime++) :

            $scheduleList = selectSchedule($displayEmp['emp_id'], $week, $startTime);



            if (!empty($scheduleList)) :
              // var_dump($scheduleList);
              // exit;
          ?>
              <tr>
                <?php
                $flag = 1;
                foreach ($week as $key => $date) :
                ?>
                  <td>
                    <?php if (!empty($scheduleList[$key])) : ?>

                      <ul style="max-height:40px;">

                        <?php foreach ($scheduleList[$key] as $schedule) : ?>
                          <?php $json[] = $schedule;
                          ?>

                          <li class="schedule-item">
                            <?= $schedule['start_time'] ?>~
                            <?= $schedule['end_time'] ?>
                            <?= $schedule['title'] ?>
                          </li>



                          <!-- スケジュール詳細画面 -->
                          <div class="schedule-desc" style="display: none;">
                            <div class="schedule-header">
                              <h4>スケジュール詳細</h4>
                              <button class="edit-btn" type="button">編集</button>
                              <button class="close-btn" type="button" value="invalid">×</button>
                            </div>
                            <p class="schedule-datetime">
                              <?php list($y, $m, $d) = explode('-', $schedule['date']); ?>
                              <?= $y ?>/<?= $m ?>/<?= $d ?>/(<?= getWeekName($key); ?>)
                              <?= $schedule['start_time'] ?>~
                              <?= $schedule['end_time'] ?>
                            </p>
                            <p><?= $schedule['title'] ?></p>
                            <p>
                              説明：<br>
                              <?= $schedule['memo'] ?>
                            </p>
                            <p>
                              参加者：
                              <?= $user['emp_name'] ?>
                              <?= fetchAttendees($schedule['attendees_id']); ?>
                            </p>
                          </div>


                          <?php $jsonIndex++; ?>
                        <?php endforeach; ?>
                        <?php $schedule = array(); ?>
                      </ul>
                    <?php endif; ?>


                  </td>

                <?php endforeach; ?>
              </tr>
          <?php endif;

          endfor; ?>
          <?php if ($flag === 0) : ?>
            <tr>
              <?php for ($j = 0; $j < 7; $j++) : ?>
                <td></td>
              <?php endfor; ?>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    <?php endforeach; ?>
    <?php //var_dump($json); 
    $scheduleDataList = json_encode($json);
    ?>


  </div>


  <!-- 空き時間表示用テーブル -->

  <?php
  $interval = 0.25;
  list($freeTimeList, $max) = calcFreeTime($displayEmpList, $week, $interval);
  ?>

  <table border="1">
    <tr>

      <?php foreach ($week as $key => $date3) : ?>
        <?php list($y, $m, $d) = explode('-', $date3); ?>
        <th><?= "$m/" . (int)$d . "(" . getWeekName($key) . ")" ?></th>
      <?php endforeach; ?>

    </tr>

    <?php

    $j = 0;
    for ($k = 0; $k < $max; $k++) :

      $flag = 0;
      for ($i = 0; $i < 7; $i++) :
        if (isset($freeTimeList[$i][$j])) :
          $flag = 1;
        endif;
      endfor;

      if ($flag === 1) :
    ?>
        <tr>
          <?php
          foreach ($week as $key => $date) :
          ?>
            <td>
              <?php if (isset($freeTimeList[$key][$j])) : ?>

                <?= $freeTimeList[$key][$j] ?>

              <?php endif; ?>
            </td>

          <?php endforeach; ?>
        </tr>


    <?php endif;
      $j++;
    endfor; ?>

  </table>



  <!-- スケジュールの新規追加・編集フォーム  -->

  <form id="add-form" class="schedule-form" action="" method="post" style="display: none;">
    <div class="form-wrapper" style="display:flex;">
      <div class="main-form">
        <h4 class="form-title">新規スケジュール登録</h4>
        <button class="close-btn" type="button">×</button>
        <input class="input-title" id="title" type="text" name="title" placeholder="タイトルを追加">
        <br>
        日時：
        <input class="input-date" type="date" name="date" value="<?= $date ?>">
        <br>
        時刻：
        <input class="input-starttime" id="begin" type="time" name="start">
        <span>～</span>
        <input class="input-endtime" id="end" type="time" name="end">
        <br>
        参加者：
        <?= $user['emp_name'] ?>
        <?php if (isset($schedule['attendees_id'])) : ?>
          <?= fetchAttendees($schedule['attendees_id']); ?>
        <?php endif; ?>
        <br>
        <textarea class="input-memo" name="memo" placeholder="説明"></textarea>
        <br>

        <input class="input-schedule-id" type="hidden" name="schedule_id" value="">
        <button class="add-btn" type="submit" name="submit_add" value="1">登録</button>
      </div>


      <div class="sub-form">
        <p>参加者を追加</p>

        <div>

          <select class="dept-select">
            <option class="dept-option" value="0">全部署</option>
            <?php foreach ($deptList as $dept) : ?>
              <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
            <?php endforeach; ?>
          </select>
          <input class="input-keyword" type="text">


          <div style="display: none;">
            <ul>
              <?php foreach ($empList as $emp) : ?>
                <li class="emp-item id-<?= $emp['emp_id'] ?>" style="display:none;">
                  <label>
                    <?= $emp['emp_id'] ?>/
                    <?= $emp['dept_name'] ?>/
                    <?= $emp['emp_name'] ?>
                    <input type="checkbox" name="checked_emp[]" value="<?= $emp['emp_id'] ?>">
                  </label>
                </li>
              <?php endforeach; ?>
            </ul>
            <button class="attendees-btn" type="button">招集</button>
          </div>

        </div>

      </div>
    </div>
  </form>

</body>





<script>
  // var scheduleForm = document.getElementById('schedule-form');
  var inputStartTime = document.querySelector('.input-starttime');
  var inputEndTime = document.querySelector('.input-endtime');


  var scheduleItemList = document.querySelectorAll('.schedule-item');
  var scheduleDescList = document.querySelectorAll('.schedule-desc');

  var addBtn = document.getElementById('add-btn');



  var addAttendeesBtn = document.getElementById('add-attendees-btn');
  var checkEmpList = document.querySelectorAll('.checkbox-emp');
  var attendeesArea = document.getElementById('attendees-area');

  console.log(attendeesArea);


  var addForm = document.getElementById('add-form');
  var addCloseBtn = addForm.querySelector('.close-btn');


  // 絞り込み検索

  var empList = <?= $jsonEmpList ?>;
  var inputKeywordList = document.querySelectorAll('.input-keyword');
  for (var inputKeyword of inputKeywordList) {

    //  console.log(deptSelect);
    // console.log(empTable);
    inputKeyword.addEventListener('input', function() {
      var empTable = this.nextElementSibling;
      var deptSelect = this.previousElementSibling;
      console.log(this.value);
      empTable.style.display = "none";
      if (this.value != "") {
        for (var emp of empList) {
          var empItem = empTable.querySelector(".id-" + emp['emp_id']);
          empItem.style.display = "none";
          if (emp['emp_id'].indexOf(this.value) != -1 ||
            emp['emp_name'].indexOf(this.value) != -1 ||
            emp['dept_name'].indexOf(this.value) != -1) {
            empTable.style.display = "block";
            empItem.style.display = "table-row";
            console.log(empTable);
            console.log('yes!!');
          }
          if (deptSelect.value > 0 && deptSelect.value != emp['dept_id']) {
            console.log(emp['dept_id']);
            empItem.style.display = "none";
          }

        }

      }
    });
    // エンターキーでのフォーム送信を無効
    inputKeyword.addEventListener('keydown', function(event) {
      console.log(event.keyCode);
      if (event.keyCode === 13) {
        console.log('yy');
        event.preventDefault();
      }
    });
  }

  var deptSelectList = document.querySelectorAll('.dept-select');
  for (var deptSelect of deptSelectList) {
    deptSelect.addEventListener('change', function() {
      for (var emp of empList) {
        var empItem = document.querySelector(".id-" + emp['emp_id']);
        if (this.value > 0 && this.value != emp['dept_id']) {
          console.log(emp['dept_id']);
          empItem.style.display = "none";
        }
      }
    })
  }


  var displayElem = document.getElementById('display-elem');

  // 新規追加ボタンを押すとフォームを表示
  addBtn.addEventListener('click', function(event) {
    console.log(this);
    displayElem.value = "disable";
    var x = event.pageX;
    var y = event.pageY;
    addForm.style.position = 'absolute';
    addForm.style.top = (y - 100) + 'px';
    addForm.style.left = (x + 100) + 'px';
    console.log(addForm);
    addForm.style.display = 'block';

  });

  addCloseBtn.addEventListener('click', function() {
    displayElem.value = 'enable';
    addForm.style.display = 'none';
  });



  var scheduleDataList = <?= $scheduleDataList ?>;


  // スケジュールを押すと詳細表示
  scheduleItemList.forEach(function(scheduleItem, index) {
    console.log(scheduleItem);
    console.log(index);


    var scheduleDesc = scheduleItem.nextElementSibling;
    var closeBtn = scheduleDesc.querySelector('.close-btn');
    var editBtn = scheduleDesc.querySelector('.edit-btn');
    var x, y;
    scheduleItem.addEventListener('click', function(event) {
      x = event.pageX;
      y = event.pageY;

      // 詳細画面を開く
      if (displayElem.value === 'enable') {
        displayElem.value = 'disable';
        scheduleDesc.style.position = 'absolute';
        scheduleDesc.style.left = x + 'px';
        scheduleDesc.style.top = y + 'px';
        scheduleDesc.style.display = 'block';
      }

    });

    // 詳細画面を閉じる
    closeBtn.addEventListener('click', function() {
      displayElem.value = "enable";
      scheduleDesc.style.display = "none";
    });

    editBtn.addEventListener('click', function() {


      scheduleDesc.style.display = "none";


      // 新規登録画面から編集画面のクローンを作成 & 表示
      var scheduleData = scheduleDataList[index];
      var editForm = addForm.cloneNode(true);
      var closeBtn = editForm.querySelector('.close-btn');

      var formTitle = editForm.querySelector('.form-title');
      var updateBtn = editForm.querySelector('.add-btn');
      var inputTitle = editForm.querySelector('.input-title');
      var inputDate = editForm.querySelector('.input-date');
      var inputStartTime = editForm.querySelector('.input-starttime');
      var inputEndTime = editForm.querySelector('.input-endtime');
      var inputMemo = editForm.querySelector('.input-memo');
      var inputScheduleId = editForm.querySelector('.input-schedule-id');



      formTitle.textContent = "スケジュール編集";
      inputTitle.setAttribute('value', scheduleData['title']);
      inputDate.setAttribute('value', scheduleData['date']);
      inputStartTime.setAttribute('value', scheduleData['start_time']);
      inputEndTime.setAttribute('value', scheduleData['end_time']);
      inputMemo.setAttribute('value', scheduleData['memo']);
      inputScheduleId.setAttribute('value', scheduleData['id']);
      updateBtn.textContent = "更新";
      updateBtn.setAttribute('name', 'submit_update');

      editForm.style.position = 'absolute';
      editForm.style.left = x + 'px';
      editForm.style.top = y + 'px';
      editForm.style.display = 'block';

      document.body.appendChild(editForm);

      // 編集画面を閉じる
      closeBtn.addEventListener('click', function(event) {
        displayElem.value = "enable";
        editForm.remove();
        console.log('editform消しますよ');
        console.log(addForm);
        console.log('editform消しました！！');
        event.stopPropagation();
      });


      console.log('y');
      console.log(editForm);
      console.log('n');
    });

  });






  // スケジュール追加前の入力エラーチェック
  addForm.addEventListener('submit', function(e) {
    var inputDate = this.querySelector('.input-date');
    var inputTitle = this.querySelector('.input-title');
    var date = new Date(inputDate.value);
    var today = new Date(getToday());

    // スケジュール登録エラーチェック
    if (date.getTime() < today.getTime()) {
      window.alert("本日以降の日付を選択してください。");
      e.preventDefault();
    }
    if (inputTitle.value.length === 0) {
      window.alert("タイトルを入力してください。");
      e.preventDefault();
    }
    if (inputStartTime.value >= inputEndTime.value) {
      window.alert("終了時刻を開始時刻より遅く設定してください。");
      e.preventDefault();
    }
  });


  inputStartTime.addEventListener('change', function() {
    inputEndTime.value = inputStartTime.value;
  })


  function getToday() {
    var date = new Date();
    var y = date.getFullYear();
    var m = ("00" + (date.getMonth() + 1)).slice(-2);
    var d = ("00" + date.getDate()).slice(-2);
    return (y + "-" + m + "-" + d);
  }
</script>
<!--
<script src="js/index.js"></script>
        -->
</body>

</html>