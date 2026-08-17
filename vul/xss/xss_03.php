<?php
/**
 * Pikachu-Enhanced v2.0 - XSS 之 href 伪协议输出教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[16] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$xss_triggered = false;
$user_input = $_GET['message'] ?? '';

if (isset($_GET['submit'])) {
    if (empty($user_input)) {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 请输入要跳转的 URL！</div>";
    } else {
        $message = htmlspecialchars($user_input, ENT_QUOTES);
        
        if (preg_match('/^javascript:/i', trim($user_input))) {
            $xss_triggered = true;
        }
        
        $html = "<div class='alert alert-info' style='margin:0;'>
            <h4 style='margin-top:0;'><i class='fa fa-external-link'></i> 生成的跳转超链接：</h4>
            <p style='margin-bottom:8px;'>请点击下方生成的超链接观察效果：</p>
            <p style='margin-bottom:0;'><a href='{$message}' class='btn btn-sm btn-primary'><i class='fa fa-link'></i> 点击跳转至你输入的链接 ({$message})</a></p>
        </div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">XSS 之 href 输出与伪协议教学</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="即使使用了 ENT_QUOTES 转义，在 href 属性中仍可使用 javascript:alert(1) 伪协议执行代码。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 9. XSS 之 href 属性输出与 JavaScript 伪协议教学
                        <span class="cyber-badge-chip">href伪协议 · 上下文盲区 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        许多安全人员以为只要对输入进行了全量 <code>htmlspecialchars(..., ENT_QUOTES)</code> 就能防住所有 XSS，这是一个极大的误区！当用户可控的数据输出在 <code>&lt;a href="..."&gt;</code>、<code>&lt;iframe src="..."&gt;</code> 的 URL 属性中时，<b>攻击者无需使用任何尖括号或引号，直接传入 <code>javascript:alert(1)</code> 伪协议</b>，用户一旦点击链接便会立即在当前域下执行恶意脚本！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> Href 属性测试控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">输入一个 URL，系统将使用 <code>htmlspecialchars(..., ENT_QUOTES)</code> 转义后填入 href：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入目标 URL / 伪协议 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input, ENT_QUOTES); ?>" placeholder="输入 URL 或 javascript: 伪协议" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-link"></i> 生成 href 链接
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('https://www.baidu.com')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常链接: 百度</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('javascript:alert(\'FLAG{HREF_JAVASCRIPT_PSEUDO_PROTOCOL}\')')"><i class="fa fa-bolt"></i> 伪协议 Payload</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('javascript:alert(document.cookie)')"><i class="fa fa-cookie"></i> 读取 Cookie Payload</button>
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
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功利用 JavaScript 伪协议触发 XSS！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">点击生成的按钮即可触发执行。恭喜获得通过凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{HREF_JAVASCRIPT_PSEUDO_PROTOCOL}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 上下文编码盲区解析</h4>
                                <div class="cyber-principle-box">
                                    <p>在 HTML 中，<code>href</code> 属性属于 <b>URL 上下文</b>。</p>
                                    <p style="margin-bottom:0;">当协议头为 <code>javascript:</code> 时，后续内容被作为脚本执行，单纯 HTML 实体转义无法防御！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐实战 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">javascript:alert('FLAG{HREF_JAVASCRIPT_PSEUDO_PROTOCOL}')</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('javascript:alert(\'FLAG{HREF_JAVASCRIPT_PSEUDO_PROTOCOL}\')')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> Href 安全防御方案</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码 (URL 协议白名单)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 严格校验 URL 协议白名单（http:// 或 https://），再做 htmlspecialchars
$url = trim($_GET['message']);
if (preg_match('/^(https?:\/\/|\/)/i', $url)) {
    $safe_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo "&lt;a href='{$safe_url}'&gt;合法链接&lt;/a&gt;";
} else {
    echo "非法链接协议！";
}
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
