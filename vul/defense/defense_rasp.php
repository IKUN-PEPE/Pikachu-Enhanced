<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Level 2: RASP Runtime Hook & Stack Trace Logger
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[223] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$hook_target = isset($_POST['hook_target']) ? $_POST['hook_target'] : 'system';
$rasp_enabled = isset($_POST['rasp_enabled']) ? $_POST['rasp_enabled'] === '1' : true;
$payload_input = isset($_POST['payload_input']) ? $_POST['payload_input'] : 'system("whoami");';

$stack_trace_output = null;

if (isset($_POST['run_rasp_test'])) {
    $blocked = false;
    $call_stack = [];

    $call_stack[] = "0: [HTTP Request] POST /vul/defense/defense_rasp.php";
    $call_stack[] = "1: [PHP Engine] zend_execute() -> main()";

    if (strpos($payload_input, 'eval') !== false || strpos($payload_input, 'assert') !== false) {
        $call_stack[] = "2: [Dynamic Eval] zend_compile_string() -> Executing dynamic code block";
    }

    if ($hook_target === 'system' || strpos($payload_input, 'system') !== false) {
        $call_stack[] = "3: [Internal Function] php_exec()";
        $call_stack[] = "4: [C Library] proc_open() / popen() -> C-Level Process Spawner";
        $call_stack[] = "5: [RASP HOOK POINT] Hooked Function: system() [ID: RASP-HOOK-SYS-01]";
    } elseif ($hook_target === 'file' || strpos($payload_input, 'etc') !== false) {
        $call_stack[] = "3: [Internal Function] php_stream_open_wrapper_ex()";
        $call_stack[] = "4: [RASP HOOK POINT] Hooked Function: file_get_contents() [ID: RASP-HOOK-FILE-02]";
    }

    if ($rasp_enabled) {
        $blocked = true;
        $call_stack[] = "6: [RASP DECISION] 🚨 THREAT DETECTED: Illegal process spawn attempt in web context!";
        $call_stack[] = "7: [RASP ACTION] Blocked execution. Threw SecurityException before C-level proc_open().";
    } else {
        $call_stack[] = "6: [RASP DECISION] RASP Protection Disabled. Executing OS command directly...";
        $call_stack[] = "7: [OS EXECUTION] Command executed. Returned output: 'nt authority\\system'";
    }

    $stack_trace_output = [
        'blocked' => $blocked,
        'enabled' => $rasp_enabled,
        'stack' => $call_stack,
        'time' => date('Y-m-d H:i:s')
    ];
}
?>

<style>
.rasp-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #6d28d9 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
}
.rasp-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.stack-box {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.8;
    margin-top: 15px;
    border-left: 4px solid #a855f7;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="rasp-hero-banner">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <span class="label label-danger" style="background-color: #a855f7; font-size: 14px; border-radius: 6px;">LEVEL 2</span>
                            ⚡ RASP 运行时自我保护与危险函数 StackTrace 拦截模拟器
                        </h1>
                        <p style="margin: 0; color: #ddd6fe; font-size: 14px;">
                            <strong>防守维度：</strong> Runtime Application Self-Protection (RASP) 内存字节码插桩与底层危险 API Hook
                        </p>
                    </div>
                    <a href="defense.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回蓝队总控大厅
                    </a>
                </div>
            </div>

            <!-- Theory -->
            <div class="rasp-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-bolt" style="color: #a855f7;"></i> RASP 为什么能防御 WAF 无法拦截的变形攻击？</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    传统 WAF 依赖网络流量层正则，容易被变形混淆（如 Base64 编码、反斜杠拼接 `s\y\s\t\e\m`）绕过。
                    而 **RASP (运行时自我保护)** 运行在 Web 应用程序内部（如 JVM 字节码插桩 / PHP C 语言扩展）。当程序尝试调用底层高危函数（如 `system()`、`proc_open()`、文件任意读取）时，RASP 在**真实函数即将落盘/调用底层操作系统之前切断执行链**，并抓取完整的调用堆栈 (Stack Trace)！
                </p>
            </div>

            <!-- Interactive Tester -->
            <div class="row">
                <div class="col-md-6">
                    <div class="rasp-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-cogs" style="color: #a855f7;"></i> RASP 内部 Hook 点控制终端</h3>
                        
                        <form method="post">
                            <div class="form-group">
                                <label style="font-weight: 700;">RASP 运行时保护开关：</label>
                                <select name="rasp_enabled" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                                    <option value="1" <?php echo $rasp_enabled?'selected':'';?>>🛡️ 开启 RASP (实时插桩 Hook 保护)</option>
                                    <option value="0" <?php echo !$rasp_enabled?'selected':'';?>>❌ 禁用 RASP (允许危险函数落地执行)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700;">监控的目标 Hook 敏感 API：</label>
                                <select name="hook_target" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                                    <option value="system" <?php echo $hook_target==='system'?'selected':'';?>>1. system() / proc_open() - 命令执行 Hook</option>
                                    <option value="file" <?php echo $hook_target==='file'?'selected':'';?>>2. file_get_contents() - 任意文件读取 Hook</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700;">模拟执行代码 / 变形 Payload：</label>
                                <textarea name="payload_input" class="form-control" rows="4" style="border-radius: 8px; font-family: monospace;" required><?php echo htmlspecialchars($payload_input); ?></textarea>
                            </div>

                            <button type="submit" name="run_rasp_test" class="btn btn-primary btn-block" style="background-color: #a855f7; border: none; border-radius: 8px; font-weight: 700;">
                                <i class="fa fa-flash"></i> 触发代码执行并抓取 RASP 函数堆栈
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rasp-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-code-fork" style="color: #6366f1;"></i> RASP 调用堆栈跟踪日志 (Call Stack Trace)</h3>
                        
                        <?php if ($stack_trace_output === null) { ?>
                            <div class="alert alert-info" style="border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-info-circle"></i> 点击左侧按钮测试在 RASP Hook 监控下危险函数的拦截表现。
                            </div>
                        <?php } else { ?>

                            <?php if ($stack_trace_output['blocked']) { ?>
                                <div class="alert alert-danger" style="border-radius: 8px; background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #dc2626;">
                                    <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-shield"></i> RASP SecurityException: 危险函数调用已被成功切断！</h4>
                                    <p style="margin-bottom: 0;">RASP 字节码插桩在底层 `proc_open` 调用前捕获到了非法系统命令衍生行为，阻止了系统命令落地。</p>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-warning" style="border-radius: 8px; background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: #b45309;">
                                    <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-exclamation-triangle"></i> RASP 保护已关闭：危险函数成功执行！</h4>
                                    <p style="margin-bottom: 0;">警告：没有 RASP 保护，变形 Payload 成功落盘并派生了系统 Shell 进程。</p>
                                </div>
                            <?php } ?>

                            <div class="stack-box">
                                <div style="color: #a855f7; font-weight: bold; margin-bottom: 8px;">--- RASP STACK TRACE MONITOR LOG ---</div>
                                <?php foreach ($stack_trace_output['stack'] as $line) {
                                    $color = strpos($line, 'HOOK') !== false ? '#fbbf24' : (strpos($line, 'DECISION') !== false || strpos($line, 'ACTION') !== false ? '#34d399' : '#a6accd');
                                    echo '<div style="color: ' . $color . ';">' . htmlspecialchars($line) . '</div>';
                                } ?>
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
