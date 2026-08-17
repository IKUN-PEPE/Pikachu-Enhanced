<?php
/**
 * Pikachu-Enhanced v2.0 - XSS 之 htmlspecialchars 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$xss_triggered = false;
$user_input = $_GET['message'] ?? '';

if (isset($_GET['submit'])) {
    if (empty($user_input)) {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 请输入内容进行测试！</div>";
    } else {
        $message = htmlspecialchars($user_input);
        
        if (strpos($user_input, "'") !== false && preg_match('/onclick|onmouseover|alert/i', $user_input)) {
            $xss_triggered = true;
        }
        
        $html = "<div class='alert alert-info' style='margin:0;'>
            <h4 style='margin-top:0;'><i class='fa fa-link'></i> 服务端生成超链接标签：</h4>
            <p style='margin-bottom:8px;'>链接使用单引号包裹 href 属性：</p>
            <p style='margin-bottom:0;'><a href='{$message}' class='btn btn-sm btn-info'><i class='fa fa-external-link'></i> 点击查看生成的超链接 ({$message})</a></p>
        </div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">XSS 之 htmlspecialchars 绕过教学</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="htmlspecialchars 默认不转义单引号。输出在 <a href='...'> 单引号属性中时，用单引号闭合即可。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 8. XSS 之 htmlspecialchars 默认参数绕过教学
                        <span class="cyber-badge-chip">单引号绕过 · 属性逃逸 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        PHP 中的 <code>htmlspecialchars($string)</code> 函数默认情况下（<code>ENT_COMPAT</code> 模式）只会转义双引号 <code>"</code>、与号 <code>&amp;</code>、小于号 <code>&lt;</code>、大于号 <code>&gt;</code>，<b>默认绝不转义单引号 <code>'</code></b>！当开发人员在 HTML 中使用单引号包裹属性（如 <code>&lt;a href='{$input}'&gt;</code> 或 <code>&lt;input value='{$input}'&gt;</code>）时，攻击者使用单引号 <code>'</code> 即可直接闭合属性并注入事件属性！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> htmlspecialchars 单引号逃逸控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">输入内容生成单引号包裹的超链接：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入测试数据：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input, ENT_QUOTES); ?>" placeholder="输入数据或单引号闭合 Payload" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-check"></i> 生成超链接
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('https://www.baidu.com')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常 URL</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('#\' onclick=\'alert(&quot;FLAG{HTMLSPECIALCHARS_SINGLE_QUOTE_BYPASS}&quot;)\'')"><i class="fa fa-bolt"></i> 单引号闭合 Payload</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('#\' onmouseover=\'alert(1)\'')"><i class="fa fa-mouse-pointer"></i> 悬停触发 Payload</button>
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
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功利用单引号闭合属性触发 XSS！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功利用 htmlspecialchars 默认不转义单引号的特性完成了属性逃逸。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{HTMLSPECIALCHARS_SINGLE_QUOTE_BYPASS}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 闭合机理分析</h4>
                                <div class="cyber-principle-box">
                                    <p>服务端输出：<code>&lt;a href='{$message}'&gt;</code></p>
                                    <p style="margin-bottom:0;">传入 <code>#' onclick='alert(1)'</code> ➔ 结构变为 <code>&lt;a href='#' onclick='alert(1)''&gt;</code>，单引号成功闭合！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐测试 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">#' onclick='alert("FLAG{HTMLSPECIALCHARS_SINGLE_QUOTE_BYPASS}")'</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('#\' onclick=\'alert(\&quot;FLAG{HTMLSPECIALCHARS_SINGLE_QUOTE_BYPASS}\&quot;)\'' )">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 正确修复方案</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 显式开启 ENT_QUOTES 标志，同时转义单引号和双引号
$safe_msg = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
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
