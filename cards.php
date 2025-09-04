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

$error = '';
$success = '';

// 生成卡密
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $points = (int)$_POST['points'];
    $count = (int)$_POST['count'];
    
    if ($points <= 0 || $count <= 0) {
        $error = '积分和数量必须大于0';
    } else {
        try {
            // 批量生成卡密
            $stmt = $pdo->prepare("INSERT INTO point_cards (card_key, points, used) VALUES (?, ?, 0)");
            for ($i = 0; $i < $count; $i++) {
                $card_key = 'CARD-' . strtoupper(bin2hex(random_bytes(10)));
                $stmt->execute([$card_key, $points]);
            }
            
            $success = '成功生成 ' . $count . ' 个卡密，每个卡密 ' . $points . ' 积分';
        } catch (Exception $e) {
            $error = '生成卡密时出错: ' . $e->getMessage();
        }
    }
}

// 获取卡密列表
try {
    $stmt = $pdo->query("SELECT id, card_key, points, used, used_by, used_at, created_at FROM point_cards ORDER BY id DESC LIMIT 100");
    $cards = $stmt->fetchAll();
} catch (Exception $e) {
    $error = '获取卡密列表时出错: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>卡密管理 - PHP加密解密系统</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>管理后台</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="cards.php" class="active">卡密管理</a></li>
                <li><a href="settings.php">系统设置</a></li>
                <li><a href="encrypt.php">加密功能</a></li>
                <li><a href="decrypt.php">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>卡密管理</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>生成卡密</h2>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="points">卡密面值 (积分):</label>
                        <input type="number" id="points" name="points" min="1" value="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="count">生成数量:</label>
                        <input type="number" id="count" name="count" min="1" max="100" value="10" required>
                    </div>
                    
                    <button type="submit" name="generate_cards" class="btn btn-success">生成卡密</button>
                </form>
                
                <h2 style="margin-top: 30px;">卡密列表</h2>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">卡密</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">积分</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">使用者</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">使用时间</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">生成时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($cards) && !empty($cards)): ?>
                            <?php foreach ($cards as $card): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($card['id']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($card['card_key']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($card['points']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $card['used_by'] ? htmlspecialchars($card['username']) : '未使用'; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $card['used_at'] ? htmlspecialchars($card['used_at']) : '未使用'; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($card['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="border: 1px solid #ddd; padding: 8px; text-align: center;">暂无卡密</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>