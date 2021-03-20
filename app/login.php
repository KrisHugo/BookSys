<?php
require_once "conn.php";
/* 检测必要的输入参数 */
if(!empty($_POST['account']) && !empty($_POST['password'])){
    $account = $_POST['account'];
    $password = $_POST['password'];
    //账户检验
	$userResult = $conn->query("SELECT id, username, authority, status FROM accounts "
	    ."WHERE account='$account' AND password='$password'");
	if($userResult->num_rows > 0){
	    $user = mysqli_fetch_assoc($userResult);
	    if ($user['status'] == 'access'){
	        /* 使用Session来保存数据 */
	        $_SESSION['account']['user'] = $user['username'];
	        $_SESSION['account']['user_id'] = $user['id'];
	        $_SESSION['account']['user_authority'] = $user['authority'];
	        /* 通过权限级别来判断 登陆跳转页面 */
	        if($user['authority'] == 'admin'){
	            /* 管理页面 */
	            header('Location: ../backend.php');
	        }
            else{
                /* 首页 */
                header('Location: ../index.php');
            }
            exit;
	    }
	    else{
	        /* 错误处理: 账户已被禁用 */
	        header('Location: ../login.php?error=b');
	        exit;
	    }
	}
	else{
	    /* 错误处理: 密码错误 */
		header('Location: ../login.php?error=e');
	    exit;
	}
}
?>