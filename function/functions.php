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
  try{
    $dbh = dbConnect();
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
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
}

function selectWeekSchedule($empId, $week, $h)
{
  try{
    $dbh = dbConnect();
    $scheduleFlag = 0;
    foreach ($week as $date) {
      $sql = "SELECT id, date, title, memo, attendees_id, TIME_FORMAT(start_time, '%k:%i') AS start_time, TIME_FORMAT(end_time, '%k:%i') AS end_time FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND start_time >= ? AND start_time < ? ORDER BY start_time ASC";
      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(1, $empId);
      $stmt->bindValue(2, '%|' . $empId . '%');
      $stmt->bindValue(3, $date);
      $stmt->bindValue(4, substr('00'.$h, -2) . ":00");
      $stmt->bindValue(5, substr('00'.($h+1), -2) . ":00");
      $stmt->execute();
      $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $scheduleList[] = $res;
      if (!empty($res)) $scheduleFlag = 1;
    }
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return !empty($scheduleFlag) ? $scheduleList : "";
}

function selectSchedule($scheduleId)
{
  try{
    $dbh = dbConnect();
    $sql = "SELECT *, TIME_FORMAT(start_time, '%k:%i') AS start_time2, TIME_FORMAT(end_time, '%k:%i') AS end_time2 FROM schedule WHERE id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $scheduleId);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return $res;
}

function updateSchedule($empId, $date, $startTime, $endTime, $title, $memo, $attendeesIdList, $scheduleId)
{
  try{
    $dbh = dbConnect();  
    $sql = "UPDATE schedule SET emp_id = ?, date = ?, start_time = ?, end_time = ?, title = ?, memo = ?, attendees_id = ? WHERE id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->bindValue(2, $date);
    $stmt->bindValue(3, $startTime);
    $stmt->bindValue(4, $endTime);
    $stmt->bindValue(5, $title);
    $stmt->bindValue(6, $memo);
    $stmt->bindValue(7, implode('|', array_unique($attendeesIdList)));
    $stmt->bindValue(8, $scheduleId);
    $stmt->execute();
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
}

function deleteSchedule($scheduleId)
{
  try{
    $dbh = dbConnect();
    $sql = "DELETE FROM schedule WHERE id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $scheduleId);
    $stmt->execute();
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
}


/* 社員情報テーブル(emp)のDB処理 */
function selectEmp($empId)
{
  try{
    $dbh = dbConnect();
    $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE emp.emp_id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return $res;
}

function insertEmp($empId, $empName, $deptId, $password)
{
  try{
    $dbh = dbConnect();
    $sql = "INSERT INTO emp (emp_id, emp_name, dept_id, password) VALUES (?, ?, ?, ?)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->bindValue(2, $empName);
    $stmt->bindValue(3, $deptId);
    $stmt->bindValue(4, password_hash($password, PASSWORD_DEFAULT));
    $res = $stmt->execute();
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return $res;
}

function selectEmpPass($empId)
{
  try{
    $dbh = dbConnect();
    $sql = "SELECT emp_id, password FROM emp WHERE emp_id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $empId);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return $res;
}


/* 部署情報テーブル(dept)のDB処理 */
function selectDept()
{
  try{
    $dbh = dbConnect();
    $sql = "SELECT * from dept";
    $stmt = $dbh->query($sql);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }catch(PDOException $e){
    echo '接続エラー: ' . $e->getMessage();
    exit;    
  }
  $stmt = null;
  $dbh = null;
  return $res;
}

/* ログインフォーム 入力チェック */
function loginErrCheck($empId, $password)
{
  $empId = removeSpace(escape($empId));
  $password = removeSpace(escape($password));

  $error = [];
  if (empty($empId)) $error[] = '社員番号を入力してください。';
  elseif (!is_numeric($empId)) $error[] = '社員番号は数字で入力してください。';
  if (empty($password)) $error[] = 'パスワードを入力してください。';
  elseif (mb_strlen($password) < 8) $error[] = 'パスワードは8文字以上で入力してください。';

  if (empty($error)) {
    $emp = selectEmpPass($empId);
    if ($emp != false && password_verify($password, $emp['password'])) {
      $_SESSION['user_id'] = $emp['emp_id']; // ログイン成功
      header('Location: index.php');
      exit();
    } else $error[] = 'IDかパスワードが間違っています。';
  }
  return $error;
}

/* 新規ユーザー登録フォーム 入力チェック */
function registerErrCheck($empId, $empName, $password, $deptId)
{
  $empId = removeSpace(escape($empId));
  $empName = removeSpace(escape($empName));
  $password = removeSpace(escape($password));
  $deptId = escape($deptId);
  $error = [];

  if (empty($empId)) $error[] = '社員番号を入力してください。';
  elseif (!is_numeric($empId)) $error[] = '社員番号は数字で入力してください。';
  else {
    $res = selectEmp($empId);
    if ($res != false) $error[] = 'この社員番号は既に登録されています。';
  }
  if (empty($password)) $error[] = 'パスワードを入力してください。';
  elseif (mb_strlen($password) < 8) $error[] = 'パスワードは8文字以上を入力してください。';
  if (empty($empName) || is_numeric($empName)) $error[] = '名前を正しく入力してください';
  if (empty($deptId)) $error[] = '所属部署を選択してください。';

  if (empty($error)) {
    $res = insertEmp($empId, $empName, $deptId, $password);
    if ($res) {
      $_SESSION['user_id'] = $empId;
      header('Location: index.php');
      exit;
    } else {
      $error[] = 'エラー：登録できませんでした。';
    }
  }
  return $error;
}

/* 文字列の中の空白を除去 */
function removeSpace($word)
{
  $word = preg_replace('/( |　)/', '', $word);
  return $word;
}

/* エスケープ処理 */
function escape($word)
{
  $res = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
  return $res;
}
