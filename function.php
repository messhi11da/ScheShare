<?php
$dsn = "mysql:host=localhost;dbname=scheshare;charset=utf8mb4";
$user = "root";
$password = "root";

try {
  $dbh = new PDO($dsn, $user, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'DB接続エラー: ' . $e->getMessage();
  exit;
}

function createCalendar($m, $y)
{
  //  $startDate = calcStartDate($m, $y);
  $startDate = new DateTime(("first day of $y-$m"));
  if ($startDate->format('w') != 0) {
    $firstOffset = $startDate->format('w');
    $startDate->modify("-$firstOffset day");
  }
  //  $endDate = calcEndDate($m, $y);
  $endDate = new DateTime("last day of $y-$m");
  if ($endDate->format('w') != 6) {
    $lastOffset = 6 - $endDate->format('w');
    $endDate->modify("+$lastOffset day");
  }
  while ($startDate <= $endDate) {
    $dateArray[] = $startDate->format('Y-m-d');
    $startDate->modify('+1 day');
  }
  return  $dateArray;
}


function getToday()
{
  $dateObj = new DateTime('today');
  //  $today = $dateObj -> format('Y-n-j');
  $today = $dateObj->format('Y-m-d');
  return $today;
}

function getWeekName($dayNum)
{
  $week = array('日', '月', '火', '水', '木', '金', '土');
  return $week[$dayNum];
}



function getWeek($theDate, $dateArray)
{
  $dateObj = new DateTime($theDate);
  //var_dump($theDate);
  foreach ($dateArray as $key => $date) {
    if ($date === $theDate) {
      $sundayKey = $key - $dateObj->format('w');
    }
  }
  for ($i = 0; $i < 7; $i++) $week[] = $dateArray[$sundayKey + $i];
  return $week;
}



function insertSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList)
{
  global $dbh;
  var_dump(is_array($attendeesIdList) ? implode(',', $attendeesIdList) : 0);
  var_dump(implode(',', $attendeesIdList));
  //exit;
  $sql = "INSERT INTO schedule (emp_id, date, start_time, end_time, title, memo, attendees_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->bindValue(2, $date);
  $stmt->bindValue(3, $startTime);
  $stmt->bindValue(4, $endTime);
  $stmt->bindValue(5, $title);
  $stmt->bindValue(6, $memo);
  $stmt->bindValue(7, is_array($attendeesIdList) ? implode(',', $attendeesIdList) : 0);
  $stmt->execute();
  var_dump($stmt);
  //  exit;
 // header("Location:http://localhost/ScheShare/index.php");
 // exit;
}

function updateSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList, $scheduleId)
{
  global $dbh;
  var_dump($scheduleId);
  // exit;
  $sql = "UPDATE schedule SET emp_id = ?, date = ?, start_time = ?, end_time = ?, title = ?, memo = ?, attendees_id = ? WHERE id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->bindValue(2, $date);
  $stmt->bindValue(3, $startTime);
  $stmt->bindValue(4, $endTime);
  $stmt->bindValue(5, $title);
  $stmt->bindValue(6, $memo);
  $stmt->bindValue(7, implode(',', $attendeesIdList));
  $stmt->bindValue(8, $scheduleId);
  var_dump($stmt);
  // exit;
  $stmt->execute();
  //header("Location:http://localhost/ScheShare/index.php");
  //exit;
}

function deleteSchedule($scheduleId)
{
  global $dbh;
  $sql = "DELETE FROM schedule WHERE id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $scheduleId);
  $stmt->execute();
  //header("Location:http://localhost/ScheShare/index.php");
  //exit;
}


