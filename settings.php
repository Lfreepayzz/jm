<?php
// 检查系统是否已安装（使用绝对路径检查）
$basePath = dirname(__DIR__);
if (!file_exists($basePath . '/config/database.php') || !file_exists($basePath . '/includes/init.php')) {
    // 系统未安装，重定向到安装向导
    header('Location: ../install/');
    exit();
}

session_start();

// 检查是否已登录
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// 包含初始化文件
require_once '../includes/init.php';

$error = '';
$success = '';

// 获取当前配置
$upload_max_size = get_system_config('upload_max_size', 2097152);
$default_algorithm = get_system_config('default_algorithm', 'enphp');
$points_per_encrypt = get_system_config('points_per_encrypt', 10);
$points_per_decrypt = get_system_config('points_per_decrypt', 5);
$registration_points = get_system_config('registration_points', 100);
$site_name = get_system_config('site_name', 'PHP加密解密系统');

// 支持的加密算法
$algorithms = [
    'eval' => 'Eval加密',
    'goto' => 'Goto加密',
    'enphp' => 'EnPHP加密',
    'enphpv2' => 'EnPHP V2加密',
    'jiamiZym' => 'JiamiZym加密',
    'magicTwo' => 'MagicTwo加密',
    'noname1' => 'NoName1加密',
    'phpjm' => 'PHPJM加密',
    'phpjm2' => 'PHPJM2加密'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_max_size = (int)$_POST['upload_max_size'];
    $default_algorithm = $_POST['default_algorithm'];
    $points_per_encrypt = (int)$_POST['points_per_encrypt'];
    $points_per_decrypt = (int)$_POST['points_per_decrypt'];
    $registration_points = (int)$_POST['registration_points'];
    $site_name = $_POST['site_name'];
    
    try {
        // 更新配置
        update_system_config('upload_max_size', $upload_max_size);
        update_system_config('default_algorithm', $default_algorithm);
        update_system_config('points_per_encrypt', $points_per_encrypt);
        update_system_config('points_per_decrypt', $points_per_decrypt);
        update_system_config('registration_points', $registration_points);
        update_system_config('site_name', $site_name);
        
        $success = '系统配置更新成功！';
    } catch (Exception $e) {
        $error = '更新配置时出错: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>管理后台</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="cards.php">卡密管理</a></li>
                <li><a href="settings.php" class="active">系统设置</a></li>
                <li><a href="encrypt.php">加密功能</a></li>
                <li><a href="decrypt.php">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>系统设置</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>系统配置</h2>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="site_name">网站名称:</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($configs['site_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="upload_max_size">上传文件大小限制 (字节):</label>
                        <input type="number" id="upload_max_size" name="upload_max_size" value="<?php echo htmlspecialchars($configs['upload_max_size']); ?>" required>
                        <small>当前值约为 <?php echo round($configs['upload_max_size'] / 1024 / 1024, 2); ?> MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="default_algorithm">默认加密算法:</label>
                        <select id="default_algorithm" name="default_algorithm">
                            <?php foreach ($algorithms as $key => $name): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($configs['default_algorithm'] === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="points_per_encrypt">每次加密消耗积分:</label>
                        <input type="number" id="points_per_encrypt" name="points_per_encrypt" value="<?php echo htmlspecialchars($configs['points_per_encrypt']); ?>" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="points_per_decrypt">每次解密消耗积分:</label>
                        <input type="number" id="points_per_decrypt" name="points_per_decrypt" value="<?php echo htmlspecialchars($configs['points_per_decrypt']); ?>" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="registration_points">注册赠送积分:</label>
                        <input type="number" id="registration_points" name="registration_points" value="<?php echo htmlspecialchars($configs['registration_points']); ?>" min="0" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">保存设置</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>