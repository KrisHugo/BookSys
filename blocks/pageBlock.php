<!-- 放置分页栏 -->
<div class="box">
	<ul class="actions special">
		<!-- 左箭头 -->
	   	<?php if ($page != 1) : ?>
		<li><a href="<?=$pageUrl.'page='.($page - 1)?>"
			class="button special small fit">上一页</a></li>
		<?php endif;?>
		<!-- 中间页数 -->
    	<?php for ($i = max([$page - 2, 1]); $i <= min([$page + 2, $maxPage]); $i++):?>
		<li><a href="<?=$pageUrl.'page='.$i?>"
			class="<?='button special small fit '. (($page == $i) ? '': 'alt') ?>"><?=$i?></a></li>
    	<?php endfor;?>
		<!-- 右箭头 -->
	   	<?php if ($maxPage != 0 && $page != $maxPage) : ?>
		<li><a href="<?=$pageUrl.'page='.($page + 1)?>"
			class="button special small fit">下一页</a></li>
		<?php endif;?>
	</ul>
</div>