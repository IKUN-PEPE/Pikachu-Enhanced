<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[302] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L10_WriteProcessMemory_DEP_ASLR_Combined}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osed_flags']['flag10'] = true;
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
        <h2 style="margin:0; font-size:24px;">[Level 10] WriteProcessMemory DEP+ASLR Combined Bypass</h2>
        <p style="margin:10px 0 0; opacity:0.9;">400 PTS - Chain memory primitives to defeat modern OS protections without relying on VirtualProtect.</p>
    </div>
    
    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> WriteProcessMemory API Mechanics</h3>
        <p>While <code>VirtualProtect</code> changes permissions of a memory page to allow execution, <code>WriteProcessMemory</code> (WPM) takes a different approach.</p>
        <div class="cmd-box">
BOOL WriteProcessMemory(
  HANDLE  hProcess,        <span class="comment">// -1 for self</span>
  LPVOID  lpBaseAddress,   <span class="comment">// Target executable region</span>
  LPCVOID lpBuffer,        <span class="comment">// Our shellcode address</span>
  SIZE_T  nSize,           <span class="comment">// Length of shellcode</span>
  SIZE_T  *lpNumberOfBytesWritten
);
        </div>
        <p>WPM can overwrite an existing RWX/RX code cave inside the same process space, effectively bypassing DEP by putting our payload into an already executable region without needing to alter page permissions explicitly.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> WPM ROP Chain Construction</h3>
        <p>Building a ROP chain for WPM is intricate because it requires passing multiple arguments onto the stack in precise order.</p>
        <div class="highlight-box">
            The chain must align the stack, dynamically populate the arguments for WPM, execute it to write shellcode into a known executable section (like the `.data` or `.text` padding of a non-ASLR module), and then finally jump to that exact address to achieve code execution.
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> ASLR+DEP Combined Defense Scenario</h3>
        <p>In environments with both DEP and ASLR, you must first secure an information leak. By combining a format string bug or out-of-bounds read, you leak a module pointer.</p>
        <p>With a single non-ASLR module (or one whose base address is successfully leaked), you can pivot the stack and execute your carefully crafted WPM chain entirely through dynamically resolved ROP gadgets.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> x64 Architecture Differences</h3>
        <p>The transition from x86 to x64 severely alters the exploitation landscape.</p>
        <div class="highlight-box">
            <ul>
                <li><b>Calling Conventions</b>: x64 uses RCX, RDX, R8, and R9 for the first four arguments. The stack is used for the rest.</li>
                <li><b>Shadow Space</b>: 32 bytes (4 QWORDs) must be allocated on the stack before the call to hold these registers if the function needs to dump them.</li>
                <li><b>Alignment</b>: Stack must be 16-byte aligned before making API calls to prevent crashes inside kernel space.</li>
            </ul>
        </div>
    </div>
    
    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> Next-gen Defenses</h3>
        <p>As techniques like WPM ROP evolve, so do defenses.</p>
        <div class="highlight-box">
            Control Flow Guard (CFG) prevents indirect calls to unauthorized locations, destroying many ROP pivot techniques. Intel CET (Control-flow Enforcement Technology) implements a hardware Shadow Stack that enforces return addresses, making traditional RET sliding effectively obsolete.
            <br><br>
            Completion code:
            <span class="flag-text">flag{OSED_L10_WriteProcessMemory_DEP_ASLR_Combined}</span>
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
        <a href="osed_l9_proto_reverse.php" class="btn-nav">← Prev: Level 9 (Proto Reversing)</a>
        <a href="osed_hub.php" class="btn-nav">Next: OSED Hub (Complete) →</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
