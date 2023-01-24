<?php
require_once dirname(__FILE__, 2) . '/function/functions.php';
session_start();

if (isset($_POST['displayEmpList'], $_POST['week'])) {
  $displayEmpList = $_POST['displayEmpList'];
  $week = $_POST['week'];

  try {
    $dbh = dbConnect();
    $interval = 0.25; // 0.25h間隔で計算
    for ($h = 0; $h < 24; $h += $interval) {
      foreach ($week as $day => $date) {
        $freeTimeFlag = 1;
        foreach ($displayEmpList as $empId) {

          $sql = "SELECT * FROM schedule WHERE (emp_id = ? OR attendees_id LIKE ?) AND date = ? AND start_time < ? AND end_time > ? ORDER BY start_time ASC";
          $stmt = $dbh->prepare($sql);
          $stmt->bindValue(1, $empId);
          $stmt->bindValue(2, '%|' . $empId . '%');
          $stmt->bindValue(3, $date);
          $stmt->bindValue(4, getTimeFormat($h + $interval));
          $stmt->bindValue(5, getTimeFormat($h));
          $stmt->execute();
          $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if (!empty($res)) {
            $freeTimeFlag = 0;
          }
        }
        if ($freeTimeFlag === 1) {
          $freeTimeList[$day][] = getTimeFormat($h) . "~" . getTimeFormat($h + $interval);
        }
      }
    }

    // 連続する空き時間のフォーマットを結合させる処理
    $maxCount = 0; // $freeTime[$day]の最大要素数
    for ($day2 = 0; $day2 < 7; $day2++) {
      $count = count($freeTimeList[$day2]);
      $i = 0;
      for ($j = 0; $j < $count - 1; $j++) {
        if (substr($freeTimeList[$day2][$i], -5) === substr($freeTimeList[$day2][$i + 1], 0, 5)) {
          $freeTimeList[$day2][$i] = substr($freeTimeList[$day2][$i], 0, 5) . "~" . substr($freeTimeList[$day2][$i + 1], -5);
          array_splice($freeTimeList[$day2], $i + 1, 1);
        } else {
          $i++;
        }
      }
      if ($maxCount < count($freeTimeList[$day2])) {
        $maxCount = count($freeTimeList[$day2]);
      }
    }
  } catch (Exception $e) {
    echo "エラー:" . $e->getMessage();
    exit();
  }

  echo "<tr>";
  foreach ($week as $day => $date) {
    list($y, $m, $d) = explode('-', $date);
    echo "<th>";
    echo (int)$m . "/" . (int)$d . "(" . getWeekName($day) . ")";
    echo "</th>";
  }
  echo "<tr>";

  for ($i = 0; $i < $maxCount; $i++) {
    echo "<tr>";
    foreach ($week as $day => $date) {
      echo "<td>";
      if (isset($freeTimeList[$day][$i])) {
        echo escape($freeTimeList[$day][$i]);
      }
      echo "</td>";
    }
    echo "</tr>";
  }
  $stmt = null;
  $dbh = null;
}

function getTimeFormat($time)
{
  $hour = floor($time);
  $minute = ($time - $hour) * 60;
  return substr('00' . $hour, -2) . ":" . substr('00' . $minute, -2);
}
