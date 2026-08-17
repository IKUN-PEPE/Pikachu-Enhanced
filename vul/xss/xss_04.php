<?php
/**
 * Pikachu-Enhanced v2.0 - XSS 之 JS 输出上下文逃逸教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[17] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$jsvar = '';
$html = '';
$user_input = $_GET['message'] ?? '';

if (isset($_GET['submit']) && !empty($user_input)) {
    $jsvar = $user_input;
    if ($jsvar === 'tmac') {
        $html = "<div class='alert alert-success' style='margin:0;'>
            <h4 style='margin-top:0;'><i class='fa fa-star'></i> 🏀 致敬麦迪：Tracy McGrady</h4>
            <img src='{$PIKA_ROOT_DIR}assets/images/nbaplayer/tmac.jpeg' style='max-width:240px; border-radius:10px; margin-top:8px;' />
        </div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">XSS 之 JS 输出上下文逃逸教学</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="用户输入直接拼接在 <script> 标签内的 JS 变量中。输入 ';alert(1);// 即可闭合引号和语句。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 10. XSS 之 JavaScript 内部变量输出上下文逃逸教学
                        <span class="cyber-badge-chip">JS上下文 · 闭合引号与标签 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当用户输入被动态嵌入在 <code>&lt;script&gt;</code> 标签内的 JavaScript 变量中（例如 <code>var str = '{$user_input}';</code>）时，这属于 <b>JavaScript 上下文</b>。攻击者可以通过两种经典方式完成逃逸：① 注入 <code>'; alert(1); //</code> 闭合字符串并提前终止 JS 语句；② 注入 <code>&lt;/script&gt;&lt;script&gt;alert(1)&lt;/script&gt;</code> 直接闭合父级 HTML Script 标签！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> JS 上下文注入控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">输入内容将动态嵌入在页面的 <code>&lt;script&gt; var msg = '...'; &lt;/script&gt;</code> 中：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入 JS 变量测试值：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input); ?>" placeholder="输入 tmac 或 JS 闭合 Payload" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-code"></i> 提交生成 JS 变量
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('tmac')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常值: tmac</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('\';alert(\'FLAG{JS_CONTEXT_STRING_ESCAPE_SUCCESS}\');//')"><i class="fa fa-bolt"></i> 方式1: 闭合单引号与分号</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('</script><script>alert(1)</script>')"><i class="fa fa-code"></i> 方式2: 闭合Script标签</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />
                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 服务端实时响应与 JS 结构</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)) echo $html; else echo "<div style='background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;'><i class='fa fa-code'></i> 提交后按 F12 打开开发者工具查看 Elements 中嵌入的 JavaScript 代码。</div>"; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> JS 上下文逃逸原理</h4>
                                <div class="cyber-principle-box">
                                    <p>页面生成代码结构：</p>
                                    <pre style="background:#0f172a; color:#f8fafc; padding:8px; border-radius:4px; font-size:11px; margin:4px 0;">
&lt;script&gt;
    var $ms = '{$jsvar}';
    if($ms.length != 0) { ... }
&lt;/script&gt;
</pre>
                                    <p style="margin-bottom:0;">传入 <code>';alert(1);//</code> ➔ 结构变为 <code>var $ms = '';alert(1);//';</code> 成功执行！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐实战 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">';alert('FLAG{JS_CONTEXT_STRING_ESCAPE_SUCCESS}');//</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('\';alert(\'FLAG{JS_CONTEXT_STRING_ESCAPE_SUCCESS}\');//')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> JS 上下文安全防御方案</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码 (json_encode 转义)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 使用 json_encode() 并配合标志位安全转义
$safe_json = json_encode($_GET['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
echo "&lt;script&gt; var safe_msg = {$safe_json}; &lt;/script&gt;";
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
    var $ms='<?php echo $jsvar;?>';
    function fillInput(val) { document.getElementById('message_input').value = val; }
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
    }
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
