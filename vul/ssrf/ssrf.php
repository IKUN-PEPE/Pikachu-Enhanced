<?php
/**
 * Pikachu-Enhanced Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[85] = 'active open';
$ACTIVE[86] = 'active';

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
                    SSRF 服务端请求伪造 (Server-Side Request Forgery)
                    <span class="overview-badge">内网边界突破利器</span>
                </h1>
                <p>SSRF (服务端请求伪造) 是一种由服务端发起 HTTP/网络请求的安全漏洞。攻击者通过诱导处于内网边界的 Web 服务器去访问攻击者无法直接触及的内部网络（如内网 10.0.0.X、192.168.X.X、Redis 6379、云元数据 169.254.169.254），从而绕过防火墙打穿内网。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 定位 URL 提取接口</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">寻找支持从外部加载图片、转存网页、WebHook 或网页预览功能的 API 接口。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #f59e0b;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 修改目标 URL 为内网地址</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">将请求参数由 `http://external.com/img.jpg` 替换为 `http://127.0.0.1:80` 或 `http://10.0.0.5:6379`。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #ef4444;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 利用协议扩展 (Gopher/Dict)</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">使用 `gopher://` 或 `dict://` 协议弹射 Redis 执行反弹 Shell 命令，或提取 AWS/阿里云元数据 Token。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 内网探测与控制</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">扫描内网开放端口与未授权微服务，彻底撕开内网防御大门。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #2563eb;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🌐 1. SSRF (cURL 场景)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">后端使用 `curl_exec()` 加载 URL。支持 `http://`, `https://`, `gopher://`, `dict://`, `file://` 等多种原生协议。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #ef4444;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📄 2. SSRF (file_get_contents 场景)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">后端使用 `file_get_contents()`。支持 `php://` 伪协议与本地文件读取。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">☁️ 3. 云元数据 & Gopher 打 Redis</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">通过 `169.254.169.254` 获取云实例 IAM 临时凭据；通过 `gopher://` 发送 RESP 协议攻击未授权 Redis 写入 SSH 公钥。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="ssrf_curl.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. SSRF (cURL) 演练</div>
                <div class="shortcut-desc">测试通过 cURL 接口探测内网及不同协议</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
        <a href="ssrf_fgc.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. SSRF (file_get_contents) 演练</div>
                <div class="shortcut-desc">测试通过 file_get_contents 伪造请求</div>
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
