<?php
/**
 * 生成 id为key的多维数组
 * @param array $array
 * @param string $indexName
 * @return array[]
 */
function indexArrayToRelatedArray(array $array, $indexName)
{
    $data = [];
    foreach ($array as $key => $value) {
        if (!is_numeric($key)) {
            continue;
        }

        $index = '';


        if (is_array($value) && isset($value[$indexName]) && !empty($value[$indexName])) {
            $index = $value[$indexName];
        } elseif (is_object($value) && isset($value->$indexName) && !empty($value->$indexName)) {
            $index = $value->$indexName;
        }

        // 重复的将被后面覆盖
        if (!empty($index)) {
            $data[$index] = $value;
        }
    }
    return $data;
}

function getArrays(array $array, $indexName)
{
    $data = [];
    foreach ($array as $key => $value) {
        $data[] = $value[$indexName];
    }
    return $data;
}

/**
 * 通过record筛选不同借阅记录得到条件的用户
 * @param mysqli $conn
 * @param string $record
 * @param array $totalIds
 * @param number $defaultTime
 * @return array
 */
function queryUserBorrowStatus($conn, $record, $totalIds, $defaultTime = 2592000)
{
    //计算是否违约或有无库存
    $defaultSql = " SELECT userId FROM borrow WHERE (returnDate IS NULL AND (UNIX_TIMESTAMP(borrowDate) < " . (time() - $defaultTime)
        . ")) OR (returnDate IS NOT NULL AND (UNIX_TIMESTAMP(returnDate) - UNIX_TIMESTAMP(borrowDate)) > " . $defaultTime . ")";
    /* 以下内容, 如果需要计算是否违约, 需要考虑上式得到的结果有无记录, 若没有记录则不可以合并,同时可以认为所有人都满足要求 */
    $recordWhere = "";
    if ($record == 'defaulted' || $record == 'perfect') {
        $defaultedResult = $conn->query($defaultSql);
        if ($defaultedResult) {
            if ($defaultedResult->num_rows > 0)
                $recordWhere = "WHERE userId IN (" . $defaultSql . ")";
            else if ($defaultedResult->num_rows == 0)
                $recordWhere = " WHERE 1 = 0 ";
        }
    }
    /* 查询借阅记录, 筛选出合法和非法的条件 */
    $borrowResult = $conn->query("SELECT userId, COUNT(userId) FROM borrow " . $recordWhere . " GROUP BY userId ORDER BY userId");
    $borrows = mysqli_fetch_all($borrowResult, MYSQLI_ASSOC);
    //进行id排查, 需要的是进行借书记录计算, 然后再进行 非mysql 的筛选
    $borrowIds = array_unique(getArrays($borrows, "userId"));
    /* 进行异或排查, 若是双选则不需要排查 */
    switch ($record) {
        /* 通过交集差集来计算结果Ids */
        case "defaulted":
        case "stored" :
            break;
        case "perfect":
        case "vacant" :
            $borrowIds = array_diff($totalIds, $borrowIds);
            break;
    }
    $borrowIds = array_intersect($totalIds, $borrowIds);
    return $borrowIds;
}

/**
 * 初始化图书列表Sessions
 *
 * @param int $pageType
 */
function initiateListQuerySessions($pageType)
{
    $_SESSION[$pageType]['books']['query'] = '';
    $_SESSION[$pageType]['books']['category'] = '';
    $_SESSION[$pageType]['books']['stored'] = '';
}



/**
 * 获取所有图书信息
 * @param mysqli $conn
 * @param string $pageType
 * @param int $page
 * @param int $pageSize
 * @param int $maxPage
 * @return array
 */
function getBooks($conn, $pageType, $page, $pageSize, &$maxPage)
{
    $query = $_SESSION[$pageType]['books']['query'];
    $category = $_SESSION[$pageType]['books']['category'];
    $stored = $_SESSION[$pageType]['books']['stored'];
    $where = "WHERE 1 = 1 "
        . (!empty($query) ? " AND ( `name` LIKE '%$query%' OR `author` LIKE '%" . $query
            . "%' OR `ISBN` LIKE '%$query%' OR `press` LIKE '%$query%' OR `desc` LIKE '%$query%')" : "")
        . (!empty($category) ? " AND category = $category" : "")
        . (!empty($stored) ? ($stored == 'stored' ? " AND count != 0" : " AND count = 0") : "");
    $bookSql = "SELECT * FROM book_info " . $where . (" LIMIT " . (($page - 1) * $pageSize) . " , " . $pageSize);
    $books = mysqli_fetch_all($conn->query($bookSql), MYSQLI_ASSOC);

    $pageResult = $conn->query("SELECT (COUNT(1) / $pageSize) `pages` FROM book_info " . $where);
    $pageInfo = mysqli_fetch_assoc($pageResult);
    $maxPage = ceil($pageInfo['pages']);

    return $books;
}

