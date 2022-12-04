<?php
  $dsn = "mysql:host=localhost;dbname=scheshare;charset=utf8mb4";
  $user = "root";
  $password = "root";
  
  try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }catch(PDOException $e){
    echo 'DB接続エラー: '.$e->getMessage();
    exit;
  }
  
  function createCalendar($m, $y){
    //$date = new DateTime('now');
    $startDate = calcStartDate($m, $y);
    
    $endDate = calcEndDate($m, $y);
 //   $diff = $date -> diff($lastDate); // date - lastdate > 0 → 1 
   // return $diff -> invert;
    
    while($startDate <= $endDate){
//   for($i=0; $i<35; $i++){ // 第一週の日曜から第五週の土曜までの日付を配列化
      $dateAry[] = $startDate -> format('Y-m-d');
      $startDate -> modify('+1 day');
    }
    return  $dateAry;
      
    //}
    
  }

  function calcEndDate($m, $y){
    $lastDate = new DateTime("last day of $y-$m");
    if($lastDate -> format('w') != 6){
      $dateOffset = 6 - $lastDate -> format('w');
      return $lastDate -> modify("+$dateOffset day");
    }else{
      return $lastDate;
    }
  }
  function calcStartDate($m, $y){
    $firstDate = new DateTime("first day of $y-$m");
    if($firstDate -> format('w') != 0){
      $dateOffset = $firstDate -> format('w');
      return $firstDate -> modify("-$dateOffset day");
    }else{
      return $firstDate;
    }
  }

  function getToday(){
    $today = new DateTime('today');
    $y = $today -> format('Y');
    $m = $today -> format('n');
    $d = $today -> format('j');
    return array($y, $m, $d);
  }

  function getWeekName($dayNum){
    $week = array('日', '月', '火', '水', '木', '金', '土');
    return $week[$dayNum];    
  }

  function getYearMonth($dateString){
    $dateObj = new DateTime($dateString);
    $y = $dateObj -> format('Y');
    $m = $dateObj -> format('n');
    return array($y, $m);
  }


  function getWeekNum($m, $d, $y){
    $date_obj = new DateTime("$y-$m-$d");
    return $date_obj -> format('w'); // 日:0 → 土:6
  }

  function getThisSunday($m, $d, $y){
    $thisSunday = $d - getWeekNum($m, $d, $y);
    return $thisSunday;
  }
/*
  function getThisWeek($d){
    for($i=0; $i<7; $i++){
      $thisWeek[] = $d;
      $d++;
    }
    return $thisWeek;
  }

  function insertSchedule($userId, $beginTime, $endTime, $title, $memo){
    global $dbh;
    $sql = "INSERT INTO schedule (user_id, begin_time, end_time, title, memo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $userId);
    $stmt -> bindValue(2, $beginTime);
    $stmt -> bindValue(3, $endTime);
    $stmt -> bindValue(4, $title);
    $stmt -> bindValue(5, $memo);
    $stmt -> execute();
  }

  function selectSchedule($userId, $datetime){
    global $dbh;
    $sql = "SELECT * FROM schedule WHERE user_id = ? && begin_time = ?";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $userId);
    $stmt -> bindValue(2, $datetime);
    $stmt -> execute();
    $res = $stmt -> fetchAll(PDO::FETCH_ASSOC);
    return $res;
  }
*/
  function escape($word){
    $res = htmlspecialchars($word, ENT_QUOTES);
    return $res;
  }
  
?>