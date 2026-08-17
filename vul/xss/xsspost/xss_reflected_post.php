<?php
/**
 * Pikachu-Enhanced v2.0 - 反射型 XSS (POST) 教学演练
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'header.php';

$link = connect();
$is_login_id = check_xss_login($link);

if (!$is_login_id) {
    header("location:post_login.php");
    exit();
}

$html = '';
$xss_triggered = false;
$user_input = $_POST['message'] ?? '';

if (isset($_POST['submit'])) {
    if (empty($user_input)) {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 请在上方输入框输入内容（例如输入 <code>kobe</code> 或 XSS Payload）。</div>";
    } else {
        if (preg_match('/<script|onerror|onload|javascript:|alert\(/i', $user_input)) {
            $xss_triggered = true;
        }
        
        if ($user_input === 'kobe') {
            $html = "<div class='alert alert-success' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-star'></i> 🏀 致敬传奇：Kobe Bryant</h4>
                <p>愿你和 <b>kobe</b> 一样，永远年轻，永远热血沸腾！</p>
                <img src='{$PIKA_ROOT_DIR}assets/images/nbaplayer/kobe.png' style='max-width:240px; border-radius:10px; margin-top:8px;' />
            </div>";
        } else {
            $html = "<div class='alert alert-info' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-commenting'></i> POST 查询结果回显：</h4>
                <p style='margin-bottom:0;'>Who is <span class='text-danger' style='font-size:15px; font-weight:bold;'>{$user_input}</span> ? I don't care!</p>
            </div>";
        }
    }
}

if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    setcookie('ant[uname]', '', time() - 3600, '/');
    setcookie('ant[pw]', '', time() - 3600, '/');
    header("location:post_login.php");
    exit();
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../xss.php">Cross-Site Scripting</a></li>
                <li class="active">反射型 XSS (POST) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="POST 请求体中提交的数据直接回显在响应页面中。可通过外部第三方恶意表单构造自动提交利用链。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 2. 反射型 XSS 漏洞攻防教学 (POST 方式)
                        <span class="cyber-badge-chip">客户端注入 · POST传输 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        POST 型反射 XSS 与 GET 型在后端处理上类似，核心区别在于<b>攻击 Payload 存在于 HTTP POST 请求体中，无法直接通过单纯的 URL 诱导受害者点击触发</b>。在真实攻击场景中，黑客通常需要搭建第三方恶意网页，利用 <code>&lt;form auto-submit&gt;</code> 构造类似 CSRF 的自动提交脚本诱导受害者访问触发。
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h4 class="cyber-step-title" style="margin:0;"><i class="fa fa-terminal" style="color:var(--primary);"></i> POST 交互控制台</h4>
                                <a href="xss_reflected_post.php?logout=1" class="btn btn-xs btn-default"><i class="fa fa-sign-out"></i> 退出登录</a>
                            </div>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">当前身份：已登录会员。请输入 POST 内容进行回显测试：</p>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">POST 数据 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input); ?>" placeholder="输入测试数据或 Payload" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-send"></i> POST 提交
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('kobe')"><i class="fa fa-star" style="color:var(--warning);"></i> 业务正常值: kobe</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('<script>alert(\'FLAG{XSS_REFLECTED_POST_SUCCESS}\')</script>')"><i class="fa fa-bolt"></i> POST XSS Payload</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('<svg onload=alert(document.cookie)>')"><i class="fa fa-code"></i> SVG Onload Payload</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 服务端实时响应回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 尚未提交数据，请在上方输入框填入内容后点击提交
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($xss_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发 POST 反射型 XSS 漏洞！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">POST 请求体中的 Payload 成功回显执行。恭喜获得通过凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{XSS_REFLECTED_POST_SUCCESS}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> POST 型 XSS 真实利用链</h4>
                                <div class="cyber-principle-box">
                                    <p>由于 POST 参数不位于 URL 中，攻击者通常结合 <b>第三方自动提交表单 (PoC)</b> 进行诱导利用：</p>
                                    <pre style="background:#0f172a; color:#f8fafc; padding:8px; border-radius:4px; font-size:11px; margin:4px 0;">
&lt;form action="http://target/xss_post.php" method="POST" id="xss"&gt;
    &lt;input type="hidden" name="message" value="&lt;script&gt;alert(1)&lt;/script&gt;" /&gt;
&lt;/form&gt;
&lt;script&gt;document.getElementById('xss').submit();&lt;/script&gt;
</pre>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用测试 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">&lt;script&gt;alert('FLAG{XSS_REFLECTED_POST_SUCCESS}')&lt;/script&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;script&gt;alert(\'FLAG{XSS_REFLECTED_POST_SUCCESS}\')&lt;/script&gt;')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 统一采用 htmlspecialchars 安全转义
$safe_msg = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
echo "&lt;p&gt;Who is " . $safe_msg . "&lt;/p&gt;";
</pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) { document.getElementById('message_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
