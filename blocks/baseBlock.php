<h1><a href="index.php">图书借阅系统</a></h1>
<nav id="nav">
	<ul>
		<li><a href="<?=$pageType=='admin'?'backend.php':'index.php'?>">首页</a></li>
		<?php if ($pageType=='admin'):?>
		<li>
			<a href="#" class="icon solid fa-angle-down">管理图书</a>
			<ul>
				<li><a href="backend.php?ctr=list">图书列表</a></li>
				<li><a href="backend.php?ctr=add">添加图书</a></li>
				<li><a href="backend.php?ctr=borrow">借阅管理</a></li>
                <li><a href="backend.php?ctr=rate">评价管理</a></li>
				<li><a href="backend.php?ctr=users">用户管理</a></li>
				<li><a href="backend.php?ctr=category">分类管理</a></li>
				<?php if($command == "update"):?>
				<li><a href="<?='backend.php?ctr=update&update='.$_GET['update']?>">修改图书</a></li>
				<?php endif;?>
			</ul>
		</li>
		<?php else :?>
		<li><a href="bookList.php">借阅图书</a></li>
		<?php endif;?>
		<?php if (!isset($_SESSION['account']['user_id'])): ?>
		<li><a href="login.php" class="button">请登录</a></li>
		<?php else: ?>
		<li>
			<?php require_once 'userListBlock.php';?>
		</li>
		<?php endif;?>
	</ul>
</nav>