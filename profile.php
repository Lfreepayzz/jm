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
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/init.php';

$error = '';
$success = '';

// 获取用户信息
try {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    $error = '获取用户信息时出错: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // 验证输入
    if (empty($email)) {
        $error = '请填写邮箱地址';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } else {
        try {
            // 更新邮箱
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            // 检查是否需要更新密码
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error = '如需修改密码，请填写所有密码字段';
                } elseif (strlen($new_password) < 6) {
                    $error = '新密码长度至少6位';
                } elseif ($new_password !== $confirm_password) {
                    $error = '两次输入的新密码不一致';
                } else {
                    // 验证当前密码
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user_data = $stmt->fetch();
                    
                    if (!password_verify($current_password, $user_data['password'])) {
                        $error = '当前密码不正确';
                    } else {
                        // 更新密码
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                        
                        $success = '个人信息和密码更新成功！';
                    }
                }
            } else {
                $success = '个人信息更新成功！';
            }
            
            // 更新会话中的邮箱（如果需要）
            if (empty($error) && !empty($success)) {
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $updated_user = $stmt->fetch();
                $user['email'] = $updated_user['email'];
            }
        } catch (Exception $e) {
            $error = '更新信息时出错: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人资料 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>用户中心</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="encrypt.php">代码加密</a></li>
                <li><a href="decrypt.php">代码解密</a></li>
                <li><a href="points.php">积分充值</a></li>
                <li><a href="profile.php" class="active">个人资料</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>个人资料</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>编辑个人资料</h2>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">用户名:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">邮箱地址:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">当前密码 (如需更改密码):</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">新密码:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">确认新密码:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-success">更新资料</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>