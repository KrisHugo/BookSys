<?php
//插入操作
if (!empty($_POST['add'])) {
    require_once "../../app/conn.php";
    require_once '../../utils/functions.php';
    $name = $_POST['name'];
    $press = $_POST['press'];
    $press_time = $_POST['press_time'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $ISBN = $_POST['ISBN'];
    $desc = $_POST['desc'];
    $category = $_POST['category'];
    $count = $_POST['count'];
    $last = $_POST['last'];
    if(!addBook($conn, $name, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count, $last)){
        header("Location: ../../error.php?error=数据库操作新增图书失败");
        exit;
    }
	header("Location: ../../backend.php");
	exit;
}
?>