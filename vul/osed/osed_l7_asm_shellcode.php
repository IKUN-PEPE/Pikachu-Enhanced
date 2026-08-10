<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[278] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L7_x86_ASM_PIC_NullFree_Shellcode}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osed_flags']['flag7'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！成功验证。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card, #1e293b); border:1px solid var(--border-color, #334155); border-radius:12px; padding:24px; margin-bottom:22px; color:#e2e8f0; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary, #f8fafc); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#f97316,#fdba74); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
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
        <h2 style="margin:0; font-size:24px;">[Level 7] x86 Assembly & Custom Shellcode Writing</h2>
        <p style="margin:10px 0 0; opacity:0.9;">350 PTS - Master manual shellcode crafting, PIC generation, and execution environment setup.</p>
    </div>
    
    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> x86 Calling Conventions</h3>
        <p>Understanding calling conventions is critical when writing shellcode. They define how arguments are passed, who cleans the stack, and which registers are preserved.</p>
        <div class="highlight-box">
            <ul>
                <li><b>cdecl</b>: Arguments passed on stack (Right-to-Left). Caller cleans the stack.</li>
                <li><b>stdcall</b>: Arguments passed on stack (Right-to-Left). Callee cleans the stack. Standard for Win32 API.</li>
                <li><b>fastcall</b>: First two arguments in ECX and EDX, rest on stack. Callee cleans stack.</li>
            </ul>
        </div>
        <p>Registers EAX, ECX, EDX are caller-saved (volatile), while EBX, ESI, EDI, EBP are callee-saved (non-volatile). ESP alignment is strictly checked in Windows APIs; the stack must be properly aligned before API invocation. For system calls, <code>int 0x80</code> vs <code>sysenter</code> vs <code>syscall</code> reflect the evolution of transitioning from user-mode to kernel-mode.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Dynamic kernel32.dll Location via PEB</h3>
        <p>Because DLLs load at different base addresses due to ASLR, shellcode must locate API functions dynamically at runtime.</p>
        <div class="cmd-box">
<span class="comment">; Walk the PEB to find kernel32.dll</span>
mov eax, [fs:0x30]        <span class="comment">; EAX = PEB</span>
mov eax, [eax + 0x0c]     <span class="comment">; EAX = PEB_LDR_DATA</span>
mov eax, [eax + 0x14]     <span class="comment">; EAX = InMemoryOrderModuleList</span>
mov eax, [eax]            <span class="comment">; EAX = ntdll.dll</span>
mov eax, [eax]            <span class="comment">; EAX = kernel32.dll</span>
mov ebx, [eax + 0x10]     <span class="comment">; EBX = Base address of kernel32.dll</span>
        </div>
        <p>After obtaining the base address, we parse the Export Address Table (EAT) to implement our own equivalent of <code>GetProcAddress</code> in pure assembly.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Writing NULL-Free Position Independent Code (PIC)</h3>
        <p>Shellcode is often delivered via string functions (like `strcpy`), meaning a `0x00` (NULL byte) will prematurely terminate the payload. We must eliminate all NULL bytes.</p>
        <div class="highlight-box">
            Instead of <code>mov eax, 0</code> (which translates to <code>B8 00 00 00 00</code>), use <code>xor eax, eax</code> (<code>31 C0</code>).<br>
            Instead of <code>mov eax, 1</code>, use <code>xor eax, eax</code> followed by <code>inc eax</code>.<br>
        </div>
        <p>To acquire the current instruction pointer (EIP) dynamically, we use the Call/Pop technique, avoiding hardcoded memory addresses.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> Reverse Shell Shellcode Structure</h3>
        <p>A Windows reverse shell typically requires initializing Winsock, creating a socket, connecting back, and mapping the socket handles to a new process's standard streams.</p>
        <div class="cmd-box">
<span class="comment">; 1. WSAStartup</span>
<span class="comment">; 2. WSASocketA</span>
<span class="comment">; 3. connect</span>
<span class="comment">; 4. CreateProcessA</span>

<span class="comment">; Redirect I/O using STARTUPINFO</span>
mov [edi + 0x38], eax     <span class="comment">; hStdInput = socket_handle</span>
mov [edi + 0x3C], eax     <span class="comment">; hStdOutput = socket_handle</span>
mov [edi + 0x40], eax     <span class="comment">; hStdError = socket_handle</span>
        </div>
        <p>Finally, we use <code>nasm -f bin shellcode.asm</code> and hexdump the output to verify our shellcode bytes and check for bad characters.</p>
    </div>
    
    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> Shellcode Encoding</h3>
        <p>When filtering is strict, custom encoders are required. An XOR encoder uses an iterative decoder stub that unpacks the payload in memory.</p>
        <div class="highlight-box">
            Advanced scenarios may require <code>alpha_mixed</code> or <code>unicode_mixed</code> encoders, limiting the bytes to purely printable alphanumeric characters or conforming to wide-character constraints.
            <br><br>
            Obtain the flag to proceed to the next module:
            <span class="flag-text">flag{OSED_L7_x86_ASM_PIC_NullFree_Shellcode}</span>
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
        <a href="osed_l6_rop.php" class="btn-nav">← Prev: Level 6 (ROP)</a>
        <a href="osed_l8_format_string.php" class="btn-nav">Next: Level 8 (Format String) →</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
