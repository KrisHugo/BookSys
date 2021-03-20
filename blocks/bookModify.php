<?php
if (!isset($command)){
    $command = "list";
}

$categoryResult = $conn->query("SELECT * FROM category");
$categories = mysqli_fetch_all( $categoryResult, MYSQLI_ASSOC);
switch ($command){
    case 'update':
        $bookResult = $conn->query("SELECT * FROM book_info WHERE id = ". $_GET['update']);
        if ($bookResult->num_rows > 0){
            $theModified = mysqli_fetch_assoc( $bookResult );
            $detailsResult = $conn->query("SELECT * FROM book_detail WHERE id = ".$_GET['update']);
            $details = mysqli_fetch_all($detailsResult, MYSQLI_ASSOC);
        }
        else {
            //未搜索到图书, id号异常
            header("Location: backend.php");
            exit;
        }
        break;
    case 'add':
        $details = [];
        break;
    default:
        header("Location: backend.php");
        exit;
}
?>

<script type="text/javascript">
    const min = parseInt(<?=($command == 'update') ? sizeof($details) : 0 ?>);
    const max = 10;
let lastCount = min;

function modifyCodeElements(countId){
    let ele;
    let i;
    let y = document.getElementById(countId).value;
    y = parseInt(y);
	if (y >= min && y <= max){
        const diff = y - lastCount;
        const o = document.getElementById("bookCodes");

        if(diff > 0){
	    	for (i = 0; i < diff; i++){
                const code = document.createElement("td");
                code.innerHTML = "新建时生成书号!";
                const status = document.createElement("td");
                status.innerHTML = "new";
                const act = document.createElement("td");
                ele = document.createElement("tr"); //创建一个tr
	    		ele.appendChild(code);
	    		ele.appendChild(status);
	    		ele.appendChild(act);
	    		o.appendChild(ele);
	    	}
		}
		else{
			for (i = diff; i < 0; i++){
                const len = o.childElementCount;
                ele = o.getElementsByTagName('tr')[len - 1];
                o.removeChild(ele);
			}
		}
		lastCount = y;
	}
	
}
</script>

<section class="box">
<h3>图书信息·<?= $params["pageName"]["$command"] ?></h3>
<form action="<?=$url?>" method="post">
	<div class="row gtr-uniform gtr-50">		
        <div class="col-6 col-12-mobilep">
        <!-- 图书ID号, 隐藏以免用户修改 -->
        <input type="hidden" name="<?=$command?>" value="<?= ($command == 'update') ? $_GET['update']: 'add'?>" />
		<input type="hidden" name="last" value="<?=($command == 'update') ? $theModified['count'] : '0' ?>" readonly>
        <label >书名: </label>
        <input type="text" name="name" placeholder="书名" value="<?=($command == 'update') ? $theModified['name'] : '' ?>" required autofocus>
        <label>作者: </label>
        <input type="text" name="author" placeholder="作者" value="<?=($command == 'update') ? $theModified['author'] : '' ?>">
        <label>出版社: </label>
        <input type="text" name="press" placeholder="XX出版社" value="<?=($command == 'update') ? $theModified['press'] : '' ?>">
        <label>出版日期: </label>
        <input type="date" name="press_time" placeholder="出版时间" value="<?=($command == 'update') ? $theModified['press_time'] : time() ?>">
        <label>ISBN号: </label>
        <input type="text" name="ISBN" placeholder="ISBN号" value="<?=($command == 'update') ? $theModified['ISBN'] : '' ?>">
        <label>简介: </label>
    	<textarea style="resize: none;" rows="4" placeholder="简介" name="desc"><?=($command == 'update') ? $theModified['desc'] : '' ?></textarea>
        <label>价格: </label>
        <input type="number" name="price" placeholder="价格" min=0 value="<?=($command == 'update') ? $theModified['price'] : '0' ?>">
        <label>类型: </label>
	    <select name="category">
	    	<?php foreach($categories as $k => $v):?>
	    		<option value="<?=$v['id']?>" <?=($v['id']== (($command == 'update') ? $theModified['category'] : -1))?"selected":""?>><?= $v['category']?></option>
	    	<?php endforeach;?>
	    </select>
	    <label>数量: (最大值:10)</label>
        <input id="count" type="number" placeholder="数目" name="count" min="<?=($command == 'update') ? sizeof($details) : '0' ?>" max=10 value="<?=($command == 'update') ? $theModified['count'] : '0' ?>" onchange="modifyCodeElements(this.id)">
        </div>
        <div class="col-6 col-12-mobilep">
			<h3>所有库存</h3>
			<?php if (sizeof($details) == 10) :?>
			<p>本书库存已满!</p>
			<?php endif;?>
			<div class="table-wrapper">
				<table class="alt">
					<thead>
						<tr>
							<th>图书编号</th>
							<th>状态</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody id="bookCodes">
                   		<?php if (sizeof($details) > 0) :?>
                		<?php foreach ($details as $k => $v):?>
						<tr>
							<td><?=$v['uniqueCode']?></td>
							<td><?=$v['status']?></td>
							<td>
								<?php if ($v['status'] != 'borrowed'):?>
								<input type="checkbox" id="<?='delete'.$v['uniqueCode']?>" name="<?='delete'.$v['uniqueCode']?>"><label for="<?='delete'.$v['uniqueCode']?>">删除</label>
								<?php else :?>
								<input type="checkbox" disabled /><label style="color: red">不能删除</label>
								<?php endif;?>
							</td>
						</tr>
                		<?php endforeach;?>
                    	<?php endif;?>
                    	
					</tbody>
				</table>
			</div>
        </div>
	</div>
	<input type="submit" style="width: 100px; margin-top: 10px;margin-left: 70%" value="<?=($command == 'update') ? '修改' : '提交' ?>"/>
</form>
</section>