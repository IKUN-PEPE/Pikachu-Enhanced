<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF 进阶关卡 2: 全局 Token 池未与用户绑定漏洞
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[31] = 'active';

$link = connect();

// 模拟全局 Token 池 (存放在全局静态缓存中)
if (!isset($_SESSION['global_token_pool'])) {
    $_SESSION['global_token_pool'] = [
        'TOK_VICTIM_' . substr(md5('vince_seed'), 0, 8),
        'TOK_ATTACKER_' . substr(md5('attacker_seed'), 0, 8)
    ];
}

// 模拟当前登录受害者用户
if (!isset($_SESSION['csrf']['username'])) {
    $_SESSION['csrf']['username'] = 'vince';
}

$alert_msg = "";
$alert_type = "";

// 攻击者获取新 Token 的模拟动作
if (isset($_GET['action']) && $_GET['action'] === 'generate_attacker_token') {
    $new_token = 'TOK_ATTACKER_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    $_SESSION['global_token_pool'][] = $new_token;
    $_SESSION['last_attacker_token'] = $new_token;
    header("location:csrf_token_pool.php");
    exit();
}

if (isset($_POST['submit'])) {
    $submitted_token = trim($_POST['token'] ?? '');
    
    // 致命逻辑漏洞：只校验 Token 是否存在于全局合法 Token 池中，未校验该 Token 是否属于当前提交表单的用户！
    if (in_array($submitted_token, $_SESSION['global_token_pool'], true)) {
        if (!empty($_POST['email']) && !empty($_POST['phonenum'])) {
            $email = escape($link, $_POST['email']);
            $phone = escape($link, $_POST['phonenum']);
            $user = escape($link, $_SESSION['csrf']['username']);
            
            $query = "UPDATE member SET email='$email', phonenum='$phone' WHERE username='$user'";
            execute($link, $query);
            
            // 消费掉该 Token
            $key = array_search($submitted_token, $_SESSION['global_token_pool'], true);
            if ($key !== false) {
                unset($_SESSION['global_token_pool'][$key]);
            }
            
            $is_attacker_token = str_contains($submitted_token, 'ATTACKER');
            $alert_type = $is_attacker_token ? "danger" : "success";
            $alert_msg = $is_attacker_token 
                ? "💥 <b>越权漏洞利用成功！</b> 服务端验证 Token <code>{$submitted_token}</code> 存在于全局池中并放行！受害者 [{$user}] 的资料已被攻击者使用黑客账号领取的 Token 越权篡改！" 
                : "✅ <b>更新成功！</b> 使用了受害者自身的有效 Token。";
        }
    } else {
        $alert_type = "warning";
        $alert_msg = "❌ <b>Token 验证失败！</b> 提交的 Token 不存在或已被消费。";
    }
}

$user_safe = escape($link, $_SESSION['csrf']['username']);
$res = execute($link, "SELECT * FROM member WHERE username='$user_safe'");
$member = mysqli_fetch_assoc($res);

$attacker_token = $_SESSION['last_attacker_token'] ?? ($_SESSION['global_token_pool'][1] ?? 'TOK_ATTACKER_SAMPLE');

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF 进阶 2: Token 池未绑定会话</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 5: Anti-CSRF Token 未与用户会话绑定漏洞
                        <span class="cyber-badge-chip">Token 机制缺陷 · 全局池混用 · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在分布式架构或集中式 Token 服务中，若后端开发仅将有效 Token 存入 Redis/数据库全局池，在用户提交时<b>仅校验 Token 是否有效，而未核验该 Token 是否绑定至当前操作用户的 Session/UID</b>，攻击者便可利用自己的账号登录领取引信 Token，并将其植入发送给受害者的 CSRF 表单中，实现降维打击！
                    </p>
                </div>

                <?php if (!empty($alert_msg)): ?>
                    <div class="alert alert-<?php echo $alert_type; ?>" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <?php echo $alert_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Victim Profile Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-user-circle" style="color:#10b981;"></i> 受害者受控表单 (当前用户: <?php echo htmlspecialchars($member['username'] ?? 'vince'); ?>)
                            </h4>

                            <form method="POST" action="csrf_token_pool.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">电子邮箱：</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">手机号码：</label>
                                    <input type="text" name="phonenum" class="form-control" value="<?php echo htmlspecialchars($member['phonenum'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:18px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">提交的 Anti-CSRF Token：</label>
                                    <input type="text" id="token_input" name="token" class="form-control" value="<?php echo htmlspecialchars($_SESSION['global_token_pool'][0] ?? ''); ?>" style="font-family:monospace; color:#10b981; font-weight:700;" required />
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-block btn-success" style="border-radius:8px; font-weight:700; padding:10px; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                                    <i class="fa fa-shield"></i> 提交资料修改 (校验 Token)
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Attacker Token Extraction & Pool Inspector -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-key" style="color:#f59e0b;"></i> 攻击者账号 Token 提取与注入模拟
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:14px; font-size:12.5px;">
                                <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                    🗄️ 当前服务端全局有效 Token 池（未绑定用户）：
                                </div>
                                <ul style="margin:0; padding-left:18px; font-family:monospace; color:var(--text-secondary);">
                                    <?php foreach ($_SESSION['global_token_pool'] as $tok): ?>
                                        <li><code><?php echo htmlspecialchars($tok); ?></code></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div style="margin-bottom:14px;">
                                <a href="csrf_token_pool.php?action=generate_attacker_token" class="btn btn-xs btn-warning" style="font-weight:700;">
                                    <i class="fa fa-refresh"></i> 模拟攻击者账号领取新 Token
                                </a>
                            </div>

                            <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                💥 攻击者利用自己领取的 Token 篡改受害者：
                            </label>
                            <button type="button" class="btn btn-danger btn-block" onclick="injectAttackerToken('<?php echo htmlspecialchars($attacker_token); ?>')" style="border-radius:8px; font-weight:700; padding:10px;">
                                <i class="fa fa-bolt"></i> 注入攻击者 Token (<?php echo htmlspecialchars($attacker_token); ?>) 并提交
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="../csrf_referer/csrf_referer.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Referer 校验绕过</a>
                    <a href="../csrf_json/csrf_json.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：JSON 表单混淆欺骗 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function injectAttackerToken(tok) {
    document.getElementById('token_input').value = tok;
    document.forms[0].elements['email'].value = 'pwned_by_token_pool@attacker.com';
    document.forms[0].elements['phonenum'].value = '13999998888';
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
