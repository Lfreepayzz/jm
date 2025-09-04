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
require_once '../includes/Encryption.php';

$error = '';
$success = '';

// 获取管理员今天的加密次数
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM encrypt_tasks WHERE user_id = 0 AND admin_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$_SESSION['admin_id']]);
    $today_count = $stmt->fetch()['today_count'];
} catch (Exception $e) {
    $today_count = 0;
}

// 支持的加密算法
$algorithms = [
    'eval' => 'Eval加密 - 基础加密方法，兼容性好',
    'goto' => 'Goto加密 - 使用跳转指令混淆代码',
    'enphp' => 'EnPHP加密 - 经典PHP代码加密工具',
    'enphpv2' => 'EnPHP V2加密 - EnPHP的升级版本',
    'jiamiZym' => 'JiamiZym加密 - 专业的PHP代码加密方案',
    'magicTwo' => 'MagicTwo加密 - 双重混淆加密技术',
    'noname1' => 'NoName1加密 - 无名但强大的加密算法',
    'phpjm' => 'PHPJM加密 - 流行的PHP代码保护工具',
    'phpjm2' => 'PHPJM2加密 - PHPJM的改进版本'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取选择的加密算法
    $algorithm = $_POST['algorithm'] ?? 'enphp';
    
    // 检查算法是否有效
    if (!array_key_exists($algorithm, $algorithms)) {
        $error = '无效的加密算法';
    } else {
        // 处理上传的文件
        if (isset($_FILES['source_code']) && $_FILES['source_code']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['source_code']['tmp_name'];
            $file_name = $_FILES['source_code']['name'];
            $file_size = $_FILES['source_code']['size'];
            
            // 检查文件大小
            $max_size = (int)get_system_config('upload_max_size', 2097152); // 默认2MB
            if ($file_size > $max_size) {
                $error = '文件大小超过限制';
            } else {
                // 检查文件扩展名
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if ($file_ext !== 'php') {
                    $error = '只支持上传PHP文件';
                } else {
                    // 读取文件内容
                    $source_code = file_get_contents($file_tmp);
                    if ($source_code === false) {
                        $error = '无法读取上传的文件';
                    } else {
                        // 检查文件内容是否为空
                        if (empty(trim($source_code))) {
                            $error = '上传的文件内容为空';
                        } else {
                            try {
                                // 执行加密
                                $encryptor = new Encryption($algorithm);
                                $encrypted_code = $encryptor->encrypt($source_code);
                                
                                // 检查加密结果
                                if (empty($encrypted_code)) {
                                    $error = '加密失败，加密结果为空';
                                } else {
                                    // 生成加密文件名
                                    $encrypted_filename = 'encrypted_' . time() . '_' . $algorithm . '_' . $file_name;
                                    $encrypted_filepath = '../uploads/' . $encrypted_filename;
                                    
                                    // 确保uploads目录存在
                                    if (!is_dir('../uploads')) {
                                        mkdir('../uploads', 0755, true);
                                    }
                                    
                                    // 准备完整的PHP文件内容
                                    $final_code = "<?php\n" . $encrypted_code . "\n?>";
                                    
                                    // 保存加密后的文件
                                    $result = file_put_contents($encrypted_filepath, $final_code);
                                    
                                    if ($result === false) {
                                        $error = '无法保存加密文件';
                                    } else {
                                        // 检查文件是否真的存在
                                        if (!file_exists($encrypted_filepath)) {
                                            $error = '加密文件保存失败';
                                        } else {
                                            // 记录加密任务（管理员操作，user_id为0）
                                            $stmt = $pdo->prepare("INSERT INTO encrypt_tasks (user_id, admin_id, original_file, encrypted_file, algorithm) VALUES (0, ?, ?, ?, ?)");
                                            $stmt->execute([$_SESSION['admin_id'], $file_name, $encrypted_filename, $algorithm]);
                                            
                                            $success = '文件加密成功！';
                                            $download_link = '../download.php?file=' . urlencode($encrypted_filename);
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                $error = '加密过程中出现错误: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        } else {
            $error = '请上传一个有效的PHP文件';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>代码加密 - PHP加密解密系统</title>
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
        .form-group select, .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group select:focus, .form-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .algorithm-description {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
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
        .download-link {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #27ae60, #219653);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);
        }
        .download-link:hover {
            background: linear-gradient(135deg, #219653, #1e8449);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.4);
        }
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .info-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .usage-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin: 20px 0;
        }
        .stat-item {
            padding: 10px;
        }
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            color: #7f8c8d;
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
            <h2>管理后台</h2>
            <ul>
                <li><a href="dashboard.php">仪表板</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="cards.php">卡密管理</a></li>
                <li><a href="settings.php">系统设置</a></li>
                <li><a href="encrypt.php" class="active">加密功能</a></li>
                <li><a href="decrypt.php">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>代码加密</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>选择加密算法</h2>
                
                <div class="info-cards">
                    <div class="info-card">
                        <h3>今日使用情况</h3>
                        <div class="usage-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $today_count; ?></div>
                                <div class="stat-label">今日加密</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3>加密说明</h3>
                        <p>作为管理员，您可以直接加密PHP代码文件，无需消耗积分。</p>
                        <p>加密后的文件仅供测试和验证使用，请妥善保管。</p>
                    </div>
                </div>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success">
                    <?php echo htmlspecialchars($success); ?>
                    <?php if (isset($download_link)): ?>
                    <br><a href="<?php echo htmlspecialchars($download_link); ?>" class="download-link" download>下载加密文件</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="algorithm">加密算法:</label>
                        <select id="algorithm" name="algorithm">
                            <?php foreach ($algorithms as $key => $description): ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($_POST['algorithm']) && $_POST['algorithm'] === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(explode(' - ', $description)[0]); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="algorithm-description" id="algorithm-description">
                            <?php 
                            $selectedAlgorithm = $_POST['algorithm'] ?? 'enphp';
                            echo isset($algorithms[$selectedAlgorithm]) ? explode(' - ', $algorithms[$selectedAlgorithm])[1] : '';
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="source_code">上传PHP文件:</label>
                        <input type="file" id="source_code" name="source_code" accept=".php">
                    </div>
                    
                    <button type="submit" class="btn btn-success">执行加密</button>
                </form>
                
                <div class="instructions">
                    <h3>使用说明</h3>
                    <ul>
                        <li>请选择需要加密的PHP文件（.php扩展名）</li>
                        <li>文件大小不能超过 <?php echo get_system_config('upload_max_size', 2097152) / 1024 / 1024; ?>MB</li>
                        <li>管理员加密不消耗积分</li>
                        <li>您可以使用 <a href="../test_strict.php" download>测试文件</a> 来测试加密功能</li>
                        <li>加密后请务必测试文件功能是否正常</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 算法描述切换
        document.getElementById('algorithm').addEventListener('change', function() {
            var algorithms = <?php echo json_encode($algorithms); ?>;
            var selected = this.value;
            var description = '';
            if (algorithms[selected]) {
                description = algorithms[selected].split(' - ')[1];
            }
            document.getElementById('algorithm-description').textContent = description;
        });
    </script>
</body>
</html>