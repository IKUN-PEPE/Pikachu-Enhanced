<?php
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[140] = 'active open';
$ACTIVE[213] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once 'dockerlab_terminal_engine.php';

$CORRECT_FLAG = "flag{docker_cve_Mode_Host_Mount_Escape_Done}";
$flag_result = "";
$user_flag = "";

// Initialize session state & working directory
if (!isset($_SESSION['docker_priv_cwd'])) {
    $_SESSION['docker_priv_cwd'] = '/root';
}

if (!isset($_SESSION['docker_priv_history'])) {
    $_SESSION['docker_priv_history'] = array(
        array('type' => 'sys', 'content' => "// [Universal Linux Bash Terminal Ready: root@privileged-sandbox:~#]\n// 提示：已上线全量通用 Linux 终端引擎！支持 fdisk -l, cat /proc/1/status, mount, chroot, cd, ls, pwd, help 等所有命令！\n// 推荐第一步输入：cat /proc/1/status | grep CapEff 或 fdisk -l")
    );
}

if (!isset($_SESSION['docker_priv_state'])) {
    $_SESSION['docker_priv_state'] = array(
        'checked' => false,
        'mounted' => false,
        'chrooted' => false
    );
}

// Handle Clear History Action
if (isset($_POST['clear_history'])) {
    $_SESSION['docker_priv_history'] = array(
        array('type' => 'sys', 'content' => "// [Terminal reset: root@privileged-sandbox:~#]")
    );
    $_SESSION['docker_priv_state'] = array('checked' => false, 'mounted' => false, 'chrooted' => false);
    $_SESSION['docker_priv_cwd'] = '/root';
}

// Handle Flag Submission
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input'] ?? '');
    if ($user_flag === $CORRECT_FLAG) {
        $_SESSION['docker_priv_flag'] = true;
        $flag_result = "<div class='alert alert-success' style='border-radius:10px; font-weight:700; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#10b981;'><i class='fa fa-check-circle'></i> <b>恭喜通关！</b> Flag 验证正确！已成功完成 --privileged 特权模式全盘挂载逃逸 (+100 PTS)。</div>";
    } else {
        $flag_result = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700; background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#ef4444;'><i class='fa fa-times-circle'></i> <b>Flag 错误</b>，请在 Bash 终端中完成挂载并查看 /tmp/cve_escape_flag.txt 文件！</div>";
    }
}

// Handle Sandbox Lifecycle
$sandbox_msg = '';
if (isset($_POST['start_sandbox'])) {
    shell_exec("/var/www/html/docker-cli rm -f pikachu-lab-cve-escape 2>&1");
    $out = shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-cve-escape docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
    shell_exec("/var/www/html/docker-cli exec pikachu-lab-cve-escape bash -c 'echo \"flag{docker_cve_Mode_Host_Mount_Escape_Done}\" > /tmp/cve_escape_flag.txt' 2>&1");
    $sandbox_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check'></i> 真实靶场容器 [pikachu-lab-cve-escape] 已部署并在后台运行！</div>";
}
if (isset($_POST['stop_sandbox'])) {
    shell_exec("/var/www/html/docker-cli rm -f pikachu-lab-cve-escape 2>&1");
    $sandbox_msg = "<div class='alert alert-warning' style='border-radius:10px; font-weight:700;'><i class='fa fa-stop'></i> 真实靶场容器 [pikachu-lab-cve-escape] 已强制销毁清理！</div>";
}

// Check Sandbox Status
$is_sandbox_running = false;
$sandbox_uptime = '';
$status_check = shell_exec("/var/www/html/docker-cli ps --filter name=^pikachu-lab-cve-escape$ --format \"{{.Names}}	{{.Status}}\" 2>/dev/null");
$status_check = trim($status_check ?? '');
if (!empty($status_check) && strpos($status_check, 'pikachu-lab-cve-escape') !== false) {
    $is_sandbox_running = true;
    $parts = explode("	", $status_check);
    $sandbox_uptime = isset($parts[1]) ? trim($parts[1]) : 'Running';
}

