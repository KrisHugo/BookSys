<header>
    <h2>《<?= $book['name'] ?>》</h2>
    <p><?= $book['author'] ?> 著</p>
</header>
<div class="box">
    <span class="image featured"><img src="images/pic01.jpg" alt=""/></span>
    <div class="row">
        <div class="col-6 col-12-mobilep">
            <h3>图书归类 : <?= $category ?></h3>
            <hr>
            <h3>图书简介</h3>
            <p><?= $book['desc'] ?></p>
            <hr>
            <h3>图书信息</h3>
            <ul class="alt">
                <li></li>
                <li>ISBN号: <?= $book['ISBN'] ?></li>
                <li>出版社: <?= $book['press'] ?></li>
                <li>出版时间: <?= $book['press_time'] ?></li>
                <li>价格: <?= $book['price'] ?></li>
                <li></li>
            </ul>
        </div>
        <div class="col-6 col-12-mobilep">
            <h3>馆内图书</h3>
            <p>馆内在册数: <?= $book['count'] ?></p>

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
                    <?php if (sizeof($details) > 0) : ?>
                        <?php foreach ($details as $k => $v): ?>
                            <tr>
                                <td><?= $v['uniqueCode'] ?></td>
                                <td><?= $v['status'] ?></td>
                                <td>
                                    <a class="button special small fit"
                                       href="<?= 'app/borrow.php?uniqueCode=' . $v['uniqueCode'] . '&bookId=' . $book['id'] ?>">借出</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($_SESSION['account']['user_authority']) and $_SESSION['account']['user_authority'] == 'admin'): ?>
            <ul class="actions special">
                <li><a href="<?= 'backend.php?ctr=update&update=' . $book['id'] ?>"
                       class="button special small fit alt">修改</a></li>
            </ul>
        <?php endif; ?>
        <div class="col-12">
            <hr/>
            <h3 class="col-12 text-center">评价列表</h3>
            <?php if (sizeof($bookRates) > 0):/*此处需要添加评价列表*/ ?>
                <?php foreach ($bookRates as $k => $v): ?>
                    <div class="media">
                        <div class="media-left">
                            <h4>
                                <a href="<?= 'info.php?id=' . $v['userId'] ?>"><?= $v['name'] ?></a>
                                <div class="icons">
                                    <?php for ($i = 0; $i < $v['rating']; $i++): ?>
                                        <span class="icon fa-star solid" aria-hidden="true"></span>
                                    <?php endfor;
                                    for ($i = 0; $i < 5 - $v['rating']; $i++): ?>
                                        <span class="icon fa-star" aria-hidden="true"></span>
                                    <?php endfor; ?>
                                </div>
                            </h4>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading"><?= $v['content'] ?></h5>
                        </div>
                        <div class="media-left">
                            <h5 class="media-heading"><?= $v['created'] ?></h5>
                        </div>
                    </div>
                    <hr/>
                <?php endforeach; ?>
                <?php $pageUrl = "book.php?book_id=" . $book['id'] . "&" ?>
                <?php require_once './blocks/pageBlock.php'; ?>
            <?php else: ?>
                <h5>暂无评价</h5>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <hr/>
            <form action="./app/rating.php" method="post">
                <h3 class="col-12 text-center">写写你的阅读感受</h3>
                <br/>
                <input id="ratingId" name="ratingId" type="hidden" value="<?= $book['id'] ?>">
                <select id="ratingNum" name="ratingNum">
                    <option value=5>5</option>
                    <option value=4>4</option>
                    <option value=3>3</option>
                    <option value=2>2</option>
                    <option value=1>1</option>
                </select>
                <br/>
                <textarea id="ratingContent" name="ratingContent"></textarea>
                <br/>
                <input type="submit" value="提交评价">
            </form>
        </div>


    </div>
</div>