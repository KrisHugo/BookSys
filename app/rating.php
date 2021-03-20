<?php
$id = $_POST['ratingId'];
$value = $_POST['ratingNum'];
$content = $_POST['ratingContent'];
$userId = $_SESSION['account']['user_id'];
$created = time();
echo $id;
echo $value;
echo $content;
echo "测试成功";
$sql = "INSERT INTO book_rate (bookId, userId, rating, content, created, modified) VALUES ($id, $userId, $value, $content, $created, $created)";
?>