/**
 * 初始化用户筛选Session
 * @param string $pageType
 */
function initiateUsersQuerySession($pageType)
{
    $_SESSION[$pageType]['users']['query'] = '';
    $_SESSION[$pageType]['users']['authority'] = '';
    $_SESSION[$pageType]['users']['college'] = '';
    $_SESSION[$pageType]['users']['record'] = '';
    $_SESSION[$pageType]['users']['status'] = '';
}

/**
 * 获取所有用户信息
 * @param mysqli $conn
 * @param int $pageType
 * @param int $page
 * @param int $pageSize
 * @param int $maxPage
 * @return array[]
 */
function getUsers($conn, $pageType, $page, $pageSize, &$maxPage)
{
    /*获取session值，尽管重复，却无法被抽取出来*/
    $query = $_SESSION[$pageType]['users']['query'];
    $authority = $_SESSION[$pageType]['users']['authority'];
    $college = $_SESSION[$pageType]['users']['college'];
    $record = $_SESSION[$pageType]['users']['record'];
    $status = $_SESSION[$pageType]['users']['status'];
    /* 账户查询 */
    $where = "WHERE (1 = 1 "
        . (!empty($query) ? " AND (username Like '%" . $query . "%' OR account Like '%" . $query . "%')" : "")
        . (!empty($authority) ? " AND authority = '" . $authority . "'" : "")
        . ")";
    /* 获取账户 */
    $accountResult = $conn->query("SELECT * FROM accounts " . $where . " ORDER BY id");
    $accounts = mysqli_fetch_all($accountResult, MYSQLI_ASSOC);
    $totalIds = getArrays($accounts, "id");
    if (!empty($record)) {
        $totalIds = queryUserBorrowStatus($conn, $record, $totalIds);
    }
    $readerIds = implode(",", $totalIds);
    $where = "WHERE id IN (" . $readerIds . ") ";
    /* 关键词查询 */
    $where .= (!empty($query) ? " AND (name Like '%" . $query . "%' OR readerID Like '%" . $query . "%' OR studentID Like '%" . $query . "%')" : "");
    /* 查询学院 */
    $where .= (!empty($college) ? " AND college = '" . $college . "'" : "");
    /* 查询状态 */
    $where .= !empty($status) ? " AND status = '" . $status . "'" : "";
    /* 获取读者信息 */
    $readerResult = $conn->query("SELECT * FROM reader " . $where . " ORDER BY id " . ("LIMIT " . ($page - 1) * $pageSize . "," . $pageSize));

    $readers = mysqli_fetch_all($readerResult, MYSQLI_ASSOC);
    /* 生成用户 */
    $validReaders = indexArrayToRelatedArray($readers, "id");
    $validAccounts = indexArrayToRelatedArray($accounts, "id");
    $users = [];
    foreach ($validReaders as $k => $v) {
        $users[$k] = array_merge($validAccounts[$k], $v);
    }

    $pageResult = $conn->query("SELECT (COUNT(1) / " . $pageSize . ") `pages` FROM reader " . $where);
    $pageInfo = mysqli_fetch_assoc($pageResult);
    $maxPage = ceil($pageInfo['pages']);
    return $users;
}

/**
 * 获取分类
 * @param mysqli $conn
 * @return array[]
 */
function getCategories($conn)
{
    /* 获取分类 */
    $categoryResult = $conn->query("SELECT * FROM category ORDER BY id");
    return indexArrayToRelatedArray(mysqli_fetch_all($categoryResult, MYSQLI_ASSOC), "id");
}


/**
 * 获取所有学院枚举值
 * @param mysqli $conn : 数据库连接
 **@return false|string[]
 * @author krishugo
 */
