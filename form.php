<?php
/* 新規スケジュール登録 */
if (isset($_POST['submit_add'])) {
    insertSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp']);
    header("Location:http://localhost/ScheShare/index.php?date=" . $date);
    exit();
  }
  
  /* スケジュール編集 */
  if (isset($_POST['submit_edit'])) {
    updateSchedule($userId, $_POST['date'], $_POST['start'], $_POST['end'], $_POST['title'], $_POST['memo'], $_POST['checked_emp'], $_POST['schedule_id']);
    header("Location:http://localhost/ScheShare/index.php?date=" . $date);
    exit();
  }
  
  /* スケジュール削除 */
  if (isset($_POST['submit_delete'])) {
    deleteSchedule($_POST['schedule_id']);
    header("Location:http://localhost/ScheShare/index.php?date=" . $date);
    exit();
  }
?>

<!DOCTYPE html>
<html lang="ja">

<body>
    <!-- スケジュール詳細画面 -->
    <div id="schedule-detail" class="display-schedule" style="display: none;">
        <div class="schedule-header">
            <h3>スケジュール詳細</h3>
            <button class="close-btn" type="button">×</button>
        </div>

        <table class="detail-table"></table>

        <div>
            <button class='edit-btn' type='button' data-id='' style="display: none;">編集</button>
        </div>
    </div>

    <!-- スケジュール新規登録＆編集用のフォーム -->
    <form id="schedule-form" class="display-schedule" action="" method="post" style="display: none;">
        <div class="schedule-header">
            <h3 class="form-title">スケジュール編集画面</h3>
            <button class="close-btn" type="button">×</button>
        </div>

        <div style="display: flex;">
            <div>
                <input type="hidden" name="checked_emp[]" value="<?= $user['emp_id'] ?>">

                <table class="form-table">
                </table>

                <input class="input-schedule-id" type="hidden" name="schedule_id" value="">
                <a class="edit-attendee-btn" href="" data-checked="">参加者を編集</a>
                <br>
                <button class="submit-btn" type="submit" name="submit_edit" value="1">更新</button>
                <button class="delete-btn" type="submit" name="submit_delete" value="1" style="display: none;">削除</button>
            </div>

            <!-- フォーム内検索画面 -->
            <div class="search-attendees" style="display: none;">
                <select class="select-dept">
                    <option class="dept-option" value="0">全部署</option>
                    <?php foreach ($deptList as $dept) : ?>
                        <option class="dept-option" value="<?= $dept['dept_id'] ?>"><?= escape($dept['dept_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <br>
                <input class="input-keyword" width="" type="text" placeholder="（例) 社員番号,名前,部署名">
                <button class="search-btn" type="submit" data-checked="0">検索</button>

                <div class="emp-table" style="display: none;">
                </div>
            </div>

        </div>
    </form>

</body>

</html>