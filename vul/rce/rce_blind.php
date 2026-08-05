<?php
/**
 * Pikachu-Enhanced: Blind RCE / Out-of-Band Lab
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[50] = 'active open';
$ACTIVE[224] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
if (isset($_POST['submit']) && !empty($_POST['host'])) {
    $host = $_POST['host'];
    
    $startTime = microtime(true);
    
    // Asynchronous / Silent Command Execution (output redirected to /dev/null or NUL)
    if (stristr(php_uname('s'), 'Windows NT')) {
        $cmd = "ping -n 1 " . $host . " > NUL 2>&1";
    } else {
        $cmd = "ping -c 1 " . $host . " > /dev/null 2>&1";
    }
    
    // Execute command silently
    system($cmd);
    
    $executionTime = round(microtime(true) - $startTime, 2);

    $html = "<div class='alert alert-warning'><i class='fa fa-clock-o'></i> <strong>后台异步任务处理完成：</strong> 命令已在后台静默执行。页面响应耗时：<code>" . $executionTime . " 秒</code>。（注意：页面不会返回任何命令行回显内容）</div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="vul">
                <h2>🙈 无回显命令盲注演练 (Blind RCE / Time-Based OOB)</h2>
                <p>
                    本关卡模拟了无回显场景（如后台定时任务、队列处理系统）。服务器将输出重定向至 <code>/dev/null</code>，页面**绝对不返回任何回显数据**。
                </p>

                <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                    <strong style="color: var(--text-primary);"><i class="fa fa-eye-slash" style="color: #ef4444;"></i> 无回显 RCE 验证思路：</strong>
                    <ul style="margin: 8px 0 0 20px; color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                        <li><strong>时间延迟验证 (Time-Based)：</strong> 注入 <code>127.0.0.1; sleep 3</code> 或 <code>127.0.0.1 & timeout 3</code>，观察页面响应时间是否显著增加。</li>
                        <li><strong>带外通信 (Out-of-Band / OOB)：</strong> 利用 <code>curl http://your-dnslog.com</code> 或 <code>ping</code> 外发请求至控制端检测数据。</li>
                        <li><strong>写入静态文件：</strong> 注入 <code>127.0.0.1; id > test.txt</code> 后直接访问静态路径读取结果。</li>
                    </ul>
                </div>

                <form method="post" action="" style="display: flex; gap: 10px; max-width: 650px; margin-bottom: 20px;">
                    <input type="text" name="host" class="form-control" placeholder="输入主机名或 Payload (例如: 127.0.0.1; sleep 3)" style="flex: 1;" required />
                    <input type="submit" name="submit" class="btn btn-primary" value="提交后台任务" />
                </form>

                <?php echo $html; ?>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
