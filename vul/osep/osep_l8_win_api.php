<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[280] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L8_Win32API_WOW64_Registry_Arch}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag8'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;">✅ 通关！OSEP L8 Windows 架构已掌握 (+150 PTS)</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;">❌ Flag 错误，继续研究 Win32 API 架构。</div>';
    }
}
?>
<style>
.ctf-stage-header{background:linear-gradient(135deg,#0c1218 0%,#1a1030 100%);border-radius:14px;padding:25px 30px;color:#fff;margin-bottom:25px;border:1px solid rgba(99,102,241,0.35);}
.ctf-stage-title{color:#fff!important;font-size:22px;font-weight:800;margin:0 0 10px 0;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.step-box{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;margin-bottom:22px;}
.step-title{font-size:16px;font-weight:700;color:var(--text-primary);margin-top:0;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.step-num{background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.cmd-box{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:14px 18px;font-family:monospace;font-size:13px;color:#7dd3fc;margin:12px 0;overflow-x:auto;line-height:1.9;}
.cmd-box .comment{color:#64748b;}
.cmd-box .flag-text{color:#fbbf24;font-weight:bold;}
.cmd-box .key{color:#a78bfa;font-weight:bold;}
.highlight-box{background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.3);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.arch-table{width:100%;border-collapse:collapse;font-size:13px;margin:12px 0;}
.arch-table th{background:rgba(99,102,241,0.15);color:var(--text-primary);padding:10px 14px;text-align:left;border:1px solid var(--border-color);}
.arch-table td{padding:9px 14px;border:1px solid var(--border-color);color:var(--text-secondary);font-family:monospace;font-size:12px;}
.flag-submit-area{background:var(--bg-card);border:2px dashed rgba(99,102,241,0.4);border-radius:12px;padding:24px;margin-top:25px;text-align:center;}
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
<div class="ctf-stage-header">
  <h1 class="ctf-stage-title">🏗️ OSEP L8：Windows 架构、Win32 API 与 WOW64
    <span style="background:rgba(99,102,241,0.2);color:#a5b4fc;border:1px solid #6366f1;padding:3px 10px;border-radius:12px;font-size:12px;">基础理论 · 150 PTS</span>
  </h1>
  <p style="color:#c4b5fd;font-size:14px;margin:0;line-height:1.6;">深入理解 Windows 架构基础：x86/x64 内存模型、WOW64 子系统、Win32 API 调用链、Windows 注册表结构，以及 C# 和 PowerShell 与 Win32 API 交互的底层原理。</p>
  <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
    <span style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#e2e8f0;">🔧 架构：x86·x64·WOW64·Win32 API·注册表·P/Invoke</span>
    <a href="osep_hub.php" style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#a5b4fc;text-decoration:none;">← OSEP 大厅</a>
  </div>
</div>

<div class="step-box">
  <h3 class="step-title"><span class="step-num">1</span> x86 vs x64：内存模型与调用约定差异</h3>
  <table class="arch-table">
    <tr><th>特性</th><th>x86 (32-bit)</th><th>x64 (64-bit)</th></tr>
    <tr><td>地址空间</td><td>4 GB（0x00000000-0xFFFFFFFF）</td><td>128 TB（0x000000000000-0x7FFFFFFFFFFF）</td></tr>
    <tr><td>用户/内核分割</td><td>2 GB / 2 GB（或 3 GB / 1 GB）</td><td>128 TB / 128 TB</td></tr>
    <tr><td>通用寄存器</td><td>EAX EBX ECX EDX ESI EDI ESP EBP</td><td>RAX RBX RCX RDX RSI RDI RSP RBP R8-R15</td></tr>
    <tr><td>函数调用约定</td><td>stdcall/cdecl：参数从右至左压栈</td><td>Windows x64：前4参数 RCX/RDX/R8/R9，其余压栈</td></tr>
    <tr><td>返回值</td><td>EAX</td><td>RAX</td></tr>
    <tr><td>栈对齐</td><td>4 字节</td><td>16 字节（调用前必须对齐）</td></tr>
  </table>
  <div class="cmd-box">
<span class="comment">; x86 stdcall 示例：MessageBoxA(NULL, "Hello", "Title", MB_OK)</span><br>
push 0           <span class="comment">; MB_OK</span><br>
push offset title <span class="comment">; "Title"</span><br>
push offset msg   <span class="comment">; "Hello"</span><br>
push 0           <span class="comment">; NULL (hWnd)</span><br>
call MessageBoxA  <span class="comment">; 参数右→左压栈</span><br><br>
<span class="comment">; x64 Windows 调用约定：</span><br>
<span class="comment">; RCX=NULL, RDX=msg, R8=title, R9=MB_OK</span><br>
sub rsp, 28h     <span class="comment">; 分配 shadow space (4×8=32字节) + 对齐</span><br>
xor ecx, ecx     <span class="comment">; RCX = NULL</span><br>
lea rdx, [msg]   <span class="comment">; RDX = 字符串指针</span><br>
lea r8, [title]  <span class="comment">; R8 = 标题指针</span><br>
xor r9d, r9d     <span class="comment">; R9 = MB_OK (0)</span><br>
call MessageBoxA
  </div>
</div>

<div class="step-box">
  <h3 class="step-title"><span class="step-num">2</span> WOW64：在 64 位 Windows 上运行 32 位进程</h3>
  <div class="cmd-box">
<span class="comment"># WOW64 (Windows on Windows 64-bit) 架构</span><br>
<span class="comment"># ┌─────────────────────────────────────────────────────────┐</span><br>
<span class="comment"># │ 32-bit 用户态进程                                        │</span><br>
<span class="comment"># │  → 调用 32-bit DLL (C:\Windows\SysWOW64\)               │</span><br>
<span class="comment"># │  → WOW64 转换层（wow64.dll · wow64win.dll · wow64cpu.dll）│</span><br>
<span class="comment"># │  → 切换到 x64 内核（Heaven's Gate：CS 段切换 0x23→0x33） │</span><br>
<span class="comment"># └─────────────────────────────────────────────────────────┘</span><br><br>
<span class="comment"># 重要路径区别：</span><br>
<span class="key">C:\Windows\System32\</span>    <span class="comment">← 64-bit DLL（x64 进程看到的）</span><br>
<span class="key">C:\Windows\SysWOW64\</span>   <span class="comment">← 32-bit DLL（x86 进程重定向到这里）</span><br>
<span class="key">C:\Windows\Sysnative\</span>  <span class="comment">← 32-bit 进程访问真实 System32 的路径</span><br><br>
<span class="comment"># 注册表重定向：</span><br>
<span class="comment"># HKLM\SOFTWARE\  → x64 程序写这里</span><br>
<span class="comment"># HKLM\SOFTWARE\WOW6432Node\ → x86 程序被重定向到这里</span><br><br>
<span class="comment"># Heaven's Gate 技术（Shellcode跨架构执行）：</span><br>
<span class="comment"># 通过修改 CS 寄存器（0x33）从 WOW64 切换到原生 x64 模式</span><br>
<span class="comment"># 可绕过 32-bit 的系统调用钩子（用于 EDR 绕过研究）</span>
  </div>
  <div class="highlight-box">
    🔬 <strong>红队意义：</strong>了解 WOW64 重定向对于编写注入代码至关重要。将 32-bit Shellcode 注入 64-bit 进程（或反之）需要处理架构兼容性问题。Heaven's Gate 技术利用 WOW64 切换机制，是部分高级 EDR 绕过的底层原理。
  </div>
</div>

<div class="step-box">
  <h3 class="step-title"><span class="step-num">3</span> Win32 API 调用链：从用户态到内核</h3>
  <div class="cmd-box">
<span class="comment"># Win32 API 调用层次（以 CreateFile 为例）：</span><br>
<span class="comment"># 用户程序 → kernel32.dll (CreateFileA/W)</span><br>
<span class="comment">#           → kernelbase.dll (CreateFileInternal)</span><br>
<span class="comment">#           → ntdll.dll (NtCreateFile) ← 系统调用边界</span><br>
<span class="comment">#           → NT内核 (ZwCreateFile / NtCreateFile)</span><br><br>
<span class="comment"># C# 通过 P/Invoke 调用 Win32 API：</span><br>
using System.Runtime.InteropServices;<br><br>
[DllImport("kernel32.dll", SetLastError=true)]<br>
static extern IntPtr VirtualAlloc(<br>
&nbsp;&nbsp;IntPtr lpAddress, uint dwSize,<br>
&nbsp;&nbsp;uint flAllocationType, uint flProtect<br>
);<br><br>
<span class="comment">// 常见内存保护常量：</span><br>
<span class="comment">// 0x1000 = MEM_COMMIT  | 0x3000 = MEM_COMMIT|MEM_RESERVE</span><br>
<span class="comment">// 0x40   = PAGE_EXECUTE_READWRITE (可读写执行)</span><br>
<span class="comment">// 0x04   = PAGE_READWRITE (仅读写，DEP友好)</span><br><br>
<span class="comment"># PowerShell 通过 Add-Type 调用 Win32 API：</span><br>
$code = @"<br>
[DllImport("kernel32")] public static extern IntPtr VirtualAlloc(<br>
&nbsp;&nbsp;IntPtr lpAddress, uint size, uint allocType, uint protect);<br>
"@<br>
Add-Type -MemberDefinition $code -Name WinAPI -Namespace Win32
  </div>
</div>

<div class="step-box">
  <h3 class="step-title"><span class="step-num">4</span> Windows 注册表结构与持久化利用路径</h3>
  <div class="cmd-box">
<span class="comment"># 注册表根键（Hive）：</span><br>
<span class="key">HKEY_LOCAL_MACHINE (HKLM)</span>  <span class="comment">← 系统级配置，需要管理员权限</span><br>
<span class="key">HKEY_CURRENT_USER (HKCU)</span>   <span class="comment">← 当前用户配置，普通用户可写</span><br>
<span class="key">HKEY_CLASSES_ROOT (HKCR)</span>   <span class="comment">← 文件关联/COM对象</span><br>
<span class="key">HKEY_USERS (HKU)</span>           <span class="comment">← 所有用户配置</span><br><br>
<span class="comment"># 渗透测试重要路径：</span><br>
<span class="comment"># 自启动（用户级，无需管理员）：</span><br>
HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\Run<br>
HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\RunOnce<br><br>
<span class="comment"># COM 劫持（研究用）：</span><br>
HKCU\SOFTWARE\Classes\CLSID\{GUID}\InprocServer32<br><br>
<span class="comment"># PowerShell 注册表操作：</span><br>
Get-ItemProperty "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run"<br>
Set-ItemProperty "HKCU:\SOFTWARE\..." -Name "MyApp" -Value "C:\path\to\app.exe"<br>
New-ItemProperty -Path "HKCU:\SOFTWARE\..." -Name "key" -Value "val" -Type String
  </div>
  <div class="highlight-box">
    🛡️ <strong>防御视角：</strong>Sysmon 可以监控注册表写入（Event ID 13）。Windows Defender 的行为检测规则包括对 Run 键的写入监控。正确配置的 EDR 会对 HKCU\Run 的修改触发告警。
  </div>
</div>

<div class="flag-submit-area">
  <h4 style="font-weight:800;color:var(--text-primary);margin-top:0;">🚩 Flag 验证 — OSEP L8</h4>
  <div class="cmd-box" style="display:inline-block;padding:10px 24px;margin:0 auto 16px;">
    <span class="flag-text">flag{OSEP_L8_Win32API_WOW64_Registry_Arch}</span>
  </div>
  <form method="post" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
    <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width:420px;border-radius:8px;font-family:monospace;">
    <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius:8px;font-weight:700;background:#6366f1;border-color:#6366f1;">✔ 验证 Flag</button>
  </form>
  <?php if(!empty($flag_msg)){echo '<div style="margin-top:10px;">'.$flag_msg.'</div>';}?>
  <div style="margin-top:16px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
    <a href="osep_l7_exfil.php" class="btn btn-sm btn-default" style="border-radius:6px;">← L7</a>
    <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius:6px;">OSEP 大厅</a>
    <a href="osep_l9_office_macro.php" class="btn btn-sm" style="border-radius:6px;background:#6366f1;color:#fff;border:none;font-weight:700;">L9 Office 宏 →</a>
  </div>
</div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
