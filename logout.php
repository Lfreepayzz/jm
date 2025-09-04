<?php
// 检查系统是否已安装（使用绝对路径检查）
$basePath = dirname(__DIR__);
if (!file_exists($basePath . '/config/database.php') || !file_exists($basePath . '/includes/init.php')) {
    // 系统未安装，重定向到安装向导
    header('Location: ../install/');
    exit();
}

session_start();

// 销毁所有会话数据
session_destroy();

// 重定向到登录页面
header('Location: index.php');
exit();
?>