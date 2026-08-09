<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 1: Reconnaissance & BloodHound Mapping
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[239] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{KingsLanding_BloodHound_Recon_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag1'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第一关】成就 (+100 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查提取的哈希或名称字段。</div>';
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
    border-left: 4px solid #6366f1;
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
                            <span class="label label-primary" style="font-size: 14px; border-radius: 6px;">LEVEL 1</span>
                            第一关：内网侦察与 BloodHound 域结构图论测绘
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.136 (winterfell)</code> / <code>192.168.56.134 (kingslanding)</code> | <strong>分值：</strong> 100 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-info-circle" style="color: #6366f1;"></i> 关卡背景与战术意图</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在渗透测试中，盲目利用 Exploit 极易触发防御报警。现代域渗透的第一步是通过非侵入性的 LDAP / RPC / SMB 协议，全面测绘当前域的林结构、信任边（Trusts）、域用户与特权组归属，进而使用图数据库（BloodHound）计算出从初始受限账号到域管的最短攻击路径。
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：端口服务探测与域环境识别</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用 Nmap 或 Netcat 对整个 <code>192.168.56.0/24</code> 网段扫描 Kerberos (88), LDAP (389), SMB (445), WinRM (5985) 等关键端口：
                </p>
                <div class="cmd-box">
nmap -p 53,88,135,389,445,1433,5985 192.168.56.134,136,138 -sV -Pn
                </div>
                <div class="output-box">
Nmap scan report for 192.168.56.134 (kingslanding.sevenkingdoms.local)
PORT    STATE SERVICE       VERSION
53/tcp  open  domain        Microsoft DNS
88/tcp  open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-09 09:32:04Z)
389/tcp open  ldap          Microsoft Windows Active Directory LDAP (Domain: sevenkingdoms.local)
445/tcp open  microsoft-ds  Windows Server 2019 Standard 17763 microsoft-ds

Nmap scan report for 192.168.56.136 (winterfell.north.sevenkingdoms.local)
PORT    STATE SERVICE       VERSION
88/tcp  open  kerberos-sec  Microsoft Windows Kerberos (Domain: north.sevenkingdoms.local)
389/tcp open  ldap          Microsoft Windows Active Directory LDAP
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：使用 CrackMapExec / NetExec 收集匿名与域内信息</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用匿名 RPC 连接或者已知低权用户 <code>jon.snow:iknownothing</code> 进行基础域信息拉取：
                </p>
                <div class="cmd-box">
netexec smb 192.168.56.136 -u 'jon.snow' -p 'iknownothing' --users
netexec smb 192.168.56.136 -u 'jon.snow' -p 'iknownothing' --groups
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    或者使用 Impacket 中的 <code>samrdump.py</code> 导出子域 <code>north.sevenkingdoms.local</code> 的所有用户清单。
                </p>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：SharpHound 数据采集与 BloodHound 攻击路径可视化</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 Linux 攻击机上使用 Python 版采集器 <code>bloodhound-python</code> 直接对域控进行全量数据搜集：
                </p>
                <div class="cmd-box">
bloodhound-python -u 'jon.snow' -p 'iknownothing' -d 'north.sevenkingdoms.local' -dc 'winterfell.north.sevenkingdoms.local' -gc 'kingslanding.sevenkingdoms.local' -c All --zip
                </div>
                <div class="output-box">
[*] Initializing BloodHound collector
[*] Connecting to LDAP server: winterfell.north.sevenkingdoms.local
[*] Found 35 users, 18 groups, 3 computers, 2 domain trusts
[*] Querying Domain Trusts...
    [+] Found ParentChild Trust: north.sevenkingdoms.local <-> sevenkingdoms.local
[*] Compressing data to 20260809_bloodhound.zip
[+] Done!
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    将生成的 ZIP 文件导入 BloodHound GUI，点击 <code>Find Shortest Paths to Domain Admins</code>，可清晰观察到从 <code>samwell.tarly</code> 与 <code>robb.stark</code> 向两级域管的横向跳板。
                </p>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    通过对林根域控 <code>kingslanding.sevenkingdoms.local</code> 侦察，确认根域与全网图论分析的 Flag 标识：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{KingsLanding_BloodHound_Recon_2026}
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
                <h3 class="step-title" style="color: #2563eb;"><i class="fa fa-shield"></i> 蓝队防御与威胁捕获 (Detection)</h3>
                <ul style="color: var(--text-secondary); font-size: 14px; line-height: 1.8; margin-bottom: 0;">
                    <li><strong>LDAP 异常查询监控：</strong> 监控 Windows Event 1644（高频 LDAP 批量枚举查询行为），识别 SharpHound 的爬网特征。</li>
                    <li><strong>限制匿名枚举：</strong> 关闭 Active Directory 的 <code>Pre-Windows 2000 Compatible Access</code> 组过度宽泛的读取权限。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
