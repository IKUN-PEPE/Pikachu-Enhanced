<?php
/**
 * Pikachu-Enhanced Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[55] = 'active open';
$ACTIVE[56] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #1e3a8a, #172554);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.1);
}
.overview-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.overview-badge {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid #60a5fa;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.overview-hero-card p {
    font-size: 15px;
    color: #e2e8f0;
    line-height: 1.7;
    max-width: 950px;
    margin-bottom: 0;
}

.workflow-section {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 30px;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.workflow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}
.workflow-step {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.2s ease;
}
.workflow-step:hover {
    transform: translateY(-3px);
}
.step-icon-badge {
    width: 36px;
    height: 36px;
    background: #2563eb;
    color: #ffffff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 12px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.detail-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    border-top: 4px solid #2563eb;
}

.lab-shortcuts {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.shortcut-card {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px 20px;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.shortcut-card:hover {
    border-color: #2563eb;
    transform: translateX(4px);
}
.shortcut-title {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 15px;
}
.shortcut-desc {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 2px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="overview-hero-card">
                <h1>
                    File Inclusion 文件包含漏洞 (Local & Remote)
                    <span class="overview-badge">服务器文件安全</span>
                </h1>
                <p>文件包含漏洞发生在后端代码调用 `include()`, `require()`, `include_once()` 等函数时，包含的文件路径参数可由外部用户控制。若未限制路径范围，攻击者可读取任意系统文件，甚至配合 `data://` / `php://input` 伪协议完成远程代码执行。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 寻找文件包含参数</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">发现目标 URL 包含 `?file=page.php` 或 `?filename=about` 等路径加载参数。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #f59e0b;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 尝试目录穿越</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">传入 `../../../../etc/passwd` 或 `C:\boot.ini` 探测是否可以跨越当前 Web 根目录。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #ef4444;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 伪协议利用</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">尝试使用 `php://filter/read=convert.base64-encode/resource=index.php` 提取后端 PHP 源码。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 日志/上传配合 RCE</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">包含 Apache/Nginx 访问日志 (Log Injection) 或上传的图片 Webshell 转化为代码执行。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #2563eb;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📂 1. 本地文件包含 (LFI)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">包含本地已有文件。可结合 `../../` 目录穿越读取 `/etc/passwd`、访问日志、临时 Session 文件。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #ef4444;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🌐 2. 远程文件包含 (RFI)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">当 PHP 配置开启 `allow_url_include=On` 时，可直接包含远程外部恶意代码（如 `http://evil.com/shell.txt`）。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🧬 3. PHP 伪协议 (Stream Wrappers)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">利用 `php://filter` 绕过源码执行直接读取 Base64 代码；利用 `data://text/plain;base64,...` 直接注入命令。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="fi_local.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 本地文件包含 (LFI) 演练</div>
                <div class="shortcut-desc">测试读取服务器敏感文件与源码</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
        <a href="fi_remote.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. 远程文件包含 (RFI) 演练</div>
                <div class="shortcut-desc">测试包含远程 URL 脚本执行</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
