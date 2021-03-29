<?php
/*
 * 本页面用于实现用户对个人借阅记录的查看
 *  */
$params = require 'static/rateParams.php';
$pageType = 'user';

require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
require_once 'blocks/rateBlock.php';
///* 删除和修改 */
if (!empty($_GET['delete'])){
    $sql = "DELETE FROM book_rate WHERE userId = $userId AND id = ".$_GET['delete'];//需要做权限判定
    if ($conn->query($sql)){
        $msg = "删除成功";
        header("Location: userRate.php?id=$userId");//进行跳转, 防止用户手动刷新导致误操作
    }else {
        header("Location: error.php?error=删除评价失败");
        exit;
    }
}
//else if (!empty($_GET['unlost'])){
//    $sql = "UPDATE borrow SET status = 'normal' WHERE id = ".$_GET['unlost'];
//    $conn->query($sql);
//    if ($conn->query($sql)){
//        $msg = "丢失申请已撤销";
//    }else {
//        header("Location: error.php?error=撤销丢失申请失败");
//        exit;
//    }
//}
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
					<?php require_once 'blocks/rateQueryBlock.php';?>
                    <section class="box">
                    	<h3>评价列表</h3>
                        <hr>
                    	<div class="table-wrapper">
                    		<table class="alt">
                    			<tbody>
                                <!-- 获取借阅记录  -->
                            	<?php 
                            	//建立查询语句
                            	$rates = getRates($conn, $userId, $page, $pageSize, $maxPage);
                            	foreach ($rates as $k => $v):
                                    $user = mysqli_fetch_assoc($conn->query("SELECT * FROM reader WHERE id = ".$v["userId"]));
                                    $book = mysqli_fetch_assoc($conn->query("SELECT * FROM book_info WHERE id=".$v["bookId"]));
                                	?>
                                    <div class="media">
                                        <div class="media-left">
                                            <h4 class="media-heading">评价图书: 《<?= $book['name'] ?>》</h4>
                                        </div>
                                        <div class="media-left">
                                            <h4>
                                                <div class="icons">
                                                    <?php for ($i = 0; $i < $v['rating']; $i++): ?>
                                                        <span class="icon fa-star solid" aria-hidden="true"></span>
                                                    <?php endfor;
                                                    for ($i = 0; $i < 5 - $v['rating']; $i++): ?>
                                                        <span class="icon fa-star" aria-hidden="true"></span>
                                                    <?php endfor; ?>
                                                </div>
                                            </h4>
                                        </div>
                                        <div class="media-body">
                                            <h5 class="media-heading">评价内容: <?= $v['content'] ?></h5>
                                        </div>
                                        <div class="media-left">
                                            <h5 class="media-heading">评价时间: <?= $v['created'] ?></h5>
                                        </div>
                                        <?php if ($_GET['id'] == $userId):?>
                                        <div class="media-left">
                                            <h5 class="media-heading" style="color:red">评论状态: <?= $params['rateStatus'][$v['status']] ?></h5>
                                        </div>
                                        <a class="button special fit" href='<?="userRate.php?id=$userId&delete=".$v['id']?>'>删除评论</a>
                                        <?php endif; ?>
                                    </div>
                                    <hr/>
                            	<?php endforeach;?>
                    			</tbody>
                    		</table>
                    	</div>
                    </section>
					<?php $pageUrl = 'userRate.php?' ?>
					<?php require_once 'blocks/pageBlock.php';?>
				</section>
			<?php require_once 'blocks/footer.php';?>
		</div>
		<?php require_once 'blocks/script.php';?>
	</body>
</html>