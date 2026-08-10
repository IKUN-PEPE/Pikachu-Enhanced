<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[290] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L17_AD_Forest_ACL_Delegation_Abuse}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag17'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！Active Directory 深度利用。</div>';
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
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 17: Active Directory 深度利用：ACL·委派·跨林 <span class="badge badge-warning">350 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡深入探讨 Active Directory 中的复杂攻击向量，包括访问控制列表 (ACL) 的滥用、各种 Kerberos 委派攻击，以及林间信任边界的突破。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> AD ACL 滥用研究</h4>
        <p>Active Directory 的访问控制列表经常由于配置不当产生攻击路径。攻击者可以通过 BloodHound 分析路径，寻找 <code>GenericAll</code>, <code>GenericWrite</code>, 或 <code>WriteDACL</code> 权限节点。</p>
        <ul>
            <li>拥有修改密码权限（ForceChangePassword）可接管账户。</li>
            <li>利用 Shadow Credentials 写入 <code>msDS-KeyCredentialLink</code> 属性进行无密码 Kerberos 身份认证。</li>
            <li>获得对域对象的高级控制权后可配置 DCSync 权限直接拉取域哈希。</li>
        </ul>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> Kerberos 非约束性委派</h4>
        <p>当机器或账户被配置为“信任计算机进行委派”时，任何访问该主机的账户都会将其 TGT 缓存在主机的 LSASS 内存中。</p>
        <p>攻击者常通过如 Printer Bug（SpoolSample）强制域控或其他高权限机器对其进行认证，捕获其 TGT，从而模拟目标机器账户接管域控。</p>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> 约束性委派与基于资源的约束委派 (RBCD)</h4>
        <p>约束性委派通过 <code>S4U2Self</code>（模拟用户获取当前服务票据）和 <code>S4U2Proxy</code>（代表用户申请其他服务票据）机制实现。若攻击者控制了委派账户，可伪造任意用户访问特定服务。</p>
        <p>RBCD 允许资源所有者控制谁可以代表其他用户访问它，通过修改目标机器的 <code>msDS-AllowedToActOnBehalfOfOtherIdentity</code> 属性，攻击者能够配置一个受控的账户来模拟任意域管访问目标机器。</p>
        <div class="cmd-box">
<span class="comment">-- 获取 flag</span>
<span class="flag-text">echo 'flag{OSEP_L17_AD_Forest_ACL_Delegation_Abuse}'</span>
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> AD 森林信任攻击</h4>
        <p>林间的单向或双向信任可能允许攻击者跨林移动。利用 SID 历史注入（SID History），攻击者可以伪造林间票据（跨林 TGT 伪造）。</p>
        <p>在创建 Golden Ticket 时注入 Enterprise Admins 的 <code>ExtraSids</code>，可以打破本林的边界，在受信任林中获得最高权限。</p>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> 防御视角</h4>
        <p>在面对如此复杂的 AD 攻击面，防御方应考虑：</p>
        <ul>
            <li><strong>Tiered Administration Model：</strong>分层管理模型，阻止高权限凭据在低层级主机残留。</li>
            <li><strong>Credential Guard：</strong>启用 LSA 保护防止内存中凭据提取。</li>
            <li><strong>Protected Users：</strong>将管理员加入保护组，防止 Kerberos 委派和哈希缓存。</li>
            <li><strong>SID Filtering：</strong>确保林信任启用了 SID 过滤，防止跨林 SID History 注入。</li>
            <li>启用强化的 PAC 验证机制，定期使用 BloodHound 进行内部权限审计。</li>
        </ul>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline" style="justify-content: center;">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="输入Flag，例如 flag{...}" style="width: 300px;">
            </div>
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#6366f1; border-color:#4f46e5; margin-left:10px;">提交验证</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="osep_l16_linux_postex.php" class="btn btn-default">上一关</a>
            <a href="osep_hub.php" class="btn btn-default" style="margin-left:10px;">下一关 (返回 Hub)</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
