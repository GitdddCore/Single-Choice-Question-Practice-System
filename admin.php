<?php
// 处理登录请求，但不使用session保持登录状态
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    $config = json_decode(file_get_contents('config.json'), true);
    $admin_password = $config['admin']['password'];
    
    if ($_POST['admin_password'] === $admin_password) {
        // 密码正确，生成临时token并重定向
        $token = bin2hex(random_bytes(32));
        $token_file = 'temp_admin_token.txt';
        file_put_contents($token_file, $token . '|' . time());
        header('Location: ' . $_SERVER['PHP_SELF'] . '?token=' . $token);
        exit;
    } else {
        // 设置密码错误标记
        $password_error = true;
    }
}

// 检查token验证
$show_admin = false;
if (isset($_GET['token'])) {
    $token_file = 'temp_admin_token.txt';
    if (file_exists($token_file)) {
        $token_data = file_get_contents($token_file);
        list($stored_token, $timestamp) = explode('|', $token_data);
        
        // token有效期30秒，只允许一次访问
        if ($_GET['token'] === $stored_token && (time() - $timestamp) < 30) {
            $show_admin = true;
            // 立即删除token，确保只能使用一次
            unlink($token_file);
        } else {
            // token过期或无效，删除文件
            if (file_exists($token_file)) {
                unlink($token_file);
            }
        }
    }
}

