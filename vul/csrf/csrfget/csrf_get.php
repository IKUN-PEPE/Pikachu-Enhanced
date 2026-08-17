<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (GET) 会员中心与攻击 PoC 生成器
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[27] = 'active';

$link = connect();

// 判断是否登录，没有登录不能访问
if (!check_csrf_login($link)) {
    header("location:csrf_get_login.php");
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    unset($_SESSION['csrf']);
    header("location:csrf_get_login.php");
    exit();
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

// 构造攻击 PoC URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$poc_url = "{$protocol}{$host}/vul/csrf/csrfget/csrf_get_edit.php?sex=girl&phonenum=13888888888&add=Hacker_Base_China&email=attacker_pwned@evil.com&submit=submit";

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
.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #06b6d4, #2563eb);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);
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

.poc-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
    margin-top: 14px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 12px;
    word-break: break-all;
    white-space: pre-wrap;
    color: #0284c7;
}
[data-theme="dark"] .poc-box {
    color: #38bdf8;
    background: #090d16;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF (GET) - 个人会员中心</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        👤 CSRF (GET) - 个人会员中心
                        <span class="cyber-badge-chip">受害者受限上下文 · 会话保持中</span>
                    </h1>
                    <p class="cyber-desc-text">
                        您已成功以会员 <b><?php echo htmlspecialchars($name); ?></b> 的身份登录。右侧为您生成了模拟攻击者的<b>恶意 PoC 链接</b>。在真实攻击中，黑客将此链接嵌入诱饵网页，受害者访问后将在毫不知情的情况下被篡改会员档案。
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Profile Info Card -->
                    <div class="col-lg-5 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <div class="profile-header-box">
                                <div class="profile-avatar">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div style="flex-grow:1;">
                                    <h3 style="margin:0 0 4px 0; color:var(--text-primary); font-weight:800; font-size:18px;">
                                        <?php echo htmlspecialchars($name); ?>
                                    </h3>
                                    <span style="font-size:12px; color:var(--text-muted);">
                                        <i class="fa fa-check-circle" style="color:#10b981;"></i> 会员会话活跃中
                                    </span>
                                </div>
                                <a href="csrf_get.php?logout=1" class="btn btn-xs btn-default" style="border-radius:6px;">
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
                                <a href="csrf_get_edit.php" class="btn btn-block btn-primary" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #0891b2, #0e7490); border:none; padding:10px;">
                                    <i class="fa fa-pencil"></i> 手动修改个人信息 (GET 表单)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live PoC & Attack Exploit Panel -->
                    <div class="col-lg-7 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                <i class="fa fa-bug" style="color:#ef4444;"></i> CSRF (GET) 攻击载荷生成与实战模拟
                            </h4>

                            <div class="alert alert-warning" style="border-radius:8px; font-size:12.5px; padding:12px; margin-bottom:14px;">
                                <i class="fa fa-exclamation-triangle"></i> <b>漏洞成因：</b> <code>csrf_get_edit.php</code> 使用 GET 请求直接处理敏感数据修改，且无 Token 或二次校验。
                            </div>

                            <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                💥 生成的恶意篡改 URL (PoC)：
                            </label>
                            <div class="poc-box" id="pocText"><?php echo htmlspecialchars($poc_url); ?></div>

                            <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                                <button type="button" class="btn btn-sm btn-info" onclick="copyPoc()" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-copy"></i> 一键复制 PoC 链接
                                </button>
                                <a href="<?php echo htmlspecialchars($poc_url); ?>" class="btn btn-sm btn-danger" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-bolt"></i> 模拟跨站点击触发篡改
                                </a>
                            </div>

                            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color);">
                                <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                    🌐 第三方恶意网页嵌入代码（零交互触发）：
                                </label>
                                <div class="poc-box">&lt;!-- 攻击者在外部恶意网页中植入零尺寸隐蔽图片 --&gt;
&lt;img src="<?php echo htmlspecialchars($poc_url); ?>" width="0" height="0" style="display:none;" /&gt;</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="csrf_get_login.php" class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-arrow-left"></i> 返回登录网关
                    </a>
                    <a href="../csrfpost/csrf_post_login.php" class="btn btn-warning" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #f59e0b, #d97706); border:none;">
                        进入下一关：CSRF (POST 方式) <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function copyPoc() {
    const text = document.getElementById('pocText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('PoC 链接已复制到剪贴板！');
    }).catch(err => {
        alert('复制失败，请手动选择复制。');
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
