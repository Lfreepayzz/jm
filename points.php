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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_key = trim($_POST['card_key']);
    
    if (empty($card_key)) {
        $error = '请输入卡密';
    } else {
        try {
            // 检查卡密是否存在且未使用
            $stmt = $pdo->prepare("SELECT id, points, used FROM point_cards WHERE card_key = ?");
            $stmt->execute([$card_key]);
            $card = $stmt->fetch();
            
            if (!$card) {
                $error = '卡密不存在';
            } elseif ($card['used']) {
                $error = '卡密已被使用';
            } else {
                // 更新卡密状态
                $stmt = $pdo->prepare("UPDATE point_cards SET used = 1, used_by = ?, used_at = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $card['id']]);
                
                // 更新用户积分
                $new_points = $_SESSION['points'] + $card['points'];
                $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
                $stmt->execute([$new_points, $_SESSION['user_id']]);
                $_SESSION['points'] = $new_points;
                
                $success = '积分充值成功！获得 ' . $card['points'] . ' 积分，当前总积分: ' . $new_points;
            }
        } catch (Exception $e) {
            $error = '充值过程中出现错误: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>积分充值 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 20px 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-top: 0;
            color: #ecf0f1;
            font-size: 1.5em;
            padding-bottom: 15px;
            border-bottom: 1px solid #34495e;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px 20px;
        }
        .sidebar ul li a {
            color: #bdc3c7;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background: linear-gradient(90deg, #3498db, #2980b9);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #3498db, #2c80c5);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 600;
        }
        .header a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .header a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .content h2 {
            margin-top: 0;
            color: #2c3e50;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        .btn:hover {
            background: linear-gradient(135deg, #2980b9, #2573a7);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #27ae60, #219653);
            box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #219653, #1e8449);
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.4);
        }
        .error {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        .success {
            background: linear-gradient(135deg, #27ae60, #219653);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);
        }
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .instructions h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .instructions ul {
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>用户中心</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="encrypt.php">代码加密</a></li>
                <li><a href="decrypt.php">代码解密</a></li>
                <li><a href="points.php" class="active">积分充值</a></li>
                <li><a href="profile.php">个人资料</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>积分充值</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>使用卡密充值积分</h2>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="card_key">卡密:</label>
                        <input type="text" id="card_key" name="card_key" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">充值积分</button>
                </form>
                
                <div class="instructions">
                    <h3>充值说明</h3>
                    <ul>
                        <li>请输入您购买的卡密进行积分充值</li>
                        <li>充值成功后积分将立即到账</li>
                        <li>如有问题请联系管理员</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>