<?php
if (empty($_GET['id'])){
    if ($pageType != 'admin'){
        header("Location: error.php?error=未输入必要参数进入页面");
        exit;
    }
    $userId = -1; //此处用于在查询是否有必须的用户信息时留作的缓存信息, 当页面类型为admin时, 需要获取的信息为所有用户的信息。
}
else{
    $userId = $_GET['id'];
}
return $userId;