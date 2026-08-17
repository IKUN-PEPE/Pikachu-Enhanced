<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF 进阶关卡 4: 双重 Cookie 校验与 Cookie 注入 (Cookie Tossing) 绕过
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[33] = 'active';

$link = connect();

if (!isset($_SESSION['csrf']['username'])) {
    $_SESSION['csrf']['username'] = 'vince';
}

// 初始化双重 Cookie (若客户端尚无 csrf_cookie)
if (!isset($_COOKIE['csrf_token_cookie'])) {
    $legit_token = 'LEGIT_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    setcookie('csrf_token_cookie', $legit_token, time() + 3600, '/');
    $_COOKIE['csrf_token_cookie'] = $legit_token;
}

$alert_msg = "";
$alert_type = "";

if (isset($_POST['submit'])) {
    $cookie_token = $_COOKIE['csrf_token_cookie'] ?? '';
    $post_token = $_POST['csrf_token'] ?? '';
    
    // 双重 Cookie 校验逻辑：无状态服务不存 Session，仅比对 Cookie 与 POST 参数是否相等
    if (!empty($cookie_token) && !empty($post_token) && hash_equals($cookie_token, $post_token)) {
        if (!empty($_POST['email']) && !empty($_POST['phonenum'])) {
            $email = escape($link, $_POST['email']);
            $phone = escape($link, $_POST['phonenum']);
            $user = escape($link, $_SESSION['csrf']['username']);
            
            $query = "UPDATE member SET email='$email', phonenum='$phone' WHERE username='$user'";
            execute($link, $query);
            
            $is_injected = str_contains($cookie_token, 'EVIL');
            $alert_type = $is_injected ? "danger" : "success";
            $alert_msg = $is_injected 
                ? "💥 <b>Cookie Tossing 绕过成功！</b> 攻击者通过注入恶意 Cookie <code>csrf_token_cookie={$cookie_token}</code>，并在 POST 表单中附带相同值，成功欺骗双重 Cookie 校验更新了会员档案！" 
                : "✅ <b>更新成功！</b> 双重 Cookie 校验匹配通过。";
        }
    } else {
        $alert_type = "warning";
        $alert_msg = "❌ <b>双重 Cookie 校验失败！</b> Cookie 中的 Token [{$cookie_token}] 与 POST 表单提交的 Token [{$post_token}] 不匹配！";
    }
}

$user_safe = escape($link, $_SESSION['csrf']['username']);
$res = execute($link, "SELECT * FROM member WHERE username='$user_safe'");
$member = mysqli_fetch_assoc($res);

$current_cookie_token = $_COOKIE['csrf_token_cookie'] ?? 'NOT_SET';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF 进阶 4: 双重 Cookie 绕过</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 7: 双重 Cookie (Double Submit Cookie) 校验与 Cookie 注入绕过
                        <span class="cyber-badge-chip">无状态防护缺陷 · Cookie Tossing · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        为了减轻服务器 Session 存储压力，部分系统采用“双重 Cookie”模式：服务端在 Cookie 中植入 Token，并在用户提交表单时要求携带同名 POST 参数，后端<b>仅核对 Cookie 与 POST 提交的 Token 是否一致</b>。然而，若攻击者利用兄弟子域名 XSS、CRLF 响应头注入或 Cookie 污染（Cookie Tossing）强行为受害者写入一个攻击者已知的 <code>csrf_token=evil_token</code>，便可在表单中同样提交 <code>evil_token</code> 完美破防！
                    </p>
                </div>

                <?php if (!empty($alert_msg)): ?>
                    <div class="alert alert-<?php echo $alert_type; ?>" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <?php echo $alert_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Normal Double Cookie Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-clone" style="color:#6366f1;"></i> 正常双重 Cookie 校验表单
                            </h4>

                            <form method="POST" action="csrf_double_cookie.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">电子邮箱：</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">手机号码：</label>
                                    <input type="text" name="phonenum" class="form-control" value="<?php echo htmlspecialchars($member['phonenum'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:18px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">POST 表单中的 Token (必须与 Cookie 匹配)：</label>
                                    <input type="text" name="csrf_token" class="form-control" value="<?php echo htmlspecialchars($current_cookie_token); ?>" style="font-family:monospace; color:#6366f1; font-weight:700;" required />
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #6366f1, #4f46e5); border:none; padding:10px;">
                                    <i class="fa fa-check"></i> 提交资料修改 (校验 Cookie vs POST)
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Cookie Tossing Attack Simulator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-bolt" style="color:#ef4444;"></i> Cookie Tossing 攻击模拟器
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:14px; font-size:12.5px;">
                                <b>🍪 当前浏览器接收到的 Cookie Token：</b><br/>
                                <code>csrf_token_cookie = <?php echo htmlspecialchars($current_cookie_token); ?></code>
                            </div>

                            <p style="color:var(--text-secondary); font-size:12.5px; line-height:1.6; margin-bottom:14px;">
                                模拟攻击者利用兄弟子域名漏洞（如 <code>test.target.com</code>）向根域注入一个已知的 Cookie <code>csrf_token_cookie=EVIL_INJECTED_999</code>，并同时在跨站 POST 表单中提交相同的 <code>EVIL_INJECTED_999</code>：
                            </p>

                            <button type="button" class="btn btn-danger btn-block" onclick="tossEvilCookieAndSubmit()" style="border-radius:8px; font-weight:700; padding:10px;">
                                <i class="fa fa-fire"></i> 注入恶意 Cookie 并发起伪造提交
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="../csrf_json/csrf_json.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：JSON 表单混淆欺骗</a>
                    <a href="../csrf_samesite/csrf_samesite.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：SameSite Lax 绕过 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="tossedForm" action="csrf_double_cookie.php" method="POST" style="display:none;">
    <input type="hidden" name="email" value="pwned_cookie_toss@evil.com" />
    <input type="hidden" name="phonenum" value="13811112222" />
    <input type="hidden" name="csrf_token" value="EVIL_INJECTED_999" />
    <input type="hidden" name="submit" value="submit" />
</form>

<script>
function tossEvilCookieAndSubmit() {
    if (confirm('确认模拟 Cookie Tossing 攻击注入恶意 Cookie 并提交？')) {
        document.cookie = "csrf_token_cookie=EVIL_INJECTED_999; path=/; max-age=3600";
        document.getElementById('tossedForm').submit();
    }
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
