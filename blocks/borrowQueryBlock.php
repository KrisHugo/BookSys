<section class="box">
	<h3>查找借阅记录</h3>
	<form action="<?= ($userId == -1) ?'backend.php?ctr=borrow&query=true':'userBorrow.php?query=true&id='.$userId?>" method="post">
		<div class="row gtr-uniform gtr-50">
			<div class="col-12">
				<input type="text" name="query" id="query" value="" placeholder="<?= ($userId == -1)?'输入用户名,条形码,图书名查找':'输入条形码,图书名查找'?>" />
			</div>
		    <div class="col-12">
    			<select name="status">
    	    		<option value="">--请选择图书状态--</option>
    	    		<option value="stored" <?=(!empty($status) ? ( $status == 'stored' ? "selected": "") : "") ?>>已还</option>
    	    		<option value="borrow" <?=(!empty($status) ? ( $status == 'borrow' ? "selected": "") : "") ?>>在借</option>
    	    		<option value="defaulted" <?=(!empty($status) ? ( $status == 'defaulted' ? "selected": "") : "") ?>>逾期</option>
    	    		<option value="lost" <?=(!empty($status) ? ( $status == 'lost' ? "selected": "") : "") ?>>丢失</option>
       			</select>
			</div>
			<div class="col-6 col-12-mobilep">
				<label>在此后借阅</label>
        		<input type="date" name="borrowAfter" placeholder="此后借阅" value="<?=!empty($borrowAfter) ? $borrowAfter : '' ?>">
			</div>			
			<div class="col-6 col-12-mobilep">
				<label>在此前借阅</label>
        		<input type="date" name="borrowBefore" placeholder="此前借阅" value="<?=!empty($borrowBefore) ? $borrowBefore : ''?>">
			</div>
		    <div class="col-12">
		    	<hr>
			</div>
			<div class="col-12">
        		<ul class="actions special">
        			<li><input type="submit" class="button special fit " value="查询"/></li>
        			<li><a href="<?=($userId == -1) ?'backend.php?ctr=borrow&reset=true':'userBorrow.php?reset=true&id='.$userId ?>" class="button special fit alt">清空</a></li>
        		</ul>
        	</div>
		</div>
	</form>
</section>