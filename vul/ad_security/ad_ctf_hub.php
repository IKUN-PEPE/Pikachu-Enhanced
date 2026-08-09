<?php
/**
 * Pikachu-Enhanced v2.0 GOAD Active Directory CTF Master Hub (10 Stages - 2500 PTS)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[238] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// Session-based flag score tracker
if (!isset($_SESSION['goad_flags'])) {
    $_SESSION['goad_flags'] = [];
}

$flags_db = [
    'flag1' => ['flag' => 'flag{KingsLanding_BloodHound_Recon_2026}', 'name' => '第一关：内网侦察与 BloodHound 域结构测绘', 'points' => 100, 'file' => 'ad_ctf_recon.php'],
    'flag2' => ['flag' => 'flag{ASREP_Roasting_No_PreAuth_Found}', 'name' => '第二关：AS-REP Roasting 预认证缺失爆破', 'points' => 150, 'file' => 'ad_ctf_asrep.php'],
    'flag3' => ['flag' => 'flag{Kerberoast_MSSQL_Service_Ticket_Cracked}', 'name' => '第三关：Kerberoasting SPN 服务票据离线破解', 'points' => 200, 'file' => 'ad_ctf_kerberoast.php'],
    'flag4' => ['flag' => 'flag{MSSQL_Execute_As_Sa_XP_Cmdshell}', 'name' => '第四关：MSSQL 模拟特权与 xp_cmdshell 提权', 'points' => 250, 'file' => 'ad_ctf_mssql.php'],
    'flag5' => ['flag' => 'flag{ADCS_ESC1_Cert_Authority_Administrator}', 'name' => '第五关：AD CS 证书服务 ESC1 模板滥用', 'points' => 300, 'file' => 'ad_ctf_adcs.php'],
    'flag6' => ['flag' => 'flag{Constrained_Delegation_S4U2Proxy_TGT_Impersonation}', 'name' => '第六关：约束性委派 S4U2Proxy 票据提权', 'points' => 300, 'file' => 'ad_ctf_delegation.php'],
    'flag7' => ['flag' => 'flag{Resource_Based_Constrained_Delegation_RBCD_Pwned}', 'name' => '第七关：基于资源的约束委派 (RBCD) 跃迁', 'points' => 300, 'file' => 'ad_ctf_rbcd.php'],
    'flag8' => ['flag' => 'flag{ADCS_ESC8_NTLM_Relay_HTTP_Enrollment}', 'name' => '第八关：AD CS ESC8 NTLM HTTP 中继注册', 'points' => 350, 'file' => 'ad_ctf_esc8.php'],
    'flag9' => ['flag' => 'flag{Shadow_Credentials_KeyCredentialLink_PKINIT}', 'name' => '第九关：影子凭据 (Shadow Credentials) 维持', 'points' => 350, 'file' => 'ad_ctf_shadow_cred.php'],
    'flag10' => ['flag' => 'flag{Forest_Root_DC_Kingslanding_Fully_Owned}', 'name' => '第十关：ACL 链式滥用与林根完全接管', 'points' => 500, 'file' => 'ad_ctf_acl.php']
];

$submit_msg = '';
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input']);
    $matched = false;
    foreach ($flags_db as $k => $item) {
        if ($user_flag === $item['flag']) {
            $_SESSION['goad_flags'][$k] = true;
            $submit_msg = '<div class="alert alert-success" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！成功提交【' . $item['name'] . '】Flag，获得 ' . $item['points'] . ' 积分！🎉</div>';
            $matched = true;
            break;
        }
    }
    if (!$matched && !empty($user_flag)) {
        $submit_msg = '<div class="alert alert-danger" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 错误，请根据关卡操作指南仔细推演！</div>';
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $_SESSION['goad_flags'] = [];
    header("Location: ad_ctf_hub.php");
    exit;
}

$total_score = 0;
$captured_count = 0;
foreach ($flags_db as $k => $item) {
    if (isset($_SESSION['goad_flags'][$k]) && $_SESSION['goad_flags'][$k]) {
        $total_score += $item['points'];
        $captured_count++;
    }
}
$progress_pct = count($flags_db) > 0 ? round(($captured_count / count($flags_db)) * 100) : 0;
?>

<style>
.ctf-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.12);
    position: relative;
    overflow: hidden;
}
.ctf-hero-banner::after {
    content: '\f135';
    font-family: 'FontAwesome';
    position: absolute;
    right: -15px;
    bottom: -25px;
    font-size: 190px;
    color: rgba(255,255,255,0.03);
    pointer-events: none;
}
.ctf-title {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.ctf-badge {
    background: rgba(244, 63, 94, 0.25);
    color: #fda4af;
    border: 1px solid #f43f5e;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.level-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    transition: all 0.25s ease;
    position: relative;
}
.level-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
    border-color: #6366f1;
}
.level-card.completed {
    border-left: 6px solid #10b981;
}
.level-card.uncompleted {
    border-left: 6px solid #6366f1;
}
.level-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.level-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.points-tag {
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 14px;
}
.level-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 15px;
}
.level-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
}
.progress-bar-custom {
    background: var(--bg-secondary);
    border-radius: 10px;
    height: 14px;
    overflow: hidden;
    margin: 15px 0;
    border: 1px solid var(--border-color);
}
.progress-bar-fill {
    background: linear-gradient(90deg, #6366f1, #10b981);
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="ctf-hero-banner">
                <h1 class="ctf-title">
                    🏆 GOAD Active Directory 域渗透 CTF 夺旗总控大厅
                    <span class="ctf-badge">10 大关卡大师版</span>
                </h1>
                <p style="font-size: 15px; color: #e2e8f0; line-height: 1.7; max-width: 950px; margin-bottom: 20px;">
                    本大厅已扩容至 <strong>10 大经典 AD 域渗透实战关卡（总分 2500 PTS）</strong>，涵盖侦察测绘、票据攻击、数据库提权、委派攻击 (S4U/RBCD)、AD CS 证书服务 (ESC1/ESC8)、影子凭据与 7 级 ACL 复合权限跃迁！
                </p>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; font-size: 13px;">
                        <i class="fa fa-flag" style="color: #f43f5e;"></i> 通关进度：<strong><?php echo $captured_count; ?> / <?php echo count($flags_db); ?></strong> 关卡
                    </div>
                    <div style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; font-size: 13px;">
                        <i class="fa fa-trophy" style="color: #fbbf24;"></i> 当前积分：<strong><?php echo $total_score; ?> / 2500</strong> PTS
                    </div>
                    <div style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; font-size: 13px;">
                        <i class="fa fa-server" style="color: #34d399;"></i> 靶场支持：<strong>3台精简版 (GOAD-Light) 与 5台完整版 (Full GOAD)</strong>
                    </div>
                </div>
            </div>

            <!-- Flag Submit Bar -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h4 style="margin: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-paper-plane" style="color: #6366f1;"></i> 统一 Flag 验证与提交中心</h4>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-secondary);">在各关卡完成攻防演练获取 Flag 后，在此输入验证即可点亮成就与累加积分。</p>
                    </div>
                    <form method="post" style="display: flex; gap: 10px; flex-grow: 1; max-width: 500px;">
                        <input type="text" name="flag_input" class="form-control" placeholder="flag{...}" required style="border-radius: 8px; font-family: monospace;">
                        <button type="submit" name="submit_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; min-width: 110px;">
                            <i class="fa fa-check"></i> 提交 Flag
                        </button>
                        <a href="ad_ctf_hub.php?reset=1" class="btn btn-default" title="重置做题进度" style="border-radius: 8px;" onclick="return confirm('确定重置所有 Flag 进度吗？');">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </form>
                </div>
                <?php if (!empty($submit_msg)) { echo '<div style="margin-top: 15px;">' . $submit_msg . '</div>'; } ?>
                
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?php echo $progress_pct; ?>%;"></div>
                </div>
            </div>

            <!-- CTF 10 Levels Grid -->
            <div class="row">
                
                <?php foreach ($flags_db as $key => $item) { 
                    $is_done = isset($_SESSION['goad_flags'][$key]) && $_SESSION['goad_flags'][$key];
                ?>
                <div class="col-md-6">
                    <div class="level-card <?php echo $is_done ? 'completed' : 'uncompleted'; ?>">
                        <div class="level-header">
                            <h3 class="level-title">
                                <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">
                                    <?php echo str_replace('flag', '', $key); ?>
                                </span>
                                <?php echo $item['name']; ?>
                            </h3>
                            <span class="points-tag"><?php echo $item['points']; ?> PTS</span>
                        </div>
                        <div class="level-actions">
                            <div>
                                <?php if ($is_done) { ?>
                                    <span class="label label-success" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-check"></i> 已通关</span>
                                <?php } else { ?>
                                    <span class="label label-default" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-clock-o"></i> 未完成</span>
                                <?php } ?>
                            </div>
                            <a href="<?php echo $item['file']; ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
                                进入关卡挑战 <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
