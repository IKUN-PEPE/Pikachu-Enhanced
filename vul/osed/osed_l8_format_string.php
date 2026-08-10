<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[279] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L8_FormatStr_Read_Write_EIP_Control}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osed_flags']['flag8'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！成功验证。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #f97316); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(249,115,22,0.3); }
.step-box { background:var(--bg-card, #1e293b); border:1px solid var(--border-color, #334155); border-radius:12px; padding:24px; margin-bottom:22px; color:#e2e8f0; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary, #f8fafc); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#f97316,#fdba74); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary, #cbd5e1); line-height:1.7; }
.flag-submit-area { background:var(--bg-card, #1e293b); border:2px dashed rgba(249,115,22,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display:flex; justify-content:space-between; margin-top:20px; }
.btn-nav { background:#334155; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none; }
.btn-nav:hover { background:#475569; color:#fff; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    
    <div class="ctf-stage-header">
        <h2 style="margin:0; font-size:24px;">[Level 8] Format String Vulnerability Exploitation</h2>
        <p style="margin:10px 0 0; opacity:0.9;">400 PTS - Identify, craft read/write primitives, and leverage arbitrary writes to gain EIP control.</p>
    </div>
    
    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> Format String Bug Identification</h3>
        <p>A format string vulnerability occurs when user input is passed directly to functions like <code>printf</code> without a format specifier.</p>
        <div class="highlight-box">
            Vulnerable: <code>printf(user_input);</code><br>
            Secure: <code>printf("%s", user_input);</code>
        </div>
        <p>Using specifiers like <code>%x</code> or <code>%p</code> allows an attacker to leak values off the stack. The special <code>%n</code> specifier writes the number of bytes outputted so far to a pointer on the stack.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Format String Read Primitive</h3>
        <p>To control the output precisely, we use Direct Parameter Access (DPA) using the syntax <code>%X$p</code>, which directly accesses the X-th argument on the stack.</p>
        <div class="cmd-box">
<span class="comment">; Binary search for self-offset on stack</span>
AAAA.%10$p.%11$p.%12$p
<span class="comment">; If we see '0x41414141', we found our input offset!</span>
        </div>
        <p>We can leak critical stack addresses, pointers to modules, or canary values, which is extremely useful for bypassing ASLR dynamically.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Format String Write Primitive</h3>
        <p>The <code>%n</code> family (<code>%n</code>, <code>%hn</code>, <code>%hhn</code>) writes bytes. We can construct an arbitrary write primitive.</p>
        <div class="highlight-box">
            For stability, we often use <code>%hhn</code> to write one byte at a time instead of massive 4-byte writes with <code>%n</code>. Target selections typically include the Global Offset Table (GOT), Saved Return Addresses on the stack, or SEH Handlers.
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> Tivoli-class Application Analysis</h3>
        <p>Advanced format string exploits often target enterprise software like IBM Tivoli. This involves protocol reverse engineering to reach the vulnerable format string endpoint.</p>
        <div class="cmd-box">
<span class="comment">; Reading Windows Event Logs using Format String read primitive</span>
<span class="comment">; Extract ASLR leaked pointers to craft the final payload</span>
payload = struct.pack("&lt;L", target_address) + b"%[padding]x%7$n"
        </div>
        <p>Combining the read leak with the write primitive constructs a full exploit chain against sophisticated targets.</p>
    </div>
    
    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> Defenses & Mitigations</h3>
        <p>Modern compilers introduce several layers of defense against format strings.</p>
        <div class="highlight-box">
            <ul>
                <li><b>FORTIFY_SOURCE</b>: Enforces checks on format functions.</li>
                <li><b>_printf_s</b>: Safe variants that reject <code>%n</code> natively.</li>
                <li><b>Static Analysis</b>: IDEs and linters detect direct input feeding.</li>
            </ul>
            <br>
            Flag achieved!
            <span class="flag-text">flag{OSED_L8_FormatStr_Read_Write_EIP_Control}</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="Enter Flag Here" required style="width:350px; background:#0f172a; color:#fff; border:1px solid #334155;">
            </div>
            <button type="submit" name="check_flag" class="btn" style="background:#f97316; color:#fff;">Submit Flag</button>
        </form>
    </div>

    <div class="nav-buttons">
        <a href="osed_l7_asm_shellcode.php" class="btn-nav">← Prev: Level 7 (ASM Shellcode)</a>
        <a href="osed_l9_proto_reverse.php" class="btn-nav">Next: Level 9 (Proto Reversing) →</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
