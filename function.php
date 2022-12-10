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
    $startDate = calcStartDate($m, $y);
    $endDate = calcEndDate($m, $y);
    while($startDate <= $endDate){
      $dateArray[] = $startDate -> format('Y-m-d');
      $startDate -> modify('+1 day');
    }
    return  $dateArray;
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
  function calcEndDate($m, $y){
    $lastDate = new DateTime("last day of $y-$m");
    if($lastDate -> format('w') != 6){
      $dateOffset = 6 - $lastDate -> format('w');
      return $lastDate -> modify("+$dateOffset day");
    }else{
      return $lastDate;
    }
  }

  function getToday(){
    $dateObj = new DateTime('today');
  //  $today = $dateObj -> format('Y-n-j');
    $today = $dateObj -> format('Y-m-d');
    return $today;
  }

  function getWeekName($dayNum){
    $week = array('日', '月', '火', '水', '木', '金', '土');
    return $week[$dayNum];    
  }



  function getWeekday($theDate, $dateArray){
    $theDateObj = new DateTime($theDate);
    var_dump($theDate);
    foreach($dateArray as $key => $date){
      if($date === $theDate){
        $sundayKey = $key - $theDateObj -> format('w');
      }
    }
    for($i=0; $i<7; $i++) $weekday[] = $dateArray[$sundayKey+$i];
    return $weekday;
  }

  function insertSchedule($empId, $date, $startTime, $endTime, $title, $memo){
    global $dbh;
    $sql = "INSERT INTO schedule (emp_id, date, start_time, end_time, title, memo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> bindValue(2, $date);
    $stmt -> bindValue(3, $startTime);
    $stmt -> bindValue(4, $endTime);
    $stmt -> bindValue(5, $title);
    $stmt -> bindValue(6, $memo);
    $stmt -> execute();
  }

  function searchEmp($keyword){
    $keywordArray = preg_split('/( |　)/', $keyword);
    $count = count($keywordArray);
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE ";
    for($i=0; $i<$count; $i++){
      $placeholder[] = "(emp_id = ? OR emp_name = ? OR dept_name = ?)";
    }
    $sql .= implode(' AND ', $placeholder);
    global $dbh;
    $stmt = $dbh -> prepare($sql);
    foreach($keywordArray as $key => $keyword){
      $stmt -> bindValue(3 * $key + 1, $keyword);      
      $stmt -> bindValue(3 * $key + 2, $keyword);      
      $stmt -> bindValue(3 * $key + 3, $keyword);      
    }
    $stmt -> execute();
    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
  }

  function selectSchedule($empId, $weekday, $h){
    global $dbh;
    $flag = 0;
    foreach($weekday as $date){
      $sql = "SELECT * FROM schedule WHERE emp_id = ? AND date = ? AND DATE_FORMAT(start_time, '%H') = ? ORDER BY start_time ASC";
      $stmt = $dbh -> prepare($sql);
      $stmt -> bindValue(1, $empId);
      $stmt -> bindValue(2, $date);
      $stmt -> bindValue(3, substr('00'.$h, -2));
      $stmt -> execute();
      $res = $stmt -> fetchAll(PDO::FETCH_ASSOC);
      if(!empty($res)) $flag = 1;
      $weekSchedule[] = $res;
    }
    return $flag === 1 ? $weekSchedule : 0;
  }

  function escape($word){
    $res = htmlspecialchars($word, ENT_QUOTES);
    return $res;
  }
  
?>