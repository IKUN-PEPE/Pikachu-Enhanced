<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[291] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSWE_L9_PHP_Type_Juggling_0e_Hash_Bypass}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['oswe_flags']['flag2'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！深度理解了 PHP 弱类型比较漏洞。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误，请检查哈希碰撞或源码。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #06b6d4); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(6,182,212,0.3); }
.step-box { background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:22px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.step-title { font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#22d3ee); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#334155; line-height:1.7; }
.flag-submit-area { background:#f8fafc; border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-nav { background: #06b6d4; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
.btn-nav:hover { background: #0891b2; color: white; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h2>OSWE Level 9: PHP 类型混淆深度研究 (Type Juggling)</h2>
        <p>200 PTS | 核心考点: 弱类型比较, 0e Magic Hash, 逻辑绕过</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> PHP 类型比较基础：== vs ===</h3>
        <p>PHP 中有松散比较（==）和严格比较（===）。松散比较会在比较前尝试进行类型转换。这就导致了许多逻辑漏洞。</p>
        <div class="cmd-box">
            <span class="comment">// 松散比较产生的问题 (PHP 8 之前尤为严重)</span><br>
            "0" == false   // bool(true)<br>
            "" == false    // bool(true)<br>
            "1" == true    // bool(true)<br>
            NULL == false  // bool(true)
        </div>
        <div class="highlight-box">
            <strong>核心思想:</strong> 当开发者在密码校验、身份令牌比对时使用 <code>==</code>，攻击者就可以通过传入不同类型的数据（如布尔值或特殊字符串）使判断条件为真。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 0e Magic Hash 利用 (科学计数法)</h3>
        <p>当两个字符串都以 "0e" 开头且后面全为数字时，PHP 的松散比较会将其视为科学计数法。0 的任意次方都是 0，因此它们彼此相等。</p>
        <div class="cmd-box">
            <span class="comment">// 0e 科学计数法比较</span><br>
            "0e123456" == "0e987654"  // bool(true)<br>
            <br>
            <span class="comment">// 如果哈希比对代码如下：</span><br>
            if (md5($password) == $db_hash) { ... }<br>
            <br>
            <span class="comment">// 已知的 Magic Hashes:</span><br>
            md5('240610708') == '0e462097431906509019562988736854'
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 字符串-数字 转换攻击向量</h3>
        <p>除了哈希，当字符串与整数进行比较时，PHP 会提取字符串开头的数字部分进行比较。</p>
        <div class="cmd-box">
            "100 ABC" == 100    // bool(true)<br>
            "3e4" == 3000.0     // bool(true)<br>
            "0" == "0e1234"     // bool(true)
        </div>
        <p>在版本比较或金额计算、JSON 反序列化产生的数据处理中，利用这种转换可突破业务逻辑限制。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 案例分析 (如 ATutor 认证绕过)</h3>
        <p>在 OSWE 的经典案例（如 ATutor）中，密码找回或认证模块可能存在此类漏洞。比如，验证传入的 token 时：</p>
        <div class="highlight-box">
            <code>if (strcmp($token, $expected_token) == 0)</code> <br>
            由于 strcmp() 在传入非字符串类型（如数组 `?token[]=`）时，在低版本 PHP 中会返回 NULL。而 `NULL == 0` 会被判定为 TRUE，直接绕过验证！
        </div>
        <div class="cmd-box">
            <span class="comment">// 本关验证的 FLAG 格式:</span><br>
            <span class="flag-text">flag{OSWE_L9_PHP_Type_Juggling_0e_Hash_Bypass}</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 防御与修复方案</h3>
        <p>在代码审计发现类型混淆后，相应的修复方法包括：</p>
        <ul>
            <li>全面改用 <code>===</code>（严格比较）进行字符串和哈希校验。</li>
            <li>使用原生的哈希函数 <code>password_hash()</code> 和 <code>password_verify()</code> 代替 MD5/SHA1 比较。</li>
            <li>处理不受信输入时，使用 <code>intval()</code> 或强制类型转换 <code>(int)</code>。</li>
        </ul>
    </div>

    <div class="flag-submit-area">
        <form method="POST">
            <h4>提交 Flag</h4>
            <input type="text" name="user_flag" class="form-control" style="width:50%; margin:10px auto;" placeholder="flag{...}">
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#06b6d4;border:none;">验证</button>
            <?php if($flag_msg) echo "<div style='margin-top:15px;'>$flag_msg</div>"; ?>
        </form>
    </div>

    <div class="nav-buttons">
        <a href="oswe_l8_sqli_blind.php" class="btn-nav">上一关: 盲注自动化</a>
        <a href="oswe_l10_java_rce.php" class="btn-nav">下一关: Java RCE 链</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
