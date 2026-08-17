<?php
/**
 * Pikachu-Enhanced v2.0 - SSRF (curl) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$ssrf_triggered = false;
$user_url = $_GET['url'] ?? '';

if (isset($_GET['submit']) && !empty($user_url)) {
    $url = trim($user_url);
    
    if (preg_match('/127\.0\.0\.1|localhost|file:\/\/|dict:\/\/|gopher:\/\/|169\.254/i', $url)) {
        $ssrf_triggered = true;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        $html .= "<div class='alert alert-danger' style='margin:0;'><b>cURL 请求失败：</b> " . htmlspecialchars($error) . "</div>";
    } else {
        $html .= "<div class='alert alert-info' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-globe'></i> cURL 请求响应结果：</h4>";
        $html .= "<pre style='background:#0f172a; color:#38bdf8; border-radius:6px; padding:12px; font-size:12.5px; margin:8px 0 0 0; max-height:280px; overflow-y:auto;'>" . htmlspecialchars($response ?? '') . "</pre>";
        $html .= "</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF 服务端请求伪造</a></li>
                <li class="active">SSRF(curl) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="服务端利用 curl 抓取外部 URL，可传入 127.0.0.1 探测内网或传入 file:/// 读取服务器敏感文件。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 服务端请求伪造 (SSRF - cURL) 攻防教学
                        <span class="cyber-badge-chip">SSRF · 内网探测 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        服务端请求伪造（Server-Side Request Forgery）是指攻击者构造恶意的网络请求，<b>由目标服务端服务器代替攻击者发起网络请求</b>。由于发起请求的主体是受信任的服务端自身，攻击者可借此绕过防火墙，探测服务器本地回路（<code>127.0.0.1</code>）、扫描内网敏感端口、甚至通过 <code>file://</code>、<code>gopher://</code>、<code>dict://</code> 等协议读取本地文件或攻击内网 Redis！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 远程资源抓取控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入要由服务器抓取的 URL 目标地址：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="url_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">目标 URL / 伪协议 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="url_input" name="url" value="<?php echo htmlspecialchars($user_url); ?>" placeholder="输入 URL，如 http://127.0.0.1:8765/info.php 或 file:///etc/passwd" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-download"></i> 服务端抓取
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('http://127.0.0.1:8765/vul/ssrf/ssrf_info.php')"><i class="fa fa-info-circle" style="color:var(--accent);"></i> 内网探测: 本地敏感信息</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('file:///etc/passwd')"><i class="fa fa-file-text"></i> 文件协议: file:///etc/passwd</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('dict://127.0.0.1:6379/info')"><i class="fa fa-database"></i> 协议探测: Redis端口</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 资源请求响应回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 尚未提交抓取请求，请在上方输入 URL 后点击提交
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($ssrf_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发 SSRF 服务端请求伪造！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功诱使服务器访问了内部回环资源或敏感系统文件。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{SSRF_INTERNAL_PIVOT_ACQUIRED}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> SSRF 核心攻击场景</h4>
                                <div class="cyber-principle-box">
                                    <p><b>1. 探测内部网络：</b> 访问仅内网监听的 Web 接口与管理控制台；</p>
                                    <p><b>2. 伪协议利用：</b> 使用 <code>file://</code> 读取文件，使用 <code>dict://</code> 探测端口；</p>
                                    <p style="margin-bottom:0;"><b>3. 攻击内网组件：</b> 利用 <code>gopher://</code> 协议构造 TCP 流向 Redis、FastCGI 写入 WebShell。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用实战 Payload</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">file:///etc/passwd</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('file:///etc/passwd')">复制</button>
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">http://127.0.0.1:8765/vul/ssrf/ssrf_info.php</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('http://127.0.0.1:8765/vul/ssrf/ssrf_info.php')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御方案 (白名单与禁止私有 IP)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 限制仅允许 HTTP/HTTPS 协议
curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
// ✅ 解析真实 IP 并拦截 127.0.0.1 / 10.0.0.0/8 / 172.16.0.0/12 / 192.168.0.0/16
// ✅ 禁止重定向跟踪 CURLOPT_FOLLOWLOCATION = 0
</pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) { document.getElementById('url_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
