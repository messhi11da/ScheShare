<?php
require_once(dirname(__FILE__) . '/db_connect.php');
session_start();

if (isset($_POST['dept_id'], $_POST['keyword'])) {
    $deptId = $_POST['dept_id'];
    $keyword = $_POST['keyword'];

    try {
        global $dbh;
        $sql = "SELECT emp_id, emp_name, dept_name FROM emp LEFT OUTER JOIN dept ON emp.dept_id = dept.dept_id WHERE ";

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
    } catch (Exception $e) {
        echo "エラー:" . $e->getMessage();
        exit();
    }
    echo var_dump($_SESSION);
    echo "検索結果：".count($empList)."件";
    echo "<br>";
        echo "<table>";
        foreach($empList as $emp){
            echo "<tr>";
            echo "<td>".$emp["emp_id"]."</td>";
            echo "<td>".$emp["dept_name"]."</td>";
            echo "<td>".$emp["emp_name"]."</td>";
          //  echo "<td><input class='input-check' type='checkbox' name='checked_emp[]' value='".$emp['emp_id']."' <?= in_array($emp['emp_name'], $_SESSION['display_emp']) ? "checked" : ""
            echo "</tr>";
        }
        echo "</table>";
}
