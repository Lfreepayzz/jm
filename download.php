<?php
// 检查系统是否已安装（使用绝对路径检查）
$basePath = __DIR__;
if (!file_exists($basePath . '/config/database.php') || !file_exists($basePath . '/includes/init.php')) {
    // 系统未安装，返回403错误
    header('HTTP/1.0 403 Forbidden');
    die('系统尚未安装');
}

// 文件下载处理页面
session_start();

// 检查用户是否已登录（用户或管理员）
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header('HTTP/1.0 403 Forbidden');
    die('访问被拒绝');
}

if (!isset($_GET['file'])) {
    header('HTTP/1.0 400 Bad Request');
    die('缺少文件参数');
}

$filename = basename($_GET['file']);
$filepath = __DIR__ . '/uploads/' . $filename;

// 检查文件是否存在
if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    die('文件不存在');
}

// 检查文件是否在uploads目录中（防止路径遍历攻击）
$realPath = realpath($filepath);
$uploadPath = realpath(__DIR__ . '/uploads');

if (!$realPath || strpos($realPath, $uploadPath) !== 0) {
    header('HTTP/1.0 403 Forbidden');
    die('非法文件访问');
}

// 设置下载头信息
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// 清除缓冲区并输出文件
ob_clean();
flush();
readfile($filepath);
exit;
?>