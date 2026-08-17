<?php
/**
 * Pikachu-Enhanced v2.0 - 命令注入 (Exec "ping") 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[52] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$rce_triggered = false;
$user_ip = $_POST['ipaddress'] ?? '';

if (isset($_POST['submit']) && !empty($user_ip)) {
    if (preg_match('/[|;&`$]/', $user_ip)) {
        $rce_triggered = true;
    }
    
    // Windows 与 Linux 兼容执行 ping 命令
    if (stristr(php_uname('s'), 'Windows')) {
        $cmd = "ping -n 2 " . $user_ip;
    } else {
        $cmd = "ping -c 2 " . $user_ip;
    }
    
    $output = shell_exec($cmd);
    $html .= "<div class='alert alert-info' style='margin:0;'>";
    $html .= "<h4 style='margin-top:0;'><i class='fa fa-terminal'></i> 系统终端执行回显：</h4>";
    $html .= "<pre style='background:#0f172a; color:#38bdf8; border-radius:6px; padding:12px; font-size:12.5px; margin:8px 0 0 0; max-height:260px; overflow-y:auto;'>" . htmlspecialchars($output ?? '无输出或命令执行超时') . "</pre>";
    $html .= "</div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="rce.php">RCE 命令执行</a></li>
                <li class="active">exec "ping" 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="系统直接将输入的 IP 拼接入 shell_exec 执行。使用管道符 | 或分号 ; 拼接执行后续任意系统命令。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 远程命令执行 (RCE) - Exec "ping" 攻防教学
                        <span class="cyber-badge-chip">RCE · 命令拼接 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        命令执行漏洞（Command Injection）发生在服务端调用系统命令执行函数（如 <code>system()</code>、<code>shell_exec()</code>、<code>exec()</code>）时，<b>直接将用户可控的外部输入拼接在命令字符串中</b>。攻击者可以利用操作系统的命令连接符（如 <code>|</code>、<code>||</code>、<code>&amp;</code>、<code>&amp;&amp;</code>、<code>;</code>）注入任意恶意命令，直接接管底层服务器！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 网络探测 Ping 控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入要测试连通性的 IP 地址或注入连接符：</p>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="ip_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">目标 IP / 命令 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="ip_input" name="ipaddress" value="<?php echo htmlspecialchars($user_ip); ?>" placeholder="输入 IP，如 127.0.0.1 或 127.0.0.1 | whoami" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-play"></i> 执行探测
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('127.0.0.1')"><i class="fa fa-wifi" style="color:var(--accent);"></i> 正常 IP: 127.0.0.1</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('127.0.0.1 | whoami')"><i class="fa fa-bolt"></i> 管道命令: whoami</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('127.0.0.1 | id; uname -a')"><i class="fa fa-server"></i> 系统指纹: uname -a</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('127.0.0.1 | cat /etc/passwd')"><i class="fa fa-file-text"></i> 读取文件: /etc/passwd</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 命令执行实时回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 尚未执行命令，请在上方输入 IP 后点击执行
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($rce_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发 RCE 远程命令执行！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功使用命令分隔符跳出 ping 限制并执行了任意操作系统命令。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{RCE_SHELL_EXEC_CONTROL_ACQUIRED}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 命令注入连接符解析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>| (管道符)：</b> 将前一命令的输出作为后一命令的输入，执行后一命令；</p>
                                    <p><b>&amp;&amp; (逻辑与)：</b> 前一命令成功执行后再执行后一命令；</p>
                                    <p style="margin-bottom:0;"><b>; (分号，Linux)：</b> 顺序执行多个互不相关的独立命令。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用实战 Payload</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">127.0.0.1 | whoami</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('127.0.0.1 | whoami')">复制</button>
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">127.0.0.1 | cat /etc/passwd</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('127.0.0.1 | cat /etc/passwd')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与防御加固</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御方案 (escapeshellcmd / escapeshellarg)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 方案 1：严格校验 IPv4/IPv6 格式
if (!filter_var($_POST['ipaddress'], FILTER_VALIDATE_IP)) {
    die("非法 IP 格式！");
}

// ✅ 方案 2：使用 escapeshellarg 转义参数
$safe_ip = escapeshellarg($_POST['ipaddress']);
$cmd = "ping -c 2 " . $safe_ip;
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
function fillInput(val) { document.getElementById('ip_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