function getColleges(mysqli $conn)
{
    $collegeResult = $conn->query("SELECT column_type FROM information_schema. COLUMNS WHERE TABLE_SCHEMA = 'BookSys' AND DATA_TYPE = 'enum' "
        . " AND table_name='reader' AND column_name='college';");
    $colleges = mysqli_fetch_all($collegeResult, MYSQLI_ASSOC);

    $preg = "/enum\('(.*)'\)/";
    preg_match_all($preg, $colleges[0]["COLUMN_TYPE"], $matchArr);
    $colleges = explode("','", $matchArr[1][0]);
    return $colleges;
}

/***
 * 仅限用于borrow和rate两个query, 未来会找到更加通用的模组
 * @param $config
 * @param $userId
 * @param $query
 * @param $after
 * @param $before
 * @param $status
 */
function configQuerySession($config, $userId, &$query, &$after, &$before, &$status){
    /* 以下内容为查询板块的缓存控制 */
    /* 评价缓存需要储存以下几个数据: 关键字, 某时间之前创建, 某时间之后创建, 状态 */
    /* 初始化Session以及设定Session */
    if (empty($_SESSION[$config]["$userId"]['query']) || !empty($_GET['reset'])){
        $_SESSION[$config]["$userId"]['query'] = '';
        $_SESSION[$config]["$userId"]['after'] = '';
        $_SESSION[$config]["$userId"]['before'] = '';
        $_SESSION[$config]["$userId"]['status'] = '';
    }
    /* 仅在发送了POST请求后才这么做 */
    if (isset($_GET['query'])) {
        $_SESSION[$config]["$userId"]['query'] =  (!empty($_POST['query']) ? $_POST['query'] : '');
        $_SESSION[$config]["$userId"]['after'] = (!empty($_POST['after']) ? $_POST['after'] : '');
        $_SESSION[$config]["$userId"]['before'] = (!empty($_POST['before']) ? $_POST['before'] : '');
        $_SESSION[$config]["$userId"]['status'] = (!empty($_POST['status']) ? $_POST['status'] : '');
    }
    $query = $_SESSION[$config]["$userId"]['query'];
    $after = $_SESSION[$config]["$userId"]['after'];
    $before = $_SESSION[$config]["$userId"]['before'];
    $status = $_SESSION[$config]["$userId"]['status'];
}

function bookQueryByKeyword($conn, $query){
    $where = "";
    //通过输入值查询是否是book.
    $bookResult = $conn->query("SELECT * FROM book_info WHERE name LIKE '%" . $query . "%'");
    if ($bookResult != false) {
        $bookIds = mysqli_fetch_all($bookResult, MYSQLI_ASSOC);
        $bookIds = getArrays($bookIds, "id");
        $where = (!empty($bookIds) ? "OR bookId IN (" . implode(",", $bookIds) . ") " : "");
    }
    return $where;
}

/**
 *  * 获取所有借阅信息, 支持用户查询和管理员查询两种模式
 * 如果userId为-1(默认值)则设定为管理员查询模式, 否则按照用户id来进行查询.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $page
 * @param int $pageSize
 * @param int $maxPage
 * @param number $defaultTime
 * @return array
 */
