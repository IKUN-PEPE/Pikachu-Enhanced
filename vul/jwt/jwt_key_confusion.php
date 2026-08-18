<?php
/**
 * Pikachu-Enhanced v2.0 - 关卡 04: JWT 算法混淆 (RS256 退化为 HS256 攻击)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[217] = 'active';

$flag_msg = '';

// Check Flag submission
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{JWT_Alg_C0nfus10n_RS256_T0_HS256_Ex1t}') {
        $_SESSION['jwt_flags']['stage4'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 JWT 算法与密钥类型混淆 (RS-to-HS) 核心原理！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请修改算法为 HS256 并使用公开的 RSA Public Key PEM 作为对称密钥重签 role=admin 提交！</div>";
    }
}

// Server's Public RSA Key (Publicly accessible in PEM format)
$server_public_key_pem = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAz8qO8F3l1t2n4m5k6p7q\n8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6t7u8v9w\n0x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6n7o8p9q0r1s2t3u4v5w6x7y8z9a0b1c\n2d3e4f5g6h7i8j9k0l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6b7c8d9e0f1g2h3i\n4j5k6l7m8n9o0p1q2r3s4t5u6v7w8x9y0z1a2b3c4d5e6f7g8h9i0j1k2l3m4n5o\n6p7q8r9s0t1u2v3w4x5y6z==\n-----END PUBLIC KEY-----";

// Default RS256 token
$default_rs256_header = json_encode(array("alg" => "RS256", "typ" => "JWT"));
$default_rs256_payload = json_encode(array("user" => "guest_member", "role" => "user", "iat" => 1700000000));
$rs_h_enc = jwt_base64url_encode($default_rs256_header);
$rs_p_enc = jwt_base64url_encode($default_rs256_payload);
$rs_fake_sig = "RSA2048_DIGITAL_SIGNATURE_BY_AUTH_PRIVATE_KEY_SECURE";
$default_token = $rs_h_enc . '.' . $rs_p_enc . '.' . $rs_fake_sig;

$result_box = '';

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
        
        $alg = strtoupper($header['alg'] ?? '');
        
        if ($alg === 'HS256') {
            // 【核心漏洞机理】：验签网关由于代码缺陷，根据 Header 中的 HS256 调用了 HMAC 验签函数，
            // 误将原本用于 RSA 验签的公钥 PEM 文本作为了 HMAC 对称密钥！
            $calc_sig = jwt_base64url_encode(hash_hmac('sha256', $sig_input, $server_public_key_pem, true));
            
            if (hash_equals($calc_sig, $sig_provided)) {
                $role = strtolower($payload['role'] ?? 'user');
                $user = $payload['user'] ?? ($payload['username'] ?? 'unknown');
                
                if ($role === 'admin' || $role === 'root' || $role === 'superadmin') {
                    $result_box = "<div class='alert alert-success'>
                        <h4 style='margin-top:0; font-weight:700;'><i class='fa fa-unlock-alt'></i> 🚀 攻击成功！成功利用算法混淆 (RS256 -&gt; HS256) 伪造管理员凭证！</h4>
                        <p><b>漏洞利用链：</b>攻击者无需持有服务端私钥，仅使用公开的 RSA Public Key PEM 文本作为对称密钥，即可通过 HMAC-SHA256 签发 <code>role=admin</code> 的任意凭证！</p>
                        <hr style='border-color:rgba(16,185,129,0.3);'>
                        <p><b>终极管理员权限凭条 Flag：</b> <span style='font-family:monospace; font-size:15px; font-weight:bold; color:#f59e0b;'>flag{JWT_Alg_C0nfus10n_RS256_T0_HS256_Ex1t}</span></p>
                    </div>";
                } else {
                    $result_box = "<div class='alert alert-info'>
                        <i class='fa fa-check'></i> <b>算法混淆验签通过！</b>但当前角色为 <code>{$user} (role: {$role})</code>。请在 payload 中将 <code>role</code> 修改为 <code>admin</code> 并使用 RSA 公钥字符串重新计算 HMAC 签名！
                    </div>";
                }
            } else {
                $result_box = "<div class='alert alert-danger'>
                    <i class='fa fa-times-circle'></i> <b>HMAC 验签错误：</b>使用 HS256 算法时，必须以<b>完整的 RSA 公钥 PEM 纯文本</b>（包含换行符与 BEGIN/END 标记）作为 HMAC-SHA256 密钥来计算签名！
                </div>";
            }
        } elseif ($alg === 'RS256') {
            if ($sig_provided === $rs_fake_sig) {
                $result_box = "<div class='alert alert-info'>
                    <i class='fa fa-user'></i> <b>合法 RS256 普通访客凭据：</b>由于攻击者没有服务端的 RSA 私钥（Private Key），无法直接以 RS256 签发管理员身份。请尝试利用算法降级/混淆漏洞，将 Header 改为 <code>HS256</code> 并用公钥重签！
                </div>";
            } else {
                $result_box = "<div class='alert alert-danger'>
                    <i class='fa fa-times-circle'></i> <b>RSA 签名校验失败：</b>在 RS256 模式下，直接篡改 payload 将无法通过非对称公钥校验（私钥仅存放在认证服务器中）。
                </div>";
            }
        } else {
            $result_box = "<div class='alert alert-warning'><b>不支持的算法：</b>系统仅支持 RS256 或退化为 HS256。</div>";
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
.key-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 8px;
    padding: 12px;
    font-family: monospace;
    font-size: 11px;
    color: #a5b4fc;
    line-height: 1.4;
    max-height: 130px;
    overflow-y: auto;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">Stage 04: JWT 算法混淆 (RS256-&gt;HS256)</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="将 Header 中的 alg 改为 HS256，Payload 中的 role 改为 admin，并将下方公开的完整 RSA Public Key 文本作为对称加密密钥计算 HMAC 签名提交！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-random" style="color:#818cf8;"></i> Stage 04: JWT 算法与密钥混淆 (Key / Algorithm Confusion)
                    <span class="label label-info" style="border-radius:12px; font-size:11px; padding:3px 10px;">250 PTS [终章]</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞机理：</b>企业级系统通常使用 <b>非对称加密 (RS256)</b>，由授权服务持有私钥签发，微服务网关持有公钥验签。然而，如果后端验签函数 <code>jwt.verify(token, key)</code> <b>盲目信任了 Token Header 中的 alg 声明</b>，当攻击者将算法篡改为 <code>HS256 (对称加密 HMAC)</code> 时，网关就会将传入的 <b>RSA 公钥文本</b> 错误地当做对称密钥传入 HMAC 函数！由于 RSA 公钥是公开的，攻击者可以直接用公钥自签超级管理员凭据！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: Public Key Info & Normal Session -->
                <div class="col-md-5">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-key" style="color:var(--primary);"></i> 公开的 RSA Public Key 证书
                        </h4>

                        <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:8px;">
                            微服务网关对外暴露的 RSA-2048 公钥证书（用于 RS256 签名合法性校验）：
                        </p>
                        <div class="key-box" id="rsa_pubkey_display"><?php echo htmlspecialchars($server_public_key_pem); ?></div>

                        <hr style="border-color:var(--border-subtle);">

                        <h5 style="font-weight:700; color:var(--text-primary);">原始合法普通用户凭据 (RS256)：</h5>
                        <div style="background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:6px; padding:10px; font-family:monospace; font-size:11px; word-break:break-all; color:var(--text-primary);">
                            <?php echo htmlspecialchars($default_token); ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Exploit Tool & Verification -->
                <div class="col-md-7">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-magic" style="color:var(--primary);"></i> 算法混淆 Exploit 载荷在线构造器
                        </h4>

                        <div style="margin-bottom:12px;">
                            <button type="button" class="btn btn-warning btn-xs" onclick="generateKeyConfusionExploit()" style="border-radius:4px; font-weight:600;">
                                <i class="fa fa-bolt"></i> 一键生成 RS256 &rarr; HS256 管理员 Exploit 载荷
                            </button>
                        </div>

                        <form method="POST">
                            <div class="form-group" style="margin-bottom:12px;">
                                <label style="font-size:12.5px; font-weight:600;">提交至鉴权网关的 JWT Bearer Token：</label>
                                <textarea name="jwt_token" id="exploit_token_area" rows="4" class="form-control" style="font-family:monospace; font-size:11.5px; word-break:break-all; border-radius:6px;" required><?php echo isset($_POST['jwt_token']) ? htmlspecialchars($_POST['jwt_token']) : $default_token; ?></textarea>
                            </div>
                            <button type="submit" name="jwt_token_submit" class="btn btn-primary btn-sm" style="border-radius:6px; font-weight:600;">
                                <i class="fa fa-send"></i> 提交 Token 鉴权
                            </button>
                        </form>

                        <div style="margin-top:16px;">
                            <?php if (!empty($result_box)) { echo $result_box; } else { ?>
                                <div class="alert alert-info" style="border-radius:8px; font-size:12.5px; margin:0;">
                                    <i class="fa fa-info-circle"></i> 点击上方的一键生成 Exploit 按钮，利用公开的公钥文本生成 HS256 对称签名管理员 Token 并提交验证！
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
                <a href="jwt_weak_secret.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 上一关：Stage 03 (JWT 弱密钥爆破)
                </a>
                <a href="jwt.php" class="btn btn-success" style="border-radius:8px; font-weight:700;">
                    <i class="fa fa-trophy"></i> 通关完毕：返回现代认证大厅
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function b64url_encode(str) {
    var b64 = window.btoa(unescape(encodeURIComponent(str)));
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function generateKeyConfusionExploit() {
    <?php
    // Pre-calculate exact server matching signature for demonstration
    $exp_h = jwt_base64url_encode(json_encode(array("alg" => "HS256", "typ" => "JWT")));
    $exp_p = jwt_base64url_encode(json_encode(array("user" => "admin_master", "role" => "admin", "iat" => 1700000000)));
    $exp_s = jwt_base64url_encode(hash_hmac('sha256', $exp_h . '.' . $exp_p, $server_public_key_pem, true));
    $exploit_token = $exp_h . '.' . $exp_p . '.' . $exp_s;
    ?>
    document.getElementById('exploit_token_area').value = "<?php echo $exploit_token; ?>";
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
