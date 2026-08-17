<?php
/**
 * Pikachu-Enhanced v2.0 - 本地文件包含 (LFI) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[57] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$lfi_triggered = false;
$filename = $_GET['filename'] ?? '';

if (isset($_GET['submit']) && !empty($filename)) {
    if (preg_match('/\.\.\/|etc\/passwd|php:\/\/|data:\/\/|input/i', $filename)) {
        $lfi_triggered = true;
    }
    
    // 【核心漏洞点】：直接将用户传入的路径拼接入 include 语句，未做目录遍历与白名单限制
    $target_file = "include/" . $filename;
    
    // 捕获输出缓冲
    ob_start();
    @include $target_file;
    $included_content = ob_get_clean();
    
    if (!empty($included_content)) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 文件包含执行成功：</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        $html .= "<div style='color:var(--text-primary); font-size:13.5px;'>" . $included_content . "</div>";
        $html .= "</div>";
    } else {
        $html .= "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 包含的目标文件为空或不存在：<code>" . htmlspecialchars($target_file) . "</code></div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="fileinclude.php">File Include</a></li>
                <li class="active">本地文件包含 (LFI) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="后端使用 include 'include/'.$filename; 直接加载。使用 ../ 向上跨越目录读取敏感文件，或使用 php:// 伪协议读取源码。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 本地文件包含 (Local File Inclusion) 攻防教学
                        <span class="cyber-badge-chip">LFI · 目录穿越 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        文件包含漏洞（File Inclusion）是指服务端通过动态变量引入外部文件并直接作为 PHP 脚本解析执行（例如 <code>include($path)</code>）。攻击者可以通过目录穿越符 <code>../</code> 跨越 Web 根目录，读取操作系统敏感配置文件（如 <code>/etc/passwd</code>、日志文件），或利用 <code>php://filter</code> 伪协议以 Base64 编码形式读取后端 PHP 源码！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 文件包含控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请选择球星卡片或直接输入目录穿越 Payload：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="file_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">选择或输入文件路径：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="file_input" name="filename" value="<?php echo htmlspecialchars($filename); ?>" placeholder="输入文件名或穿越路径，如 file1.php 或 ../../../../etc/passwd" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-file-code-o"></i> 包含执行
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('file1.php')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常文件: Kobe</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('../../../../etc/passwd')"><i class="fa fa-bolt"></i> 目录穿越: /etc/passwd</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('php://filter/read=convert.base64-encode/resource=fi_local.php')"><i class="fa fa-code"></i> 伪协议: 读当前源码</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 包含文件解析回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 尚未包含文件，请在上方输入路径后点击提交
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($lfi_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发本地文件包含 (LFI) 漏洞！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功使用目录穿越或伪协议读取了受限文件。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{LFI_DIRECTORY_TRAVERSAL_MASTER}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 文件包含与伪协议利用</h4>
                                <div class="cyber-principle-box">
                                    <p><b>1. 目录穿越：</b> <code>../../../../etc/passwd</code> 跳出默认 <code>include/</code> 目录；</p>
                                    <p style="margin-bottom:0;"><b>2. PHP 伪协议：</b> <code>php://filter/read=convert.base64-encode/resource=filename</code> 防止文件作为 PHP 执行，以 Base64 编码原样读出源码！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用实战 Payload</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">../../../../etc/passwd</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('../../../../etc/passwd')">复制</button>
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">php://filter/read=convert.base64-encode/resource=fi_local.php</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('php://filter/read=convert.base64-encode/resource=fi_local.php')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码 (白名单校验)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 使用白名单严格限定允许包含的文件名
$whitelist = ['file1.php', 'file2.php', 'file3.php', 'file4.php', 'file5.php'];
if (in_array($_GET['filename'], $whitelist, true)) {
    include "include/" . $_GET['filename'];
} else {
    die("非法文件包含请求！");
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
function fillInput(val) { document.getElementById('file_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
