<?php
session_start();
require_once(dirname(__FILE__) . '/db_connect.php');
require_once(dirname(__FILE__) . '/function.php');

$user = selectEmp($_SESSION['user_id']);

if (isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $editFlag = $_POST['edit_flag'];
    if (!empty($scheduleId)) {
        try {
            global $dbh;

            $sql = "SELECT *, TIME_FORMAT(start_time, '%k:%i') AS start_time2, TIME_FORMAT(end_time, '%k:%i') AS end_time2 FROM schedule WHERE id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $scheduleId);
            $stmt->execute();
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            $attendeeList = fetchAttendeeList($schedule['attendees_id']); // 登録者と参加者のデータを取得

        } catch (Exception $e) {
            echo "エラー:" . $e->getMessage();
            exit();
        }

        if ($editFlag === "0") {
            // スケジュール詳細画面生成
            
            echo "<tr>";
            echo "<td>タイトル：</td>";
            echo "<td>" . $schedule['title'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>日時：</td>";
            echo "<td>" . $schedule['date'] . " " . $schedule['start_time2'] . "~" . $schedule['end_time2'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>説明：</td>";
            echo "<td>" . $schedule['memo'] . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>参加者：</td>";
            echo "<td>";
            echo "<ul>";
          //  echo var_dump($attendeeList);
            $attendeeFlag = 0;
            foreach ($attendeeList as $attendee) {
                if($attendee['emp_id'] === $_SESSION['user_id']) {$attendeeFlag = 1;}
                echo "<li>" . $attendee["dept_name"] . " " . $attendee["emp_name"] . "(" . $attendee['emp_id'] . ")</li>";
            }
            echo "</ul>";
            echo "<input class='is-attendee' type='hidden' value='".$attendeeFlag."'>";
            echo "</td>";
            echo "</tr>";
            

        } else {
            // スケジュール編集フォーム生成
            echo "<tr>";
            echo "<td>タイトル：</td>";
            echo "<td><input type='text' name='title' value='" . $schedule['title'] . "'></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>日時：</td>";
            echo "<td><input type='date' name='date' value='" . $schedule['date'] . "'></td>";
            echo "</tr>";
            echo "<td>時刻：</td>";
            echo "<td><input type='time' name='start' value='" . $schedule['start_time'] . "'>";
            echo "~";
            echo "<input type='time' name='end' value='" . $schedule['end_time'] . "'></td>";
            echo "<tr>";
            echo "<td>メモ：</td>";
            echo "<td><textarea name='memo' value='" . $schedule['memo'] . "'></textarea></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>参加者：</td>";
            echo "<td>";
            echo "<ul>";
    //          echo var_dump($attendeeList);
            foreach ($attendeeList as $attendee) {
                $checkedList[] = $attendee['emp_id'];
                echo "<li>" . $attendee["dept_name"] . " " . $attendee["emp_name"] . "(" . $attendee['emp_id'] . ")</li>";
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
            echo "<td>".$user['dept_name']." ".$user['emp_name']."(".$user['emp_id'].")</td>";        
            echo "</tr>";        
    } 
}


?>
