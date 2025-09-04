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

// 支持的解密算法
$algorithms = [
    'eval' => 'Eval解密',
    'goto' => 'Goto解密',
    'enphp' => 'EnPHP解密',
    'enphpv2' => 'EnPHP V2解密',
    'jiamiZym' => 'JiamiZym解密',
    'magicTwo' => 'MagicTwo解密',
    'noname1' => 'NoName1解密',
    'phpjm' => 'PHPJM解密',
    'phpjm2' => 'PHPJM2解密'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取选择的解密算法
    $algorithm = $_POST['algorithm'] ?? 'enphp';
    
    // 检查算法是否有效
    if (!array_key_exists($algorithm, $algorithms)) {
        $error = '无效的解密算法';
    } else {
        // 处理上传的文件
        if (isset($_FILES['encrypted_file']) && $_FILES['encrypted_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['encrypted_file']['tmp_name'];
            $file_name = $_FILES['encrypted_file']['name'];
            $file_size = $_FILES['encrypted_file']['size'];
            
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
                    $encrypted_code = file_get_contents($file_tmp);
                    if ($encrypted_code === false) {
                        $error = '无法读取上传的文件';
                    } else {
                        // 检查文件内容是否为空
                        if (empty(trim($encrypted_code))) {
                            $error = '上传的文件内容为空';
                        } else {
                            try {
                                // 执行解密
                                $encryptor = new Encryption($algorithm);
                                $decrypted_code = $encryptor->decrypt($encrypted_code);
                                
                                // 生成解密文件名
                                $decrypted_filename = 'decrypted_' . time() . '_' . $algorithm . '_' . $file_name;
                                $decrypted_filepath = '../uploads/' . $decrypted_filename;
                                
                                // 确保uploads目录存在
                                if (!is_dir('../uploads')) {
                                    mkdir('../uploads', 0755, true);
                                }
                                
                                // 保存解密后的文件
                                $result = file_put_contents($decrypted_filepath, $decrypted_code);
                                
                                if ($result === false) {
                                    $error = '无法保存解密文件';
                                } else {
                                    // 检查文件是否真的存在
                                    if (!file_exists($decrypted_filepath)) {
                                        $error = '解密文件保存失败';
                                    } else {
                                        // 记录解密任务
                                        $stmt = $pdo->prepare("INSERT INTO decrypt_tasks (admin_id, encrypted_file, decrypted_file, algorithm) VALUES (?, ?, ?, ?)");
                                        $stmt->execute([$_SESSION['admin_id'], $file_name, $decrypted_filename, $algorithm]);
                                        
                                        $success = '文件解密成功！';
                                        $download_link = '../download.php?file=' . urlencode($decrypted_filename);
                                    }
                                }
                            } catch (Exception $e) {
                                $error = '解密过程中出现错误: ' . $e->getMessage();
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
    <title>代码解密 - PHP加密解密系统</title>
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
                <li><a href="encrypt.php">加密功能</a></li>
                <li><a href="decrypt.php" class="active">解密功能</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>代码解密</h1>
                <div>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php">退出</a></div>
            </div>
            
            <div class="content">
                <h2>选择解密算法</h2>
                
                <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success">
                    <?php echo htmlspecialchars($success); ?>
                    <?php if (isset($download_link)): ?>
                    <br><a href="<?php echo htmlspecialchars($download_link); ?>" class="download-link" download>下载解密文件</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="algorithm">解密算法:</label>
                        <select id="algorithm" name="algorithm">
                            <?php foreach ($algorithms as $key => $name): ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($_POST['algorithm']) && $_POST['algorithm'] === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="algorithm-description" id="algorithm-description">
                            <?php 
                            $selectedAlgorithm = $_POST['algorithm'] ?? 'enphp';
                            echo isset($algorithms[$selectedAlgorithm]) ? $algorithms[$selectedAlgorithm] : '';
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="encrypted_file">上传加密文件:</label>
                        <input type="file" id="encrypted_file" name="encrypted_file" accept=".php">
                    </div>
                    
                    <button type="submit" class="btn btn-success">执行解密</button>
                </form>
                
                <div class="instructions">
                    <h3>使用说明</h3>
                    <ul>
                        <li>请选择需要解密的PHP文件（.php扩展名）</li>
                        <li>文件大小不能超过 <?php echo get_system_config('upload_max_size', 2097152) / 1024 / 1024; ?>MB</li>
                        <li>管理员解密不消耗积分</li>
                        <li>解密后请务必测试文件功能是否正常</li>
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
                description = algorithms[selected];
            }
            document.getElementById('algorithm-description').textContent = description;
        });
    </script>
</body>
</html>