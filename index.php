<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP加密解密系统 - 安装向导</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #3498db, #2c80c5);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2em;
        }
        
        .content {
            padding: 30px;
        }
        
        .requirements {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .req-item {
            padding: 20px;
            border-radius: 8px;
        }
        
        .req-item.pass {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
        }
        
        .req-item.fail {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 1px solid #f5c6cb;
        }
        
        .req-item strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1.1em;
        }
        
        .step {
            margin-bottom: 30px;
            padding: 25px;
            border-left: 5px solid #3498db;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }
        
        .step h3 {
            margin-top: 0;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP加密解密系统</h1>
            <p>安装向导</p>
        </div>
        <div class="content">
            <h2>欢迎使用PHP加密解密系统</h2>
            <p>本安装向导将帮助您完成系统的安装和配置。</p>
            
            <div class="step">
                <h3>系统要求检查</h3>
                <div class="requirements">
                    <?php
                    // 检查PHP版本
                    $phpVersion = version_compare(PHP_VERSION, '7.4.0', '>=');
                    echo '<div class="req-item ' . ($phpVersion ? 'pass' : 'fail') . '">';
                    echo '<strong>PHP版本: ' . PHP_VERSION . '</strong>';
                    echo '<span>' . ($phpVersion ? '✓ 满足要求' : '✗ 需要PHP 7.4或更高版本') . '</span>';
                    echo '</div>';
                    
                    // 检查OpenSSL扩展
                    $openssl = extension_loaded('openssl');
                    echo '<div class="req-item ' . ($openssl ? 'pass' : 'fail') . '">';
                    echo '<strong>OpenSSL扩展</strong>';
                    echo '<span>' . ($openssl ? '✓ 已安装' : '✗ 未安装') . '</span>';
                    echo '</div>';
                    
                    // 检查Zip扩展
                    $zip = extension_loaded('zip');
                    echo '<div class="req-item ' . ($zip ? 'pass' : 'fail') . '">';
                    echo '<strong>Zip扩展</strong>';
                    echo '<span>' . ($zip ? '✓ 已安装' : '✗ 未安装') . '</span>';
                    echo '</div>';
                    
                    // 检查PDO扩展
                    $pdo = extension_loaded('pdo');
                    echo '<div class="req-item ' . ($pdo ? 'pass' : 'fail') . '">';
                    echo '<strong>PDO扩展</strong>';
                    echo '<span>' . ($pdo ? '✓ 已安装' : '✗ 未安装') . '</span>';
                    echo '</div>';
                    
                    // 检查GD扩展
                    $gd = extension_loaded('gd');
                    echo '<div class="req-item ' . ($gd ? 'pass' : 'fail') . '">';
                    echo '<strong>GD扩展</strong>';
                    echo '<span>' . ($gd ? '✓ 已安装' : '✗ 未安装') . '</span>';
                    echo '</div>';
                    
                    // 检查文件写入权限
                    $writable = is_writable('../config/');
                    echo '<div class="req-item ' . ($writable ? 'pass' : 'fail') . '">';
                    echo '<strong>配置目录写入权限</strong>';
                    echo '<span>' . ($writable ? '✓ 可写入' : '✗ 不可写入') . '</span>';
                    echo '</div>';
                    ?>
                </div>
            </div>
            
            <div class="step">
                <h3>安装步骤</h3>
                <ol>
                    <li>环境检查（当前步骤）</li>
                    <li>数据库配置</li>
                    <li>管理员账户设置</li>
                    <li>完成安装</li>
                </ol>
            </div>
            
            <?php if ($phpVersion && $openssl && $zip && $pdo && $gd && $writable): ?>
            <div style="text-align: center; margin-top: 30px;">
                <a href="step1.php" class="btn">开始安装</a>
            </div>
            <?php else: ?>
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #e74c3c;">请解决以上问题后再继续安装</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>