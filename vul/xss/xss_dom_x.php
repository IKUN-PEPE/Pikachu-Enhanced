<?php
/**
 * Pikachu-Enhanced v2.0 - DOM 型 XSS-x 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[21] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">DOM 型 XSS-x 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="前端从 location.search 中提取 text 参数解码并拼写入 innerHTML，直接构造 URL 发送即可利用。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 5. DOM 型 XSS-x 漏洞攻防教学 (URL 参数源解析)
                        <span class="cyber-badge-chip">location.search · URL传递 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在 DOM-XSS-x 场景中，JavaScript 直接从 <b>当前浏览器的 URL 查询参数 (<code>window.location.search</code>)</b> 中提取参数值，并调用 <code>decodeURIComponent()</code> 解码后写入 DOM。这意味着攻击者可以直接构造恶意的完整链接（如 <code>http://target/?text=...</code>）发送给受害者，受害者只需在浏览器打开，纯前端逻辑便会自动解析并触发恶意脚本！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> URL 参数交互控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">输入内容后提交，前端会将参数追加到当前 URL 并重新加载解析：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="text" style="font-weight:600; color:var(--text-primary); font-size:13px;">输入参数 text：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="text" name="text" value="<?php echo htmlspecialchars($_GET['text'] ?? ''); ?>" placeholder="输入测试数据或闭合 Payload" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fa fa-refresh"></i> 更新 URL 解析
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('2026')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常值: 2026</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('#\' onclick=&quot;alert(\'FLAG{DOM_XSS_LOCATION_SEARCH_EXPLOITED}\')&quot;>')"><i class="fa fa-bolt"></i> 填入闭合 Payload</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> DOM 实时渲染结果</h4>
                            <div id="dom" style="padding:14px; background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); font-size:13px;">
                                <!-- JS 填充 -->
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 漏洞解析过程分析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>Source:</b> <span class="flow-step-tag">window.location.search</span></p>
                                    <p><b>Sink:</b> <span class="flow-step-tag">dom.innerHTML = "&lt;a href='"+str+"'&gt;..."</span></p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐实战 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">#' onclick="alert('FLAG{DOM_XSS_LOCATION_SEARCH_EXPLOITED}')"&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('#\' onclick=\&quot;alert(\'FLAG{DOM_XSS_LOCATION_SEARCH_EXPLOITED}\')\&quot;>')">复制</button>
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
function domxss() {
    var str = window.location.search;
    var txArr = str.split("text=");
    var dom = document.getElementById("dom");
    if(txArr.length > 1) {
        var tx = txArr[1].split("&")[0];
        var text = decodeURIComponent(tx);
        dom.innerHTML = "<a href='"+text+"' class='btn btn-sm btn-warning'><i class='fa fa-link'></i> 有些费尽心机，想知道答案。(点击查看效果)</a>";
    } else {
        dom.innerHTML = "<span style='color:var(--text-muted);'>当前 URL 中未检测到 text 参数，请在上方提交后观察...</span>";
    }
}
domxss();
function fillInput(val) { document.getElementById('text').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
