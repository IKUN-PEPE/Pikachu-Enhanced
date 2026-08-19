<?php
/**
 * Pikachu-Enhanced v2.0 - 会话固定漏洞演练 (登录后敏感信息中心 Profile)
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

if (!isset($_SESSION['sessionfixation']['username'])) {
    header('location:fixation_login.php');
    exit();
}

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[128] = 'active open';
$ACTIVE[131] = 'active';

$username = $_SESSION['sessionfixation']['username'];
$level = $_SESSION['sessionfixation']['level'] ?? 0;
$login_time = $_SESSION['sessionfixation']['login_time'] ?? date('Y-m-d H:i:s');
$sid_at_login = $_SESSION['sessionfixation']['sid_at_login'] ?? session_id();
$current_sid = session_id();

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.profile-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.profile-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.profile-terminal {
    background: #020617;
    border: 1px solid #10b981;
    border-radius: 12px;
    padding: 24px;
    color: #38bdf8;
    font-family: monospace;
    line-height: 1.8;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sessionfixation.php">Session Fixation</a></li>
                <li class="active">已登录会员敏感档案中心 (Profile)</li>
            </ul>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Hero Banner -->
            <div class="profile-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-id-card" style="color:#818cf8;"></i> 会员核心隐私档案中枢 (Authorized Session Active)
                    <span class="label label-success" style="border-radius:12px; font-size:11px; padding:3px 10px;">AUTH VERIFIED</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    此页面仅供已登录用户访问。若攻击者在未输入密码的情况下通过预设的固定 SID 成功进入并查看到此界面，即证明会话固定（Session Fixation）攻击链完整生效！
                </p>
            </div>

            <div class="row">
                <!-- Left: Session State & Profile Info -->
                <div class="col-md-6">
                    <div class="profile-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-user" style="color:var(--primary);"></i> 当前会话认证状态与档案信息
                        </h4>

                        <table class="table table-bordered" style="font-size:13px; margin-bottom:16px;">
                            <tbody>
                                <tr>
                                    <td style="width:35%; font-weight:600; background:var(--bg-secondary);">当前登录用户</td>
                                    <td><b style="color:#3b82f6;"><?php echo htmlspecialchars($username); ?></b></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">权限等级 (Level)</td>
                                    <td>
                                        <span class="label <?php echo $level == 1 ? 'label-danger' : 'label-info'; ?>" style="border-radius:4px; font-size:11px;">
                                            <?php echo $level == 1 ? '超级管理员 (Level 1)' : '普通会员 (Level 2)'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">会话登录时间</td>
                                    <td style="font-family:monospace;"><?php echo htmlspecialchars($login_time); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">当前生效 Session ID</td>
                                    <td style="font-family:monospace; color:#f59e0b; word-break:break-all;"><?php echo htmlspecialchars($current_sid); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="display:flex; gap:10px;">
                            <a href="fixation_login.php" class="btn btn-default btn-sm" style="border-radius:6px;">
                                <i class="fa fa-arrow-left"></i> 返回登录演练控制台
                            </a>
                            <a href="fixation_login.php?logout=1" class="btn btn-danger btn-sm" style="border-radius:6px;">
                                <i class="fa fa-power-off"></i> 安全注销当前会话
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Exploit Verification & Flag -->
                <div class="col-md-6">
                    <div class="profile-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-shield" style="color:var(--primary);"></i> 漏洞利用验证与会话凭证
                        </h4>

                        <div class="profile-terminal">
                            <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:10px;">
                                <i class="fa fa-check-circle"></i> 🎯 凭证劫持攻击验证成功 (Session Fixation Confirmed)
                            </div>
                            <div>
                                [+] 目标账户: <b><?php echo htmlspecialchars($username); ?></b><br>
                                [+] 登录前预置 SID: <code><?php echo htmlspecialchars($sid_at_login); ?></code><br>
                                [+] 登录后当前 SID: <code><?php echo htmlspecialchars($current_sid); ?></code><br>
                                [+] 漏洞根因: 服务端登录后缺失 <code>session_regenerate_id(true)</code><br>
                                [+] 会话固定通关 Flag: <br>
                                <div style="margin:8px 0; background:rgba(245,158,11,0.15); border:1px solid #f59e0b; padding:8px 10px; border-radius:6px;">
                                    <span style="color:#f59e0b; font-weight:bold; font-size:14px;">flag{Session_Fixation_PreAuth_Hijack_Success}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="sessionfixation.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 返回模块概述
                </a>
                <a href="fixation_login.php" class="btn btn-primary" style="border-radius:8px;">
                    前往登录漏洞页面 (Login Lab) <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
