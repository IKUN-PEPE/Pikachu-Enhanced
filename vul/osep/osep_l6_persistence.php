<?php
/**
 * OSEP L6: 持久化 计划任务/注册表/服务/WMI订阅 (300 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[257] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L6_Persistence_Task_Reg_Service}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag6'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSEP L6】持久化已掌握 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #a855f7, #7c3aed); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.persist-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.persist-table th { background: rgba(168,85,247,0.15); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.persist-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(168,85,247,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            🔒 OSEP L6：持久化 计划任务/注册表/服务
            <span style="background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 12px;">高级 · 300 PTS</span>
        </h1>
        <p style="color: #d8b4fe; font-size: 14px; margin: 0; line-height: 1.6;">分析 Windows 四大持久化路径的技术机制、注册表路径、事件日志特征，理解 SOC 如何通过 Sysmon/SIEM 检测持久化行为。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 技术：计划任务 · 注册表 Run 键 · 服务 · WMI 事件订阅</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osep_hub.php" style="color: #d8b4fe;">← 返回 OSEP 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> Windows 持久化机制全景</h3>
        <table class="persist-table">
            <tr><th>持久化方式</th><th>触发条件</th><th>所需权限</th><th>检测事件 ID</th><th>OPSEC</th></tr>
            <tr><td><strong>计划任务</strong></td><td>时间/登录/事件触发</td><td>管理员（SYSTEM 触发）</td><td>4698, 4702 (Task Scheduler)</td><td>🟡 中等</td></tr>
            <tr><td><strong>注册表 Run 键</strong></td><td>用户登录时</td><td>当前用户或管理员</td><td>Sysmon 12/13 (Registry)</td><td>🟡 中等</td></tr>
            <tr><td><strong>Windows 服务</strong></td><td>系统启动时</td><td>管理员/SYSTEM</td><td>4697 (Service Install), 7045</td><td>🔴 高噪声</td></tr>
            <tr><td><strong>WMI 事件订阅</strong></td><td>WMI 事件（灵活）</td><td>管理员</td><td>Sysmon 19/20/21</td><td>🟢 隐蔽</td></tr>
            <tr><td><strong>DLL 搜索顺序劫持</strong></td><td>目标应用程序启动时</td><td>写权限即可</td><td>Sysmon 7 (Image Load)</td><td>🟢 隐蔽</td></tr>
        </table>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 计划任务（Scheduled Tasks）</h3>
        <div class="cmd-box">
<span class="comment"># 创建计划任务（管理员权限）</span><br>
<span class="cmd">schtasks /create /tn "WindowsUpdate" /sc onlogon /ru SYSTEM /tr "C:\windows\temp\payload.exe" /f</span><br><br>
<span class="comment"># PowerShell 方式（更灵活）</span><br>
<span class="cmd">$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-enc BASE64_PAYLOAD"</span><br>
<span class="cmd">$trigger = New-ScheduledTaskTrigger -AtLogon</span><br>
<span class="cmd">Register-ScheduledTask -TaskName "SyncUpdate" -Action $action -Trigger $trigger -RunLevel Highest -Force</span><br><br>
<span class="comment"># 枚举现有计划任务（防御者排查）</span><br>
<span class="cmd">schtasks /query /fo LIST /v | Select-String -Pattern "Task Name|Status|Run As|Task To Run"</span><br><br>
<span class="comment"># 检测重点：</span><br>
<span class="comment"># EventID 4698: 新计划任务创建（记录 XML 内容）</span><br>
<span class="comment"># EventID 4702: 计划任务更新</span><br>
<span class="comment"># 位置：\Microsoft\Windows\TaskScheduler 事件日志</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 注册表 Run 键与 WMI 事件订阅</h3>
        <div class="cmd-box">
<span class="comment"># 注册表 Run 键（用户级，无需管理员）</span><br>
<span class="cmd">reg add "HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "WindowsDefender" /t REG_SZ /d "C:\temp\payload.exe" /f</span><br><br>
<span class="comment"># HKLM Run 键（系统级，需管理员）</span><br>
<span class="cmd">reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "SvcHost" /t REG_SZ /d "C:\windows\temp\svc.exe" /f</span><br><br>
<span class="comment"># ---- WMI 事件订阅（最隐蔽，Sysmon EventID 19/20/21）----</span><br>
<span class="comment"># WMI 三要素：EventFilter + EventConsumer + FilterToConsumerBinding</span><br>
<span class="cmd">$filter = ([wmiclass]"root\subscription:__EventFilter").CreateInstance()</span><br>
<span class="cmd">$filter.Name = "SysFilter"</span><br>
<span class="cmd">$filter.QueryLanguage = "WQL"</span><br>
<span class="cmd">$filter.Query = "SELECT * FROM __InstanceModificationEvent WITHIN 60 WHERE TargetInstance ISA 'Win32_LocalTime' AND TargetInstance.Hour=12"</span><br>
<span class="cmd">$filter.Put()</span><br><br>
<span class="comment"># 防御：Sysmon EventID 19=Filter, 20=Consumer, 21=Binding</span><br>
<span class="comment"># 任何 WMI 永久订阅都应触发 SOC 告警</span>
        </div>
        <div class="highlight-box">
            🔍 <strong>防御排查命令：</strong>
            <code>Get-WMIObject -Namespace root\subscription -Class __EventFilter</code><br>
            <code>Get-WMIObject -Namespace root\subscription -Class CommandLineEventConsumer</code>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L6</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            掌握四大持久化路径的技术机制与防御检测点后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSEP_L6_Persistence_Task_Reg_Service}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #a855f7; border-color: #a855f7;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osep_l5_av_evasion.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
            <a href="osep_l7_exfil.php" class="btn btn-sm" style="border-radius: 6px; background: #a855f7; color: #fff; border: none; font-weight: 700;">最终关：数据外渗 →</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
