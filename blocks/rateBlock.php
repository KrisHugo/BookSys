<?php
require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
$params = require 'static/rateParams.php';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pageSize = 12;
$maxPage = 1;
require_once "app/LoginPermission.php";
$userId = require_once "app/UserUniqueCodeCheck&Get.php";
configQuerySession('rate', $userId, $query, $after, $before, $status);
?>