function getBorrows($conn, $userId, $page, $pageSize, &$maxPage, $defaultTime = 2592000)
{
    $query = $_SESSION['borrow']["$userId"]['query'];
    $after = $_SESSION['borrow']["$userId"]['after'];
    $before = $_SESSION['borrow']["$userId"]['before'];
    $status = $_SESSION['borrow']["$userId"]['status'];

    //建立查询语句
    //通过输入值查询是否是编号
    $where = ($userId == -1) ? "WHERE 1 = 1 " : "WHERE userId =" . $userId;
    if (!empty($query)) {
        $where .= " AND ( 1 = 0 ";
        $codeResult = $conn->query("SELECT * FROM book_detail WHERE uniqueCode LIKE '%" . $query . "%'");
        if ($codeResult != false) {
            $codes = mysqli_fetch_all($codeResult, MYSQLI_ASSOC);
            $codes = getArrays($codes, "uniqueCode");
            $where .= (!empty($codes) ? "OR uniqueCode IN (" . implode(",", $codes) . ") " : "");
        }
        //通过输入值查询是否是book.
        $where .= bookQueryByKeyword($conn, $query);
//        $bookResult = $conn->query("SELECT * FROM book_info WHERE name LIKE '%" . $query . "%'");
//        if ($bookResult != false) {
//            $bookIds = mysqli_fetch_all($bookResult, MYSQLI_ASSOC);
//            $bookIds = getArrays($bookIds, "id");
//            $where .= (!empty($bookIds) ? "OR bookId IN (" . implode(",", $bookIds) . ") " : "");
//        }
        if ($userId == -1) {
            //通过输入值查询是否是user.
            $userResult = $conn->query("SELECT * FROM reader WHERE name LIKE '%" . $query . "%'");
            if ($userResult != false) {
                $userIds = mysqli_fetch_all($userResult, MYSQLI_ASSOC);
                $userIds = getArrays($userIds, "id");
                $where .= (!empty($userIds) ? "OR userId IN (" . implode(",", $userIds) . ") " : "");
            }
        }
        $where .= ")";
    }
    if (!empty($after)) {
        $where .= " AND borrowDate >= '" . $after . "'";
    }
    if (!empty($before)) {
        $where .= " AND borrowDate <= '" . $before . "'";
    }
    //通过输入值查询期限
    if (!empty($status)) {
        switch ($status) {
            case 'stored':
                $where .= " AND returnDate IS NOT NULL AND status = 'normal'";
                break;
            case 'borrow':
                $where .= " AND returnDate IS NULL AND status = 'normal'";
                break;
            case 'defaulted':
                $where .= " AND returnDate IS NULL AND UNIX_TIMESTAMP(borrowDate) < " . (time() - $defaultTime);
                break;
            case 'lost':
                $where .= "  AND status = 'lost'";
                break;
        }
    }
    $sql = "SELECT * FROM borrow $where LIMIT " . (($page - 1) * $pageSize) .", $pageSize;";
    $borrowResult = $conn->query($sql);
    $borrows = mysqli_fetch_all($borrowResult, MYSQLI_ASSOC);

    $pageResult = $conn->query("SELECT (COUNT(1) / " . $pageSize . ") `pages` FROM borrow " . $where);
    $pageInfo = mysqli_fetch_assoc($pageResult);
    $maxPage = max(1, ceil($pageInfo['pages']));

    return $borrows;
}

/***
 * @param $conn
 * @param $userId
 * @param $page
 * @param $pageSize
 * @param $maxPage
 * @param int $defaultTime
 * @return array
 */
function getRates($conn, $userId, $page, $pageSize, &$maxPage, $defaultTime = 2592000){
    $query = $_SESSION['rate']["$userId"]['query'];
    $after = $_SESSION['rate']["$userId"]['after'];
    $before = $_SESSION['rate']["$userId"]['before'];
    $status = $_SESSION['rate']["$userId"]['status'];

    //建立查询语句
    //通过输入值查询是否是编号
    $where = ($userId == -1) ? "WHERE 1 = 1 " : "WHERE userId =" . $userId;
    if (!empty($query)) {
        $where .= " AND ( 1 = 0 ";
        //通过输入值查询是否是bookName
        $where .= bookQueryByKeyword($conn, $query);
        //通过输入值查询是否是content
        $rateResult = $conn->query("SELECT * FROM book_rate WHERE content LIKE '%" . $query . "%'");
        if ($rateResult != false) {
            $rateIds = mysqli_fetch_all($rateResult, MYSQLI_ASSOC);
            $rateIds = getArrays($rateIds, "id");
            $where .= (!empty($rateIds) ? "OR id IN (" . implode(",", $rateIds) . ") " : "");
        }
        if ($userId == -1) {
            //通过输入值查询是否是user.
            $userResult = $conn->query("SELECT * FROM reader WHERE name LIKE '%" . $query . "%'");
            if ($userResult != false) {
                $userIds = mysqli_fetch_all($userResult, MYSQLI_ASSOC);
                $userIds = getArrays($userIds, "id");
                $where .= (!empty($userIds) ? "OR userId IN (" . implode(",", $userIds) . ") " : "");
            }
        }
        $where .= ")";
    }
    if (!empty($after)) {
        $where .= " AND created >= '" . $after . "'";
    }
    if (!empty($before)) {
        $where .= " AND created <= '" . $before . "'";
    }
    //通过输入值查询期限
    if (!empty($status)) {
        switch ($status) {
            case 'normal':
                $where .= " AND status = 'normal'";
                break;
            case 'verifying':
                $where .= " AND status = 'verifying'";
                break;
            case 'blocked':
                $where .= " AND status = 'blocked'";
                break;
        }
    }
    $sql = "SELECT * FROM book_rate $where LIMIT " . (($page - 1) * $pageSize) .", $pageSize;";
    $rateResult = $conn->query($sql);
    $rates = mysqli_fetch_all($rateResult, MYSQLI_ASSOC);

    $pageResult = $conn->query("SELECT (COUNT(1) / " . $pageSize . ") `pages` FROM book_rate " . $where);
    $pageInfo = mysqli_fetch_assoc($pageResult);
    $maxPage = max(1, ceil($pageInfo['pages']));

    return $rates;
}

