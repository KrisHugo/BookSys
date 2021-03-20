<?php
require_once 'conn.php';
/* 仅有输入内容均填入才能进入 */
if (!empty($_POST['account']) && !empty($_POST['password']) && !empty($_POST['confirm'])){
    /* 需要检验账户名不能相同 */
    $accountResult = $conn->query("SELECT * FROM accounts WHERE account = '".$_POST['account']."'");
    if(!$accountResult || $accountResult->num_rows > 0){
        header("Location:../register.php?error=r");
        exit;
    }
    $stuIdResult = $conn->query("SELECT * FROM reader WHERE studentId = '".$_POST['studentID']."'");
    if (!$stuIdResult || $stuIdResult->num_rows >0){
        header("Location:../register.php?error=v");
        exit;
    }
    /* 由于是选项，html页面无法判断有无选中 */
    if (empty($_POST['college'])){
        header("Location: ../register.php?error=c");
        exit;
    }
    /* 检查密码是否同一 */
    if ($_POST['password'] != $_POST['confirm']){
        header("Location: ../register.php?error=s");
        exit;
    }
    /* 开始检验输入内容有效性 */
    $accountPreg = '/^[a-zA-Z0-9_]{6,18}$/';
    $passwordPreg = '/^[a-zA-Z0-9_]{8,16}$/';
    $namePreg = '/^[\x4e00-\x9fa5]{1,5}$/u';
    $stuIdPreg = '/^[0-9]{12}$/';
    $phonePreg = '/^1[3456789]\d{9}$/';
    if(!preg_match($accountPreg, $_POST['account'])){
        header("Location: ../register.php?error=a");
        exit;
    }
    /* 检测密码是否合规 */
    elseif (!preg_match($passwordPreg, $_POST['password'])){
        header("Location: ../register.php?error=p");
        exit;
    }
//暂时不检测
//    /* 检测姓名是否合规 */
//    elseif (empty($_POST['name']) || !preg_match($namePreg, $_POST['name'])){
//        header("Location: ../register.php?error=n");
//        exit;
//    }
    /* 检测学号是否合规 */
    elseif (empty($_POST['studentID']) || !preg_match($stuIdPreg, $_POST['studentID'])){
        header("Location: ../register.php?error=s");
        exit;
    }
    /* 当检测到输入电话后, 需检测电话是否合规 */
    elseif (!empty($_POST['phone']) && !preg_match($phonePreg, $_POST['phone'])){
        header("Location: ../register.php?error=t");
        exit;
    }
    /* 创建账户登陆 */
    else {
        /* 通过事务来使整个删除连贯, 避免中途出错导致脏数据 */
        $conn->query("SET AUTOCOMMIT=0");
        $conn->query("BEGIN");//开始事务定义
        $accountSql = "INSERT INTO accounts (username, account, password, authority) VALUES ('"
            . $_POST['name'] ."','" .$_POST['account'] ."','".$_POST['password']."','user')";
        $accountQuery = $conn->query($accountSql);
        /* 获取新插入记录的ID */
        $insertId = mysqli_insert_id($conn);
        $readerSql = "INSERT INTO reader (id, name, studentID, ".(!empty($_POST['phone'])?"phone,":"")." college) VALUES ("
            .$insertId.",'".$_POST['name']."','".$_POST['studentID']."','".(!empty($_POST['phone'])?$_POST['phone']."','":"").$_POST['college']."')";
        $readerQuery = $conn->query($readerSql);
        if(!$accountQuery|| !$readerQuery){//至少有一个不成功
            $conn->query("ROLLBACK");//判断执行失败回滚
            
            header("Location: ../error.php?error=数据库操作 $accountSql && $readerSql 失败");
            exit;
        }
        $conn->query("COMMIT");//执行事务//成功
        $conn->query("END");
        /* 记录缓存 */
        $_SESSION['account']['user'] = $_POST['name'];
        $_SESSION['account']['user_id'] = $insertId;
        $_SESSION['account']['user_authority'] = 'user';
        /* 登陆跳转 */
        header("Location: ../index.php");
        exit;
    }
} else {
    /* 错误处理 */
    header("Location: ../register.php?error=f");
    exit;
}
    
