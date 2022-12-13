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
    //var_dump($theDate);
    foreach($dateArray as $key => $date){
      if($date === $theDate){
        $sundayKey = $key - $theDateObj -> format('w');
      }
    }
    for($i=0; $i<7; $i++) $weekday[] = $dateArray[$sundayKey+$i];
    return $weekday;
  }


//  function changeDateFormat()


  function insertSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList){
    global $dbh;
    var_dump(implode('X', $attendeesIdList));
    //exit;
    $sql = "INSERT INTO schedule (emp_id, date, start_time, end_time, title, memo, attendees_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> bindValue(2, $date);
    $stmt -> bindValue(3, $startTime);
    $stmt -> bindValue(4, $endTime);
    $stmt -> bindValue(5, $title);
    $stmt -> bindValue(6, $memo);
    $stmt -> bindValue(7, implode('X', $attendeesIdList));
    $stmt -> execute();
    var_dump($stmt);
  //  exit;
    header("Location:http://localhost/ScheShare/index.php");
    exit;
  }

  function updateSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList, $scheduleId){
    global $dbh;
    var_dump($scheduleId);
   // exit;
    $sql = "UPDATE schedule SET emp_id = ?, date = ?, start_time = ?, end_time = ?, title = ?, memo = ?, attendees_id = ? WHERE id = ?";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> bindValue(2, $date);
    $stmt -> bindValue(3, $startTime);
    $stmt -> bindValue(4, $endTime);
    $stmt -> bindValue(5, $title);
    $stmt -> bindValue(6, $memo);
    $stmt -> bindValue(7, implode('X', $attendeesIdList));
    $stmt -> bindValue(8, $scheduleId);
    var_dump($stmt);
   // exit;
    $stmt -> execute();
    header("Location:http://localhost/ScheShare/index.php");
  //  exit;
  }


  function searchEmp($keyword, $deptId){
    global $dbh;
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE ";

    $keywordArray = preg_split('/( |　)/', $keyword);
    //var_dump($keywordArray);
    for($i=0; $i<count($keywordArray); $i++){
      $placeholder[] = "(emp_id LIKE ? OR emp_name LIKE ? OR dept_name LIKE ?)";
    }
    $sql .= "( ".implode(' AND ', $placeholder)." )";

    if(!empty($deptId)) $sql .= " AND dept.dept_id = ?"; 
    $sql .= " ORDER BY emp_id ASC";
    $stmt = $dbh -> prepare($sql);
    foreach($keywordArray as $key => $keyword){
      $stmt -> bindValue(3 * $key + 1, "%$keyword%");      
      $stmt -> bindValue(3 * $key + 2, "%$keyword%");      
      $stmt -> bindValue(3 * $key + 3, "%$keyword%");      
    }
    
    if(!empty($deptId)) $stmt -> bindValue(3 * $key + 4, $deptId);
    $stmt -> execute();
    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
  }


  function selectEmp($empId){
    global $dbh;
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id = ?";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> execute();
    return $stmt -> fetch(PDO::FETCH_ASSOC);
  }

  function selectAllEmp($empId){
    global $dbh;
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id != ?";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> execute();
    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
  }
/*  
  function selectAllEmp($empId){
    global $dbh;
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id NOT IN (?)";
    $stmt = $dbh -> prepare($sql);
    $stmt -> bindValue(1, $empId);
    $stmt -> execute();
    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
  }
  */
  function selectDept(){
    global $dbh;
    $sql = "SELECT * from dept";
    $stmt = $dbh -> query($sql);
    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
  }

  function selectSchedule($empId, $weekday, $startTime){
    global $dbh;
    $flag = 0;
    var_dump($startTime);
  
    foreach($weekday as $date){
      $sql = "SELECT * FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND DATE_FORMAT(start_time, '%H') = ? ORDER BY start_time ASC";
      $stmt = $dbh -> prepare($sql);
      $stmt -> bindValue(1, $empId);
      $stmt -> bindValue(2, '%X'.$empId);
      $stmt -> bindValue(3, $date);
      $stmt -> bindValue(4, substr('00'.$startTime, -2));
      $stmt -> execute();
      $res = $stmt -> fetchAll(PDO::FETCH_ASSOC);
      if(!empty($res)) $flag = 1;
      $weekSchedule[] = $res;
    }
    return $flag === 1 ? $weekSchedule : 0;
  }

  function fetchAttendees($attendeesId){
    $attendeesIdList = explode('X', $attendeesId);
  //  var_dump($attendeesIdList);
    foreach($attendeesIdList as $attendeeId){
      $attendee = selectEmp($attendeeId);
      $attendeeName[] = $attendee['emp_name'];
    }
    return implode(", ", $attendeeName);
  }




  function escape($word){
    $res = htmlspecialchars($word, ENT_QUOTES);
    return $res;
  }
  
?>