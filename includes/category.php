<?php 
require_once "app/conn.php";
if(!empty($_POST['insert'])){
    $category = $_POST['insert'];
    if(!insertCategory($conn, $category)){
        header("Location: error.php?error=数据库操作添加分类出错");
        exit;
    }
    
}//删除分类功能实现
else if(!empty($_GET['delete'])){
    $categoryId = $_GET['delete'];
    if(!deleteCategory($conn, $categoryId)){
        header("Location: error.php?error=数据库操作删除分类出错");
        exit;
    }
} 
else if (!empty($_GET['modify'])){
    $dic = [];
    foreach ($_POST as $k => $v){
        if (count($id = explode('name', $k)) > 1){
            if ($id[1] == -1){
                continue;
            }
            $dic[$id[1]] = $v;
        }
    }
    if (!updateCategories($conn, $dic)){
        header("Location: error.php?error=数据库操作更新分类出错");
        exit;
    }
    
}
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pageSize = 12;
$maxPage = 1;
?>

<!-- 添加分类 -->
<section class="box">
	<h3>添加分类</h3>
	<form action="backend.php?ctr=category" method="post">
		<div class="row gtr-uniform gtr-50">
			<div class="col-9 col-12-mobilep">
				<input type="text" name="insert" value="" placeholder="输入分类名" />
			</div>
			<div class="col-3 col-12-mobilep">
				<input type="submit" value="添加" class="fit" />
			</div>
		</div>
	</form>
</section>
<section class="box">
	<h3>分类列表</h3>
	<div class="table-wrapper">
		<form action="backend.php?ctr=category&modify=true" method="post">
    		<table class="alt">
    			<thead>
                	<tr>
                		<th>编号</th>
                		<th>分类</th>
                		<th>操作</th>
                	</tr>
    			</thead>
    			<tbody>
    				<?php 
                	//获取分类集合
                	$categoryResult = $conn->query("SELECT * FROM category ORDER BY id ASC LIMIT ".(($page - 1)*$pageSize).",".$pageSize);
                	$categories = mysqli_fetch_all( $categoryResult, MYSQLI_ASSOC);
                	$pageResult = $conn->query("SELECT (COUNT(1) / ".$pageSize.") `pages` FROM category");
                	$pageInfo = mysqli_fetch_assoc($pageResult);
                	$maxPage = ceil($pageInfo['pages']);
                	foreach ($categories as $k => $v):
                	?>
    				<tr>
                		<td><?=$v["id"]?></td>
                		<td><input type="text" name="<?='name'.$v['id'] ?>" value="<?=$v["category"]?>"/></td>
                		<?php if ($v["id"] != -1 ): ?>
                		<td>
                		<a class="button special small"  href="<?='backend.php?ctr=category&delete='.$v["id"]?>">删除</a>
                		</td>
                		<?php else :?>
                		<td><button class="button special small" disabled>禁用</button></td>
    					<?php endif;?>
    				</tr>
    				<?php endforeach;?>
    			</tbody>
    		</table>
			<div class="col-12">
        		<ul class="actions special">
        			<li><input type="submit" value="修改" /></li>
        			<li><input class="alt" type="reset" value="重置"/></li>
        		</ul>
			</div>
		</form>
	</div>
</section>
<?php $pageUrl='backend.php?ctr=category&'?>
<?php require_once 'blocks/pageBlock.php';?>