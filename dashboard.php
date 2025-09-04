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

// 获取系统统计信息
try {
    // 用户总数
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $users_count = $stmt->fetch()['count'];
    
    // 加密任务总数
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM encrypt_tasks");
    $encrypt_count = $stmt->fetch()['count'];
    
    // 解密任务总数
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM decrypt_tasks");
    $decrypt_count = $stmt->fetch()['count'];
    
    // 卡密总数
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM point_cards");
    $cards_count = $stmt->fetch()['count'];
} catch (Exception $e) {
    $error = '获取统计数据时出错: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>管理后台</h2>
            <ul>
                <li><a href="dashboard.php" class="active">仪表板</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="cards.php">卡密管理</a></li>
                <li><a href="settings.php">系统设置</a></li>
                <li><a href="encrypt.php">加密功能</a></li>
                <li><a href="decrypt.php">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>仪表板</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <h3>用户总数</h3>
                    <div class="number"><?php echo $users_count ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>加密任务</h3>
                    <div class="number"><?php echo $encrypt_count ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>解密任务</h3>
                    <div class="number"><?php echo $decrypt_count ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>卡密总数</h3>
                    <div class="number"><?php echo $cards_count ?? 0; ?></div>
                </div>
            </div>
            
            <div class="welcome">
                <h2>欢迎使用PHP加密解密系统</h2>
                <p>这是一个功能强大的PHP代码加密与解密系统，支持多种加密算法。</p>
                <p>通过左侧菜单可以管理系统的各个方面：</p>
                <ul>
                    <li><strong>用户管理</strong> - 管理系统用户</li>
                    <li><strong>卡密管理</strong> - 生成和管理积分卡密</li>
                    <li><strong>系统设置</strong> - 配置系统参数</li>
                    <li><strong>加密功能</strong> - 在线加密PHP代码</li>
                    <li><strong>解密功能</strong> - 在线解密PHP代码</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>