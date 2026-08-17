<?php
/**
 * Pikachu-Enhanced v2.0 - 服务端模板注入 (Server-Side Template Injection / SSTI RCE) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[50] = 'active open';
$ACTIVE[228] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$user_name = $_POST['name'] ?? '';

if (isset($_POST['submit']) && $user_name !== '') {
    // 模拟服务端模板引擎对 {{ ... }} 表达式进行动态解析
    $template = "Hello, {{ " . $user_name . " }}! Welcome to Pikachu Template Engine.";
    
    $rendered = preg_replace_callback('/\{\{(.*?)\}\}/s', function($matches) {
        $code = trim($matches[1]);
        ob_start();
        try {
            eval('echo ' . $code . ';');
            $res = ob_get_clean();
            return $res;
        } catch (Throwable $t) {
            ob_end_clean();
            return "[Template Error: " . htmlspecialchars($t->getMessage()) . "]";
        }
    }, $template);

    $html = "<div class='alert alert-info' style='margin:0; border-radius:8px; font-weight:700;'>
        <i class='fa fa-code'></i> 模板引擎渲染输出 (Rendered Result)：
        <div style='margin-top:8px; padding:12px; background:#090d16; color:#38bdf8; border-radius:6px; font-family:monospace; font-size:13px;'>" . $rendered . "</div>
    </div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="rce.php">RCE</a></li>
                <li class="active">服务端模板注入 (SSTI)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 SSTI 探测技巧" data-content="先输入 7*7 测试是否回显 49，再使用 system('whoami') 逃逸模板沙箱实现 RCE！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        💻 关卡 5: 服务端模板注入漏洞攻防教学 (SSTI to RCE)
                        <span class="cyber-badge-chip">RCE · SSTI 表达式逃逸 · 模板沙箱突破 · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        服务端模板注入（SSTI）产生于服务端直接将未经转义的用户输入与模板静态字符串进行<b>字符串拼接（String Concatenation）</b>后送入模板引擎（如 Jinja2、Twig、Smarty、Velocity）进行编译渲染。攻击者利用 <code>{{ ... }}</code> 语法，通过模板表达式沙箱逃逸链最终获取系统底层 RCE 权限！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Payload Chips -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-magic" style="color:var(--primary);"></i> 模板变量渲染控制台
                            </h4>

                            <form method="POST" action="rce_ssti.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">用户昵称输入 (拼接进 <code>Hello, {{ input }}!</code>)：</label>
                                    <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入用户名或模板表达式..." style="font-family:monospace;" required />
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试 SSTI 注入载荷：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillName('Pikachu')">
                                            <i class="fa fa-user" style="color:#06b6d4;"></i> 正常文本：<code>Pikachu</code> (回显 Hello, Pikachu!)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillName('7*7')">
                                            <i class="fa fa-calculator" style="color:#f59e0b;"></i> <b>数学运算探测：</b> <code>7*7</code> (若回显 49 则确认存在 SSTI)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillName('system(\'whoami\')')">
                                            <i class="fa fa-bolt" style="color:#ef4444;"></i> <b>执行系统命令：</b> <code>system('whoami')</code> -> RCE
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillName('phpversion() . \' on \' . php_uname()')">
                                            <i class="fa fa-server" style="color:#8b5cf6;"></i> <b>读取系统环境：</b> <code>phpversion() . ' on ' . php_uname()</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 发起模板编译与渲染
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Render Output -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 模板引擎渲染结果
                            </h4>

                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-code" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    在左侧输入模板表达式并点击渲染
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="rce_blind.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：无回显盲 RCE</a>
                    <a href="rce.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回 RCE 演练大厅 <i class="fa fa-th-large"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillName(v) {
    document.getElementById('name_input').value = v;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