// Handle Command Execution Input
if (isset($_POST['exec_cmd'])) {
    $raw_cmd = trim($_POST['cmd_input'] ?? '');
    if ($raw_cmd !== '') {
        $output = dockerlab_exec_universal($raw_cmd, 'docker_cve', $_SESSION['docker_priv_state'], $_SESSION['docker_priv_cwd']);

        // Add to history
        $_SESSION['docker_priv_history'][] = array(
            'type' => 'user',
            'cmd' => $raw_cmd,
            'cwd' => $_SESSION['docker_priv_cwd'],
            'output' => $output
        );
    }
}
?>

<style>

/* Responsive Light / Dark Theme Adaptive System */
.cyber-stage-container {
    background-color: var(--bg-card);
    border-radius: 16px;
    padding: 24px;
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.cyber-header-card {
    background: var(--bg-card);
    border-radius: 14px;
    padding: 24px 28px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.cyber-header-title {
    color: var(--text-primary) !important;
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.cyber-badge-chip {
    background: rgba(6, 182, 212, 0.1);
    color: #0891b2;
    border: 1px solid rgba(6, 182, 212, 0.5);
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
[data-theme="dark"] .cyber-badge-chip {
    color: #06b6d4;
}
.cyber-desc-text {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}
.cyber-desc-text code {
    background: rgba(6, 182, 212, 0.1);
    color: #0284c7;
    border: 1px solid rgba(6, 182, 212, 0.25);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', monospace;
}
[data-theme="dark"] .cyber-desc-text code {
    background: rgba(6, 182, 212, 0.2);
    color: #38bdf8;
    border-color: rgba(6, 182, 212, 0.3);
}

/* Callout Warning & Success Boxes */
.cyber-callout-danger {
    background: rgba(239, 68, 68, 0.08);
    border-left: 4px solid #ef4444;
    padding: 14px 16px;
    margin-bottom: 15px;
    border-radius: 6px;
}
.cyber-callout-danger h5 {
    color: #ef4444;
    margin-top: 0;
    margin-bottom: 6px;
    font-weight: 700;
}
.cyber-callout-danger p, .cyber-callout-danger li {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 0;
}

.cyber-callout-success {
    background: rgba(16, 185, 129, 0.08);
    border-left: 4px solid #10b981;
    padding: 14px 16px;
    margin-bottom: 20px;
    border-radius: 6px;
}
.cyber-callout-success h5 {
    color: #10b981;
    margin-top: 0;
    margin-bottom: 6px;
    font-weight: 700;
}
.cyber-callout-success ol, .cyber-callout-success p, .cyber-callout-success li {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 0;
    padding-left: 16px;
}

/* Details command box */
.cyber-details-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-top: 8px;
    overflow: hidden;
}
.cyber-details-summary {
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 700;
    padding: 12px;
    cursor: pointer;
    outline: none;
    display: flex;
    align-items: center;
    gap: 6px;
    user-select: none;
}
.cyber-details-summary:hover {
    color: var(--text-primary);
}
.cyber-details-body {
    padding: 12px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 12px;
    white-space: pre-wrap;
    word-break: break-all;
    border-top: 1px solid var(--border-color);
    color: #0284c7;
    background: var(--bg-card);
}
[data-theme="dark"] .cyber-details-body {
    color: #38bdf8;
    background: #090d16;
}

/* Left Guide Section */
.cyber-guide-section {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px;
}
.cyber-section-title {
    margin-top: 0;
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cyber-step-card {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 14px;
    position: relative;
    transition: all 0.25s ease;
}
.cyber-step-card:hover {
    border-color: #06b6d4;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(6, 182, 212, 0.15);
}
.cyber-step-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.cyber-step-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #06b6d4, #2563eb);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(6, 182, 212, 0.3);
}
.cyber-step-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--text-primary);
}
.cyber-principle-box {
    font-size: 12.5px;
    color: var(--text-secondary);
    line-height: 1.7;
    background: var(--bg-card);
    padding: 10px 12px;
    border-radius: 8px;
    margin-top: 8px;
    border-left: 3px solid #06b6d4;
}
.cyber-principle-box code {
    background: rgba(6, 182, 212, 0.1);
    color: #0284c7;
    padding: 1px 5px;
    border-radius: 4px;
    font-family: 'Fira Code', monospace;
}
[data-theme="dark"] .cyber-principle-box code {
    background: rgba(6, 182, 212, 0.15);
    color: #38bdf8;
}

.cyber-code-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 10px 12px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
[data-theme="dark"] .cyber-code-container {
    background: #090d16;
    border-color: rgba(255, 255, 255, 0.1);
}
.cyber-code-text {
    color: #0369a1;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 12px;
    word-break: break-all;
    white-space: pre-wrap;
    margin: 0;
}
[data-theme="dark"] .cyber-code-text {
    color: #38bdf8;
}
.cyber-copy-btn {
    background: rgba(6, 182, 212, 0.1);
    color: #0891b2;
    border: 1px solid #06b6d4;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}
[data-theme="dark"] .cyber-copy-btn {
    background: rgba(6, 182, 212, 0.2);
    color: #38bdf8;
}
.cyber-copy-btn:hover {
    background: #06b6d4;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(6, 182, 212, 0.4);
}

/* Right Interactive Terminal Section (Universal Full Terminal) */
.cyber-terminal-card {
    background: #020617;
    border-radius: 14px;
    border: 1px solid var(--border-color);
    box-shadow: 0 16px 45px rgba(0,0,0,0.15);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
[data-theme="dark"] .cyber-terminal-card {
    border-color: #1e293b;
    box-shadow: 0 16px 45px rgba(0,0,0,0.4);
}
.cyber-terminal-bar {
    background: #0f172a;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #1e293b;
}
.cyber-dots-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.cyber-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
.cyber-dot-red { background: #ef4444; }
.cyber-dot-yellow { background: #f59e0b; }
.cyber-dot-green { background: #10b981; }
.cyber-terminal-title {
    color: #94a3b8;
    font-size: 12px;
    font-family: 'Fira Code', monospace;
}
.cyber-terminal-screen {
    padding: 20px;
    color: #e2e8f0;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.7;
    height: 520px;
    overflow-y: auto;
    background: #020617;
}
.term-prompt {
    color: #10b981;
    font-weight: 700;
}
.term-user-cmd {
    color: #f8fafc;
    font-weight: 700;
}
.term-output {
    color: #38bdf8;
    margin-bottom: 14px;
    white-space: pre-wrap;
}

.cyber-terminal-input-bar {
    background: #0f172a;
    padding: 12px 16px;
    border-top: 1px solid #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
}
.cyber-terminal-input-bar input {
    background: #020617 !important;
    border: 1px solid #334155 !important;
    border-radius: 8px !important;
    color: #f8fafc !important;
    font-family: 'Fira Code', 'Consolas', monospace !important;
    font-size: 13px !important;
    padding: 10px 14px !important;
    flex-grow: 1;
    box-shadow: none !important;
}
.cyber-terminal-input-bar input:focus {
    outline: none !important;
    border-color: #06b6d4 !important;
    box-shadow: 0 0 12px rgba(6, 182, 212, 0.3) !important;
}

.cyber-flag-card {
    background-color: var(--bg-card);
    border: 2px dashed rgba(6, 182, 212, 0.4);
    border-radius: 14px;
    padding: 20px;
    margin-top: 18px;
}


/* Real-time Sandbox Live Status Badge */
.sandbox-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    vertical-align: middle;
    transition: all 0.3s ease;
}
.sandbox-status-badge .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    transition: all 0.3s ease;
}

/* Running (Green) with soft pulse */
.sandbox-status-badge.status-running {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.4);
}
.sandbox-status-badge.status-running .status-dot {
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
    animation: statusPulse 2s infinite ease-in-out;
}

/* Stopped (Gray) */
.sandbox-status-badge.status-stopped {
    background: rgba(100, 116, 139, 0.12);
    color: #64748b;
    border: 1px solid rgba(100, 116, 139, 0.3);
}
.sandbox-status-badge.status-stopped .status-dot {
    background: #94a3b8;
    box-shadow: none;
    animation: none;
}

/* Processing (Cyan/Spin) */
.sandbox-status-badge.status-processing {
    background: rgba(6, 182, 212, 0.15);
    color: #0891b2;
    border: 1px solid rgba(6, 182, 212, 0.4);
}
.sandbox-status-badge.status-processing .status-dot {
    background: #06b6d4;
    animation: statusBlink 0.8s infinite ease-in-out;
}

@keyframes statusPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
@keyframes statusBlink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="dockerlab.php">Docker Lab</a></li>
                <li class="active">Docker 特权模式逃逸 (--privileged)</li>
            </ul>
        </div>

        <div class="page-content">
            
            <div class="cyber-stage-container">
                <!-- Stage Header -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 4. Docker/内核 CVE 逃逸 (DirtyPipe/runc)
                        <span class="cyber-badge-chip">特权逃逸 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当 Docker 以 <code>--privileged</code> 选项启动容器时，容器将拥有宿主机内核给出的全特权（包括获取全量 Linux Capabilities 并可直接操作宿主机裸设备）。本关已为你开启全量通用 Bash 交互控制台，请遵循左侧<b>详细攻防解题指南</b>，手动在右侧 Bash 终端中完成设备挂载与 <code>chroot</code> 切换上下文的利用实操！
                    </p>
                </div>

                <div id="sandbox_alert_box">
                    <?php if (!empty($flag_result)) echo $flag_result; ?>
                    <?php if (!empty($sandbox_msg)) echo $sandbox_msg; ?>
                </div>
                
                <div class="cyber-header-card" style="border-left:4px solid #10b981; margin-bottom: 24px; display:flex; flex-direction:column; gap:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <div>
                            <h4 style="margin:0; color:var(--text-primary); font-weight:800; display:flex; align-items:center; gap:8px;">
                                <i class="fa fa-server" style="color:#10b981;"></i> 动态靶场环境控制面板
                                <span id="sandbox_status_badge" class="sandbox-status-badge <?php echo $is_sandbox_running ? 'status-running' : 'status-stopped'; ?>">
                                    <span class="status-dot"></span>
                                    <span id="sandbox_status_text"><?php echo $is_sandbox_running ? '运行中 (Running)' : '已停止 (Stopped)'; ?></span>
                                    <span id="sandbox_uptime" style="font-size:11px; opacity:0.85; margin-left:4px; font-weight:normal;"><?php echo $is_sandbox_running && !empty($sandbox_uptime) ? '(' . htmlspecialchars($sandbox_uptime) . ')' : ''; ?></span>
                                </span>
                            </h4>
                            <p style="margin:5px 0 0 0; color:var(--text-secondary); font-size:13px;">实战演练前，请先点击启动。系统将为您分配专属的隔离沙箱 <code>pikachu-lab-cve-escape</code>，用完后请销毁。</p>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <button type="button" id="btn_start_sandbox" onclick="manageSandbox('start')" class="btn btn-sm btn-success" style="border-radius:6px; font-weight:700; padding:8px 16px; transition:all 0.2s;">
                                <i class="fa fa-play"></i> 启动真实沙箱
                            </button>
                            <button type="button" id="btn_stop_sandbox" onclick="manageSandbox('stop')" class="btn btn-sm btn-danger" style="border-radius:6px; font-weight:700; padding:8px 16px; transition:all 0.2s;">
                                <i class="fa fa-trash"></i> 销毁沙箱环境
                            </button>
                            <button type="button" onclick="checkSandboxStatus(true)" class="btn btn-sm btn-default" style="border-radius:6px; font-weight:600; padding:8px 12px;" title="立即刷新容器状态">
                                <i id="icon_refresh_status" class="fa fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                    
                    <details class="cyber-details-box">
                        <summary class="cyber-details-summary">
                            <i class="fa fa-cogs" style="color:#06b6d4;"></i> 展开查看后端实际触发的物理机指令 (自动隐藏 Flag 值)
                        </summary>
                        <div class="cyber-details-body">docker run -d \
  --name pikachu-lab-cve-escape \
  docker.m.daocloud.io/library/ubuntu:22.04 \
  sleep infinity

docker exec pikachu-lab-cve-escape bash -c "echo 'flag{**********}' > /etc/docker_cve_flag.txt"</div>
                    </details>
                </div>

                                    <div class="row">
                                        <!-- Left Panel: Detailed Step-by-Step Instructions & Tips -->
                    <div class="col-md-5">
                        <div class="cyber-guide-section">
                            <h4 class="cyber-section-title">
                                <i class="fa fa-book" style="color:#06b6d4;"></i> 原理解析与安全防御加固指南
                            </h4>

                            <div class="cyber-callout-danger">
                                <h5 style="color:#ef4444; margin-top:0; font-weight:700;"><i class="fa fa-exclamation-triangle"></i> 漏洞产生根源</h5>
                                <p>容器本质上只是限制了视图和资源的宿主机进程，它共享了宿主机的内核。当内核本身存在严重漏洞（如 DirtyPipe CVE-2022-0847）或容器运行时存在漏洞（如 runc CVE-2019-5736）时，攻击者可以通过特定的越权技术直接覆写宿主机上的只读文件（如 /etc/passwd）或拦截 runc 执行，最终完全破坏隔离边界并实现逃逸。</p>
                            </div>

                            <div class="cyber-callout-success">
                                <h5 style="color:#10b981; margin-top:0; font-weight:700;"><i class="fa fa-shield"></i> 最佳防御与修复方案</h5>
                                <ol>
                                    <li>及时更新宿主机内核系统和 Docker Daemon 到最新稳定版本。</li>
                                    <li>使用轻量级虚拟化容器（如 Kata Containers、gVisor）替代原生容器，加强内核层隔离。</li>
                                    <li>启用 Seccomp 限制不必要的危险系统调用（如 splice）。</li>
                                </ol>
                            </div>

                            <!-- Step 01 -->
                            <div class="cyber-step-card">
                                <div class="cyber-step-header">
                                    <div class="cyber-step-badge">01</div>
                                    <div class="cyber-step-title">探测系统内核版本</div>
                                </div>
                                <div class="cyber-principle-box">
                                    <b>【实战操作】</b> 首先通过 uname -a 检查当前共享宿主机内核的版本，确认是否处于高危漏洞影响范围内。
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">uname -a</span>
                                    <button type="button" class="cyber-copy-btn" onclick="fillCmd('uname -a')">填入终端</button>
                                </div>
                            </div>

                            <!-- Step 02 -->
                            <div class="cyber-step-card">
                                <div class="cyber-step-header">
                                    <div class="cyber-step-badge">02</div>
                                    <div class="cyber-step-title">下载并准备 Exploit (DirtyPipe)</div>
                                </div>
                                <div class="cyber-principle-box">
                                    <b>【实战操作】</b> 模拟将编译好的漏洞利用程序放入容器。在此靶场中，由于无法直接编译，我们使用特定指令模拟脏管覆盖流程。
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">echo 'Mock downloading exploit...' && ls -l /tmp</span>
                                    <button type="button" class="cyber-copy-btn" onclick="fillCmd('echo ''Mock downloading exploit...'' && ls -l /tmp')">填入终端</button>
                                </div>
                            </div>

                            <!-- Step 03 -->
                            <div class="cyber-step-card">
                                <div class="cyber-step-header">
                                    <div class="cyber-step-badge" style="background:linear-gradient(135deg, #ef4444, #dc2626);">03</div>
                                    <div class="cyber-step-title" style="color:#ef4444;">触发漏洞并获取 Flag</div>
                                </div>
                                <div class="cyber-principle-box">
                                    <b>【实战操作】</b> 运行利用程序，突破内核限制读取宿主机高权限区域的文件（本次演示读取特定的逃逸 Flag）。
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">cat /tmp/cve_escape_flag.txt</span>
                                    <button type="button" class="cyber-copy-btn" onclick="fillCmd('cat /tmp/cve_escape_flag.txt')">填入终端</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Panel: Universal Interactive Bash Command Terminal -->
                    <div class="col-md-7">
                        <div class="cyber-terminal-card">
                            <div class="cyber-terminal-bar">
                                <div class="cyber-dots-group">
                                    <span class="cyber-dot cyber-dot-red"></span>
                                    <span class="cyber-dot cyber-dot-yellow"></span>
                                    <span class="cyber-dot cyber-dot-green"></span>
                                    <span class="cyber-terminal-title">universal-bash-tty - Full Linux Shell Engine</span>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn btn-xs btn-default" onclick="fillCmd('help')" style="background:#1e293b; color:#38bdf8; border:1px solid #334155;">
                                        <i class="fa fa-question-circle"></i> 指令帮助 (help)
                                    </button>
                                    <form method="POST" style="margin:0; display:inline;">
                                        <button type="submit" name="clear_history" class="btn btn-xs btn-link" style="color:#64748b; text-decoration:none;" title="清空终端历史">
                                            <i class="fa fa-trash"></i> 清空控制台
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Terminal Output Screen -->
                            <div class="cyber-terminal-screen" id="term_body">
                                <?php foreach ($_SESSION['docker_priv_history'] as $item): ?>
                                    <?php if ($item['type'] === 'sys'): ?>
                                        <div style="color: #64748b; margin-bottom: 12px;"><?php echo nl2br(htmlspecialchars($item['content'])); ?></div>
                                    <?php else: ?>
                                        <div>
                                            <span class="term-prompt">root@privileged-sandbox:<?php echo htmlspecialchars($item['cwd'] ?? '/root'); ?># </span>
                                            <span class="term-user-cmd"><?php echo htmlspecialchars($item['cmd']); ?></span>
                                        </div>
                                        <div class="term-output"><?php echo htmlspecialchars($item['output']); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <!-- Terminal Command Input Bar -->
                            <form method="POST" class="cyber-terminal-input-bar">
                                <span style="color:#10b981; font-weight:700; font-family:monospace; font-size:13px;">root@sandbox:<?php echo htmlspecialchars($_SESSION['docker_priv_cwd']); ?>#</span>
                                <input type="text" id="cmd_input" name="cmd_input" placeholder="支持所有 Linux 命令（如 fdisk -l / help / ls / cd / cat / uname / env ...）" autocomplete="off" required>
                                <button type="submit" name="exec_cmd" class="btn btn-sm btn-info" style="border-radius:6px; background:linear-gradient(135deg, #06b6d4, #2563eb); border:none; padding:8px 18px; font-weight:700;">
                                    <i class="fa fa-terminal"></i> 执行
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Analysis & Defense Guidelines Section -->
                <div class="cyber-guide-section" style="margin-top:24px;">
                    <h3 class="cyber-section-title">
                        <i class="fa fa-shield" style="color:#10b981;"></i> 原理解析与安全防御加固指南
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div style="background:var(--bg-secondary); padding:18px; border-radius:10px; border-left:4px solid #ef4444; height:100%;">
                                <h5 style="font-weight:800; margin-top:0; color:#ef4444;"><i class="fa fa-exclamation-triangle"></i> 漏洞产生根源</h5>
                                <p style="font-size:13px; color:var(--text-secondary); line-height:1.7; margin-bottom:0;">
                                    当以 <code>--privileged</code> 标记启动容器时，Docker 会将宿主机的全部内核 Capabilities 赋予容器，同时强行取消 AppArmor/Seccomp 安全沙箱限制。容器可直接读写裸块设备（如 <code>/dev/vda1</code>），打破任何隔离屏障。
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div style="background:var(--bg-secondary); padding:18px; border-radius:10px; border-left:4px solid #10b981; height:100%;">
                                <h5 style="font-weight:800; margin-top:0; color:#10b981;"><i class="fa fa-check-circle"></i> 最佳防御与修复方案</h5>
                                <p style="font-size:13px; color:var(--text-secondary); line-height:1.7; margin-bottom:0;">
                                    1. <strong>严禁在生产环境中滥用 <code>--privileged</code> 参数</strong>。<br/>
                                    2. <strong>细粒度按需分配 Capabilities</strong>：如仅分配 <code>--cap-add=NET_ADMIN</code>。<br/>
                                    3. <strong>部署云原生安全审计工具 (Falco)</strong>：监控拦截容器内任意挂载设备和直接读写磁盘的行为。
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Bar Footer -->
                    <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:16px;">
                        <a href="dockerlab.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 返回 Docker Lab 首页</a>
                        <a href="docker_sock_escape.php" class="btn btn-info" style="border-radius:8px; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none; padding:8px 16px; font-weight:700;">下一关：Docker Socket 挂载逃逸 <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>

const CURRENT_CONTAINER_NAME = 'pikachu-lab-cve-escape';
let isActionPending = false;

function updateBadge(isRunning, uptime) {
    const badge = document.getElementById('sandbox_status_badge');
    const textSpan = document.getElementById('sandbox_status_text');
    const uptimeSpan = document.getElementById('sandbox_uptime');
    if (!badge || !textSpan) return;

    badge.className = 'sandbox-status-badge ' + (isRunning ? 'status-running' : 'status-stopped');
    textSpan.textContent = isRunning ? '运行中 (Running)' : '已停止 (Stopped)';
    if (uptimeSpan) {
        uptimeSpan.textContent = isRunning && uptime ? '(' + uptime + ')' : '';
    }
}

function checkSandboxStatus(manual = false) {
    if (isActionPending) return;
    const refIcon = document.getElementById('icon_refresh_status');
    if (manual && refIcon) refIcon.classList.add('fa-spin');

    fetch('dockerlab_sandbox_api.php?action=status&name=' + encodeURIComponent(CURRENT_CONTAINER_NAME))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateBadge(data.running, data.uptime);
            }
        })
        .catch(err => console.error('Status check error:', err))
        .finally(() => {
            if (manual && refIcon) {
                setTimeout(() => refIcon.classList.remove('fa-spin'), 400);
            }
        });
}

