<?php
session_start();

// 如果没有数据库配置，重定向到第一步
if (!isset($_SESSION['db_config'])) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_username = trim($_POST['admin_username']);
    $admin_password = trim($_POST['admin_password']);
    $admin_password_confirm = trim($_POST['admin_password_confirm']);
    
    if (empty($admin_username) || empty($admin_password)) {
        $error = '请填写所有必填字段';
    } elseif (strlen($admin_username) < 3) {
        $error = '管理员用户名至少需要3个字符';
    } elseif (strlen($admin_password) < 6) {
        $error = '管理员密码至少需要6个字符';
    } elseif ($admin_password !== $admin_password_confirm) {
        $error = '两次输入的密码不一致';
    } else {
        // 保存管理员信息到会话
        $_SESSION['admin_config'] = [
            'username' => $admin_username,
            'password' => password_hash($admin_password, PASSWORD_BCRYPT)
        ];
        
        header('Location: step3.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员账户设置 - PHP加密解密系统安装向导</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP加密解密系统</h1>
            <p>安装向导 - 第2步：管理员账户设置</p>
        </div>
        <div class="content">
            <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="admin_username">管理员用户名 *</label>
                    <input type="text" id="admin_username" name="admin_username" value="<?php echo isset($_POST['admin_username']) ? htmlspecialchars($_POST['admin_username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">管理员密码 *</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                    <small>至少6个字符</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm">确认密码 *</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
                </div>
                
                <div class="navigation">
                    <a href="step1.php" class="btn btn-secondary">上一步</a>
                    <button type="submit" class="btn">下一步</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>