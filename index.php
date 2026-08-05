<?php
/**
 * Pikachu-Enhanced System Introduction & Guide
 */
include_once 'inc/config.inc.php';
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[0] = 'active';
include 'header.php';

$html = '';
$link = @mysqli_connect(DBHOST, DBUSER, DBPW, DBNAME, DBPORT);
if(!$link){
    $html .= "<div class='alert alert-danger' style='margin-bottom: 20px; border-radius: 8px;'><i class='fa fa-exclamation-triangle'></i> <strong>提示：</strong>系统尚未初始化，<a href='install.php' style='text-decoration:underline; font-weight:bold;'>请点击此处进行安装和初始化</a>。</div>";
}else{
    @mysqli_set_charset($link, 'utf8');
    @mysqli_close($link);
}
?>
<style>
/* Modern Intro Page Styles */
.intro-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 30px;
}
.intro-banner {
    background: linear-gradient(135deg, var(--nav-bg-start), var(--nav-bg-end));
    border-radius: 16px;
    padding: 40px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.intro-banner::after {
    content: "\f132";
    font-family: FontAwesome;
    font-size: 150px;
    position: absolute;
    right: 40px;
    top: -20px;
    opacity: 0.1;
    transform: rotate(15deg);
}
.intro-banner h1 {
    font-size: 32px;
    font-weight: 800;
    margin-top: 0;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.intro-banner p {
    font-size: 18px; line-height: 1.8;
    max-width: 800px;
    opacity: 0.9;
    line-height: 1.6;
}

.intro-section {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid var(--border-color);
}
.intro-section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 15px;
}
.intro-section-title i {
    color: var(--nav-bg-start);
}

.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.module-card {
    background-color: var(--bg-secondary);
    border-radius: 10px;
    padding: 20px 30px;
    border: 1px solid var(--border-color);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.module-icon {
    font-size: 2rem;
    margin-bottom: 15px;
}
.module-card h3 {
    font-size: 18px; line-height: 1.8;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 10px;
}
.module-card p {
    color: var(--text-secondary);
    font-size: 15px;
    line-height: 1.5;
    margin-bottom: 0;
}

.guide-step {
    display: flex;
    margin-bottom: 25px;
    gap: 20px;
}
.step-number {
    width: 40px;
    height: 40px;
    background-color: var(--nav-bg-start);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    flex-shrink: 0;
}
.step-content h4 {
    margin-top: 0;
    font-size: 18px; margin-bottom: 12px;
    font-weight: bold;
    color: var(--text-primary);
    margin-bottom: 8px;
}
.step-content p {
    color: var(--text-secondary);
    line-height: 1.6;
}
.step-content code {
    background-color: var(--bg-primary);
    color: #e83e8c;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 14px;
}

.disclaimer-box {
    background-color: rgba(239, 68, 68, 0.1);
    border-left: 5px solid #ef4444;
    padding: 20px 30px;
    border-radius: 0 8px 8px 0;
    color: var(--text-primary);
}
.disclaimer-box h4 {
    color: #ef4444;
    margin-top: 0;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
}

[data-theme="dark"] .intro-banner {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}
[data-theme="dark"] .module-card {
    background-color: var(--bg-primary);
}
[data-theme="dark"] .step-content code {
    background-color: var(--bg-hover);
    color: #f472b6;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="index.php">系统介绍与说明</a>
                </li>
            </ul>
        </div>

        <div class="page-content">
            <?php echo $html; ?>

            <div class="intro-container">
                
                <!-- Hero Banner -->
                <div class="intro-banner">
                    <h1>Pikachu-Enhanced v2.0</h1>
                    <p>
                        欢迎来到 <strong>Pikachu-Enhanced</strong>，这是一个专为现代 Web 安全和高级渗透测试设计的综合实战演练平台。基于经典 Pikachu 平台进行全面重构与架构升级，不仅保留了经典的 Web 漏洞，更引进了云原生、AI 大模型、现代微服务协议等前沿安全实战场景，总计涵盖 <strong>5 大维度、41 大类、170+ 实战靶点</strong>。
                    </p>
                </div>

                <!-- Core Modules Section -->
                <div class="intro-section">
                    <h2 class="intro-section-title">
                        <i class="fa fa-cubes"></i> 五大核心实战维度
                    </h2>
                    
                    <div class="module-grid">
                        <div class="module-card" style="border-top: 4px solid var(--cat-classic);">
                            <div class="module-icon">🏛️</div>
                            <h3>经典 Web 攻防演练</h3>
                            <p>全面覆盖 OWASP Top 10 核心漏洞，包含 SQL 注入、XSS、CSRF、RCE、文件上传/下载、越权等基础漏洞的深度挖掘与利用，筑牢 Web 安全底座。</p>
                        </div>
                        
                        <div class="module-card" style="border-top: 4px solid var(--cat-cloud);">
                            <div class="module-icon">☁️</div>
                            <h3>云原生与微服务架构安全</h3>
                            <p>深入现代云基础设施，演练 Docker 容器逃逸、K8s RBAC 权限滥用、Serverless 无服务器架构攻击、以及云元数据 SSRF 深度利用技巧。</p>
                        </div>
                        
                        <div class="module-card" style="border-top: 4px solid var(--cat-ai);">
                            <div class="module-icon">🤖</div>
                            <h3>AI 与大模型应用安全</h3>
                            <p>应对生成式人工智能的安全挑战，包括 Prompt 注入绕过、模型数据投毒、RAG 检索增强生成欺骗，以及 AI 智能体 (Agent) 的沙箱越权与数据逃逸。</p>
                        </div>
                        
                        <div class="module-card" style="border-top: 4px solid var(--cat-proto);">
                            <div class="module-icon">🌐</div>
                            <h3>前沿协议与数据安全</h3>
                            <p>聚焦现代通讯协议与身份认证，涵盖 HTTP/2 走私、gRPC 协议攻击、WebSocket 劫持，以及 JWT/SAML/OAuth 等高级单点登录协议的伪造与绕过。</p>
                        </div>
                        
                        <div class="module-card" style="border-top: 4px solid #10b981;">
                            <div class="module-icon">🛡️</div>
                            <h3>蓝队防守与实战防御</h3>
                            <p>以攻促防，提供完整的防御机制研究，包括 WAF 规则绕过分析、RASP 运行时自我保护原理剖析，以及安全日志取证与应急响应训练。</p>
                        </div>
                    </div>
                </div>

                <!-- Usage Guide Section -->
                <div class="intro-section">
                    <h2 class="intro-section-title">
                        <i class="fa fa-book"></i> 平台使用与通关指南
                    </h2>
                    
                    <div class="guide-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>系统初始化部署</h4>
                            <p>平台推荐使用 Docker 环境部署。进入项目根目录执行 <code>docker compose up -d</code> 即可一键启动包含 PHP、MySQL、Nginx 和各种微服务的完整靶场群。首次访问时，请点击页面顶部的红色提示链接，进入 <code>install.php</code> 初始化底层数据库架构。</p>
                        </div>
                    </div>
                    
                    <div class="guide-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>配置代理与抓包工具</h4>
                            <p>许多高级漏洞（如协议走私、JWT 伪造、多步骤认证绕过）无法仅通过浏览器界面完成。强烈建议配置 <strong>Burp Suite</strong> 或 <strong>Postman</strong> 代理，拦截并修改底层 HTTP/1.1、HTTP/2 请求头及载荷。部分复杂并发漏洞可使用 Python 脚本自动化发包。</p>
                        </div>
                    </div>
                    
                    <div class="guide-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>白盒代码审计与调试</h4>
                            <p>Pikachu-Enhanced 不仅仅是一个“黑盒”靶场。所有漏洞都附带了存在缺陷的后端源码。遇到瓶颈时，可以直接进入项目对应的 <code>vul/</code> 目录，审查 PHP 代码逻辑，理解漏洞产生的根本原因（Root Cause）。知其然，更知其所以然。</p>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="disclaimer-box">
                    <h4><i class="fa fa-warning"></i> 法律免责与安全合规声明</h4>
                    <p style="margin-bottom: 0;">
                        本系统（Pikachu-Enhanced）是一款专为网络安全技术研究、防御技术测试、以及合法授权教学所设计的封闭式模拟演练平台。<strong>平台内置了大量高危且极易被利用的代码缺陷。</strong><br><br>
                        严禁将其部署在任何对外开放的公网环境或生产网络中。使用者应当严格遵守当地网络安全法律法规。所有在未经授权的真实目标上进行的黑客攻击行为均属违法犯罪。作者及开发团队不对任何因非法使用本平台技术而造成的直接或间接后果负责。
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>