// 如果没有通过密码验证，显示登录页面
if (!isset($show_admin) || !$show_admin) {
    
    // 显示登录页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>单选题练习系统 - 管理员登录</title>
        <link rel="icon" type="image/x-icon" href="favicon.ico">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <?php include 'assets/modules/notification.php'; ?>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            @font-face {
                font-family: 'HarmonyOS Sans Bold';
                src: url('assets/fonts/HarmonyOS_Sans.ttf') format('truetype');
                font-weight: bold;
                font-style: normal;
            }
            
            body {
                font-family: 'HarmonyOS Sans Bold', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-weight: bold;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #333;
            }
            
            .login-container {
                background: white;
                border-radius: 12px;
                padding: 40px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
                text-align: center;
            }
            
            .login-title {
                font-size: 24px;
                font-weight: bold;
                color: #333;
                margin-bottom: 8px;
            }
            
            .login-subtitle {
                color: #666;
                margin-bottom: 32px;
                font-size: 14px;
            }
            
            .form-group {
                margin-bottom: 20px;
                text-align: left;
            }
            
            .form-label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.3s ease;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .btn-login {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: transform 0.2s ease;
            }
            
            .btn-login:hover {
                transform: translateY(-2px);
            }
            

            
            .login-icon {
                font-size: 48px;
                color: #667eea;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <i class="fas fa-shield-alt login-icon"></i>
            <h1 class="login-title">管理员登录</h1>
            <p class="login-subtitle">请输入管理员密码以访问后台</p>
            

            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="admin_password">
                        <i class="fas fa-lock"></i> 管理员密码
                    </label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> 登录
                </button>
            </form>
        </div>
        
        <script>
            // 自动聚焦到密码输入框
            document.getElementById('admin_password').focus();
            
            // 检查密码错误
            <?php if (isset($password_error) && $password_error): ?>
            window.addEventListener('load', function() {
                notification.error('密码错误，请重试');
            });
            <?php endif; ?>
            

        </script>
    </body>
    </html>
    <?php
    exit;
}


?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>单选题练习系统 - 管理员后台</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 引入通知组件 -->
    <?php include 'assets/modules/notification.php'; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @font-face {
            font-family: 'HarmonyOS Sans Bold';
            src: url('assets/fonts/HarmonyOS_Sans.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'HarmonyOS Sans Bold', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: bold;
            background: #f5f5f5;
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }



        .nav-tabs {
            display: flex;
            background: white;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .nav-tab {
            flex: 1;
            padding: 16px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: #666;
            transition: all 0.2s ease;
            text-align: center;
        }

        .nav-tab:hover {
            background: #e9ecef;
            color: #333;
        }

        .nav-tab.active {
            background: #007bff;
            color: white;
        }

        .tab-content {
            display: none;
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: white;
            color: #333;
            min-width: 80px;
            white-space: nowrap;
        }

        .btn-primary { background: #007bff; color: white; border-color: #007bff; }
        .btn-primary:hover { background: #0056b3; border-color: #0056b3; }
        .btn-success { background: #28a745; color: white; border-color: #28a745; }
        .btn-success:hover { background: #1e7e34; border-color: #1e7e34; }
        .btn-danger { background: #dc3545; color: white; border-color: #dc3545; }
        .btn-danger:hover { background: #c82333; border-color: #c82333; }
        .btn-warning { background: #ffc107; color: #212529; border-color: #ffc107; }
        .btn-warning:hover { background: #e0a800; border-color: #e0a800; }



        /* 文件上传区域样式 */
        .file-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background: #e0e7ff;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .file-upload-area:hover .upload-icon {
            color: #667eea;
        }

        .upload-text .upload-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .upload-text .upload-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            margin-top: 12px;
        }

        .file-details {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-details i {
            color: #059669;
            font-size: 20px;
        }

        .file-name {
            font-weight: bold;
            color: #374151;
        }

        .file-size {
            color: #6b7280;
            font-size: 14px;
        }

        .remove-file {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .remove-file:hover {
            background: #fee2e2;
        }

        .search-box {
            position: relative;
            display: inline-block;
        }

        .search-input {
            padding: 8px 40px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            width: 320px;
        }

        .search-input:focus {
            outline: none;
            border-color: #007bff;
        }

        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 14px;
            pointer-events: none;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        /* 表格列宽 */
        #questions-table th:nth-child(1), #questions-table td:nth-child(1) { width: 5%; }
        #questions-table th:nth-child(2), #questions-table td:nth-child(2) { width: 10%; }
        #questions-table th:nth-child(3), #questions-table td:nth-child(3) { width: 40%; }
        #questions-table th:nth-child(4), #questions-table td:nth-child(4) { width: 20%; }
        #questions-table th:nth-child(5), #questions-table td:nth-child(5) { width: 25%; }
        
        #question-sets-table th:nth-child(1), #question-sets-table td:nth-child(1) { width: 10%; }
        #question-sets-table th:nth-child(2), #question-sets-table td:nth-child(2) { width: 10%; }
        #question-sets-table th:nth-child(3), #question-sets-table td:nth-child(3) { width: 50%; }
        #question-sets-table th:nth-child(4), #question-sets-table td:nth-child(4) { width: 30%; }

        /* 操作按钮横向排列 */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            margin: 0;
            min-width: 60px;
            font-size: 12px;
            padding: 6px 10px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .pagination button:hover {
            background: #f8f9fa;
        }

        .pagination button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1001;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-body {
            padding: 20px;
            max-height: calc(80vh - 80px);
            overflow-y: auto;
        }

        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-success { background-color: #28a745; }
        .status-error { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .alert-info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .db-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .nav-links {
            display: flex;
            gap: 16px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }

        .nav-link {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .nav-link:hover {
            background: #0056b3;
            color: white;
        }


    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>单选题练习系统管理后台</h1>

        </div>

        <div class="nav-tabs">
            <button class="nav-tab active" data-tab="questions">
                <i class="fas fa-question-circle"></i> 题目管理
            </button>
            <button class="nav-tab" data-tab="question-sets">
                <i class="fas fa-layer-group"></i> 套题管理
            </button>
        </div>

        <!-- 题目管理标签页 -->
        <div id="questions" class="tab-content active">
            <div class="action-bar">
                <div class="search-box">
                    <input type="text" id="question-search" class="search-input" placeholder="搜索ID、题目内容、知识点分类...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button id="batch-delete" class="btn btn-danger" style="display: none;">
                        <i class="fas fa-trash"></i> 批量删除
                    </button>
                    <button id="add-question" class="btn btn-success">
                        <i class="fas fa-plus"></i> 添加题目
                    </button>
                    <button id="batch-import" class="btn btn-warning">
                        <i class="fas fa-upload"></i> 批量导入
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="questions-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-questions"></th>
                            <th>题目编号</th>
                            <th>题目内容</th>
                            <th>知识点分类</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 题目数据将通过JavaScript动态加载 -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="questions-per-page" style="font-size: 14px; color: #666; white-space: nowrap;">每页显示:</label>
                    <select id="questions-per-page" class="form-control" style="width: 80px; padding: 6px 8px; font-size: 14px;">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span style="font-size: 14px; color: #666; white-space: nowrap;">条</span>
                </div>
                <div class="pagination" id="questions-pagination">
                    <!-- 分页控件将通过JavaScript动态生成 -->
                </div>
            </div>
        </div>

        <!-- 套题管理标签页 -->
        <div id="question-sets" class="tab-content">
            <div class="action-bar">
                <div class="search-box">
                    <input type="text" id="set-search" class="search-input" placeholder="搜索套题编号...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button id="create-question-set" class="btn btn-success">
                        <i class="fas fa-plus"></i> 创建套题
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="question-sets-table">
                    <thead>
                        <tr>
                            <th>套题编号</th>
                            <th>题目数量</th>
                            <th>知识点</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 套题数据将通过JavaScript动态加载 -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label for="sets-per-page" style="font-size: 14px; color: #666; white-space: nowrap;">每页显示:</label>
                    <select id="sets-per-page" class="form-control" style="width: 80px; padding: 6px 8px; font-size: 14px;">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span style="font-size: 14px; color: #666; white-space: nowrap;">条</span>
                </div>
                <div class="pagination" id="question-sets-pagination">
                    <!-- 分页控件将通过JavaScript动态生成 -->
                </div>
            </div>
        </div>
    </div>

    <!-- 题目编辑模态框 -->
    <div id="question-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">编辑题目</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="question-form">
                    <input type="hidden" id="question-id">
                    
                    <div class="form-group">
                        <label class="form-label">题目内容</label>
                        <textarea id="question-content" class="form-control" rows="3"></textarea>
                    </div>
                    

                    
                    <div class="form-group">
                        <label class="form-label">选项A</label>
                        <input type="text" id="option-a" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选项B</label>
                        <input type="text" id="option-b" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选项C</label>
                        <input type="text" id="option-c" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选项D</label>
                        <input type="text" id="option-d" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">正确答案</label>
                        <select id="correct-answer" class="form-control">
                            <option value="">请选择正确答案</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">知识点分类</label>
                        <input type="text" id="question-category" class="form-control" placeholder="请输入知识点分类">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">解析</label>
                        <textarea id="question-explanation" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group" style="text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 保存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 套题创建模态框 -->
    <div id="question-set-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">创建套题</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="question-set-form">
                    <div class="form-group">
                        <label class="form-label">套题编号</label>
                        <input type="text" id="set-number" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选择题目</label>
                        <div class="search-box">
                            <input type="text" id="question-filter" class="search-input" placeholder="搜索题目...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0; padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                            <span id="selected-count" style="font-size: 14px; color: #666;">已选择 0 道题目</span>
                            <button type="button" id="toggle-all-questions" class="btn btn-sm" style="padding: 4px 12px; font-size: 12px;">
                                <i class="fas fa-check-square"></i> 全选
                            </button>
                        </div>
                        <div id="available-questions" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #fafafa; border-radius: 4px;">
                        <!-- 可选题目列表将通过JavaScript动态加载 -->
                        </div>
                    </div>
                
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 创建套题
                        </button>
                        <button type="button" class="btn" onclick="closeModal('question-set-modal')">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 编辑套题模态框 -->
    <div id="edit-question-set-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">编辑套题</h3>
                <button class="close" onclick="closeEditQuestionSetModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-question-set-form">
                    <div class="form-group">
                        <label class="form-label">套题编号</label>
                        <input type="text" id="edit-set-number" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">选择题目</label>
                        <div class="search-box">
                            <input type="text" id="edit-question-filter" class="search-input" placeholder="搜索题目...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0; padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                            <span id="edit-selected-count" style="font-size: 14px; color: #666;">已选择 0 道题目</span>
                            <button type="button" id="edit-toggle-all-questions" class="btn btn-sm" style="padding: 4px 12px; font-size: 12px;">
                                <i class="fas fa-check-square"></i> 全选
                            </button>
                        </div>
                        <div id="edit-available-questions" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #fafafa; border-radius: 4px;">
                        <!-- 可选题目列表将通过JavaScript动态加载 -->
                        </div>
                    </div>
                
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 更新套题
                        </button>
                        <button type="button" class="btn" onclick="closeEditQuestionSetModal()">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 批量导入模态框 -->
    <div id="import-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">批量导入题目</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="import-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">选择Excel文件</label>
                    <div class="file-upload-area" id="file-upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <p class="upload-title">拖拽文件到此处或点击选择</p>
                            <p class="upload-subtitle">支持 .xlsx, .xls 格式</p>
                        </div>
                        <input type="file" id="import-file" class="file-input" accept=".xlsx,.xls">
                    </div>
                    <div class="file-info" id="file-info" style="display: none;">
                        <div class="file-details">
                            <i class="fas fa-file-excel"></i>
                            <span class="file-name"></span>
                            <span class="file-size"></span>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <strong>导入说明: </strong>仅支持Excel文件, 请下载模板文件后进行导入。
                    </div>
                </div>
                
                    <div class="form-group" style="text-align: center;">
                        <a href="questions.php?action=download_template" class="btn btn-primary">
                            <i class="fas fa-download"></i> 下载模板
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> 开始导入
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        
        // 全局变量
        let currentQuestionsData = [];
        let currentQuestionsPage = 1;
        let questionsPerPage = 10;
        let selectedQuestionIds = [];
        let editSelectedQuestionIds = []; // 编辑模式下的选中题目
        let currentSearchTerm = ''; // 当前搜索词
        
        // 套题管理相关变量
        let currentQuestionSetsData = [];
        let currentQuestionSetsPage = 1;
        let questionSetsPerPage = 10;
        let currentSetSearchTerm = ''; // 当前套题搜索词

        // 通用数据获取函数
        async function fetchData(url, data) {
            const formData = new FormData();
            Object.entries(data).forEach(([key, value]) => formData.append(key, value));
            const response = await fetch(url, { method: 'POST', body: formData });
            return await response.json();
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadQuestions();
            loadQuestionSets();
            loadCategories();
        });

        // 已移除重复的事件绑定代码，统一在DOMContentLoaded中处理



        // 切换标签页
        function switchTab(tabName) {
            // 更新标签按钮状态
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            
            // 更新内容区域
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
        }

        // 加载题目数据
        async function loadQuestions(page = 1, search = '') {
            try {
                const result = await fetchData('questions.php', {
                    action: 'get_questions',
                    page,
                    per_page: questionsPerPage,
                    search
                });
                
                if (result.success) {
                    currentQuestionsData = result.data;
                    currentQuestionsPage = page;
                    renderQuestionsTable(result.data.questions || result.data || []);
                    renderQuestionsPagination(result.data.total || result.total || 0, page);
                } else {
                    notification.toast('获取题目失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                notification.toast('获取题目失败，请检查网络连接', 'error');
            }
        }

        // 渲染题目表格
        function renderQuestionsTable(questions) {
            const tbody = document.querySelector('#questions-table tbody');
            tbody.innerHTML = '';
            
            if (!questions || questions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">暂无题目数据</td></tr>';
                return;
            }
            
            questions.forEach(question => {
                const questionText = question.question || question.content || question.title || '无题目内容';
                const category = question.knowledge_category || question.category || question.knowledge_point || '未分类';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="checkbox" class="question-checkbox" value="${question.id}" onchange="updateBatchDeleteButton()"></td>
                    <td>${question.id || '未知'}</td>
                    <td>${questionText.substring(0, 50)}${questionText.length > 50 ? '...' : ''}</td>
                    <td>${category}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-warning" onclick="editQuestion(${question.id})">
                                <i class="fas fa-edit"></i> 编辑
                            </button>
                            <button class="btn btn-danger" onclick="deleteQuestion(${question.id})">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // 重置全选状态
            document.getElementById('select-all-questions').checked = false;
            updateBatchDeleteButton();
        }

        // 通用分页渲染函数
        function renderPagination(paginationId, total, currentPage, perPage, loadFunction, searchTerm = '') {
            const totalPages = Math.ceil(total / perPage);
            const pagination = document.getElementById(paginationId);
            const createBtn = (text, disabled, onClick, isEllipsis = false) => {
                const btn = document.createElement('button');
                btn.textContent = text;
                btn.disabled = disabled;
                if (onClick) btn.onclick = onClick;
                if (isEllipsis) {
                    btn.style.cursor = 'default';
                    btn.style.background = 'transparent';
                    btn.style.border = 'none';
                }
                return btn;
            };
            
            pagination.innerHTML = '';
            
            // 添加上一页按钮
            pagination.append(
                createBtn('上一页', currentPage === 1, () => currentPage > 1 && loadFunction(currentPage - 1, searchTerm))
            );
            
            // 如果总页数小于等于7，显示所有页码
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) {
                    const btn = createBtn(i, false, () => loadFunction(i, searchTerm));
                    if (i === currentPage) btn.className = 'active';
                    pagination.append(btn);
                }
            } else {
                // 总页数大于7时，使用省略号逻辑
                
                // 始终显示第1页
                const firstBtn = createBtn(1, false, () => loadFunction(1, searchTerm));
                if (currentPage === 1) firstBtn.className = 'active';
                pagination.append(firstBtn);
                
                // 如果当前页距离第1页较远，添加省略号
                if (currentPage > 4) {
                    pagination.append(createBtn('...', true, null, true));
                }
                
                // 显示当前页附近的页码
                const startPage = Math.max(2, currentPage - 1);
                const endPage = Math.min(totalPages - 1, currentPage + 1);
                
                for (let i = startPage; i <= endPage; i++) {
                    const btn = createBtn(i, false, () => loadFunction(i, searchTerm));
                    if (i === currentPage) btn.className = 'active';
                    pagination.append(btn);
                }
                
                // 如果当前页距离最后一页较远，添加省略号
                if (currentPage < totalPages - 3) {
                    pagination.append(createBtn('...', true, null, true));
                }
                
                // 始终显示最后一页
                if (totalPages > 1) {
                    const lastBtn = createBtn(totalPages, false, () => loadFunction(totalPages, searchTerm));
                    if (currentPage === totalPages) lastBtn.className = 'active';
                    pagination.append(lastBtn);
                }
            }
            
            // 添加下一页按钮
            pagination.append(
                createBtn('下一页', currentPage === totalPages, () => currentPage < totalPages && loadFunction(currentPage + 1, searchTerm))
            );
        }
        
        // 渲染题目分页
        function renderQuestionsPagination(total, currentPage) {
            renderPagination('questions-pagination', total, currentPage, questionsPerPage, loadQuestions, currentSearchTerm);
        }

        // 搜索题目
        function searchQuestions() {
            const searchTerm = document.getElementById('question-search').value;
            currentSearchTerm = searchTerm;
            currentQuestionsPage = 1;
            loadQuestions(1, searchTerm);
        }

        // 加载套题数据
        async function loadQuestionSets(page = 1, search = '') {
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_question_sets',
                    page,
                    per_page: questionSetsPerPage,
                    search
                });
                
                if (result.success) {
                    currentQuestionSetsData = result.data;
                    currentQuestionSetsPage = page;
                    renderQuestionSetsTable(result.data.question_sets || result.data);
                    renderQuestionSetsPagination(result.data.total || result.total, page);
                } else {
                    notification.toast('获取套题失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('获取套题失败: ' + error.message, 'error');
            }
        }

        // 渲染套题表格
        function renderQuestionSetsTable(questionSets) {
            const tbody = document.querySelector('#question-sets-table tbody');
            tbody.innerHTML = '';
            
            // 按ID从小到大排序
            const sortedSets = questionSets.sort((a, b) => {
                const idA = parseInt(a.id || a.set_number);
                const idB = parseInt(b.id || b.set_number);
                return idA - idB;
            });
            
            sortedSets.forEach(set => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${set.id || set.set_number}</td>
                    <td>${set.question_count}</td>
                    <td>${set.knowledge_points || ''}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="viewQuestionSet('${set.id || set.set_number}')">
                                <i class="fas fa-eye"></i> 查看
                            </button>
                            <button class="btn btn-warning" onclick="editQuestionSet('${set.id || set.set_number}')">
                                <i class="fas fa-edit"></i> 编辑
                            </button>
                            <button class="btn btn-danger" onclick="deleteQuestionSet('${set.id || set.set_number}')">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // 渲染套题分页
        function renderQuestionSetsPagination(total, currentPage) {
            renderPagination('question-sets-pagination', total, currentPage, questionSetsPerPage, loadQuestionSets, currentSetSearchTerm);
        }

        // 搜索套题
        function searchQuestionSets() {
            const searchTerm = document.getElementById('set-search').value;
            currentSetSearchTerm = searchTerm;
            currentQuestionSetsPage = 1; // 重置页码
            loadQuestionSets(1, searchTerm);
        }

        // 加载分类数据
        async function loadCategories() {
            try {
                const result = await fetchData('questions.php', { action: 'get_categories' });
                
                if (result.success) {
                    const categorySelect = document.getElementById('question-category');
                    categorySelect.innerHTML = '<option value="">请选择知识点分类</option>' +
                        result.data.map(category => `<option value="${category}">${category}</option>`).join('');
                }
            } catch (error) {
                // 加载分类失败
            }
        }

        // 打开题目模态框
        function openQuestionModal(questionId = null) {
            if (questionId) {
                // 编辑模式
                loadQuestionForEdit(questionId);
            } else {
                // 新增模式
                document.getElementById('question-form').reset();
                document.getElementById('question-id').value = '';
                document.querySelector('#question-modal .modal-title').textContent = '添加题目';
            }
            openModal('question-modal');
        }

        // 加载题目用于编辑
        async function loadQuestionForEdit(questionId) {
            try {
                const result = await fetchData('questions.php', {
                    action: 'get_question',
                    id: questionId
                });
                
                if (result.success) {
                    const q = result.data;
                    const fields = {
                        'question-id': q.id,
                        'question-content': q.question || '',
                        'option-a': q.option_1 || '',
                        'option-b': q.option_2 || '',
                        'option-c': q.option_3 || '',
                        'option-d': q.option_4 || '',
                        'correct-answer': q.correct_answer || '',
                        'question-category': q.knowledge_category || '',
                        'question-explanation': q.explanation || ''
                    };
                    Object.entries(fields).forEach(([id, value]) => {
                        document.getElementById(id).value = value;
                    });
                    document.querySelector('#question-modal .modal-title').textContent = '编辑题目';
                } else {
                    notification.toast('加载题目失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('加载题目失败: ' + error.message, 'error');
            }
        }



        // 保存题目
        async function saveQuestion(e) {
            e.preventDefault();
            
            const getValue = id => document.getElementById(id).value.trim();
            
            // 验证题目内容
            const questionContent = getValue('question-content');
            if (!questionContent) {
                notification.toast('请填写题目内容', 'warning');
                return;
            }
            
            // 验证正确答案
            const correctAnswer = getValue('correct-answer');
            if (!correctAnswer) {
                notification.toast('请选择正确答案', 'warning');
                return;
            }
            
            // 验证知识点分类
            const category = getValue('question-category');
            if (!category) {
                notification.toast('请填写知识点分类', 'warning');
                return;
            }
            
            const options = ['option-a', 'option-b', 'option-c', 'option-d'].map(getValue);
            const explanation = getValue('question-explanation');
            
            if (options.some(opt => !opt)) {
                notification.toast('请填写完整的选项', 'warning');
                return;
            }
            
            if (!explanation) {
                notification.toast('请填写题目解析', 'warning');
                return;
            }
            
            const questionId = getValue('question-id');
            const data = {
                action: questionId ? 'update_question' : 'add_question',
                question: getValue('question-content'),
                option_1: options[0],
                option_2: options[1],
                option_3: options[2],
                option_4: options[3],
                correct_answer: getValue('correct-answer'),
                knowledge_category: getValue('question-category'),
                explanation
            };
            
            if (questionId) data.id = questionId;
            
            try {
                const result = await fetchData('questions.php', data);
                
                if (result.success) {
                    notification.toast(questionId ? '题目更新成功' : '题目添加成功', 'success');
                    closeModal('question-modal');
                    loadQuestions(currentQuestionsPage);
                } else {
                    notification.toast('保存失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('保存失败: ' + error.message, 'error');
            }
        }

        // 编辑题目
        function editQuestion(questionId) {
            openQuestionModal(questionId);
        }

        // 删除题目
        async function deleteQuestion(questionId) {
            try {
                const checkResult = await fetchData('questions.php', {
                    action: 'check_question_usage',
                    id: questionId
                });
                
                if (!checkResult.success) {
                    notification.toast('检查题目使用情况失败: ' + checkResult.message, 'error');
                    return;
                }
                
                const confirmMessage = checkResult.is_used 
                    ? `本题目已被套题 ${checkResult.used_in_sets.join('、')} 使用，若删除则自动移除套题对本题目的引用，是否继续删除？`
                    : '确定要删除这道题目吗？';
                
                notification.confirm(confirmMessage, '确认删除', {
                    onConfirm: async () => {
                        try {
                            const result = await fetchData('questions.php', {
                                action: 'delete_question',
                                id: questionId
                            });
                            
                            if (result.success) {
                                notification.toast(result.message || '题目删除成功', 'success');
                                await checkAndRedirectToFirstPage();
                            } else {
                                notification.toast('删除失败: ' + result.message, 'error');
                            }
                        } catch (error) {
                            notification.toast('删除失败: ' + error.message, 'error');
                        }
                    }
                });
                return;
                
            } catch (error) {
                notification.toast('删除失败: ' + error.message, 'error');
            }
        }

        // 打开套题模态框
        function openQuestionSetModal() {
            // 重置编辑状态
            window.currentEditingSetNumber = null;
            
            document.getElementById('question-set-form').reset();
            selectedQuestionIds = []; // 重置选择的题目
            document.getElementById('question-filter').value = ''; // 清空搜索框
            
            // 重置套题编号输入框状态
            document.getElementById('set-number').readOnly = false;
            
            // 重置模态框标题
            const modalTitle = document.querySelector('#question-set-modal .modal-title');
            if (modalTitle) {
                modalTitle.textContent = '创建套题';
            }
            
            // 重置保存按钮
            const saveBtn = document.querySelector('#question-set-modal .btn-primary');
            if (saveBtn) {
                saveBtn.textContent = '创建套题';
                saveBtn.onclick = function(e) { saveQuestionSet(e); };
            }
            
            loadUnusedQuestions();
            openModal('question-set-modal');
        }

        // 加载未被套题使用的题目（用于创建套题）
        async function loadUnusedQuestions(search = '') {
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_available_questions',
                    page: 1,
                    limit: 100,
                    search,
                    exclude_used: true
                });
                
                if (result.success) {
                    renderAvailableQuestions(result.data.questions || result.data);
                }
            } catch (error) {
                // 加载未使用题目失败
            }
        }
        
        // 加载可选题目（保留原函数以兼容现有代码）
        async function loadAvailableQuestions(search = '') {
            return loadUnusedQuestions(search);
        }
        
        // 加载可选题目（编辑模式，包括当前套题已选的题目）
        async function loadAvailableQuestionsForEdit(setNumber, search = '') {
            try {
                // 并行获取未使用题目和当前套题详情
                const [unusedResult, currentResult] = await Promise.all([
                    fetchData('question_sets.php', {
                        action: 'get_available_questions',
                        page: 1,
                        limit: 100,
                        search,
                        exclude_used: true
                    }),
                    fetchData('question_sets.php', {
                        action: 'get_question_set_details',
                        set_number: setNumber
                    })
                ]);
                
                let allQuestions = [];
                
                // 添加未被使用的题目
                if (unusedResult.success) {
                    allQuestions = [...(unusedResult.data.questions || unusedResult.data)];
                }
                
                // 添加当前套题已选的题目
                if (currentResult.success && currentResult.data) {
                    const currentQuestions = currentResult.data.map(q => ({
                        id: q.id,
                        question: q.question,
                        content: q.question,
                        knowledge_category: q.knowledge_category,
                        type: q.type
                    }));
                    
                    // 合并题目，避免重复
                    currentQuestions.forEach(currentQ => {
                        if (!allQuestions.find(q => q.id === currentQ.id)) {
                            allQuestions.push(currentQ);
                        }
                    });
                }
                
                // 如果有搜索条件，过滤题目
                if (search) {
                    allQuestions = allQuestions.filter(q => 
                        (q.question || q.content || '').toLowerCase().includes(search.toLowerCase()) ||
                        q.id.toString().includes(search)
                    );
                }
                
                // 按ID排序
                allQuestions.sort((a, b) => parseInt(a.id) - parseInt(b.id));
                
                renderAvailableQuestions(allQuestions);
            } catch (error) {
                // 加载可选题目失败
            }
        }

        // 渲染可选题目
        function renderAvailableQuestions(questions) {
            const container = document.getElementById('available-questions');
            container.innerHTML = '';
            
            // 存储当前显示的题目ID列表
            window.currentAvailableQuestions = questions.map(q => q.id);
            
            questions.forEach(question => {
                const div = document.createElement('div');
                div.className = 'checkbox-item';
                div.style.cssText = `
                    margin-bottom: 6px;
                    padding: 8px 12px;
                    background-color: white;
                    border: 1px solid #e9ecef;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                    cursor: pointer;
                `;
                
                // 检查是否已选中
                const isSelected = selectedQuestionIds.includes(question.id);
                
                // 添加悬停效果
                div.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.borderColor = '#007bff';
                });
                
                div.addEventListener('mouseleave', function() {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    this.style.backgroundColor = checkbox.checked ? '#e3f2fd' : 'white';
                    this.style.borderColor = checkbox.checked ? '#2196f3' : '#e9ecef';
                });
                
                div.innerHTML = `
                    <input type="checkbox" id="q-${question.id}" value="${question.id}" ${isSelected ? 'checked' : ''} onchange="toggleQuestionSelection(${question.id})" style="margin-right: 8px;">
                    <label for="q-${question.id}" style="cursor: pointer; font-size: 14px; line-height: 1.4; margin: 0;">
                        <span style="font-weight: 600; color: #007bff;">#${question.id}</span>
                        <span style="margin-left: 8px; color: #333;">${(question.question || question.content || '').substring(0, 100)}${(question.question || question.content || '').length > 100 ? '...' : ''}</span>
                    </label>
                `;
                
                // 设置初始选中状态的样式
                if (isSelected) {
                    div.style.backgroundColor = '#e3f2fd';
                    div.style.borderColor = '#2196f3';
                }
                
                container.appendChild(div);
            });
            
            // 更新选择计数和全选按钮状态
            updateSelectionUI();
        }

        // 切换题目选择
        function toggleQuestionSelection(questionId) {
            const checkbox = document.getElementById(`q-${questionId}`);
            const item = checkbox.closest('.checkbox-item');
            
            if (checkbox.checked) {
                if (!selectedQuestionIds.includes(questionId)) {
                    selectedQuestionIds.push(questionId);
                }
                // 更新选中状态的样式
                item.style.backgroundColor = '#e3f2fd';
                item.style.borderColor = '#2196f3';
            } else {
                const index = selectedQuestionIds.indexOf(questionId);
                if (index > -1) {
                    selectedQuestionIds.splice(index, 1);
                }
                // 更新未选中状态的样式
                item.style.backgroundColor = 'white';
                item.style.borderColor = '#e9ecef';
            }
            
            // 更新选择计数和全选按钮状态
            updateSelectionUI();
        }
        
        // 全选/取消全选功能
        function toggleAllQuestions() {
            const toggleBtn = document.getElementById('toggle-all-questions');
            const currentQuestions = window.currentAvailableQuestions || [];
            
            // 检查当前显示的题目是否全部已选中
            const allCurrentSelected = currentQuestions.every(id => selectedQuestionIds.includes(id));
            
            if (allCurrentSelected) {
                // 取消选择当前显示的所有题目
                currentQuestions.forEach(id => {
                    const index = selectedQuestionIds.indexOf(id);
                    if (index > -1) {
                        selectedQuestionIds.splice(index, 1);
                    }
                    const checkbox = document.getElementById(`q-${id}`);
                    if (checkbox) checkbox.checked = false;
                });
            } else {
                // 选择当前显示的所有题目
                currentQuestions.forEach(id => {
                    if (!selectedQuestionIds.includes(id)) {
                        selectedQuestionIds.push(id);
                    }
                    const checkbox = document.getElementById(`q-${id}`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            // 更新UI
            updateSelectionUI();
        }
        
        // 更新选择计数和全选按钮状态
        function updateSelectionUI() {
            const selectedCountElement = document.getElementById('selected-count');
            const toggleBtn = document.getElementById('toggle-all-questions');
            const currentQuestions = window.currentAvailableQuestions || [];
            
            if (selectedCountElement) {
                selectedCountElement.textContent = `已选择 ${selectedQuestionIds.length} 道题目`;
            }
            
            if (toggleBtn && currentQuestions.length > 0) {
                const allCurrentSelected = currentQuestions.every(id => selectedQuestionIds.includes(id));
                
                if (allCurrentSelected) {
                    toggleBtn.innerHTML = '<i class="fas fa-square"></i> 取消全选';
                    toggleBtn.className = 'btn btn-sm btn-warning';
                } else {
                    toggleBtn.innerHTML = '<i class="fas fa-check-square"></i> 全选';
                    toggleBtn.className = 'btn btn-sm btn-primary';
                }
            }
        }

        // 过滤可选题目
        function filterAvailableQuestions() {
            const searchTerm = document.getElementById('question-filter').value;
            
            // 检查是否在编辑模式
            if (window.currentEditingSetNumber) {
                loadAvailableQuestionsForEdit(window.currentEditingSetNumber, searchTerm);
            } else {
                loadUnusedQuestions(searchTerm);
            }
        }
        
        // 更新套题
        async function updateQuestionSet() {
            if (editSelectedQuestionIds.length === 0) {
                notification.toast('请至少选择一道题目', 'warning');
                return;
            }
            
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'update_question_set',
                    set_number: window.currentEditingSetNumber,
                    question_ids: JSON.stringify(editSelectedQuestionIds)
                });
                
                if (result.success) {
                    notification.toast('套题更新成功', 'success');
                    closeEditQuestionSetModal();
                    loadQuestionSets();
                } else {
                    notification.toast('更新失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('更新失败: ' + error.message, 'error');
            }
        }

        // 保存套题
        async function saveQuestionSet(e) {
            e.preventDefault();
            
            const setNumber = document.getElementById('set-number').value.trim();
            
            // 验证套题编号
            if (!setNumber) {
                notification.toast('请填写套题编号', 'warning');
                return;
            }
            
            if (selectedQuestionIds.length === 0) {
                notification.toast('请至少选择一道题目', 'warning');
                return;
            }
            
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'add_question_set',
                    set_number: setNumber,
                    question_ids: JSON.stringify(selectedQuestionIds)
                });
                
                if (result.success) {
                    notification.toast('套题创建成功', 'success');
                    closeModal('question-set-modal');
                    selectedQuestionIds = [];
                    loadQuestionSets();
                } else {
                    notification.toast('创建失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('创建失败: ' + error.message, 'error');
            }
        }

        // 查看套题
        async function viewQuestionSet(setNumber) {
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_question_set_details',
                    set_number: setNumber
                });
                
                if (result.success && result.data) {
                    showQuestionSetDetails(result.data, setNumber);
                } else {
                    notification.toast('获取套题详情失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                notification.toast('获取套题详情失败: ' + error.message, 'error');
            }
        }
        
        // 显示套题详情
        function showQuestionSetDetails(questions, setNumber) {
            let html = `
                <div class="question-set-details">
                    <h3>套题 ${setNumber} 详情</h3>
                    <p>共 ${questions.length} 道题目</p>
                    <div class="questions-list">
            `;
            
            questions.forEach((question, index) => {
                html += `
                    <div class="question-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                        <h4>(编号: ${question.id}) ${question.question}</h4>
                        <p><strong>知识点:</strong> ${question.knowledge_category}</p>
                `;
                
                if (question.type === '单选题') {
                    html += `
                        <div class="options">
                            <p><strong>选项:</strong></p>
                            <p>A. ${question.option_1}</p>
                            <p>B. ${question.option_2}</p>
                            <p>C. ${question.option_3}</p>
                            <p>D. ${question.option_4}</p>
                        </div>
                    `;
                }
                
                html += `
                        <p><strong>正确答案:</strong> <span style="color: green; font-weight: bold;">${question.correct_answer}</span></p>
                    </div>
                `;
            });
            
            html += `
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button class="btn btn-secondary" onclick="closeQuestionSetDetails()">关闭</button>
                    </div>
                </div>
            `;
            
            // 创建模态框显示详情
            const modal = document.createElement('div');
            modal.id = 'questionSetModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 20px;
                border-radius: 10px;
                max-width: 80%;
                max-height: 80%;
                overflow-y: auto;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            `;
            
            modalContent.innerHTML = html;
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
        }
        
        // 关闭套题详情
        function closeQuestionSetDetails() {
            const modal = document.getElementById('questionSetModal');
            if (modal) {
                modal.remove();
            }
        }

        // 编辑套题
        async function editQuestionSet(setNumber) {
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_question_set',
                    set_number: setNumber
                });
                
                if (result.success && result.data) {
                    // 设置当前编辑的套题信息
                    window.currentEditingSetNumber = setNumber;
                    
                    // 解析已选题目ID
                    const questionIds = result.data.question_ids ? result.data.question_ids.split(',').map(id => id.trim()) : [];
                    editSelectedQuestionIds = [...questionIds]; // 使用独立的编辑选中数组
                    
                    // 设置套题编号（编辑时不可修改）
                    document.getElementById('edit-set-number').value = setNumber;
                    
                    // 清空搜索框
                    document.getElementById('edit-question-filter').value = '';
                    
                    // 加载可选题目（包括当前套题已选的题目）
                    await loadEditAvailableQuestions(setNumber);
                    
                    // 打开编辑模态框
                    openModal('edit-question-set-modal');
                } else {
                    notification.toast('获取套题信息失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                notification.toast('获取套题信息失败: ' + error.message, 'error');
            }
        }

        // 关闭编辑套题模态框
        function closeEditQuestionSetModal() {
            closeModal('edit-question-set-modal');
            // 清空编辑状态
            editSelectedQuestionIds = [];
            window.currentEditingSetNumber = null;
            document.getElementById('edit-set-number').value = '';
            document.getElementById('edit-question-filter').value = '';
            document.getElementById('edit-available-questions').innerHTML = '';
            updateEditSelectedCount();
        }

        // 更新编辑模式下的选中计数
        function updateEditSelectedCount() {
            const countElement = document.getElementById('edit-selected-count');
            if (countElement) {
                countElement.textContent = `已选择 ${editSelectedQuestionIds.length} 道题目`;
            }
        }

        // 获取编辑套题时需要的题目（专门的函数）
        async function getQuestionsForEdit(setNumber) {
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_questions_for_edit',
                    set_number: setNumber
                });
                
                return result;
                
            } catch (error) {
                return {
                    success: false,
                    message: error.message
                };
            }
        }

        // 加载编辑模式下的可选题目
        async function loadEditAvailableQuestions(setNumber) {
            try {
                const result = await getQuestionsForEdit(setNumber);
                
                if (result.success) {
                    renderEditAvailableQuestions(result.questions);
                    updateEditSelectedCount();
                } else {
                    notification.toast('加载题目失败: ' + (result.message || '未知错误'), 'error');
                }
            } catch (error) {
                notification.toast('加载题目时发生错误', 'error');
            }
        }

        // 渲染编辑模式下的可选题目
        function renderEditAvailableQuestions(questions) {
            const container = document.getElementById('edit-available-questions');
            if (!container) return;
            
            if (!questions || questions.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">暂无可选题目</p>';
                return;
            }
            
            const html = questions.map(question => {
                const isSelected = editSelectedQuestionIds.includes(question.id.toString());
                const selectedClass = isSelected ? 'selected' : '';
                
                return `
                    <div class="question-item ${selectedClass}" data-question-id="${question.id}" onclick="toggleEditQuestionSelection('${question.id}')">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <input type="checkbox" ${isSelected ? 'checked' : ''} style="margin-top: 2px;" onclick="event.stopPropagation(); toggleEditQuestionSelection('${question.id}');">
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: #333; line-height: 1.4;">
                                    (编号: ${question.id}) ${(question.question || question.content || '无内容').substring(0, 120)}${(question.question || question.content || '').length > 120 ? '...' : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            container.innerHTML = html;
        }

        // 切换编辑模式下的题目选择
        function toggleEditQuestionSelection(questionId) {
            const index = editSelectedQuestionIds.indexOf(questionId.toString());
            if (index > -1) {
                editSelectedQuestionIds.splice(index, 1);
            } else {
                editSelectedQuestionIds.push(questionId.toString());
            }
            
            // 更新UI
            const questionItem = document.querySelector(`#edit-available-questions .question-item[data-question-id="${questionId}"]`);
            const checkbox = questionItem?.querySelector('input[type="checkbox"]');
            
            if (questionItem && checkbox) {
                const isSelected = editSelectedQuestionIds.includes(questionId.toString());
                checkbox.checked = isSelected;
                questionItem.classList.toggle('selected', isSelected);
            }
            
            updateEditSelectedCount();
        }

        // 编辑模式下的全选/取消全选
        function toggleAllEditQuestions() {
            const questionItems = document.querySelectorAll('#edit-available-questions .question-item');
            const allSelected = questionItems.length > 0 && editSelectedQuestionIds.length === questionItems.length;
            
            if (allSelected) {
                // 取消全选
                editSelectedQuestionIds = [];
            } else {
                // 全选
                editSelectedQuestionIds = Array.from(questionItems).map(item => item.dataset.questionId);
            }
            
            // 更新UI
            questionItems.forEach(item => {
                const questionId = item.dataset.questionId;
                const checkbox = item.querySelector('input[type="checkbox"]');
                const isSelected = editSelectedQuestionIds.includes(questionId);
                
                checkbox.checked = isSelected;
                item.classList.toggle('selected', isSelected);
            });
            
            updateEditSelectedCount();
        }

        // 更新套题
        async function updateQuestionSet() {
            if (!window.currentEditingSetNumber) {
                notification.toast('未找到要更新的套题信息', 'error');
                return;
            }
            
            if (editSelectedQuestionIds.length === 0) {
                notification.toast('请至少选择一道题目', 'warning');
                return;
            }
            
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'update_question_set',
                    set_number: window.currentEditingSetNumber,
                    question_ids: editSelectedQuestionIds.join(',')
                });
                
                if (result.success) {
                    notification.toast('套题更新成功', 'success');
                    closeEditQuestionSetModal();
                    loadQuestionSets(); // 重新加载套题列表
                } else {
                    notification.toast('更新失败: ' + result.message, 'error');
                }
            } catch (error) {
                notification.toast('更新套题时发生错误', 'error');
            }
        }

        // 删除套题
        async function deleteQuestionSet(setNumber) {
            notification.confirm('确定要删除这个套题吗？', '确认删除', {
                onConfirm: async () => {
                    try {
                        const result = await fetchData('question_sets.php', {
                            action: 'delete_question_set',
                            set_number: setNumber
                        });
                        
                        if (result.success) {
                            notification.toast('套题删除成功', 'success');
                            await checkAndRedirectToFirstPageForSets();
                        } else {
                            notification.toast('删除失败: ' + result.message, 'error');
                        }
                    } catch (error) {
                        notification.toast('删除失败: ' + error.message, 'error');
                    }
                }
            });
        }

        // 检查并跳转到第一页（套题管理）
        async function checkAndRedirectToFirstPageForSets() {
            // 重新加载当前页数据以检查是否还有数据
            try {
                const result = await fetchData('question_sets.php', {
                    action: 'get_question_sets',
                    page: currentQuestionSetsPage,
                    per_page: questionSetsPerPage,
                    search: document.getElementById('set-search').value
                });
                
                if (result.success) {
                    const questionSets = result.data.question_sets || result.data;
                    
                    // 条件1：不是第一页
                    // 条件2：当前页没有数据
                    if (currentQuestionSetsPage > 1 && questionSets.length === 0) {
                        // 跳转到第一页
                        loadQuestionSets(1, document.getElementById('set-search').value);
                    } else {
                        // 正常重新加载当前页
                        loadQuestionSets(currentQuestionSetsPage, document.getElementById('set-search').value);
                    }
                } else {
                    // 如果获取数据失败，重新加载当前页
                    loadQuestionSets(currentQuestionSetsPage, document.getElementById('set-search').value);
                }
            } catch (error) {
                // 如果出错，重新加载当前页
                loadQuestionSets(currentQuestionSetsPage, document.getElementById('set-search').value);
            }
        }

        // 批量导入题目
        async function importQuestions(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('import-file');
            const file = fileInput.files[0];
            
            if (!file) {
                notification.toast('请选择要导入的文件', 'warning');
                return;
            }
            
            try {
                const result = await fetchData('questions.php', {
                    action: 'batch_import',
                    file: file
                });
                
                if (result.success) {
                    notification.toast(result.message || '导入成功', 'success');
                    // 清空文件上传框
                    removeFile();
                    closeModal('import-modal');
                    loadQuestions(currentQuestionsPage);
                } else {
                    notification.toast(result.message || '导入失败', 'error');
                }
            } catch (error) {
                notification.toast('导入失败: ' + error.message, 'error');
            }
        }

        // 打开模态框
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        // 关闭模态框
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // 使用notification模块替代原有的showAlert函数
        function showAlert(message, type = 'info') {
            switch(type) {
                case 'success':
                    notification.success(message);
                    break;
                case 'error':
                    notification.error(message);
                    break;
                case 'warning':
                    notification.warning(message);
                    break;
                default:
                    notification.info(message);
            }
        }

        // 设置文件上传功能
        function setupFileUpload() {
            const uploadArea = document.getElementById('file-upload-area');
            const fileInput = document.getElementById('import-file');
            const fileInfo = document.getElementById('file-info');

            // 拖拽事件
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });

            // 文件选择事件
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }

        // 处理文件选择
        function handleFileSelect(file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            
            if (!allowedTypes.includes(file.type)) {
                notification.toast('请选择Excel文件（.xlsx或.xls格式）', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB限制
                notification.toast('文件大小不能超过10MB', 'error');
                return;
            }

            // 显示文件信息
            const uploadArea = document.getElementById('file-upload-area');
            const fileInfo = document.getElementById('file-info');
            const fileName = fileInfo.querySelector('.file-name');
            const fileSize = fileInfo.querySelector('.file-size');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            uploadArea.style.display = 'none';
            fileInfo.style.display = 'flex';
        }

        // 移除文件
        function removeFile() {
            const uploadArea = document.getElementById('file-upload-area');
            const fileInfo = document.getElementById('file-info');
            const fileInput = document.getElementById('import-file');

            fileInput.value = '';
            uploadArea.style.display = 'block';
            fileInfo.style.display = 'none';
        }

        // 格式化文件大小
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // 全选/取消全选功能
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all-questions');
            const questionCheckboxes = document.querySelectorAll('.question-checkbox');
            
            questionCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBatchDeleteButton();
        }

        // 更新批量删除按钮显示状态
        function updateBatchDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.question-checkbox:checked');
            const batchDeleteBtn = document.getElementById('batch-delete');
            
            if (checkedBoxes.length > 0) {
                batchDeleteBtn.style.display = 'inline-block';
            } else {
                batchDeleteBtn.style.display = 'none';
            }
            
            // 更新全选状态
            const allCheckboxes = document.querySelectorAll('.question-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-questions');
            
            if (allCheckboxes.length > 0) {
                selectAllCheckbox.checked = checkedBoxes.length === allCheckboxes.length;
            }
        }

        // 批量删除题目
        async function batchDeleteQuestions() {
            const checkedBoxes = document.querySelectorAll('.question-checkbox:checked');
            const questionIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);
            
            if (questionIds.length === 0) {
                notification.toast('请选择要删除的题目', 'warning');
                return;
            }
            
            try {
                // 先检查题目使用情况
                const checkFormData = new FormData();
                checkFormData.append('action', 'batch_check_question_usage');
                checkFormData.append('question_ids', JSON.stringify(questionIds));
                
                const checkResponse = await fetch('questions.php', {
                    method: 'POST',
                    body: checkFormData
                });
                
                const checkResult = await checkResponse.json();
                
                if (!checkResult.success) {
                    showAlert('检查题目使用情况失败: ' + checkResult.message, 'error');
                    return;
                }
                
                let confirmMessage = `确定要删除选中的 ${questionIds.length} 道题目吗？`;
                if (Object.keys(checkResult.usage_info).length > 0) {
                    const usedQuestions = Object.keys(checkResult.usage_info);
                    const affectedSets = new Set();
                    
                    for (const questionId in checkResult.usage_info) {
                        checkResult.usage_info[questionId].forEach(setId => affectedSets.add(setId));
                    }
                    
                    const setNumbers = Array.from(affectedSets).join('、');
                    confirmMessage = `选中的题目中有 ${usedQuestions.length} 道题目已被套题 ${setNumbers} 使用，若删除则自动移除套题对这些题目的引用，是否继续删除？`;
                }
                
                notification.confirm(confirmMessage, '确认批量删除', {
                    onConfirm: async () => {
                        try {
                            const formData = new FormData();
                            formData.append('action', 'batch_delete_questions');
                            formData.append('question_ids', JSON.stringify(questionIds));
                            
                            const response = await fetch('questions.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const result = await response.json();
                            
                            if (result.success) {
                                notification.toast(result.message, 'success');
                                await checkAndRedirectToFirstPage();
                            } else {
                                notification.toast('批量删除失败: ' + result.message, 'error');
                            }
                        } catch (error) {
                            notification.toast('批量删除失败: ' + error.message, 'error');
                        }
                    }
                });
                return;
            } catch (error) {
                notification.toast('批量删除失败: ' + error.message, 'error');
            }
        }

        // 检查并跳转到第一页（如果当前页没有数据且不是第一页）
        async function checkAndRedirectToFirstPage() {
            // 重新加载当前页数据以检查是否还有数据
            try {
                const formData = new FormData();
                formData.append('action', 'get_questions');
                formData.append('page', currentQuestionsPage);
                formData.append('per_page', questionsPerPage);
                formData.append('search', currentSearchTerm);
                
                const response = await fetch('questions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const questions = result.data.questions || result.data;
                    
                    // 条件1：不是第一页
                    // 条件2：当前页没有数据
                    if (currentQuestionsPage > 1 && questions.length === 0) {
                        // 跳转到第一页
                        loadQuestions(1, currentSearchTerm);
                    } else {
                        // 正常重新加载当前页
                        loadQuestions(currentQuestionsPage, currentSearchTerm);
                    }
                } else {
                    // 如果获取数据失败，重新加载当前页
                    loadQuestions(currentQuestionsPage, currentSearchTerm);
                }
            } catch (error) {
                // 如果出错，重新加载当前页
                loadQuestions(currentQuestionsPage, currentSearchTerm);
            }
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化标签页
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });

            // 初始化搜索功能（实时搜索）
            const questionSearchInput = document.getElementById('question-search');
            if (questionSearchInput) {
                // 实时搜索：输入时直接搜索
                questionSearchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.trim();
                    
                    // 清除之前的延迟搜索
                    clearTimeout(this.searchTimeout);
                    
                    // 如果搜索框为空且之前有搜索词，立即恢复原表内容
                    if (searchTerm === '' && currentSearchTerm !== '') {
                        currentSearchTerm = '';
                        currentQuestionsPage = 1; // 重置页码
                        loadQuestions(1, '');
                    } else if (searchTerm !== '' && searchTerm !== currentSearchTerm) {
                        // 延迟搜索，避免频繁请求
                        this.searchTimeout = setTimeout(() => {
                            currentSearchTerm = searchTerm;
                            currentQuestionsPage = 1; // 重置页码
                            loadQuestions(1, searchTerm);
                        }, 300);
                    }
                });
                
                // 保留回车键搜索功能
                questionSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const searchTerm = e.target.value.trim();
                        currentSearchTerm = searchTerm;
                        currentQuestionsPage = 1; // 重置页码
                        loadQuestions(1, searchTerm);
                    }
                });
            }

            // 初始化套题搜索功能（实时搜索）
            const setSearchInput = document.getElementById('set-search');
            if (setSearchInput) {
                // 实时搜索：输入时直接搜索
                setSearchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.trim();
                    
                    // 清除之前的延迟搜索
                    clearTimeout(this.searchTimeout);
                    
                    // 如果搜索框为空且之前有搜索词，立即恢复原表内容
                    if (searchTerm === '' && currentSetSearchTerm !== '') {
                        currentSetSearchTerm = '';
                        currentQuestionSetsPage = 1; // 重置页码
                        loadQuestionSets(1, '');
                    } else if (searchTerm !== '' && searchTerm !== currentSetSearchTerm) {
                        // 延迟搜索，避免频繁请求
                        this.searchTimeout = setTimeout(() => {
                            currentSetSearchTerm = searchTerm;
                            currentQuestionSetsPage = 1; // 重置页码
                            loadQuestionSets(1, searchTerm);
                        }, 300);
                    }
                });
                
                // 保留回车键搜索功能
                setSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const searchTerm = e.target.value.trim();
                        currentSetSearchTerm = searchTerm;
                        currentQuestionSetsPage = 1; // 重置页码
                        loadQuestionSets(1, searchTerm);
                    }
                });
            }

            // 初始化按钮事件
            document.getElementById('add-question').addEventListener('click', () => openQuestionModal());
            document.getElementById('batch-import').addEventListener('click', () => openModal('import-modal'));
            document.getElementById('batch-delete').addEventListener('click', batchDeleteQuestions);
            document.getElementById('create-question-set').addEventListener('click', openQuestionSetModal);

            // 初始化全选功能
            document.getElementById('select-all-questions').addEventListener('change', toggleSelectAll);

            // 初始化模态框关闭事件
            document.querySelectorAll('.close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal.id);
                });
            });

            // 点击模态框外部关闭
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this.id);
                    }
                });
            });

            // 初始化表单提交事件
            document.getElementById('question-form').addEventListener('submit', saveQuestion);
            document.getElementById('question-set-form').addEventListener('submit', saveQuestionSet);
            document.getElementById('edit-question-set-form').addEventListener('submit', function(e) {
                e.preventDefault();
                updateQuestionSet();
            });
            document.getElementById('import-form').addEventListener('submit', importQuestions);

            // 创建套题模态框的全选按钮
            document.getElementById('toggle-all-questions').addEventListener('click', toggleAllQuestions);
            
            // 编辑模态框的全选按钮
            document.getElementById('edit-toggle-all-questions').addEventListener('click', toggleAllEditQuestions);

            // 创建套题模态框的搜索功能
            document.getElementById('question-filter').addEventListener('input', function(e) {
                const searchTerm = e.target.value;
                filterAvailableQuestions();
            });
            
            // 编辑模态框的搜索功能
            document.getElementById('edit-question-filter').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const questionItems = document.querySelectorAll('#edit-available-questions .question-item');
                
                questionItems.forEach(item => {
                    const content = item.textContent.toLowerCase();
                    if (content.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // 初始化文件上传
            setupFileUpload();

            // 初始化每页数量选择器事件
            document.getElementById('questions-per-page').addEventListener('change', function() {
                questionsPerPage = parseInt(this.value);
                loadQuestions(1, document.getElementById('question-search').value);
            });
            
            document.getElementById('sets-per-page').addEventListener('change', function() {
                questionSetsPerPage = parseInt(this.value);
                loadQuestionSets(1, currentSetSearchTerm);
            });

            // 加载初始数据
            loadQuestions();
            loadQuestionSets();
            loadCategories();
        });
    </script>
</body>
</html>