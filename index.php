<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();

// ログインしているかどうかチェック
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === "") {
  header('Location: http://localhost/ScheShare/login.php');
  exit();
}

// ログインユーザー情報取得
$userId = $_SESSION['user_id'];
$user = selectEmp($userId);

// 最初にスケジュールを表示するユーザーはログインユーザーのみとする
if (!isset($_SESSION['display_emp']) || $_SESSION['display_emp'] === "" || !in_array($user, $_SESSION['display_emp'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
}

//var_dump($_SESSION['display_emp']);

// 表示するカレンダーの年月情報を取得
if (isset($_GET['date'])) {
  $date = escape($_GET['date']);
  list($y, $m, $d) = explode('-', $date);
} else {
  $date = getToday();
  list($y, $m, $d) = explode('-', $date);
}
$dateArray = createCalendar($m, $y);  // 指定した年月の一ヶ月の情報を取得
$week = getWeek($date, $dateArray); // 指定した日付を含む週の一週間の情報を取得




$selectedDeptId = '0'; // ?
$deptList = selectDept(); // 部署リストを取得



// 表示する社員の取得
if (isset($_POST['submit_display'])) {
  $_SESSION['display_emp'] = array();
  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $_SESSION['display_emp'][] = selectEmp($checkedEmpId);
  }
}

// 新規スケジュール登録
if (isset($_POST['submit_add'])) {
  var_dump($_POST);
  //exit;
  insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit();
}

// スケジュール編集
if (isset($_POST['submit_update'])) {
  updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp'], $_POST['schedule_id']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
}

// スケジュール削除
if (isset($_POST['submit_delete'])) {
  deleteSchedule($_POST['schedule_id']);
  var_dump($_POST);
  exit;
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
}

$jsonDisplay = $_SESSION['display_emp'];
$jsonDisplay = json_encode($jsonDisplay);

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
    <!-- 社員検索画面エリア -->
    <form class="search-area" action="" method="post">
      <select class="select-dept">
        <option class="dept-option" value="0">全部署</option>
        <?php foreach ($deptList as $dept) : ?>
          <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <input class="input-keyword" width="" type="text" placeholder="（例) 社員番号,名前,部署名">
      <button class="search-btn" type="submit" data-id="display_emp">検索</button>



      <div class="search-result" style="display: none;">
        <div class="emp-list" style="display: none;">
        </div>
        <button class="display-btn" type="submit" name="submit_display" value="1">スケジュールを表示</button>
      </div>
    </form>

    <!-- カレンダー表示エリア -->
    <div id="calendar-area">
      <h2 class="calendar-header">
        <a href="index.php?date=<?= $m - 1 > 0 ? $y . "-" . ($m - 1) . "-1" : ($y - 1) . "-12-1" ?>">◁</a>
        <span><?= $y ?>年<?= $m ?>月</span>
        <a href="index.php?date=<?= $m + 1 <= 12 ? $y . "-" . ($m + 1) . "-1" : ($y + 1) . "-1-1" ?>">▷</a>
      </h2>

      <!-- カレンダー -->
      <table id="calendar-table" border="1" style="table-layout:fixed; min-height:200px;">
        <tr style="color:#FFFFFF;">
          <!-- 曜日の表示 -->
          <?php for ($day = 0; $day < 7; $day++) : ?>
            <th bgcolor=<?= $day === 0 ? "#FF0000" : ($day === 6 ? "#0000FF" : "#000000") ?>>
              <?= getWeekName($day); ?>
            </th>
          <?php endfor; ?>
        </tr>
        <!-- 日付の表示 -->
        <?php foreach ($dateArray as $day2 => $date2) : ?>
          <?php list($y2, $m2, $d2) = explode('-', $date2); ?>
          <?php if (($day2) % 7 == 0) : ?>
            <tr>
            <?php endif; ?>
            <td style="opacity: <?= ($m2 < $m || $m2 > $m) ? "0.5" : "1" ?>">
              <a href="index.php?date=<?= $date2 ?>">
                <?= ($m2 < $m || $m2 > $m) ? (int)$m . "/" . (int)$d2 : (int)$d2 ?>
              </a>
            </td>
            <?php if (($day2) % 7 == 6) : ?>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </table>

    </div>
  </div>

  <!-- ある要素の表示中に他の要素を表示させないようにするためのフラグ -->
  <input id="display-elem" type="hidden" value="enable">


  <!-- スケジュール（一週間分） -->
  <div id="schedule-wrapper">

    <div id="schedule-header">
      <button id="add-btn" type="button">新規スケジュール追加</button>
      <button id="free-time-btn" type="button">空き時間を表示</button>
    </div>

    <?php
    if (!empty($_SESSION['display_emp'])) :
      foreach ($_SESSION['display_emp'] as $key => $displayEmp) :
    ?>
        <div class="schedule-container">
          <div class="emp-profile">
            <div>
              <?= $displayEmp['dept_name'] ?>
              <?= $displayEmp['emp_name'] ?>
            </div>
            (<?= $displayEmp['emp_id'] ?>)
          </div>

          <table class="schedule-table" border="1" width="850">

            <?php if ($key === 0) : ?>
              <tr style="height: 30px;">

                <?php foreach ($week as $day3 => $date3) : ?>
                  <?php list($y, $m, $d) = explode('-', $date3); ?>
                  <th><?= $m . "/" . (int)$d . "(" . getWeekName($day3) . ")" ?></th>
                <?php endforeach; ?>

              </tr>
            <?php endif; ?>

            <!-- 一週間のスケジュール（一人分） -->


            <?php
            $scheduleFlag = 0; // スケジュール検出用フラグ
            for ($startTime = 0; $startTime < 24; $startTime++) :
              $scheduleList = selectWeekSchedule($displayEmp['emp_id'], $week, $startTime);

              if (!empty($scheduleList)) :
            ?>
                <tr>
                  <?php
                  $scheduleFlag = 1;
                  foreach ($week as $day4 => $date4) :
                  ?>
                    <td bgcolor="<?= ($day4 + $key) % 2 != 1 ? "white" : "#EEEEEE" ?>">
                      <?php if (!empty($scheduleList[$day4])) : ?>
                        <ul style="min-height:40px;">
                          <?php foreach ($scheduleList[$day4] as $schedule) : ?>
                            <li class="schedule-item" data-id="<?= $schedule['id'] ?>">
                              <?= $schedule['start_time'] ?>~
                              <?= $schedule['end_time'] ?>
                              <?= $schedule['title'] ?>
                            </li>

        </div>

      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    </td>

  <?php endforeach; ?>
  </tr>
<?php
              endif;

            endfor; ?>
<!-- 一週間の予定が何もないとき -->
<?php if ($scheduleFlag === 0) : ?>
  <tr>
    <?php for ($day6 = 0; $day6 < 7; $day6++) : ?>
      <td bgcolor="<?= ($day6) % 2 != 1 ? "#FFFFFF" : "#EEEEEE" ?>"></td>
    <?php endfor; ?>
  </tr>
<?php endif; ?>
</table>
  </div>
<?php endforeach; ?>
<?php //var_dump($json); 
      $scheduleDataList = json_encode($jsonSchedule);
?>
</div>


<!-- 空き時間表示テーブル -->

<?php
      list($freeTimeList, $max) = calcFreeTime($_SESSION['display_emp'], $week);
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

      <?php foreach ($week as $day5 => $date3) : ?>
        <?php list($y, $m, $d) = explode('-', $date3); ?>
        <th><?= "$m/" . (int)$d . "(" . getWeekName($day5) . ")" ?></th>
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



<!-- スケジュール詳細画面 -->
<div class="schedule-desc display-elem" style="display: none;">
  <div class="display-header">
    <h4>スケジュール詳細</h4>
    <button class="close-btn" type="button" value="invalid">×</button>

  </div>

  <table class="detail-table">
  </table>
  <button class="edit-btn" type="button" data-id="">編集</button>
</div>

<!-- スケジュール新規登録＆編集用のフォーム -->
<form id="form" class="display-elem" action="" method="post" style="display: none;">
  <div class="display-header">
    <h4 class="form-title">スケジュール編集画面</h4>
    <button class="close-btn" type="button">×</button>
  </div>
  <div style="display: flex;">
    <div>

      <table class="input-table">
      </table>

      <input type="hidden" name="checked_emp[]" value="<?= $user['emp_id'] ?>">
      <button class="submit-btn" type="submit" name="submit_edit" value="1">更新</button>
      <a class="edit-attendee-btn" href="" data-id="">参加者を編集</a>
      <button class="delete-btn" type="submit" name="submit_delete" value="1" style="display: none;">削除</button>
    </div>
  </div>
  
    <!-- 検索フォーム -->
    <div class="search-area2" style="display: none;">
      <select class="select-dept">
        <option class="dept-option" value="0">全部署</option>
        <?php foreach ($deptList as $dept) : ?>
          <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <input class="input-keyword" width="" type="text" placeholder="（例) 社員番号,名前,部署名">
      <button class="search-btn" type="submit" data-checked="0">検索</button>
  
      <div class="emp-list" style="display: none;">
      </div>
    </div>

</form>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script>



  


  $(function() {

    // 検索結果
    var $searchBtn = $(".search-btn");
    console.log($searchBtn);
    $searchBtn.on("click", function(e) {
      e.preventDefault();
      var keyword = $(this).parent().find(".input-keyword").val();
      var deptId = $(this).parent().find(".select-dept").val();
      var $empTable = $(this).parent().find(".emp-list");
      var $searchRes = $(this).parent().find(".search-result");
      console.log(keyword);
      console.log(deptId);
      var array = $(this).data("checked");
      console.log(array);
      var checkedId = $(this).data("id");
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          "dept_id": deptId,
          "keyword": keyword,
          "checked_id": checkedId
        }
      }).done(function(data) {
        console.log("success");
        console.log(data);
        console.log($empTable);
        $empTable.html(data);
        $empTable.css("display", "block");
        $searchRes.css("display", "block");
      }).fail(function() {

      });

    });

    // スケジュール詳細画面の表示
    var $scheduleItem = $(".schedule-item");
    var $editBtn = $(".edit-btn");
    var $detailTable = $(".detail-table");
    var $scheduleDisplay = $(".schedule-desc");
    var $form = $("#form");

    var $searchArea = $(".search-area2");


    $scheduleItem.on("click", function() {
      console.log($(this).parent().find(".schedule-info"));
      var scheduleId = $(this).data("id");
      var $scheduleInfo = $(this).parent().find(".detail-table");
      $.ajax({
        type: "POST",
        url: "ajax2.php",
        data: {
          "schedule_id": scheduleId,
          "edit_flag": "0"
        }
      }).done(function(data) {
        console.log($editBtn.data());
        $editBtn.data("id", scheduleId);
        $detailTable.html(data);
        $form.css("display", "none");
        $scheduleDisplay.css("display", "block");

      }).fail(function() {

      });
    });

    // スケジュール編集フォームの表示
    var $inputTable = $(".input-table");
    var $delBtn = $(".delete-btn");
    var $editAttendeeBtn = $(".edit-attendee-btn");

    $editBtn.on("click", function() {
      var scheduleId = $(this).data("id");
      
      $.ajax({
        type: "POST",
        url: "ajax2.php",
        data: {
          "schedule_id": scheduleId,
          "edit_flag": "1"
        }
      }).done(function(data) {
        $inputTable.html(data);
        $editAttendeeBtn.data("id", scheduleId);
        $scheduleDisplay.css("display", "none");
        $searchArea.css("display", "none");
        $delBtn.css("display", "block");
        $form.css("display", "block");
      }).fail(function() {

      });
    });

    // 新規スケジュール登録フォーム表示
    var $addBtn = $("#add-btn");
    var $formTitle = $form.find(".form-title");
    var $formSubmitBtn = $form.find(".submit-btn");
    $addBtn.on("click", function() {
      $.ajax({
        type: "POST",
        url: "ajax2.php",
        data: {
          "schedule_id": "0",
          "edit_flag": "0"
        }
      }).done(function(data) {
        $inputTable.html(data);
        $scheduleDisplay.css("display", "none");
        $delBtn.css("display", "none");
        $searchArea.css("display", "none");
        $formTitle.text("新規スケジュール登録");
        $formSubmitBtn.text("登録");
        $formSubmitBtn.attr("name", "submit_add");
        $form.css("display", "block");
      }).fail(function() {
      });
    });

    // 新規登録・編集フォーム用の検索画面の表示

    $editAttendeeBtn.on("click", function(e) {
      e.preventDefault();
      console.log($searchArea);
      var scheduleId = $editAttendeeBtn.data("id");
      var $searchBtn = $form.find(".search-btn");
      $searchBtn.data("checked", scheduleId);
      $(this).parent().parent().append($searchArea);
      $searchArea.css("display", "block");
    });

    //

    // ×ボタンを押すとクローズ
    var $closeBtn = $(".close-btn");
    $closeBtn.on("click", function() {
      var $displayElem = $(this).parent().parent();
      $displayElem.css("display", "none");
    })
  });


  //var displayElem = document.getElementById('display-elem');



/*
  var inputCheckList = document.querySelectorAll('.input-attend-check');
  for (var inputCheck of inputCheckList) {
    inputCheck.addEventListener('change', function() {
      if (this.checked) {

      }
    });

  }


    
  var inputStartTime = document.querySelector('.input-starttime');
  var inputEndTime = document.querySelector('.input-endtime');


  var addForm = document.getElementById('add-form');

    // エンターキーでのフォーム送信を無効
  function noEnter(event) {
    if (event.keyCode === 13) {
      console.log('yy');
      event.preventDefault();
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


      for (var inputCheck of inputCheckList) {
        inputCheck.addEventListener('change', addAttendees.bind(null, attendeesList, inputCheckList));
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
  */
</script>

</body>

</html>