/**
 * 事务处理
 * @param mysqli $conn
 * @param array $SQLs
 * @return boolean
 */
function commit($conn, array $SQLs)
{
    /* 通过事务来使整个删除连贯, 避免中途出错导致脏数据 */
    $conn->query("SET AUTOCOMMIT=0");
    $conn->query("BEGIN");//开始事务定义
    foreach ($SQLs as $k => $v) {
        // 循环执行, 出错回滚
        if (!$conn->query($v)) {
            $conn->query("ROLLBACK");
            return $v;
        }
    }
    $conn->query("COMMIT");//执行事务//成功
    $conn->query("END");
    return true;
}

/**
 * 准许借阅
 * @param mysqli $conn
 * @param int $bookId
 * @param int $borrowId
 * @param int $uniqueCode
 * @param int $handlerId
 * @return boolean
 */
function allowBorrow($conn, $bookId, $borrowId, $uniqueCode, $handlerId)
{
    $time = date("Y-m-d", time());
    // 修改图书记录
    $sql1 = "UPDATE book_detail SET status = 'borrowed' WHERE uniqueCode= $uniqueCode";
    // 图书存数减少
    $sql2 = "UPDATE book_info SET count = count - 1 WHERE id = $bookId";
    // 修该借书记录
    $sql3 = "UPDATE borrow SET `borrowDate` = '$time', `status` ='normal', `borrowHandlerId` = $handlerId WHERE id = $borrowId";
    $SQLs = [$sql1, $sql2, $sql3];
    return commit($conn, $SQLs);
}

/**
 * 续借操作
 * @param mysqli $conn
 * @param int $bookId
 * @param int $userId
 * @param int $borrowId
 * @param int $uniqueCode
 * @param int $handlerId
 * @return boolean
 */
function continueBorrow($conn, $bookId, $userId, $borrowId, $uniqueCode, $handlerId)
{
    //不用恢复书籍状态
    //不用更新书籍数量
    $time = date("Y-m-d", time());
    $updateSql = updateBorrow($time, $handlerId, $borrowId, $uniqueCode);
    $insertSql = insertBorrow($time, $bookId, $userId, $uniqueCode, $handlerId);
    $SQLs = [$updateSql, $insertSql];
    return commit($conn, $SQLs);
}

/**
 * 删除借阅记录 / 拒绝借阅请求
 * @param mysqli $conn
 * @param int $borrowId
 * @return boolean
 */
function deleteBorrow($conn, $borrowId)
{
    //删除借书记录
    $sql = "DELETE FROM borrow WHERE id = $borrowId";
    $SQLs = [$sql];
    return commit($conn, $SQLs);
}

/**
 * 还书操作
 * @param mysqli $conn
 * @param int $borrowId
 * @param int $bookId
 * @param int $uniqueCode
 * @param int $handlerId
 * @return boolean
 */
function returnBorrow($conn, $borrowId, $bookId, $uniqueCode, $handlerId)
{
    $time = date("Y-m-d", time());
    $bookDetailSql = "UPDATE book_detail SET status = 'stored' WHERE id = " . $bookId . " AND uniqueCode = $uniqueCode";
    $bookInfoSql = "UPDATE book_info SET count = count + 1 WHERE id = " . $bookId;
    $borrowSql = updateBorrow($time, $handlerId, $borrowId, $uniqueCode);
    $SQLs = [$bookDetailSql, $bookInfoSql, $borrowSql];
    return commit($conn, $SQLs);
}

