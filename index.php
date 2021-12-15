<?php require_once "app/conn.php"; 
      require_once 'utils/functions.php';
      $params = require_once 'static/indexParams.php';
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
				<h2>东莞理工学院·图书馆</h2>
				<p>学而知不足</p>
				<ul class="actions special">
					<li><a href="bookList.php" class="button primary">借书</a></li>
				</ul>
				<?php if (!empty($_GET['error'])): ?>
				<p style="color:red"><?=$params['error'][$_GET['error']]?></p>
				<?php elseif (!empty($_GET['success'])): ?>
				<p style="color:green"><?=$params['success'][$_GET['success']]?></p>
				<?php endif;?>
			</section>

		<!-- Main -->
			<section id="main" class="container">
				<section class="box special">
					<header class="major">
						<h2>欢迎进入
						<br />
						东莞理工学院
						<br />
						电子图书借阅系统</h2>
						<p>下面是本系统实现的功能介绍.</p>
					</header>
					<span class="image featured"><img src="images/pic06.jpg" alt="" /></span>
				</section>

				<section class="box special features">
					<div class="features-row">
						<section>
							<span class="icon solid major fa-bolt accent2"></span>
							<h3>图书管理</h3>
							<p>将图书进行整理, 存储了图书的基本信息以及单独使用唯一识别每一单本书的条形码,确保图书能唯一认证</p>
						</section>
						<section>
							<span class="icon solid major fa-chart-area accent3"></span>
							<h3>读者管理</h3>
							<p>通过建立读者信息, 清晰记录读者认证以及所有借阅记录,方便读者查看自己的借阅信息,同时也方便了管理者轻易管理用户权限.</p>
						</section>
					</div>
					<div class="features-row">
						<section>
							<span class="icon solid major fa-cloud accent4"></span>
							<h3>管理系统</h3>
							<p>允许管理者单独后台页面对网站进行管理, 管理借阅记录以及读者信息, 并能对图书信息和分类情况进行及时更新和补充</p>
						</section>
						<section>
							<span class="icon solid major fa-lock accent5"></span>
							<h3>借阅信用系统</h3>
							<p>通过明确详细的借阅记录使得读者信用得到充分认证, 让管理者和读者都能得到最优的服务.</p>
						</section>
					</div>
				</section>
				
				<section class="box special">				
					<h2>下面是部分优质图书展示</h2>
                    <h5>基于图书热门程度以及用户相似度进行推荐图书</h5>
				</section>
				<!-- 用于展示图书 -->
                <div class="row">
                <!-- 通过Python获取数据-->
                <?php
                    if (isset($_SESSION['account']['user_id'])):
                        $userId = $_SESSION['account']['user_id'];
                        exec("python ./Complex-User-CF.py {$userId}", $output, $return_val);
                        if(isset($output[0]) && $output[0] != "[]"):
                            $bookIds = explode("', '",trim(rtrim(ltrim($output[0], "["), "]"), "'"));
                            $bookIdsStr = join(",", $bookIds);
                            $sql = "SELECT * FROM book_info WHERE id IN ($bookIdsStr)";
                            $bookResult = $conn->query($sql);
                            if ($bookResult != false):
                                foreach(mysqli_fetch_all($bookResult, MYSQLI_ASSOC) as $book):
                                    require './blocks/bookBlock.php';
                                endforeach;
                            else:
                                print_r($output);
                                print_r($bookResult);
                                print_r("获取推荐图书错误");
                            endif;
                        else:
                            $searchBlock = "WHERE 1 = 1 ".
                                (empty($_GET['search']) ? "" : " AND name LIKE '%" . $_GET['search'] . "%'") .
                                (empty($_GET['category'])? "" : " AND category = '" . $_GET['category'] . "'");
                            $orderBlock = "ORDER BY rating DESC";
                            $limitBlock = "LIMIT 4";
                            $bookResult = $conn->query("SELECT * FROM book_info " . $searchBlock . " " . $orderBlock);
                            $bookCount = 0;
                            while (++$bookCount <= 4 && ($book = mysqli_fetch_assoc($bookResult))):
                                require './blocks/bookBlock.php';
                            endwhile;
                        endif;

                    else:
                        $searchBlock = "WHERE 1 = 1 ".
                            (empty($_GET['search']) ? "" : " AND name LIKE '%" . $_GET['search'] . "%'") .
                            (empty($_GET['category'])? "" : " AND category = '" . $_GET['category'] . "'");
                        $orderBlock = "ORDER BY rating DESC";
                        $limitBlock = "LIMIT 4";
                        $bookResult = $conn->query("SELECT * FROM book_info " . $searchBlock . " " . $orderBlock);
                        $bookCount = 0;
                        while (++$bookCount <= 4 && ($book = mysqli_fetch_assoc($bookResult))):
                            require './blocks/bookBlock.php';
                        endwhile;
                    endif;
                	?>
                </div>
			</section>
		<?php require_once './blocks/footer.php';?>
	</div>
	<?php require_once './blocks/script.php';?>
</body>
</html>
