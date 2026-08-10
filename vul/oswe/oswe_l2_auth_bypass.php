<?php
/**
 * OSWE L2: 认证绕过逻辑漏洞链 (150 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[263] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L2_AuthBypass_Logic_Chain_Broken}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag2'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L2】认证绕过已掌握 (+150 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
// 演示 PHP 类型混淆
$demo_result = '';
if (isset($_POST['demo_compare'])) {
    $input = $_POST['demo_input'];
    // 故意演示弱类型漏洞
    if ($input == 0) {
        $demo_result = '⚠️ 使用 == 比较：输入 "' . htmlspecialchars($input) . '" 与 0 相等！（PHP 弱类型漏洞）';
    } else {
        $demo_result = '使用 == 比较：输入 "' . htmlspecialchars($input) . '" 与 0 不相等。';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .bad-code { color: #f87171; }
.cmd-box .good-code { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(99,102,241,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
.demo-box { background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.2); border-radius: 10px; padding: 18px; margin: 12px 0; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🔓 OSWE L2：认证绕过逻辑漏洞链
            <span style="background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid #3b82f6; padding: 3px 10px; border-radius: 12px; font-size: 12px;">初级 · 150 PTS</span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0; line-height: 1.6;">分析认证绕过的典型模式：PHP 弱类型比较漏洞、哈希比较绕过（0e magic hash）、密码找回逻辑缺陷、多步认证竞争条件与 JWT 算法替换。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 技术：PHP 弱类型 · 0e Hash · Token 预测 · 竞争条件 · JWT none alg</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="oswe_hub.php" style="color: var(--text-secondary);">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> PHP 弱类型比较：最常见的认证绕过</h3>
        <div class="cmd-box">
<span class="comment"># PHP 弱类型（== 运算符）比较规则：</span><br>
<span class="comment"># 当两端类型不同时，PHP 尝试类型转换再比较</span><br><br>
<span class="bad-code"># ❌ 易受攻击的代码：</span><br>
<span class="bad-code">if ($input_password == $stored_hash) { // 弱等号</span><br>
<span class="bad-code">&nbsp;&nbsp;login_success();</span><br>
<span class="bad-code">}</span><br><br>
<span class="comment"># 0e Magic Hash 绕过：</span><br>
<span class="comment"># md5("240610708") = "0e462097431906509019562988736854"</span><br>
<span class="comment"># md5("QNKCDZO")   = "0e830400451993494058024219903391"</span><br>
<span class="comment"># PHP 弱类型将 "0e..." 格式字符串视为科学计数法 → 都等于 0</span><br>
<span class="comment"># 如果数据库存储的 Hash 恰好是 "0e..." 开头，输入任意 0e Hash 可绕过！</span><br><br>
<span class="comment"># 数字与字符串比较：</span><br>
<span class="comment"># "abc" == 0 → true (字符串转数字失败，得到 0)</span><br>
<span class="comment"># "1abc" == 1 → true (字符串转数字取前缀数字 1)</span><br>
<span class="comment"># NULL == 0 == "" == false → 所有都相等！</span><br><br>
<span class="good-code"># ✅ 安全的代码（严格比较）：</span><br>
<span class="good-code">if ($input_password === $stored_hash) { // 三等号：类型+值都必须相同</span>
        </div>

        <!-- 演示 PHP 弱类型 -->
        <div class="demo-box">
            <h5 style="font-weight: 700; color: var(--text-primary); margin-top: 0;"><i class="fa fa-flask" style="color: #6366f1;"></i> 互动演示：PHP 弱类型比较</h5>
            <p style="font-size: 13px; color: var(--text-secondary);">尝试输入一个字母开头的字符串（如 "abc"、"hack"），看看 PHP 如何用 <code>==</code> 与数字 0 进行比较：</p>
            <form method="post" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="demo_input" class="form-control" placeholder="输入字符串..." value="<?php echo isset($_POST['demo_input']) ? htmlspecialchars($_POST['demo_input']) : ''; ?>" style="max-width: 200px; border-radius: 6px; font-family: monospace;">
                <button type="submit" name="demo_compare" class="btn btn-sm" style="background: #6366f1; color: #fff; border: none; border-radius: 6px; font-weight: 700;">测试弱类型</button>
            </form>
            <?php if (!empty($demo_result)): ?>
                <div style="margin-top: 10px; font-family: monospace; font-size: 13px; padding: 10px; background: #0f172a; border-radius: 6px; color: <?php echo strpos($demo_result, '⚠️') !== false ? '#f87171' : '#94a3b8'; ?>;">
                    <?php echo $demo_result; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 密码找回逻辑缺陷与 Token 预测</h3>
        <div class="cmd-box">
<span class="comment"># 常见密码找回漏洞类型：</span><br><br>
<span class="comment"># 类型 1：Token 基于时间戳生成（可预测）</span><br>
<span class="bad-code">$token = md5(time() . $email);  // ❌ 时间戳可预测</span><br>
<span class="comment"># 攻击：记录发送时间 → 暴力枚举时间戳 ± 几秒 → 重算 Token</span><br><br>
<span class="comment"># 类型 2：回显 Token 的某部分（信息泄露）</span><br>
<span class="comment"># 错误页面、邮件 CC 头、HTTP 响应头中泄露 Token</span><br><br>
<span class="comment"># 类型 3：Token 复用（不过期）</span><br>
<span class="comment"># 同一个 Token 可无限次使用，不验证有效期</span><br><br>
<span class="comment"># 类型 4：Host Header 注入影响邮件链接</span><br>
<span class="comment"># POST /reset-password HTTP/1.1</span><br>
<span class="comment"># Host: attacker.com   ← 注入 Host 头</span><br>
<span class="comment"># 应用使用 Host 头生成重置链接 → 链接发到受害者邮箱但域名变成 attacker.com</span><br><br>
<span class="comment"># JWT none 算法绕过（OSWE 经典考题）：</span><br>
<span class="comment"># Header: {"alg":"none"} → 签名验证被跳过</span><br>
<span class="comment"># 构造：base64(header) + "." + base64(payload) + "."</span><br>
<span class="comment"># 修改 payload 中的 role/admin 字段</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 多步认证竞争条件与 TOCTOU</h3>
        <div class="cmd-box">
<span class="comment"># TOCTOU (Time of Check vs Time of Use) 竞争条件</span><br>
<span class="comment"># 场景：2FA 验证码在被使用后才失效，但存在并发窗口</span><br><br>
<span class="comment"># 攻击方式（概念理解）：</span><br>
<span class="comment"># 1. 获取合法 2FA 验证码（如截获的 TOTP）</span><br>
<span class="comment"># 2. 同时发送 N 个请求（并发）使用同一个验证码</span><br>
<span class="comment"># 3. 如果服务器未做幂等性保护 → 多个请求同时通过验证</span><br><br>
<span class="comment"># Python 并发请求示例（概念代码）：</span><br>
import threading, requests<br><br>
def try_bypass(session, url, token):<br>
&nbsp;&nbsp;response = session.post(url, data={"token": token})<br>
&nbsp;&nbsp;if "success" in response.text:<br>
&nbsp;&nbsp;&nbsp;&nbsp;print("Bypass!", response.cookies)<br><br>
<span class="comment"># threads = [threading.Thread(target=try_bypass, ...) for _ in range(20)]</span><br>
<span class="comment"># 防御：验证码使用后立即删除（原子操作），添加请求限速</span>
        </div>
        <div class="highlight-box">
            🔑 <strong>OSWE 利用脚本要点：</strong>考试要求编写 Python 自动化脚本完成完整利用链。脚本必须：保持 Session（requests.Session()）、处理 CSRF Token（从响应中提取）、按顺序执行多步操作，最终获取 flag 文件内容。
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSWE L2</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSWE_L2_AuthBypass_Logic_Chain_Broken}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="oswe_l1_whitebox.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSWE 大厅</a>
            <a href="oswe_l3_sqli_auth.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">下一关：SQL注入 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
