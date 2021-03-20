<?php
/* 
 * 本页面需要完成的功能如下
 * 1. 实现用户信息可视化
 * 2. 实现用户自行还书
 * 3. 实现用户确认还书剩余时间
 *  */
require_once "app/conn.php";
require_once 'utils/functions.php';
if (empty($_GET['id'])){
    header("Location: index.php");
}
if (!empty($_SESSION['account']['user_id'])){
    /* 获取用户信息 */
    $userResult = $conn->query("SELECT * FROM accounts a, reader r WHERE a.id = r.id AND a.id = ".$_GET['id']);
    if($userResult){
        if ($userResult->num_rows > 0){
            $user = mysqli_fetch_assoc($userResult);
        }
        else{
            header("Location: error.php?error=无效ID,访问失败");
            exit;
        }
    }else{
        header("Location: error.php?error=获取用户信息失败");
        exit;
    }
}
else{
    header("Location:login.php?error=l");
}
$colleges = getColleges($conn);
if (!empty($_GET['update'])){
    $msg = "";
    if (empty($_POST['username'])){
        $msg = "请输入名字!";
    }
    else if (empty($_POST['college'])){
        $msg = "请选择学院!";
    }
    else if (empty($_POST['sex'])){
        $msg = "请选择性别!";
    }
    $phonePreg = '/^1[3456789]\d{9}$/';
    /* 检测电话是否合规 */
    if (!empty($_POST['phone']) && !preg_match($phonePreg, $_POST['phone'])){
        $msg = "请使用合规的电话号码!";
    }
    /* 修改密码 */
    $passwordPreg = '/^[a-zA-Z0-9_]{8,16}$/';
    /* 只有在写入confirm之后才会修改密码 */
    if (!empty($_POST['password']) && !empty($_POST['confirm'])){
        if ($_POST['password'] != $_POST['confirm']){
            $msg = "请确认两次密码一致!";
        }
        else {
            if (!preg_match($passwordPreg, $_POST['password'])){
                $msg = "请使用合规的密码!";
            }
        }
    }
    if (empty($msg)){
        $accountSql = "UPDATE accounts SET username = '". $_POST['username']."' ".(!empty($_POST['confirm'])? ",password = '".$_POST['password']."'":"")." WHERE id=".$_GET['id'];
        if(!$conn->query($accountSql)){
            header("Location: error.php?error=更新操作失败");
            exit;
        }
        $readerSql = "UPDATE reader SET sex = '".$_POST['sex']."',"
            ."college = '".$_POST['college']."'"
                .(!empty($_POST['class'])?",class = '".$_POST['class']."'":"")
                .(!empty($_POST['phone'])?",phone = '".$_POST['phone']."'":"")
                ." WHERE id =".$_GET['id'];
        if (!$conn->query($readerSql)){
            header("Location: error.php?error=插入操作失败");
            exit;
        }
        setcookie("user", $_POST['username'], 0,"/");
        header("Location: info.php?id=".$_GET["id"]."&updated=true");
        exit;
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
	<title>图书借阅管理系统</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<link rel="stylesheet" href="assets/css/custom.css" />
</head>
<body class="landing is-preload">
	<div id="page-wrapper">
		<?php require_once './blocks/indexHeader.php';?>
		<!-- Banner -->
			<section id="banner">
				<h2><?=$user['username']?></h2>
				<p>学而知不足</p>
				<ul class="actions special">
					<li><a href="bookList.php" class="button primary">借书</a></li>
				</ul>
			</section>

		<!-- Main -->
			<section id="main" class="container">
				<section class="box">
					<header class="major">
						<h2>个人信息</h2>
						<?php if (!empty($msg)):?>
						<p style="color: red"><?=$msg?></p>
						<?php elseif (!empty($_GET['updated']) && $_GET['updated'] == 'true') :?>
						<p style="color: black">修改成功!</p>
						<?php endif;?>
					</header>
					<span class="image featured"><img src="images/pic06.jpg" alt="" /></span>
					<form action="<?='info.php?id='.$user['id'].'&update=true'?>" method="post">
    					<div class="row gtr-uniform gtr-50">
    						<div class="col-12">
                			<h3>账户信息</h3>
                			</div>
    						<div class="col-6 col-12-mobilep">
    							<label for="username">用户名:</label>
                				<input id="username" type="text" name=username value="<?=$user['username']?>" <?=$user['id'] == $_SESSION['account']['user_id'] ? "required":"readonly"?>/>
    							<label for="account">账号:</label>
                				<input id="account" type="text" name="account" value="<?=$user['account'] ?>"  readonly/>
    							<?php if ($user['id'] == $_SESSION['account']['user_id']):?>
    							<label for="password">密码:</label>
                				<input id="password" type="password" name="password" value="" />
    							<label for="confirm">确认密码:</label>
                				<input id="confirm" type="password" name="confirm" value="" />
                				<?php endif;?>
                			</div>
                			
                			<div class="col-12">
                				<hr>
                				<h3>认证信息</h3>
                    			<ul class="alt">
                    				<li>姓名: <input type="text" name="name" value="<?= $user['name'] ?>" readonly /></li>
                    				<li>借书证: <input type="text" name="readerID" value="<?=!empty($user['readerID'])?$user['readerID']:"暂未授权"?>" readonly/></li>
                    				<li>学生证: <input type="text" name="studentID" value="<?=$user['studentID']?>" readonly /></li>
                    				<li>学院:
                    					<?php if ($user['id'] == $_SESSION['account']['user_id']):?>								
                    					<select name="college" id="college">
  						                	<option value="">--请选择学院--</option>
                                        	<?php foreach ($colleges as $k => $v):?>
                                        	<option value="<?=$v?>" <?= $user['college'] == $v ? "selected": "" ?>><?=$v?></option>
                                        	<?php endforeach;?>
										</select>
										<?php else :?>
										<input type="text" name="college" id="college" value = <?=$user['college']?> readonly />
										<?php endif;?>
									</li>
                    				<li>班级: <input type="text" name="class" value="<?=!empty($user['class'])?$user['class']:""?>" <?=$user['id'] == $_SESSION['account']['user_id'] ? "":"readonly"?>/></li>
                    			</ul>
                			</div>
                			<div class="col-12">
                				<hr>
                				<h3>个人信息</h3>
                			</div>
							<div class="col-6 col-12-mobilep">
								<label for="sex">性别</label>
            					<?php if ($user['id'] == $_SESSION['account']['user_id']):?>
								<select name="sex">
									<option value="">--请选择性别--</option>
									<option value="male" <?= !empty($user['sex'])? $user['sex'] == 'male' ? "selected" : "" :"" ?>>男</option>
									<option value="female" <?= !empty($user['sex'])? $user['sex'] != 'male' ? "selected" : "" :"" ?>>女</option>
								</select>
								<?php else :?>
								<input type="text" name="sex" id="sex" value ="<?=!empty($user['sex']) ? $user['sex'] == 'female' ? '女':'男' :'未填' ?>" readonly />
								<?php endif;?>
    							<label for="phone">电话号码</label>
								<input type="text" name="phone" id="phone" value="<?=!empty($user['phone'])?$user['phone']:''?>" placeholder="电话号码" <?=$user['id'] == $_SESSION['account']['user_id'] ? "":"readonly"?>/>
							</div>
                			<div class="col-12">
                				<hr>
                			</div>
                		</div>
                		<br>
                		<?php if ($_SESSION['account']['user_id'] == $user["id"]):?>
                		<ul class="actions special">
                			<li><button type="submit" class="button special fit">修改</button></li>
                		</ul>
                		<?php endif;?>
            		</form>
				</section>
				
			</section>
		<?php require_once './blocks/footer.php';?>
	</div>
	<?php require_once './blocks/script.php';?>
</body>
</html>
