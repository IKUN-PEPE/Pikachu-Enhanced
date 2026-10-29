<?php
/**
 * Pikachu-Enhanced Modern Cyber Range Portal & Architecture Guide
 * Refactored strictly per landing-page-design & redesign-existing-projects
 */
include_once 'inc/config.inc.php';
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[0] = 'active';
include 'header.php';

$html = '';
$link = @mysqli_connect(DBHOST, DBUSER, DBPW, DBNAME, DBPORT);
if(!$link){
    $html .= "<div class='alert alert-danger' style='margin-bottom: var(--spacing-300); border-radius: var(--radius-lg); background: var(--danger-light); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: var(--spacing-200); font-weight: 500;'><i class='ph-bold ph-warning' style='font-size:16px; vertical-align:middle; margin-right:6px;'></i> <strong>提示：</strong>数据库尚未初始化，<a href='install.php' style='text-decoration:underline; font-weight:700; color:inherit;'>请点击此处进行快速初始化与环境安装</a>。</div>";
}else{
    @mysqli_set_charset($link, 'utf8');
    @mysqli_close($link);
}
?>

<style>
/* ==========================================================================
   Modern Index Portal Styles
   Strict adherence to landing-page-design & redesign-existing-projects
   ========================================================================== */

.portal-container {
    max-width: 1360px;
    margin: 0 auto;
    padding: var(--spacing-200) var(--spacing-200) var(--spacing-900) var(--spacing-200);
}

/* 1. Flat Modern Hero Card (No Background Gradients) */
.portal-hero-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-600) var(--spacing-600);
    margin-bottom: var(--spacing-500);
    position: relative;
}

.portal-status-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-75);
    padding: 4px 10px;
    border-radius: var(--radius-full);
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    font-size: 12px;
    line-height: 16px;
    font-weight: 600;
    letter-spacing: 0.04em;
    color: var(--primary);
    text-transform: uppercase;
    margin-bottom: var(--spacing-200);
}

.portal-status-dot {
    width: 6px;
    height: 6px;
    border-radius: var(--radius-full);
    background: var(--success);
    display: inline-block;
}

.portal-headline {
    font-size: 36px;
    line-height: 40px;
    font-weight: 700;
    letter-spacing: -0.02em;
    max-width: 680px;
    margin: 0 0 var(--spacing-100) 0;
    text-wrap: balance;
}

.portal-subheadline {
    font-size: 16px;
    line-height: 24px;
    color: var(--text-secondary);
    max-width: 680px;
    margin: 0 0 var(--spacing-400) 0;
    text-wrap: pretty;
}

.portal-actions-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-100);
    flex-wrap: wrap;
}

.portal-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-75);
    padding: 8px 14px;
    border-radius: var(--radius-lg);
    background: var(--primary);
    color: #ffffff !important;
    font-size: 16px;
    line-height: 24px;
    font-weight: 600;
    text-decoration: none !important;
    transition: transform var(--duration-movement) var(--ease-base), background-color var(--duration-color) var(--ease-base);
    border: none;
    cursor: pointer;
}
.portal-btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
}
.portal-btn-primary:active {
    transform: scale(0.98);
}

.portal-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-75);
    padding: 8px 14px;
    border-radius: var(--radius-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    color: var(--text-primary) !important;
    font-size: 16px;
    line-height: 24px;
    font-weight: 600;
    text-decoration: none !important;
    transition: transform var(--duration-movement) var(--ease-base), background-color var(--duration-color) var(--ease-base), border-color var(--duration-color) var(--ease-base);
    cursor: pointer;
}
.portal-btn-secondary:hover {
    background: var(--bg-tertiary);
    border-color: var(--border-strong);
    transform: translateY(-1px);
}
.portal-btn-secondary:active {
    transform: scale(0.98);
}

/* 2. Architecture Domains Grid */
.section-block {
    margin-bottom: var(--spacing-600);
}

.section-label {
    font-size: 12px;
    line-height: 16px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: var(--spacing-75);
}

.section-title {
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-400) 0;
    text-wrap: balance;
}

.domain-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap: var(--spacing-200);
}

.domain-overview-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    padding: var(--spacing-300);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform var(--duration-movement) var(--ease-base), border-color var(--duration-color) var(--ease-base), box-shadow var(--duration-color) var(--ease-base);
}
.domain-overview-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.domain-card-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: var(--spacing-200);
}

.domain-card-title {
    font-size: 16px;
    line-height: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-75) 0;
}

.domain-card-desc {
    font-size: 13.5px;
    line-height: 20px;
    color: var(--text-secondary);
    margin: 0 0 var(--spacing-300) 0;
    text-wrap: pretty;
}

