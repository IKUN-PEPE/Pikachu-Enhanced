<?php
/**
 * Pikachu-Enhanced v2.0 - SSRF (服务端请求伪造) 概览大厅与攻防矩阵
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[106] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.ssrf-hero-banner {
    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
    border-radius: 16px;
    padding: 32px 36px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(2, 132, 199, 0.25);
    margin-bottom: 26px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    position: relative;
    overflow: hidden;
}
.ssrf-hero-banner::after {
    content: '\f0ac';
    font-family: 'FontAwesome';
    position: absolute;
    right: 20px;
    bottom: -20px;
    font-size: 140px;
    opacity: 0.08;
    pointer-events: none;
}
.ssrf-hero-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 12px 0;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ssrf-chip-badge {
    background: rgba(14, 165, 233, 0.25);
    color: #7dd3fc;
    border: 1px solid #38bdf8;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.ssrf-hero-desc {
    font-size: 14px;
    color: #e0f2fe;
    line-height: 1.75;
    max-width: 960px;
    margin: 0;
}

.ssrf-flow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin: 20px 0;
}
.ssrf-flow-step {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 18px 20px;
    transition: all 0.25s ease;
}
.ssrf-flow-step:hover {
    border-color: #0284c7;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.15);
}
.ssrf-step-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
}

.ssrf-level-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 26px;
}
.ssrf-level-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    border-top: 4px solid #0284c7;
}
.ssrf-level-card:hover {
    transform: translateY(-4px);
    border-color: #0284c7;
    box-shadow: 0 10px 25px rgba(2, 132, 199, 0.15);
}
.ssrf-level-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ssrf-level-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 18px;
    flex-grow: 1;
}

.ssrf-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    font-size: 13px;
}
.ssrf-table th {
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 700;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    text-align: left;
}
.ssrf-table td {
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    line-height: 1.6;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li class="active">SSRF 服务端请求伪造攻防演练大厅</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="ssrf-hero-banner">
                    <h1 class="ssrf-hero-title">
                        🌐 SSRF (Server-Side Request Forgery) 服务端请求伪造
                        <span class="ssrf-chip-badge">内网穿透 · 云元数据窃取 · Gopher RCE · 350 PTS</span>
                    </h1>
                    <p class="ssrf-hero-desc">
                        SSRF（服务端请求伪造）是由攻击者构造恶意载荷，诱使目标服务器向内部网络或第三方受控系统发起未经授权的网络请求的漏洞。由于目标服务器通常处于受信任的内部网络边界之内，攻击者可借助该服务器作为“跳板”，探测内网开放端口、读取本地敏感文件（<code>file://</code>）、窃取云服务器实例元数据（AWS / 阿里云 IMDS）、甚至利用万能协议（<code>gopher://</code>）对内网未授权 Redis / FastCGI 实施远程代码执行（RCE）！
                    </p>
                </div>

                <!-- Architecture Lifecycle -->
                <div class="cyber-header-card" style="margin-bottom: 26px;">
                    <h4 style="margin:0 0 8px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                        <i class="fa fa-sitemap" style="color:#0284c7;"></i> SSRF 核心攻击链路演进
                    </h4>
                    <div class="ssrf-flow-grid">
                        <div class="ssrf-flow-step">
                            <div class="ssrf-step-badge">1</div>
                            <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">注入远程 URL</div>
                            <p style="margin:0; font-size:12.5px; color:var(--text-secondary);">攻击者在图片抓取、Webhook 回调或 URL 导入参数中植入受控地址。</p>
                        </div>
                        <div class="ssrf-flow-step">
                            <div class="ssrf-step-badge">2</div>
                            <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">服务端发起请求</div>
                            <p style="margin:0; font-size:12.5px; color:var(--text-secondary);">目标服务器未严格校验协议与目标 IP，使用底层 cURL/FGC 客户端向内网发送请求。</p>
                        </div>
                        <div class="ssrf-flow-step">
                            <div class="ssrf-step-badge">3</div>
                            <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">内网资产探测</div>
                            <p style="margin:0; font-size:12.5px; color:var(--text-secondary);">绕过外部防火墙限制，直接访问内网 127.0.0.1、10.0.0.0/8、192.168.0.0/16 及元数据服务。</p>
                        </div>
                        <div class="ssrf-flow-step">
                            <div class="ssrf-step-badge">4</div>
                            <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">深度利用与提权</div>
                            <p style="margin:0; font-size:12.5px; color:var(--text-secondary);">窃取 Cloud AK/SK 凭证接管云资源，或利用 Gopher 发送 RESP 二进制流打 Redis 实现 RCE。</p>
                        </div>
                    </div>
                </div>

                <!-- Level Cards -->
                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                    <i class="fa fa-gamepad" style="color:#0284c7;"></i> SSRF 核心与进阶实战关卡
                </h4>

                <div class="ssrf-level-grid">
                    <!-- Level 1: cURL -->
                    <div class="ssrf-level-card" style="border-top-color:#06b6d4;">
                        <div>
                            <div class="ssrf-level-title">
                                <span>⚡ 关卡 1: SSRF (cURL 方式)</span>
                                <span class="badge badge-info" style="font-size:11px;">100 PTS</span>
                            </div>
                            <p class="ssrf-level-desc">
                                后端使用 PHP <code>curl_exec()</code> 执行网络抓取。演示利用 <code>http://</code> 探测内网及 <code>file:///etc/passwd</code> 读取本地任意文件。
                            </p>
                        </div>
                        <a href="ssrf_curl.php" class="btn btn-block btn-info" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none;">
                            进入演练：SSRF (cURL) <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 2: FGC -->
                    <div class="ssrf-level-card" style="border-top-color:#f59e0b;">
                        <div>
                            <div class="ssrf-level-title">
                                <span>⚡ 关卡 2: SSRF (file_get_contents)</span>
                                <span class="badge badge-warning" style="font-size:11px;">100 PTS</span>
                            </div>
                            <p class="ssrf-level-desc">
                                后端使用 PHP <code>file_get_contents()</code> 读取远程资源。分析其支持的 PHP 伪协议（<code>php://filter</code>、<code>data://</code>）与利用限制。
                            </p>
                        </div>
                        <a href="ssrf_fgc.php" class="btn btn-block btn-warning" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #f59e0b, #d97706); border:none;">
                            进入演练：SSRF (FGC) <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 3: Cloud Metadata -->
                    <div class="ssrf-level-card" style="border-top-color:#8b5cf6;">
                        <div>
                            <div class="ssrf-level-title">
                                <span>⚡ 关卡 3: 云服务器元数据凭证窃取</span>
                                <span class="badge badge-purple" style="font-size:11px; background:#8b5cf6;">250 PTS</span>
                            </div>
                            <p class="ssrf-level-desc">
                                模拟阿里云 ECS（<code>100.100.100.200</code>）与 AWS EC2（<code>169.254.169.254</code>）元数据接口，实战提取 RAM 角色 STS 临时访问凭证（AK/SK）。
                            </p>
                        </div>
                        <a href="ssrf_cloud.php" class="btn btn-block btn-purple" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #8b5cf6, #7c3aed); border:none;">
                            进入演练：云元数据窃取 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 4: Gopher Redis -->
                    <div class="ssrf-level-card" style="border-top-color:#ef4444;">
                        <div>
                            <div class="ssrf-level-title">
                                <span>⚡ 关卡 4: Gopher 协议打 Redis (RCE)</span>
                                <span class="badge badge-danger" style="font-size:11px;">350 PTS</span>
                            </div>
                            <p class="ssrf-level-desc">
                                经典 SSRF 终极利用：将 Redis RESP 协议转化为 <code>gopher://</code> 载荷，向内网 Redis 6379 写入 PHP WebShell 或定时任务反弹 Shell。
                            </p>
                        </div>
                        <a href="ssrf_gopher_redis.php" class="btn btn-block btn-danger" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #ef4444, #dc2626); border:none;">
                            进入演练：Gopher 打 Redis <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 5: DNS Rebinding & Bypass -->
                    <div class="ssrf-level-card" style="border-top-color:#10b981;">
                        <div>
                            <div class="ssrf-level-title">
                                <span>⚡ 关卡 5: DNS 重绑定与 IP 变形绕过</span>
                                <span class="badge badge-success" style="font-size:11px;">250 PTS</span>
                            </div>
                            <p class="ssrf-level-desc">
                                深入研习针对内网 IP 黑名单的各种绕过技巧：进制转换（<code>0177.0.0.1</code> / <code>0x7f.1</code>）、点分十进制省略、IPv6 格式及 DNS Rebinding 双解析绕过。
                            </p>
                        </div>
                        <a href="ssrf_dns_rebinding.php" class="btn btn-block btn-success" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                            进入演练：DNS 重绑定与绕过 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Defense Matrix -->
                <div class="cyber-header-card">
                    <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                        <i class="fa fa-shield" style="color:#10b981;"></i> SSRF 纵深防御方案矩阵
                    </h4>
                    <div style="overflow-x:auto;">
                        <table class="ssrf-table">
                            <thead>
                                <tr>
                                    <th style="width:25%;">防御策略</th>
                                    <th style="width:18%;">防护评级</th>
                                    <th>实现机制与效果说明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>协议限制白名单</b></td>
                                    <td><span class="label label-success" style="border-radius:4px;">★★★★★ 必备策略</span></td>
                                    <td>仅允许 <code>http://</code> 与 <code>https://</code> 协议，彻底禁用 <code>file://</code>、<code>gopher://</code>、<code>dict://</code> 等危险伪协议。</td>
                                </tr>
                                <tr>
                                    <td><b>目标 IP 白名单 / 私网黑名单</b></td>
                                    <td><span class="label label-success" style="border-radius:4px;">★★★★☆ 核心防护</span></td>
                                    <td>解析域名获取真实 IP 后判断，严禁访问 <code>127.0.0.0/8</code>、<code>10.0.0.0/8</code>、<code>172.16.0.0/12</code>、<code>192.168.0.0/16</code> 及元数据地址。</td>
                                </tr>
                                <tr>
                                    <td><b>防 DNS Rebinding 二次校验</b></td>
                                    <td><span class="label label-success" style="border-radius:4px;">★★★★★ 深度防御</span></td>
                                    <td>在解析域名并校验 IP 合法后，直接通过该解析后的 IP 地址发起请求，避免客户端二次解析引发 DNS 重绑定竞争。</td>
                                </tr>
                                <tr>
                                    <td><b>云端开启 IMDSv2 (Session Token)</b></td>
                                    <td><span class="label label-info" style="border-radius:4px;">★★★★★ 云环境基线</span></td>
                                    <td>AWS / 阿里云均支持 IMDSv2，要求先通过 PUT 获取 Token 才能请求元数据，有效阻断常规 GET SSRF 窃取凭证。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
