<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (跨站请求伪造) 概览与核心攻防大厅
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[26] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.csrf-hero-banner {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    border-radius: 16px;
    padding: 32px 36px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(49, 46, 129, 0.25);
    margin-bottom: 26px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    position: relative;
    overflow: hidden;
}
.csrf-hero-banner::after {
    content: '\f21b';
    font-family: 'FontAwesome';
    position: absolute;
    right: 20px;
    bottom: -20px;
    font-size: 140px;
    opacity: 0.08;
    pointer-events: none;
}
.csrf-hero-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 12px 0;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.csrf-chip-badge {
    background: rgba(99, 102, 241, 0.25);
    color: #a5b4fc;
    border: 1px solid #818cf8;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.csrf-hero-desc {
    font-size: 14px;
    color: #e0e7ff;
    line-height: 1.75;
    max-width: 960px;
    margin: 0;
}

/* Attack Chain Flow */
.csrf-flow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin: 20px 0;
}
.csrf-flow-step {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 18px 20px;
    position: relative;
    transition: all 0.25s ease;
}
.csrf-flow-step:hover {
    border-color: #6366f1;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.15);
}
.flow-step-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
}
.flow-step-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.flow-step-desc {
    font-size: 12.5px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

/* Level Access Cards */
.csrf-level-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 26px;
}
.csrf-level-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    border-top: 4px solid #6366f1;
}
.csrf-level-card:hover {
    transform: translateY(-4px);
    border-color: #6366f1;
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15);
}
.csrf-level-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.csrf-level-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 18px;
    flex-grow: 1;
}

