<?php
  require_once(dirname(__FILE__).'/function.php');
  
  $empId = 154;

  if(isset($_GET['date'])){
    $date = escape($_GET['date']);
    list($y, $m, $d) = explode('-', $date);   
  }else{ 
    $date = getToday();
    list($y, $m, $d) = explode('-', $date);   
  }

  $dateArray = createCalendar($m, $y);
  $weekday = getWeekday($date, $dateArray);




  if(isset($_POST['submitSearch'])){
    $keyword = escape($_POST['keyword']);
    $checkEmpty = preg_replace('/( |　)/', '' , $keyword);
    if(empty($checkEmpty)){
      header('Location:Location:http://localhost/ScheShare/index.php');
      exit;
    }
    $Employees = selectEmp($keyword);
    
    var_dump($Employees);

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
      <input type="text" name="keyword" placeholder="社員情報">
      <button type="submit" name="submitSearch" value="1">検索</button>
    </form>
    <ul>
      <li></li>
    </ul>


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
      <?php foreach($dateArray as $key => $date): ?>
        <?php list($y, $m, $d) = explode('-', $date); ?>
        <?php if(($key) % 7 == 0): ?>
          <tr>
        <?php endif; ?>
        <td>
          <a href="index.php?date=<?= $date ?>"><?= $d ?></a>
        </td>
        <?php if(($key) % 7 == 6): ?>
          </tr>
        <?php endif; ?>
      <?php endforeach; ?>

    </table>

    <br>

    
    <!-- 週ごとのスケジュール -->
    <table id="schedule-table" border="1">
      <tr>
        <th> </th>
        <?php foreach($weekday as $key => $day): ?>
          <?php list($y, $m, $d) = explode('-', $day); ?>
          <th><?= "$m/$d(".getWeekName($key).")" ?></th>
        <?php endforeach; ?>
      </tr>

    
      <?php for($i=0; $i<=24; $i++): ?>
        <tr>
          <td><?= $i ?>:00</td>
          <?php foreach($weekday as $day): ?>
            <td class="hour-schedule">
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endfor; ?>
    </table>
            

    <form action="" method="post">
      <h4>新規スケジュール登録</h4>
      <input type="date" name="date" value="<?= $date ?>">
      <br>
      <input id="title" type="text" name="title" placeholder="タイトルを追加">
      <br>
      <input id="begin" type="time" name="start">
      <span>～</span>
      <input id="end" type="time" name="end">
      <br>
      <textarea name="memo" placeholder="説明"></textarea>
      <br>
      <button type="submit" name="submit_add" value="1">登録</button>
    </form>
            
  </body>
</html>