<?php
if (empty($_SESSION['account']['user_id'])){
    header("Location: ../login.php?error=l");
    exit;
}
require_once "conn.php";
$id = $_POST['ratingId'];
$value = $_POST['ratingNum'];
$content = $_POST['ratingContent'];
$userId = $_SESSION['account']['user_id'];
$created = date("y-m-d");
echo $id;
echo $value;
echo $content;
echo "测试成功";
$sql = "INSERT INTO book_rate (bookId, userId, rating, content, created, modified) VALUES ($id, $userId, $value, '$content', '$created', '$created')";
if($conn->query($sql)){
    header("Location: ../book.php?book_id=".$id);
    exit;
}
else {
    header("Location: ../error.php?error=数据库请求 $sql 失败");
    exit;
}


?>