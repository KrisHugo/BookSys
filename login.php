<?php 

$params = require_once 'static/loginParams.php';
?>
<!DOCTYPE html>
<<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>图书馆借阅系统·登陆</title>
		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<!-- Custom styles for this template -->
		<link rel="stylesheet" href="assets/css/signin.css">
	</head>

	<body class="text-center">
		<form class="form-signin" action="app/login.php" method="post" >
			<img alt="" class="mb-4" height="72" src="images/index.svg" width="72">
			<h1 class="h3 mb-3 font-weight-normal" >请登录</h1>
			<!-- 只在错误发生时才会显示 -->
			<?php if (!empty($_GET['error'])): ?>
			<p style="color: red" ><?=$params['error'][$_GET['error']]?></p>
			<?php endif;?>
			<label class="sr-only">账户</label>
			<input type="text" name="account" class="form-control" placeholder="请输入用户名" required>
			<label class="sr-only">密码</label>
			<input type="password" name="password" class="form-control" placeholder="请输入密码" required>
			<button class="btn btn-lg btn-primary btn-block btnPlus" type="submit">登陆</button>
			<a class="hrefBtn" href="register.php">
				<button class="btn btn-lg btn-primary btn-block btnPlus" type="button">注册</button>
			</a>
			<p class="mt-5 mb-3 text-muted">&copy;KrisHugo  书语——智能图书馆系统</p>
		</form>
	</body>

</html>