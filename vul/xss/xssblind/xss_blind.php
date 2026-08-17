<?php
/**
 * Pikachu-Enhanced v2.0 - XSS 之盲打教学演练
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[13] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$link = connect();
$html = '';

if (isset($_POST['submit'])) {
    if (!empty($_POST['content']) && !empty($_POST['name'])) {
        $content = escape($link, $_POST['content']);
        $name = escape($link, $_POST['name']);
        $time = date('Y-m-d H:i:s');
        $query = "insert into xssblind(time,content,name) values('$time','$content','$name')";
        $result = execute($link, $query);
        if (mysqli_affected_rows($link) >= 1) {
            $html = "<div class='alert alert-success' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 意见反馈提交成功！</h4>
                <p style='margin-bottom:8px;'>管理员将在后台审计控制台审核你的留言。请点击右侧或下方链接进入<b>管理员审核后台</b>查看漏洞触发效果！</p>
                <a href='admin_login.php' target='_blank' class='btn btn-xs btn-primary'><i class='fa fa-shield'></i> 模拟管理员登录审核后台 (admin / 123456)</a>
            </div>";
        }
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 称呼与反馈内容均不能为空！</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../xss.php">Cross-Site Scripting</a></li>
                <li class="active">XSS 盲打 (Blind XSS) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="前台提交反馈后存入数据库，前台不展示。管理员在 admin.php 审核时触发执行。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 6. XSS 之盲打漏洞攻防教学 (Blind XSS)
                        <span class="cyber-badge-chip">后台盲打 · 权限渗透 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        盲打跨站脚本（Blind XSS）通常发生在<b>前台提交输入后，前台页面并不回显内容</b>（例如“意见反馈”、“实名认证审核”、“客服工单”、“发票抬头”等功能）。攻击者将 XSS Payload 隐藏在提交的表单中，由于前台看不到回显，攻击者并不知道是否成功，直到<b>管理员在后台管理系统打开审核列表时，恶意代码在管理员的特权浏览器中静默触发</b>！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-envelope" style="color:var(--primary);"></i> 提交用户意见与反馈 (前台表单)</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请在此提交你的反馈建议（前台不显示，将存入数据库供管理员审核）：</p>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="name_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">你的称呼 (Name)：</label>
                                    <input class="form-control" type="text" id="name_input" name="name" placeholder="例如：白帽子" value="白帽子" style="font-family:monospace;" />
                                </div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="content_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">反馈内容 / 盲打 Payload：</label>
                                    <textarea class="form-control" name="content" id="content_input" rows="3" placeholder="写下反馈内容或 XSS 盲打 Payload..." style="font-family:monospace;"></textarea>
                                </div>
                                <button class="btn btn-primary" type="submit" name="submit" value="submit" style="margin-bottom:14px;">
                                    <i class="fa fa-send"></i> 提交反馈到后台
                                </button>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('产品功能很好用，希望继续保持！')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常反馈</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('<script>alert(\'FLAG{BLIND_XSS_ADMIN_COOKIE_STOLEN}\')</script>')"><i class="fa fa-bolt"></i> 盲打弹窗 Payload</button>
                                </div>
                            </form>

                            <?php if (!empty($html)) echo "<div style='margin-bottom:14px;'>$html</div>"; ?>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />
                            <h4 class="cyber-step-title"><i class="fa fa-external-link" style="color:var(--accent);"></i> 后台管理员验证入口</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:10px;">提交盲打 Payload 后，点击下方按钮模拟管理员登录后台查看审核效果：</p>
                            <a href="admin_login.php" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-user-secret"></i> 前往管理员审核控制台 (admin / 123456)</a>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 盲打 XSS 的实操演练方法</h4>
                                <div class="cyber-principle-box">
                                    <p>1. 在前台输入盲打 Payload 并提交；</p>
                                    <p>2. 在新标签页打开 <code>admin_login.php</code> (admin / 123456)；</p>
                                    <p style="margin-bottom:0;">3. 进入审核列表，未转义的内容立即在管理员特权会话中执行！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐盲打 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">&lt;script&gt;alert('FLAG{BLIND_XSS_ADMIN_COOKIE_STOLEN}')&lt;/script&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;script&gt;alert(\'FLAG{BLIND_XSS_ADMIN_COOKIE_STOLEN}\')&lt;/script&gt;')">复制</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) { document.getElementById('content_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
