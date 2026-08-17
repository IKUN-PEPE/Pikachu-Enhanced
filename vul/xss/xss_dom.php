<?php
/**
 * Pikachu-Enhanced v2.0 - DOM 型 XSS 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[12] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">DOM 型 XSS 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="前端 JS 将 input.value 拼接在 <a href='...'> 中写入 innerHTML。输入 #' onclick='alert(1)'> 闭合单引号即可。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 4. DOM 型 XSS 漏洞攻防教学 (纯前端解析缺陷)
                        <span class="cyber-badge-chip">DOM Source/Sink · 纯前端 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        DOM 型跨站脚本（DOM-based XSS）是指恶意脚本的解析和执行完全在客户端浏览器的 <b>Document Object Model (DOM)</b> 环境中发生，<b>数据流完全不经过后端服务器处理或反射</b>。前端 JavaScript 读取不可信输入（Source），通过危险的 DOM 接收器（Sink，如 <code>innerHTML</code>、<code>eval()</code>、<code>document.write()</code>）写入页面导致执行。
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> DOM 动态生成控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">输入字符串生成一个超链接，JavaScript 将拼接该字符串到 <code>dom.innerHTML</code>：</p>
                            
                            <div class="form-group" style="margin-bottom:12px;">
                                <label for="text" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入链接 URL / 闭合 Payload：</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="text" name="text" value="" placeholder="输入 URL 或闭合 Payload" style="font-family:monospace;" />
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" id="dom_btn" onclick="domxss()">
                                            <i class="fa fa-magic"></i> 插入 DOM
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                <button type="button" class="payload-chip-btn" onclick="fillInput('https://www.baidu.com')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常 URL</button>
                                <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('#\' onclick=&quot;alert(\'FLAG{DOM_XSS_SOURCE_SINK_CRACKED}\')&quot;>')"><i class="fa fa-bolt"></i> 闭合 A 标签 Payload</button>
                                <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('javascript:alert(document.domain)')"><i class="fa fa-code"></i> 伪协议 Payload</button>
                            </div>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> DOM 实时渲染结果</h4>
                            <div id="dom" style="padding:14px; background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); color:var(--text-muted); font-size:13px; text-align:center;">
                                尚未生成链接，请在上方输入并点击按钮...
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> DOM XSS Source 与 Sink 分析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>Source（源）：</b> <span class="flow-step-tag">document.getElementById("text").value</span></p>
                                    <p><b>Sink（汇聚点）：</b> <span class="flow-step-tag">dom.innerHTML = "&lt;a href='"+str+"'&gt;..."</span></p>
                                    <p style="margin-bottom:0;">输入 <code>#' onclick="alert(1)"&gt;</code>，即可闭合单引号并注入点击事件。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐闭合 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">#' onclick="alert('FLAG{DOM_XSS_SOURCE_SINK_CRACKED}')"&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('#\' onclick=\&quot;alert(\'FLAG{DOM_XSS_SOURCE_SINK_CRACKED}\')\&quot;>')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 客户端安全防御方案</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码 (Remediated)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 正确做法：使用安全属性 API，避免字符串拼接 innerHTML
var a = document.createElement('a');
a.href = str;
a.textContent = 'what do you see?';
dom.innerHTML = '';
dom.appendChild(a);
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
function domxss() {
    var str = document.getElementById("text").value;
    var dom = document.getElementById("dom");
    dom.style.textAlign = "left";
    dom.innerHTML = "<a href='"+str+"' class='btn btn-sm btn-info'><i class='fa fa-external-link'></i> what do you see? 点击触发生成的链接</a>";
}
function fillInput(val) { document.getElementById('text').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
