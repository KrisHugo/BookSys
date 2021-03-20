<?php
require_once '../../app/conn.php';
require_once '../../utils/functions.php';
$dic = [];
foreach ($_POST as $k => $v){
    if (count($id = explode('authority', $k)) > 1){
        $dic[$id[1]] = $v;
    }
}
if (!modifyAuthority($conn, $dic)){
    header("Location:../../error.php?error=进行userUpdate时操作数据库出错.");
    exit;
}
header("Location:../../backend.php?ctr=users");
exit;
?>

