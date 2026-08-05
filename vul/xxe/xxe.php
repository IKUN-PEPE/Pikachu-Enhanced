<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[99] = 'active open';
$ACTIVE[100] = 'active';

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
                    XXE 外部实体注入 (XML External Entity)
                    <span class="overview-badge">XML 解析器漏洞</span>
                </h1>
                <p>XXE 漏洞发生在应用程序解析 XML 输入时，未禁用外部实体的加载。攻击者可以构造包含恶意外置实体引用的 XML 报文，当 XML 解析器处理该数据时，将读取服务器本地敏感文件、发起内部端口扫描 (SSRF) 或引发拒绝服务攻击 (Billion Laughs)。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #06b6d4;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 寻找 XML 解析接口</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">定位接收 `Content-Type: text/xml` 或 SOAP 协议的 API 请求点。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 注入 DTD 外部实体</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">在 XML 头声明 `<!DOCTYPE foo [<!ENTITY xxe SYSTEM 'file:///etc/passwd'> ]>`。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #a855f7;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 调用实体引用</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">在实际的 XML 节点中使用 `&xxe;` 触发解析器去读取外部系统文件。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 无回显 XXE (Blind)</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">如果无直接响应回显，则使用外带注入 (OOB) 将文件内容发送到攻击者的远程服务器。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #0891b2;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📂 1. 有回显文件读取</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">最常见的利用方式，直接利用外部实体 SYSTEM 读取本地文件并回显在 HTTP 响应中。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #7c3aed;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🌐 2. XXE 结合 SSRF</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">使用 HTTP 协议的外部实体 (SYSTEM 'http://10.0.0.1:80') 探测内网或攻击内网服务。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛡️ 3. 防御方案</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">在各种语言的 XML 解析器（如 libxml_disable_entity_loader）中强行关闭外部实体与 DTD 校验功能。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="xxe_1.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 有回显 XXE 漏洞</div>
                <div class="shortcut-desc">构造外部实体读取系统敏感文件</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="xxe_blind.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. 无回显 XXE 漏洞 (Blind)</div>
                <div class="shortcut-desc">使用 OOB 外带方式获取文件内容</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
