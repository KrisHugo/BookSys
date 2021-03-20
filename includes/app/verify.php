<?php 
require_once '../../app/conn.php';
$sql = "UPDATE reader SET readerID = studentID WHERE id = ".$_GET['verify'];
if(!$conn->query($sql)){
    header("Location: ../../error.php?error=数据库操作赋予读书证失败");
}
header("Location:../../backend.php?ctr=users");
exit;
?>