<?php
/**
 * Pikachu-Enhanced Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[65] = 'active open';
$ACTIVE[66] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #7f1d1d, #450a0a);
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
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
    border: 1px solid #f87171;
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
                    Unsafe File Upload 不安全的文件上传
                    <span class="overview-badge">Webshell 提权核心入口</span>
                </h1>
                <p>不安全的文件上传是 Web 安全中危害最直接的漏洞之一。当系统允许用户上传文件（如头像、文档、附件），但未在服务端严格校验文件扩展名、MIME 类型以及文件内容时，攻击者可上传 `.php` 或 Webshell 脚本并直接访问解析，一步拿到服务器 Webshell 控制权。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 定位文件上传功能</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">找到头像上传、图片选择、文件导入或富文本编辑器附件上传接口。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #f59e0b;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 上传 Webshell 脚本</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">准备包含木马代码的文件 (如 `shell.php`)，并尝试直接发送上传。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #ef4444;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 绕过检测机制</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">若被拦截，通过修改 Client-Side JS、伪造 `Content-Type`、或利用 `.php5`/`.phtml`/`00截断` 绕过。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 访问 Webshell 获取权限</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">通过返回的文件相对路径访问木马文件，连接蚁剑/菜刀等 Webshell 管理工具。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #2563eb;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">💻 1. 前端 JS 校验绕过</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">验证仅在前端 `onchange` 事件中校验 `.jpg` 扩展名。禁用浏览器 JS 或用 Burp Suite 修改扩展名即可轻松绕过。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #ef4444;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🔍 2. MIME / Content-Type 伪造</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">服务端校验 `$_FILES['file']['type']`。只需使用 Burp 将 `application/x-php` 改为 `image/jpeg` 即可通过。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🖼️ 3. getimagesize() / 文件头校验</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">服务端读取文件头幻数 (GIF89a)。攻击者可将 Webshell 嵌入正常图片中制成图片马，再配合文件包含或解析漏洞触发。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="clientcheck.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 客户端 JS 检查绕过</div>
                <div class="shortcut-desc">测试绕过前端 JavaScript 扩展名校验</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
        <a href="servercheck.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. 服务端 MIME 检查绕过</div>
                <div class="shortcut-desc">测试伪造 Content-Type 标头绕过</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
        <a href="getimagesize.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">3. getimagesize() 图片马绕过</div>
                <div class="shortcut-desc">测试图片头幻数与图片马合成演练</div>
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
