<?php
// 自动加载文件
session_start();

// 数据库连接
require_once __DIR__ . '/../config/database.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// 获取系统配置
function get_system_config($key, $default = null) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT config_value FROM system_config WHERE config_key = ?');
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['config_value'] : $default;
}

// 更新系统配置
function update_system_config($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO system_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?');
    return $stmt->execute([$key, $value, $value]);
}

// 用户认证检查
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// 管理员认证检查
function is_admin() {
    return isset($_SESSION['admin_id']);
}
?>