<section class="box">
	<h3>查找图书</h3>
	<form action="<?=$pageType=='admin'?'backend.php?ctr=list&query=true':'booklist.php?query=true'?>" method="post">
		<div class="row gtr-uniform gtr-50">
			<div class="col-12">
				<input id="query" type="text" name="query" id="query" value="<?=(!empty($query) ? $query : "") ?>" placeholder="输入图书名,ISBN,关键词,作者,出版社查找" />
			</div>
			<!-- 选择分类 -->
		    <div class="col-6 col-12-mobilep">
    			<select name="category">
    				<option value="" selected>--请选择筛选分类--</option>
        	    	<?php foreach($categories as $k => $v):?>
        	    		<option value="<?=$v['id']?>" <?=(!empty($category) ? ( $category == $v['id'] ? "selected": "") : "") ?>><?= $v['category']?></option>
        	    	<?php endforeach;?>
    			</select>
			</div>
		    <div class="col-6 col-12-mobilep">
    			<select name="stored">
    				<option value="">--请选择筛选条件--</option>
        	    	<option value="stored" <?=!empty($stored)? ($stored=='stored' ? "selected": "") :""?> >有库存</option>
        	    	<option value="vacant" <?=!empty($stored)? ($stored=='vacant' ? "selected": "") :""?> >无库存</option>
    			</select>
			</div>
			<div class="col-12">
				<hr>
			</div>
			<div class="col-12">
        		<ul class="actions special">
        			<li><input type="submit" class="button special fit " value="查询"/></li>
        			<li><a href="<?=$pageType=='admin'?'backend.php?ctr=list&reset=true':'booklist.php?reset=true'?>" class="button special fit alt">清空</a></li>
        		</ul>
			</div>
		</div>
	</form>
</section>
