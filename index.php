<?php
/**
 * Pikachu-Enhanced v2.0 System Introduction & Guide
 */
include_once 'inc/config.inc.php';
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[0] = 'active';
include 'header.php';

$html = '';
$link = @mysqli_connect(DBHOST, DBUSER, DBPW, DBNAME, DBPORT);
if(!$link){
    $html .= "<div class='alert alert-danger' style='margin-bottom: 20px;'><i class='fa fa-exclamation-triangle'></i> <strong>提示：</strong>系统尚未初始化，<a href='install.php' style='text-decoration:underline; font-weight:bold; color:inherit;'>请点击此处进行安装和初始化</a>。</div>";
}else{
    @mysqli_set_charset($link, 'utf8');
    @mysqli_close($link);
}
?>
<style>
/* Modern Home Index Page Styles */
.intro-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 10px 0 40px 0;
}

.intro-banner {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.95), rgba(6, 182, 212, 0.95));
    border-radius: var(--radius-xl);
    padding: 40px;
    color: #ffffff;
    box-shadow: var(--shadow-lg);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

[data-theme="dark"] .intro-banner {
    background: linear-gradient(135deg, #1e3a8a, #0e7490);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.intro-banner::after {
    content: "\f132";
    font-family: FontAwesome;
    font-size: 170px;
    position: absolute;
    right: 40px;
    top: -25px;
    opacity: 0.12;
    transform: rotate(15deg);
    pointer-events: none;
}

.intro-banner h1 {
    font-size: 32px;
    font-weight: 800;
    margin-top: 0;
    margin-bottom: 12px;
    color: #ffffff;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.intro-banner p {
    font-size: 15.5px;
    line-height: 1.7;
    max-width: 850px;
    opacity: 0.95;
    color: #f1f5f9;
    margin-bottom: 24px;
}

.intro-banner-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.intro-banner-btn {
    background: #ffffff;
    color: var(--primary);
    border: none;
    padding: 9px 20px;
    border-radius: var(--radius-md);
    font-size: 13.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-md);
    transition: all 0.2s ease;
}
.intro-banner-btn:hover {
    background: #f8fafc;
    color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.intro-banner-btn-secondary {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #ffffff;
    padding: 9px 20px;
    border-radius: var(--radius-md);
    font-size: 13.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.intro-banner-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.28);
    color: #ffffff;
    transform: translateY(-2px);
}

.intro-section {
    background-color: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    margin-bottom: 28px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-subtle);
}

.intro-section-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 2px solid var(--border-subtle);
    padding-bottom: 14px;
}

.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.module-card {
    background-color: var(--bg-secondary);
    border-radius: var(--radius-md);
    padding: 24px;
    border: 1px solid var(--border-subtle);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.module-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
    background-color: var(--bg-primary);
}

.module-icon {
    font-size: 2.2rem;
    margin-bottom: 12px;
}

.module-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 8px;
}

