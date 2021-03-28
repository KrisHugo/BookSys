<?php 
/* 
 * 本页面用于实现管理员对借阅记录的管理
 * 1. 支持对借阅记录的删查
 * 2. 支持对借阅情况的查看(若逾期: 逾期时长, 逾期人, 归还状况; 若正常归还: 归还时间, 归还人)
 *  */
require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
/* 经手人ID */
$handlerId = $_SESSION['account']['user_id'];
/* 确认借书 */
if (!empty($_GET['borrow'])){
    $borrowId = $_GET['borrow'];
    /* 检索是否还有图书 */
    $sql = "SELECT * FROM book_detail d, borrow b WHERE b.id = $borrowId AND b.uniqueCode = d.uniqueCode AND d.status = 'stored'";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0){
            $book = mysqli_fetch_assoc($result);
            //进行操作有效检测
            if(!allowBorrow($conn, $book['id'], $borrowId, $book['uniqueCode'], $handlerId)){
                header("Location: error.php?error=数据库操作借出失败");
                exit;
            }
            header('Location: backend.php?ctr=borrow');
            exit();
        } else{
            $msg = "该书已被借出";
        }
    } else {
        header("Location: error.php?error=数据库操作 $sql 失败");
        exit();
    }
}
/* 当借书请求为false时, 拒绝借书 */
else if (!empty($_GET['unborrow'])){
    $borrowId = $_GET['unborrow'];
    if (!deleteBorrow($conn, $borrowId)){
        header("Location: error.php?error=数据库操作拒绝借书失败");
        exit;
    }
    header("Location: backend.php?ctr=borrow");
    exit;
}
//删除记录
else if(!empty($_GET['delete'])){
    $borrowId = $_GET['delete'];
    if (!deleteBorrow($conn, $borrowId)){
        header("Location: error.php?error=数据库操作删除记录失败");
        exit;
    }
    header("Location: backend.php?ctr=borrow");
    exit;
    
}
//续借
else if(!empty($_GET['continue'])){
    $borrowId = $_GET['continue'];
    $uniqueCode = $_GET['uniqueCode'];
    $sql = "SELECT * FROM book_detail d, borrow b WHERE b.id = $borrowId AND b.uniqueCode = d.uniqueCode AND d.status = 'borrowed'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $book = mysqli_fetch_assoc($result);
        $uniqueCode = $book['uniqueCode'];
        $bookId = $book['id'];
        $userId = $book['userId'];
        if (!continueBorrow($conn, $bookId, $userId, $borrowId, $uniqueCode, $handlerId)){
            header("Location: error.php?error=数据库操作续借失败");
            exit;
        }
        header("Location: backend.php?ctr=borrow");
        exit;
    } else {
        header("Location: error.php?error=数据库操作 $sql 失败");
        exit();
    }
}
//还书
else if(!empty($_GET['return'])){
    $borrowId = $_GET['return'];
    $uniqueCode = $_GET['uniqueCode'];
    $bookId = $_GET['bookId'];
    if(!returnBorrow($conn, $borrowId, $bookId, $uniqueCode, $handlerId)){
        header("Location: error.php?error=数据库操作还书失败");
        exit;
    }
    header("Location: backend.php?ctr=borrow");
    exit;
}
//丢失确认
else if (!empty($_GET['lost'])){
    $borrowId = $_GET['lost'];
    $uniqueCode = $_GET['uniqueCode'];
    $bookId = $_GET['bookId'];
    if(!lostBorrow($conn, $borrowId, $bookId, $uniqueCode, $handlerId)){
        header("Location: error.php?error=数据库操作丢失确认失败");
        exit;
    }
    header("Location: backend.php?ctr=borrow");
    exit;
}
require_once 'blocks/borrowBlock.php';
require_once 'blocks/borrowQueryBlock.php';
?>

<section class="box">
	<h3>借阅列表</h3>
	<div class="table-wrapper">
		<table class="alt">
			<thead>
            	<tr>
            		<th>图书编号</th>
            		<th>图书名称</th>
            		<th>借书时间</th>
            		<th>还书时间</th>
            		<th>用户名</th>
            		<th>借阅状态</th>
            		<th>经手人ID</th>
            		<th>操作</th>
            	</tr>
			</thead>
			<tbody>
            <!-- 获取借阅记录  -->
        	<?php 
        	//建立查询语句
        	//通过输入值查询是否是编号
        	$borrows = getBorrows($conn, $userId, $page, $pageSize, $maxPage);
        	foreach ($borrows as $k => $v): 
            	$book = mysqli_fetch_assoc($conn->query("SELECT * FROM book_info WHERE id=".$v["bookId"]));
            	$reader=mysqli_fetch_assoc($conn->query("SELECT * FROM reader WHERE id=".$v["userId"]));?>
            	<tr>
            		<td><?=$v["uniqueCode"]?></td>
            		<td><?=(!empty($book['name'])?$book['name']:"已移除书本")?></td>
            		<td><?=(!empty($v["borrowDate"]))?$v["borrowDate"] :'请求中'?></td>
            		<td><?=(!empty($v["borrowDate"]))?(!empty($v["returnDate"])? $v["returnDate"] : '至今') : '----'?></td>
            		<td><?=(!empty($reader)?$reader['name']:"已注销用户")?></td>
            		<td><?=empty($v["returnDate"]) && ($v['status'] == 'normal') ? '在借' : $params['bookStatus'][$v['status']] ?></td>
            		<td><?=(empty($v['borrowHandlerId'])?"":$v['borrowHandlerId']).(empty($v['returnHandlerId'])?"":(",".$v['returnHandlerId']))?></td>
            		<td>
            		<?php if(empty($v["borrowDate"]) && $v["status"] == 'quest'): ?>
        			<a href="<?='backend.php?ctr=borrow&borrow='.$v['id']?>">借出</a>
            		<a href="<?='backend.php?ctr=borrow&unborrow='.$v['id']?>">拒绝</a>
            		<?php elseif(empty($v["returnDate"])):?>
                		<?php if ($v['status'] == 'lost'):?>
                		<a href="<?='backend.php?ctr=borrow&lost='.$v["id"].'&bookId='.$v["bookId"].'&uniqueCode='.$v["uniqueCode"].'&userId='.$v["userId"]?>">丢失</a>
                		<?php else :?>
                		<a href="<?='backend.php?ctr=borrow&return='.$v["id"].'&bookId='.$v["bookId"].'&uniqueCode='.$v["uniqueCode"]?>">还书</a>
                		<a href="<?='backend.php?ctr=borrow&continue='.$v["id"].'&bookId='.$v["bookId"].'&uniqueCode='.$v["uniqueCode"]."&userId=".$v['userId']?>">续借</a>
                		<?php endif;?>
            		<?php else:?>
            		<a href="<?='backend.php?ctr=borrow&delete='.$v["id"]?>">删除</a>
            		<?php endif;?>
            		</td>
            	</tr>
        	<?php endforeach;?>
			</tbody>
		</table>
	</div>
</section>
<?php $pageUrl = 'backend.php?ctr=borrow&'?>
<?php require_once 'blocks/pageBlock.php';?>