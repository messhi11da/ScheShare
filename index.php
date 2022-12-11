<?php
  require_once(dirname(__FILE__).'/function.php');
  

  $empId = '154';

  $today = getToday();
  if(isset($_GET['date'])){
    $date = escape($_GET['date']);
    list($y, $m, $d) = explode('-', $date);   
  }else{ 
    $date = $today;
    list($y, $m, $d) = explode('-', $date);   
  }

  $deptList = selectDept();
  //var_dump($deptList);
  $dateArray = createCalendar($m, $y);
 // var_dump($date);
 // echo "<br>";
 // var_dump($dateArray);
  $weekday = getWeekday($date, $dateArray);

  
  $selectedDeptId = '0';
  //var_dump($selectedDeptId);
  if(isset($_POST['submit_search'])){
    $keyword = escape($_POST['keyword']);
    $selectedDeptId = $_POST['dept_id'];
    var_dump($selectedDeptId);
    //  $keywordCheck = preg_replace('/( |　)/', '', $keyword);
    //  var_dump($keyword);
    var_dump($_POST['dept_id']);
    $searchedEmpList = searchEmp($keyword, $selectedDeptId);
  }


  var_dump($_POST);
  $displayEmpList[] = selectEmp($empId);
  
  if(isset($_POST['submit_checked'])){
    $keyword = $_POST['keyword'];
    $selectedDeptId = $_POST['dept_id'];
    $searchedEmpList = searchEmp($keyword, $selectedDeptId);
    $checkedEmpIdList = $_POST['checked_emp'];
    foreach($checkedEmpIdList as $checkedEmpId){
      $displayEmpList[] = selectEmp($checkedEmpId);
    }
    var_dump($displayEmpList);
  }


  if(isset($_POST['submit_add'])){
    $title = escape($_POST['title']);
    $date = $_POST['date'];
    $startTime = $_POST['start'];
    $endTime = $_POST['end'];
    $memo = escape($_POST['memo']);
    if(empty($title)) $error[] = "タイトルを入力してください。";
    if($startTime >= $endTime) $error[] = "終了時刻を開始時刻より遅く設定してください"; 
    if(empty($error)){
      insertSchedule($empId, $date, $startTime, $endTime, $title, $memo);
      header("Location:http://localhost/ScheShare/index.php");
      exit;
    }
  }



