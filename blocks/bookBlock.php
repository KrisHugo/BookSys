<!-- 图书展列单块 -->
<div class="col-6 col-12-narrower">
	<section class="box special">
		<span class="image featured"><img src="images/pic01.jpg" alt="" /></span>
		<h3><?=$book['name']?></h3>
		<p class="rowAutoCollapse" style="text-align: left; float: left;"><?=$book['press']?></p>
		<p class="rowAutoCollapse" style="text-align: right; float: right;"><?=$book['author']?></p>
		<p class="textAutoCollapse"><?=$book['desc']?></p>
		<p style="text-align: left; float: left;"><?=$book['press_time']?></p>
		<p style="text-align: right; float: right;">在馆数: <?=$book['count']?></p>
		<ul class="actions special">
			<li><a href="<?='book.php?book_id='.$book['id']?>" class="button special small fit">了解更多</a></li>
		</ul>
		<hr>
	</section>
</div>