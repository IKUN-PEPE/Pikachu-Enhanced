<?php
/**
 * Pikachu-Enhanced v2.0 - 关卡 02: JWT None 算法免签绕过 (None Algorithm Auth Bypass)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[159] = 'active';

$flag_msg = '';

// Check Flag submission
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}') {
        $_SESSION['jwt_flags']['stage2'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 CVE-2015-9235 JWT None 算法免签绕过精髓！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请修改 Token Header 中 alg 为 none 并将身份提升为 vip 以提取机密文件！</div>";
    }
}

// Insecure JWT Parser (Simulating None algorithm vulnerability)
function parse_jwt_insecure_none($jwt_str) {
    $parts = explode('.', $jwt_str);
    if (count($parts) < 2) return null;
    
    $header = json_decode(jwt_base64url_decode($parts[0]), true);
    $payload = json_decode(jwt_base64url_decode($parts[1]), true);
    $signature = isset($parts[2]) ? $parts[2] : '';

    if (!is_array($header) || !is_array($payload)) {
        return null;
    }

    // 【核心漏洞点】：若算法为 none/None/NONE，直接跳过签名验证并放行！
    if (isset($header['alg']) && strtolower(trim($header['alg'])) === 'none') {
        return array('header' => $header, 'payload' => $payload, 'valid' => true, 'is_none' => true);
    }

    // 常规校验（模拟有效验签）
    $expected_sig = jwt_base64url_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], 'pikachu-jwt-secret', true));
    if ($signature === $expected_sig || $signature === 'valid_signature_for_guest') {
        return array('header' => $header, 'payload' => $payload, 'valid' => true, 'is_none' => false);
    }

    return array('header' => $header, 'payload' => $payload, 'valid' => false, 'is_none' => false);
}

// Handle Custom Token POST or Cookie
if (isset($_POST['apply_token'])) {
    $token_in = trim($_POST['token_input'] ?? '');
    if ($token_in !== '') {
        setcookie('auth_token', $token_in, time() + 3600, '/');
        $_COOKIE['auth_token'] = $token_in;
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $default_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0IiwiaWF0IjoxNjIwMDAwMDAwfQ.valid_signature_for_guest";
    setcookie('auth_token', $default_token, time() + 3600, '/');
    $_COOKIE['auth_token'] = $default_token;
    header('location:jwt_none.php');
    exit();
}

$default_guest_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0IiwiaWF0IjoxNjIwMDAwMDAwfQ.valid_signature_for_guest";
$current_token = $_COOKIE['auth_token'] ?? $default_guest_token;
$parse_result = parse_jwt_insecure_none($current_token);

$user_role = 'guest';
$user_name = 'guest';
$is_vip = false;
$is_valid = false;

if ($parse_result && $parse_result['valid']) {
    $is_valid = true;
    $user_role = $parse_result['payload']['role'] ?? 'guest';
    $user_name = $parse_result['payload']['username'] ?? 'guest';
    if ($user_role === 'vip' || $user_role === 'admin' || $user_role === 'root') {
        $is_vip = true;
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
.token-box {
    background: var(--bg-app);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 12px;
    font-family: monospace;
    font-size: 12px;
    word-break: break-all;
    color: var(--text-primary);
}
.vault-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 24px;
    color: #38bdf8;
    font-family: monospace;
    margin-top: 15px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">Stage 02: JWT None 算法免签绕过</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="将 Header 中的 alg 修改为 'none'，Payload 中的 role 修改为 'vip'，并将第三段签名删除（保留 header.payload. 结尾带点或不带点格式），提交即可免密提权！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-shield" style="color:#818cf8;"></i> Stage 02: JWT None 算法免签绕过 (CVE-2015-9235)
                    <span class="label label-info" style="border-radius:12px; font-size:11px; padding:3px 10px;">150 PTS</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞机理：</b>RFC 7519 规范允许使用 <code>none</code> 算法来表示未经过加密保护的明文 Token。然而许多有缺陷的 JWT 验证库在处理客户端请求时，<b>直接采纳了 Token Header 中的 alg 声明</b>。当攻击者传入 <code>{"alg":"none"}</code> 时，服务端便跳过了第三部分 Signature 的数学验签逻辑，导致攻击者可零门槛伪造超级管理员凭证！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: VIP Vault Portal -->
                <div class="col-md-5">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-university" style="color:var(--primary);"></i> 绝密 VIP 云端文档金库
                        </h4>

                        <div style="margin-bottom:14px;">
                            <span style="font-size:13px; color:var(--text-secondary);">当前认证状态：</span>
                            <?php if ($is_valid) { ?>
                                <span class="label <?php echo $is_vip ? 'label-warning' : 'label-default'; ?>" style="border-radius:6px; font-size:12px;">
                                    <?php echo htmlspecialchars($user_name); ?> (Role: <?php echo htmlspecialchars($user_role); ?>)
                                </span>
                            <?php } else { ?>
                                <span class="label label-danger" style="border-radius:6px; font-size:12px;">Token 签名校验失败</span>
                            <?php } ?>
                        </div>

                        <div class="token-box" style="margin-bottom:15px;">
                            <div style="font-weight:700; color:var(--text-primary); margin-bottom:4px;">当前载入的 Cookie (auth_token)：</div>
                            <?php echo htmlspecialchars($current_token); ?>
                        </div>

                        <?php if ($is_vip) { ?>
                            <div class="vault-box">
                                <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:10px;">
                                    <i class="fa fa-unlock"></i> 🎉 VIP 金库授权通过 (Access Granted)
                                </div>
                                <div style="font-size:13px; color:#e2e8f0; line-height:1.7;">
                                    [+] 欢迎贵宾用户: <b><?php echo htmlspecialchars($user_name); ?></b><br>
                                    [+] 验签机制: None Algorithm Insecure Bypass Activated<br>
                                    [+] 提取核心机密 Flag: <br>
                                    <span style="color:#f59e0b; font-weight:bold; font-size:14px; background:rgba(245,158,11,0.1); padding:4px 8px; border-radius:4px; display:inline-block; margin-top:4px;">flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}</span>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">
                                <i class="fa fa-lock" style="font-size:24px; margin-bottom:8px; display:block; color:var(--text-muted);"></i>
                                抱歉！绝密文件库仅允许 <code>role: vip</code> 或 <code>role: admin</code> 下载。<br>
                                当前身份 <code><?php echo htmlspecialchars($user_role); ?></code> 权限不足！
                            </div>
                        <?php } ?>

                        <div style="margin-top:15px; text-align:right;">
                            <a href="jwt_none.php?reset=1" class="btn btn-default btn-xs" style="border-radius:4px;">
                                <i class="fa fa-refresh"></i> 重置恢复默认访客 Token
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Exploit Generator & Submission Tool -->
                <div class="col-md-7">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-terminal" style="color:var(--primary);"></i> None 算法 Exploit 载荷在线生成器
                        </h4>

                        <div class="row">
                            <div class="col-sm-6" style="margin-bottom:12px;">
                                <label style="font-size:12px; font-weight:700; color:#ef4444;">1. Header JSON (设置 alg 为 none):</label>
                                <textarea id="none_header_json" style="width:100%; height:90px; font-family:monospace; font-size:12px; background:var(--bg-app); border:1px solid rgba(239,68,68,0.3); border-radius:6px; padding:8px; color:#ef4444;">{
  "alg": "none",
  "typ": "JWT"
}</textarea>
                            </div>
                            <div class="col-sm-6" style="margin-bottom:12px;">
                                <label style="font-size:12px; font-weight:700; color:#a855f7;">2. Payload JSON (设置 role 为 vip):</label>
                                <textarea id="none_payload_json" style="width:100%; height:90px; font-family:monospace; font-size:12px; background:var(--bg-app); border:1px solid rgba(168,85,247,0.3); border-radius:6px; padding:8px; color:#a855f7;">{
  "username": "hacker",
  "role": "vip",
  "iat": <?php echo time(); ?>
}</textarea>
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="font-size:12.5px; font-weight:700; color:var(--text-primary);">3. 组装生成的 None 算法 Token (保留结尾的点号 <code>.</code>，去掉第三段签名)：</label>
                            <form method="POST">
                                <textarea id="none_token_output" name="token_input" style="width:100%; height:75px; font-family:monospace; font-size:12px; background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:6px; padding:8px; color:#38bdf8; word-break:break-all;" required></textarea>
                                
                                <div style="margin-top:10px; display:flex; gap:10px; align-items:center;">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="craftNoneToken()" style="border-radius:6px; font-weight:600;">
                                        <i class="fa fa-magic"></i> 重新编码组装
                                    </button>
                                    <button type="submit" name="apply_token" class="btn btn-danger btn-sm" style="border-radius:6px; font-weight:600;">
                                        <i class="fa fa-send"></i> 写入 Cookie 并发送验证
                                    </button>
                                </div>
                            </form>
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
                <a href="jwt_login.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 上一关：Stage 01 (JWT 状态篡改)
                </a>
                <a href="jwt_weak_secret.php" class="btn btn-primary" style="border-radius:8px;">
                    下一关：Stage 03 (JWT 弱密钥爆破) <i class="fa fa-arrow-right"></i>
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

function craftNoneToken() {
    var hStr = document.getElementById('none_header_json').value.trim();
    var pStr = document.getElementById('none_payload_json').value.trim();
    try {
        var hObj = JSON.parse(hStr);
        var pObj = JSON.parse(pStr);
        var hEnc = b64url_encode(JSON.stringify(hObj));
        var pEnc = b64url_encode(JSON.stringify(pObj));
        // RFC 7519: Empty signature segment with trailing dot
        document.getElementById('none_token_output').value = hEnc + '.' + pEnc + '.';
    } catch(e) {
        alert("JSON 语法解析失败，请检查逗号与双引号！");
    }
}

window.addEventListener('DOMContentLoaded', function() {
    craftNoneToken();
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
