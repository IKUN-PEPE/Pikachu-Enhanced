<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[288] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L16_Kiosk_Enum_Escape_PrivEsc}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag16'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！Kiosk 逃逸与环境突破。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #6366f1); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(99,102,241,0.3); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(99,102,241,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 16: Kiosk 逃逸技术研究 <span class="badge badge-warning">250 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡研究突破受限环境（Kiosk/展厅计算机）沙箱、获取底层操作系统访问权限的各种逃逸手法。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> Kiosk 环境识别与枚举</h4>
        <p>Kiosk 模式（如信息查询终端、自动取款机、展台电脑）旨在限制用户只能与特定应用交互。其实现机制多种多样：</p>
        <ul>
            <li><strong>自定义 Shell 替换：</strong>将 <code>explorer.exe</code> 替换为特定的应用程序。</li>
            <li><strong>策略限制：</strong>使用软件限制策略（SRP）或 AppLocker 阻止未授权应用运行。</li>
            <li><strong>Windows Assigned Access：</strong>Win10+ 提供的原生 Kiosk 模式，直接锁定特定 UWP 应用。</li>
        </ul>
        <div class="highlight-box">检测手段包括：尝试键盘快捷键（Ctrl+Alt+Del, Alt+Tab, Windows 键）、触发右键菜单、寻找任何能调出“帮助”或“打印”对话框的功能点。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> 常见逃逸入口（文件对话框）</h4>
        <p>大部分 Kiosk 逃逸基于 Windows 操作系统在调用通用对话框（Common Dialogs）时的统一处理方式：</p>
        <p>在任意应用程序中（如 PDF 阅读器、Web 浏览器）：</p>
        <ol>
            <li>触发 <strong>"Save As"（另存为）</strong>、<strong>"Open"（打开）</strong> 或 <strong>"Print"（打印）</strong> 对话框。</li>
            <li>在对话框的文件路径输入栏中键入 <code>C:\Windows\System32\cmd.exe</code> 并回车。</li>
            <li>通常可以绕过 UI 封锁，直接启动命令行终端。</li>
        </ol>
        <div class="cmd-box">
<span class="comment"># Flag 获取</span>
<span class="flag-text">echo "flag{OSEP_L16_Kiosk_Enum_Escape_PrivEsc}"</span>
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> 浏览器-基 Kiosk 逃逸</h4>
        <p>当 Kiosk 由全屏浏览器实现时，如果地址栏或开发者控制台未被彻底封死，可以通过以下方式逃逸：</p>
        <ul>
            <li><strong>本地文件协议：</strong>在地址栏输入 <code>file:///C:/Windows/System32/cmd.exe</code> 诱导下载并执行。</li>
            <li><strong>Data URI / JavaScript 执行：</strong>利用页面内的交互组件执行页面级的代码，调用可能存在的特殊协议或未修复的浏览器漏洞。</li>
            <li><strong>浏览器内建工具：</strong>通过 F12 开发者工具，或是“查看页面源代码”功能调出记事本，进而利用记事本的“打开文件”对话框。</li>
        </ul>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> 权限提升路径</h4>
        <p>成功从 Kiosk 应用逃逸出 CMD 或 PowerShell 后，当前上下文往往是一个权限极低的受限用户。接下来需要执行本地提权（Privilege Escalation）：</p>
        <ul>
            <li><strong>服务与注册表配置错误：</strong>检查是否存在弱权限服务、无引号服务路径（Unquoted Service Path）。</li>
            <li><strong>系统补丁缺陷：</strong>由于 Kiosk 终端常年不维护，往往存在已知的高危内核漏洞，可用于直接提权至 SYSTEM。</li>
            <li><strong>AlwaysInstallElevated：</strong>如果注册表中启用了 MSI 高权限安装，可使用 msfvenom 生成恶意 msi 执行提权。</li>
        </ul>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> 防御建议</h4>
        <div class="highlight-box">
            <strong>加固建议：</strong><br>
            1. <strong>使用官方 Assigned Access：</strong>避免使用“只运行全屏浏览器”这种廉价方案，利用 Windows 原生 Assigned Access 锁定用户会话。<br>
            2. <strong>禁用键盘热键：</strong>通过组策略彻底禁用 Ctrl+Alt+Del 等热键响应，拦截粘滞键。<br>
            3. <strong>组合防护：</strong>引入 AppLocker / Windows Defender Application Control (WDAC)，即使能调出文件对话框，也无法执行白名单以外的 <code>cmd.exe</code> 或 <code>powershell.exe</code>。<br>
            4. <strong>最小化系统组件：</strong>移除系统非必要功能（如 IE，无用的帮助文件），减少逃逸暴露面。
        </div>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline" style="justify-content: center;">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="输入Flag，例如 flag{...}" style="width: 300px;">
            </div>
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#6366f1; border-color:#4f46e5; margin-left:10px;">提交验证</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="osep_l15_mssql.php" class="btn btn-default">上一关</a>
            <a href="#" class="btn btn-default disabled" style="margin-left:10px;">已是最后一关</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
