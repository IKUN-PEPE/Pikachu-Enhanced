<?php
/**
 * Pikachu-Enhanced v3.0 Enterprise Cyber Range
 * Global Threat Matrix & Interactive Vulnerability Roadmap
 */

include_once 'inc/config.inc.php';

$ACTIVE = array_fill(0, 400, '');
$ACTIVE[330] = 'active';

include 'header.php';

// Calculate session solved count
$osep_solved = isset($_SESSION['osep_flags']) && is_array($_SESSION['osep_flags']) ? count($_SESSION['osep_flags']) : 0;
$oswe_solved = isset($_SESSION['oswe_flags']) && is_array($_SESSION['oswe_flags']) ? count($_SESSION['oswe_flags']) : 0;
$osed_solved = isset($_SESSION['osed_flags']) && is_array($_SESSION['osed_flags']) ? count($_SESSION['osed_flags']) : 0;
$total_flags_solved = $osep_solved + $oswe_solved + $osed_solved;
?>

<style>
/* ==========================================================================
   Cyber Range Command Center & Threat Matrix Styles (v3.0 High-End)
   ========================================================================== */
:root {
    --cyber-glow-blue: rgba(56, 189, 248, 0.35);
    --cyber-glow-purple: rgba(168, 85, 247, 0.35);
    --cyber-glow-green: rgba(34, 197, 94, 0.35);
    --cyber-glow-amber: rgba(245, 158, 11, 0.35);
}

.threat-matrix-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 6px 0 50px 0;
}

/* Futuristic Command Center Hero */
.command-hero {
    position: relative;
    border-radius: 16px;
    padding: 36px 40px;
    margin-bottom: 26px;
    color: #ffffff;
    overflow: hidden;
    background: linear-gradient(135deg, #090d16 0%, #0f172a 45%, #1e1b4b 100%);
    border: 1px solid rgba(56, 189, 248, 0.25);
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.command-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: 
        linear-gradient(rgba(56, 189, 248, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56, 189, 248, 0.05) 1px, transparent 1px);
    background-size: 32px 32px;
    pointer-events: none;
    opacity: 0.8;
}

.command-hero::after {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(99, 102, 241, 0.15) 50%, transparent 70%);
    border-radius: 50%;
    filter: blur(40px);
    pointer-events: none;
}

.hero-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    border-radius: 9999px;
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.4);
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: #38bdf8;
    text-transform: uppercase;
    margin-bottom: 16px;
    backdrop-filter: blur(8px);
}

.radar-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 10px #22c55e;
    animation: radarPulse 2s infinite ease-in-out;
}

@keyframes radarPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.command-title {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 10px 0;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 12px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.command-title span.version-badge {
    font-size: 13px;
    font-weight: 700;
    padding: 3px 10px;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    border-radius: 6px;
    color: #fff;
    vertical-align: middle;
    letter-spacing: 0.02em;
}

.command-subtitle {
    font-size: 14.5px;
    line-height: 1.7;
    color: #94a3b8;
    max-width: 960px;
    margin-bottom: 24px;
}

/* Tactical Telemetry Metrics Row */
.telemetry-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-top: 18px;
}

.telemetry-card {
    background: rgba(15, 23, 42, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 14px 18px;
    backdrop-filter: blur(10px);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 14px;
}

.telemetry-card:hover {
    border-color: rgba(56, 189, 248, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.telemetry-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.icon-blue { background: rgba(37, 99, 235, 0.18); color: #38bdf8; border: 1px solid rgba(37, 99, 235, 0.3); }
.icon-emerald { background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.icon-purple { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
.icon-amber { background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }

.telemetry-num {
    font-size: 22px;
    font-weight: 800;
    color: #f8fafc;
    line-height: 1.2;
}

.telemetry-label {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
}

/* Interactive Control Console */
.control-console {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 26px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.search-input-wrap {
    position: relative;
    width: 100%;
}

.search-input-wrap i.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 15px;
}

.search-input-wrap input {
    width: 100%;
    height: 46px;
    padding: 0 46px 0 44px;
    border-radius: 10px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-app);
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.search-input-wrap input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    background: var(--bg-card);
}

.filter-categories-scroll {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.domain-pill {
    padding: 7px 14px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-app);
    color: var(--text-secondary);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    user-select: none;
}

.domain-pill:hover {
    color: var(--primary);
    border-color: var(--primary);
    background: var(--primary-light);
    transform: translateY(-1px);
}

.domain-pill.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
}

.domain-pill .pill-badge {
    font-size: 10.5px;
    padding: 1px 6px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.08);
}
.domain-pill.active .pill-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}

/* Category Sections */
.domain-section {
    margin-bottom: 34px;
}

.domain-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border-subtle);
}

.domain-section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.domain-section-badge {
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 6px;
    background: var(--bg-sidebar-hover);
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
}

/* Matrix Card Grid */
.matrix-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 16px;
}

.matrix-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.matrix-card:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
    box-shadow: var(--shadow-md), 0 0 16px rgba(37, 99, 235, 0.12);
}

.card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.card-cat-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.cat-badge-web { background: rgba(37, 99, 235, 0.1); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2); }
.cat-badge-cloud { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.cat-badge-auth { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }
.cat-badge-proto { background: rgba(6, 182, 212, 0.1); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.2); }
.cat-badge-ai { background: rgba(236, 72, 153, 0.1); color: #ec4899; border: 1px solid rgba(236, 72, 153, 0.2); }
.cat-badge-defense { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
.cat-badge-ad { background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.2); }
.cat-badge-osep { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2); }
.cat-badge-oswe { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.2); }
.cat-badge-osed { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }

