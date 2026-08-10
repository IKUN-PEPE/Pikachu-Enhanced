<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[301] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L9_Protocol_Reverse_IDA_WinDbg_Vuln}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osed_flags']['flag9'] = true;
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
        <h2 style="margin:0; font-size:24px;">[Level 9] Application Protocol Reverse Engineering</h2>
        <p style="margin:10px 0 0; opacity:0.9;">350 PTS - Map closed-source protocols, trace execution flows, and uncover memory corruption bugs via structured fuzzing.</p>
    </div>
    
    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> Static Analysis Workflow</h3>
        <p>Without documentation, understanding a proprietary protocol requires robust static analysis.</p>
        <div class="highlight-box">
            Load the target EXE/DLL into IDA Pro. Use the Strings window to find protocol-specific keywords (e.g. "AUTH", "CONNECT_OK").<br>
            Check the Imports table for socket functions like <code>recv()</code>, <code>send()</code>, or <code>WSARecv()</code>. From these imports, do a cross-reference (XREF) search backwards to locate the primary protocol parsing switch statements.
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Dynamic Debugging to Trace Protocol</h3>
        <p>Combining IDA with WinDbg allows us to observe protocol handling live.</p>
        <div class="cmd-box">
<span class="comment">; Set a hardware breakpoint on the receive function</span>
ba e 1 ws2_32!recv
<span class="comment">; When hit, dump the receive buffer</span>
dd esp+8 L10
        </div>
        <p>Trace the call chain back into the application logic. Monitor registers (like RDX or R8 on x64) tracking the packet length parsing, looking for integer truncation or unchecked bounds.</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Wireshark Capture vs Reverse Engineering</h3>
        <p>Simultaneously capturing traffic with Wireshark provides ground-truth packets to compare against assembly logic.</p>
        <div class="highlight-box">
            Once you identify the Length, Flags, and Command bytes, you can write a custom Lua Dissector for Wireshark to automatically parse and colorize the packets. This speeds up payload crafting significantly.
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> Vulnerability Mining Methods</h3>
        <p>With the protocol structure known, we begin targeted fuzzing.</p>
        <div class="cmd-box">
<span class="comment"># Python pseudocode for length fuzzing</span>
def craft_packet(cmd_id, payload):
    # What happens if we supply 0xFFFF for length but 
    # only 4 bytes of actual payload?
    header = struct.pack(">H B", 0xFFFF, cmd_id)
    return header + payload
        </div>
        <p>Analyze boundary conditions for flag fields and map paths that guide execution directly into vulnerable routines such as unchecked <code>memcpy</code>.</p>
    </div>
    
    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> Defenses</h3>
        <p>Modern applications secure proprietary protocols in layers.</p>
        <div class="highlight-box">
            Wrapping the internal protocol in a TLS layer prevents easy network sniffing and manipulation. Secure coding practices require strict boundary checks, type validation, and fixed buffer size limits.
            <br><br>
            Secret found:
            <span class="flag-text">flag{OSED_L9_Protocol_Reverse_IDA_WinDbg_Vuln}</span>
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
        <a href="osed_l8_format_string.php" class="btn-nav">← Prev: Level 8 (Format String)</a>
        <a href="osed_l10_wpm_bypass.php" class="btn-nav">Next: Level 10 (WPM Bypass) →</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
