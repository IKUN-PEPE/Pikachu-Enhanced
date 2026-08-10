<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[283] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L11_AMSI_UAC_Bypass_PowerShell}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag11'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.5);color:#fff;">✅ 恭喜通关！您已成功掌握 AMSI 与 UAC 的绕过技术。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.5);color:#fff;">❌ Flag 错误，请检查输入！</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
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
            <i class="fa fa-shield"></i> 关卡 3: AMSI 与 UAC 绕过技术研究
        </h2>
        <p style="margin-bottom:0; opacity:0.9;">300 PTS · 深入分析 Windows 内置的安全审查接口及权限提升机制。</p>
    </div>

    <?= $flag_msg ?>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> AMSI 工作原理</div>
        <p>AMSI（Antimalware Scan Interface）提供了一套标准的 COM 接口，使得应用程序可以请求防病毒软件扫描恶意内容。它广泛应用于 PowerShell、VBScript、JavaScript 以及 .NET 汇编中。</p>
        <div class="highlight-box">
            核心函数：<br>
            • <code>AmsiScanBuffer</code>: 扫描内存中的字节缓冲区。<br>
            • <code>AmsiScanString</code>: 扫描字符串。<br>
            Defender 通常作为默认的 AMSI Provider，在脚本被解释器执行前，捕获其明文内容，阻断经过混淆后释放出的真实恶意代码。
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> AMSI 绕过思路分类</div>
        <p>面对 AMSI 的审查，安全研究员们开发了多种绕过方式：</p>
        <ul style="color:var(--text-secondary);">
            <li><b>内存 Patch:</b> 直接在当前进程内存中修改 <code>amsi.dll</code> 中的 <code>AmsiScanBuffer</code> 函数，使其始终返回 <code>AMSI_RESULT_CLEAN</code>，从而欺骗接口。</li>
            <li><b>反射调用（PowerShell）:</b> 通过反射找到 <code>AmsiUtils</code> 类中的 <code>amsiInitFailed</code> 字段，并将其设为 true，禁用 AMSI 初始化。</li>
            <li><b>环境变量干扰:</b> 利用 COM 劫持或环境变量，诱导加载假的 AMSI Provider DLL。</li>
            <li><b>混淆与分割:</b> 继续对抗特征码扫描，将危险函数分散。</li>
        </ul>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> JScript 中的 AMSI 绕过研究</div>
        <p>除了 PowerShell，JScript 和 VBScript 也受到了 AMSI 的严密监控。特别是利用 <code>WScript.Shell</code> 对象执行命令时。</p>
        <p>著名的 <b>DotNetToJScript</b> 技术允许从 JScript 内存中直接加载 .NET 汇编，从而实现更强大的功能。而为了保障此过程，必须在执行敏感代码前，先通过 JScript 实现对 AMSI 的禁用或绕过，通常需要结合 Win32 API 动态调用完成。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> UAC 绕过经典技术</div>
        <p>用户账户控制（UAC）阻挡了许多默认以中等完整性级别运行的恶意操作。绕过 UAC 通常依赖于 Windows 中特有的 <b>自动提升（Auto-Elevation）</b> 机制。</p>
        <div class="highlight-box">
            常见手法：<br>
            • <b>注册表劫持</b>: 利用特定签名的可执行文件（如 <code>fodhelper.exe</code>、<code>eventvwr.exe</code>），当它们启动时会检查特定的注册表键（如 HKCU 中的 <code>ms-settings</code>），通过修改这些键指向恶意程序实现高权限执行。<br>
            • <b>COM 接口利用</b>: 使用 <code>ICMLuaUtil</code> 等允许管理员权限执行的 COM 接口，实现静默提权。<br>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御加固建议</div>
        <p>针对上述绕过技术，蓝队需部署纵深防御策略：</p>
        <p style="color:var(--text-secondary);">
            • <b>增强日志收集:</b> 开启 AMSI ETW 日志和 Defender 脚本块日志（Event 4104），以便即使绕过了内存拦截，依然能在后端感知到执行尝试。<br>
            • <b>UAC 策略调整:</b> 将 UAC 级别调整为 "Always Notify"，阻止静默的自动提升。<br>
            • <b>语言模式限制:</b> 将 PowerShell 切换到 <b>受限语言模式 (Constrained Language Mode)</b>，切断对大部分 Win32 API 和复杂 COM 对象的调用。
        </p>
    </div>

    <div class="flag-submit-area">
        <h4 style="color:var(--text-primary); margin-bottom: 20px;">🚩 提交您的 Flag</h4>
        <form method="POST" style="max-width: 500px; margin: 0 auto; display: flex; gap: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 Flag，例如：flag{...}" style="flex: 1; border-radius: 8px; border: 1px solid rgba(168,85,247,0.3); background: var(--bg-card); color: var(--text-primary);">
            <button type="submit" name="check_flag" class="btn btn-nav" style="border-radius: 8px;">验证</button>
        </form>
        <div style="margin-top:20px; font-family:monospace; font-size:12px; color:var(--text-secondary);">
            实验环境提示：本关 Flag 已隐写在下方字符串中：<br>
            <span style="color:#64748b;">$flag = "flag{OSEP_L11_AMSI_UAC_Bypass_PowerShell}";</span>
        </div>
    </div>

    <div class="nav-buttons">
        <a href="osep_l10_process_inject.php" class="btn-nav"><i class="fa fa-arrow-left"></i> 上一关：L10 进程注入</a>
        <a href="osep_l12_applocker.php" class="btn-nav">下一关：L12 AppLocker <i class="fa fa-arrow-right"></i></a>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
