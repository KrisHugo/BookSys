<?php

/*** 删除所有的session变量..也可用unset($_SESSION[xxx])逐个删除。****/
$_SESSION = array();
/***删除session id.由于session默认是基于cookie的，所以使用setCookie删除包含session id的cookie.***/
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}
// 最后彻底销毁session.
session_destroy();
/* unset($_SESSION)被认为是不可以使用的, 这会导致无法继续创建SESSION */
//跳转回首页
header("Location: ../index.php");
exit("退出成功");
?>