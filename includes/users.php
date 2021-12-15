<?php
/* 
 * 本页面用于实现管理员对所有用户的管理操作
 * 1. 支持对用户的删改
 * 2. 支持对用户的借阅信息核查
 * 3. 支持直接筛选出存在借阅超时未偿还的书籍的用户(还可以细化至 未还书, 已还书, 已偿还)
 * 4. 支持对用户借取的书进行确认归还操作
 *  */
$msg = '';
/* 删除操作 */
if (isset($_GET['delete'])){
    $userId = $_GET['delete'];
    //如果还有未还的借阅记录, 则禁止删除;
    $borrowResult = $conn->query("SELECT * FROM borrow WHERE returnDate IS NULL AND userId = $userId");
    if (!$borrowResult){
        header("error.php?error=sql语句错误");
        exit;
    }
    else {
        if ($borrowResult->num_rows > 0){
            $msg = "该用户仍有未还书籍的记录, 禁止删除";
        }
        else{
            if(!deleteUser($conn, $userId)){
                $msg = "删除失败";
            }
            $msg = "删除成功";
        }
    }
}
else if (isset($_GET['ban'])){
    $userId = $_GET['ban'];
    if(modifyStatus($conn, $userId, "banned"))
        $msg = "禁用成功";
    else
        $msg = "禁用失败";
}
elseif (isset($_GET['access'])){
    $userId = $_GET['access'];
    if(modifyStatus($conn, $userId, "access"))
        $msg = "恢复权限成功";
    else
        $msg = "恢复权限失败";
}

require_once 'utils/functions.php';
/* 设置初始值 */
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$maxPage = 1;
$pageSize = 12;
$pageType = 'user';
$colleges = getColleges($conn);
/* 初始化默认设置 */
if (!isset($_SESSION[$pageType]['users']['query'])){
    initiateUsersQuerySession($pageType);
}
/* 仅在发送了POST请求后才这么做 */
if (isset($_GET['reset'])) {
    initiateUsersQuerySession($pageType);
} else if (isset($_GET['query'])) {
    $_SESSION[$pageType]['users']['query'] = (! empty($_POST['query']) ? $_POST['query'] : '');
    $_SESSION[$pageType]['users']['authority'] = (! empty($_POST['authority']) ? $_POST['authority'] : '');
    $_SESSION[$pageType]['users']['college'] = (! empty($_POST['college']) ? $_POST['college'] : '');
    $_SESSION[$pageType]['users']['record'] = (! empty($_POST['record']) ? $_POST['record'] : '');
    $_SESSION[$pageType]['users']['status'] = (! empty($_POST['status']) ? $_POST['status'] : '');
}
/*获取session值，尽管重复，却无法被抽取出来*/
$query = $_SESSION[$pageType]['users']['query'];
$authority = $_SESSION[$pageType]['users']['authority'];
$college = $_SESSION[$pageType]['users']['college'];
$stored =  $_SESSION[$pageType]['users']['record'];
$status =  $_SESSION[$pageType]['users']['status'];

