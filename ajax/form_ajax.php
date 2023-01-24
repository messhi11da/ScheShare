<?php
session_start();
require_once(dirname(__FILE__, 2) . '/function/functions.php');

$user = selectEmp($_SESSION['user_id']);

if (isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $formFlag = $_POST['form_flag'];
    if (!empty($scheduleId)) {
        try {
            $dbh = dbConnect();
            $sql = "SELECT *, TIME_FORMAT(start_time, '%k:%i') AS start_time2, TIME_FORMAT(end_time, '%k:%i') AS end_time2 FROM schedule WHERE id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $scheduleId);
            $stmt->execute();
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            $attendeeList = fetchAttendeeList($schedule['attendees_id']);
        } catch (Exception $e) {
            echo "エラー:" . $e->getMessage();
            exit();
        }
        if ($formFlag === "0") {
            // スケジュール詳細画面生成     
            echo "<tr>";
            echo "<td>タイトル：</td>";
            echo "<td>" . escape($schedule['title']) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>日時：</td>";
            echo "<td>" . escape($schedule['date']) . " " . escape($schedule['start_time2']) . "~" . escape($schedule['end_time2']) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>説明：</td>";
            echo "<td>" . escape($schedule['memo']) . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>参加者：</td>";
            echo "<td>";
            echo "<ul>";
            $attendeeFlag = 0; // 編集ボタンを参加者だけに表示するためのフラグ
            foreach ($attendeeList as $attendee) {
                if($attendee['emp_id'] === $_SESSION['user_id']) $attendeeFlag = 1;
                echo "<li>" . escape($attendee["dept_name"]) . " " . escape($attendee["emp_name"]) . "(" . escape($attendee['emp_id']) . ")</li>";
            }
            echo "</ul>";
            echo "<input class='is-attendee' type='hidden' value='".$attendeeFlag."'>";
            echo "</td>";
            echo "</tr>";
        } else {
            // スケジュール編集フォーム生成
            echo "<tr>";
            echo "<td>タイトル：</td>";
            echo "<td><input type='text' name='title' value='" . escape($schedule['title']) . "'></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>日時：</td>";
            echo "<td><input type='date' name='date' value='" . escape($schedule['date']) . "'></td>";
            echo "</tr>";
            echo "<td>時刻：</td>";
            echo "<td><input type='time' name='start' value='" . escape($schedule['start_time']) . "'>";
            echo "~";
            echo "<input type='time' name='end' value='" . escape($schedule['end_time']) . "'></td>";
            echo "<tr>";
            echo "<td>メモ：</td>";
            echo "<td><textarea name='memo'>".escape($schedule['memo'])."</textarea></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>参加者：</td>";
            echo "<td>";
            echo "<ul>";
            foreach ($attendeeList as $attendee) {
                $checkedList[] = $attendee['emp_id'];
                echo "<li>";
                echo escape($attendee["dept_name"]) . " " . escape($attendee["emp_name"]) . "(" . escape($attendee['emp_id']) . ")";
                if($attendee['emp_id'] != $user['emp_id']){
                    echo "<input class='input-attendees' type='hidden' name='checked_emp[]' value='".$attendee['emp_id']."' >";
                }
                echo "</li>";
            }
            echo "</ul>";
            echo "</td>";
            echo "</tr>";
            echo "<input class='checked-list' type='hidden' data-checked='".json_encode($checkedList)."'";
        }
    }else{
            // 新規スケジュール登録フォーム生成
            echo "<tr>";
            echo "<td>タイトル：</td>";
            echo "<td><input type='text' name='title'></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>日時：</td>";
            echo "<td><input type='date' name='date'></td>";
            echo "</tr>";
            echo "<td>時刻：</td>";
            echo "<td><input type='time' name='start'>";
            echo "~";
            echo "<input type='time' name='end'></td>";
            echo "<tr>";
            echo "<td>メモ：</td>";
            echo "<td><textarea name='memo'></textarea></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>参加者：</td>";
            echo "<td>".escape($user['dept_name'])." ".escape($user['emp_name'])."(".escape($user['emp_id']).")</td>";        
            echo "</tr>";        
    } 
    $stmt = null;
    $dbh = null;
}

// フィールドの文字列情報から参加者の情報を抜き出す
function fetchAttendeeList($attendeeIdList)
{
    $attendeeIdArray = explode('|', $attendeeIdList);
    foreach ($attendeeIdArray as $attendeeId) {
        $attendeeList[] = selectEmp($attendeeId);
    }
    return $attendeeList;
}

?>
