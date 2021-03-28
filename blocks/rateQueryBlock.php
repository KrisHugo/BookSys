<section class="box">
    <h3>查找评价记录</h3>
    <form action="<?= ($userId == -1) ?'backend.php?ctr=rate&query=true':'userRate.php?query=true&id='.$userId?>" method="post">
        <div class="row gtr-uniform gtr-50">
            <div class="col-12">
                <input type="text" name="query" id="query"  value="<?=!empty($query) ? $query : '' ?>"  placeholder="<?= ($userId == -1)?'输入用户名,图书名,评价内容查找':'输入图书名,评价内容查找'?>" />
            </div>
            <div class="col-12">
                <select name="status">
                    <option value="">--请选择评价状态--</option>
                    <option value="normal" <?=(!empty($status) ? ( $status == 'normal' ? "selected": "") : "") ?>>正常</option>
                    <option value="blocked" <?=(!empty($status) ? ( $status == 'blocked' ? "selected": "") : "") ?>>屏蔽</option>
                    <option value="verifying" <?=(!empty($status) ? ( $status == 'verifying' ? "selected": "") : "") ?>>核验中</option>
                </select>
            </div>
            <div class="col-6 col-12-mobilep">
                <label>在此后评价</label>
                <input type="date" name="after" placeholder="此后评价" value="<?=!empty($after) ? $after : '' ?>">
            </div>
            <div class="col-6 col-12-mobilep">
                <label>在此前评价</label>
                <input type="date" name="before" placeholder="此前评价" value="<?=!empty($before) ? $before : ''?>">
            </div>
            <div class="col-12">
                <hr>
            </div>
            <div class="col-12">
                <ul class="actions special">
                    <li><input type="submit" class="button special fit " value="查询"/></li>
                    <li><a href="<?=($userId == -1) ?'backend.php?ctr=rate&reset=true':'userRate.php?reset=true&id='.$userId ?>" class="button special fit alt">清空</a></li>
                </ul>
            </div>
        </div>
    </form>
</section>