.card-rating-pill {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
}
.card-rating-pill span.pts {
    color: #eab308;
    margin-left: 4px;
}

.card-vuln-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 8px 0;
    line-height: 1.4;
}

.card-vuln-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0 0 14px 0;
}

.card-tags-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
}

.tech-tag {
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--bg-sidebar-hover);
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
}

.card-footer-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 10px;
    border-top: 1px solid var(--border-subtle);
}

.launch-target-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 700;
    color: #ffffff !important;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    text-decoration: none !important;
    transition: all 0.18s ease;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
}

.launch-target-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
}

.launch-target-btn i {
    transition: transform 0.2s ease;
}
.launch-target-btn:hover i {
    transform: translateX(3px);
}

/* Empty State */
.empty-search-state {
    display: none;
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px dashed var(--border-subtle);
}
.empty-search-state i {
    font-size: 48px;
    color: var(--text-muted);
    margin-bottom: 16px;
}
.empty-search-state h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}
.empty-search-state p {
    color: var(--text-muted);
    font-size: 13px;
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
                <li class="active">📊 全局漏洞图鉴与网络空间靶标态势中枢 (v3.0)</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="threat-matrix-wrapper">

                <!-- 1. Futuristic Command Hero -->
                <div class="command-hero">
                    <div class="hero-status-pill">
                        <span class="radar-dot"></span>
                        CYBER MATRIX ONLINE · THREAT INTEL COMMAND
                    </div>
                    <h1 class="command-title">
                        ⚡ 全局漏洞图鉴与战术攻防演练路线图
                        <span class="version-badge">v3.0 ENTERPRISE</span>
                    </h1>
                    <div class="command-subtitle">
                        系统化全技术栈网络安全攻防图谱！覆盖 11 大顶层核心安全域、47 个战术漏洞矩阵与 226+ 个全真沙箱靶标。对标 OWASP Top 10、MITRE ATT&CK 框架与 OffSec (OSEP / OSWE / OSED) 认证考核标准，支持一站式检索与快速直达。
                    </div>

                    <!-- Telemetry Metrics Row -->
                    <div class="telemetry-grid">
                        <div class="telemetry-card">
                            <div class="telemetry-icon icon-blue"><i class="fa fa-crosshairs"></i></div>
                            <div>
                                <div class="telemetry-num">226+</div>
                                <div class="telemetry-label">实战物理靶标关卡</div>
                            </div>
                        </div>
                        <div class="telemetry-card">
                            <div class="telemetry-icon icon-emerald"><i class="fa fa-sitemap"></i></div>
                            <div>
                                <div class="telemetry-num">11 大</div>
                                <div class="telemetry-label">核心顶层安全领域</div>
                            </div>
                        </div>
                        <div class="telemetry-card">
                            <div class="telemetry-icon icon-purple"><i class="fa fa-cubes"></i></div>
                            <div>
                                <div class="telemetry-num">47 个</div>
                                <div class="telemetry-label">细分漏洞战术矩阵</div>
                            </div>
                        </div>
                        <div class="telemetry-card">
                            <div class="telemetry-icon icon-amber"><i class="fa fa-flag"></i></div>
                            <div>
                                <div class="telemetry-num"><?php echo $total_flags_solved; ?> / 42</div>
                                <div class="telemetry-label">认证 Flag 攻破进度</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Interactive Control Console -->
                <div class="control-console">
                    <div class="search-input-wrap">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="matrixSearchInput" placeholder="🔍 检索靶标名称、CVE 编号、技术协议、攻防手法（如：JWT, RCE, 走私, OSEP, 内存免杀, 原型链...）" />
                    </div>

                    <div class="filter-categories-scroll" id="domainFilterTabs">
                        <button class="domain-pill active" data-target="all"><i class="fa fa-th-large"></i> 全局展现 <span class="pill-badge">226+</span></button>
                        <button class="domain-pill" data-target="classic"><i class="fa fa-fire"></i> 🏛️ 经典 Web <span class="pill-badge">17</span></button>
                        <button class="domain-pill" data-target="cloud"><i class="fa fa-cloud"></i> ☁️ 云原生微服务 <span class="pill-badge">9</span></button>
                        <button class="domain-pill" data-target="auth"><i class="fa fa-key"></i> 🔑 现代身份认证 <span class="pill-badge">11</span></button>
                        <button class="domain-pill" data-target="business"><i class="fa fa-bolt"></i> ⚡ 业务逻辑并发 <span class="pill-badge">5</span></button>
                        <button class="domain-pill" data-target="proto"><i class="fa fa-globe"></i> 🌐 新型协议接口 <span class="pill-badge">12</span></button>
                        <button class="domain-pill" data-target="ai"><i class="fa fa-robot"></i> 🤖 AI 大模型安全 <span class="pill-badge">4</span></button>
                        <button class="domain-pill" data-target="defense"><i class="fa fa-shield"></i> 🛡️ 蓝队主动防御 <span class="pill-badge">6</span></button>
                        <button class="domain-pill" data-target="ad"><i class="fa fa-network-wired"></i> 🌐 AD 域控渗透 <span class="pill-badge">6</span></button>
                        <button class="domain-pill" data-target="osep"><i class="fa fa-bullseye"></i> 🎯 OSEP 红队评估 <span class="pill-badge">18</span></button>
                        <button class="domain-pill" data-target="oswe"><i class="fa fa-search-plus"></i> 🔍 OSWE 白盒审计 <span class="pill-badge">14</span></button>
                        <button class="domain-pill" data-target="osed"><i class="fa fa-microchip"></i> 🔬 OSED 漏洞逆向 <span class="pill-badge">10</span></button>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyMatrixState" class="empty-search-state">
                    <i class="fa fa-search-minus"></i>
                    <h3>未检索到匹配的靶标关卡</h3>
                    <p>请尝试精简或更换关键词，或点击“全局展现”恢复所有安全域靶标。</p>
                </div>

                <!-- ================= 1. 经典 Web 攻防演练 ================= -->
                <div class="domain-section" data-domain="classic">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🏛️ 经典 Web 攻防演练 (Classic Web Vulnerabilities)
                        </h2>
                        <span class="domain-section-badge">17 大核心靶标系列</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">SQL 注入</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">SQL-Inject 综合演练 (10关)</h3>
                                <p class="card-vuln-desc">数字型、字符型、搜索型、XX型、报错注入、布尔时间双盲注、宽字节绕过与堆叠注入数据提取。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">UNION SELECT</span>
                                    <span class="tech-tag">时间盲注</span>
                                    <span class="tech-tag">宽字节</span>
                                    <span class="tech-tag">LOAD_FILE</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/sqli/sqli_str.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">跨站脚本</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Cross-Site Scripting XSS (10关)</h3>
                                <p class="card-vuln-desc">反射型 GET/POST、存储型留言板、DOM 型、盲打管理员后台、实体转义绕过与不同 JS 上下文逃逸。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Reflected</span>
                                    <span class="tech-tag">Stored</span>
                                    <span class="tech-tag">DOM-X</span>
                                    <span class="tech-tag">Cookie Stealing</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/xss/xss_reflected_get.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">命令注入</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">RCE 远程命令与代码执行</h3>
                                <p class="card-vuln-desc">过滤不严的 ping / eval 函数调用，利用分号管道符拼接待执行 OS 命令、WAF 绕过与无回显外带。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">exec()</span>
                                    <span class="tech-tag">eval()</span>
                                    <span class="tech-tag">WAF Bypass</span>
                                    <span class="tech-tag">OOB 反弹</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/rce/rce_ping.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">高阶上传</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Zip Slip 压缩包解压目录穿越</h3>
                                <p class="card-vuln-desc">突破传统后缀过滤，构造带路径跳转符号的恶性压缩包，解压时跨目录覆盖 Web 根目录实现 RCE。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Zip Slip</span>
                                    <span class="tech-tag">Path Traversal</span>
                                    <span class="tech-tag">WebShell 覆写</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/unsafeupload/zip_slip.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">服务端请求</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">SSRF Gopher 打击内网 Redis</h3>
                                <p class="card-vuln-desc">利用 cURL 支持 Gopher 协议特性伪造 Redis 通信数据包，直接写入未授权定时任务或 WebShell 控机。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">SSRF</span>
                                    <span class="tech-tag">Gopher 协议</span>
                                    <span class="tech-tag">Redis 未授权</span>
                                    <span class="tech-tag">Crontab RCE</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ssrf/ssrf_gopher_redis.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">XML 注入</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">XXE 外部实体任意文件读取</h3>
                                <p class="card-vuln-desc">XML 解析器未禁用外部实体，构造 SYSTEM 伪协议读取系统配置文件并结合 DTD 实现带外盲注。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">XXE</span>
                                    <span class="tech-tag">SYSTEM Entity</span>
                                    <span class="tech-tag">Blind XXE</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/xxe/xxe_1.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">反序列化</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Phar 伪协议隐式触发反序列化</h3>
                                <p class="card-vuln-desc">目标未显式调用 unserialize()，利用 file_exists() 等文件函数传入 phar:// 伪协议自动触发魔术方法。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">phar://</span>
                                    <span class="tech-tag">__destruct</span>
                                    <span class="tech-tag">POP Chain</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/phar/phar.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">文件包含</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">File Inclusion 本地/远程包含</h3>
                                <p class="card-vuln-desc">动态文件包含参数过滤不严，利用路径穿越包含本地敏感文件，或通过 php://filter 读取源代码 Base64。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">LFI / RFI</span>
                                    <span class="tech-tag">php://filter</span>
                                    <span class="tech-tag">data:// 协议</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/fileinclude/fi_local.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">跨站伪造</span>
                                    <span class="card-rating-pill">★★☆☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">CSRF 跨站请求伪造</h3>
                                <p class="card-vuln-desc">诱导已登录受害者访问恶意页面，利用浏览器自动携带凭证机制，静默完成特权指令篡改与转账。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">GET / POST CSRF</span>
                                    <span class="tech-tag">Anti-CSRF Token</span>
                                    <span class="tech-tag">SameSite</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/csrf/csrfget/csrf_get_edit.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">访问控制</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Over Permission 水平/垂直越权</h3>
                                <p class="card-vuln-desc">参数未绑定 Session 会话主体，攻击者篡改用户 ID 实现越权横向查看修改他人档案及管理员纵向越权。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">水平越权</span>
                                    <span class="tech-tag">垂直提权</span>
                                    <span class="tech-tag">BOLA 破坏</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/overpermission/op1_mem.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">文件上传</span>
                                    <span class="card-rating-pill">★★☆☆☆ <span class="pts">100 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">传统文件上传全手法绕过</h3>
                                <p class="card-vuln-desc">绕过前端 JS 扩展名验证、篡改 MIME Content-Type 头、构造图片马突破 getimagesize() 校验。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">MIME 伪造</span>
                                    <span class="tech-tag">图片马</span>
                                    <span class="tech-tag">点空格绕过</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/unsafeupload/clientcheck.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="classic">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-web">反序列化</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Java 原生反序列化 (readObject)</h3>
                                <p class="card-vuln-desc">深入分析 ObjectInputStream.readObject 序列化流魔数 rO0AB，实操 URLDNS 与自定义 Gadget 链。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">0xACED0005</span>
                                    <span class="tech-tag">ysoserial</span>
                                    <span class="tech-tag">URLDNS</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/java_unserialize/java_unserialize.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 2. 云原生与云基础设施安全 ================= -->
                <div class="domain-section" data-domain="cloud">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            ☁️ 云原生与云基础设施安全 (Cloud Native & Infrastructure)
                        </h2>
                        <span class="domain-section-badge">9 大前沿云靶标</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">容器逃逸</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Docker 特权模式逃逸 (--privileged)</h3>
                                <p class="card-vuln-desc">容器挂载裸磁盘设备 /dev/vda1，攻击者直接 mount 重挂载宿主机根磁盘并 chroot 切入 Host OS。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">--privileged</span>
                                    <span class="tech-tag">mount /dev</span>
                                    <span class="tech-tag">chroot</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/dockerlab/docker_privileged_escape.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">Socket 逃逸</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Docker Socket 挂载逃逸 (docker.sock)</h3>
                                <p class="card-vuln-desc">容器挂载 /var/run/docker.sock，利用 REST API 创建带宿主机根目录挂载的新特权容器夺取宿主机权限。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">docker.sock</span>
                                    <span class="tech-tag">Docker Engine API</span>
                                    <span class="tech-tag">escape_pod</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/dockerlab/docker_sock_escape.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">内核逃逸</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Capabilities 特权逃逸 (CAP_SYS_ADMIN)</h3>
                                <p class="card-vuln-desc">利用特权能力挂载 cgroups 控制器注入 release_agent 回调脚本，使宿主机内核以 Host Root 执行提权命令。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">CAP_SYS_ADMIN</span>
                                    <span class="tech-tag">cgroups v1</span>
                                    <span class="tech-tag">release_agent</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/dockerlab/docker_caps_escape.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">K8s 集群</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Kubernetes ServiceAccount 越权逃逸</h3>
                                <p class="card-vuln-desc">提取 Pod 内挂载的 ServiceAccount 凭据向 K8s API Server 越权请求特权 DaemonSet 掌控 Master 节点。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">K8s API</span>
                                    <span class="tech-tag">ServiceAccount</span>
                                    <span class="tech-tag">RBAC 提权</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/dockerlab/k8s_token_escape.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">对象存储</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">AWS S3 / 阿里云 OSS 存储桶未授权盲取</h3>
                                <p class="card-vuln-desc">云存储 Bucket ACL 错误配置为公共读，匿名枚举遍历敏感业务数据包并接管悬挂域名。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">S3 Bucket</span>
                                    <span class="tech-tag">OSS ACL</span>
                                    <span class="tech-tag">Subdomain Takeover</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/cloud_storage/s3_unauth.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="cloud">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-cloud">无服务器</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Serverless 云函数环境变量与凭证泄露</h3>
                                <p class="card-vuln-desc">从微服务冷启动临时容器中倾倒 process.env，获取云厂商主账号 AccessKey 及持久化 /tmp 注入点。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Lambda / 云函数</span>
                                    <span class="tech-tag">AccessKey 窃取</span>
                                    <span class="tech-tag">冷启动投毒</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/serverless/lambda_env_leak.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 3. 现代身份认证与访问控制 ================= -->
                <div class="domain-section" data-domain="auth">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🔑 现代身份认证与访问控制 (Modern Auth & Identity Security)
                        </h2>
                        <span class="domain-section-badge">11 大攻防场景</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">JWT 攻击</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">JWT 弱密钥爆破与 None 算法绕过</h3>
                                <p class="card-vuln-desc">离线字典碰撞 HMAC 弱密钥、篡改 Header 为 alg: none 剥离签名，直接伪造管理员身份令牌。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">JWT</span>
                                    <span class="tech-tag">alg: none</span>
                                    <span class="tech-tag">Secret Crack</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/jwt/jwt_weak_secret.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">算法混淆</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">JWT RS256 ➔ HS256 公钥算法混淆</h3>
                                <p class="card-vuln-desc">服务端验证未锁定非对称密钥体系，将公钥证书 PEM 当作对称共享密钥签名生成特权 JWT 令牌。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Key Confusion</span>
                                    <span class="tech-tag">RS256 vs HS256</span>
                                    <span class="tech-tag">PEM Public Key</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/jwt/jwt_key_confusion.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">单点登录</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">SAML 2.0 签名包装攻击 (XSW)</h3>
                                <p class="card-vuln-desc">利用 XML 签名验证与业务提取逻辑差异，向断言中插入恶意包装结构，接管单点登录企业用户。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">SAML 2.0</span>
                                    <span class="tech-tag">XML Signature Wrapping</span>
                                    <span class="tech-tag">SSO 劫持</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/sso_saml/saml_xsw.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">OAuth 劫持</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">OAuth 2.0 State 伪造与授权码劫持</h3>
                                <p class="card-vuln-desc">未校验 state 参数导致 CSRF 登录绑定攻击，或利用 redirect_uri 宽泛匹配截获用户授权 Access Token。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">OAuth 2.0</span>
                                    <span class="tech-tag">State Bypass</span>
                                    <span class="tech-tag">Token Theft</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oauth/oauth_state.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">双因子绕过</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">MFA 多因素认证竞争条件绕过</h3>
                                <p class="card-vuln-desc">多步验证流程缺乏事务状态锁，并发请求在二次验证码录入前提前结束鉴权流程完成登录。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">MFA Bypass</span>
                                    <span class="tech-tag">Race Condition</span>
                                    <span class="tech-tag">2FA 缺陷</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/mfa_bypass/mfa_race.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="auth">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-auth">API 安全</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Mass Assignment 批量自动赋值提权</h3>
                                <p class="card-vuln-desc">REST API 实体反序列化未限制模型可写属性，在 JSON 请求中注入 role: admin 获得超级管理权限。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Mass Assignment</span>
                                    <span class="tech-tag">REST API</span>
                                    <span class="tech-tag">角色提权</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/api_security/mass_assignment.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 4. 现代网络协议与新型数据接口 ================= -->
                <div class="domain-section" data-domain="proto">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🌐 现代网络协议与新型数据接口 (Modern Protocols & API Security)
                        </h2>
                        <span class="domain-section-badge">12 大协议与接口靶标</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">请求走私</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">HTTP 请求走私 (CL-TE / TE-CL 冲突)</h3>
                                <p class="card-vuln-desc">前端代理与后端解析 Content-Length 与 Transfer-Encoding 不一致，走私前缀劫持后续用户请求。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">HTTP Smuggling</span>
                                    <span class="tech-tag">CL-TE / TE-CL</span>
                                    <span class="tech-tag">请求劫持</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/http_smuggling/cl_te.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">RPC 协议</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">微服务 gRPC 反射与未授权调用</h3>
                                <p class="card-vuln-desc">Protobuf/HTTP2 传输链路未关闭 Server Reflection，攻击者枚举全量 proto 元数据并绕过鉴权伪造调用。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">gRPC</span>
                                    <span class="tech-tag">Protobuf</span>
                                    <span class="tech-tag">Server Reflection</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/grpc/grpc_reflection.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">回调安全</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Webhook 异步回调 SSRF 与内网穿透</h3>
                                <p class="card-vuln-desc">事件订阅系统未校验回调 Target URL，借助 DNS 重绑定与 302 重定向穿透局域网探测敏感端口。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Webhook</span>
                                    <span class="tech-tag">SSRF</span>
                                    <span class="tech-tag">DNS Rebinding</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/webhook/webhook_ssrf.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">长连接</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">WebSocket 跨站劫持 (CSWSH)</h3>
                                <p class="card-vuln-desc">WebSocket 握手阶段未校验 Origin 请求头，跨站构造恶意长连接握手窃取即时聊天凭证与私密信道。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">WebSocket</span>
                                    <span class="tech-tag">CSWSH</span>
                                    <span class="tech-tag">双向会话劫持</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/websocket/cswsh.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">缓存漏洞</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Web Cache Deception 缓存欺骗</h3>
                                <p class="card-vuln-desc">利用静态资源路径解析差异（如 /account.php/test.css）诱使 CDN 缓存受害者隐私页面实现脱库。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Cache Deception</span>
                                    <span class="tech-tag">CDN 缓存投毒</span>
                                    <span class="tech-tag">Path Parsing</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/web_cache/cache_deception.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="proto">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-proto">前端安全</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">JavaScript 原型链污染 (Prototype Pollution)</h3>
                                <p class="card-vuln-desc">对象深层归并函数未过滤 __proto__ 属性，篡改 Object.prototype 全局影响原型执行链引发 XSS 或 RCE。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">__proto__</span>
                                    <span class="tech-tag">Object.prototype</span>
                                    <span class="tech-tag">Node / EJS RCE</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/frontend/prototype_pollution.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 5. AI 与大模型应用安全 ================= -->
                <div class="domain-section" data-domain="ai">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🤖 AI 与大模型应用安全 (AI & LLM Application Security)
                        </h2>
                        <span class="domain-section-badge">4 大核心场景</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="ai">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ai">提示词越狱</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Prompt 注入与防御绕过</h3>
                                <p class="card-vuln-desc">利用特殊分隔符覆盖预设 System Prompt，诱导 AI 忽略道德与业务安全限制输出受限系统数据。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">OWASP LLM-01</span>
                                    <span class="tech-tag">Jailbreak</span>
                                    <span class="tech-tag">Context Override</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ai_security/prompt_injection.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ai">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ai">系统窃取</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">敏感规则与系统提示词逆向提取</h3>
                                <p class="card-vuln-desc">社工诱导大模型逐步打印、倒序输出或 Base64 编码，泄露底层不可见的商业秘密与 System Prompt 规则。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">OWASP LLM-06</span>
                                    <span class="tech-tag">Prompt Leakage</span>
                                    <span class="tech-tag">逆向指令</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ai_security/llm_data_leakage.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ai">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ai">智能体 RCE</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">插件工具命令注入 (AI Agent RCE)</h3>
                                <p class="card-vuln-desc">滥用 AI 调用的命令行与自动化插件，注入管道符拼接底层命令，实现对 AI 宿主机的远程系统接管。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Agent Tool RCE</span>
                                    <span class="tech-tag">Function Call</span>
                                    <span class="tech-tag">Command Injection</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ai_security/llm_plugin_rce.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ai">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ai">不安全输出</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">不安全输出渲染 (LLM XSS & SSRF)</h3>
                                <p class="card-vuln-desc">构造特殊输入促使 AI 回显恶意外链或脚本标签，在前端渲染上下文引发 DOM 劫持与盲打 SSRF。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">OWASP LLM-02</span>
                                    <span class="tech-tag">Markdown SSRF</span>
                                    <span class="tech-tag">DOM 渲染劫持</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ai_security/llm_xss.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 6. 蓝队防守与主动实战防御 ================= -->
                <div class="domain-section" data-domain="defense">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🛡️ 蓝队防守与实战防御 (Blue Team Active Defense & Forensics)
                        </h2>
                        <span class="domain-section-badge">6 大实战防守场景</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="defense">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-defense">WAF 对抗</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">200 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">WAF 动态特征规则拦截与绕过研判</h3>
                                <p class="card-vuln-desc">分析正则规则引擎与状态机匹配原理，演练字符集编码、分块传输与参数污染对防御边界的穿透。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">WAF Rules</span>
                                    <span class="tech-tag">Chunked Smuggling</span>
                                    <span class="tech-tag">防御分析</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/defense/waf_bypass_analysis.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="defense">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-defense">RASP 探针</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">运行时应用自我保护 (RASP) 原理</h3>
                                <p class="card-vuln-desc">通过 JVM 字节码插桩与底层 Hook 技术，在执行堆栈最深层阻断非法命令与代码执行的实战机理。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">RASP</span>
                                    <span class="tech-tag">Java Agent Hook</span>
                                    <span class="tech-tag">堆栈阻断</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/defense/rasp_concept.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="defense">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-defense">动态蜜罐</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">高仿真动态蜜罐指纹识别与诱捕</h3>
                                <p class="card-vuln-desc">分析诱饵服务特征（Fake SSH / MySQL 读文件），演练攻击者探测蜜罐与防守方反向溯源的攻防博弈。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Honeypot</span>
                                    <span class="tech-tag">指纹识别</span>
                                    <span class="tech-tag">溯源取证</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/defense/honeypot_detection.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="defense">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-defense">应急响应</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">WebShell 内存查杀与网络流量取证</h3>
                                <p class="card-vuln-desc">演练冰蝎 / 哥斯拉加密流量特征提取，使用内存排查定位无文件 WebShell 并还原完整攻击链路。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Behinder / Godzilla</span>
                                    <span class="tech-tag">流量解密</span>
                                    <span class="tech-tag">内存马排查</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/defense/webshell_analysis.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 7. 内网与 Active Directory 域安全 ================= -->
                <div class="domain-section" data-domain="ad">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🌐 内网与 Active Directory 域安全 (AD & Intranet Security)
                        </h2>
                        <span class="domain-section-badge">6 大域渗透攻防核心</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="ad">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ad">AD 全景体系</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">AD 域 6 大知识体系全景图鉴</h3>
                                <p class="card-vuln-desc">全面掌握域森林、信任关系、Kerberos 认证流、Group Policy、证书服务 ADCS 与 ACL 权限树。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">AD Architecture</span>
                                    <span class="tech-tag">Kerberos 原理</span>
                                    <span class="tech-tag">GPO 策略</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ad_security/ad_knowledge.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ad">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ad">图论分析</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">内网侦察与 BloodHound 拓扑图论</h3>
                                <p class="card-vuln-desc">无侵入枚举域用户、域组与对象 ACL，导入 BloodHound 利用图论算法计算出最短的域管提权击杀链。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">BloodHound</span>
                                    <span class="tech-tag">SharpHound</span>
                                    <span class="tech-tag">最短提权路径</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ad_security/ad_ctf_recon.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ad">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ad">票据攻击</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Kerberos 域认证与票据攻击</h3>
                                <p class="card-vuln-desc">演练 Kerberoasting 离线爆破 SPN 服务口令、AS-REP Roasting 提取以及伪造 Golden Ticket 黄金票据。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Kerberoasting</span>
                                    <span class="tech-tag">Golden Ticket</span>
                                    <span class="tech-tag">krbtgt 散列</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ad_security/ad_ctf_kerberoast.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="ad">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-ad">委派利用</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">Kerberos 委派攻击与 AD CS 证书中继</h3>
                                <p class="card-vuln-desc">实操 S4U2proxy 约束委派、基于资源的 RBCD 委派、ADCS ESC8 证书 NTLM HTTP 中继与权限维持。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">S4U2Self / S4U2Proxy</span>
                                    <span class="tech-tag">RBCD 委派</span>
                                    <span class="tech-tag">ADCS ESC8</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/ad_security/ad_ctf_delegation.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 8. OSEP 内网穿透与红队评估 ================= -->
                <div class="domain-section" data-domain="osep">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🎯 OSEP 内网穿透与红队评估 (OffSec OSEP Certified Path - 18 关)
                        </h2>
                        <span class="domain-section-badge">18 关高阶免杀与穿透</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">OSEP 专区</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">OFFSEC</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 OSEP 认证实战指挥大厅 (Hub)</h3>
                                <p class="card-vuln-desc">OffSec 认证考核完整进阶谱系：涵盖 API 内存执行、宏武器化、进程镂空、AMSI 绕过与森林委派。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">18 关进阶</span>
                                    <span class="tech-tag">Flag 结算中心</span>
                                    <span class="tech-tag">红队战术</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_hub.php" class="launch-target-btn">⚡ 接入大厅 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">L8 免杀</span>
                                    <span class="card-rating-pill">★★★☆☆ <span class="pts">150 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L8 Win32 API · WOW64 · 架构基础</h3>
                                <p class="card-vuln-desc">深入分析 VirtualAlloc, WriteProcessMemory, CreateThread 调用链，解析 PE 结构与 32/64 位切换。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Win32 API</span>
                                    <span class="tech-tag">PE Header</span>
                                    <span class="tech-tag">Heaven's Gate</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_l8_win_api.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">L10 注入</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L10 进程注入与进程镂空技术</h3>
                                <p class="card-vuln-desc">演练挂起创建合法进程、ZwUnmapViewOfSection 卸载内存段、覆写恶性 PE 镜像并恢复主线程执行。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Process Hollowing</span>
                                    <span class="tech-tag">Reflective DLL</span>
                                    <span class="tech-tag">EDR 规避</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_l10_process_inject.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">L11 防御绕过</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L11 AMSI 与 UAC 绕过技术</h3>
                                <p class="card-vuln-desc">内存 Patch AmsiScanBuffer 函数返回 AMSI_RESULT_CLEAN，结合 COM 接口自动提升突破 UAC 限制。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">AMSI Patch</span>
                                    <span class="tech-tag">UAC Bypass</span>
                                    <span class="tech-tag">PowerShell 反射</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_l11_amsi_bypass.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">L13 隐蔽信道</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L13 网络过滤与 DNS 隐蔽隧道</h3>
                                <p class="card-vuln-desc">构建 DNS TXT/CNAME 隐蔽数据回传通道，结合 CDN 域前置 (Domain Fronting) 规避出口防火墙检测。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">DNS Tunnel</span>
                                    <span class="tech-tag">Domain Fronting</span>
                                    <span class="tech-tag">C2 隐蔽通信</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_l13_net_evasion.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osep">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osep">L18 终章</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">400 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L18 AD 森林信任与委派利用 [终章]</h3>
                                <p class="card-vuln-desc">跨森林 SID 历史注入、ExtraSids 伪造 Golden Ticket、非约束委派与 Printer Bug 触发完整击杀链。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Forest Trust</span>
                                    <span class="tech-tag">ExtraSids</span>
                                    <span class="tech-tag">SpoolSample</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osep/osep_l18_ad_deep.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 9. OSWE 高级代码审计与白盒利用 ================= -->
                <div class="domain-section" data-domain="oswe">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🔍 OSWE 高级代码审计与白盒利用 (OffSec OSWE Certified Path - 14 关)
                        </h2>
                        <span class="domain-section-badge">14 关全量白盒审计利用链</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">OSWE 专区</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">OFFSEC</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 OSWE 白盒审计实战指挥大厅 (Hub)</h3>
                                <p class="card-vuln-desc">OffSec 白盒代码审计实战进阶：涵盖数据流污点分析、POP 链、SSTI 沙箱逃逸与自动化 Exploit 脚本。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">14 关全栈</span>
                                    <span class="tech-tag">Python 脚本化</span>
                                    <span class="tech-tag">POP 链挖掘</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_hub.php" class="launch-target-btn">⚡ 接入大厅 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">L4 反序列化</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L4 PHP/Java 反序列化与 POP 链构造</h3>
                                <p class="card-vuln-desc">深挖魔术方法调用链 (__destruct ➔ __toString ➔ __call)，从属性可控到最终实现任意代码执行。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">POP Chain</span>
                                    <span class="tech-tag">__destruct</span>
                                    <span class="tech-tag">Gadget 挖掘</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_l4_deser.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">L5 模板逃逸</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L5 SSTI 服务端模板注入与沙箱逃逸</h3>
                                <p class="card-vuln-desc">Jinja2 / Twig 引擎模板注入，遍历 Python 类继承链 (__mro__) 访问 subprocess.Popen 实现沙箱逃逸。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">SSTI</span>
                                    <span class="tech-tag">Jinja2 沙箱</span>
                                    <span class="tech-tag">__subclasses__</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_l5_ssti.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">L8 盲注脚本</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L8 盲注自动化利用脚本编写</h3>
                                <p class="card-vuln-desc">编写 Python 多线程二分法盲注利用脚本，涵盖会话维持、逐字符提取与完整无交互 Exploit 串联。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Python Exploit</span>
                                    <span class="tech-tag">二分法提取</span>
                                    <span class="tech-tag">自动化渗透</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_l8_sqli_blind.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">L11 原型链</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L11 JavaScript 原型链污染深度利用</h3>
                                <p class="card-vuln-desc">污染 lodash.merge 等深层归并函数，进而污染 EJS outputFunctionName 或 Handlebars 辅助函数实现 RCE。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Prototype Pollution</span>
                                    <span class="tech-tag">EJS RCE</span>
                                    <span class="tech-tag">Node.js 审计</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_l11_proto_pollution.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="oswe">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-oswe">L14 终章</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">300 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L14 CSRF + CORS 认证绕过联合链 [终章]</h3>
                                <p class="card-vuln-desc">利用配置不当的 CORS 跨域反射，结合 CSRF 强行发起携带凭据的交互，无需目标交互全自动窃取特权凭据。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">CORS Misconfig</span>
                                    <span class="tech-tag">CSRF Exploit</span>
                                    <span class="tech-tag">无需用户交互</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/oswe/oswe_l14_csrf_cors.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 10. OSED 二进制漏洞利用与逆向 ================= -->
                <div class="domain-section" data-domain="osed">
                    <div class="domain-section-header">
                        <h2 class="domain-section-title">
                            🔬 OSED 二进制漏洞利用与逆向 (OffSec OSED Certified Path - 10 关)
                        </h2>
                        <span class="domain-section-badge">10 关底层漏洞利用硬核演练</span>
                    </div>
                    <div class="matrix-grid">
                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">OSED 专区</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">OFFSEC</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 OSED 二进制漏洞利用实战大厅 (Hub)</h3>
                                <p class="card-vuln-desc">OffSec 二进制逆向进阶：涵盖 PEB 动态定位、PIC 自定义 Shellcode、ROP 链构造及 WPM 联合绕过。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">10 关硬核</span>
                                    <span class="tech-tag">ROP 链</span>
                                    <span class="tech-tag">Shellcode 编写</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_hub.php" class="launch-target-btn">⚡ 接入大厅 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">L4 内存搜索</span>
                                    <span class="card-rating-pill">★★★★☆ <span class="pts">250 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L4 Egghunter 内存分块搜索定位技术</h3>
                                <p class="card-vuln-desc">缓冲区受限场景下编写 32 字节 Egghunter 汇编跳板，利用 NtAccessCheckServerToken 跨内存段寻址执行 Shellcode。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Egghunter</span>
                                    <span class="tech-tag">x86 汇编</span>
                                    <span class="tech-tag">0x77303074</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_l4_egghunter.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">L6 ROP 链</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L6 ROP 返回导向编程与 DEP 绕过</h3>
                                <p class="card-vuln-desc">寻找非 ASLR 模块中的 Gadget 指令碎片，在栈上拼装 VirtualProtect API 参数，将不可执行栈标记为 RWX。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">ROP Gadgets</span>
                                    <span class="tech-tag">VirtualProtect</span>
                                    <span class="tech-tag">DEP Bypass</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_l6_rop.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">L7 汇编编写</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">350 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L7 x86 汇编与自定义 PIC Shellcode</h3>
                                <p class="card-vuln-desc">通过 FS:[0x30] 遍历 PEB 结构动态定位 kernel32.dll 导出表，编写不含 0x00 坏字符的位置无关反弹 Shell 字节码。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">PEB Traversal</span>
                                    <span class="tech-tag">Null-Free PIC</span>
                                    <span class="tech-tag">GetProcAddress</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_l7_asm_shellcode.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">L8 格式化字符串</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">400 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L8 格式化字符串读写原语利用</h3>
                                <p class="card-vuln-desc">利用 %p 原语泄露栈基址绕过 ASLR，构造 %n 系列指令按字节任意写，覆写 SEH 或返回地址劫持控制流。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">Format String</span>
                                    <span class="tech-tag">%p 泄露基址</span>
                                    <span class="tech-tag">%n 任意写</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_l8_format_string.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="matrix-card" data-domain="osed">
                            <div>
                                <div class="card-header-row">
                                    <span class="card-cat-badge cat-badge-osed">L10 终章</span>
                                    <span class="card-rating-pill">★★★★★ <span class="pts">400 PTS</span></span>
                                </div>
                                <h3 class="card-vuln-title">🚩 L10 WriteProcessMemory DEP+ASLR 绕过 [终章]</h3>
                                <p class="card-vuln-desc">无需修改页保护属性，构造 ROP 链调用 WriteProcessMemory 将 Shellcode 注入已知可执行代码段跳转执行。</p>
                                <div class="card-tags-row">
                                    <span class="tech-tag">WriteProcessMemory</span>
                                    <span class="tech-tag">DEP + ASLR 联合绕过</span>
                                    <span class="tech-tag">终章通关</span>
                                </div>
                            </div>
                            <div class="card-footer-row">
                                <a href="vul/osed/osed_l10_wpm_bypass.php" class="launch-target-btn">⚡ 接入演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- /.threat-matrix-wrapper -->
        </div><!-- /.page-content -->
    </div>
</div><!-- /.main-content -->

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('matrixSearchInput');
    const filterTabs = document.querySelectorAll('.domain-pill');
    const domainSections = document.querySelectorAll('.domain-section');
    const emptyState = document.getElementById('emptyMatrixState');

    let currentDomainFilter = 'all';

    function performMatrixFilter() {
        const query = (searchInput.value || '').toLowerCase().trim();
        let totalVisibleCards = 0;

        domainSections.forEach(section => {
            const domainKey = section.getAttribute('data-domain');
            const cards = section.querySelectorAll('.matrix-card');
            let visibleInSection = 0;

            const domainMatches = (currentDomainFilter === 'all' || currentDomainFilter === domainKey);

            cards.forEach(card => {
                const title = (card.querySelector('.card-vuln-title') ? card.querySelector('.card-vuln-title').textContent : '').toLowerCase();
                const desc = (card.querySelector('.card-vuln-desc') ? card.querySelector('.card-vuln-desc').textContent : '').toLowerCase();
                const tags = Array.from(card.querySelectorAll('.tech-tag')).map(t => t.textContent.toLowerCase()).join(' ');

                const queryMatches = !query || title.includes(query) || desc.includes(query) || tags.includes(query);

                if (domainMatches && queryMatches) {
                    card.style.display = 'flex';
                    visibleInSection++;
                    totalVisibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleInSection > 0) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        if (totalVisibleCards === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }

    filterTabs.forEach(btn => {
        btn.addEventListener('click', function() {
            filterTabs.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentDomainFilter = this.getAttribute('data-target');
            performMatrixFilter();
        });
    });

    searchInput.addEventListener('input', performMatrixFilter);
});
</script>

<?php
include 'footer.php';
?>
