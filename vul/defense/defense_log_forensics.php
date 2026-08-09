<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Level 3: Web Log Forensics & Incident Response
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[224] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$answers = [
    'ip' => '192.168.56.88',
    'shell' => 'shell.php',
    'type' => 'upload'
];

$feedback = '';

if (isset($_POST['submit_forensics'])) {
    $user_ip = trim($_POST['user_ip']);
    $user_shell = trim($_POST['user_shell']);
    $user_type = trim($_POST['user_type']);

    if ($user_ip === $answers['ip'] && strtolower($user_shell) === $answers['shell'] && $user_type === $answers['type']) {
        $feedback = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！分析完全正确！成功提取入侵源 IP [192.168.56.88] 与恶意 WebShell [shell.php]，完成应急响应排查！</div>';
    } else {
        $feedback = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> 分析结论存在偏差，请仔细观察 Nginx Access Log 中 HTTP 200 返回码与敏感上传/命令执行路径。</div>';
    }
}
?>

<style>
.forensics-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
}
.forensics-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.log-terminal {
    background: #0f172a;
    color: #38bdf8;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 12px;
    line-height: 1.7;
    height: 280px;
    overflow-y: auto;
    border: 1px solid #1e293b;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="forensics-hero-banner">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <span class="label label-info" style="font-size: 14px; border-radius: 6px;">LEVEL 3</span>
                            🔍 Web 入侵日志取证与应急响应排查实验室
                        </h1>
                        <p style="margin: 0; color: #bae6fd; font-size: 14px;">
                            <strong>防守维度：</strong> Nginx Access Log 取证、攻击链路还原、威胁 IP 标记与 WebShell 排查
                        </p>
                    </div>
                    <a href="defense.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回蓝队总控大厅
                    </a>
                </div>
            </div>

            <!-- Theory -->
            <div class="forensics-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-search" style="color: #0284c7;"></i> 日志取证 (Log Forensics) 的核心判定方法</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在发生 WebSec 安全事件时，蓝队应急响应人员的第一步是调取服务器的 <code>access.log</code>。
                    通过观察 <code>HTTP 状态码</code>（如 <code>404</code> 表示试探扫描，<code>200</code> 且响应字节数发生突变表示成功利用）、<code>请求方法 (POST)</code>、<code>URI Payload 关键字</code>，可以完整重构黑客从扫描探测 $\to$ 寻找漏洞 $\to$ 上传木马 $\to$ 命令行调用的全生命周期链路。
                </p>
            </div>

            <!-- Access Log Terminal -->
            <div class="forensics-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-terminal" style="color: #38bdf8;"></i> 现场调取的 Nginx /var/log/nginx/access.log 原始日志</h3>
                
                <div class="log-terminal">
192.168.56.1 - - [09/Aug/2026:20:10:01 +0800] "GET / HTTP/1.1" 200 4521 "-" "Mozilla/5.0"
192.168.56.88 - - [09/Aug/2026:20:11:05 +0800] "GET /admin/login.php HTTP/1.1" 404 162 "-" "Gobuster/3.1"
192.168.56.88 - - [09/Aug/2026:20:11:08 +0800] "GET /vul/sqli/sqli_str.php?name=1%27%20UNION%20SELECT%201,version()-- HTTP/1.1" 200 890 "-" "sqlmap/1.5"
192.168.56.88 - - [09/Aug/2026:20:12:30 +0800] "GET /vul/unsafeupload/upload.php HTTP/1.1" 200 3210 "http://192.168.56.100/" "Mozilla/5.0"
192.168.56.88 - - [09/Aug/2026:20:13:15 +0800] "POST /vul/unsafeupload/clientcheck.php HTTP/1.1" 200 450 "http://192.168.56.100/" "Mozilla/5.0"
192.168.56.88 - - [09/Aug/2026:20:13:40 +0800] "POST /vul/unsafeupload/uploads/shell.php HTTP/1.1" 200 1280 "-" "Mozilla/5.0"
192.168.56.88 - - [09/Aug/2026:20:14:02 +0800] "GET /vul/unsafeupload/uploads/shell.php?cmd=cat+/etc/passwd HTTP/1.1" 200 2450 "-" "Mozilla/5.0"
192.168.56.88 - - [09/Aug/2026:20:15:10 +0800] "GET /vul/unsafeupload/uploads/shell.php?cmd=whoami HTTP/1.1" 200 320 "-" "Mozilla/5.0"
192.168.56.1 - - [09/Aug/2026:20:16:00 +0800] "GET /intro.php HTTP/1.1" 200 5600 "-" "Mozilla/5.0"
                </div>
            </div>

            <!-- Forensic Q&A Form -->
            <div class="forensics-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-edit" style="color: #10b981;"></i> 蓝队应急响应分析判定提交表单</h3>
                
                <form method="post" style="max-width: 700px;">
                    <div class="form-group">
                        <label style="font-weight: 700;">1. 攻击者的真实源 IP 地址 (Attacker IP):</label>
                        <input type="text" name="user_ip" class="form-control" placeholder="例如: 192.168.x.x" required style="border-radius: 8px; font-family: monospace;">
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 700;">2. 攻击者成功上传并在服务器上调用的 WebShell 文件名 (WebShell Filename):</label>
                        <input type="text" name="user_shell" class="form-control" placeholder="例如: xxx.php" required style="border-radius: 8px; font-family: monospace;">
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 700;">3. 攻击者用于 Getshell 的核心漏洞类型 (Root Cause Vulnerability):</label>
                        <select name="user_type" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                            <option value="sqli">A. SQL 注入脱库 (SQL Injection)</option>
                            <option value="upload">B. 不安全的文件上传 (Unsafe File Upload)</option>
                            <option value="xss">C. 存储型 XSS 盗取 Cookie</option>
                        </select>
                    </div>

                    <button type="submit" name="submit_forensics" class="btn btn-info" style="border-radius: 8px; font-weight: 700; padding: 10px 24px;">
                        <i class="fa fa-check"></i> 提交应急响应排查报告
                    </button>
                </form>

                <?php if (!empty($feedback)) { echo '<div style="margin-top: 20px;">' . $feedback . '</div>'; } ?>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
