<?php
/**
 * Pikachu-Enhanced v2.0 - HTTP Host 报头注入 (Host Header Poisoning) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[125] = 'active open';
$ACTIVE[127] = 'active';

$mail_sent = false;
$poisoned = false;
$flag_msg = '';

// Flag Verification
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{Host_Header_Injection_Password_Reset_Poisoned}') {
        $_SESSION['hostheader_flag_solved'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 Host 报头投毒与密码重置劫持防御机制！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请修改 Host 报头并生成投毒邮件后获取对应 Flag！</div>";
    }
}

// Extract Host & Protocol headers
$host_raw = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$forwarded_host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
$effective_host = !empty($forwarded_host) ? $forwarded_host : $host_raw;

$scheme = 'http';
if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')) {
    $scheme = 'https';
}

$account = 'victim_admin@pikachu.com';
$reset_token = 'sec_token_' . substr(md5(time() . 'pikachu_reset_salt'), 0, 16);
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$reset_url = $scheme . '://' . $effective_host . $path . '/trust.php?reset_token=' . $reset_token;

// Handle Reset Request
if (isset($_POST['submit_reset'])) {
    $account = trim($_POST['account'] ?? 'victim_admin@pikachu.com');
    $mail_sent = true;
    
    // Check if Host header was poisoned (not standard localhost/127.0.0.1)
    if (!preg_match('/^(127\.0\.0\.1|localhost)(:\d+)?$/i', $effective_host)) {
        $poisoned = true;
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.host-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.host-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.mail-box {
    background: #020617;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 18px;
    color: #cbd5e1;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.7;
    margin-top: 16px;
}
.code-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 8px;
    padding: 12px 14px;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.6;
    color: #38bdf8;
    word-break: break-all;
}
.flow-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
}
.flow-num {
    width: 28px;
    height: 28px;
    background: #6366f1;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.flow-content h5 {
    margin: 0 0 4px 0;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
}
.flow-content p {
    margin: 0;
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.5;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="hostheader.php">Host Header</a></li>
                <li class="active">HTTP Host 报头盲信任与重置链接投毒</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示"
               data-content="使用 Burp Suite 或 curl 抓包，将请求报头中的 Host: 127.0.0.1:8765 修改为恶意域名（如 Host: evil-attacker.com），观察系统生成的重置邮件链接是否指向攻击者服务器！">
                <i class="fa fa-lightbulb-o text-warning"></i> 攻防提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Hero Banner -->
            <div class="host-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-shield" style="color:#818cf8;"></i> HTTP Host 报头注入与密码重置投毒 (Host Header Poisoning)
                    <span class="label label-danger" style="border-radius:12px; font-size:11px; padding:3px 10px;">高危 · 凭据劫持</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞机理：</b>服务端在为用户生成密码重置链接、OAuth 回调地址或邮件确认 URL 时，<b>盲目信任并直接读取客户端请求中传入的 <code>$_SERVER['HTTP_HOST']</code></b> 拼接绝对 URL。攻击者可通过篡改 Host 报头，诱导系统向受害者邮箱发送指向攻击者恶意服务器的重置链接，当受害者点击该邮件时，秘密重置凭证（Token）将直接泄露至攻击者日志中，造成账户被秒级劫持！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left Column: Password Reset Simulator & Mail Inbox -->
                <div class="col-md-5">
                    <div class="host-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-envelope" style="color:var(--primary);"></i> 密码重置邮件投递中枢 (Reset Portal)
                        </h4>

                        <!-- Request Headers Monitor -->
                        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:8px; padding:14px; margin-bottom:18px;">
                            <div style="font-size:12.5px; font-weight:700; color:var(--text-primary); margin-bottom:8px;">
                                <i class="fa fa-exchange"></i> 当前服务端捕获的客户端请求报头：
                            </div>
                            <table class="table table-bordered" style="font-size:12px; margin:0;">
                                <tbody>
                                    <tr>
                                        <td style="width:40%; font-weight:600; background:var(--bg-card);">HTTP_HOST</td>
                                        <td style="font-family:monospace; color:<?php echo $poisoned ? '#ef4444' : '#38bdf8'; ?>; font-weight:bold;">
                                            <?php echo htmlspecialchars($host_raw); ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($forwarded_host)) { ?>
                                    <tr>
                                        <td style="font-weight:600; background:var(--bg-card);">X-Forwarded-Host</td>
                                        <td style="font-family:monospace; color:#f59e0b;"><?php echo htmlspecialchars($forwarded_host); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td style="font-weight:600; background:var(--bg-card);">协议头 (Scheme)</td>
                                        <td style="font-family:monospace;"><?php echo htmlspecialchars($scheme); ?>://</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:600; background:var(--bg-card);">Host 安全判定</td>
                                        <td>
                                            <span class="label <?php echo $poisoned ? 'label-danger' : 'label-success'; ?>" style="border-radius:4px; font-size:11px;">
                                                <?php echo $poisoned ? '⚠️ 检测到外部投毒 Host' : '✅ 默认本地 Host'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reset Form -->
                        <form method="POST" style="margin-bottom:16px;">
                            <div class="form-group">
                                <label style="font-weight:600; font-size:13px;">目标用户注册邮箱 (Account Email):</label>
                                <input type="email" name="account" class="form-control" placeholder="victim_admin@pikachu.com" value="<?php echo htmlspecialchars($account); ?>" required style="border-radius:6px;">
                            </div>
                            <button type="submit" name="submit_reset" class="btn btn-primary btn-block" style="border-radius:6px; font-weight:700; padding:9px;">
                                <i class="fa fa-paper-plane"></i> 触发密码重置流程 (发送邮件)
                            </button>
                        </form>

                        <!-- Virtual Email Inbox Simulator -->
                        <?php if ($mail_sent) { ?>
                            <div class="mail-box" style="border-color:<?php echo $poisoned ? '#ef4444' : '#10b981'; ?>;">
                                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #334155; padding-bottom:8px; margin-bottom:10px;">
                                    <b style="color:#f8fafc; font-size:13px;"><i class="fa fa-inbox"></i> 📬 受害者虚拟邮箱收件箱</b>
                                    <span style="font-size:11px; color:#94a3b8;"><?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                                <div style="color:#94a3b8;"><b>收件人:</b> <span style="color:#e2e8f0;"><?php echo htmlspecialchars($account); ?></span></div>
                                <div style="color:#94a3b8;"><b>主题:</b> <span style="color:#e2e8f0;">[Pikachu 官方] 您的账号密码重置链接</span></div>
                                <div style="margin:12px 0 6px 0; color:#e2e8f0; line-height:1.6;">
                                    尊敬的用户，您好！<br>
                                    系统已收到您的密码重置请求，请在 15 分钟内点击下方专属链接重置密码：<br>
                                    <div style="background:#0f172a; border:1px solid <?php echo $poisoned ? '#ef4444' : '#38bdf8'; ?>; border-radius:6px; padding:10px; margin:8px 0; word-break:break-all;">
                                        <a href="<?php echo htmlspecialchars($reset_url); ?>" target="_blank" style="color:<?php echo $poisoned ? '#ef4444' : '#38bdf8'; ?>; font-weight:bold; text-decoration:underline;">
                                            <?php echo htmlspecialchars($reset_url); ?>
                                        </a>
                                    </div>
                                    (若非本人操作，请忽略本邮件)
                                </div>

                                <?php if ($poisoned) { ?>
                                    <div style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; border-radius:6px; padding:10px; margin-top:12px;">
                                        <div style="color:#f87171; font-weight:bold; font-size:13px;">
                                            <i class="fa fa-exclamation-triangle"></i> 🎯 恭喜！Host 报头投毒攻击生效！
                                        </div>
                                        <div style="font-size:12px; color:#cbd5e1; margin-top:4px;">
                                            邮件链接已被恶意篡改指向 <code><?php echo htmlspecialchars($effective_host); ?></code>！受害者点击后重置 Token 将直接发送至攻击者服务器！<br>
                                            [+] 通关验证 Flag: <br>
                                            <div style="margin:6px 0; background:#020617; border:1px solid #f59e0b; padding:6px 10px; border-radius:4px;">
                                                <span style="color:#f59e0b; font-weight:bold; font-size:13.5px;">flag{Host_Header_Injection_Password_Reset_Poisoned}</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div style="font-size:11.5px; color:#94a3b8; margin-top:8px;">
                                        <i class="fa fa-info-circle"></i> 当前链接指向默认本地主机 (127.0.0.1)。请通过代理或 curl 修改 Host 报头以完成投毒攻击！
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:20px; text-align:center; color:var(--text-muted); font-size:12.5px;">
                                <i class="fa fa-paper-plane-o" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                点击上方按钮模拟用户触发密码重置，下方将实时展示系统构建并投递的邮件内容。
                            </div>
                        <?php } ?>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Form -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" value="<?php echo $poisoned ? 'flag{Host_Header_Injection_Password_Reset_Poisoned}' : ''; ?>" required>
                            <button type="submit" name="check_flag" class="btn btn-success" style="border-radius:6px; font-weight:700; white-space:nowrap;">
                                <i class="fa fa-check"></i> 提交 Flag
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: SVG Architecture & Exploit Manual -->
                <div class="col-md-7">
                    <div class="host-card">
                        <h4 style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sitemap" style="color:var(--primary);"></i> Host 报头投毒与凭据劫持时序流程图
                        </h4>

                        <!-- High Resolution SVG Flowchart -->
                        <div style="background:#020617; border:1px solid #1e293b; border-radius:12px; padding:16px; margin-bottom:16px; text-align:center;">
                            <svg viewBox="0 0 760 210" style="width:100%; max-width:720px; height:auto; display:inline-block;" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="hostGradRed" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#991b1b" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="hostGradBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="hostGradGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#065f46" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <marker id="host-arr" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#38bdf8"/>
                                    </marker>
                                    <marker id="host-arr-red" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#ef4444"/>
                                    </marker>
                                </defs>

                                <!-- Step 1: Attacker sends request with poisoned Host -->
                                <rect x="15" y="25" width="205" height="70" rx="8" fill="url(#hostGradRed)" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="117" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">1. 攻击者请求重置密码</text>
                                <text x="117" y="68" fill="#fca5a5" font-size="10.5" text-anchor="middle" font-family="monospace">Host: evil-attacker.com</text>
                                <text x="117" y="82" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">指定受害者账号 email</text>

                                <!-- Arrow 1 -> 2 -->
                                <path d="M 220 60 L 275 60" stroke="#ef4444" stroke-width="1.5" marker-end="url(#host-arr-red)"/>

                                <!-- Step 2: Vulnerable Server builds URL -->
                                <rect x="285" y="25" width="200" height="70" rx="8" fill="#0f172a" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="385" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">2. 缺陷服务端盲信 Host</text>
                                <text x="385" y="68" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="monospace">url = "http://" . $_SERVER['HTTP_HOST']</text>
                                <text x="385" y="82" fill="#ef4444" font-size="10" font-weight="bold" text-anchor="middle" font-family="sans-serif">❌ 拼接投毒后的恶意 URL</text>

                                <!-- Arrow 2 -> 3 -->
                                <path d="M 485 60 L 535 60" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#host-arr)"/>

                                <!-- Step 3: Victim Receives Email -->
                                <rect x="545" y="25" width="200" height="70" rx="8" fill="url(#hostGradBlue)" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="645" y="48" fill="#60a5fa" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">3. 邮件发送至受害者</text>
                                <text x="645" y="68" fill="#94a3b8" font-size="10" text-anchor="middle" font-family="monospace">http://evil-attacker.com/...</text>
                                <text x="645" y="82" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">受害者点击钓鱼链接</text>

                                <!-- Arrow 3 -> 4 -->
                                <path d="M 645 95 L 645 125" stroke="#10b981" stroke-width="1.5" marker-end="url(#host-arr)"/>

                                <!-- Step 4: Token Leaks to Attacker (Bottom) -->
                                <rect x="285" y="125" width="460" height="65" rx="8" fill="url(#hostGradGreen)" stroke="#10b981" stroke-width="2"/>
                                <text x="515" y="148" fill="#34d399" font-size="13" font-weight="bold" text-anchor="middle" font-family="sans-serif">4. 重置凭据 Token 泄露至攻击者服务器日志</text>
                                <text x="515" y="166" fill="#fbbf24" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">GET /trust.php?reset_token=sec_token_8a9f... &rarr; 账号被劫持</text>
                                <text x="515" y="180" fill="#a7f3d0" font-size="10" text-anchor="middle" font-family="sans-serif">✅ 获取 Flag: flag{Host_Header_Injection_Password_Reset_Poisoned}</text>
                            </svg>
                        </div>

                        <!-- Step Explanations -->
                        <div class="flow-step">
                            <div class="flow-num" style="background:#ef4444;">1</div>
                            <div class="flow-content">
                                <h5 style="color:#ef4444;">攻击者拦截并修改 Host 报头</h5>
                                <p>攻击者提交受害者邮箱 <code>victim_admin@pikachu.com</code> 触发找回密码，同时将请求报头修改为 <code>Host: evil-attacker.com</code> 或注入 <code>X-Forwarded-Host: evil-attacker.com</code>。</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="flow-num" style="background:#3b82f6;">2</div>
                            <div class="flow-content">
                                <h5 style="color:#3b82f6;">受害者邮箱收到包含攻击者域名的重置链接</h5>
                                <p>由于服务端未对 Host 做白名单验证，直接把 <code>evil-attacker.com</code> 作为基准 URL 拼接入邮件正文，将合法的重置 Token 发送至受害者收件箱。</p>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="flow-num" style="background:#10b981;">3</div>
                            <div class="flow-content">
                                <h5 style="color:#10b981;">受害者点击邮件，Token 直达攻击者控制的 Web 日志</h5>
                                <p>受害者点击邮件链接时，请求将直接打到攻击者域名 <code>evil-attacker.com</code>，攻击者从 Access Log 中提取出 <code>reset_token</code>，直接完成目标密码重置！</p>
                            </div>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Hands-on Exploit Guide (Burp & curl) -->
                        <h4 style="margin:0 0 12px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-terminal" style="color:var(--primary);"></i> 手动利用实战指南 (Burp Suite & curl)
                        </h4>

                        <div style="margin-bottom:14px;">
                            <label style="font-size:12.5px; font-weight:600;"><i class="fa fa-wrench"></i> 方法一：使用 Burp Suite 拦截并篡改 Host 报头：</label>
                            <div class="code-box" style="white-space:pre-wrap;">
POST /vul/hostheader/trust.php HTTP/1.1
<span style="color:#ef4444; font-weight:bold;">Host: evil-attacker.com</span>  <span style="color:#94a3b8;">&lt;-- 将原本 127.0.0.1:8765 修改为攻击者域名</span>
Content-Type: application/x-www-form-urlencoded
Content-Length: 53

account=victim_admin%40pikachu.com&submit_reset=1
</div>
                        </div>

                        <div>
                            <label style="font-size:12.5px; font-weight:600;"><i class="fa fa-terminal"></i> 方法二：使用命令行 curl 一键发送投毒请求：</label>
                            <div class="code-box" style="white-space:pre-wrap;">
curl.exe -s -H <span style="color:#34d399;">"Host: evil-attacker.com"</span> -d <span style="color:#34d399;">"account=victim_admin@pikachu.com&submit_reset=1"</span> http://127.0.0.1:8765/vul/hostheader/trust.php | findstr <span style="color:#34d399;">"flag{"</span>
</div>
                        </div>

                        <div style="margin-top:14px; background:rgba(16,185,129,0.1); border-left:3px solid #10b981; border-radius:6px; padding:10px 14px; font-size:12px; color:var(--text-secondary);">
                            <b style="color:#10b981;"><i class="fa fa-shield"></i> 官方安全修复方案：</b><br>
                            1. <b>杜绝盲信 <code>$_SERVER['HTTP_HOST']</code></b>：绝对 URL 必须从服务端安全的全局静态配置（如 <code>config['domain'] = 'https://pikachu.com'</code>）中读取基准域名。<br>
                            2. <b>Nginx / Apache 严格白名单校验</b>：配置默认 server 块丢弃或阻断未知 Host 报头的 HTTP 请求。<br>
                            3. <b>禁用不可信的反向代理报头</b>：除非在受信任的内部网络，否则不要轻易使用 <code>X-Forwarded-Host</code> / <code>X-Host</code>。
                        </div>

                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="hostheader.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 返回模块概述
                </a>
                <a href="hostheader.php" class="btn btn-primary" style="border-radius:8px;">
                    完成演练 <i class="fa fa-check"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