function lostBorrow($conn, $borrowId, $bookId, $uniqueCode, $handlerId)
{
    $time = date("Y-m-d", time());
    //设置书籍状态为lost
    $bookDetailSql = "UPDATE book_detail SET status = 'lost' WHERE uniqueCode = $uniqueCode";
    //更新书籍数量, 若需要删除书籍, 并从根本上减少书目, 则可以在数目修改中改动, 这个count仍然增1是为了保持数据的一致性
    $bookInfoSql = "UPDATE book_info SET count = count + 1 WHERE id = $bookId";
    //确认丢失时间
    $borrowSql = "UPDATE borrow SET `status` = 'lost', `returnDate` = .'$time' ,`returnHandlerId` = '$handlerId' WHERE id = $borrowId";
    $SQLs = [$bookDetailSql, $bookInfoSql, $borrowSql];
    return commit($conn, $SQLs);
}

/**
 * 更新借阅记录, 归还图书的sql语句
 * @param string $time
 * @param int $handlerId
 * @param int $borrowId
 * @param int $uniqueCode
 * @return string
 */
function updateBorrow($time, $handlerId, $borrowId, $uniqueCode)
{
    return "UPDATE borrow SET `status` = ( CASE "
        . "WHEN UNIX_TIMESTAMP(borrowDate) >= " . (time() - 2592000) . " THEN 'normal'"
        . "WHEN UNIX_TIMESTAMP(borrowDate) < " . (time() - 2592000) . " THEN 'defaulted'"
        . "END), `returnDate` = '$time', `returnHandlerId` = $handlerId  WHERE id = $borrowId AND uniqueCode = $uniqueCode";
}

/**
 * 新建借阅记录的sql语句
 * @param string $time
 * @param int $bookId
 * @param int $userId
 * @param int $uniqueCode
 * @param int $handlerId
 * @return string
 */
function insertBorrow($time, $bookId, $userId, $uniqueCode, $handlerId)
{
    return "INSERT INTO borrow (`borrowDate`, `bookId`, `userId`, `uniqueCode`, `status`, `borrowHandlerId`) VALUES "
        . "('$time',$bookId,$userId,$uniqueCode,'normal',$handlerId)";
}

/**
 * 删除用户
 * @param mysqli $conn
 * @param int $userId
 * @return boolean
 */
function deleteUser($conn, $userId)
{
    $accountSql = "DELETE FROM accounts WHERE id = $userId";
    $readerSql = "DELETE FROM reader WHERE id = $userId";
    $SQLs = [$accountSql, $readerSql];
    return commit($conn, $SQLs);
}


/**
 * 更新权限
 * @param mysqli $conn
 * @param array $dic
 * @return boolean
 */
function modifyAuthority($conn, $dic)
{
    if (sizeof($dic) > 0) {
        $sql = "UPDATE accounts SET authority = ( CASE ";
        foreach ($dic as $k => $v) {
            $sql .= "WHEN id = $k THEN '$v' ";
        }
         $sql .= "END ) WHERE id IN (" . implode(',', array_keys($dic)) . ")";
        $SQLs = [$sql];
        return commit($conn, $SQLs);
    }
    return true;
}

/**
 * 更新账户状态
 * @param mysqli $conn
 * @param int $userId
 * @param string $status
 * @return boolean
 */
function modifyStatus($conn, $userId, $status)
{
    $sql = "UPDATE accounts SET status = '$status' WHERE id = $userId";
    $SQLs = [$sql];
    return commit($conn, $SQLs);
}

/**
 * 新增书籍
 * 由于需要使用到前面语句中插入得到的insertId,
 * @param mysqli $conn
 * @param string $name
 * @param string $press
 * @param string $press_time
 * @param string $author
 * @param int $price
 * @param string $ISBN
 * @param string $desc
 * @param int $category
 * @param int $count
 * @param int $last
 * @return boolean
 */
function addBook($conn, $name, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count, $last)
{
    /* 通过事务来使整个删除连贯, 避免中途出错导致脏数据 */
    $conn->query("SET AUTOCOMMIT=0");
    $conn->query("BEGIN");//开始事务定义

    $bookInfoSql = insertBookInfo($name, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count);
    if (!$conn->query($bookInfoSql)) {
        $conn->query("ROLLBACK");
        return false;
    }
    $details = $count - $last;
    if ($details > 0) {
        $bookDetailSql = insertBookDetail(mysqli_insert_id($conn), $details);
        //$conn->query($bookDetailSql);
        //需要做事务回滚检查
        if (!$conn->query($bookDetailSql)) {
            $conn->query("ROLLBACK");
            return false;
        }
    }
    $conn->query("COMMIT");//执行事务//成功
    $conn->query("END");
    return true;
}

