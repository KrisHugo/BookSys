<?php
require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
$params = require 'static/borrowParams.php';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pageSize = 12;
$maxPage = 1;
/* 若未登陆不允许查看任何人的借阅记录 */
if (empty($_SESSION['account']['user_id'])){
    header("Location: login.php?error=l");
    exit;
}
else{
    if (empty($_GET['id'])){
        if ($pageType != 'admin'){
            header("Location: error.php?error=未输入必要参数进入页面");
            exit;
        }
        $userId = -1;
    }
    else{
        $userId = $_GET['id'];
    }
}
/* 初始化Session以及设定Session */
if (empty($_SESSION['borrow']["$userId"]['query']) || !empty($_GET['reset'])){
    initiateBorrowQuerySessions($userId);
}
/* 仅在发送了POST请求后才这么做 */
if (isset($_GET['query'])) {
    $_SESSION['borrow']["$userId"]['query'] =  (!empty($_POST['query']) ? $_POST['query'] : '');
    $_SESSION['borrow']["$userId"]['borrowAfter'] = (!empty($_POST['borrowAfter']) ? $_POST['borrowAfter'] : '');
    $_SESSION['borrow']["$userId"]['borrowBefore'] = (!empty($_POST['borrowBefore']) ? $_POST['borrowBefore'] : '');
    $_SESSION['borrow']["$userId"]['status'] = (!empty($_POST['status']) ? $_POST['status'] : '');
}
$query = $_SESSION['borrow']["$userId"]['query'];
$borrowAfter = $_SESSION['borrow']["$userId"]['borrowAfter'];
$borrowBefore = $_SESSION['borrow']["$userId"]['borrowBefore'];
$status = $_SESSION['borrow']["$userId"]['status'];
?>