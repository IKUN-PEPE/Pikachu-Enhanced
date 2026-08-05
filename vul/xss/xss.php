<?php
/**
 * Pikachu-Enhanced XSS Vulnerability Overview & Interactive Workflow
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[7] = 'active open';
$ACTIVE[8] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.xss-hero-card {
    background: linear-gradient(135deg, #1e1b4b, #311042);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.1);
}
.xss-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.xss-badge {
    background: rgba(168, 85, 247, 0.2);
    color: #c084fc;
    border: 1px solid rgba(168, 85, 247, 0.4);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.xss-hero-card p {
    font-size: 15px;
    color: #cbd5e1;
    line-height: 1.7;
    max-width: 900px;
    margin-bottom: 0;
}

.xss-workflow-section {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 30px;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.xss-workflow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}
.xss-step {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
}
.xss-step-badge {
    width: 36px;
    height: 36px;
    background: #a855f7;
    color: #ffffff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 12px;
}

.xss-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.xss-type-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    border-top: 4px solid #a855f7;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="xss-hero-card">
                <h1>
                    XSS 跨站脚本攻击 (Cross-Site Scripting)
                    <span class="xss-badge">OWASP Top 10 核心漏洞</span>
                </h1>
                <p>
                    XSS 是一种发生在前端浏览器侧的经典注入漏洞。当应用程序包含用户可控制的数据，且未对数据实施严格的安全编码、转义或过滤，就直接输出渲染到前端 HTML/JavaScript 上下文时，攻击者即可在受害者浏览器中执行恶意 JavaScript 脚本，从而窃取 Cookie、劫持 Session 甚至发起 CSRF / 钓鱼攻击。
                </p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="xss-workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-code" style="color: #a855f7;"></i> XSS 漏洞触发与攻击链流程
                </h3>
                
                <div class="xss-workflow-grid">
                    <div class="xss-step">
                        <div class="xss-step-badge" style="background: #3b82f6;">1</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">注入 Payload</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">攻击者提交包含 <code>&lt;script&gt;</code>、<code>onerror</code> 或 <code>javascript:</code> 伪协议的恶意载荷。</div>
                    </div>
                    
                    <div class="xss-step">
                        <div class="xss-step-badge" style="background: #f59e0b;">2</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">未实体化转义</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">服务器直接存储或回显输入，未调用 <code>htmlspecialchars()</code> 实体化转义特殊字符。</div>
                    </div>
                    
                    <div class="xss-step">
                        <div class="xss-step-badge" style="background: #ef4444;">3</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">前端浏览器解析</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">受害者访问页面，浏览器误将注入数据当作合法代码执行。</div>
                    </div>
                    
                    <div class="xss-step">
                        <div class="xss-step-badge" style="background: #10b981;">4</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">凭据窃取与控制</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">JS 脚本读取 <code>document.cookie</code> 发送至攻击者接收服务器 (XSS Background)。</div>
                    </div>
                </div>
            </div>

            <!-- XSS Types -->
            <div class="xss-type-grid">
                <div class="xss-type-card" style="border-top-color: #3b82f6;">
                    <h3 style="margin-top:0; font-size:18px;">⚡ 1. 反射型 XSS (Reflected XSS)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        攻击载荷包含在请求 URL 参数中。服务器接收到请求后，立即将参数“反射”回显在响应 HTML 中，仅影响主动点击包含 Payload 链接的用户。
                    </p>
                </div>

                <div class="xss-type-card" style="border-top-color: #ef4444;">
                    <h3 style="margin-top:0; font-size:18px;">💾 2. 存储型 XSS (Stored XSS)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        危害最大。攻击载荷被永久持久化存储到数据库、留言板或配置文件中。任何正常访问该页面的用户都会无差别触发攻击。
                    </p>
                </div>

                <div class="xss-type-card" style="border-top-color: #10b981;">
                    <h3 style="margin-top:0; font-size:18px;">🌐 3. DOM 型 XSS (DOM-based XSS)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        不依赖服务器端回显。纯粹在客户端通过 JavaScript 从 <code>location.search</code> 或输入框提取数据并动态写入 DOM (如 <code>innerHTML</code>) 触发。
                    </p>
                </div>
            </div>

            <div class="vul" style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 16px;">🎯 开始 XSS 实战关卡演练</h3>
                    <p style="margin: 0; font-size: 14px;">可以通过左侧菜单进入反射型、存储型或 DOM 型 XSS 关卡进阶测试！</p>
                </div>
                <a href="xss_reflected_get.php" class="btn btn-primary" style="flex-shrink: 0;">进入反射型 XSS (GET) →</a>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
