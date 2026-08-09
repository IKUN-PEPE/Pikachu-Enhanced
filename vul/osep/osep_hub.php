<?php
/**
 * Pikachu-Enhanced v2.0 OSEP Advanced Penetration Testing CTF Hub
 * OSCE³ - OSEP Direction: 7 Stages, 1650 PTS Total
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[251] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

if (!isset($_SESSION['osep_flags'])) {
    $_SESSION['osep_flags'] = [];
}

$flags_db = [
    'flag1' => ['flag' => 'flag{OSEP_L1_OPSEC_Enum_NoiseReduction_Done}', 'name' => 'L1: 初始侦察与 OPSEC 操守', 'points' => 100, 'file' => 'osep_l1_enum.php', 'difficulty' => '入门'],
    'flag2' => ['flag' => 'flag{OSEP_L2_Phishing_Macro_HTA_Delivery}', 'name' => 'L2: 钓鱼向量与载荷投递机制', 'points' => 150, 'file' => 'osep_l2_phishing.php', 'difficulty' => '初级'],
    'flag3' => ['flag' => 'flag{OSEP_L3_Lateral_WMI_PSRemoting_Pass}', 'name' => 'L3: 横向移动 WMI/PS-Remoting', 'points' => 200, 'file' => 'osep_l3_lateral.php', 'difficulty' => '中级'],
    'flag4' => ['flag' => 'flag{OSEP_L4_Pivot_Chisel_SOCKS5_Tunnel}', 'name' => 'L4: 内网穿透 Chisel/SSHuttle', 'points' => 250, 'file' => 'osep_l4_pivot.php', 'difficulty' => '中级'],
    'flag5' => ['flag' => 'flag{OSEP_L5_AV_AMSI_ETW_Defense_Arch}', 'name' => 'L5: 杀软检测架构与防御研究', 'points' => 300, 'file' => 'osep_l5_av_evasion.php', 'difficulty' => '高级'],
    'flag6' => ['flag' => 'flag{OSEP_L6_Persistence_Task_Reg_Service}', 'name' => 'L6: 持久化 计划任务/注册表/服务', 'points' => 300, 'file' => 'osep_l6_persistence.php', 'difficulty' => '高级'],
    'flag7' => ['flag' => 'flag{OSEP_L7_Exfil_DNS_ICMP_HTTP_Channel}', 'name' => 'L7: 数据外渗通道分析与防御', 'points' => 350, 'file' => 'osep_l7_exfil.php', 'difficulty' => '专家'],
];

$submit_msg = '';
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input']);
    $matched = false;
    foreach ($flags_db as $k => $item) {
        if ($user_flag === $item['flag']) {
            $_SESSION['osep_flags'][$k] = true;
            $submit_msg = '<div class="alert alert-success" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！成功提交【' . $item['name'] . '】Flag，获得 ' . $item['points'] . ' 积分！🎉</div>';
            $matched = true;
            break;
        }
    }
    if (!$matched && !empty($user_flag)) {
        $submit_msg = '<div class="alert alert-danger" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 错误，请根据关卡操作指南仔细推演后再提交！</div>';
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $_SESSION['osep_flags'] = [];
    header("Location: osep_hub.php");
    exit;
}

$total_score = 0;
$captured_count = 0;
foreach ($flags_db as $k => $item) {
    if (isset($_SESSION['osep_flags'][$k]) && $_SESSION['osep_flags'][$k]) {
        $total_score += $item['points'];
        $captured_count++;
    }
}
$progress_pct = count($flags_db) > 0 ? round(($captured_count / count($flags_db)) * 100) : 0;
?>

<style>
.osep-hero-banner {
    background: linear-gradient(135deg, #0c0a1e 0%, #1a0533 50%, #2d1b69 100%);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 40px rgba(109, 40, 217, 0.3);
    margin-bottom: 25px;
    border: 1px solid rgba(139, 92, 246, 0.3);
    position: relative;
    overflow: hidden;
}
.osep-hero-banner::before {
    content: 'OSEP';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 100px;
    font-weight: 900;
    color: rgba(139, 92, 246, 0.08);
    letter-spacing: -2px;
    pointer-events: none;
}
.osep-title {
    font-size: 26px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.osep-badge {
    background: rgba(139, 92, 246, 0.25);
    color: #c4b5fd;
    border: 1px solid #8b5cf6;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}
.diff-badge {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.diff-入门 { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid #10b981; }
.diff-初级 { background: rgba(59,130,246,0.15); color: #93c5fd; border: 1px solid #3b82f6; }
.diff-中级 { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid #f59e0b; }
.diff-高级 { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid #ef4444; }
.diff-专家 { background: rgba(139,92,246,0.15); color: #c4b5fd; border: 1px solid #8b5cf6; }
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
    box-shadow: 0 8px 25px rgba(139, 92, 246, 0.15);
    border-color: #8b5cf6;
}
.level-card.completed { border-left: 6px solid #10b981; }
.level-card.uncompleted { border-left: 6px solid #8b5cf6; }
.level-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.level-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.points-tag {
    background: rgba(139, 92, 246, 0.15);
    color: #8b5cf6;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 14px;
}
.level-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 14px;
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
    background: linear-gradient(90deg, #8b5cf6, #ec4899);
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}
.stat-pill {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    color: #e2e8f0;
}
.cert-roadmap {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 25px;
}
.roadmap-step {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}
.roadmap-step:last-child { border-bottom: none; }
.roadmap-num {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">

            <!-- Hero Header -->
            <div class="osep-hero-banner">
                <h1 class="osep-title">
                    🎯 OSCE³ · OSEP 进阶渗透测试 CTF 靶场
                    <span class="osep-badge">7 大关卡 · 1650 PTS</span>
                    <span class="osep-badge" style="background: rgba(236,72,153,0.2); color: #f9a8d4; border-color: #ec4899;">OSCE³ 认证路径</span>
                </h1>
                <p style="font-size: 15px; color: #c4b5fd; line-height: 1.7; max-width: 950px; margin: 15px 0 20px 0;">
                    对标 Offensive Security OSEP (PEN-300) 考纲，覆盖 <strong style="color: #e9d5ff;">OPSEC 侦察 → 钓鱼投递 → 横向移动 → 内网穿透 → 杀软检测架构 → 持久化 → 数据外渗检测</strong> 完整进攻链路。每个关卡提供详细步骤教学与 Flag 验证。
                </p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="stat-pill"><i class="fa fa-flag" style="color: #ec4899;"></i> 通关进度：<strong><?php echo $captured_count; ?> / <?php echo count($flags_db); ?></strong> 关</div>
                    <div class="stat-pill"><i class="fa fa-trophy" style="color: #fbbf24;"></i> 当前积分：<strong><?php echo $total_score; ?> / 1650</strong> PTS</div>
                    <div class="stat-pill"><i class="fa fa-certificate" style="color: #34d399;"></i> 目标认证：<strong>OSEP (PEN-300)</strong></div>
                    <div class="stat-pill"><i class="fa fa-clock-o" style="color: #93c5fd;"></i> 考试时长：<strong>47.75 小时</strong></div>
                </div>
            </div>

            <!-- OSEP 考纲对应 -->
            <div class="cert-roadmap">
                <h4 style="margin-top:0; font-weight: 700; color: var(--text-primary);">
                    <i class="fa fa-map" style="color: #8b5cf6;"></i> OSEP PEN-300 考纲覆盖路径
                </h4>
                <div class="roadmap-step">
                    <div class="roadmap-num">01</div>
                    <div><strong style="color: var(--text-primary);">客户端攻击面</strong><br><span style="font-size: 12px; color: var(--text-secondary);">Macro、HTA、JScript/VBScript 载荷投递，OPSEC 优化的侦察方法论</span></div>
                </div>
                <div class="roadmap-step">
                    <div class="roadmap-num">02</div>
                    <div><strong style="color: var(--text-primary);">进程注入与规避</strong><br><span style="font-size: 12px; color: var(--text-secondary);">AMSI/ETW 防御架构理解，基于防御者视角的检测规则构建</span></div>
                </div>
                <div class="roadmap-step">
                    <div class="roadmap-num">03</div>
                    <div><strong style="color: var(--text-primary);">横向移动技术</strong><br><span style="font-size: 12px; color: var(--text-secondary);">WMI、PS-Remoting、DCOM、SMB、RDP 各协议横向路径</span></div>
                </div>
                <div class="roadmap-step">
                    <div class="roadmap-num">04</div>
                    <div><strong style="color: var(--text-primary);">网络穿透与代理</strong><br><span style="font-size: 12px; color: var(--text-secondary);">Chisel、SSHuttle、Ligolo-ng、SOCKS5 多层隧道搭建</span></div>
                </div>
                <div class="roadmap-step">
                    <div class="roadmap-num">05</div>
                    <div><strong style="color: var(--text-primary);">持久化与维持访问</strong><br><span style="font-size: 12px; color: var(--text-secondary);">计划任务、服务、注册表、WMI 订阅 四大持久化路径</span></div>
                </div>
            </div>

            <!-- Flag Submit Bar -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h4 style="margin: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-paper-plane" style="color: #8b5cf6;"></i> OSEP Flag 验证与提交中心</h4>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-secondary);">完成每个关卡的模拟操作后，提交 flag{...} 格式 Flag 即可点亮成就积分。</p>
                    </div>
                    <form method="post" style="display: flex; gap: 10px; flex-grow: 1; max-width: 500px;">
                        <input type="text" name="flag_input" class="form-control" placeholder="flag{...}" required style="border-radius: 8px; font-family: monospace;">
                        <button type="submit" name="submit_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; min-width: 110px; background: #8b5cf6; border-color: #8b5cf6;">
                            <i class="fa fa-check"></i> 提交 Flag
                        </button>
                        <a href="osep_hub.php?reset=1" class="btn btn-default" title="重置进度" style="border-radius: 8px;" onclick="return confirm('确定重置所有 OSEP Flag 进度吗？');"><i class="fa fa-refresh"></i></a>
                    </form>
                </div>
                <?php if (!empty($submit_msg)) { echo '<div style="margin-top: 15px;">' . $submit_msg . '</div>'; } ?>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?php echo $progress_pct; ?>;"></div>
                </div>
                <div style="text-align: right; font-size: 12px; color: var(--text-secondary);">进度：<?php echo $progress_pct; ?>% (<?php echo $captured_count; ?>/<?php echo count($flags_db); ?> 关)</div>
            </div>

            <!-- 7 Levels Grid -->
            <div class="row">
                <?php
                $descriptions = [
                    'flag1' => '学习如何在不触发告警的前提下进行网络侦察，掌握 OPSEC 操作安全意识，理解被动侦察（Shodan、OSINT）与主动侦察（端口扫描节奏控制）的差异。',
                    'flag2' => '研究企业钓鱼攻击向量，包括 Office Macro 宏文档、HTA 应用、DDE 注入，理解投递链路中的沙箱检测机制与邮件网关过滤逻辑。',
                    'flag3' => '深入分析 WMI 执行、PowerShell Remoting、DCOM 实例化等横向移动技术的底层机制，理解各协议在 Windows 事件日志中的取证痕迹。',
                    'flag4' => '掌握 Chisel SOCKS5 代理隧道、SSHuttle 透明代理、Ligolo-ng 全局路由穿透的搭建与调试，理解多层 NAT 穿透原理。',
                    'flag5' => '从防御者视角研究 AMSI（反恶意软件扫描接口）和 ETW（Windows 事件追踪）的工作机制，理解杀软检测模型与 EDR 行为分析架构。',
                    'flag6' => '分析 Windows 持久化四大技术路径：计划任务、服务注册、注册表 Run 键、WMI 事件订阅，理解各路径的告警规则与防御检测点。',
                    'flag7' => '研究 DNS 隐蔽通道、ICMP 隧道、HTTPS 回传等数据外渗检测技术，理解 DLP 系统的工作原理和网络层检测策略。',
                ];
                foreach ($flags_db as $key => $item) {
                    $is_done = isset($_SESSION['osep_flags'][$key]) && $_SESSION['osep_flags'][$key];
                ?>
                <div class="col-md-6">
                    <div class="level-card <?php echo $is_done ? 'completed' : 'uncompleted'; ?>">
                        <div class="level-header">
                            <h3 class="level-title">
                                <span style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800;">
                                    <?php echo str_replace('flag', '', $key); ?>
                                </span>
                                <?php echo $item['name']; ?>
                            </h3>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <span class="diff-badge diff-<?php echo $item['difficulty']; ?>"><?php echo $item['difficulty']; ?></span>
                                <span class="points-tag"><?php echo $item['points']; ?> PTS</span>
                            </div>
                        </div>
                        <p class="level-desc"><?php echo $descriptions[$key]; ?></p>
                        <div class="level-actions">
                            <div>
                                <?php if ($is_done) { ?>
                                    <span class="label label-success" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-check"></i> 已通关</span>
                                <?php } else { ?>
                                    <span class="label label-default" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-clock-o"></i> 待挑战</span>
                                <?php } ?>
                            </div>
                            <a href="<?php echo $item['file']; ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700; background: #8b5cf6; border-color: #8b5cf6;">
                                进入关卡 <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
