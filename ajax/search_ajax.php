<?php
require_once(dirname(__FILE__, 2) . '/function/functions.php');
session_start();

if (isset($_POST['dept_id'], $_POST['keyword'])) {
    $deptId = $_POST['dept_id'];
    $keyword = $_POST['keyword'];
    $checkedList = $_POST['checked_list'];

    try {
        global $dbh;
        $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE ";

        $emptyCheck = removeSpace($keyword);
        if(!empty($emptyCheck)){
            $keywordArray = preg_split('/( |　)/', $keyword);
            //var_dump($keywordArray);
            for ($i = 0; $i < count($keywordArray); $i++) {
                $placeholder[] = "(emp_id LIKE ? OR emp_name LIKE ? OR dept_name LIKE ?)";
            }
            $sql .= "( " . implode(' AND ', $placeholder) . " )";
    
            if (!empty($deptId)) $sql .= " AND dept.dept_id = ?";
            $sql .= " ORDER BY emp_id ASC";
            $stmt = $dbh->prepare($sql);
            foreach ($keywordArray as $key => $word) {
                $stmt->bindValue(3 * $key + 1, "%$word%");
                $stmt->bindValue(3 * $key + 2, "%$word%");
                $stmt->bindValue(3 * $key + 3, "%$word%");
            }
    
            if (!empty($deptId)) $stmt->bindValue(3 * $key + 4, $deptId);
            $stmt->execute();
            $empList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $empList = [];
        }
    } catch (Exception $e) {
        echo "エラー:" . $e->getMessage();
        exit();
    }
  //  echo var_dump($_SESSION);
    echo "検索結果：".count($empList)."件";
    echo "<br>";
        echo "<table align='center'>";
        foreach($empList as $emp){
            echo "<tr>";
            echo "<td>".escape($emp["emp_id"])."</td>";
            echo "<td>".escape($emp["dept_name"])."</td>";
            echo "<td>".escape($emp["emp_name"])."</td>";
            if($emp['emp_id'] === $_SESSION['user_id']) echo "<td></td>"; // ログインユーザーにチェックボックスは表示しない
            else echo "<td><input class='input-check' type='checkbox' name='checked_emp[]' value='".$emp['emp_id']."' ".isChecked($emp['emp_id'], $checkedList)."></td>";
            echo "</tr>";
        }
        echo "</table>";
}

function isChecked($empId, $checkedList){
   // echo var_dump($checkedList);
   if($checkedList === "0") return "";
   $checkedFlag = 0;
    foreach($checkedList as $checkedEmp){
        if($checkedEmp === $empId){
            $checkedFlag = 1;
        }
    }
    return $checkedFlag === 1 ? "checked" : "";
}

?>
