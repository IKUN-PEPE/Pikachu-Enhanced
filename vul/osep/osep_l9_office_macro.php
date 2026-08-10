<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // OSEP parent menu index
$ACTIVE[281] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L9_Office_Macro_VBA_InMemory_Shell}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag9'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.5);color:#fff;">✅ 恭喜通关！您已成功掌握 Office 宏武器化与 VBA 免杀技术原理。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.5);color:#fff;">❌ Flag 错误，请检查输入或仔细阅读实验手册！</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #6366f1); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(168,85,247,0.3); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(168,85,247,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-nav { background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; text-decoration: none; }
.btn-nav:hover { background: #a855f7; color: white; text-decoration: none; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h2 style="margin-top:0; font-size:24px; font-weight:bold; display:flex; align-items:center; gap:10px;">
            <i class="fa fa-file-word-o"></i> 关卡 1: Office 宏武器化与 VBA 免杀
        </h2>
        <p style="margin-bottom:0; opacity:0.9;">200 PTS · 深入研究 VBA 宏的高级应用、Windows API 调用原理与免杀分析。</p>
    </div>

    <?= $flag_msg ?>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> Office 宏执行链</div>
        <p>Office 文档中的宏能够通过特定的事件自动触发，从而实现静默或半静默的代码执行。这是钓鱼攻击和红队行动中常用的初始访问方式。</p>
        <div class="highlight-box">
            常见的宏自动执行入口：<br>
            • <b>AutoOpen</b>: 当文档被打开时执行。<br>
            • <b>AutoExec</b>: 当 Word 启动时执行（通常针对全局模板）。<br>
            • <b>Document_Open</b>: ThisDocument 模块中的文档打开事件。<br>
        </div>
        <p>安全专家需要理解这些入口点，以便在分析恶意文档时快速定位到恶意的 VBA 代码并进行调试分析。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> VBA 内存执行 Shellcode</div>
        <p>通过 VBA 调用 Windows 系统的 API 函数，可以直接在 Office 进程（如 WINWORD.EXE）内部执行原生 Shellcode，从而避免落地可执行文件，减少被杀毒软件静态扫描的风险。</p>
        <div class="cmd-box">
            <span class="comment">' 声明 Win32 API 示例</span><br>
            Private Declare PtrSafe Function VirtualAlloc Lib "KERNEL32" (ByVal lpAddress As LongPtr, ByVal dwSize As Long, ByVal flAllocationType As Long, ByVal flProtect As Long) As LongPtr<br>
            Private Declare PtrSafe Function CreateThread Lib "USER32" (...) As LongPtr<br>
        </div>
        <p>通过 <code>VirtualAlloc</code> 申请可读可写可执行（RWX）内存，将 Shellcode 写入，再通过 <code>CreateThread</code> 执行。这种方式的代价是容易触发启发式行为监控。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> VBA 免杀基础</div>
        <p>随着安全防护产品的演进，直接在 VBA 中使用 <code>VirtualAlloc</code>、<code>CreateThread</code> 以及明文的 Shellcode 很容易被静态和动态查杀。绕过的核心在于混淆、变形和环境检测。</p>
        <div class="highlight-box">
            常见免杀策略：<br>
            1. <b>特征码定位与规避</b>: 定位引起杀软报警的具体代码段，进行等价替换。<br>
            2. <b>字符串混淆与加密</b>: 避免出现可疑字符串，如使用 Base64 编码或自定义异或加密存放 Shellcode。<br>
            3. <b>分块存储</b>: 将长篇的 Shellcode 分割成多个小块（数组、UserForm属性、甚至文档自定义属性），在运行时拼接。<br>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> 代理流量转发</div>
        <p>在企业环境中，受害机可能无法直接连接外网，必须通过企业代理上网。此时生成的 Shellcode 必须具备“代理感知（Proxy-Aware）”能力。</p>
        <p>WPAD（Web Proxy Auto-Discovery Protocol）常用于自动配置代理，攻击者如果配置了具有相关功能的 Payload，即可复用当前的系统代理设置，绕过出站限制。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御分析</div>
        <p>现代 Windows 系统引入了多重防御机制来对抗恶意宏的执行：</p>
        <div class="highlight-box">
            • <b>AMSI（Antimalware Scan Interface）</b>: 在脚本执行前，将其内容传递给反病毒引擎进行动态扫描。<br>
            • <b>行为监控（Behavior Monitoring）</b>: 检测 Office 进程是否产生了异常的子进程（如 cmd.exe, powershell.exe），或是否进行了高危的 API 调用。<br>
            • <b>受保护的视图（Protected View）</b>: 默认情况下，从互联网下载的文档会在沙盒中打开，禁用宏和其他活动内容。<br>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="color:var(--text-primary); margin-bottom: 20px;">🚩 提交您的 Flag</h4>
        <form method="POST" style="max-width: 500px; margin: 0 auto; display: flex; gap: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 Flag，例如：flag{...}" style="flex: 1; border-radius: 8px; border: 1px solid rgba(168,85,247,0.3); background: var(--bg-card); color: var(--text-primary);">
            <button type="submit" name="check_flag" class="btn btn-nav" style="border-radius: 8px;">验证</button>
        </form>
        <div style="margin-top:20px; font-family:monospace; font-size:12px; color:var(--text-secondary);">
            实验环境提示：本关 Flag 已隐写在下方字符串中：<br>
            <span style="color:#64748b;">$flag = "flag{OSEP_L9_Office_Macro_VBA_InMemory_Shell}";</span>
        </div>
    </div>

    <div class="nav-buttons">
        <a href="osep_l8_win_api.php" class="btn-nav"><i class="fa fa-arrow-left"></i> 上一关：L8 Windows API</a>
        <a href="osep_l10_process_inject.php" class="btn-nav">下一关：L10 进程注入 <i class="fa fa-arrow-right"></i></a>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
