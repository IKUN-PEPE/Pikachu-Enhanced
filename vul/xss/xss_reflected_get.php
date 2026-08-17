<?php
/**
 * Pikachu-Enhanced v2.0 - 反射型 XSS (GET) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
$xss_triggered = false;
$user_input = $_GET['message'] ?? '';

if (isset($_GET['submit'])) {
    if (empty($user_input)) {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 请在上方输入框输入内容（例如输入 <code>kobe</code> 或 XSS Payload）。</div>";
    } else {
        if (preg_match('/<script|onerror|onload|javascript:|alert\(/i', $user_input)) {
            $xss_triggered = true;
        }
        
        if ($user_input === 'kobe') {
            $html = "<div class='alert alert-success' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-star'></i> 🏀 致敬传奇：Kobe Bryant</h4>
                <p>愿你和 <b>kobe</b> 一样，永远年轻，永远热血沸腾！</p>
                <img src='{$PIKA_ROOT_DIR}assets/images/nbaplayer/kobe.png' style='max-width:240px; border-radius:10px; margin-top:8px;' />
            </div>";
        } else {
            $html = "<div class='alert alert-info' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-commenting'></i> 查询结果回显：</h4>
                <p style='margin-bottom:0;'>Who is <span class='text-danger' style='font-size:15px; font-weight:bold;'>{$user_input}</span> ? I don't care!</p>
            </div>";
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">反射型 XSS (GET) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="目标参数未经过滤直接拼接进 HTML Body。直接输入 <script>alert(1)</script> 即可触发。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <!-- 头部卡片 -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 反射型 XSS 漏洞攻防教学 (GET 方式)
                        <span class="cyber-badge-chip">客户端注入 · 基础篇 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        反射型跨站脚本（Reflected XSS）是指应用程序在接收到客户端的恶意输入后，<b>未经过滤或转义就直接将该输入反射回显给浏览器渲染执行</b>。因为攻击 Payload 携带在 URL 查询参数中，攻击者通常通过钓鱼邮件、恶意链接等方式诱骗受害者点击触发。
                    </p>
                </div>

                <div class="row">
                    <!-- 左侧：攻防实战交互区 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 漏洞交互与演练控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入你想查询的球星名称或 XSS Payload 测试反射回显：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">球星名称 / Payload 输入框：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="message_input" name="message" value="<?php echo htmlspecialchars($user_input); ?>" placeholder="输入球星或 Payload，如 <script>alert(1)</script>" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-paper-plane"></i> 提交查询
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('kobe')"><i class="fa fa-star" style="color:var(--warning);"></i> 填入业务正常值: kobe</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('<script>alert(\'FLAG{XSS_REFLECTED_GET_MASTER}\')</script>')"><i class="fa fa-bolt"></i> 经典 Script Payload</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('<img src=x onerror=alert(document.domain)>')"><i class="fa fa-image"></i> 经典 Img Payload</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <!-- 结果回显区 -->
                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 服务端实时响应回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 尚未提交数据，请在上方输入框填入内容后点击提交
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($xss_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发反射型 XSS 漏洞！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">服务端已将未经编码的脚本原样反射在 HTML 结构中执行。恭喜获得通过凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{XSS_REFLECTED_GET_MASTER}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 右侧：教学模式与攻防指南 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <!-- 步骤 1 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 漏洞原理与数据流分析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>数据流向：</b> <span class="flow-step-tag">浏览器 URL</span> ➔ <span class="flow-step-tag">$_GET['message']</span> ➔ <span class="flow-step-tag">拼接 HTML</span> ➔ <span class="flow-step-tag">浏览器渲染执行</span></p>
                                    <p style="margin-bottom:0;">当输出位置处于 <b>HTML Body 标签上下文</b> 中时，浏览器 HTML 解析器会将 <code>&lt;script&gt;</code>、<code>&lt;img&gt;</code>、<code>&lt;svg&gt;</code> 等标签识别为新的 DOM 节点，并直接执行其中嵌入的 JavaScript 代码。</p>
                                </div>
                            </div>

                            <!-- 步骤 2 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用测试 Payload 与实战演练</h4>
                                
                                <div style="margin-bottom:12px;">
                                    <span style="font-size:12.5px; color:var(--text-secondary); font-weight:600;">Payload A: 经典 Script 标签注入</span>
                                    <div class="cyber-code-container">
                                        <span class="cyber-code-text">&lt;script&gt;alert('FLAG{XSS_REFLECTED_GET_MASTER}')&lt;/script&gt;</span>
                                        <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;script&gt;alert(\'FLAG{XSS_REFLECTED_GET_MASTER}\')&lt;/script&gt;')">复制</button>
                                    </div>
                                </div>

                                <div>
                                    <span style="font-size:12.5px; color:var(--text-secondary); font-weight:600;">Payload B: 属性事件驱动型注入 (无需闭合)</span>
                                    <div class="cyber-code-container">
                                        <span class="cyber-code-text">&lt;img src=1 onerror=alert(document.cookie)&gt;</span>
                                        <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;img src=1 onerror=alert(document.cookie)&gt;')">复制</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 步骤 3 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御方案</h4>
                                
                                <details class="cyber-details-box">
                                    <summary class="cyber-details-summary">查看漏洞代码 (Vulnerable Code)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ❌ 危险：未对 $_GET 进行任何实体转义即直接回显输出
$html .= "&lt;p&gt;Who is " . $_GET['message'] . ", i don't care!&lt;/p&gt;";
</pre>
                                    </div>
                                </details>

                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">查看安全防御代码 (Remediated Code)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 正确：在 HTML 实体上下文中，使用 htmlspecialchars 进行安全转义
$safe_msg = htmlspecialchars($_GET['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
$html .= "&lt;p&gt;Who is " . $safe_msg . ", i don't care!&lt;/p&gt;";
</pre>
                                    </div>
                                </details>

                                <div class="cyber-callout-success" style="margin-top:12px; margin-bottom:0;">
                                    <h5 style="margin:0 0 6px 0; font-size:13.5px; font-weight:700;"><i class="fa fa-shield"></i> 纵深防御建议</h5>
                                    <ul style="margin:0; padding-left:18px; font-size:12.5px; color:var(--text-secondary); line-height:1.6;">
                                        <li>开启 <b>Content-Security-Policy (CSP)</b> 响应头，禁止未经授权的内联脚本执行；</li>
                                        <li>对敏感 Cookie 开启 <code>HttpOnly</code> 标记，防止会话凭据被 XSS 窃取。</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) {
    document.getElementById('message_input').value = val;
}
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Payload 已复制到剪贴板！');
    }, function() {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Payload 已复制到剪贴板！');
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
