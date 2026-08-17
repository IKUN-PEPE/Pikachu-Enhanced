<?php
/**
 * Pikachu-Enhanced v2.0 - PHP 代码注入 (eval() / assert() RCE) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[50] = 'active open';
$ACTIVE[53] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$output = "";
$error_log = "";
$user_code = $_POST['txt'] ?? '';

if (isset($_POST['submit']) && $user_code !== '') {
    // 【核心漏洞点】：服务端未做任何白名单过滤，直接将用户输入送入 eval() 执行
    ob_start();
    try {
        // 在沙箱环境中执行用户提交的 PHP 代码
        $eval_result = @eval($user_code);
        $output = ob_get_clean();
        if ($eval_result === false && empty($output)) {
            $error_log = "PHP eval() 解析错误或语法异常！请检查分号 ; 与闭合。";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        $error_log = "执行异常: " . $e->getMessage();
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="rce.php">RCE</a></li>
                <li class="active">代码注入 (eval)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 代码执行技巧" data-content="直接输入完整的 PHP 代码并以分号结尾，例如：phpinfo(); 或 echo 'Hello'; 或 system('whoami');">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        💻 2. PHP 代码注入漏洞攻防教学 (eval() 任意代码执行)
                        <span class="cyber-badge-chip">RCE · eval() 执行 · 内存沙箱 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        代码注入（Code Injection）与系统命令执行（Command Injection）不同，代码注入是指用户的输入被直接作为<b>服务端编程语言本身的代码（如 PHP eval、assert、Python exec 等）</b>进行解析执行。攻击者不仅可以调用系统命令（如 <code>system()</code>），还可以直接读写服务端文件、调用内置类和扩展库！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Code Input & Quick Payload Chips -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> PHP 代码执行终端
                            </h4>

                            <form method="POST" action="rce_eval.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入要执行的 PHP 语句：</label>
                                    <textarea class="form-control" id="code_input" name="txt" rows="4" placeholder="例如：phpinfo(); 或 system('whoami');" style="font-family:'Fira Code', monospace; font-size:13px;" required><?php echo htmlspecialchars($user_code); ?></textarea>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试经典代码注入 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillCode('echo \'PHP 运行环境正常，当前时间: \' . date(\'Y-m-d H:i:s\');')">
                                            <i class="fa fa-info-circle" style="color:#06b6d4;"></i> <b>基础输出：</b> 打印当前时间 <code>date()</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillCode('system(\'whoami\');')">
                                            <i class="fa fa-user" style="color:#f59e0b;"></i> <b>执行系统命令：</b> <code>system('whoami');</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillCode('system(\'id; uname -a\');')">
                                            <i class="fa fa-server" style="color:#8b5cf6;"></i> <b>系统信息探测：</b> <code>system('id; uname -a');</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillCode('highlight_string(file_get_contents(__FILE__));')">
                                            <i class="fa fa-code" style="color:#10b981;"></i> <b>读取当前源码：</b> <code>highlight_string(...)</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 执行 PHP 动态代码
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Execution Result -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 代码执行标准输出 (Stdout)
                            </h4>

                            <?php if (!empty($error_log)): ?>
                                <div class="alert alert-danger" style="border-radius:8px; font-size:12.5px; font-weight:600; margin-bottom:12px;">
                                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_log); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($output)): ?>
                                <pre style="background:#090d16; color:#10b981; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12.5px; line-height:1.6; max-height:280px; overflow-y:auto;"><?php echo htmlspecialchars($output); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-terminal" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    在左侧输入 PHP 代码或点击快捷 Payload 进行测试
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="rce_ping.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：命令执行 (Ping)</a>
                    <a href="rce.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回 RCE 演练大厅 <i class="fa fa-th-large"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCode(val) {
    document.getElementById('code_input').value = val;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