.module-card p {
    color: var(--text-secondary);
    font-size: 13.5px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.guide-step {
    display: flex;
    margin-bottom: 22px;
    gap: 18px;
}

.step-number {
    width: 36px;
    height: 36px;
    background-color: var(--primary-light);
    color: var(--primary);
    border: 1px solid rgba(37, 99, 235, 0.3);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 800;
    flex-shrink: 0;
}

.step-content h4 {
    margin-top: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.step-content p {
    color: var(--text-secondary);
    font-size: 13.5px;
    line-height: 1.6;
    margin-bottom: 0;
}

.step-content code {
    background-color: var(--bg-tertiary);
    color: var(--primary);
    border: 1px solid var(--border-subtle);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-size: 13px;
}

.disclaimer-box {
    background-color: var(--danger-light);
    border-left: 4px solid var(--danger);
    padding: 20px 24px;
    border-radius: var(--radius-md);
    color: var(--text-primary);
}

.disclaimer-box h4 {
    color: var(--danger);
    margin-top: 0;
    margin-bottom: 8px;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.disclaimer-box p {
    font-size: 13px;
    line-height: 1.6;
    color: var(--text-secondary);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="index.php">主页</a>
                </li>
                <li class="active">平台系统说明</li>
            </ul>
        </div>

        <div class="page-content">
            <?php echo $html; ?>

            <div class="intro-container">
                
                <!-- Hero Banner -->
                <div class="intro-banner">
                    <h1>⚡ Pikachu-Enhanced v2.0</h1>
                    <p>
                        欢迎来到 <strong>Pikachu-Enhanced</strong>，专为现代 Web 安全和高级渗透测试设计的综合实战演练平台。基于经典 Pikachu 进行架构重构与深度拓展，涵盖 <strong>5 大攻防维度、41 大类场景、170+ 实战关卡</strong>，深度融合云原生、AI 大模型安全与微服务前沿协议。
                    </p>
                    <div class="intro-banner-actions">
                        <a href="intro.php" class="intro-banner-btn">
                            <i class="fa fa-map-signs"></i> 浏览全站漏洞学习路线图
                        </a>
                        <a href="vul/dockerlab/dockerlab_center.php" class="intro-banner-btn-secondary">
                            <i class="fa fa-cubes"></i> 容器与微服务控制台
                        </a>
                        <a href="vul/xss/xss.php" class="intro-banner-btn-secondary">
                            <i class="fa fa-shield"></i> 经典 Web 攻防演练
                        </a>
                    </div>
                </div>

                <!-- Core Modules Section -->
                <div class="intro-section">
                    <h2 class="intro-section-title">
                        <i class="fa fa-cubes" style="color:var(--primary);"></i> 五大核心实战维度
                    </h2>
                    
                    <div class="module-grid">
                        <div class="module-card" style="border-top: 3px solid var(--cat-classic);">
                            <div class="module-icon">🏛️</div>
                            <h3>经典 Web 攻防演练</h3>
                            <p>全面覆盖 OWASP Top 10 核心漏洞，包含 SQL 注入、XSS、CSRF、RCE、文件上传/包含、越权等基础漏洞的深度挖掘与利用，筑牢 Web 安全底座。</p>
                            <a href="vul/burteforce/burteforce.php" class="btn btn-xs btn-default"><i class="fa fa-arrow-right"></i> 进入演练</a>
                        </div>
                        
                        <div class="module-card" style="border-top: 3px solid var(--cat-cloud);">
                            <div class="module-icon">☁️</div>
                            <h3>云原生与微服务架构安全</h3>
                            <p>深入现代云基础设施，演练 Docker 容器特权逃逸、Socket 逃逸、K8s RBAC 权限滥用、Serverless 无服务器攻击及云元数据 SSRF 深度利用。</p>
                            <a href="vul/dockerlab/dockerlab_center.php" class="btn btn-xs btn-default"><i class="fa fa-arrow-right"></i> 进入演练</a>
                        </div>
                        
                        <div class="module-card" style="border-top: 3px solid var(--cat-ai);">
                            <div class="module-icon">🤖</div>
                            <h3>AI 与大模型应用安全</h3>
                            <p>应对生成式人工智能的安全挑战，包括 Prompt 注入绕过、模型数据投毒、RAG 检索增强生成欺骗，以及 AI 智能体 (Agent) 沙箱越权与数据逃逸。</p>
                            <a href="vul/ai_security/prompt_injection.php" class="btn btn-xs btn-default"><i class="fa fa-arrow-right"></i> 进入演练</a>
                        </div>
                        
                        <div class="module-card" style="border-top: 3px solid var(--cat-proto);">
                            <div class="module-icon">🌐</div>
                            <h3>前沿协议与身份安全</h3>
                            <p>聚焦现代通讯协议与身份认证，涵盖 HTTP/2 请求走私、gRPC 协议攻击、WebSocket 劫持，以及 JWT / SAML / OAuth 高级 SSO 单点登录伪造与绕过。</p>
                            <a href="vul/http_smuggling/cl_te.php" class="btn btn-xs btn-default"><i class="fa fa-arrow-right"></i> 进入演练</a>
                        </div>
                        
                        <div class="module-card" style="border-top: 3px solid var(--success);">
                            <div class="module-icon">🛡️</div>
                            <h3>蓝队防守与实战防御</h3>
                            <p>以攻促防，提供完整的防御机制研究，包括 WAF 规则绕过分析、RASP 运行时自我保护原理剖析，以及安全日志取证与应急响应训练。</p>
                            <a href="vul/ad_security/ad_ctf_kerberoast.php" class="btn btn-xs btn-default"><i class="fa fa-arrow-right"></i> 进入演练</a>
                        </div>
                    </div>
                </div>

                <!-- Usage Guide Section -->
                <div class="intro-section">
                    <h2 class="intro-section-title">
                        <i class="fa fa-book" style="color:var(--primary);"></i> 平台使用与通关指南
                    </h2>
                    
                    <div class="guide-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>系统初始化部署</h4>
                            <p>平台推荐使用 Docker 环境部署。进入项目根目录执行 <code>docker compose up -d</code> 即可一键启动包含 PHP、MySQL、Nginx 和各种微服务的完整靶场群。首次访问请点击页面顶部的初始化链接完成底层数据库架构配置。</p>
                        </div>
                    </div>
                    
                    <div class="guide-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>配置代理与抓包工具</h4>
                            <p>许多高级漏洞（如协议走私、JWT 伪造、多步骤认证绕过）无法仅通过浏览器界面完成。强烈建议配置 <strong>Burp Suite</strong> 代理，拦截并修改底层 HTTP/1.1、HTTP/2 请求头及载荷。</p>
                        </div>
                    </div>
                    
                    <div class="guide-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>白盒代码审计与调试</h4>
                            <p>Pikachu-Enhanced 所有漏洞都附带了存在缺陷的后端源码与教学模式。遇到瓶颈时，可以直接进入项目对应的 <code>vul/</code> 目录审查 PHP 逻辑，知其然更知其所以然。</p>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="disclaimer-box">
                    <h4><i class="fa fa-warning"></i> 法律免责与安全合规声明</h4>
                    <p style="margin-bottom: 0;">
                        本系统（Pikachu-Enhanced）是一款专为网络安全技术研究、防御技术测试、以及合法授权教学所设计的封闭式模拟演练平台。<strong>平台内置了大量高危且极易被利用的代码缺陷。</strong>严禁将其部署在任何对外开放的公网环境或生产网络中。
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
