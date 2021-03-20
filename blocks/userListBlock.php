<a href="#" class="icon solid fa-angle-down"><?=$_SESSION['account']['user']?></a>
<ul>
	<li><a href="<?='info.php?id='.$_SESSION['account']['user_id']?>">个人信息</a></li>
	<li><a href="<?='userBorrow.php?id='.$_SESSION['account']['user_id']?>">我的借阅</a></li>
	<?php if ($pageType=='user' && $_SESSION['account']['user_authority'] == 'admin'):?>
	<li><a href="backend.php">转入后台</a>
	<?php elseif ($pageType=='admin'):?>
	<li><a href="index.php">转入前台</a>
	<?php endif;?>
	<li><a href="app/logout.php">登出</a></li>
</ul>