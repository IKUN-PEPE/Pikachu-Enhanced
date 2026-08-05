<?php
/**
 * Pikachu-Enhanced: SSTI Server-Side Template Injection RCE Lab
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[50] = 'active open';
$ACTIVE[223] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
if (isset($_POST['name'])) {
    $name = $_POST['name'];
    
    // Simulate SSTI Expression Engine Processing
    // Evaluates {{ ... }} expressions inside user input
    $template = "Hello, {{ " . $name . " }}! Welcome to Pikachu Template Engine.";
    
    // Insecure template expression parser using eval/preg_replace_callback
    $rendered = preg_replace_callback('/\{\{(.*?)\}\}/s', function($matches) {
        $code = trim($matches[1]);
        // Evaluate expression
        ob_start();
        try {
            eval('echo ' . $code . ';');
            $res = ob_get_clean();
            return $res;
        } catch (Throwable $t) {
            ob_end_clean();
            return "[Template Render Error: " . htmlspecialchars($t->getMessage()) . "]";
        }
    }, $template);

    $html = "<div class='alert alert-info'><strong>模板渲染结果 (Rendered Output)：</strong></div>";
    $html .= "<div style='padding: 20px; background: var(--bg-secondary); border-radius: 8px; font-size: 16px; border: 1px solid var(--border-color); color: var(--text-primary);'>" . $rendered . "</div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="vul">
                <h2>💻 SSTI 服务器端模板注入演练 (Server-Side Template Injection RCE)</h2>
                <p>
                    本关卡展示了现代 Web 应用在解析用户传入的动态模板表达式时，因模板引擎拼接不当引发的 SSTI 漏洞。输入表达式会被放入 <code>{{ ... }}</code> 渲染。
                </p>

                <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                    <strong style="color: var(--text-primary);"><i class="fa fa-code" style="color: #a855f7;"></i> SSTI 测试 Payload 示例：</strong>
                    <ul style="margin: 8px 0 0 20px; color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                        <li><strong>数学逻辑运算：</strong> 输入 <code>7*7</code> -> 返回 <code>Hello, 49!</code></li>
                        <li><strong>执行 PHP 代码：</strong> 输入 <code>system('id')</code> -> 成功逃逸模板沙箱执行系统命令！</li>
                        <li><strong>获取服务器敏感环境：</strong> 输入 <code>phpversion()</code> 或 <code>getcwd()</code></li>
                    </ul>
                </div>

                <form method="post" action="" style="display: flex; gap: 10px; max-width: 650px; margin-bottom: 20px;">
                    <input type="text" name="name" class="form-control" placeholder="输入用户名或模板表达式 (例如: system('id'))" style="flex: 1;" required />
                    <input type="submit" class="btn btn-primary" value="渲染模板" />
                </form>

                <?php echo $html; ?>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