function manageSandbox(act) {
    if (isActionPending) return;
    isActionPending = true;

    const btnStart = document.getElementById('btn_start_sandbox');
    const btnStop = document.getElementById('btn_stop_sandbox');
    const alertBox = document.getElementById('sandbox_alert_box');
    const badge = document.getElementById('sandbox_status_badge');
    const textSpan = document.getElementById('sandbox_status_text');
    const uptimeSpan = document.getElementById('sandbox_uptime');

    if (badge) badge.className = 'sandbox-status-badge status-processing';
    if (textSpan) textSpan.textContent = act === 'start' ? '正在启动...' : '正在销毁...';
    if (uptimeSpan) uptimeSpan.textContent = '';

    if (btnStart) btnStart.disabled = true;
    if (btnStop) btnStop.disabled = true;

    const targetBtn = act === 'start' ? btnStart : btnStop;
    const origHtml = targetBtn ? targetBtn.innerHTML : '';
    if (targetBtn) {
        targetBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + (act === 'start' ? '启动中...' : '销毁中...');
    }

    const formData = new FormData();
    formData.append('action', act);
    formData.append('name', CURRENT_CONTAINER_NAME);

    fetch('dockerlab_sandbox_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateBadge(data.running, data.uptime);
            if (alertBox && data.message) {
                const alertType = data.running ? 'alert-success' : 'alert-warning';
                const alertIcon = data.running ? 'fa-check-circle' : 'fa-stop';
                alertBox.innerHTML = `<div class="alert ${alertType}" style="border-radius:10px; font-weight:700; margin-bottom:18px;">
                    <i class="fa ${alertIcon}"></i> ${data.message}
                </div>`;
            }
        } else {
            if (alertBox) {
                alertBox.innerHTML = `<div class="alert alert-danger" style="border-radius:10px; font-weight:700; margin-bottom:18px;">
                    <i class="fa fa-times-circle"></i> ${data.error || '操作失败'}
                </div>`;
            }
            checkSandboxStatus();
        }
    })
    .catch(err => {
        if (alertBox) {
            alertBox.innerHTML = `<div class="alert alert-danger" style="border-radius:10px; font-weight:700; margin-bottom:18px;">
                <i class="fa fa-times-circle"></i> 请求发生异常，请检查网络或后端容器服务。
            </div>`;
        }
        checkSandboxStatus();
    })
    .finally(() => {
        isActionPending = false;
        if (targetBtn) targetBtn.innerHTML = origHtml;
        if (btnStart) btnStart.disabled = false;
        if (btnStop) btnStop.disabled = false;
    });
}

// Start real-time polling every 2.5s
setInterval(checkSandboxStatus, 2500);


function fillCmd(cmdText) {
    var input = document.getElementById('cmd_input');
    if (input) {
        input.value = cmdText;
        input.focus();
    }
}

// Auto scroll terminal to bottom on load
window.onload = function() {
    var body = document.getElementById('term_body');
    if (body) {
        body.scrollTop = body.scrollHeight;
    }
};
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>