?>
<section class="box">
	<h3>查找用户</h3>
	<form action="backend.php?ctr=users&query=true" method="post">
		<div class="row gtr-uniform gtr-50">
			<div class="col-12">
				<input id="query" type="text" name="query" value="<?=(!empty($query) ? $query : "") ?>" placeholder="输入用户名, 认证名字, 读者证, 学生证查找" />
			</div>
			<!-- 选择权限 -->
		    <div class="col-12">
    			<select name="authority">
    	    		<option value="" >--请选择用户权限--</option>
    	    		<option value="user" <?=(!empty($authority) ? ($authority == 'user' ? "selected": "") : "") ?>>读者</option>
    	    		<option value="admin" <?=(!empty($authority) ? ($authority == 'admin' ? "selected": "") : "") ?>>管理员</option>
				</select>
			</div>
			<!-- 选择学院 -->
		    <div class="col-12">
    			<select name="college">
    	    		<option value="">--请选择学院--</option>
    	    		<?php foreach ($colleges as $k => $v) : ?>
    	    		<option value="<?=$v?>" <?=(!empty($college) ? ( $college == $v ? "selected": "") : "") ?>><?=$v?></option>
   					<?php endforeach;?>
       			</select>
			</div>
			<div class="col-12">
				<hr>
			</div>
		    <div class="col-6 col-12-mobilep">
    			<select name="record">
    	    		<option value="">--请选择借阅条件--</option>
    	    		<option value="stored" <?=(!empty($stored) ? ( $stored == 'stored' ? "selected": "") : "") ?>>有借书</option>
    	    		<option value="vacant" <?=(!empty($stored) ? ( $stored == 'vacant' ? "selected": "") : "") ?>>未借书</option>
    	    		<option value="defaulted" <?=(!empty($stored) ? ( $stored == 'defaulted' ? "selected": "") : "") ?>>有违约</option>
    	    		<option value="perfect" <?=(!empty($stored) ? ( $stored == 'perfect' ? "selected": "") : "") ?>>无违约</option>
       			</select>
			</div>
		    <div class="col-6 col-12-mobilep">
    			<select name="status">
    	    		<option value="">--请选择用户状态条件--</option>
    	    		<option value="access" <?=(!empty($status) ? ( $status == 'access' ? "selected": "") : "") ?>>正常用户</option>
    	    		<option value="banned" <?=(!empty($status) ? ( $status == 'banned' ? "selected": "") : "") ?>>未授权用户</option>
       			</select>
			</div>
			<div class="col-12">
        		<ul class="actions special">
        			<li><input type="submit" class="button special fit " value="查询"/></li>
        			<li><a href="backend.php?ctr=users&reset=true" class="button special fit alt">清空</a></li>
        		</ul>
			</div>
		</div>
	</form>
</section>
<?php $users = getUsers($conn, $pageType,  $page, $pageSize, $maxPage);?>
<section class="box">

    <?php if (count($users) > 0):?>
	<h3>用户列表</h3>
	<?php if (!empty($msg)):?>
	<p style="color: red"><?=$msg?></p>
	<?php endif;?>
	<div class="table-wrapper">
	<form action="includes/app/userUpdate.php" method="post">
		<table class="alt">
			<thead>
				<tr>
					<th>用户名</th>
					<th>权限级别</th>
					<th>姓名</th>
					<th>学院</th>
					<th>借书证</th>
					<th>状态</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
            <?php foreach ($users as $k => $v) :?>
            	<tr>
    				<td><?=$v['username']?></td>
    				<td>
        			<select name="<?= 'authority'.$v['id']?>">
        	    		<option value="user" <?=$v["authority"] == 'user' ? "selected": "" ?>>读者</option>
        	    		<option value="admin" <?=$v["authority"] == 'admin' ? "selected": "" ?>>管理员</option>
    				</select></td>
    				<td><?=$v["name"]?></td>
    				<td><?=$v["college"]?></td>
    				<td><?php if(!empty($v["readerID"])): ?>
    				<?=$v["readerID"]?>
    				<?php else :?>
    				<a class="button special small fit" href="<?='includes/app/verify.php?verify='.$v['id']?>">认证</a>
    				<?php endif;?>
    				</td>
    				<td><?=$v["status"] == 'access'?"正常":"禁用"?></td>
    				<td>
    					<a href="info.php?id=<?=$v["id"]?>" >详细</a>
    					<?php if (!empty($v['status']) && $v['status'] == 'access') : ?>
    					<a href="backend.php?ctr=users&ban=<?=$v["id"]?>">禁用</a>
    					<?php elseif (!empty($v['status']) && $v['status'] == 'banned') :?>
    					<a href="backend.php?ctr=users&access=<?=$v["id"]?>">恢复</a>
    					<?php endif;?>
    					<a href="backend.php?ctr=users&delete=<?=$v["id"]?>">删除</a>
    				</td>
    			</tr>
        		<?php endforeach;?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6"></td>
					<td><input class="alt" type="submit" value="修改" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
	</div>
    <?php else: ?>
    <h3>没有符合的用户数据</h3>
    <?php endif; ?>

</section>

<?php $pageUrl = 'backend.php?ctr=users&' ?>
<?php require_once 'blocks/pageBlock.php';?>
