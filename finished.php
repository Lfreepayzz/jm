<?php
session_start();

// 如果没有完成安装，重定向到第一步
if (!isset($_SESSION['installed']) || !$_SESSION['installed']) {
    header('Location: index.php');
    exit();
}

// 清除安装会话
unset($_SESSION['db_config']);
unset($_SESSION['admin_config']);
unset($_SESSION['installed']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装完成 - PHP加密解密系统</title>
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
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
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
            margin: 10px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-primary {
            background: #28a745;
        }
        .btn-primary:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP加密解密系统</h1>
            <p>安装完成</p>
        </div>
        <div class="content">
            <div class="success">
                <h3>恭喜！系统已成功安装</h3>
                <p>您可以开始使用PHP加密解密系统了</p>
            </div>
            
            <p>为了安全，请删除 install 目录</p>
            
            <div style="margin-top: 30px;">
                <a href="../admin/" class="btn btn-primary">进入管理后台</a>
                <a href="../user/login.php" class="btn">用户登录</a>
            </div>
        </div>
    </div>
</body>
</html>