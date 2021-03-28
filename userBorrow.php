<?php
/*
 * 本页面用于实现用户对个人借阅记录的查看
 *  */
$params = require 'static/borrowParams.php';
$pageType = 'user';
require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
/* 丢失申请和撤销丢失申请 */
if (!empty($_GET['lost'])){
    $sql = "UPDATE borrow SET status = 'lost' WHERE id = ".$_GET['lost'];
    if ($conn->query($sql)){
        $msg = "丢失申请已发出";
    }else {
        header("Location: error.php?error=丢失申请失败");
        exit;
    }
} else if (!empty($_GET['unlost'])){
    $sql = "UPDATE borrow SET status = 'normal' WHERE id = ".$_GET['unlost'];
    if ($conn->query($sql)){
        $msg = "丢失申请已撤销";
    }else {
        header("Location: error.php?error=撤销丢失申请失败");
        exit;
    }
}
require_once 'blocks/borrowBlock.php';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>图书管理系统·借阅记录</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/custom.css" />
	</head>
	<body class="is-preload">
		<div id="page-wrapper">
                <!-- Header -->
				<?php require_once 'blocks/pageHeader.php';?>
			    <!-- Main -->	
				<section id="main" class="container">
					<?php require_once 'blocks/borrowQueryBlock.php';?>
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
                                		<th>借阅状态</th>
                                		<th>丢失申请</th>
                                	</tr>
                    			</thead>
                    			<tbody>
                                <!-- 获取借阅记录  -->
                            	<?php 
                            	//建立查询语句
                            	$borrows = getBorrows($conn, $userId, $page, $pageSize, $maxPage);
                            	foreach ($borrows as $k => $v): 
                                    
                                	$book = mysqli_fetch_assoc($conn->query("SELECT * FROM book_info WHERE id=".$v["bookId"]));
                            	   /* 
                            	    * 计算每条记录的借阅状态
                            	    * 已还, 在借, 逾期, 丢失
                            	    * 这些条件先直接全部选出来, 然后通过结果来筛选
                            	    *  */
                                	?>
                                	<tr>
                                		<td><?=$v["uniqueCode"]?></td>
                                		<td><?=(!empty($book['name'])?$book['name']:"已移除书本")?></td>
                                		<td><?=(!empty($v["borrowDate"]))?$v["borrowDate"] :'请求中'?></td>
                                		<td><?=(!empty($v["borrowDate"]))?(!empty($v["returnDate"])? $v["returnDate"] : '至今') : '----'?></td>
                                		<td><?=(empty($v["returnDate"]) && $v['status'] == 'normal') ? '在借' : $params['bookStatus'][$v['status']] ?></td>
                                		<td>
                                            <!-- 功能按钮 -->
                                            <!-- 当该书已被用户借出过，且已经还回过（即有阅读记录了），且未被评价过，则允许用户进行读书笔记/评价的新增
                                             操作 -->
                                            <?php if ($v['returnDate']  && $v['status'] == 'normal' /*需要判定有无被评价*/):?>
                                            <a href="<?= 'userBorrow.php?lost='.$v['id'] ?>">评价</a>
                                                <!-- 当该书已被用户借出过，且已经还回过（即有阅读记录了），但该书已经被评价过了，则允许用户进行 读书笔记/评价的修改 操作 -->
                                            <?php elseif ($v['returnDate']  && $v['status'] == 'normal' /*需要判定有无被评价*/):?>
                                            <a href="<?= 'userBorrow.php?lost='.$v['id'] ?>">修改评价</a>
                                            <!-- 当该书已被用户借出过，但还未还回时，允许用户进行 丢失申报 操作 -->
                                			<?php elseif (empty($v['returnDate'])  && $v['status'] == 'normal'):?>
                                			<a href="<?= 'userBorrow.php?lost='.$v['id'] ?>">丢失</a>
                                            <!-- 当该书已被用户丢失申报了，允许用户进行 撤销丢失申报 操作 -->
                                			<?php elseif (empty($v['returnDate'])  && $v['status'] == 'lost'):?>
                                			<a href="<?='userBorrow.php?unlost='.$v['id']?>">撤消</a>
                                			<?php endif;?>
                                		</td>
                                	</tr>
                            	<?php endforeach;?>
                    			</tbody>
                    		</table>
                    	</div>
                    </section>
					<?php $pageUrl = 'userBorrow.php?' ?>
					<?php require_once 'blocks/pageBlock.php';?>
				</section>
			<?php require_once 'blocks/footer.php';?>
		</div>
		<?php require_once 'blocks/script.php';?>
	</body>
</html>