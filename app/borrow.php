<?php
if (empty($_SESSION['account']['user_id'])){
    header("Location: ../login.php?error=l");
    exit;
}
require_once "conn.php";
$uniqueCode = $_GET['uniqueCode'];
$bookId = $_GET['bookId'];
$userId = $_SESSION['account']['user_id'];
/* 需要检验有无读书证,以及有没有被禁用, 没有读书证则不能进行借书 */
/* 未来想要继续丰富用户权限的功能的话, 仅仅使用读书证实在有点太简单了, 读书证只用于借阅书籍, 而账户状态可以管理能否参与系统 */
$result = $conn->query("SELECT * FROM reader r, accounts a WHERE r.id = a.id AND a.id = $userId AND r.readerID IS NOT NULL AND a.status = 'access'");
if ($result->num_rows > 0) {
    /* 查找有无该图书 */
    $sql = "SELECT * FROM book_detail WHERE uniqueCode=$uniqueCode AND status = 'stored'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        /* 发送借阅请求 */
        $sql = "INSERT INTO borrow (`bookId`, `userId`, `uniqueCode`, `status`) VALUES ($bookId,$userId,$uniqueCode,'quest')";
        if ($conn->query($sql)){
            header("Location: ../userBorrow.php?id=".$userId);
            exit;
        }
        else {
            header("Location: ../error.php?error=数据库请求 $sql 失败");
            exit;
        }
    }
    else {
        header("Location: ../error.php?error=数据库请求 $sql 失败");
        exit;
    }
}
else{
    header('Location: ../index.php?error=b');
    exit();
}
?>