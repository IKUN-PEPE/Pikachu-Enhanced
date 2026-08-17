<?php
/**
 * Pikachu-Enhanced v2.0 - SSRF 进阶演练: DNS 重绑定 (DNS Rebinding) 与 IP 变形绕过实战
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[219] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';

$log = "";
$status_type = "";
$user_url = $_POST['url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($user_url)) {
    $url = trim($user_url);
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';
    $scheme = $parsed['scheme'] ?? '';

    if (!in_array(strtolower($scheme), ['http', 'https'])) {
        $status_type = "danger";
        $log = "❌ [WAF 拦截] 协议不合法！仅允许 HTTP/HTTPS 协议。";
    } elseif (empty($host)) {
        $status_type = "danger";
        $log = "❌ [解析错误] 无法解析有效 Host 主机名。";
    } else {
        // 简陋的黑名单检查 (仅检测字面量 '127.0.0.1' 和 'localhost')
        $naive_blacklist = ['127.0.0.1', 'localhost', '192.168.', '10.0.', '172.16.'];
        $blocked = false;
        foreach ($naive_blacklist as $bad) {
            if (stripos($host, $bad) !== false) {
                $blocked = true;
                break;
            }
        }

        if ($blocked) {
            $status_type = "warning";
            $log = "⛔ [防御触发] 目标主机 [{$host}] 命中黑名单关键字 (127.0.0.1 / localhost 等)，已拒绝连接！";
        } else {
            // 模拟判定是否成功绕过黑名单并触达本地敏感内网接口
            $is_local_bypass = false;
            $bypass_reason = "";

            // 1. 八进制 IP: 0177.0.0.1 或 017700000001
            if (preg_match('/^0177/i', $host)) {
                $is_local_bypass = true;
                $bypass_reason = "八进制 IP 表示法 (0177.0.0.1 解析为 127.0.0.1)";
            }
            // 2. 十六进制 IP: 0x7f000001 或 0x7f.1
            elseif (preg_match('/^0x7f/i', $host)) {
                $is_local_bypass = true;
                $bypass_reason = "十六进制 IP 表示法 (0x7f000001 解析为 127.0.0.1)";
            }
            // 3. 整数 IP: 2130706433
            elseif ($host === '2130706433') {
                $is_local_bypass = true;
                $bypass_reason = "十进制长整型 IP (2130706433 解析为 127.0.0.1)";
            }
            // 4. IPv6 本地回环: [::1] 或 [::] 或 0.0.0.0
            elseif ($host === '0.0.0.0' || $host === '0' || $host === '[::1]' || $host === '[::]') {
                $is_local_bypass = true;
                $bypass_reason = "0.0.0.0 / IPv6 [::1] 本地回环缺省路由";
            }
            // 5. DNS Rebinding / 特殊泛域名: *.nip.io / *.sslip.io / 127.0.0.1.nip.io
            elseif (str_contains($host, '127.0.0.1.nip.io') || str_contains($host, 'localtest.me') || str_contains($host, 'spoof.internal.com')) {
                $is_local_bypass = true;
                $bypass_reason = "DNS 重绑定 / 动态解析域名 (解析结果指向 127.0.0.1)";
            }
            // 6. @ 符号绕过: http://google.com@127.0.0.1 或 http://127.0.0.1#google.com
            elseif (str_contains($url, '@')) {
                $is_local_bypass = true;
                $bypass_reason = "URL 用户凭据 @ 符号解析歧义欺骗";
            }

            if ($is_local_bypass) {
                $status_type = "success";
                $log = "💥 [SSRF 绕过成功！]\n" .
                       "[+] 绕过手段: {$bypass_reason}\n" .
                       "[+] 目标请求成功绕过 WAF 黑名单并直连本地 127.0.0.1 管理服务！\n\n" .
                       "HTTP/1.1 200 OK\nServer: Internal-Admin-Console\n\n" .
                       "🎉 恭喜获得内网管理控制台数据:\n" .
                       "FLAG{SSRF_DNS_REBINDING_AND_IP_TRANSFORM_PWNED}\n" .
                       "System Config: DB_PASSWORD=SecretProdDatabasePass!2026";
            } else {
                $status_type = "info";
                $log = "ℹ️ [请求发送] 目标主机 [{$host}] 未命中已知本地绕过特征，已尝试正常连接。";
            }
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF</a></li>
                <li class="active">DNS 重绑定与 IP 变形绕过</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 5: SSRF 防护黑名单绕过 - DNS 重绑定与 IP 进制变形
                        <span class="cyber-badge-chip" style="border-color:#10b981; color:#34d399; background:rgba(16,185,129,0.15);">黑名单绕过 · DNS Rebinding · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        许多系统采用粗糙的关键字黑名单过滤 SSRF（如仅过滤 <code>127.0.0.1</code> 或 <code>localhost</code>）。安全研究员可以通过<b>八进制（0177.0.0.1）、十六进制（0x7f000001）、十进制整数（2130706433）、缺省路由（0.0.0.0）、IPv6（[::]）、URL @ 符号歧义</b>，或配置 <b>TTL=0 的 DNS 重绑定域名（DNS Rebinding）</b>让两次解析结果发生突变，从而轻松穿透黑名单防御！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Payload Chips -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-crosshairs" style="color:#10b981;"></i> SSRF 黑名单绕过演练测试台
                            </h4>

                            <form method="POST" action="ssrf_dns_rebinding.php">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">目标 URL (受简陋黑名单保护)：</label>
                                    <input type="text" id="target_url" name="url" class="form-control" value="<?php echo htmlspecialchars($user_url); ?>" placeholder="http://..." style="font-family:monospace;" required />
                                </div>

                                <div style="margin-bottom:18px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试经典绕过 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('http://0177.0.0.1/admin')">
                                            <i class="fa fa-magic" style="color:#f59e0b;"></i> <b>八进制绕过：</b> <code>http://0177.0.0.1/admin</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('http://0x7f000001/admin')">
                                            <i class="fa fa-magic" style="color:#06b6d4;"></i> <b>十六进制绕过：</b> <code>http://0x7f000001/admin</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('http://2130706433/admin')">
                                            <i class="fa fa-magic" style="color:#8b5cf6;"></i> <b>十进制整型绕过：</b> <code>http://2130706433/admin</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('http://0.0.0.0/admin')">
                                            <i class="fa fa-magic" style="color:#10b981;"></i> <b>缺省路由 0.0.0.0：</b> <code>http://0.0.0.0/admin</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setPayload('http://127.0.0.1.nip.io/admin')">
                                            <i class="fa fa-globe" style="color:#ec4899;"></i> <b>DNS Rebinding 域名：</b> <code>http://127.0.0.1.nip.io/admin</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-block" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none; padding:10px;">
                                    <i class="fa fa-play"></i> 发送请求测试黑名单绕过
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Response & Trace -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-shield" style="color:#06b6d4;"></i> 防御拦截与执行状态
                            </h4>

                            <?php if (!empty($log)): ?>
                                <pre style="background:#090d16; color:<?php echo $status_type === 'success' ? '#10b981' : ($status_type === 'warning' ? '#f59e0b' : '#ef4444'); ?>; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12.5px; line-height:1.6; max-height:300px; overflow-y:auto;"><?php echo htmlspecialchars($log); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-arrow-left" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    点击左侧各种 IP 变形方式测试黑名单拦截机制
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="ssrf_gopher_redis.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Gopher 协议打 Redis</a>
                    <a href="ssrf.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回 SSRF 演练大厅 <i class="fa fa-th-large"></i></a>
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
