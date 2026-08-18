<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (Token) 会员中心与 Token 动态监控
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[29] = 'active';

$link = connect();

// 判断是否登录，没有登录不能访问
if (!check_csrf_login($link)) {
    header("location:token_get_login.php");
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    unset($_SESSION['csrf']);
    unset($_SESSION['token']);
    header("location:token_get_login.php");
    exit();
}

// 确保 Token 存在
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    set_token();
}

// 查询当前会员信息
$username = $_SESSION['csrf']['username'];
$query = "select * from member where username='$username'";
$result = execute($link, $query);
$data = mysqli_fetch_assoc($result);

$name = $data['username'] ?? '';
$sex = $data['sex'] ?? '';
$phonenum = $data['phonenum'] ?? '';
$add = $data['address'] ?? '';
$email = $data['email'] ?? '';
$current_token = $_SESSION['token'] ?? '';

// 构造一个不带 Token 的攻击 URL，供对比测试
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$invalid_attack_url = "{$protocol}{$host}/vul/csrf/csrftoken/token_get_edit.php?sex=girl&phonenum=13888888888&add=Attack_No_Token&email=attacker@evil.com&submit=submit";

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.profile-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 26px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    height: 100%;
}
.profile-header-box {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 20px;
}
.profile-avatar-token {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}
.profile-field-row {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed var(--border-color);
    font-size: 13.5px;
}
.profile-field-label {
    width: 100px;
    color: var(--text-muted);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.profile-field-val {
    color: var(--text-primary);
    font-weight: 700;
    flex-grow: 1;
}

.token-display-box {
    background: var(--bg-secondary);
    border: 1px solid #10b981;
    border-radius: 10px;
    padding: 14px 16px;
    margin: 14px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    color: #059669;
}
[data-theme="dark"] .token-display-box {
    color: #34d399;
    background: #091a13;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF Token - 个人会员中心</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        👤 CSRF Token - 个人会员中心
                        <span class="cyber-badge-chip" style="border-color:#10b981; color:#34d399; background:rgba(16,185,129,0.15);">Anti-CSRF Token 保护生效中</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当前会员 <b><?php echo htmlspecialchars($name); ?></b> 已经受到服务端 <b>Anti-CSRF Token 校验机制</b> 的保护。修改任何个人信息时，客户端必须随请求提交正确的 Token 参数，未携带或携带错误 Token 的跨站伪造请求将被服务端坚决拒绝！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Profile Info Card -->
                    <div class="col-lg-5 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <div class="profile-header-box">
                                <div class="profile-avatar-token">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div style="flex-grow:1;">
                                    <h3 style="margin:0 0 4px 0; color:var(--text-primary); font-weight:800; font-size:18px;">
                                        <?php echo htmlspecialchars($name); ?>
                                    </h3>
                                    <span style="font-size:12px; color:var(--text-muted);">
                                        <i class="fa fa-shield" style="color:#10b981;"></i> Token 防护已激活
                                    </span>
                                </div>
                                <a href="token_get.php?logout=1" class="btn btn-xs btn-default" style="border-radius:6px;">
                                    <i class="fa fa-sign-out"></i> 安全退出
                                </a>
                            </div>

                            <div class="profile-field-row">
                                <span class="profile-field-label"><i class="fa fa-id-card"></i> 账 户</span>
                                <span class="profile-field-val"><?php echo htmlspecialchars($name); ?></span>
                            </div>
                            <div class="profile-field-row">
                                <span class="profile-field-label"><i class="fa fa-venus-mars"></i> 性 别</span>
                                <span class="profile-field-val"><?php echo htmlspecialchars($sex); ?></span>
                            </div>
                            <div class="profile-field-row">
                                <span class="profile-field-label"><i class="fa fa-phone"></i> 手 机</span>
                                <span class="profile-field-val"><?php echo htmlspecialchars($phonenum); ?></span>
                            </div>
                            <div class="profile-field-row">
                                <span class="profile-field-label"><i class="fa fa-map-marker"></i> 住 址</span>
                                <span class="profile-field-val"><?php echo htmlspecialchars($add); ?></span>
                            </div>
                            <div class="profile-field-row" style="border-bottom:none;">
                                <span class="profile-field-label"><i class="fa fa-envelope"></i> 邮 箱</span>
                                <span class="profile-field-val"><?php echo htmlspecialchars($email); ?></span>
                            </div>

                            <div style="margin-top:24px;">
                                <a href="token_get_edit.php" class="btn btn-block btn-success" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none; padding:10px;">
                                    <i class="fa fa-pencil"></i> 手动修改个人信息 (带 Token 保护)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Token Inspection Panel -->
                    <div class="col-lg-7 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                <i class="fa fa-key" style="color:#10b981;"></i> 会话 Anti-CSRF Token 实时探针
                            </h4>

                            <div class="alert alert-success" style="border-radius:8px; font-size:12.5px; padding:12px; margin-bottom:14px;">
                                <i class="fa fa-check-circle"></i> <b>防护机制正常运行：</b> 每次修改资料时，后端将比对提交的 <code>token</code> 与服务器 <code>$_SESSION['token']</code>。
                            </div>

                            <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">
                                🔑 当前会话中的活跃 Token 值：
                            </label>
                            <div class="token-display-box">
                                <span><?php echo htmlspecialchars($current_token); ?></span>
                                <span class="badge badge-success" style="font-family:sans-serif; font-size:11px;">活跃且受保护</span>
                            </div>

                            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color);">
                                <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                    🧪 对比实验：测试未携带 Token 的伪造请求能否成功？
                                </label>
                                <p style="font-size:12.5px; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
                                    第三方恶意网站因为无法获知上面的动态 Token，只能向目标发送不带 Token 的请求。点击下方按钮测试该请求是否会被服务器成功拦截：
                                </p>
                                <a href="<?php echo htmlspecialchars($invalid_attack_url); ?>" class="btn btn-sm btn-danger" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-ban"></i> 发起无 Token 伪造攻击（预期被拦截）
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../csrfpost/csrf_post.php" class="btn btn-warning" style="border-radius:8px; font-weight:700; color:#fff;">
                        <i class="fa fa-arrow-left"></i> 上一关：CSRF (POST 方式)
                    </a>
                    <a href="../csrf_referer/csrf_referer.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">
                        下一关：CSRF Referer 绕过 <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
