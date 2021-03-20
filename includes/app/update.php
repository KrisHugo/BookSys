<?php
if(!empty($_POST['update'])){
    if($_POST['count'] >= $_POST['last']){
        require_once "../../app/conn.php";
        require_once '../../utils/functions.php';
        $bookId = $_POST['update'];
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
        //获取需要删除的书目
        $deletes = [];
        foreach ($_POST as $k => $v){
            if (count($id = explode('delete', $k)) > 1)
                $deletes[] = $id[1];
        }
        if (!updateBook($conn, $name, $bookId, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count, $last, $deletes)){
            header("Location:../../error.php?error=数据库请求更新书本出错.");
            exit;
        }
        header("Location: ../../backend.php?updated=true");
        exit;
    }
    //不允许修改的书本数量小于已经借出去的书本数量
    else {
        header("Location: ../../backend.php?ctr=update&error=c&update=".$_POST['update']);
        exit;
    }
}
?>