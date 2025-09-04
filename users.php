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

require_once '../includes/init.php';

// 获取用户列表
try {
    $stmt = $pdo->query("SELECT id, username, email, points, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $error = '获取用户列表时出错: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>管理后台</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="users.php" class="active">用户管理</a></li>
                <li><a href="cards.php">卡密管理</a></li>
                <li><a href="settings.php">系统设置</a></li>
                <li><a href="encrypt.php">加密功能</a></li>
                <li><a href="decrypt.php">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>用户管理</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>用户列表</h2>
                
                <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">用户名</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">邮箱</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">积分</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">角色</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">注册时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($users) && !empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['points']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="border: 1px solid #ddd; padding: 8px; text-align: center;">暂无用户</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>