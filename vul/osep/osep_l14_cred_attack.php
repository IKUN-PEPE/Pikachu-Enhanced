<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[286] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L14_Token_Kerberos_Cred_Harvest}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag14'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！Windows 凭据与令牌技术掌握。</div>';
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
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 14: Windows 凭据攻击与令牌操纵 <span class="badge badge-warning">350 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡探讨 Windows 操作系统中的身份验证凭据提取技术、访问令牌操作及离线密码破解方法。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> Windows 凭据存储位置</h4>
        <p>在 Windows 环境中，不同类型的身份凭据存放在不同的安全区域：</p>
        <ul>
            <li><strong>LSASS (Local Security Authority Subsystem Service)：</strong>存储明文密码（旧系统/WDigest开启时）、NTLM 哈希、Kerberos 票据等。通常被 Mimikatz 等工具读取内存转储获取。</li>
            <li><strong>SAM/SECURITY Hive：</strong>本地 SAM 数据库存储本地账户的 NTLM 哈希；SECURITY Hive 包含 LSA Secrets（服务密码、计划任务凭据、计算机账户密码等）。</li>
            <li><strong>凭据管理器 / DPAPI：</strong>存储 Web 浏览器凭据、RDP 保存的密码等。DPAPI (Data Protection API) 被用于加密这些数据。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 导出系统和 SAM 注册表用于离线提取本地哈希</span>
reg save HKLM\SYSTEM system.save
reg save HKLM\SAM sam.save
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> 访问令牌（Access Token）操纵</h4>
        <p>Windows 使用访问令牌来标识登录会话的安全上下文。令牌主要分两类：<strong>主令牌 (Primary Token)</strong>（绑定到进程）和<strong>模拟令牌 (Impersonation Token)</strong>（绑定到线程，用于扮演其他用户）。</p>
        <p>若进程拥有 <code>SeImpersonatePrivilege</code> 特权（如 IIS 服务账户），攻击者可利用 Potato 家族漏洞（如 RottenPotato, RoguePotato, PrintSpoofer）强制高权限系统进程认证，窃取并模拟 SYSTEM 令牌。</p>
        <div class="cmd-box">
<span class="comment"># 使用 Incognito 模块模拟令牌（Token Stealing）</span>
meterpreter > use incognito
meterpreter > list_tokens -u
meterpreter > impersonate_token "NT AUTHORITY\SYSTEM"
        </div>
        <div class="highlight-box">此外，通过 API <code>CreateProcessWithTokenW</code> 或 <code>DuplicateTokenEx</code>，可以在获得他人令牌后衍生具有其权限的新进程。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> Kerberos 凭据链攻击</h4>
        <p>在 Active Directory 环境中，Kerberos 认证流程存在多个可被滥用的节点：</p>
        <ul>
            <li><strong>Pass-the-Ticket (PtT)：</strong>提取内存中的 TGT 票据并导入当前会话，无需密码即可访问资源。</li>
            <li><strong>Overpass-the-Hash (Pass-the-Key)：</strong>使用用户的 NTLM/AES 哈希向 KDC 发送 AS-REQ 请求，换取有效的 TGT（转 NTLM 认证为 Kerberos）。</li>
            <li><strong>Golden Ticket（金票）：</strong>若获取了域控的 <code>krbtgt</code> 账户哈希，即可伪造任何用户的 TGT，获得域内持久化最高权限。</li>
            <li><strong>Silver Ticket（银票） / Diamond Ticket：</strong>使用服务账户哈希伪造 TGS（银票）；或使用合法 TGT 并请求修改 PAC 从而生成具备高权限的票据（钻石票据）。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 提取 flag</span>
<span class="flag-text">echo "flag{OSEP_L14_Token_Kerberos_Cred_Harvest}"</span>
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> 凭据离线破解</h4>
        <p>获取到 NTLM 哈希或 NetNTLM 挑战响应后，可使用 <code>hashcat</code> 等工具进行离线暴力破解。</p>
        <p>Hashcat 常用模式：</p>
        <ul>
            <li><code>-m 1000</code>：NTLM 哈希（常用于 Pass-the-Hash）。</li>
            <li><code>-m 3000</code>：LM 哈希（较老的弱加密）。</li>
            <li><code>-m 5600</code>：NetNTLMv2 响应（通过 Responder 毒化获取）。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># Hashcat 字典+规则攻击破解 NTLMv2</span>
hashcat.exe -m 5600 -a 0 hashes.txt rockyou.txt -r rules/best64.rule
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> 防御建议</h4>
        <div class="highlight-box">
            <strong>加固建议：</strong><br>
            1. <strong>Credential Guard：</strong>基于虚拟化安全性（VBS）保护 LSASS 内存，防止工具直接读取明文凭据和 NTLM 哈希。<br>
            2. <strong>进程保护（PPL）：</strong>为 LSASS 启用 RunAsPPL，防止未签名或恶意进程注入与读取。<br>
            3. <strong>禁用 NTLM / 强 Kerberos：</strong>逐步禁用 NTLM 认证，强制 Kerberos 使用 AES256 加密（弃用 RC4）。<br>
            4. <strong>LAPS 部署：</strong>部署 Microsoft LAPS 定期轮换本地管理员密码，防止横向移动中的 Pass-the-Hash 攻击。
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
            <a href="osep_l13_net_evasion.php" class="btn btn-default">上一关</a>
            <a href="osep_l15_mssql.php" class="btn btn-default" style="margin-left:10px;">下一关</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
