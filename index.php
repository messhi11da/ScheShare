<?php
  require_once(dirname(__FILE__).'/function.php');
  
  $empId = 154;

  $today = getToday();
  if(isset($_GET['date'])){
    $date = escape($_GET['date']);
    list($y, $m, $d) = explode('-', $date);   
  }else{ 
    $date = $today;
    list($y, $m, $d) = explode('-', $date);   
  }

  $dateArray = createCalendar($m, $y);
  var_dump($date);
  echo "<br>";
  var_dump($dateArray);
  $weekday = getWeekday($date, $dateArray);

  
    
  if(isset($_POST['submit_search'])){
    $keyword = escape($_POST['keyword']);
    if(!empty($keyword)) $searchedEmp = searchEmp($keyword);
  }
  
  if(isset($_POST['submit_checked'], $_POST['checkedEmp'])){ 
    $checkedEmp = selectEmp($_POST['checkedEmp']);
  }




  if(isset($_POST['submit_add'])){
    $title = escape($_POST['title']);
    $date = $_POST['date'];
    $startTime = $_POST['start'];
    $endTime = $_POST['end'];
    $memo = escape($_POST['memo']);
    if(empty($title)) $error[] = "タイトルを入力してください。";
    if($startTime >= $endTime) $error[] = "終了時刻を開始時刻より遅く設定してください"; 
    if(empty($error)){
      insertSchedule($empId, $date, $startTime, $endTime, $title, $memo);
      header("Location:http://localhost/ScheShare/index.php");
      exit;
    }
  }



?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>ScheShare</title>
    <link href="./css/style.css" rel="stylesheet">
  </head>
  
    <h1>ScheShare</h1>

    <form action="" method="post">
      <input type="text" name="keyword" placeholder="社員を探す">
      <button type="submit" name="submit_search" value="1">検索</button>
    </form>

    <?php if(isset($searchedEmp)): ?>
      <p>検索結果: <?= count($searchedEmp); ?>件</p>
      <?php if(count($searchedEmp) > 0): ?>
        <form action="" method="post">
          <table>
            <tr>
              <th>社員番号</th>
              <th>部署</th>
              <th>名前</th>
              <th>
                <button type="submit" name="submit_checked" value="1">選択</button>
              </th>
            </tr>
            <?php foreach($searchedEmp as $emp): ?>
              <tr>
                <td><?= $emp['emp_id'] ?></td>
                <td><?= $emp['dept_name'] ?></td>
                <td><?= $emp['emp_name'] ?></td>
                <td>
                  <input type="checkbox" name="checkedEmp[]" value="<?= $emp['emp_id'] ?>">
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </form>
      <?php endif; ?>
    <?php endif; ?>
    <table></table>


    <a href="index.php?date=<?= $m-1 > 0? $y."-".($m-1)."-1" : ($y-1)."-12-1" ?>">◁</a>
    <span><?= $y ?>年<?= $m ?>月</span>
    <a href="index.php?date=<?= $m+1 <= 12? $y."-".($m+1)."-1" : ($y+1)."-1-1" ?>">▷</a>
    <!-- 月毎のカレンダー -->
    <table id="calendar-table" border="1">
      <tr>
        <!-- 曜日の表示 -->
        <?php for($i=0; $i<7; $i++): ?>
          <th><?= getWeekName($i); ?></th>
        <?php endfor; ?>
      </tr>
      <?php foreach($dateArray as $key => $date2): ?>
        <?php list($y, $m, $d) = explode('-', $date2); ?>
        <?php if(($key) % 7 == 0): ?>
          <tr>
        <?php endif; ?>
        <td>
          <a href="index.php?date=<?= $date2 ?>"><?= (int)$d ?></a>
        </td>
        <?php if(($key) % 7 == 6): ?>
          </tr>
        <?php endif; ?>
      <?php endforeach; ?>

    </table>

    <br>

    
    <!-- 週ごとのスケジュール -->
    <div class="schedule-block">
      <div>自分</div>
      <table id="schedule-table" border="1">
        <tr>
          <th> </th>
          <?php foreach($weekday as $key => $day): ?>
            <?php list($y, $m, $d) = explode('-', $day); ?>
            <th><?= "$m/".(int)$d."(".getWeekName($key).")" ?></th>
          <?php endforeach; ?>
        </tr>

        <!-- 一週間のスケジュール（一人分） -->
        <?php 
          foreach($weekday as $date3):
            $mySchedule = selectSchedule($userId, $date3);
            var_dump($mySchedule);
        ?>    
            
        <?php endforeach; ?>
            

      </table>
    </div>


            

    <form id="schedule-form" action="" method="post">
      <h4>新規スケジュール登録</h4>
      <input class="input-date" type="date" name="date" value="<?= $date ?>">
      <br>
      <input class="input-title" id="title" type="text" name="title" placeholder="タイトルを追加">
      <br>
      <input class="input-starttime" id="begin" type="time" name="start" value="09:00">
      <span>～</span>
      <input class="input-endtime" id="end" type="time" name="end">
      <br>
      <textarea name="memo" placeholder="説明"></textarea>
      <br>
      <button type="submit" name="submit_add" value="1">登録</button>
    </form>
            


    <script>
      var scheduleForm = document.getElementById('schedule-form');
      var inputStartTime = document.querySelector('.input-starttime');
      var inputEndTime = document.querySelector('.input-endtime');

      scheduleForm.addEventListener('submit', function(e){
        var inputDate = this.querySelector('.input-date');
        var inputTitle = this.querySelector('.input-title');
        var date = new Date(inputDate.value);
        var today = new Date(getToday());

        // スケジュール登録エラーチェック
        if(date.getTime() < today.getTime()){
          window.alert("本日以降の日付を選択してください。");
          e.preventDefault();
        }
        if(inputTitle.value.length === 0){
          window.alert("タイトルを入力してください。");
          e.preventDefault();
        }
        if(inputStartTime.value >= inputEndTime.value){
          window.alert("終了時刻を開始時刻より遅く設定してください。");
          e.preventDefault();  
        }
      });

      inputStartTime.addEventListener('change', function(){
        inputEndTime.value = inputStartTime.value;
      })
      
      
      function getToday(){
        var date = new Date();
        var y = date.getFullYear();
        var m = ("00" + (date.getMonth()+1)).slice(-2);
        var d = ("00" + date.getDate()).slice(-2);
        return (y + "-" + m + "-" + d);
      }
      
    </script>
    
  </body>
</html>