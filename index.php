<?php
require_once(dirname(__FILE__) . '/function.php');
session_start();

/* ログインチェック */
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === "") {
  header('Location: http://localhost/ScheShare/login.php');
  exit();
}

/* ログインユーザー情報取得 */
$userId = $_SESSION['user_id'];
$user = selectEmp($userId);

/* スケジュールを表示するユーザーの変数にログインユーザーを格納 */
if (!isset($_SESSION['display_emp']) || $_SESSION['display_emp'] === "" || !in_array($user, $_SESSION['display_emp'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user;
}


/* 表示するカレンダーの年月情報を取得 */
if (isset($_GET['date'])) {
  $date = escape($_GET['date']);
  list($y, $m, $d) = explode('-', $date);
} else {
  $date = getToday();
  list($y, $m, $d) = explode('-', $date);
}
$dateArray = createCalendar($m, $y);  // 指定した年月の一ヶ月の情報を取得
$week = getWeek($date, $dateArray); // 指定した日付を含む週の一週間の情報を取得



/* 部署リストを取得 */
$deptList = selectDept();

/* 表示する社員情報を取得　*/
if (isset($_POST['submit_display'])) {
  $_SESSION['display_emp'] = array();
  $_SESSION['display_emp'][] = $user; // ログインユーザーは一番上に表示させる
  foreach ($_POST['checked_emp'] as $checkedEmpId) {
    $_SESSION['display_emp'][] = selectEmp($checkedEmpId);
  }
}

/* 非同期通信用に表示する社員のidのみの配列データを別で生成 */
foreach ($_SESSION['display_emp'] as $displayEmp) {
  $displayEmpList[] = $displayEmp['emp_id'];
}

/* 新規スケジュール登録 */
if (isset($_POST['submit_add'])) {
  insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit();
}

/* スケジュール編集 */
if (isset($_POST['submit_edit'])) {
  var_dump($_POST);
  //  exit;
  updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp'], $_POST['schedule_id']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit();
}

/* スケジュール削除 */
if (isset($_POST['submit_delete'])) {
  deleteSchedule($_POST['schedule_id']);
  header("Location:http://localhost/ScheShare/index.php?date=" . $date);
  exit;
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
    <h1><a href="index.php">ScheShare</a></h1>
    <div>
      <p><?= $userId ?>さんログイン中</p>
      <p><a href="login.php">ログアウト</a></p>
    </div>
  </header>


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
    <div id="schedule-header">
      <div>
        <?php if (!empty($_SESSION['display_emp'])) : ?>
          <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
            <?= $displayEmp['emp_name'] . "(" . $displayEmp['emp_id'] . ")さん " ?>
          <?php endforeach; ?>
          のスケジュールを表示中
        <?php endif; ?>
      </div>

      <div>
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
              <?= $displayEmp['dept_name'] ?>
              <?= $displayEmp['emp_name'] ?>
            </div>
            (<?= $displayEmp['emp_id'] ?>)
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
                            <li class="schedule-item" data-id="<?= $schedule['id'] ?>">
                              <?= $schedule['start_time'] ?>~
                              <?= $schedule['end_time'] ?>
                              <?= $schedule['title'] ?>
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


<!-- 以下、非同期通信用部品 -->

<!-- スケジュール詳細画面 -->
<div class="schedule-desc display-elem" style="display: none;">
  <div class="display-header">
    <h3>スケジュール詳細</h3>
    <button class="close-btn" type="button" value="invalid">×</button>
  </div>

  <table class="detail-table"></table>

  <div>
    <button class='edit-btn' type='button' data-id='' style="display: none;">編集</button>
  </div>
</div>

<!-- スケジュール新規登録＆編集用のフォーム -->
<form id="form" class="display-elem" action="" method="post" style="display: none;">
  <div class="display-header">
    <h3 class="form-title">スケジュール編集画面</h3>
    <button class="close-btn" type="button">×</button>
  </div>

  <div style="display: flex;">
    <div>

      <table class="form-table">
      </table>

      <input type="hidden" name="checked_emp[]" value="<?= $user['emp_id'] ?>">
      <input class="input-schedule-id" type="hidden" name="schedule_id" value="">
      <a class="edit-attendee-btn" href="" data-checked="">参加者を編集</a>
      <br>
      <button class="submit-btn" type="submit" name="submit_edit" value="1">更新</button>
      <button class="delete-btn" type="submit" name="submit_delete" value="1" style="display: none;">削除</button>
    </div>


    <!-- フォーム内検索画面 -->
    <div class="search-attendees" style="display: none;">
      <select class="select-dept">
        <option class="dept-option" value="0">全部署</option>
        <?php foreach ($deptList as $dept) : ?>
          <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <br>
      <input class="input-keyword" width="" type="text" placeholder="（例) 社員番号,名前,部署名">
      <button class="search-btn" type="submit" data-checked="0">検索</button>

      <div class="emp-table" style="display: none;">
      </div>
    </div>

  </div>
</form>


<!-- 空き時間テーブル -->
<div id="display-free-time" class="display-elem" style="display: none;">
  <div class="display-header">
    <?php if (!empty($_SESSION['display_emp'])) : ?>
      <?php foreach ($_SESSION['display_emp'] as $displayEmp) : ?>
        <?= $displayEmp['emp_name'] . "(" . $displayEmp['emp_id'] . ")さん " ?>
      <?php endforeach; ?>
      の空いてる時間帯
    <?php endif; ?>

    <button class="close-btn" type="button">×</button>
  </div>

  <table class="free-time-table" border="1" bgcolor="#FFFFFF" width="700">
  </table>
</div>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script>
  $(function() {

    // 検索処理
    var $searchBtn = $(".search-btn");
    console.log($searchBtn);
    $searchBtn.on("click", function(e) {
      e.preventDefault();
      var keyword = $(this).parent().find(".input-keyword").val();
      var deptId = $(this).parent().find(".select-dept").val();
      var $empTable = $(this).parent().find(".emp-table");
      var checkedId = $(this).data("checked");
      console.log(checkedId);
      $.ajax({
        type: "POST",
        url: "search_ajax.php",
        data: {
          "dept_id": deptId,
          "keyword": keyword,
          "checked_list": checkedId
        }
      }).done(function(data) {
        $empTable.html(data);
        $empTable.css("display", "block");
      }).fail(function() {

      });

    });

    // スケジュール詳細画面の表示
    var $scheduleItem = $(".schedule-item");
    var $editBtn = $(".edit-btn");
    var $detailTable = $(".detail-table");
    var $displaySchedule = $(".schedule-desc");
    var $form = $("#form");

    var $searchAttendees = $(".search-attendees");


    $scheduleItem.on("click", function(e) {
      var scheduleId = $(this).data("id");

      $.ajax({
        type: "POST",
        url: "form_ajax.php",
        data: {
          "schedule_id": scheduleId,
          "edit_flag": "0"
        }
      }).done(function(data) {
        console.log(data);
        $editBtn.data("id", scheduleId);
        $detailTable.html(data);
        var isAttendee = $detailTable.find(".is-attendee").val();
        console.log(!!isAttendee);
        console.log($detailTable.find(".is-attendee").val());
        if (isAttendee == 1) $editBtn.css("display", "block");
        else $editBtn.css("display", "none");
        $form.css("display", "none");
        $displaySchedule.css("display", "block");

      }).fail(function() {

      });
    });

    // スケジュール編集フォームの表示
    var $formTable = $(".form-table");
    var $delBtn = $(".delete-btn");
    var $editAttendeeBtn = $(".edit-attendee-btn");
    var $formInputId = $form.find(".input-schedule-id");
    //var $inputKeyword = $(".input-keyword");

    $editBtn.on("click", function() {
      formReset(); // フォーム初期化
      $this = $(this);
      var scheduleId = $(this).data("id"); // クリックしたスケジュールのidを取得

      $.ajax({
        type: "POST",
        url: "form_ajax.php",
        data: {
          "schedule_id": scheduleId,
          "edit_flag": "1"
        }
      }).done(function(data) {
        console.log(data);
        console.log("aaaa");
        $formTable.html(data);
        $displaySchedule.css("display", "none");

        $delBtn.css("display", "inline-block");
        $form.css("display", "block");

        $formTitle.text("スケジュール編集");
        $formSubmitBtn.text("更新");
        $formSubmitBtn.attr("name", "submit_edit");
        $formInputId.val(scheduleId);

        // チェック済みの社員のデータを取得しておく
        var $checkedList = $form.find(".checked-list").data("checked");
        console.log($checkedList);
        $editAttendeeBtn.data("checked", $checkedList);
        //       console.log($checkedList);
      }).fail(function() {

      });
    });

    // 新規スケジュール登録フォーム表示
    var $addBtn = $("#add-btn");
    var $formTitle = $form.find(".form-title");
    var $formSubmitBtn = $form.find(".submit-btn");
    var $formSearchBtn = $form.find(".search-btn");
    $addBtn.on("click", function() {
      formReset(); // フォーム初期化

      //console.log($formSearchBtn.data("checked"));
      $.ajax({
        type: "POST",
        url: "form_ajax.php",
        data: {
          "schedule_id": "0",
          "edit_flag": "0"
        }
      }).done(function(data) {
        $formTable.html(data);
        $displaySchedule.css("display", "none");
        $delBtn.css("display", "none");

        $formTitle.text("新規スケジュール登録");
        $formSubmitBtn.text("登録");
        $formSubmitBtn.attr("name", "submit_add");


        $form.css("display", "block");
      }).fail(function() {});
    });

    // 新規登録・編集フォーム用の検索画面の表示

    $editAttendeeBtn.on("click", function(e) {
      e.preventDefault();
      //   console.log($searchArea);
      var checkedList = $editAttendeeBtn.data("checked");
      //   console.log(checkedList);

      $formSearchBtn.data("checked", checkedList);
      $(this).parent().parent().append($searchAttendees);
      $searchAttendees.css("display", "block");
    });

    // 表示された社員の空き時間を表示
    var freeTimeBtn = $("#free-time-btn");
    var displayFreeTime = $('#display-free-time');
    var freeTimeTable = $('.free-time-table');
    freeTimeBtn.on("click", function() {
      var displayEmpList = $(this).data("id");
      var week = $(this).data("week");
      console.log(freeTimeTable);

      $.ajax({
        type: "POST",
        url: "freetime_ajax.php",
        data: {
          displayEmpList: displayEmpList,
          week: week
        }
      }).done(function(data) {
        console.log(data);
        freeTimeTable.html(data);
        displayFreeTime.css("display", "block");


      }).fail(function() {

      });

    });


    // ×ボタンを押すとクローズ
    var $closeBtn = $(".close-btn");
    $closeBtn.on("click", function() {
      var $displayElem = $(this).parent().parent();
      $displayElem.css("display", "none");
    })

    // フォーム初期化処理 (新規登録フォーム⇄編集フォーム)
    function formReset() {
      $searchAttendees.css("display", "none");
      $searchAttendees.find(".emp-table").empty();
      $form.find(".input-keyword").val("");
      $editAttendeeBtn.data("checked", "");
      $formInputId.val("");
    }
  });




  // エンターキーでのフォーム送信を無効
  function noEnter(event) {
    if (event.keyCode === 13) {
      console.log('yy');
      event.preventDefault();
    }
  }

  var form = document.getElementById('form');

  // スケジュール登録・更新時の入力エラーチェック
  form.addEventListener('submit', function(e) {
    var inputTitle = this.querySelector('input[name="title"]');
    var inputDate = this.querySelector('input[name="date"]');
    var inputStartTime = this.querySelector('input[name="start"]');
    var inputEndTime = this.querySelector('input[name="end"]');

    var error = [];
    if (inputTitle.value === "") {
      error.push("入力エラー：タイトルを入力してください。");
    }
    if (inputDate.value === "" || inputStartTime === "" || inputEndTime === "") {
      error.push("入力エラー：日時を入力してください。");
    } else {
      var now = new Date();
      var startTime = new Date(inputDate.value + " " + inputStartTime.value);
      var endTime = new Date(inputDate.value + " " + inputEndTime.value);
      if (startTime.getTime() < now.getTime()) {
        error.push("入力エラー：日時を現在よりも遅く設定してください。");
      }
      if(startTime.getTime() >= endTime.getTime()){
        error.push("入力エラー：終了時刻を開始時刻より遅く設定してください。");
      }
    }
    if(error.length > 0){
      window.alert(error.join("\n"));
      e.preventDefault();
    }
  });

</script>

</body>

</html>