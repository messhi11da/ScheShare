<?php
require_once(dirname(__FILE__) . '/function.php');


if(isset($_POST['create_form'])){
    if(!empty($_POST['schedule_id'])){
        $schedule = selectSchedule(($_POST['schedule_id']));
    }    
}

?>

<form id="add-form" class="display-elem" action="" method="post" style="display: none;">
  <div class="form-wrapper">
    <div class="main-form">
      <div class="display-header">
        <h4 class="form-title">新規スケジュール登録</h4>

        <button class="delete-btn" type="submit" name="submit_delete" value="1" style="display:none;">削除</button>
        <button class="close-btn" type="button">×</button>
      </div>
      <input class="input-title" id="title" type="text" name="title" placeholder="タイトルを追加">
      <br>
      日時：
      <input class="input-date" type="date" name="date" value="<?= $date ?>">
      <br>
      時刻：
      <input class="input-starttime" id="begin" type="time" name="start">
      <span>～</span>
      <input class="input-endtime" id="end" type="time" name="end">
      <br>
      説明：
      <textarea class="input-memo" name="memo" placeholder="説明"></textarea>
      <br>
      参加者：
      <ul class="attendees-list">
        <li class="attendees-item"><?= $user['emp_name'] . "(" . $user['emp_id'] . ")" ?></li>
      </ul>


      <input class="input-schedule-id" type="hidden" name="schedule_id" value="">
      <button class="add-btn" type="submit" name="submit_add" value="1">登録</button>
    </div>


  </div>
</form>