function searchEmp($keyword, $deptId)
{
  global $dbh;
  $sql = "SELECT emp_id, emp_name, dept_id, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE ";

  $keywordArray = preg_split('/( |　)/', $keyword);
  //var_dump($keywordArray);
  for ($i = 0; $i < count($keywordArray); $i++) {
    $placeholder[] = "(emp_id LIKE ? OR emp_name LIKE ? OR dept_name LIKE ?)";
  }
  $sql .= "( " . implode(' AND ', $placeholder) . " )";

  if (!empty($deptId)) $sql .= " AND dept.dept_id = ?";
  $sql .= " ORDER BY emp_id ASC";
  $stmt = $dbh->prepare($sql);
  foreach ($keywordArray as $key => $keyword) {
    $stmt->bindValue(3 * $key + 1, "%$keyword%");
    $stmt->bindValue(3 * $key + 2, "%$keyword%");
    $stmt->bindValue(3 * $key + 3, "%$keyword%");
  }

  if (!empty($deptId)) $stmt->bindValue(3 * $key + 4, $deptId);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function selectAllEmp()
{
  global $dbh;
    $sql = "SELECT emp_id, emp_name, dept_name, emp.dept_id FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id ORDER BY emp_id ASC";
    $stmt = $dbh->query($sql);
  return $stmt -> fetchAll(PDO::FETCH_ASSOC);
}

function selectEmp($empId)
{
  global $dbh;
  $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}


function selectDept()
{
  global $dbh;
  $sql = "SELECT * from dept";
  $stmt = $dbh->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function selectSchedule($empId, $week, $startTime)
{
  global $dbh;
  $flag = 0;
  foreach ($week as $date) {
    $sql = "SELECT * FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND start_time >= ? AND start_time < ? ORDER BY start_time ASC";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->bindValue(2, '%X' . $empId);
    $stmt->bindValue(3, $date);
    $stmt->bindValue(4, getTimeFormat($startTime));
    $stmt->bindValue(5, getTimeFormat($startTime + 1));
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $scheduleList[] = $res;
  
    if (!empty($res)) $flag = 1;
  }
  //exit;
  return $flag === 1 ? $scheduleList : 0;
}



function calcFreeTime($displayEmpList, $week,  $interval)
{
  global $dbh;

  for ($i = 0; $i < 24; $i += $interval) {
    foreach ($week as $key1 => $date) {
      $flag = 0;
      foreach ($displayEmpList as $displayEmp) {

        $sql = "SELECT * FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND start_time < ? AND end_time > ? ORDER BY start_time ASC";

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $displayEmp['emp_id']);
        $stmt->bindValue(2, '%X' . $displayEmp['emp_id']);
        $stmt->bindValue(3, $date);
        $stmt->bindValue(4, getTimeFormat($i + $interval));
        $stmt->bindValue(5, getTimeFormat($i));
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res)) {
       //   $scheduleList[$key2][$key1][$i] = $res; 
          $flag = 1;
        }
      }
      if (empty($flag)) {
     //   var_dump($i);
        //      $freeTimeList[$key][] = $i;
        $freeTimeList[$key1][] = getTimeFormat($i) . "~" . getTimeFormat($i + $interval);
      }
    }
    // var_dump($freeTimeList[0]);
  }

  $max = 0;
  for ($key1 = 0; $key1 < 7; $key1++) {
    $count = count($freeTimeList[$key1]);
var_dump($count);
//exit;
    $i = 0;
    $cnt = 0;
    for($j=0; $j<$count; $j++){
      if (substr($freeTimeList[$key1][$i], -5) === substr($freeTimeList[$key1][$i + 1], 0, 5)) {
        $freeTimeList[$key1][$i] = substr($freeTimeList[$key1][$i], 0, 5) . "~" . substr($freeTimeList[$key1][$i+1], -5);
        
        array_splice($freeTimeList[$key1], $i+1, 1);
        $cnt++;
       // unset($freeTimeList[$key1][$j]);
    }else{
      $i++;
    }
  }
  if($max < ($count - $cnt)){
    $max = $count - $cnt;
  }

  }
 // var_dump($scheduleList[1]);
  //var_dump($freeTimeList);
  var_dump($max);
 // exit;
  return array($freeTimeList, $max);
}


function getTimeFormat($time)
{
  $hour = floor($time);
  $minute = ($time - $hour) * 60;
  return substr('00' . $hour, -2) . ":" . substr('00' . $minute, -2);
}

function fetchAttendees($attendeesId)
{
  $attendeesIdList = explode(',', $attendeesId);
  //var_dump($attendeesIdList);
  foreach ($attendeesIdList as $attendeeId) {
    $attendees[] = selectEmp($attendeeId);
  }
  return $attendees;
}


/* ログイン関係 */
function insertEmp($empId, $deptId, $password){
  global $dbh;
  $sql = "INSERT INTO emp (emp_id, dept_id, password) VALUES (?, ?, ?)";
  $stmt = $dbh -> prepare($sql);
  $stmt -> bindValue(1, $empId);
  $stmt -> bindValue(1, $deptId);
  $stmt -> bindValue(1, password_hash($password, PASSWORD_DEFAULT));
  $res = $stmt -> execute();
  return $res;
}



function loginErrorCheck($empId, $password){
  $error = [];
  $empId2 = preg_replace('/( |　)/', '', $empId);
  $password2 = preg_replace('/( |　)/', '', $password);
  if(empty($empId2)) $error[] = '社員番号を入力してください。';
  if(empty($password2)) $error[] = 'パスワードを入力してください。';
  if(empty($error)){
    if(!is_numeric($empId2)) $error[] = '社員番号は数字で入力してください。';
    if(mb_strlen($password2) < 8) $error[] = 'パスワードは8文字以上を入力してください。';
  }

}


function registerErrorCheck($empId, $empName, $password, $deptId){
  $error[] = loginErrorCheck($empId, $password);
}

function escape($word)
{
  $res = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
  return $res;
}