?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>ScheShare</title>
    <link href="./css/style.css" rel="stylesheet">
  </head>
  
    <h1>ScheShare</h1>

    <form action="" method="post">
      <select name="dept_id">
        <option value="0">全体</option>
        <?php foreach($deptList as $dept): ?>
          <option value="<?= $dept['dept_id'] ?>" <?= $dept['dept_id'] ===  $selectedDeptId ? "selected" : "" ?>><?= $dept['dept_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="keyword" value="<?= isset($keyword) ? $keyword : '' ?>" placeholder="社員を探す">
      <button type="submit" name="submit_search" value="1">検索</button>
    </form>

    <?php if(isset($searchedEmpList)): ?>
      <p>検索結果: <?= count($searchedEmpList); ?>件</p>
      <?php if(count($searchedEmpList) > 0): ?>
        <form action="" method="post">
          <input name="dept_id" value="<?= $selectedDeptId ?>" type="hidden">
          <input name="keyword" value="<?= $keyword ?>" type="hidden">
          <table>
            <tr>
              <th>社員番号</th>
              <th>部署</th>
              <th>名前</th>
              <th>
                <button id="display-btn" type="submit" name="submit_checked" value="1">スケジュール表示</button>
              </th>
            </tr>
            <?php foreach($searchedEmpList as $key => $emp): ?>
              <tr>
                <td><?= $emp['emp_id'] ?></td>
                <td><?= $emp['dept_name'] ?></td>
                <td><?= $emp['emp_name'] ?></td>
                <td>
                  <?php if($emp['emp_id'] != $empId): ?>
                    <input type='checkbox' name="checked_emp[]" value="<?= $emp['emp_id'] ?>" <?= in_array($emp['emp_id'], $checkedEmpIdList)? 'checked' : '' ?>>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </form>
      <?php endif; ?>
    <?php endif; ?>
    <table></table>


    <a href="index.php?date=<?= $m-1 > 0? $y."-".($m-1)."-1" : ($y-1)."-12-1" ?>">◁</a>
    <span><?= $y ?>年<?= $m ?>月</span>
    <a href="index.php?date=<?= $m+1 <= 12? $y."-".($m+1)."-1" : ($y+1)."-1-1" ?>">▷</a>
    <!-- 月毎のカレンダー -->
    <table id="calendar-table" border="1">
      <tr>
        <!-- 曜日の表示 -->
        <?php for($i=0; $i<7; $i++): ?>
          <th><?= getWeekName($i); ?></th>
        <?php endfor; ?>
      </tr>
      <?php foreach($dateArray as $key => $date2): ?>
        <?php list($y, $m, $d) = explode('-', $date2); ?>
        <?php if(($key) % 7 == 0): ?>
          <tr>
        <?php endif; ?>
        <td>
          <a href="index.php?date=<?= $date2 ?>"><?= (int)$d ?></a>
        </td>
        <?php if(($key) % 7 == 6): ?>
          </tr>
        <?php endif; ?>
      <?php endforeach; ?>

    </table>

    <br>

    
    <!-- 週ごとのスケジュール -->
    <div class="schedule-content">
      <table id="schedule-table" border="1">
        <tr>
          <th></th>
          <?php foreach($weekday as $key => $date3): ?>
            <?php list($y, $m, $d) = explode('-', $date3); ?>
            <th><?= "$m/".(int)$d."(".getWeekName($key).")" ?></th>
          <?php endforeach; ?>
        </tr>

        <!-- 一週間のスケジュール（一人分） -->
  
        
        <?php 
          foreach($displayEmpList as $displayEmp):
            $hourMemory = 0;
            for($hour=0; $hour<24; $hour++):
              $weekSchedule = selectSchedule($displayEmp['emp_id'], $weekday, $hour);
              if(!empty($weekSchedule)): // 一週間の中でスケジュールが入っている時刻リストを取得  
                $hourDiff = $hour - $hourMemory; // 前回スケジュールがあった時刻(hour)との差
        ?>
                <tr>
                  <?php if($hourMemory === 0): ?>
                    <td rowspan="24">
                      <?= $displayEmp['emp_id'] ?>
                      <br>
                      <?= $displayEmp['emp_name'] ?>
                      <br>
                      <?= $displayEmp['dept_name'] ?>

                    </td>
                  <?php endif; ?>

                  <?php for($col=0; $col<7; $col++): ?>
                    <td rowspan="<?= $hourDiff ?>"><?= $hourDiff ?></td>
                  <?php endfor; ?>
                </tr>
                <?php for($row=0; $row<($hourDiff-1); $row++): ?>
                  <tr></tr>
                <?php endfor; ?>
    

                <tr>
                  <?php for($col=0; $col<7; $col++): ?>
                    <td>
                      <ul>
                        <?php foreach($weekSchedule[$col] as $dateSchedule): ?>
                          <li>
                            <?= substr($dateSchedule['start_time'], 0, 5); ?>~
                            <?= substr($dateSchedule['end_time'], 0, 5); ?>
                            <?= $dateSchedule['title'] ?>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </td>
                  <?php endfor; ?>   
                </tr>
              <?php $hourMemory = $hour + 1; ?>
            <?php endif; ?>
          <?php endfor; ?> 

          <?php if($hourMemory < 24): ?>
            <tr>
              <?php if($hourMemory === 0): ?>
                
                <td rowspan="24">
                  <?= $displayEmp['emp_id'] ?>
                  <br>
                  <?= $displayEmp['emp_name'] ?>
                  <br>
                  <?= $displayEmp['dept_name'] ?>
                </td>
              <?php endif; ?>
              <?php for($col=0; $col<7; $col++): ?>
                <td rowspan="<?= 24-$hourMemory ?>"><?= 24-$hourMemory ?></td>
              <?php endfor; ?>
            </tr>
            <?php for($row=0; $row<(23-$hourMemory); $row++): ?>
              <tr></tr>
            <?php endfor; ?>
          <?php endif; ?>
        <?php endforeach; ?>







<!--




        <?php 
          foreach($checkedEmpList as $checkedEmp):
            for($h=0; $h<24; $h++):
              $weekSchedule = selectSchedule($checkedEmp, $weekday, $h);
        ?>
            <tr>
              <?php if($h === 0): ?>
                <td rowspan="24"><?= $checkedEmp['emp_name'] ?></td>
              <?php endif; ?>
              <?php if(!empty($weekSchedule)): ?> 
                <?php foreach($weekSchedule as $dateSchedule): ?>
                  <td>
                    <ul>
                      <?php foreach($dateSchedule as $schedule): ?>
                        <li>
                          <?= substr($schedule['start_time'], 0, 5); ?>~<?= substr($schedule['end_time'], 0, 5); ?> <?= $schedule['title'] ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </td>
                <?php endforeach; ?>
              <?php endif; ?>    
            </tr>
          <?php 
            endfor;
          endforeach;
          ?>
          -->
      </table>
      <div>
          <table border="1">
            <tr>
              <td rowspan="5">5</td>
              <td rowspan="1">1</td>
              <td rowspan="1">1</td>
              <td rowspan="1">1</td>
              <td rowspan="1">1</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>

          </table>
      </div>
    </div>



    <form id="schedule-form" action="" method="post">
      <h4>新規スケジュール登録</h4>
      <input class="input-date" type="date" name="date" value="<?= $date ?>">
      <br>
      <input class="input-title" id="title" type="text" name="title" placeholder="タイトルを追加">
      <br>
      <input class="input-starttime" id="begin" type="time" name="start" value="09:00">
      <span>～</span>
      <input class="input-endtime" id="end" type="time" name="end">
      <br>
      <textarea name="memo" placeholder="説明"></textarea>
      <br>

      <button type="submit" name="submit_add" value="1">登録</button>
    </form>
            


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script>
      var scheduleForm = document.getElementById('schedule-form');
      var inputStartTime = document.querySelector('.input-starttime');
      var inputEndTime = document.querySelector('.input-endtime');


      
      var checkEmp = document.querySelectorAll('.check_emp');
      var displayBtn = document.getElementById('display-btn');
      console.log(checkEmp);
      displayBtn.addEventListener('click', function(){
        var checkedEmpList = [];
        for(var elem of checkEmp){
          console.log(elem);
          if(elem.checked) checkedEmpList.push(elem.value);
        }
        console.log(checkedEmpList);
        $.get('index.php', 
          {key: "value1"});
      });


      scheduleForm.addEventListener('submit', function(e){
        var inputDate = this.querySelector('.input-date');
        var inputTitle = this.querySelector('.input-title');
        var date = new Date(inputDate.value);
        var today = new Date(getToday());

        // スケジュール登録エラーチェック
        if(date.getTime() < today.getTime()){
          window.alert("本日以降の日付を選択してください。");
          e.preventDefault();
        }
        if(inputTitle.value.length === 0){
          window.alert("タイトルを入力してください。");
          e.preventDefault();
        }
        if(inputStartTime.value >= inputEndTime.value){
          window.alert("終了時刻を開始時刻より遅く設定してください。");
          e.preventDefault();  
        }
      });

      inputStartTime.addEventListener('change', function(){
        inputEndTime.value = inputStartTime.value;
      })
      
      
      function getToday(){
        var date = new Date();
        var y = date.getFullYear();
        var m = ("00" + (date.getMonth()+1)).slice(-2);
        var d = ("00" + date.getDate()).slice(-2);
        return (y + "-" + m + "-" + d);
      }
      
    </script>
    
  </body>
</html>