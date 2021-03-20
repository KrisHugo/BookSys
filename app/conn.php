<?php
     $conn=mysqli_connect("localhost","root","root3306") or die("数据库服务器连接错误".mysqli_error());
     mysqli_select_db($conn,"BookSys") or die("数据库访问错误".mysqli_error());
     mysqli_query($conn,"set names utf8");
?>