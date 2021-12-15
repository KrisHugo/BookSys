<?php
/*
 * 本页面用于实现系统管理员对个人借阅记录的管理
 *  */
$params = require 'static/rateParams.php';
$pageType = 'admin';

require_once "app/conn.php";//打开数据库连接
require_once 'utils/functions.php';
require_once 'blocks/rateBlock.php';
///* 屏蔽和通过 */
if (!empty($_GET['pass'])){
    $sql = "UPDATE book_rate SET status = 'normal' WHERE id = ".$_GET['pass'];
    if ($conn->query($sql)){
        $msg = "已通过该评论审核";
        header("Location: backend.php?ctr=rate");//进行跳转, 防止用户手动刷新导致误操作
        exit;
    }else {
                header("Location: error.php?error=通过评论失败");
        exit;
    }
} else if (!empty($_GET['blocked'])){
    $sql = "UPDATE book_rate SET status = 'blocked' WHERE id = ".$_GET['blocked'];
    $conn->query($sql);
    if ($conn->query($sql)){
        $msg = "已屏蔽该评论";
        header("Location: backend.php?ctr=rate");//进行跳转, 防止用户手动刷新导致误操作
        exit;
    }else {
        header("Location: error.php?error=屏蔽评论失败");
        exit;
    }
}
?>
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
                            <h5 class="media-heading">评价用户: <?= $user['name'] ?></h5>
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

                        <div class="media-left">
                            <h5 class="media-heading" style="color:red">评论状态: <?= $params['rateStatus'][$v['status']] ?></h5>
                            <?php if($v['status'] == 'verifying') :?>
                                <a class="button special fit" href='<?="backend.php?ctr=rate&pass=".$v['id']?>'>通过评论</a>
                            <?php endif; ?>
                            <?php if($v['status'] != 'blocked') :?>
                                <a class="button fit" href='<?="backend.php?ctr=rate&blocked=".$v['id']?>'>屏蔽评论</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr/>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </section>
<?php $pageUrl = 'userRate.php?' ?>
<?php require_once 'blocks/pageBlock.php';?>