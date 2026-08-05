<?php
/**
 * Pikachu-Enhanced: Command Injection WAF Bypass Lab
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[50] = 'active open';
$ACTIVE[222] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
if (isset($_POST['submit']) && !empty($_POST['ip'])) {
    $ip = $_POST['ip'];
    
    // WAF Rules Demonstration
    $blacklisted_keywords = array('cat', 'flag', 'passwd', 'ls');
    $has_blocked_word = false;
    foreach ($blacklisted_keywords as $word) {
        if (stripos($ip, $word) !== false) {
            $has_blocked_word = $word;
            break;
        }
    }

    if (strpos($ip, ' ') !== false) {
        $html = "<div class='alert alert-danger'><i class='fa fa-ban'></i> <strong>WAF 拦截：</strong>检测到空格字符 (Space)！已被系统的黑名单防御过滤。</div>";
    } elseif ($has_blocked_word !== false) {
        $html = "<div class='alert alert-danger'><i class='fa fa-ban'></i> <strong>WAF 拦截：</strong>检测到敏感关键字 [<code>" . htmlspecialchars($has_blocked_word) . "</code>]！系统严禁执行该系统命令。</div>";
    } else {
        // Execute command
        if (stristr(php_uname('s'), 'Windows NT')) {
            $cmd = "ping " . $ip;
        } else {
            $cmd = "ping -c 2 " . $ip;
        }
        
        $res = shell_exec($cmd);
        $html = "<div class='alert alert-success'><strong>执行命令：</strong> <code>" . htmlspecialchars($cmd) . "</code></div>";
        $html .= "<pre>" . htmlspecialchars($res) . "</pre>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="vul">
                <h2>🛡️ 命令注入黑名单与 WAF 绕过演练 (WAF Bypass RCE)</h2>
                <p>
                    本关卡模拟了企业级应用在底层执行系统命令时部署的 WAF 规则。系统禁用了<strong>空格</strong>以及敏感关键字 <code>cat</code>, <code>flag</code>, <code>passwd</code>, <code>ls</code>。请尝试使用命令注入绕过技巧读取服务器数据。
                </p>

                <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                    <strong style="color: var(--text-primary);"><i class="fa fa-lightbulb-o" style="color: #f59e0b;"></i> 常用 WAF 绕过技巧提示：</strong>
                    <ul style="margin: 8px 0 0 20px; color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                        <li><strong>空格绕过：</strong> 使用 <code>${IFS}</code>、<code>$9</code> 或 <code>&lt;</code> 替代空格（如 <code>127.0.0.1;cat$IFS/etc/passwd</code>）</li>
                        <li><strong>单双引号分割：</strong> 使用 <code>c'a't</code> 或 <code>c"a"t</code> 绕过关键字匹配</li>
                        <li><strong>反斜杠拼接：</strong> 使用 <code>c\at</code> 或 <code>p\asswd</code></li>
                        <li><strong>通配符匹配：</strong> 使用 <code>/bin/c?t /etc/p?sswd</code> 绕过字符串扫描</li>
                    </ul>
                </div>

                <form method="post" action="" style="display: flex; gap: 10px; max-width: 650px; margin-bottom: 20px;">
                    <input type="text" name="ip" class="form-control" placeholder="输入 IP 地址 (例如: 127.0.0.1;c'a't${IFS}/etc/passwd)" style="flex: 1;" required />
                    <input type="submit" name="submit" class="btn btn-primary" value="执行测试" />
                </form>

                <?php echo $html; ?>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
