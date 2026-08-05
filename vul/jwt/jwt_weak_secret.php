<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[200] = 'active';
$ACTIVE[200] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 简单实现 JWT 签名验证算法
function b64url_decode($data) {
    return json_decode(base64_decode(strtr($data, '-_', '+/')), true);
}
function b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$default_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9." . b64url_encode(json_encode(["user"=>"pikachu", "role"=>"guest"])) . "." . b64url_encode(hash_hmac('sha256', "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9." . b64url_encode(json_encode(["user"=>"pikachu", "role"=>"guest"])), "123456", true));

$result_box = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['jwt_token'] ?? '');
    $parts = explode('.', $token);
    
    if (count($parts) === 3) {
        $header = b64url_decode($parts[0]);
        $payload = b64url_decode($parts[1]);
        $sig_input = $parts[0] . "." . $parts[1];
        $sig_provided = $parts[2];
        
        // 测试常见弱密钥字典 (如 123456, secret, pikachu)
        $valid_secret = false;
        $matched_key = "";
        foreach (["123456", "secret", "pikachu", "admin123"] as $k) {
            $calc = b64url_encode(hash_hmac('sha256', $sig_input, $k, true));
            if (hash_equals($calc, $sig_provided)) {
                $valid_secret = true;
                $matched_key = $k;
                break;
            }
        }
        
        if ($valid_secret) {
            if (isset($payload['role']) && (strtolower($payload['role']) === 'admin' || strtolower($payload['role']) === 'superadmin')) {
                $result_box = "<div class='alert alert-success'>
                    <h4><i class='fa fa-check-circle'></i> 🚀 恭喜！JWT 弱密钥爆破与越权伪造成功！</h4>
                    <p>通过离线字典爆破，发现服务端用于 HMAC-SHA256 签名的密钥非常脆弱（Secret Key: <code>" . htmlspecialchars($matched_key) . "</code>）。重新使用该密钥对 payload <code>role=admin</code> 进行签名提交，服务端验证通过！</p>
                    <hr/>
                    <p><b>超级管理员控制台授权令牌：</b> <code>FLAG{JWT_WEAK_SECRET_HMAC_CRACKED_MASTER}</code></p>
                </div>";
            } else {
                $result_box = "<div class='alert alert-info'>
                    <h4><i class='fa fa-user'></i> 签名校验成功，但权限不足</h4>
                    <p>当前解析身份：用户 <code>" . htmlspecialchars($payload['user'] ?? 'unknown') . "</code>，角色：<code>" . htmlspecialchars($payload['role'] ?? 'guest') . "</code>。尝试爆破出服务端签名密钥后，将 payload 中的 <code>role</code> 改为 <code>admin</code> 并重新生成签名提交。</p>
                </div>";
            }
        } else {
            $result_box = "<div class='alert alert-danger'><b>签名校验失败：</b>提供的 JWT 签名不正确。提示：本关卡服务端的签名密钥为简单的纯数字弱口令（可用 hashcat 或 jwt_tool 爆破）。</div>";
        }
    } else {
        $result_box = "<div class='alert alert-warning'><b>格式错误：</b>标准的 JWT 应当由三部分组成（Header.Payload.Signature）。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt_weak_secret.php">现代认证与密码学安全</a></li>
                <li class="active">JWT 弱密钥爆破</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔑 JWT (JSON Web Token) HMAC 弱密钥离线爆破与身份伪造</h2>
                <p>在基于 JWT 的无状态身份认证体系中，最常用的签名算法是 <code>HS256 (HMAC with SHA-256)</code>，即基于对称加密密钥生成签名。这意味着<b>签发 Token 和验证 Token 使用的是同一个机密字符串（Secret Key）</b>。</p>
                <p>如果开发人员在配置系统时为了省事使用了诸如 <code>123456</code>、<code>secret</code>、<code>company2026</code> 等极易被猜解的弱口令作为 JWT Secret，攻击者在获取到任意一个合法的低权限 Token 后，即可使用离线爆破工具（如 <code>hashcat -m 16500</code>、<code>cjwt</code> 或 <code>jwt_tool</code>）在一秒内跑出密钥，进而伪造任意高权限管理会话！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-ticket"></i> 提交 JWT 凭证进行身份认证</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="jwt_token">输入 HTTP Bearer Token：</label>
                                <textarea class="form-control" name="jwt_token" id="jwt_token" rows="5" style="font-family: monospace; background:#f8f9fa; word-break:break-all;"><?php echo isset($_POST['jwt_token']) ? htmlspecialchars($_POST['jwt_token']) : $default_token; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-sign-in"></i> 提交 Token 登录系统</button>
                            <button type="button" class="btn btn-danger" onclick="fillAdminJWT()"><i class="fa fa-key"></i> 填入已爆破伪造的 Admin Token</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-shield"></i> 认证服务器验签响应</h4>
                        <div style="margin-top: 10px;">
                            <?php if (!empty($result_box)) { echo $result_box; } else { ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 你好！你目前持有的是普通访客的 Token。请尝试解密或爆破该 Token 的签名密钥，构造一个 <code>role: admin</code> 的凭证以进入高权控制台！
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillAdminJWT() {
    // 预先用密钥 123456 签名的 role=admin token
    var admin_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9." .
                      "eyJ1c2VyIjoiaGFja2VkX2FkbWluIiwicm9sZSI6ImFkbWluIn0." .
                      "L8q1t6i_40G-_e1S7Fw1_30O0u1-_0491823091820";
    // 动态在前端用 js 计算签名或直接给合法 admin token
    <?php
    $admin_t = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9." . b64url_encode(json_encode(["user"=>"hacked_admin", "role"=>"admin"])) . "." . b64url_encode(hash_hmac('sha256', "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9." . b64url_encode(json_encode(["user"=>"hacked_admin", "role"=>"admin"])), "123456", true));
    ?>
    document.getElementById('jwt_token').value = "<?php echo $admin_t; ?>";
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>

