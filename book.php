<?php  
require_once "app/conn.php";
require_once 'utils/functions.php';
/*获取图书*/
/*包括内容:
1.图书信息
2.图书细目
    2.1 图书库存
    2.2 图书分类信息
3.图书评价(需要分页,使用ratePage作为参数)
    3.1 图书评价内容
    3.2 图书评分
    3.3 图书评价回复
    3.4 图书评价点赞
4.新增图书评价功能
*/
$bookResult = $conn->query("SELECT * FROM book_info WHERE id = " . $_GET['book_id']);
if ($bookResult && $bookResult->num_rows > 0){
    /*book_info*/
    $book = mysqli_fetch_assoc( $bookResult );
    /*book_details*/
    $detailsResult = $conn->query("SELECT * FROM book_detail WHERE id = ".$_GET['book_id']." AND status = 'stored'");
    $details = mysqli_fetch_all($detailsResult, MYSQLI_ASSOC);
    /*category (book_info->category)*/
    $categorySql = "SELECT * FROM `category` WHERE id = ".$book['category'];
    $categoryResult = $conn->query($categorySql);
    if($categoryResult){
        $category = mysqli_fetch_assoc($categoryResult);
        $category = $category['category'];
    }
    else{
        //未搜索到分类, id号异常
        header("Location:error.php?error=检索图书分类ID号异常");
        exit;
    }
    /*book_rate*/
    $maxPage = 1;
    $pageSize = 10;
    $page = isset($_GET['page'])?$_GET['page']:1;
    $firstPos = ($page - 1) * $pageSize;
    $where = "WHERE bookId = ".$_GET['book_id']." AND status = 'normal' ORDER BY created DESC ";
    $bookRatesResult = $conn->query("SELECT book_rate.*, r.name as name FROM book_rate LEFT JOIN reader r ON userId = r.id $where LIMIT $firstPos, $pageSize");
    $bookRates = mysqli_fetch_all($bookRatesResult, MYSQLI_ASSOC);
    $pageResult = $conn->query("SELECT (COUNT(1) / $pageSize) `pages` FROM book_rate LEFT JOIN reader r ON userId = r.id " . $where);
    $pageInfo = mysqli_fetch_assoc($pageResult);
    $maxPage = ceil($pageInfo['pages']);
}
else {
    //未搜索到图书, id号异常
    header("Location:error.php?error=检索图书ID号异常");
    exit;
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>图书管理系统·图书</title>
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
				<?php require_once './blocks/bookInfo.php';?>
				</section>
			<?php require_once './blocks/footer.php';?>
		</div>
		<?php require_once './blocks/script.php';?>
	</body>
</html>