<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[299] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L18_AD_ACL_Kerberos_Delegation_Forest_Trust}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag18'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;">🏆 恭喜！OSEP 全部通关！Active Directory 深度利用已掌握 (+400 PTS)</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;">❌ Flag 错误，继续深入研究 AD 安全机制。</div>';
    }
}
?>
<style>
.ctf-stage-header{background:linear-gradient(135deg,#0c0a06 0%,#1c1000 100%);border-radius:14px;padding:25px 30px;color:#fff;margin-bottom:25px;border:1px solid rgba(245,158,11,0.4);}
.ctf-stage-title{color:#fff!important;font-size:22px;font-weight:800;margin:0 0 10px 0;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.step-box{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;margin-bottom:22px;}
.step-title{font-size:16px;font-weight:700;color:var(--text-primary);margin-top:0;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.step-num{background:linear-gradient(135deg,#f59e0b,#d97706);color:#000;width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.cmd-box{background:#0c0a00;border:1px solid #44330a;border-radius:8px;padding:14px 18px;font-family:monospace;font-size:13px;color:#fcd34d;margin:12px 0;overflow-x:auto;line-height:1.9;}
.cmd-box .comment{color:#78716c;}
.cmd-box .flag-text{color:#f59e0b;font-weight:bold;}
.cmd-box .key{color:#fb923c;font-weight:bold;}
.cmd-box .tool{color:#a3e635;}
.highlight-box{background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.def-box{background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.3);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.attack-chain{background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.25);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.flag-submit-area{background:var(--bg-card);border:2px dashed rgba(245,158,11,0.5);border-radius:12px;padding:24px;margin-top:25px;text-align:center;}
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">

<div class="ctf-stage-header">
  <h1 class="ctf-stage-title">🏆 OSEP L18：Active Directory 深度利用——ACL · Kerberos 委派 · 跨林攻击 [终章]
    <span style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid #f59e0b;padding:3px 10px;border-radius:12px;font-size:12px;">AD 终章 · 400 PTS · OSEP 完结</span>
  </h1>
  <p style="color:#fcd34d;font-size:14px;margin:0;line-height:1.6;">从蓝队视角理解 AD 高级攻击技术：ACL 权限链滥用检测、Kerberos 非约束/约束委派安全分析、基于资源的约束委派（RBCD）攻击路径、AD 林信任与 SID 历史注入原理，以及对应的防御加固策略。</p>
  <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
    <span style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#e2e8f0;">🏅 BloodHound · ACL · Kerberos · RBCD · Forest Trust · SID History</span>
    <a href="osep_hub.php" style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#fbbf24;text-decoration:none;">← OSEP 大厅</a>
  </div>
</div>

<!-- Step 1: AD ACL 滥用链 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">1</span> AD ACL 权限滥用链——BloodHound 路径分析与检测</h3>
  <div class="highlight-box">
    📚 <strong>核心概念：</strong>Active Directory 对象权限（ACE/ACL）控制哪些账户可以对 AD 对象执行何种操作。攻击者利用被错误配置的权限构建"ACL 链"，无需任何漏洞即可提升至域管。
  </div>
  <div class="cmd-box">
<span class="comment"># 高危 ACE 权限类型（BloodHound 标记的危险边）：</span><br>
<span class="key">GenericAll</span>     ← 完全控制目标对象（可强制修改密码、添加组成员等）<br>
<span class="key">GenericWrite</span>   ← 可写任意属性（ServicePrincipalName、msDS-KeyCredentialLink）<br>
<span class="key">WriteDACL</span>      ← 可修改 DACL → 可给自己授予 DCSync 权限<br>
<span class="key">WriteOwner</span>     ← 修改对象所有者 → 所有者可修改 DACL<br>
<span class="key">ForceChangePassword</span> ← 强制重置目标账户密码<br><br>
<span class="comment"># 蓝队视角：BloodHound 数据采集与分析</span><br>
<span class="comment"># 使用 SharpHound 采集数据（合规授权测试环境）：</span><br>
<span class="tool">SharpHound.exe</span> -c All --zipfilename bloodhound_data.zip<br><br>
<span class="comment"># BloodHound 中关键查询（检测高危路径）：</span><br>
<span class="comment"># "Find Shortest Paths to Domain Admins"</span><br>
<span class="comment"># "Find Principals with DCSync Rights"</span><br>
<span class="comment"># "Shortest Paths from Owned Principals"</span><br><br>
<span class="comment"># Shadow Credentials 攻击（GenericWrite → msDS-KeyCredentialLink）：</span><br>
<span class="comment"># 攻击原理：写入 PKINIT 公钥证书到属性 → 使用私钥申请 TGT</span><br>
<span class="comment"># 检测：监控 msDS-KeyCredentialLink 属性的修改事件</span><br>
<span class="comment"># Event ID 5136: A directory service object was modified</span><br>
<span class="comment"># 过滤 attributeLDAPDisplayName = msDS-KeyCredentialLink</span>
  </div>
  <div class="def-box">
    🛡️ <strong>防御建议：</strong>① 定期运行 BloodHound 并追踪危险路径；② 限制对 adminSDHolder 影响的 ACE；③ 启用 LDAP 签名和通道绑定（防止 LDAP Relay）；④ 对 AD 对象的 DACL 修改启用审计（Object Access Auditing）；⑤ 使用 Microsoft's ATA/Defender for Identity 检测异常属性修改。
  </div>
</div>

<!-- Step 2: 非约束委派 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">2</span> Kerberos 非约束委派（Unconstrained Delegation）安全分析</h3>
  <div class="cmd-box">
<span class="comment"># 非约束委派工作原理：</span><br>
<span class="comment"># 设置了 TrustedForDelegation 属性的计算机账户，</span><br>
<span class="comment"># 会在用户 TGS 中接收用户的 TGT（可转发 TGT）</span><br>
<span class="comment"># → 攻击者控制该机器后，可提取内存中的 TGT 冒充任意用户</span><br><br>
<span class="comment"># 枚举非约束委派计算机（蓝队审计）：</span><br>
<span class="comment"># PowerShell（需要 AD 模块）：</span><br>
Get-ADComputer -Filter {TrustedForDelegation -eq $true} -Properties TrustedForDelegation,Name<br><br>
<span class="comment"># 强制认证触发（Printer Bug / SpoolSample）：</span><br>
<span class="comment"># 攻击原理：MS-RPRN 协议漏洞强制 DC 向攻击者机器认证</span><br>
<span class="comment"># 导致 DC 的 TGT 被发送到运行非约束委派的机器</span><br>
<span class="comment"># 检测：</span><br>
<span class="comment"># Event ID 4768: Kerberos TGT 请求（关注 ClientName=DC$）</span><br>
<span class="comment"># Event ID 4769: Kerberos Service Ticket 请求（关注异常 SPN）</span><br><br>
<span class="comment"># 其他强制认证原语（Coercion Primitives）：</span><br>
MS-RPRN  ← PrinterBug（SMB + RPC，经典）<br>
MS-EFSRPC ← PetitPotam（可未认证触发）<br>
MS-DFSNM  ← DFSCoerce<br>
MS-FSRVP  ← ShadowCoerce
  </div>
  <div class="def-box">
    🛡️ <strong>防御建议：</strong>① 将所有委派账户加入 Protected Users 组（阻止 TGT 委派）；② 禁用不必要的非约束委派（仅域控和必要服务需要）；③ 启用 MS-RPRN 的"Print Spooler"服务白名单；④ 安装 MS-EFSRPC 的安全更新（KB5005413）；⑤ 监控来自域控向非 DC 机器的 TGT 请求。
  </div>
</div>

<!-- Step 3: 约束委派与 RBCD -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">3</span> 约束委派 与 基于资源的约束委派（RBCD）攻击路径</h3>
  <div class="cmd-box">
<span class="comment"># ===== 传统约束委派（Constrained Delegation）=====</span><br>
<span class="comment"># msDS-AllowedToDelegateTo 属性指定可委派到的 SPN 列表</span><br>
<span class="comment"># S4U2Self：允许服务为任意用户获取 Service Ticket（无需用户密码）</span><br>
<span class="comment"># S4U2Proxy：使用 S4U2Self 票据访问 AllowedToDelegateTo 中的 SPN</span><br><br>
<span class="comment"># 枚举约束委派账户：</span><br>
Get-ADObject -Filter {msDS-AllowedToDelegateTo -ne "$null"} -Properties msDS-AllowedToDelegateTo<br><br>
<span class="comment"># ===== RBCD（Resource-Based Constrained Delegation）=====</span><br>
<span class="comment"># 关键属性：msDS-AllowedToActOnBehalfOfOtherIdentity（在目标机器上设置）</span><br>
<span class="comment"># 攻击前提：对目标机器有 GenericWrite / GenericAll 权限</span><br><br>
<span class="comment"># 攻击思路（概念性）：</span><br>
<span class="comment"># 1. 创建一个机器账户（默认域用户可创建最多 10 个）</span><br>
<span class="comment"># 2. 在目标机器的 msDS-AllowedToActOnBehalfOfOtherIdentity 中写入攻击者机器账户</span><br>
<span class="comment"># 3. 使用攻击者机器账户的凭据执行 S4U2Self + S4U2Proxy</span><br>
<span class="comment"># 4. 获得以目标机器管理员身份访问目标机器的 Service Ticket</span><br><br>
<span class="comment"># 检测要点：</span><br>
<span class="comment"># 监控 msDS-AllowedToActOnBehalfOfOtherIdentity 属性修改</span><br>
<span class="comment"># 监控非常规的机器账户创建（Event ID 4741）</span><br>
<span class="comment"># 监控 S4U2Self 请求（Kerberos 日志中 Transited-Services 字段）</span>
  </div>
  <div class="attack-chain">
    ⚠️ <strong>攻击链示例理解（GenericWrite → 机器管理员）：</strong><br>
    <code>拥有 GenericWrite → 目标计算机账户</code><br>
    → 修改 msDS-AllowedToActOnBehalfOfOtherIdentity<br>
    → 创建攻击者控制的机器账户<br>
    → S4U2Self（以 Administrator 名义）→ S4U2Proxy（获取目标机 CIFS Ticket）<br>
    → 本地管理员权限访问目标机器
  </div>
</div>

<!-- Step 4: AD 林信任攻击 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">4</span> AD 林信任（Forest Trust）与 SID 历史注入</h3>
  <div class="cmd-box">
<span class="comment"># AD 林信任类型：</span><br>
<span class="key">单向信任</span>：信任方（Trusting）信任被信任方（Trusted），仅单向访问<br>
<span class="key">双向信任</span>：两个林互相信任，可双向横向移动<br>
<span class="key">外部信任</span>：跨不同林的特定域信任<br>
<span class="key">林信任</span>：整个林级别的信任（最危险）<br><br>
<span class="comment"># SID History 注入（跨林横向移动）：</span><br>
<span class="comment"># sIDHistory 属性：历史上用户曾属于的 SID（用于迁移场景）</span><br>
<span class="comment"># 攻击原理：在跨林 TGT 中注入目标林的高权限组 SID</span><br>
<span class="comment"># 前提：已获得源林的 krbtgt 哈希（Golden Ticket）</span><br><br>
<span class="comment"># ExtraSids 字段：跨林 Golden Ticket 中可添加目标林 SID</span><br>
<span class="comment"># 检测：</span><br>
<span class="comment"># Event ID 4769 + TGT 中的 ExtraSids 字段异常</span><br>
<span class="comment"># SID Filtering（默认在林信任中启用）：过滤 ExtraSids</span><br>
<span class="comment"># SID Filtering 关闭场景：某些迁移工具会临时关闭</span><br><br>
<span class="comment"># 枚举林信任：</span><br>
Get-ADTrust -Filter * | Select-Object Name, TrustType, Direction, SIDFilteringQuarantined<br>
<span class="comment"># SIDFilteringQuarantined = False → 存在 SID 历史注入风险</span>
  </div>
  <div class="def-box">
    🛡️ <strong>防御建议：</strong>① 确保所有林信任启用 SID Filtering（Quarantine）；② 最小化林信任范围——非必要不建立双向林信任；③ 监控 sIDHistory 属性的修改（Event 4738）；④ Tiered Administration Model：域管账户不跨林使用；⑤ 定期使用 PowerView/BloodHound 审计信任路径。
  </div>
</div>

<!-- Step 5: 综合防御 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">5</span> AD 深度防御加固策略汇总</h3>
  <div class="cmd-box">
<span class="comment"># ===== Tiered Administration Model（分层管理模型）=====</span><br>
<span class="key">Tier 0</span>：域控、PKI、AD Connect → 仅 Tier 0 管理员可访问<br>
<span class="key">Tier 1</span>：服务器、应用服务器 → Tier 1 管理员<br>
<span class="key">Tier 2</span>：工作站、VDI → 普通 IT 管理员<br>
<span class="comment"># 核心规则：高 Tier 账户绝不登录低 Tier 设备</span><br><br>
<span class="comment"># ===== Credential Guard =====</span><br>
<span class="comment"># 使用 VBS（Virtualization Based Security）隔离 LSASS</span><br>
<span class="comment"># 防止 Mimikatz 类工具从内存提取明文凭据</span><br>
Enable-WindowsOptionalFeature -Online -FeatureName Windows-Defender-Credential-Guard<br><br>
<span class="comment"># ===== Protected Users 安全组 =====</span><br>
<span class="comment"># 加入该组的账户：</span><br>
<span class="comment"># × 无法使用 NTLM 认证</span><br>
<span class="comment"># × 无法使用 RC4 加密的 Kerberos TGT</span><br>
<span class="comment"># × TGT 有效期限制为 4 小时</span><br>
<span class="comment"># × 无法委派（含非约束/约束委派）</span><br>
Add-ADGroupMember -Identity "Protected Users" -Members "DomainAdmin1"<br><br>
<span class="comment"># ===== PAC 验证强化 =====</span><br>
<span class="comment"># CVE-2021-42278 (sAMAccountName Spoofing)</span><br>
<span class="comment"># CVE-2021-42287 (PAC Validation bypass)</span><br>
<span class="comment"># 确保安装 2021年11月 AD 安全更新</span><br><br>
<span class="comment"># ===== 定期审计工具 =====</span><br>
<span class="tool">BloodHound</span>    ← 定期采集分析高危 ACL 路径<br>
<span class="tool">PingCastle</span>    ← AD 安全评分与配置审计<br>
<span class="tool">Purple Knight</span> ← Semperis 出品的 AD 风险扫描工具<br>
<span class="tool">Microsoft Defender for Identity</span> ← 实时 AD 行为分析与告警
  </div>
  <div class="def-box">
    🎓 <strong>OSEP 综合总结：</strong>完整的 AD 渗透链覆盖——初始访问（钓鱼/Client-Side Exploits）→ 内网横向移动（WMI/Kerberos/Token 操纵）→ 权限提升（ACL链/委派滥用）→ 域控攻陷（DCSync/Golden Ticket）→ 跨林横向移动（SID History/林信任）→ 持久化与数据外渗。理解攻击链才能构建有效的防御体系。
  </div>
</div>

<div class="flag-submit-area">
  <h4 style="font-weight:800;color:var(--text-primary);margin-top:0;">🏆 OSEP 终章 Flag — L18 AD 深度利用</h4>
  <p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px;">完成本关学习后，提交 Flag 完成 OSEP 全部 18 关！</p>
  <div class="cmd-box" style="display:inline-block;padding:10px 24px;margin:0 auto 16px;">
    <span class="flag-text">flag{OSEP_L18_AD_ACL_Kerberos_Delegation_Forest_Trust}</span>
  </div>
  <form method="post" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
    <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width:520px;border-radius:8px;font-family:monospace;">
    <button type="submit" name="check_flag" class="btn btn-warning" style="border-radius:8px;font-weight:800;background:#f59e0b;border-color:#d97706;color:#000;">🏆 提交终章 Flag</button>
  </form>
  <?php if(!empty($flag_msg)){echo '<div style="margin-top:10px;">'.$flag_msg.'</div>';}?>
  <div style="margin-top:16px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
    <a href="osep_l17_linux_postex.php" class="btn btn-sm btn-default" style="border-radius:6px;">← L17 Linux 后渗透</a>
    <a href="osep_hub.php" class="btn btn-sm btn-warning" style="border-radius:6px;background:#f59e0b;color:#000;border:none;font-weight:800;">🎯 OSEP 大厅 · 查看总进度</a>
  </div>
</div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
