<?php
require_once "app/conn.php";
// 删除
if (!empty($_GET['delete'])) {
    $bookId = $_GET['delete'];
    if (!deleteBook($conn, $bookId)){
        header("Location: error.php?error=数据库操作删除图书错误");
        exit;
    }
    header("Location: backend.php?ctr=list");
    exit;
}
$pageType = 'admin';
require_once 'utils/functions.php';
/* 获取分页信息 */
$pageSize = 12;
require_once 'blocks/listBlock.php'; 

require_once 'blocks/listQueryBlock.php';
?>
<section class="box">
	<h3>图书列表</h3>
	<div class="table-wrapper">
		<table class="alt">
			<thead>
				<tr>
					<th>书名</th>
					<th>作者</th>
					<th>类型</th>
					<th>价格</th>
					<th>数量</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
        	<?php
    	$maxPage = 1;
    	$books = getBooks($conn, $pageType,  $page, $pageSize, $maxPage);
        foreach ($books as $k => $v) :
            ?>
        	<tr>
				<td><?=$v["name"]?></td>
				<td><?=$v["author"]?></td>
				<td><?=$categories[$v["category"]]["category"]?></td>
				<td><?=$v["price"]?></td>
				<td><?=$v["count"]?></td>
				<td><a href="book.php?book_id=<?=$v["id"]?>" style="float: left;">详细</a>
					<a href="backend.php?ctr=update&update=<?=$v["id"]?>"
					style="float: left; padding-left: 5px">修改</a> <a
					href="backend.php?ctr=list&delete=<?=$v["id"]?>"
					style="float: right; padding-left: 5px">删除</a></td>
			</tr>
        	<?php endforeach;?>
			</tbody>
		</table>
	</div>
</section>
<?php $pageUrl = 'backend.php?ctr=list&'?>
<?php require_once './blocks/pageBlock.php';?>