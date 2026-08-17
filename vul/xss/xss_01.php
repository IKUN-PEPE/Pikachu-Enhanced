<?php
/**
 * Pikachu-Enhanced v2.0 - XSS 之过滤绕过教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[14] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$xss_triggered = false;
$user_input = $_GET['message'] ?? '';

if (isset($_GET['submit']) && !empty($user_input)) {
    $filtered = preg_replace('/<(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t/i', '', $user_input);
    
    if (preg_match('/onerror|onload|onfocus|onmouseover|alert\(/i', $user_input)) {
        $xss_triggered = true;
    }
    
    if ($filtered === 'yes') {
        $html = "<div class='alert alert-success' style='margin:0;'><h4>那就去操场跑一圈！</h4></div>";
    } else {
        $html = "<div class='alert alert-info' style='margin:0;'>
            <h4 style='margin-top:0;'><i class='fa fa-filter'></i> 过滤后回显输出：</h4>
            <p style='margin-bottom:0;'>你说了这些 '<strong>{$filtered}</strong>' 的话，真伤心，请离开！</p>
        </div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">XSS 之过滤绕过教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="后端正则过滤了 <script 标签，使用 <img src=1 onerror=...> 等事件属性即可轻松绕过。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 7. XSS 之黑名单过滤绕过教学 (Filter Bypass)
                        <span class="cyber-badge-chip">黑名单缺陷 · 事件驱动 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        开发人员试图通过正则表达式匹配并删除 <code>&lt;script&gt;</code> 标签来防止 XSS，但<b>黑名单防御是不完备的</b>。HTML 中支持执行 JavaScript 的标签和属性远不止 <code>&lt;script&gt;</code> 一种，例如 <code>&lt;img&gt;</code>、<code>&lt;svg&gt;</code>、<code>&lt;body&gt;</code>、<code>&lt;input&gt;</code> 等元素均支持通过事件监听器（Event Handlers 如 <code>onerror</code>、<code>onload</code>、<code>onfocus</code>）执行代码。
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 过滤绕过控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">测试提交数据，观察服务端正则对 <code>&lt;script</code> 的过滤效果：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入测试 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input); ?>" placeholder="输入 Payload" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-filter"></i> 提交测试
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('<script>alert(1)</script>')"><i class="fa fa-times" style="color:var(--danger);"></i> 被过滤测试: Script标签</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('<img src=1 onerror=alert(\'FLAG{XSS_FILTER_BYPASS_EVENT_HANDLER}\')>')"><i class="fa fa-bolt"></i> 绕过: Img Onerror</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('<svg onload=alert(1)>')"><i class="fa fa-code"></i> 绕过: Svg Onload</button>
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
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功绕过正则黑名单并触发 XSS！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功使用事件属性绕过了对 script 的过滤。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{XSS_FILTER_BYPASS_EVENT_HANDLER}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 黑名单过滤的缺陷</h4>
                                <div class="cyber-principle-box">
                                    <p style="margin-bottom:0;">正则 <code>preg_replace('/&lt;(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t/i', '', ...)</code> 试图消灭 script，但使用非 script 标签的事件监听器即可轻松绕过。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用绕过 Payload 汇总</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">&lt;img src=1 onerror="alert('FLAG{XSS_FILTER_BYPASS_EVENT_HANDLER}')"&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;img src=1 onerror=\&quot;alert(\'FLAG{XSS_FILTER_BYPASS_EVENT_HANDLER}\')\&quot;&gt;')">复制</button>
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">&lt;svg onload="alert(1)"&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;svg onload=\&quot;alert(1)\&quot;&gt;')">复制</button>
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
function fillInput(val) { document.getElementById('message_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
