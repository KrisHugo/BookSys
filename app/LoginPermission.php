<?php
// 登录权限获取
/* 若未登陆不允许查看被限定为登陆后查阅的内容 */
if (empty($_SESSION['account']['user_id'])) {
    header("Location: login.php?error=l");
    exit;
}
