<?php
// 手动创建配置文件的脚本

// 确保目录存在
if (!is_dir('config')) {
    mkdir('config', 0755, true);
}

if (!is_dir('includes')) {
    mkdir('includes', 0755, true);
}

if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

// 创建数据库配置文件示例
$config_content = "<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'encryption_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// 系统配置
define('UPLOAD_MAX_SIZE', 2097152); // 2MB
define('SITE_NAME', 'PHP加密解密系统');
?>
";

file_put_contents('config/database.php', $config_content);

// 创建包含文件
$includes_content = "<?php
// 自动加载文件
session_start();

// 数据库连接
require_once __DIR__ . '/../config/database.php';

try {
    \$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException \$e) {
    die('数据库连接失败: ' . \$e->getMessage());
}

// 获取系统配置
function get_system_config(\$key, \$default = null) {
    global \$pdo;
    \$stmt = \$pdo->prepare('SELECT config_value FROM system_config WHERE config_key = ?');
    \$stmt->execute([\$key]);
    \$result = \$stmt->fetch();
    return \$result ? \$result['config_value'] : \$default;
}

// 更新系统配置
function update_system_config(\$key, \$value) {
    global \$pdo;
    \$stmt = \$pdo->prepare('INSERT INTO system_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?');
    return \$stmt->execute([\$key, \$value, \$value]);
}

// 用户认证检查
function is_logged_in() {
    return isset(\$_SESSION['user_id']);
}

// 管理员认证检查
function is_admin() {
    return isset(\$_SESSION['admin_id']);
}
?>
";

file_put_contents('includes/init.php', $includes_content);

echo "配置文件已创建成功！\n";
echo "请修改 config/database.php 文件中的数据库连接信息。\n";
?>