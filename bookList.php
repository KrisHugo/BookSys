<?php require_once "app/conn.php"; 
      require_once 'utils/functions.php';
      /* 获取分页信息 */
      $maxPage = 1;
      $pageSize = 12;
      /* 修改参数 */
      $pageType = 'user';
      require_once 'blocks/listBlock.php';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>图书借阅管理系统·图书列表</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/custom.css" />
	</head>
	<body class="is-preload">
		<div id="page-wrapper">
			<?php require_once './blocks/pageHeader.php';?>

			<!-- Main -->
			<section id="main" class="container">			
    			<header>
                	<h2>图书列表</h2>
                	<p>欢迎浏览借阅!</p>
                </header>
				<?php require_once 'blocks/listQueryBlock.php';?>
                <div class="box">
    				<!-- 用于展示图书 -->
                    <div class="row">
                    	<?php 
                    	$books = getBooks($conn, $pageType,  $page, $pageSize, $maxPage);
                    	foreach ($books as $k => $book):
    				       require './blocks/bookBlock.php';
                    	endforeach;?>
                    </div>
                </div>
                <?php $pageUrl = 'bookList.php?'?>
				<?php require_once './blocks/pageBlock.php';?>
			</section>
			<?php require_once './blocks/footer.php';?>
		</div>
		<?php require_once './blocks/script.php';?>
	</body>
</html>