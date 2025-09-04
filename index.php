<?php
// 检查系统是否已安装
if (!file_exists('./config/database.php') || !file_exists('./includes/init.php')) {
    // 系统未安装，重定向到安装向导
    header("Location: install/");
    exit();
} else {
    // 系统已安装，重定向到用户登录页面
    header("Location: user/login.php");
    exit();
}
?>