<?php
require_once(dirname(__FILE__) . '/db_connect.php');


/* カレンダー作成関係 */
function createCalendar($m, $y)
{
  $startDate = new DateTime(("first day of $y-$m"));
  if ($startDate->format('w') != 0) {
    $firstOffset = $startDate->format('w');
    $startDate->modify("-$firstOffset day");
  }
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


/* スケジュールテーブル(schedule)のDB処理 */
function insertSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList)
{
  global $dbh;

  $sql = "INSERT INTO schedule (emp_id, date, start_time, end_time, title, memo, attendees_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->bindValue(2, $date);
  $stmt->bindValue(3, $startTime);
  $stmt->bindValue(4, $endTime);
  $stmt->bindValue(5, $title);
  $stmt->bindValue(6, $memo);
  $stmt->bindValue(7, implode('|', $attendeesIdList));
  $stmt->execute();
}

function selectWeekSchedule($empId, $week, $h)
{
  global $dbh;
  $scheduleFlag = 0;
  foreach ($week as $date) {
    $sql = "SELECT id, date, title, memo, attendees_id, TIME_FORMAT(start_time, '%k:%i') AS start_time, TIME_FORMAT(end_time, '%k:%i') AS end_time FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND start_time >= ? AND start_time < ? ORDER BY start_time ASC";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->bindValue(2, '%|' . $empId.'%');
    $stmt->bindValue(3, $date);
    $stmt->bindValue(4, substr('00' . $h, -2).":00");
    $stmt->bindValue(5, substr('00' . $h+1, -2).":00");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $scheduleList[] = $res;
  
    if (!empty($res)) $scheduleFlag = 1;
  }
  return $scheduleFlag === 1 ? $scheduleList : 0;
}

function selectSchedule($scheduleId){
  global $dbh;

  $sql = "SELECT *, TIME_FORMAT(start_time, '%k:%i') AS start_time2, TIME_FORMAT(end_time, '%k:%i') AS end_time2 FROM schedule WHERE id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $scheduleId);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 文字列情報から参加者の情報を抜き出す
function fetchAttendeeList($attendeeIdList)
{
    $attendeeIdArray = explode('|', $attendeeIdList);
    foreach ($attendeeIdArray as $attendeeId) {
        $attendeeList[] = selectEmp($attendeeId);
    }
    return $attendeeList;
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
  $stmt->bindValue(7, implode('|', $attendeesIdList));
  $stmt->bindValue(8, $scheduleId);
  $stmt->execute();
}

function deleteSchedule($scheduleId)
{
  global $dbh;
  $sql = "DELETE FROM schedule WHERE id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $scheduleId);
  $stmt->execute();
}


/* 社員情報テーブル(emp)のDB処理 */
function selectEmp($empId)
{
  global $dbh;
  $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function insertEmp($empId, $empName, $deptId, $password){
  global $dbh;
  $sql = "INSERT INTO emp (emp_id, emp_name, dept_id, password) VALUES (?, ?, ?, ?)";
  $stmt = $dbh -> prepare($sql);
  $stmt -> bindValue(1, $empId);
  $stmt -> bindValue(2, $empName);
  $stmt -> bindValue(3, $deptId);
  $stmt -> bindValue(4, password_hash($password, PASSWORD_DEFAULT));
  var_dump($stmt);
 // exit;
  $res = $stmt -> execute();
  return $res;
}

function selectEmpPass($empId){
  global $dbh;
  $sql = "SELECT emp_id, password FROM emp WHERE emp_id = ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $empId);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}


/* 部署情報テーブル(dept)のDB処理 */
function selectDept()
{
  global $dbh;
  $sql = "SELECT * from dept";
  $stmt = $dbh->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* エスケープ処理 */
function escape($word)
{
  $res = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
  return $res;
}

?>