.domain-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: var(--spacing-100);
    border-top: 1px solid var(--border-subtle);
    font-size: 12px;
    color: var(--text-muted);
}

.domain-card-meta a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s ease;
}
.domain-card-meta a:hover {
    color: var(--primary-hover);
}

/* 3. Quickstart Guide Steps */
.quickstart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-200);
}

.quickstart-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    padding: var(--spacing-300);
}

.quickstart-step-num {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    background: var(--primary-light);
    border: 1px solid rgba(2, 132, 199, 0.2);
    padding: 3px 8px;
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-100);
}

.quickstart-card h4 {
    font-size: 15px;
    line-height: 22px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-50) 0;
}

.quickstart-card p {
    font-size: 13.5px;
    line-height: 20px;
    color: var(--text-secondary);
    margin: 0;
    text-wrap: pretty;
}

/* 4. Specifications Table */
.specs-table-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.specs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    color: var(--text-secondary);
}

.specs-table th {
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 700;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-subtle);
    text-align: left;
}

.specs-table td {
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-subtle);
}

.specs-table tr:last-child td {
    border-bottom: none;
}

.spec-chip {
    display: inline-block;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    font-family: 'Geist Mono', monospace;
    font-size: 12px;
    color: var(--text-primary);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ph-bold ph-house" style="font-size:14px; vertical-align:middle;"></i>
                    <a href="index.php">主页</a>
                </li>
                <li class="active">平台架构体系与演练指引</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="portal-container">
                <?php echo $html; ?>

                <!-- 1. Hero Overview -->
                <section class="portal-hero-card scroll-reveal">
                    <div class="portal-status-badge">
                        <span class="portal-status-dot"></span>
                        ENTERPRISE EDITION · v3.0 ROADMAP
                    </div>
                    
                    <h1 class="portal-headline hero-text-gradient">
                        全技术栈网络安全攻防实战演练中枢
                    </h1>
                    
                    <p class="portal-subheadline">
                        Pikachu-Enhanced 面向高阶安全研究员、渗透测试工程师与安全审计人员。深度集成 11 大核心技术域、47 个漏洞矩阵与 226 个物理靶标关卡，支持从经典 Web 漏洞到内核级二进制逆向的系统化实践。
                    </p>

                    <div class="portal-actions-row">
                        <a href="intro.php" class="portal-btn-primary">
                            <i class="ph-bold ph-map-trifold" style="font-size:18px;"></i> 查看全局战术图鉴
                        </a>
                        <a href="install.php" class="portal-btn-secondary">
                            <i class="ph-bold ph-arrows-clockwise" style="font-size:18px;"></i> 初始化环境数据库
                        </a>
                    </div>
                </section>

                <!-- 2. Core Four Pillars -->
                <section class="section-block scroll-reveal">
                    <div class="section-label">CURRICULUM ARCHITECTURE</div>
                    <h2 class="section-title">四大核心靶标体系，构建全维攻防矩阵</h2>

                    <div class="domain-cards-grid">
                        <div class="domain-overview-card">
                            <div>
                                <div class="domain-card-icon-wrap"><i class="ph-bold ph-fire"></i></div>
                                <h3 class="domain-card-title">经典 Web 攻防演练</h3>
                                <p class="domain-card-desc">涵盖 SQL 注入、跨站脚本 XSS、远程命令执行 RCE、高阶文件上传绕过、SSRF 探测、XXE 实体注入与反序列化利用等 17 个细分靶标矩阵。</p>
                            </div>
                            <div class="domain-card-meta">
                                <span>17 个核心矩阵 · 70+ 关卡</span>
                                <a href="intro.php#domain-classic">直达矩阵 <i class="ph-bold ph-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="domain-overview-card">
                            <div>
                                <div class="domain-card-icon-wrap"><i class="ph-bold ph-key"></i></div>
                                <h3 class="domain-card-title">现代身份认证与协议</h3>
                                <p class="domain-card-desc">探索 JWT 签名算法混淆、OAuth2 授权劫持、SAML 伪造、HTTP/2 请求走私、GraphQL 批量内省与 WebSocket 跨站劫持等 23 个现代 API 威胁。</p>
                            </div>
                            <div class="domain-card-meta">
                                <span>23 个高阶矩阵 · 50+ 关卡</span>
                                <a href="intro.php#domain-auth">直达矩阵 <i class="ph-bold ph-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="domain-overview-card">
                            <div>
                                <div class="domain-card-icon-wrap"><i class="ph-bold ph-cloud"></i></div>
                                <h3 class="domain-card-title">云原生与前沿安全</h3>
                                <p class="domain-card-desc">包含 Kubernetes API 越权、Docker Socket 逃逸、中间件未授权访问、AI 大模型 Prompt 注入以及蓝队主动防御欺骗蜜罐等新一代攻防技术。</p>
                            </div>
                            <div class="domain-card-meta">
                                <span>15 个前沿矩阵 · 40+ 关卡</span>
                                <a href="intro.php#domain-cloud">直达矩阵 <i class="ph-bold ph-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="domain-overview-card">
                            <div>
                                <div class="domain-card-icon-wrap"><i class="ph-bold ph-crosshair"></i></div>
                                <h3 class="domain-card-title">OffSec 认证通关实战</h3>
                                <p class="domain-card-desc">严格对标 OSEP 红队免杀评估、OSWE 白盒代码审计与 OSED 漏洞利用逆向三大 Offensive Security 国际顶尖认证，配备 42 个加密动态 Flag 校验关卡。</p>
                            </div>
                            <div class="domain-card-meta">
                                <span>3 大认证体系 · 42 认证 Flag</span>
                                <a href="intro.php#domain-osep">直达矩阵 <i class="ph-bold ph-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Quickstart Workflow -->
                <section class="section-block scroll-reveal">
                    <div class="section-label">QUICKSTART GUIDE</div>
                    <h2 class="section-title">快速上手指南，快速开启第一门演练</h2>

                    <div class="quickstart-grid">
                        <div class="quickstart-card">
                            <div class="quickstart-step-num">STEP 01</div>
                            <h4>环境状态检查</h4>
                            <p>确认平台各组件运行正常。如初次部署或测试后数据被破坏，可随时访问 install.php 页面一键完成表结构与测试数据的重置。</p>
                        </div>
                        <div class="quickstart-card">
                            <div class="quickstart-step-num">STEP 02</div>
                            <h4>通过侧边栏或图鉴导航</h4>
                            <p>利用左侧树状折叠侧边栏检索目标关卡，或在“全局漏洞图鉴”页面通过关键词与安全域标签进行快速定位直达。</p>
                        </div>
                        <div class="quickstart-card">
                            <div class="quickstart-step-num">STEP 03</div>
                            <h4>阅读原理与实操验证</h4>
                            <p>关卡页面内置详细的数据流走向与攻防解密提示。建议先独立分析漏洞逻辑，再使用抓包工具构造 Payload 验证修复。</p>
                        </div>
                    </div>
                </section>

                <!-- 4. Environment Specifications -->
                <section class="section-block scroll-reveal">
                    <div class="section-label">RUNTIME SPECIFICATIONS</div>
                    <h2 class="section-title">运行环境与核心架构标准</h2>

                    <div class="specs-table-card">
                        <table class="specs-table">
                            <thead>
                                <tr>
                                    <th>组件项目</th>
                                    <th>技术标准 / 运行时版本</th>
                                    <th>设计规范与说明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>服务端核心语言</strong></td>
                                    <td><span class="spec-chip">PHP 8.2 (Apache)</span></td>
                                    <td>符合 PSR-4 与 PSR-12 标准，配备自适应 SVG 矢量防混淆验证码引擎</td>
                                </tr>
                                <tr>
                                    <td><strong>数据库引擎</strong></td>
                                    <td><span class="spec-chip">MariaDB 10.6 / MySQL 8.0</span></td>
                                    <td>采用 UTF-8 编码，包含预置企业业务数据表及注入演练专有上下文</td>
                                </tr>
                                <tr>
                                    <td><strong>前端交互设计体系</strong></td>
                                    <td><span class="spec-chip">Geist Typography · Phosphor Icons</span></td>
                                    <td>严格遵循 landing-page-design 规范，平铺式高阶表面与无死角状态响应</td>
                                </tr>
                                <tr>
                                    <td><strong>认证 Flag 引擎</strong></td>
                                    <td><span class="spec-chip">Pikachu\FlagEngine</span></td>
                                    <td>会话签名级动态 Flag 生成，涵盖 OSEP/OSWE/OSED 42 个认证关卡</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div> <!-- /.portal-container -->
        </div><!-- /.page-content -->
    </div>
</div><!-- /.main-content -->

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Scroll reveal observers
    if ('IntersectionObserver' in window) {
        const revealElements = document.querySelectorAll('.scroll-reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        document.querySelectorAll('.scroll-reveal').forEach(el => el.classList.add('revealed'));
    }
});
</script>

<?php
include 'footer.php';
?>
