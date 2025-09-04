<?php
session_start();

// 如果没有配置信息，重定向到第一步
if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_config'])) {
    header('Location: step1.php');
    exit();
}

// 如果已经完成安装，重定向到首页
if (isset($_SESSION['installed']) && $_SESSION['installed']) {
    header('Location: ../admin/');
    exit();
}

$error = '';
$success = '';

// 执行安装过程
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 获取数据库配置
        $db_config = $_SESSION['db_config'];
        
        // 创建数据库连接
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // 创建数据库（如果不存在）
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // 重新连接到指定数据库
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // 创建数据表
        $sql = "
        -- 用户表
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `email` varchar(100) DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `points` int(11) DEFAULT 0,
            `role` enum('user','admin') DEFAULT 'user',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- 管理员表
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- 积分卡密表
        CREATE TABLE IF NOT EXISTS `point_cards` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `card_key` varchar(100) NOT NULL UNIQUE,
            `points` int(11) NOT NULL,
            `used` tinyint(1) DEFAULT 0,
            `used_by` int(11) DEFAULT NULL,
            `used_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- 加密任务表
        CREATE TABLE IF NOT EXISTS `encrypt_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `original_file` varchar(255) NOT NULL,
            `encrypted_file` varchar(255) NOT NULL,
            `algorithm` varchar(50) NOT NULL,
            `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- 解密任务表
        CREATE TABLE IF NOT EXISTS `decrypt_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `encrypted_file` varchar(255) NOT NULL,
            `decrypted_file` varchar(255) NOT NULL,
            `algorithm` varchar(50) NOT NULL,
            `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        -- 系统配置表
        CREATE TABLE IF NOT EXISTS `system_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(100) NOT NULL UNIQUE,
            `config_value` text,
            `description` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sql);
        
        // 插入管理员账户
        $admin_config = $_SESSION['admin_config'];
        $stmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password`) VALUES (?, ?)");
        $stmt->execute([$admin_config['username'], $admin_config['password']]);
        
        // 插入默认系统配置
        $configs = [
            ['upload_max_size', '2097152', '上传文件最大大小（字节）'],
            ['default_algorithm', 'enphp', '默认加密算法'],
            ['points_per_encrypt', '10', '每次加密消耗积分'],
            ['points_per_decrypt', '5', '每次解密消耗积分'],
            ['registration_points', '100', '注册赠送积分'],
            ['site_name', 'PHP加密解密系统', '网站名称']
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO `system_config` (`config_key`, `config_value`, `description`) VALUES (?, ?, ?)");
        foreach ($configs as $config) {
            $stmt->execute($config);
        }
        
        // 创建配置文件
        $config_content = "<?php
// 数据库配置
define('DB_HOST', '" . addslashes($db_config['host']) . "');
define('DB_PORT', '" . addslashes($db_config['port']) . "');
define('DB_NAME', '" . addslashes($db_config['name']) . "');
define('DB_USER', '" . addslashes($db_config['user']) . "');
define('DB_PASS', '" . addslashes($db_config['pass']) . "');

// 系统配置
define('UPLOAD_MAX_SIZE', 2097152); // 2MB
define('SITE_NAME', 'PHP加密解密系统');
?>
";
        
        // 确保config目录存在
        if (!is_dir('../config')) {
            mkdir('../config', 0755, true);
        }
        
        file_put_contents('../config/database.php', $config_content);
        
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
        
        file_put_contents('../includes/init.php', $includes_content);
        
        // 创建加密算法类文件
        $encryption_content = "<?php
class Encryption {
    private \$algorithm;
    
    public function __construct(\$algorithm = 'enphp') {
        \$this->algorithm = \$algorithm;
    }
    
    public function encrypt(\$code) {
        switch (\$this->algorithm) {
            case 'eval':
                return \$this->evalEncrypt(\$code);
            case 'goto':
                return \$this->gotoEncrypt(\$code);
            case 'enphp':
                return \$this->enphpEncrypt(\$code);
            case 'enphpv2':
                return \$this->enphpV2Encrypt(\$code);
            case 'jiamiZym':
                return \$this->jiamiZymEncrypt(\$code);
            case 'magicTwo':
                return \$this->magicTwoEncrypt(\$code);
            case 'noname1':
                return \$this->noname1Encrypt(\$code);
            case 'phpjm':
                return \$this->phpjmEncrypt(\$code);
            case 'phpjm2':
                return \$this->phpjm2Encrypt(\$code);
            default:
                throw new Exception('未知的加密算法: ' . \$this->algorithm);
        }
    }
    
    public function decrypt(\$code) {
        // 解密功能实现
        // 注意：某些算法可能是单向的或需要密钥，这里仅作示例
        return base64_decode(\$code);
    }
    
    private function evalEncrypt(\$code) {
        return 'eval(base64_decode(\"' . base64_encode(\$code) . '\"));';
    }
    
    private function gotoEncrypt(\$code) {
        // 简化的goto加密示例
        \$encoded = base64_encode(\$code);
        return 'goto a; die; a: eval(base64_decode(\"' . \$encoded . '\"));';
    }
    
    private function enphpEncrypt(\$code) {
        // 简化的enphp加密示例
        \$encoded = str_rot13(base64_encode(\$code));
        return 'eval(str_rot13(base64_decode(\"' . \$encoded . '\")));';
    }
    
    private function enphpV2Encrypt(\$code) {
        // 简化的enphpv2加密示例
        \$encoded = gzdeflate(base64_encode(\$code));
        \$encoded = base64_encode(\$encoded);
        return 'eval(base64_decode(gzinflate(base64_decode(\"' . \$encoded . '\"))));';
    }
    
    private function jiamiZymEncrypt(\$code) {
        // 简化的jiamiZym加密示例
        \$encoded = str_rot13(gzdeflate(\$code));
        \$encoded = base64_encode(\$encoded);
        return 'eval(gzinflate(str_rot13(base64_decode(\"' . \$encoded . '\"))));';
    }
    
    private function magicTwoEncrypt(\$code) {
        // 简化的magicTwo加密示例
        \$encoded = gzencode(base64_encode(\$code));
        \$encoded = str_rot13(\$encoded);
        return 'eval(base64_decode(gzdecode(str_rot13(\"' . \$encoded . '\"))));';
    }
    
    private function noname1Encrypt(\$code) {
        // 简化的noname1加密示例
        \$encoded = strrev(base64_encode(\$code));
        return 'eval(base64_decode(strrev(\"' . \$encoded . '\")));';
    }
    
    private function phpjmEncrypt(\$code) {
        // 简化的phpjm加密示例
        \$encoded = gzcompress(base64_encode(\$code));
        \$encoded = str_rot13(\$encoded);
        return 'eval(base64_decode(gzuncompress(str_rot13(\"' . \$encoded . '\"))));';
    }
    
    private function phpjm2Encrypt(\$code) {
        // 简化的phpjm2加密示例
        \$encoded = gzdeflate(str_rot13(base64_encode(\$code)));
        \$encoded = base64_encode(\$encoded);
        return 'eval(str_rot13(base64_decode(gzinflate(base64_decode(\"' . \$encoded . '\")))));';
    }
}
?>
";
        
        file_put_contents('../includes/Encryption.php', $encryption_content);
        
        // 标记安装完成
        $_SESSION['installed'] = true;
        
        // 重定向到完成页面
        header('Location: finished.php');
        exit();
    } catch (Exception $e) {
        $error = '安装过程中出现错误: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>完成安装 - PHP加密解密系统安装向导</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .process {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP加密解密系统</h1>
            <p>安装向导 - 第3步：完成安装</p>
        </div>
        <div class="content">
            <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="process">
                <h3>即将完成安装</h3>
                <p>点击下面的按钮完成系统的最终安装：</p>
                <ul>
                    <li>创建数据库和数据表</li>
                    <li>插入初始数据</li>
                    <li>创建配置文件</li>
                    <li>创建核心类文件</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="navigation">
                    <a href="step2.php" class="btn btn-secondary">上一步</a>
                    <button type="submit" class="btn">完成安装</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>