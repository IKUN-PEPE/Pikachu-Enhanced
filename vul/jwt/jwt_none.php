<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[157] = 'active open';
$ACTIVE[159] = 'active';
$ACTIVE[157] = 'active open';
$ACTIVE[159] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 简易 JWT 解码与验证函数（模拟漏洞场景）
function parse_jwt($jwt_str) {
    $parts = explode('.', $jwt_str);
    if(count($parts) < 2) return null;
    
    $header = json_decode(base64_decode($parts[0]), true);
    $payload = json_decode(base64_decode($parts[1]), true);
    $signature = isset($parts[2]) ? $parts[2] : '';

    // 【漏洞点】：如果算法是 none，直接信任，不校验签名！
    if (isset($header['alg']) && strtolower($header['alg']) === 'none') {
        return $payload;
    }

    // 正常的签名校验逻辑（这里简写，只要原版 JWT 没改动就通过）
    $expected_sig = hash_hmac('sha256', $parts[0] . '.' . $parts[1], 'secret_key');
    // base64url_encode 省略，假设系统默认签发的 valid token 能通过
    
    return $payload;
}

// 默认生成一个 guest 的 JWT token
// Header: {"alg":"HS256","typ":"JWT"} => eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9
// Payload: {"username":"guest","role":"guest"} => eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0In0
// Sig: a fake valid sig
$default_jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0In0.valid_signature_for_guest";

if (!isset($_COOKIE['auth_token'])) {
    setcookie("auth_token", $default_jwt, time() + 3600, "/");
    $_COOKIE['auth_token'] = $default_jwt;
}

$current_token = $_COOKIE['auth_token'];
$user_info = parse_jwt($current_token);

$message = "";
if (isset($_POST['download'])) {
    if ($user_info && isset($user_info['role']) && $user_info['role'] === 'vip') {
        $message = "<div class='alert alert-success'>🎉 下载成功！这里是机密文件内容：FLAG{JWT_N0N3_BYP4SS_SUCCESS}</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ 下载失败：此文件仅限 VIP 用户下载！你的身份是：" . htmlspecialchars($user_info['role'] ?? 'unknown') . "</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">JWT None 算法绕过</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔑 JWT None 算法绕过</h2>
                <p>JWT 标准支持一种名为 <code>none</code> 的签名算法。这原本是为了在已经有安全信道（如 TLS）时免去签名的性能开销设计的。</p>
                <p>但是，如果后端的 JWT 解析库没有强制要求必须使用某些强算法（如 RS256/HS256），攻击者就可以将 Header 中的 <code>alg</code> 修改为 <code>none</code>，并删除第三部分的签名，从而<strong>伪造任意用户身份</strong>！</p>
                <hr>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">VIP 机密文件库</div>
                            <div class="panel-body">
                                <p>当前你的 Token 解析结果：</p>
                                <pre><?php print_r($user_info); ?></pre>
                                
                                <form method="POST">
                                    <button type="submit" name="download" class="btn btn-warning">下载机密文件</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="alert alert-info">
                            <h4>如何攻击？</h4>
                            <p>1. 使用浏览器控制台 (F12) 或抓包工具查看 Cookie 中的 <code>auth_token</code>。</p>
                            <p>2. 将 Header 改为 <code>{"alg":"none","typ":"JWT"}</code> (Base64: <code>eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0=</code>) 并去掉等号。</p>
                            <p>3. 将 Payload 改为 <code>{"username":"hacker","role":"vip"}</code> (Base64: <code>eyJ1c2VybmFtZSI6ImhhY2tlciIsInJvbGUiOiJ2aXAifQ==</code>) 并去掉等号。</p>
                            <p>4. 将新的 Token（注意最后有一个点 <code>.</code>，因为去掉了签名）写回 Cookie，再次点击下载！</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


