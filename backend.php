<?php
if(empty($_SESSION['account']['user_id']) ||  $_SESSION['account']['user_authority'] == 'user'){
    header("Location: login.php?error=l");
    exit;
}
$pageType = 'admin';
require "app/conn.php";
$command = !empty($_GET['ctr']) ? $_GET['ctr'] : 'list';
$url = "./includes/app/". $command .".php";
require_once './utils/functions.php';
$params = require_once './static/backendParams.php';
/* 修改参数 */
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
			<?php require_once './blocks/backendHeader.php';?>
            <!-- Banner -->
            <section id="banner">
            	<h2>图书借阅管理系统·<?=$params['pageName'][$command]?></h2>
            	<p>东莞理工学院</p>
            </section>
			<!-- Main -->
			<!-- 此处用于添加被选中的功能选单 -->
				<section id="main" class="container">
					<div class="row">
    					<div class="col-12">
    					<?php switch ($command){
        					        case "add": require_once "./includes/add.php"; break;
        					        case "update": require_once "./includes/update.php"; break;
        					        case "category": require_once "./includes/category.php"; break;
        					        case "borrow": require_once "./includes/borrow.php"; break;
                                    case "rate": require_once "./includes/rate.php"; break;
        					        case "users": require_once "./includes/users.php"; break;
        					        case "list": default: require_once "./includes/list.php"; break;
        					    }?>
    					</div>
					</div>
				</section>
			<?php require_once './blocks/footer.php';?>
		</div>
<?php require_once './blocks/script.php';?>

</body>
</html>
