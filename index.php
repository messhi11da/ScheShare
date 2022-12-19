<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();

$_SESSION['display_emp'] = array();

$userId = $_SESSION['user_id'];

var_dump($_SESSION['user_id']);
//exit;

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ""){
  header('Location: http://localhost/ScheShare/login.php');
  exit();
}

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
$empDataList = json_encode($empList);

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
$_SESSION['display_emp'][] = $user;

// 表示する社員の取得

if (isset($_POST['submit_display'])) {
  // var_dump($_POST);

  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $_SESSION['display_emp'][] = selectEmp($checkedEmpId);;
  }
  var_dump($_SESSION);

  //exit;
}



// 新規スケジュール登録処理
if (isset($_POST['submit_add'])) {
  var_dump($_POST);
  //exit;
  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);
  $checkedEmp = $_POST['checked_emp'];
  var_dump($checkedEmp);
  //exit;
  insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $checkedEmp);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
}

// スケジュール編集処理
if (isset($_POST['submit_update'])) {
  var_dump($_POST);
  //exit;
  $title = escape($_POST['title']);
  $memo = escape($_POST['memo']);
  $checkedEmp = $_POST['checked_emp'];
  updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $title, $memo, $checkedEmp, $_POST['schedule_id']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
}

// スケジュール削除
if (isset($_POST['submit_delete'])) {
  var_dump($_POST);
  // exit;
  deleteSchedule($_POST['schedule_id']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
}

var_dump($_POST);
//exit;



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
      <p><a href="login.php">ログアウト</a></p>
    </div>
  </header>

  <?php if (!empty($_SESSION['display_emp'])) : ?>
    <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
      <?= $displayEmp['emp_name'] . "(" . $displayEmp['emp_id'] . ")さん " ?>
    <?php endforeach; ?>
    のスケジュールを表示中
  <?php endif; ?>


  <div id="top-wrapper">


    <form id="search-area" action="" method="post">

      <select class="select-dept">
        <option class="dept-option" value="0">全部署</option>
        <?php foreach ($deptList as $dept) : ?>
          <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <input class="input-keyword" width="" type="text" placeholder="（例) 社員番号,名前,部署名" style="width: 100%;">



      <div class="emp-list" style="display: none;">
        <ul>
          <?php foreach ($empList as $emp) : ?>
            <li class="emp-item id-<?= $emp['emp_id'] ?>" style="display:none;">
              <label>
                <?= $emp['emp_id'] ?>/
                <?= $emp['dept_name'] ?>/
                <?= $emp['emp_name'] ?>

                <input class="input-check" type="checkbox" name="checked_emp[]" value="<?= $emp['emp_id'] ?>" <?= in_array($emp['emp_name'], $_SESSION['display_emp']) ? "checked" : "" ?>>


              </label>
            </li>

          <?php endforeach; ?>
        </ul>

        <button class="display-btn" type="submit" name="submit_display" value="1">スケジュールを表示</button>
      </div>
    </form>







    <div id="calendar-area">

      <h2 class="calendar-header">
        <a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . ($m - 1) . "-1" : ($y - 1) . "-12-1" ?>">◁</a>
        <span><?= $y ?>年<?= $m ?>月</span>
        <a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . ($m + 1) . "-1" : ($y + 1) . "-1-1" ?>">▷</a>
      </h2>

      <!-- 月毎のカレンダー -->
      <table id="calendar-table" border="1" style="table-layout:fixed; min-height:200px;">
        <tr style="color:#FFFFFF;">
          <!-- 曜日の表示 -->
          <?php for ($i = 0; $i < 7; $i++) : ?>
            <th style="background-color:<?= $i === 0 ? "red;" : ($i === 6 ? "blue;" : "black;") ?>">
              <?= getWeekName($i); ?>
            </th>
          <?php endfor; ?>
        </tr>
        <?php foreach ($dateArray as $day2 => $date2) : ?>
          <?php list($y, $m, $d) = explode('-', $date2); ?>
          <?php if (($day2) % 7 == 0) : ?>
            <tr>
            <?php endif; ?>
            <td bgcolor="<?= $day2 % 7 === 0 ? "#FFCCFF" : ($day2 % 7 === 6 ? "#66CCFF" : "#FFFFFF") ?>">
              <a href="index.php?date=<?= $date2 ?>">
                <?= (int)$d ?></a>
            </td>
            <?php if (($day2) % 7 == 6) : ?>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>

      </table>

    </div>


  </div>


  <br>







  <input id="display-elem" type="hidden" value="enable">


  <!-- 週ごとのスケジュール -->
  <div id="schedule-wrapper">

    <div id="schedule-header">
      <button id="add-btn" type="button">新規スケジュール追加</button>
      <button id="free-time-btn" type="button">空き時間を表示</button>
    </div>

    <?php
    $json = [];
    $jsonIndex = 0;
    if (!empty($_SESSION['display_emp'])) :
      foreach ($_SESSION['display_emp'] as $key2 => $displayEmp) :
        //   $lastEndTime = 0; 
    ?>
        <div class="schedule-container">
          <div class="emp-profile">
            ここに社員の情報
          </div>

          <table class="schedule-table" border="1" width="850">

            <?php if ($key2 === 0) : ?>
              <tr style="height: 30px;">

                <?php foreach ($week as $day3 => $date3) : ?>
                  <?php list($y, $m, $d) = explode('-', $date3); ?>
                  <th><?= "$m/" . (int)$d . "(" . getWeekName($day3) . ")" ?></th>
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
                  foreach ($week as $day4 => $date4) :
                  ?>
                    <td bgcolor="<?= ($day4 + $key2) % 2 != 1 ? "white" : "#EEEEEE" ?>">
                      <?php if (!empty($scheduleList[$day4])) : ?>

                        <ul style="max-height:40px;">

                          <?php foreach ($scheduleList[$day4] as $schedule) : ?>
                            <?php $json[] = $schedule;
                            ?>

                            <li class="schedule-item" style="list-style: none;">
                              <?= $schedule['start_time'] ?>~
                              <?= $schedule['end_time'] ?>
                              <?= $schedule['title'] ?>
                            </li>



                            <!-- スケジュール詳細画面 -->
                            <div class="schedule-desc display-elem" style="display: none;">
                              <div class="display-header">
                                <h4>スケジュール詳細</h4>
                                <div>
                                  <button class="edit-btn" type="button">編集</button>
                                  <button class="close-btn" type="button" value="invalid">×</button>
                                </div>
                              </div>
                              <p class="schedule-datetime">
                                <?php list($y, $m, $d) = explode('-', $schedule['date']); ?>
                                <?= $y ?>/<?= $m ?>/<?= $d ?>/(<?= getWeekName($day4); ?>)
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
                              <ul>
                                <li>
                                  <?= $user['emp_name'] . "(" . $user['emp_id'] . ")" ?>
                                </li>
                                <?php if (!empty($schedule['attendees_id'])) :

                                  $attendees = fetchAttendees($schedule['attendees_id']);
                                  foreach ($attendees as $attendee) :
                                ?>
                                    <li>
                                      <?= $attendee["emp_name"] . "(" . $attendee['emp_id'] . ")" ?>
                                    </li>

                                  <?php endforeach; ?>
                                <?php endif; ?>
                              </ul>
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
                  <td bgcolor="<?= ($day4 + $j) % 2 != 1 ? "white" : "#EEEEEE" ?>"></td>
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


  <!-- 空き時間表示テーブル -->

  <?php
      $interval = 0.25;
      list($freeTimeList, $max) = calcFreeTime($_SESSION['display_emp'], $week, $interval);
  ?>

  <div id="free-time-table" class="display-elem" style="display: none;">
    <div class="display-header">
      <?php if (!empty($_SESSION['display_emp'])) : ?>
        <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
          <?= $displayEmp['emp_name'] . "(" . $displayEmp['emp_id'] . ")さん " ?>
        <?php endforeach; ?>
        の空いてる時間帯
      <?php endif; ?>

      <button class="close-btn" type="button">×</button>
    </div>
    <table border="1" bgcolor="#FFFFFF" width="700">
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

  </div>
<?php endif; ?>


<!-- スケジュールの新規追加・編集フォーム  -->

<form id="add-form" class="display-elem" action="" method="post" style="display: none;">
  <div class="form-wrapper">
    <div class="main-form">
      <div class="display-header">
        <h4 class="form-title">新規スケジュール登録</h4>

        <button class="delete-btn" type="submit" name="submit_delete" value="1" style="display:none;">削除</button>
        <button class="close-btn" type="button">×</button>
      </div>
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
      説明：
      <textarea class="input-memo" name="memo" placeholder="説明"></textarea>
      <br>
      参加者：
      <ul class="attendees-list">
        <li class="attendees-item"><?= $user['emp_name'] . "(" . $user['emp_id'] . ")" ?></li>
      </ul>


      <input class="input-schedule-id" type="hidden" name="schedule_id" value="">
      <button class="add-btn" type="submit" name="submit_add" value="1">登録</button>
    </div>


    <div class="sub-form">
      <p>参加者を追加</p>

      <div>

        <select class="select-dept">
          <option class="dept-option" value="0">全部署</option>
          <?php foreach ($deptList as $dept) : ?>
            <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
          <?php endforeach; ?>
        </select>
        <input class="input-keyword" type="text">


        <div class="emp-list">
          <input type="hidden" name="checked_emp[]" value="<?= $userId ?>">
          <ul>
            <?php foreach ($empList as $emp) : ?>
              <li class="emp-item id-<?= $emp['emp_id'] ?>" style="display:none;">
                <label>
                  <?= $emp['emp_id'] ?>/
                  <?= $emp['dept_name'] ?>/
                  <?= $emp['emp_name'] ?>
                  <input class="input-attend-check" type="checkbox" name="checked_emp[]" data-name="<?= $emp['emp_name'] ?>" value="<?= $emp['emp_id'] ?>" <?= $emp['emp_id'] === $userId ? "checked disabled" : "" ?>>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
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

  var addForm = document.getElementById('add-form');
  var addCloseBtn = addForm.querySelector('.close-btn');

  var empDataList = <?= $empDataList ?>;
  var scheduleDataList = <?= $scheduleDataList ?>;

  // エンターキーでのフォーム送信を無効
  function noEnter(event) {
    if (event.keyCode === 13) {
      console.log('yy');
      event.preventDefault();
    }
  }
  // 入力ワードで絞り込み（社員番号、部署名、名前）
  function sortInput(event) {
    var empList = this.parentElement.querySelector('.emp-list');
    var selectDept = this.parentElement.querySelector('.select-dept');
    console.log(this.value);
    console.log(empList);



    empList.style.display = "none";
    if (this.value != "") {
      for (var empData of empDataList) {
        var empItem = empList.querySelector(".id-" + empData['emp_id']);
        var inputCheckEmp = empItem.querySelector(".input-check");
        console.log(inputCheckEmp);

        empItem.style.display = "none";

        if (empData['emp_id'].indexOf(this.value) != -1 ||
          empData['emp_name'].indexOf(this.value) != -1 ||
          empData['dept_name'].indexOf(this.value) != -1) {
          empList.style.display = "block";
          empItem.style.display = "block";
          console.log(empList);
          console.log('yes!!');
        }
        if (selectDept.value > 0 && selectDept.value != empData['dept_id']) {

          empItem.style.display = "none";
        }
      }
    }



  }
  // 部署名の選択肢で絞り込み
  function sortSelect(event) {
    for (var empData of empDataList) {
      var empList = this.parentElement.querySelector('.emp-list');
      var empItem = empList.querySelector(".id-" + empData['emp_id']);
      console.log(empList);
      if (this.value > 0 && this.value != empData['dept_id']) {
        console.log(empData['dept_id']);
        empItem.style.display = "none";
      }
    }

  }

  // チェックした社員を参加者一覧に表示する
  function addAttendees(attendeesList, inputCheckList) {
    while (attendeesList.firstChild) {
      attendeesList.removeChild(attendeesList.firstChild);
    }
    for (var inputCheck of inputCheckList) {
      if (inputCheck.checked) {
        var li = document.createElement("li");
        li.textContent = inputCheck.dataset.name + "(" + inputCheck.value + ")";
        attendeesList.appendChild(li);
      }
    }
  }

  // 絞り込み検索


  var inputKeywordList = document.querySelectorAll('.input-keyword');
  console.log(inputKeywordList);
  for (var inputKeyword of inputKeywordList) {
    inputKeyword.addEventListener('input', sortInput);
    inputKeyword.addEventListener('keydown', noEnter);
  }

  var selectDeptList = document.querySelectorAll('.select-dept');
  for (var selectDept of selectDeptList) {
    selectDept.addEventListener('change', sortSelect);
  }

  var empListList = document.querySelectorAll('.emp-list');
  for (var empList of empListList) {
    empList
  }




  // スケジュールを押すと詳細表示
  scheduleItemList.forEach(function(scheduleItem, index) {

    var scheduleDesc = scheduleItem.parentElement.querySelector(".schedule-desc");

    var closeBtn = scheduleDesc.querySelector('.close-btn');
    var editBtn = scheduleDesc.querySelector('.edit-btn');
    //var attendeesList = scheduleDesc.querySelector('.attendees-list')
    var x, y;

    //console.log(attendeesList);

    scheduleItem.addEventListener('click', function(event) {

      // 詳細画面を開く  
      if (displayElem.value === 'enable') {
        displayElem.value = 'disable';

        scheduleDesc.style.display = 'block';
      }
    });

    // 詳細画面を閉じる
    closeBtn.addEventListener('click', function() {
      displayElem.value = "enable";
      scheduleDesc.style.display = "none";
    });


    editBtn.addEventListener('click', function() {
      displayElem.value = "disable";
      scheduleDesc.style.display = "none";

      // 新規登録画面から編集画面のクローンを作成 & 表示
      var scheduleData = scheduleDataList[index];
      var editForm = addForm.cloneNode(true);
      var closeBtn = editForm.querySelector('.close-btn');

      var formTitle = editForm.querySelector('.form-title');
      var delBtn = editForm.querySelector('.delete-btn');
      var updateBtn = editForm.querySelector('.add-btn');
      var inputTitle = editForm.querySelector('.input-title');
      var inputDate = editForm.querySelector('.input-date');
      var inputStartTime = editForm.querySelector('.input-starttime');
      var inputEndTime = editForm.querySelector('.input-endtime');
      var inputMemo = editForm.querySelector('.input-memo');
      var inputScheduleId = editForm.querySelector('.input-schedule-id');
      var inputCheckList = editForm.querySelectorAll(".input-attend-check");
      var inputKeyword = editForm.querySelector('.input-keyword');
      var selectDept = editForm.querySelector('.select-dept');
      var attendeesList = editForm.querySelector(".attendees-list");

      // var addAttendeesBtn = editForm.querySelector(".add-attendees-btn");




      console.log(scheduleData);

      formTitle.textContent = "スケジュール編集";
      delBtn.style.display = "block";
      inputTitle.setAttribute('value', scheduleData['title']);
      inputDate.setAttribute('value', scheduleData['date']);
      inputStartTime.setAttribute('value', scheduleData['start_time']);
      inputEndTime.setAttribute('value', scheduleData['end_time']);
      inputMemo.setAttribute('value', scheduleData['memo']);
      inputScheduleId.setAttribute('value', scheduleData['id']);
      updateBtn.textContent = "更新";
      updateBtn.setAttribute('name', 'submit_update');


      //  登録されている参加者にチェックを入れる
      var checkedEmpList = [];
      if (scheduleData['attendees_id'] != "0") {
        var attendeesIdList = scheduleData['attendees_id'].split(',');
        for (var inputCheck of inputCheckList) {
          //   console.log(inputAttendee.value);
          for (var attendeeId of attendeesIdList) {
            if (inputCheck.value === attendeeId) {
              inputCheck.checked = true;
              checkedEmpList.push(attendeeId);
            }
          }
        }
      }

      addAttendees(attendeesList, inputCheckList);

      editForm.style.display = 'block';
      document.body.appendChild(editForm);

      for (var inputCheck of inputCheckList) {
        inputCheck.addEventListener('change', addAttendees.bind(null, attendeesList, inputCheckList));
      }

      inputKeyword.addEventListener('input', sortInput);
      selectDept.addEventListener('change', sortSelect);

      // 編集画面を閉じる
      closeBtn.addEventListener('click', function(event) {
        displayElem.value = "enable";
        editForm.remove();
      });
    });

  });


  var displayElem = document.getElementById('display-elem');


  // 新規追加ボタンを押すとフォームを表示
  addBtn.addEventListener('click', function(event) {
    if (displayElem.value === "enable") {
      displayElem.value = "disable";
      addForm.style.display = 'block';
    }
  });

  var addAttendeesList = addForm.querySelector(".attendees-list");
  var addInputCheckList = addForm.querySelectorAll(".input-attend-check");
  for (var inputCheck of addInputCheckList) {
    inputCheck.addEventListener("change", addAttendees.bind(null, addAttendeesList, addInputCheckList));
  }


  addCloseBtn.addEventListener('click', function() {
    displayElem.value = 'enable';
    addForm.style.display = 'none';
  });


  var inputCheckList = document.querySelectorAll('.input-attend-check');
  for (var inputCheck of inputCheckList) {
    inputCheck.addEventListener('change', function() {
      if (this.checked) {

      }
    });

  }


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

  var freeTimeTable = document.getElementById("free-time-table");
  var freeTimeBtn = document.getElementById("free-time-btn");
  var freeTimeCloseBtn = freeTimeTable.querySelector(".close-btn");
  console.log(freeTimeTable);
  freeTimeBtn.addEventListener("click", function() {
    console.log(freeTimeTable);
    if (displayElem.value === "enable") {
      freeTimeTable.style.display = "block";
    }
  });
  freeTimeCloseBtn.addEventListener("click", function() {
    freeTimeTable.style.display = "none";
    displayElem.value = "enable";
  });

  function getToday() {
    var date = new Date();
    var y = date.getFullYear();
    var m = ("00" + (date.getMonth() + 1)).slice(-2);
    var d = ("00" + date.getDate()).slice(-2);
    return (y + "-" + m + "-" + d);
  }
</script>

</body>

</html>