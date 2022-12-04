<?php
  require_once(dirname(__FILE__).'/function.php');
  

  if(isset($_GET['y']) && isset($_GET['m']) && isset($_GET['d'])){
    list($y, $m, $d) = [escape($_GET['y']), escape($_GET['m']), escape($_GET['d'])];    
  }else{ 
    list($y, $m, $d) = getToday();
  }

  $date = createCalendar($m, $y);


  var_dump($date);

  
/*
  if(isset($_GET['today'])) $today = escape($_GET['today']);
  else $today = $current -> format('d');

  $beginHour = 8; //就業時刻
  $endHour = 22; //退勤時刻

  $userId = 1;

  if(isset($_POST['submit_add'])){
    $title = escape($_POST['title']);
    $beginTime = escape($_POST['date']." ".$_POST['begin']);
    $endTime = escape($_POST['date']." ".$_POST['end']);
    $memo = escape($_POST['memo']);

    
    var_dump($beginTime);
    insertSchedule($userId, $beginTime, $endTime, $title, $memo);
    header("Location:http://localhost/ScheShare/index.php");
    exit;
  }
*/


?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>ScheShare</title>
    <link href="./css/style.css" rel="stylesheet">
  </head>
  
    <h1>ScheShare</h1>
    <a href="index.php?y=<?= $m-1 > 0? $y : $y-1 ?>&m=<?= $m-1 > 0? $m-1 : 12 ?>&d=1">◁</a>
    <span><?= $y ?>年<?= $m ?>月</span>
    <a href="index.php?y=<?= $m+1 <= 12? $y : $y+1 ?>&m=<?= $m+1 <= 12? $m+1 : 1 ?>&d=1">▷</a>


    <!-- 月毎のカレンダー -->
    <table id="month-schedule" border="1">
      <tr>
        <!-- 曜日の表示 -->
        <?php for($i=0; $i<7; $i++): ?>
          <th><?= getWeekName($i); ?></th>
        <?php endfor; ?>
      </tr>
      <tr>
        <!-- 月初セル埋め合わせ -->
        <?php for($i=0; $i<getWeekNum($m, 1, $y); $i++): ?>
          <td></td>
        <?php endfor; ?>
        <!-- 今月セル -->
        <?php for($i=1; checkdate($m, $i, $y); $i++): ?>
          <?php if(getWeekNum($m, $i, $y) === '0'): ?>
            </tr>
            <tr>
          <?php endif; ?>
          <td class="the-day">
            <a href="./index.php?y=<?= $y ?>&m=<?= $m ?>&d=<?= $i ?>"><?= aaa ?></a>
          </td>
        <?php endfor; ?>
        <!-- 月末セル埋め合わせ -->
        <?php for($j=0; $j<6-getWeekNum($m, $i-1, $y); $j++): ?>
          <td></td>
        <?php endfor; ?>
      </tr>
    </table>

    <br>

    
    <!-- 週ごとのスケジュール -->
    <table id="week-schedule" border="1">
      <tr>
        <th> </th>
        <?php $d2 = getThisSunday($m, $d, $y);  // 今週の日曜日 ?>
        <?php for($i=0; $i<7 ;$i++, $d2++): ?>
          <th><?= checkdate($m, $d2, $y) ? $d2."日(".getWeekName($i).")" : " " ?></th>
        <?php endfor; ?>
      </tr>
    </table>

    <!--
      <?php for($i=$beginHour; $i<=$endHour; $i++): ?>
      <tr>
        <td><?= $i ?>:00</td>
        <?php $d = getThisSunday($m, $today, $y);  // 今週の日曜日 ?>
        <?php for($j=0; $j<7 ;$j++): ?>
          <td class="hour-schedule">
            <?php
              $datetime = $y."-".$m."-".$d." ".$i.":00";
              $schedule = selectSchedule($userId, $datetime);
              //cho echedule['title'];
              foreach($schedule as $value){
                echo $value['title'];
                echo "<br>";
              }
              $d++
            ?>
          </td>
        <?php endfor; ?>
      </tr>
      <?php endfor; ?>
    </table>
            -->

    <form action="" method="post">
      <h4>新規スケジュール登録</h4>
      <input type="date" name="date" value="2022-11-27">
      <br>
      <input id="title" type="text" name="title" placeholder="タイトルを追加">
      <br>
      <input id="begin" type="time" name="begin" value="08:00">
      <span>～</span>
      <input id="end" type="time" name="end" value="09:00">
      <br>
      <textarea name="memo" placeholder="説明"></textarea>
      <br>
      <button type="submit" name="submit_add">登録</button>
    </form>
            
  </body>
</html>