/**
 * 插入图书信息sql语句
 * @param string $name
 * @param string $press
 * @param string $press_time
 * @param string $author
 * @param int $price
 * @param string $ISBN
 * @param string $desc
 * @param int $category
 * @param int $count
 * @return string
 */
function insertBookInfo($name, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count)
{
    return "INSERT INTO book_info ( `name`,`press`,`press_time`,`author`,`price`,`ISBN`,`desc`,`category`,`count`) VALUES ('$name','$press','$press_time','$author',$price,'$ISBN','$desc',$category,$count)";
}

/**
 * 插入图书书目sql语句
 * @param int $bookId
 * @param int $details 数目数量，而非书目条目
 * @return string
 */
function insertBookDetail($bookId, $details)
{
    $sql = "INSERT INTO book_detail (`id`) VALUES ";
    for ($i = 0; $i < ($details - 1); $i++) {
        $sql .= "($bookId),";
    }
    $sql .= "($bookId);";
    return $sql;
}

/**
 * 更新图书
 * @param mysqli $conn
 * @param string $name
 * @param int $bookId
 * @param string $press
 * @param string $press_time
 * @param string $author
 * @param int $price
 * @param string $ISBN
 * @param string $desc
 * @param int $category
 * @param int $count
 * @param int $last
 * @param array $deletes
 * @return boolean
 */
function updateBook($conn, $name, $bookId, $press, $press_time, $author, $price, $ISBN, $desc, $category, $count, $last, $deletes)
{
    $SQLs = [];
    if (sizeof($deletes) > 0) {
        $deleteDetailSql = "DELETE FROM book_detail WHERE uniqueCode IN (" . implode(",", $deletes) . ")";
        $SQLs[] = $deleteDetailSql;
    }
    //批量插入数目信息
    $details = $count - $last;
    if ($details > 0) {
        $SQLs[] = insertBookDetail($bookId, $details);
    }
    //修改书籍信息
    $bookInfoSql = "UPDATE book_info SET name = '$name', author = '$author', press = '$press', press_time = '$press_time', price = $price,  ISBN = '$ISBN'"
        . ",`desc` = '$desc', category = $category, count = " . ($count - sizeof($deletes)) . "  WHERE id = $bookId";
    $SQLs[] = $bookInfoSql;
    return commit($conn, $SQLs);
}

/**
 * 删除图书
 * @param mysqli $conn
 * @param int $bookId
 * @return boolean
 */
function deleteBook($conn, $bookId)
{
    $borrowSql = "DELETE FROM borrow WHERE id= $bookId";
    // 删除所有书目
    $detailSql = "DELETE FROM book_detail WHERE id= $bookId";
    // 删除书本
    $infoSql = "DELETE FROM book_info WHERE id= $bookId";
    $SQLs = [$borrowSql, $detailSql, $infoSql];
    return commit($conn, $SQLs);
}

/**
 * 插入分类
 * @param mysqli $conn
 * @param string $category
 * @return boolean
 */
function insertCategory($conn, $category)
{
    $sql = "INSERT INTO category (`category`) VALUES ('$category')";
    $SQLs = [$sql];
    return commit($conn, $SQLs);
}

/**
 * 批量更新分类
 * @param mysqli $conn
 * @param $dic
 * @return bool
 */
function updateCategories(mysqli $conn, $dic)
{
    if (sizeof($dic) > 0) {
        $sql = "UPDATE category SET category = ( CASE ";
        foreach ($dic as $k => $v) {
            $sql .= " WHEN id = $k THEN '$v' ";
        }
        $sql .= " END ) WHERE id IN (" . implode(",", array_keys($dic)) . ")";
        $SQLs = [$sql];
        return commit($conn, $SQLs);
    }
    return true;
}

/**
 * 删除分类
 * @param mysqli $conn
 * @param int $categoryId
 * @return boolean
 */
function deleteCategory($conn, $categoryId)
{
    $infoSql = "UPDATE book_info SET `category` = -1 WHERE `category` = $categoryId";
    $categorySql = "DELETE FROM category WHERE id = $categoryId";
    $SQLs = [$infoSql, $categorySql];
    return commit($conn, $SQLs);
}
