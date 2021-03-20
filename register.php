<?php 
require_once 'app/conn.php';
require_once 'utils/functions.php';
$colleges = getColleges($conn);
$params = require_once 'static/loginParams.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>图书馆借阅系统·注册</title>
		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<!-- Custom styles for this template -->
		<link rel="stylesheet" href="assets/css/signin.css">
	</head>

	<body class="text-center">
		<form class="form-signin" action="app/register.php" method="post" >
<!-- 			<img alt="" class="mb-4" height="72" src="images/index.svg" width="72"> -->
			<h1 class="h4 mb-3 font-weight-normal" >注册</h1>
			<!-- 只在错误发生时才会显示 -->
			<?php if (!empty($_GET['error'])): ?>
			<p style="color: red" ><?=$params['error'][$_GET['error']]?></p>
			<?php endif;?>
			<hr>
			<p class="h5 mb-3 font-weight-normal" >账户信息</p>
			<label class="sr-only">账户</label>
			<input type="text" name="account" class="form-control" placeholder="账户名(6-18位英文,数字,下划线)" required>
			<label class="sr-only">密码</label>
			<input type="password" name="password" class="form-control" placeholder="密码(8-16位英文,数字,下划线)" required>
			<label class="sr-only">确认密码</label>
			<input type="password" name="confirm" class="form-control" placeholder="确认密码" required>
			<div>
			<p class="h5 mb-3 font-weight-normal" >系统认证信息</p>
			<label class="sr-only">认证姓名</label>
			<input type="text" name="name" class="form-control" placeholder="姓名(2-5个中文)" required>
			<label class="sr-only">认证学号</label>
			<input type="text" name="studentID" class="form-control" placeholder="请选择学号(12位数字)" required>
			<label class="sr-only">认证学院</label>
			<select name="college" class="form-control" style="padding:5px;height: calc(2.25rem + 8px)">
              <option value="" selected>--请选择学院--</option>
              <?php foreach ($colleges as $k => $v):?>
              <option value="<?=$v?>" <?=(!empty($college) ? ( $college == $v ? "selected": "") : "") ?>><?=$v?></option>
              <?php endforeach;?>
            </select>
			<label class="sr-only">电话号码</label>
			<input type="text" name="phone" class="form-control" placeholder="电话号码(11位数字, 可选)">
            </div>
			<hr>
			<button class="btn btn-lg btn-primary btn-block btnPlus" type="submit">注册</button>
			<a class="btn btn-lg btn-block btn-info btnPlus" href="login.php">返回登陆</a>
			<p class="mt-5 mb-3 text-muted">&copy; 作业人: 黄文翔 201741402221   项目: 图书借阅管理系统</p>
		</form>
	</body>

</html>