/* Defense Matrix */
.csrf-defense-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    font-size: 13px;
}
.csrf-defense-table th {
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 700;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    text-align: left;
}
.csrf-defense-table td {
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
                <li class="active">CSRF 跨站请求伪造演练大厅</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Hero Banner -->
                <div class="csrf-hero-banner">
                    <h1 class="csrf-hero-title">
                        🛡️ CSRF (Cross-Site Request Forgery) 跨站请求伪造
                        <span class="csrf-chip-badge">会话挟持 · 客户端欺骗 · 300 PTS</span>
                    </h1>
                    <p class="csrf-hero-desc">
                        CSRF（跨站请求伪造）是一种挟持用户已认证会话向受信任站点发起恶意操作的客户端漏洞。攻击者并不直接窃取用户的 Cookie 凭据，而是诱导受害者在已登录状态下访问恶意第三方页面，利用浏览器<b>发起跨站请求时自动携带目标域 Cookie</b> 的机制，以受害者的名义执行转账、修改密码或个人信息等未授权业务。
                    </p>
                </div>

                <!-- Attack Flow Visualization Section -->
                <div class="cyber-header-card" style="margin-bottom: 26px;">
                    <h4 style="margin:0 0 8px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                        <i class="fa fa-sitemap" style="color:#6366f1;"></i> CSRF 攻击核心生命周期链
                    </h4>
                    <p style="color:var(--text-secondary); font-size:13px; margin:0;">CSRF 漏洞的成功触发依赖于以下 4 个关键节点的协同运作：</p>

                    <div class="csrf-flow-grid">
                        <div class="csrf-flow-step">
                            <div class="flow-step-badge">1</div>
                            <div class="flow-step-title">用户成功鉴权</div>
                            <p class="flow-step-desc">受害者登录受信网站 A，并在浏览器中保留了有效的 Session ID 或身份 Cookie。</p>
                        </div>
                        <div class="csrf-flow-step">
                            <div class="flow-step-badge">2</div>
                            <div class="flow-step-title">访问恶意页面</div>
                            <p class="flow-step-desc">受害者未注销网站 A 的情况下，在同一浏览器中访问了攻击者精心构造的网站 B。</p>
                        </div>
                        <div class="csrf-flow-step">
                            <div class="flow-step-badge">3</div>
                            <div class="flow-step-title">自动跨站提交</div>
                            <p class="flow-step-desc">网站 B 包含指向网站 A 的请求（如 <code>&lt;img&gt;</code> 或自动提交的隐藏表单），浏览器自动附加 A 的凭据。</p>
                        </div>
                        <div class="csrf-flow-step">
                            <div class="flow-step-badge">4</div>
                            <div class="flow-step-title">目标服务端执行</div>
                            <p class="flow-step-desc">网站 A 验证 Cookie 有效，误认为是受害者自主发起的正常请求并完成关键修改。</p>
                        </div>
                    </div>
                </div>

                <!-- Level Cards Section -->
                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                    <i class="fa fa-gamepad" style="color:#6366f1;"></i> CSRF 实战演练核心与进阶关卡全景
                </h4>

                <div class="csrf-level-grid">
                    <!-- Level 1: GET -->
                    <div class="csrf-level-card" style="border-top-color:#06b6d4;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 1: CSRF (GET 方式)</span>
                                <span class="badge badge-info" style="font-size:11px;">100 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                业务操作直接通过 HTTP GET 参数传递并在服务端执行持久化修改。攻击者只需通过诱导访问一个图片链接（如 <code>&lt;img src="...edit.php?add=evil"&gt;</code>）即可零点击完成跨站篡改。
                            </p>
                        </div>
                        <a href="csrfget/csrf_get_login.php" class="btn btn-block btn-info" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none;">
                            进入演练：CSRF (GET) <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 2: POST -->
                    <div class="csrf-level-card" style="border-top-color:#f59e0b;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 2: CSRF (POST 方式)</span>
                                <span class="badge badge-warning" style="font-size:11px;">100 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                业务操作改为 POST 请求体传输。单纯将 GET 转为 POST 并不能防范 CSRF，攻击者可以通过构造第三方隐藏表单与 <code>document.forms[0].submit()</code> 脚本实现全自动跨站提交。
                            </p>
                        </div>
                        <a href="csrfpost/csrf_post_login.php" class="btn btn-block btn-warning" style="border-radius:8px; font-weight:700; color:#ffffff; background:linear-gradient(135deg, #f59e0b, #d97706); border:none;">
                            进入演练：CSRF (POST) <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 3: Token -->
                    <div class="csrf-level-card" style="border-top-color:#10b981;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 3: CSRF Token 防御机制</span>
                                <span class="badge badge-success" style="font-size:11px;">100 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                服务端在表单中植入不可预测的随机 Token。由于同源策略（SOP）限制第三方站点读取响应体内容，跨站发起的请求无法携带合法的 Token 参数，从而被服务端有效拦截。
                            </p>
                        </div>
                        <a href="csrftoken/token_get_login.php" class="btn btn-block btn-success" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                            进入演练：CSRF Token <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 4: Referer Bypass -->
                    <div class="csrf-level-card" style="border-top-color:#6366f1;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 4: Referer 来源校验绕过</span>
                                <span class="badge badge-primary" style="font-size:11px;">200 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                依赖 Referer 请求头的防御存在空 Referer 放行与弱正则匹配漏洞。利用 <code>&lt;meta name="referrer" content="no-referrer"&gt;</code> 剥离来源头绕过防御。
                            </p>
                        </div>
                        <a href="csrf_referer/csrf_referer.php" class="btn btn-block btn-primary" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #6366f1, #4f46e5); border:none;">
                            进入演练：Referer 绕过 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 5: Token Pool Unbound -->
                    <div class="csrf-level-card" style="border-top-color:#8b5cf6;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 5: Token 池未绑定会话</span>
                                <span class="badge badge-purple" style="font-size:11px; background:#8b5cf6;">200 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                服务端维护了全局有效 Token 池，但在校验时未检查 Token 是否属于当前会话用户。攻击者用自己账号获取合法 Token 植入受害者表单实施篡改。
                            </p>
                        </div>
                        <a href="csrf_token_pool/csrf_token_pool.php" class="btn btn-block btn-purple" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #8b5cf6, #7c3aed); border:none;">
                            进入演练：Token 池缺陷 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 6: JSON Form CSRF -->
                    <div class="csrf-level-card" style="border-top-color:#ec4899;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 6: JSON 表单编码混淆欺骗</span>
                                <span class="badge badge-pink" style="font-size:11px; background:#ec4899;">250 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                针对接收 JSON 数据的 REST API，当服务端未强校验 Content-Type 时，利用 <code>&lt;form enctype="text/plain"&gt;</code> 构造合法 JSON 跨站伪造。
                            </p>
                        </div>
                        <a href="csrf_json/csrf_json.php" class="btn btn-block btn-pink" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #ec4899, #db2777); border:none;">
                            进入演练：JSON 表单混淆 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 7: Double Submit Cookie -->
                    <div class="csrf-level-card" style="border-top-color:#0284c7;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 7: 双重 Cookie 校验绕过</span>
                                <span class="badge badge-info" style="font-size:11px; background:#0284c7;">250 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                无状态双重 Cookie 仅比对 Cookie 与 POST 参数。攻击者利用子域名注入 Cookie（Cookie Tossing）为目标域写入已知 Token 击穿防护。
                            </p>
                        </div>
                        <a href="csrf_double_cookie/csrf_double_cookie.php" class="btn btn-block btn-info" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #0284c7, #0369a1); border:none;">
                            进入演练：双重 Cookie 绕过 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Level 8: SameSite Lax Bypass -->
                    <div class="csrf-level-card" style="border-top-color:#ef4444;">
                        <div>
                            <div class="csrf-level-title">
                                <span>⚡ 关卡 8: SameSite Lax 限制绕过</span>
                                <span class="badge badge-danger" style="font-size:11px;">300 PTS</span>
                            </div>
                            <p class="csrf-level-desc">
                                针对浏览器默认的 SameSite=Lax 策略，利用 <code>_method=POST</code> 请求方法覆盖与顶层 GET 导航携带 Cookie 的特性触发敏感情境。
                            </p>
                        </div>
                        <a href="csrf_samesite/csrf_samesite.php" class="btn btn-block btn-danger" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #ef4444, #dc2626); border:none;">
                            进入演练：SameSite Lax 绕过 <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Defense Matrix Section -->
                <div class="cyber-header-card">
                    <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                        <i class="fa fa-shield" style="color:#10b981;"></i> CSRF 主流防御技术方案矩阵
                    </h4>
                    <div style="overflow-x:auto;">
                        <table class="csrf-defense-table">
                            <thead>
                                <tr>
                                    <th style="width:22%;">防御机制</th>
                                    <th style="width:18%;">防护等级</th>
                                    <th>实现原理与防护效果</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>Anti-CSRF Token</b></td>
                                    <td><span class="label label-success" style="border-radius:4px;">★★★★★ 工业级标准</span></td>
                                    <td>服务端在表单和 Session 中生成不可预测的随机令牌并在提交时比对。第三方站点因同源策略无法读取 Token，是目前最稳健的防御手段。</td>
                                </tr>
                                <tr>
                                    <td><b>SameSite Cookie 属性</b></td>
                                    <td><span class="label label-success" style="border-radius:4px;">★★★★☆ 现代浏览器基线</span></td>
                                    <td>配置 <code>SameSite=Strict</code> 或 <code>SameSite=Lax</code>。跨站请求（Cross-Site）时浏览器将拒绝自动附带 Cookie，从底层切断会话凭据。</td>
                                </tr>
                                <tr>
                                    <td><b>Referer / Origin 校验</b></td>
                                    <td><span class="label label-warning" style="border-radius:4px;">★★★☆☆ 辅助防御</span></td>
                                    <td>服务端检查 HTTP 头部中的来源域名是否属于受信任白名单。存在协议降级丢失、代理剥离或正则解析绕过风险，需作为辅助手段。</td>
                                </tr>
                                <tr>
                                    <td><b>敏感操作二次验证</b></td>
                                    <td><span class="label label-info" style="border-radius:4px;">★★★★★ 针对核心业务</span></td>
                                    <td>在资金交易、密码修改等高危接口强制输入短信验证码、图形验证码或密码重新校验，彻底杜绝无感自动提交。</td>
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
