<!--编辑图书评价页面-->
<?php
/* 获取评价图书的基本信息
 * 图书ID
 * 获取用于显示给用户的内容
 * */
$bookId = $_GET['book_id'];
$userId = $_SESSION['account']['user_id'];

$bookResult = $conn->query("SELECT name,author FROM book_info WHERE id = " .$bookId);
if ($bookResult && $bookResult->num_rows > 0){
    /*book_info*/
    $book = mysqli_fetch_assoc( $bookResult );
    $bookName = $book['name'];
    $bookAuthor = $book['author'];
}
else {
    //未搜索到图书, id号异常
    header("Location:error.php?error=检索图书ID号异常");
    exit;
}
/*
 * 评分系统
 * */
/*
 * 文本评价内容（使用jquery的editor来解决）
 * */
?>