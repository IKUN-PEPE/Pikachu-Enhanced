<?php
/**
 * Pikachu-Enhanced v2.0 - 关卡 03: JWT 弱密钥离线字典爆破 (HMAC-SHA256 Weak Secret Cracking)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 400, '');
$ACTIVE[157] = 'active open';
$ACTIVE[316] = 'active';

$flag_msg = '';

// Check Flag submission
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{JWT_HMAC_W34k_S3cr3t_Cr4ck3d_2026}') {
        $_SESSION['jwt_flags']['stage3'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 JWT HMAC 对称弱密钥离线爆破与重签利用全流程！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请爆破出服务端的 HMAC Secret Key (123456)，重签 role=admin 身份提交获取 Flag！</div>";
    }
}

// Fixed weak secret for this stage
$STAGE_SECRET = "123456";

// Generate initial valid guest token
$guest_header = json_encode(array("alg" => "HS256", "typ" => "JWT"));
$guest_payload = json_encode(array("user" => "pikachu", "role" => "guest", "iat" => 1700000000));
$h_enc = jwt_base64url_encode($guest_header);
$p_enc = jwt_base64url_encode($guest_payload);
$sig_calc = jwt_base64url_encode(hash_hmac('sha256', $h_enc . '.' . $p_enc, $STAGE_SECRET, true));
$default_guest_token = $h_enc . '.' . $p_enc . '.' . $sig_calc;

$result_box = '';
$is_admin = false;
$parsed_user = 'guest';
$parsed_role = 'guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jwt_token_submit'])) {
    $token_in = trim($_POST['jwt_token'] ?? '');
    $parts = explode('.', $token_in);
    
    if (count($parts) === 3) {
        $h_raw = jwt_base64url_decode($parts[0]);
        $p_raw = jwt_base64url_decode($parts[1]);
        $header = json_decode($h_raw, true);
        $payload = json_decode($p_raw, true);
        $sig_input = $parts[0] . '.' . $parts[1];
        $sig_provided = $parts[2];
        
        if (is_array($header) && is_array($payload)) {
            // Verify with stage secret
            $expected_sig = jwt_base64url_encode(hash_hmac('sha256', $sig_input, $STAGE_SECRET, true));
            
            if (hash_equals($expected_sig, $sig_provided)) {
                $parsed_user = $payload['user'] ?? ($payload['username'] ?? 'unknown');
                $parsed_role = $payload['role'] ?? 'guest';
                
                if (strtolower($parsed_role) === 'admin' || strtolower($parsed_role) === 'superadmin' || strtolower($parsed_role) === 'root') {
                    $is_admin = true;
                    $result_box = "<div class='alert alert-success'>
                        <h4 style='margin-top:0; font-weight:700;'><i class='fa fa-check-circle'></i> 🚀 恭喜！JWT 弱密钥爆破与管理员身份伪造成功！</h4>
                        <p>服务端检测到有效的 HMAC-SHA256 签名（验签 Secret: <code>{$STAGE_SECRET}</code>），并且身份为 <b>{$parsed_role}</b>！</p>
                        <hr style='border-color:rgba(16,185,129,0.3);'>
                        <p><b>超级管理员控制台授权 Flag：</b> <span style='font-family:monospace; font-size:15px; font-weight:bold; color:#f59e0b;'>flag{JWT_HMAC_W34k_S3cr3t_Cr4ck3d_2026}</span></p>
                    </div>";
                } else {
                    $result_box = "<div class='alert alert-info'>
                        <i class='fa fa-info-circle'></i> <b>签名校验通过</b>，但当前身份为 <code>{$parsed_user} (role: {$parsed_role})</code>，权限不足！请将 payload 中的 <code>role</code> 修改为 <code>admin</code> 并使用密钥 <code>{$STAGE_SECRET}</code> 重新生成签名提交。
                    </div>";
                }
            } else {
                $result_box = "<div class='alert alert-danger'>
                    <i class='fa fa-times-circle'></i> <b>签名校验失败：</b>提交的 Token 签名与服务端 Secret 密钥不匹配。请使用字典爆破获取真实签名密钥！
                </div>";
            }
        } else {
            $result_box = "<div class='alert alert-warning'><b>格式错误：</b>Header 或 Payload 无法解析为有效 JSON。</div>";
        }
    } else {
        $result_box = "<div class='alert alert-warning'><b>格式错误：</b>标准 JWT 必须包含三段 (Header.Payload.Signature)。</div>";
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.stage-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.stage-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.crack-terminal {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 10px;
    padding: 16px;
    color: #38bdf8;
    font-family: monospace;
    font-size: 12.5px;
    min-height: 160px;
    max-height: 220px;
    overflow-y: auto;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">Stage 03: JWT 弱密钥离线爆破</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="使用下方的在线爆破模拟器（或本地 Hashcat: hashcat -m 16500）跑出密钥为 123456，再使用该密钥对 role=admin 的 payload 计算 HMAC-SHA256 签名！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-key" style="color:#818cf8;"></i> Stage 03: JWT HMAC 对称弱密钥离线爆破
                    <span class="label label-info" style="border-radius:12px; font-size:11px; padding:3px 10px;">200 PTS</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞机理：</b>在基于 <code>HS256 (HMAC-SHA256)</code> 对称加密的架构中，签发与校验 Token 共用同一个机密字符串（Secret Key）。若系统使用了诸如 <code>123456</code>、<code>secret</code> 等常见弱口令，攻击者获取任意低权合法 Token 后，可在离线环境下使用 GPU（如 Hashcat）以每秒数亿次的速度爆破出密钥，进而自行计算签名签发管理员 Token！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: In-Browser Hashcat Cracker & Wordlist -->
                <div class="col-md-6">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-tachometer" style="color:var(--primary);"></i> 在线离线字典爆破模拟器 (Hashcat Simulator)
                        </h4>

                        <div style="font-size:12.5px; color:var(--text-secondary); margin-bottom:10px;">
                            <b>目标待爆破 Token (持有访客会话)：</b>
                        </div>
                        <div style="background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:6px; padding:10px; font-family:monospace; font-size:11.5px; word-break:break-all; margin-bottom:14px; color:var(--text-primary);">
                            <span id="target_token_text"><?php echo $default_guest_token; ?></span>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:12px; color:var(--text-muted);">字典列表: <code>rockyou_mini.txt</code></span>
                            <button type="button" class="btn btn-danger btn-xs" onclick="runInBrowserCrack()" style="border-radius:4px; font-weight:600;">
                                <i class="fa fa-play"></i> ⚡ 启动极速字典碰撞
                            </button>
                        </div>

                        <div class="crack-terminal" id="crack_log_terminal">
                            [INFO] Hashcat v6.2.6 Engine Initialized (Mode: 16500 - JWT HMAC-SHA256)<br>
                            [INFO] Target Hash Loaded: <?php echo substr($default_guest_token, 0, 30); ?>...<br>
                            [INFO] Ready. Click '启动极速字典碰撞' to start brute-forcing.
                        </div>

                        <div style="margin-top:14px; background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:8px; padding:12px; font-size:12px;">
                            <b>💻 真实终端爆破实战命令：</b><br>
                            <code>hashcat -m 16500 target_jwt.txt /usr/share/wordlists/rockyou.txt</code><br>
                            <code>jwt_tool -C -d rockyou.txt target_jwt_token_here</code>
                        </div>

                        <!-- Visual SVG Architecture Flowchart -->
                        <div style="background:#020617; border:1px solid #1e293b; border-radius:10px; padding:12px; margin-top:14px; text-align:center;">
                            <svg viewBox="0 0 540 160" style="width:100%; height:auto; display:inline-block;" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <marker id="ws-arr" markerWidth="7" markerHeight="5" refX="6" refY="2.5" orient="auto">
                                        <polygon points="0 0, 7 2.5, 0 5" fill="#38bdf8"/>
                                    </marker>
                                </defs>
                                <rect x="10" y="15" width="150" height="55" rx="6" fill="#1e293b" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="85" y="36" fill="#60a5fa" font-size="11" font-weight="bold" text-anchor="middle" font-family="sans-serif">1. 抓取低权 Token</text>
                                <text x="85" y="54" fill="#94a3b8" font-size="9.5" text-anchor="middle" font-family="monospace">role: guest, HS256</text>

                                <path d="M 160 42 L 200 42" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#ws-arr)"/>

                                <rect x="210" y="15" width="160" height="55" rx="6" fill="#1e293b" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="290" y="36" fill="#f87171" font-size="11" font-weight="bold" text-anchor="middle" font-family="sans-serif">2. 离线 GPU 字典碰撞</text>
                                <text x="290" y="54" fill="#fca5a5" font-size="9.5" text-anchor="middle" font-family="monospace">hashcat -m 16500</text>

                                <path d="M 370 42 L 410 42" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#ws-arr)"/>

                                <rect x="420" y="15" width="110" height="55" rx="6" fill="#022c22" stroke="#10b981" stroke-width="1.5"/>
                                <text x="475" y="36" fill="#34d399" font-size="11" font-weight="bold" text-anchor="middle" font-family="sans-serif">3. 还原 Secret</text>
                                <text x="475" y="54" fill="#fbbf24" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">"123456"</text>

                                <!-- Down and Left -->
                                <path d="M 475 70 L 475 110 L 375 110" stroke="#10b981" stroke-width="1.5" marker-end="url(#ws-arr)"/>

                                <rect x="10" y="90" width="355" height="55" rx="6" fill="#020617" stroke="#10b981" stroke-width="1.5"/>
                                <text x="187" y="112" fill="#34d399" font-size="11.5" font-weight="bold" text-anchor="middle" font-family="sans-serif">4. 重签 role=admin 提交鉴权网关 &rarr; 获得 Flag</text>
                                <text x="187" y="132" fill="#fbbf24" font-size="10" font-weight="bold" text-anchor="middle" font-family="monospace">flag{JWT_HMAC_W34k_S3cr3t_Cr4ck3d_2026}</text>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Right: HMAC Re-sign Tool & Gateway Verification -->
                <div class="col-md-6">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-shield" style="color:var(--primary);"></i> HMAC 重签工具与鉴权网关验证
                        </h4>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; font-weight:600;">已爆破出的 Secret Key:</label>
                                <input type="text" id="cracked_secret_input" class="form-control input-sm" placeholder="输入爆破出的密钥" value="" style="font-family:monospace; border-radius:4px;">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; font-weight:600;">目标角色 Payload:</label>
                                <input type="text" id="admin_role_input" class="form-control input-sm" value="admin" style="font-family:monospace; border-radius:4px;">
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <button type="button" class="btn btn-warning btn-xs" onclick="signAdminToken()" style="border-radius:4px; font-weight:600;">
                                <i class="fa fa-pencil"></i> 使用爆破密钥重新计算 HMAC-SHA256 签名
                            </button>
                        </div>

                        <form method="POST">
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-size:12.5px; font-weight:600;">提交至鉴权网关的 JWT Bearer Token：</label>
                                <textarea name="jwt_token" id="submit_token_area" rows="4" class="form-control" style="font-family:monospace; font-size:11.5px; word-break:break-all; border-radius:6px;" required><?php echo isset($_POST['jwt_token']) ? htmlspecialchars($_POST['jwt_token']) : $default_guest_token; ?></textarea>
                            </div>
                            <button type="submit" name="jwt_token_submit" class="btn btn-primary btn-sm" style="border-radius:6px; font-weight:600;">
                                <i class="fa fa-paper-plane"></i> 提交 Token 鉴权
                            </button>
                        </form>

                        <div style="margin-top:16px;">
                            <?php if (!empty($result_box)) { echo $result_box; } else { ?>
                                <div class="alert alert-info" style="border-radius:8px; font-size:12.5px; margin:0;">
                                    <i class="fa fa-info-circle"></i> 当前鉴权网关就绪。请先在左侧碰撞出服务端的签名密钥，再在上方重签管理员身份提交！
                                </div>
                            <?php } ?>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Form -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" required>
                            <button type="submit" name="check_flag" class="btn btn-success" style="border-radius:6px; font-weight:700; white-space:nowrap;">
                                <i class="fa fa-check"></i> 提交 Flag
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="jwt_none.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 上一关：Stage 02 (JWT None 算法)
                </a>
                <a href="jwt_key_confusion.php" class="btn btn-primary" style="border-radius:8px;">
                    下一关：Stage 04 (JWT 算法混淆 RS-to-HS) <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/crypto-js.min.js"></script>
<script>
function b64url_encode_raw(str) {
    var b64 = window.btoa(unescape(encodeURIComponent(str)));
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

// Fallback HMAC calculation using simple js hash or CryptoJS if present
function calculateHMAC(msg, key) {
    if (window.CryptoJS && CryptoJS.HmacSHA256) {
        var hash = CryptoJS.HmacSHA256(msg, key);
        var b64 = CryptoJS.enc.Base64.stringify(hash);
        return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }
    // Hardcoded simulation for common keys
    if (key === "123456") {
        return "<?php echo $sig_calc; ?>";
    }
    return "simulated_hmac_sig_" + key;
}

function runInBrowserCrack() {
    var term = document.getElementById('crack_log_terminal');
    term.innerHTML = "[*] Loading dictionary: 10,000 words...<br>[*] Starting brute-force dictionary attack on target token...<br>";
    
    var dict = ["password", "12345678", "admin", "secret", "root", "qwerty", "pikachu", "123456"];
    var idx = 0;
    
    var timer = setInterval(function() {
        if (idx < dict.length) {
            var word = dict[idx];
            term.innerHTML += "[TRY] Testing secret candidate: <code>" + word + "</code> ... [MISMATCH]<br>";
            term.scrollTop = term.scrollHeight;
            if (word === "123456") {
                clearInterval(timer);
                term.innerHTML += "<br><span style='color:#10b981; font-weight:bold;'>[+] CRACKED SUCCESSFUL! KEY FOUND: &gt;&gt; 123456 &lt;&lt;</span><br>";
                term.innerHTML += "[+] Speed: 4,820,000 H/s | Time elapsed: 0.12s<br>";
                term.scrollTop = term.scrollHeight;
                document.getElementById('cracked_secret_input').value = "123456";
            }
            idx++;
        } else {
            clearInterval(timer);
        }
    }, 150);
}

function signAdminToken() {
    var secret = document.getElementById('cracked_secret_input').value.trim();
    if (!secret) {
        alert("请先填入爆破出的 Secret 密钥（如 123456）！");
        return;
    }
    var role = document.getElementById('admin_role_input').value.trim() || 'admin';
    var header = { "alg": "HS256", "typ": "JWT" };
    var payload = { "user": "admin", "role": role, "iat": 1700000000 };
    
    var hEnc = b64url_encode_raw(JSON.stringify(header));
    var pEnc = b64url_encode_raw(JSON.stringify(payload));
    
    // Exact signature for 123456
    var sig = "P98qjN3z5_33hTf4dM37t8G0m6T_J2b9K5l7N8p0R1s";
    if (secret === "123456") {
        // Pre-computed PHP matching HMAC for 123456
        <?php
        $adm_h = jwt_base64url_encode(json_encode(array("alg" => "HS256", "typ" => "JWT")));
        $adm_p = jwt_base64url_encode(json_encode(array("user" => "admin", "role" => "admin", "iat" => 1700000000)));
        $adm_s = jwt_base64url_encode(hash_hmac('sha256', $adm_h . '.' . $adm_p, '123456', true));
        ?>
        document.getElementById('submit_token_area').value = "<?php echo $adm_h . '.' . $adm_p . '.' . $adm_s; ?>";
    } else {
        document.getElementById('submit_token_area').value = hEnc + '.' + pEnc + '.' + calculateHMAC(hEnc + '.' + pEnc, secret);
    }
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
