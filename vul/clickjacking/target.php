<?php
/**
 * Pikachu-Enhanced v2.0 - Clickjacking (点击劫持) 目标页面重构
 * 模拟受害者业务系统核心账户与安全控制中心 (Victim Target Portal)
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

// 读取或切换防御模式
$defense_mode = isset($_GET['defense']) ? $_GET['defense'] : (isset($_SESSION['cj_target_defense']) ? $_SESSION['cj_target_defense'] : 'none');
if (isset($_GET['defense'])) {
    $_SESSION['cj_target_defense'] = $defense_mode;
}

// 依据防御模式发送 HTTP 安全响应头 (必须在任何输出前执行)
if ($defense_mode === 'x_frame_sameorigin') {
    header('X-Frame-Options: SAMEORIGIN');
} elseif ($defense_mode === 'x_frame_deny') {
    header('X-Frame-Options: DENY');
} elseif ($defense_mode === 'csp') {
    header("Content-Security-Policy: frame-ancestors 'self'");
}

// 初始化 Session 账户状态
if (!isset($_SESSION['cj_target_email'])) {
    $_SESSION['cj_target_email'] = 'admin@pikachu.com';
}
if (!isset($_SESSION['cj_target_2fa'])) {
    $_SESSION['cj_target_2fa'] = true;
}
if (!isset($_SESSION['cj_target_messages'])) {
    $_SESSION['cj_target_messages'] = [
        ['id' => 101, 'title' => '安全登录通知：管理员 admin 已从内网终端完成认证', 'time' => '10分钟前', 'type' => 'normal'],
        ['id' => 102, 'title' => '安全告警：监测到疑似点击劫持外部 iframe 探针扫描', 'time' => '1小时前', 'type' => 'warning'],
        ['id' => 103, 'title' => '基准策略生效：双因子认证 (2FA) 与敏感审计记录已开启', 'time' => '昨天 15:30', 'type' => 'info']
    ];
}
if (!isset($_SESSION['cj_target_history'])) {
    $_SESSION['cj_target_history'] = [
        ['time' => date('H:i:s', time() - 1800), 'event' => '账户初始化安全基准建立完成', 'status' => '安全', 'level' => 'success']
    ];
}

$action_message = null;
$action_status = 'info';

// 处理敏感操作请求 (点击劫持攻击核心利用目标)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'change_email') {
        $old_email = $_SESSION['cj_target_email'];
        $_SESSION['cj_target_email'] = 'attacker@pikachu.com';
        $_SESSION['cj_target_history'][] = [
            'time' => date('H:i:s'),
            'event' => "安全邮箱被篡改: {$old_email} ➔ attacker@pikachu.com (未进行二次口令核验)",
            'status' => '🚨 严重越权',
            'level' => 'danger'
        ];
        $action_message = "🚨 <strong>【高危敏感操作被触发】</strong> 账户安全邮箱已成功换绑为：<code>attacker@pikachu.com</code>！攻击者现已可通过密码重置链路完全接管该管理员账号！";
        $action_status = 'danger';
    } elseif ($action === 'delete_message') {
        if (!empty($_SESSION['cj_target_messages'])) {
            $del = array_pop($_SESSION['cj_target_messages']);
            $_SESSION['cj_target_history'][] = [
                'time' => date('H:i:s'),
                'event' => "一条敏感审计日志被销毁: [{$del['title']}]",
                'status' => '⚠️ 日志擦除',
                'level' => 'warning'
            ];
            $action_message = "⚠️ <strong>【安全审计日志已销毁】</strong> 成功删除一条关键追踪日志。";
            $action_status = 'warning';
        } else {
            $action_message = "ℹ️ 当前没有更多可删除的安全消息。";
            $action_status = 'info';
        }
    } elseif ($action === 'toggle_2fa') {
        $_SESSION['cj_target_2fa'] = !$_SESSION['cj_target_2fa'];
        $state_str = $_SESSION['cj_target_2fa'] ? '开启' : '关闭';
        $_SESSION['cj_target_history'][] = [
            'time' => date('H:i:s'),
            'event' => "管理员 2FA 双因子认证状态被切换为 [{$state_str}]",
            'status' => $_SESSION['cj_target_2fa'] ? '安全' : '🚨 风险',
            'level' => $_SESSION['cj_target_2fa'] ? 'success' : 'danger'
        ];
        $action_message = "🔐 <strong>【双因子认证策略变更】</strong> 2FA 状态已更新为：<strong>{$state_str}</strong>。";
        $action_status = $_SESSION['cj_target_2fa'] ? 'success' : 'danger';
    } elseif ($action === 'reset') {
        $_SESSION['cj_target_email'] = 'admin@pikachu.com';
        $_SESSION['cj_target_2fa'] = true;
        $_SESSION['cj_target_messages'] = [
            ['id' => 101, 'title' => '安全登录通知：管理员 admin 已从内网终端完成认证', 'time' => '10分钟前', 'type' => 'normal'],
            ['id' => 102, 'title' => '安全告警：监测到疑似点击劫持外部 iframe 探针扫描', 'time' => '1小时前', 'type' => 'warning'],
            ['id' => 103, 'title' => '基准策略生效：双因子认证 (2FA) 与敏感审计记录已开启', 'time' => '昨天 15:30', 'type' => 'info']
        ];
        $_SESSION['cj_target_history'] = [
            ['time' => date('H:i:s'), 'event' => '账户安全基准已重置恢复', 'status' => '安全', 'level' => 'success']
        ];
        $action_message = "✅ <strong>【状态已恢复】</strong> 账户安全绑定信息与审计日志已重置为安全初始状态。";
        $action_status = 'success';
    }
}

// 计算安全评分
$sec_score = 100;
if ($_SESSION['cj_target_email'] !== 'admin@pikachu.com') $sec_score -= 50;
if (!$_SESSION['cj_target_2fa']) $sec_score -= 30;
if ($defense_mode === 'none') $sec_score -= 20;
if ($sec_score < 0) $sec_score = 0;

$is_compromised = ($_SESSION['cj_target_email'] !== 'admin@pikachu.com');

// 判断是否为 iframe 专用纯净模式
$is_iframe_param = (isset($_GET['in_iframe']) && $_GET['in_iframe'] == '1');
?>
<?php if ($is_iframe_param): ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>企业员工安全中心 - 敏感操作 (Target Frame)</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "PingFang SC", sans-serif;
            background: #ffffff;
            color: #0f172a;
        }
        .iframe-card {
            background: #ffffff;
            border: 2px solid <?php echo $is_compromised ? '#ef4444' : '#e2e8f0'; ?>;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            max-width: 500px;
            margin: 0 auto;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-safe { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .btn-action {
            display: block;
            width: 100%;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-danger-custom {
            background: #dc2626;
            color: #ffffff;
        }
        .btn-danger-custom:hover {
            background: #b91c1c;
        }
        .btn-warning-custom {
            background: #f59e0b;
            color: #ffffff;
            margin-top: 10px;
        }
        .notice-box {
            margin-top: 14px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.5;
            background: #f1f5f9;
        }
    </style>
    <?php if ($defense_mode === 'framebuster'): ?>
    <script>
        if (top !== self) {
            alert('🛡️ [JS Framebuster 触发] 检测到外部 iframe 尝试劫持嵌套，正在强制破框重定向！');
            top.location = self.location.href;
        }
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="iframe-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-weight:800; font-size:16px; color:#1e293b; display:flex; align-items:center; gap:6px;">
                <i class="ph-bold ph-shield-check" style="color:#4f46e5;"></i> Pikachu 企业账户安全中心
            </div>
            <span class="badge <?php echo $is_compromised ? 'badge-danger' : 'badge-safe'; ?>">
                <?php echo $is_compromised ? '🚨 邮箱已被劫持' : '🛡️ 状态正常'; ?>
            </span>
        </div>

        <div style="background:#f8fafc; border-radius:8px; padding:12px; margin-bottom:16px; font-size:13px;">
            <div style="margin-bottom:6px;"><strong>当前登录：</strong> admin (系统超级管理员)</div>
            <div>
                <strong>绑定邮箱：</strong>
                <span style="font-family:monospace; color:<?php echo $is_compromised ? '#dc2626' : '#2563eb'; ?>; font-weight:700;">
                    <?php echo htmlspecialchars($_SESSION['cj_target_email']); ?>
                </span>
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <a href="target.php?action=change_email&in_iframe=1&defense=<?php echo urlencode($defense_mode); ?>" 
               id="target-change-email-btn"
               class="btn-action btn-danger-custom">
                <i class="ph-bold ph-envelope-simple"></i> 一键换绑安全邮箱 (无需验证码)
            </a>
            <a href="target.php?action=delete_message&in_iframe=1&defense=<?php echo urlencode($defense_mode); ?>" 
               class="btn-action btn-warning-custom">
                <i class="ph-bold ph-trash"></i> 销毁安全审计消息
            </a>
        </div>

        <?php if ($action_message): ?>
            <div class="notice-box" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b;">
                <?php echo $action_message; ?>
            </div>
            <script>
                // 通知父级页面攻击成功
                if (window.parent) {
                    window.parent.postMessage('CLICKJACKING_SUCCESS', '*');
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
<?php exit; endif; ?>

<?php
// 标准完整页面模式 (包含 Pikachu 顶栏、侧边栏与页脚)
$ACTIVE = array_fill(0, 150, '');
$ACTIVE[136] = 'active open';
$ACTIVE[138] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
/* 核心视觉与现代卡片系统 */
.target-hero-banner {
    background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #172554 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: var(--radius-xl, 16px);
    padding: 30px 34px;
    color: #ffffff;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.25);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.target-hero-banner::after {
    content: "TARGET";
    position: absolute;
    right: 25px;
    bottom: -20px;
    font-size: 110px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    pointer-events: none;
    line-height: 1;
    font-family: monospace;
}
.target-hero-title {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #f8fafc;
}
.target-badge-tag {
    background: rgba(99, 102, 241, 0.2);
    color: #c7d2fe;
    border: 1px solid rgba(199, 210, 254, 0.3);
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
}
.target-hero-desc {
    font-size: 14px;
    color: #cbd5e1;
    line-height: 1.65;
    max-width: 980px;
    margin: 0;
}

/* 控制条与实时探测面板 */
.target-control-bar {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.defense-btn-group {
    display: inline-flex;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
}
.defense-btn-group a {
    padding: 8px 14px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    background: #f8fafc;
    color: #475569;
    border-right: 1px solid #cbd5e1;
    transition: all 0.15s ease;
}
.defense-btn-group a:last-child {
    border-right: none;
}
.defense-btn-group a.active-none {
    background: #fee2e2;
    color: #991b1b;
    font-weight: 700;
}
.defense-btn-group a.active-defense {
    background: #dcfce7;
    color: #166534;
    font-weight: 700;
}

/* 卡片布局通用样式 */
.app-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.app-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* 危险区 Danger Zone 核心卡片 */
.danger-zone-box {
    border: 2px dashed #f87171;
    background: rgba(254, 242, 242, 0.4);
    border-radius: 12px;
    padding: 20px;
    margin-top: 18px;
}

/* 按钮设计 */
.btn-target-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff !important;
    border: none;
    padding: 13px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14.5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    text-decoration: none !important;
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-target-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}

/* 交互式 Clickjacking 模拟器 */
.inline-simulator-stage {
    position: relative;
    width: 100%;
    height: 280px;
    background: #f8fafc;
    border: 2px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 14px;
}
.inline-decoy-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1;
    padding: 20px;
    text-align: center;
}
.inline-iframe-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
    z-index: 2;
    transition: opacity 0.1s ease;
}

/* 状态审计列表 */
.audit-timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 13px;
}
.audit-timeline-item:last-child {
    border-bottom: none;
}

/* 当整个页面不幸被直接 iframe 嵌入时的自适应 */
.is-framed #navbar,
.is-framed #sidebar,
.is-framed #breadcrumbs,
.is-framed .footer {
    display: none !important;
}
.is-framed .main-content {
    margin-left: 0 !important;
    padding: 0 !important;
}
</style>

<?php if ($defense_mode === 'framebuster'): ?>
<script>
    if (top !== self) {
        alert('🛡️ [JS Framebuster 触发] 检测到外部 iframe 尝试劫持嵌套，正在强制破框重定向！');
        top.location = self.location.href;
    }
</script>
<?php endif; ?>

<script>
    // 检测是否处于 iframe 中
    if (window.self !== window.top) {
        document.documentElement.classList.add('is-framed');
    }
</script>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="clickjacking.php">Clickjacking 点击劫持</a></li>
                <li class="active">Target 目标受害者业务控制台</li>
            </ul>
            <div style="float:right; display:flex; align-items:center; gap:8px;">
                <a href="attacker.php" class="btn btn-xs btn-danger" style="border-radius:4px; font-weight:700;">
                    <i class="ph-bold ph-skull"></i> 前往黑客钓鱼页面 (attacker.php)
                </a>
            </div>
        </div>

        <div class="page-content">
            
            <!-- Hero Header Banner -->
            <div class="target-hero-banner">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                    <span class="target-badge-tag"><i class="ph-bold ph-crosshair"></i> 受害者目标端 (Victim Portal)</span>
                    <span class="target-badge-tag" style="background:rgba(239,68,68,0.2); color:#fca5a5; border-color:rgba(252,165,165,0.3);">CWE-1021</span>
                    <span class="target-badge-tag" style="background:rgba(245,158,11,0.2); color:#fde68a; border-color:rgba(253,230,138,0.3);">CVSS 4.3 中危</span>
                </div>
                <h1 class="target-hero-title">
                    🎯 Pikachu 云平台 · 用户账户安全与敏感操作管理中心
                </h1>
                <p class="target-hero-desc">
                    当前页面代表企业内部真实的“账户管理控制台”。如果服务端未在 HTTP 响应头中配置 <code>X-Frame-Options</code> 或 CSP <code>frame-ancestors</code> 防御策略，恶意攻击者便可在第三方诱导网页中使用透明 <code>&lt;iframe&gt;</code> 完整嵌入当前页面，诱骗用户在毫不知情下点击“换绑邮箱/销毁日志”等高危接口，达成点击劫持越权！
                </p>
            </div>

            <!-- 控制条: 防御模式切换与重置 -->
            <div class="target-control-bar">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <span style="font-size:13.5px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-shield-check" style="color:#4f46e5;"></i> 选择当前服务端的防护策略：
                    </span>
                    <div class="defense-btn-group">
                        <a href="target.php?defense=none" class="<?php echo $defense_mode==='none' ? 'active-none' : ''; ?>">
                            ❌ 无防御 (脆弱允许嵌套)
                        </a>
                        <a href="target.php?defense=x_frame_sameorigin" class="<?php echo $defense_mode==='x_frame_sameorigin' ? 'active-defense' : ''; ?>">
                            🛡️ X-Frame-Options: SAMEORIGIN
                        </a>
                        <a href="target.php?defense=x_frame_deny" class="<?php echo $defense_mode==='x_frame_deny' ? 'active-defense' : ''; ?>">
                            🔒 X-Frame-Options: DENY
                        </a>
                        <a href="target.php?defense=csp" class="<?php echo $defense_mode==='csp' ? 'active-defense' : ''; ?>">
                            🌐 CSP: frame-ancestors 'self'
                        </a>
                        <a href="target.php?defense=framebuster" class="<?php echo $defense_mode==='framebuster' ? 'active-defense' : ''; ?>">
                            📜 JS Framebusting 破框脚本
                        </a>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="target.php?action=reset&defense=<?php echo urlencode($defense_mode); ?>" class="btn btn-sm btn-default" style="border-radius:6px; font-weight:600;">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i> 重置安全基线
                    </a>
                </div>
            </div>

            <!-- 操作触发提示框 -->
            <?php if ($action_message): ?>
                <div class="alert alert-<?php echo $action_status; ?>" style="border-radius:10px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <i class="ph-bold ph-info" style="font-size:18px; vertical-align:middle; margin-right:6px;"></i>
                    <?php echo $action_message; ?>
                </div>
            <?php endif; ?>

            <!-- 主内容双栏布局 -->
            <div class="row">
                
                <!-- 左栏: 核心业务账户与高危敏感操作区 (受害者视图) -->
                <div class="col-md-6">
                    
                    <!-- 账户安全状态卡片 -->
                    <div class="app-card">
                        <div class="app-card-title">
                            <span><i class="ph-bold ph-user-circle-gear" style="color:#4f46e5;"></i> 管理员凭据与安全评分</span>
                            <span style="font-size:12px; font-weight:normal; color:#64748b;">
                                安全评分: <strong style="font-size:15px; color:<?php echo $sec_score >= 80 ? '#16a34a' : ($sec_score >= 50 ? '#f59e0b' : '#dc2626'); ?>"><?php echo $sec_score; ?>/100</strong>
                            </span>
                        </div>

                        <!-- 安全评分进度条 -->
                        <div style="height:8px; background:#e2e8f0; border-radius:9999px; overflow:hidden; margin-bottom:20px;">
                            <div style="height:100%; width:<?php echo $sec_score; ?>%; background:<?php echo $sec_score >= 80 ? '#22c55e' : ($sec_score >= 50 ? '#f59e0b' : '#ef4444'); ?>; transition:width 0.4s ease;"></div>
                        </div>

                        <!-- 个人信息列表 -->
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:40px; height:40px; border-radius:50%; background:#4f46e5; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px;">
                                        A
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:#0f172a; font-size:14px;">admin (系统超级管理员)</div>
                                        <div style="font-size:12px; color:#64748b;">UID: 10001 · 拥有全部管理特权</div>
                                    </div>
                                </div>
                                <span class="badge" style="background:#e0e7ff; color:#4338ca; font-weight:700; padding:4px 8px;">Super Admin</span>
                            </div>

                            <hr style="margin:12px 0; border-color:#e2e8f0;">

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:13px;">
                                <div>
                                    <span style="color:#64748b; display:block; margin-bottom:2px;">绑定安全邮箱：</span>
                                    <strong style="font-family:monospace; color:<?php echo $is_compromised ? '#dc2626' : '#0f172a'; ?>; font-size:13.5px;">
                                        <?php echo htmlspecialchars($_SESSION['cj_target_email']); ?>
                                    </strong>
                                    <?php if ($is_compromised): ?>
                                        <div style="font-size:11.5px; color:#ef4444; margin-top:2px; font-weight:700;">
                                            🚨 危险！已绑定黑客受控邮箱！
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span style="color:#64748b; display:block; margin-bottom:2px;">双因子验证 (2FA)：</span>
                                    <span class="badge" style="background:<?php echo $_SESSION['cj_target_2fa'] ? '#dcfce7' : '#fee2e2'; ?>; color:<?php echo $_SESSION['cj_target_2fa'] ? '#166534' : '#991b1b'; ?>;">
                                        <?php echo $_SESSION['cj_target_2fa'] ? '已开启 (TOTP/SMS)' : '已关闭 (弱防护)'; ?>
                                    </span>
                                </div>
                                <div>
                                    <span style="color:#64748b; display:block; margin-bottom:2px;">密保手机：</span>
                                    <strong style="font-family:monospace; color:#0f172a;">138****8888</strong>
                                </div>
                                <div>
                                    <span style="color:#64748b; display:block; margin-bottom:2px;">有效安全消息：</span>
                                    <strong style="color:#0f172a;"><?php echo count($_SESSION['cj_target_messages']); ?> 条未归档</strong>
                                </div>
                            </div>
                        </div>

                        <!-- 危险区: 点击劫持的核心利用目标按钮 -->
                        <div class="danger-zone-box">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; color:#b91c1c; font-weight:800; font-size:14px;">
                                <i class="ph-bold ph-warning-octagon"></i> 核心敏感操作区 (Clickjacking 核心攻击热区)
                            </div>
                            <p style="font-size:12.5px; color:#7f1d1d; margin-bottom:16px; line-height:1.5;">
                                ⚠️ <strong>攻防演练说明</strong>：下方按钮代表业务系统中缺少“二次密码输入”或“短信验证码”保护的高危单向操作。攻击者将本页面嵌入透明 iframe 后，诱导层的大奖按钮将精准重叠在下方按钮位置！
                            </p>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div>
                                    <a href="target.php?action=change_email&defense=<?php echo urlencode($defense_mode); ?>" 
                                       id="btn-victim-email"
                                       class="btn-target-danger" 
                                       style="width:100%;">
                                        <i class="ph-bold ph-envelope-simple"></i> 一键换绑安全邮箱 (无需二次确认)
                                    </a>
                                    <span style="font-size:11.5px; color:#64748b; display:block; margin-top:4px;">
                                        点击即执行换绑，直接将安全邮箱转移给 <code>attacker@pikachu.com</code>。
                                    </span>
                                </div>

                                <div style="display:flex; gap:10px;">
                                    <a href="target.php?action=delete_message&defense=<?php echo urlencode($defense_mode); ?>" 
                                       class="btn btn-warning" 
                                       style="flex:1; border-radius:8px; font-weight:700; padding:10px;">
                                        <i class="ph-bold ph-trash"></i> 销毁安全审计消息
                                    </a>
                                    <a href="target.php?action=toggle_2fa&defense=<?php echo urlencode($defense_mode); ?>" 
                                       class="btn btn-default" 
                                       style="flex:1; border-radius:8px; font-weight:700; padding:10px;">
                                        <i class="ph-bold ph-lock-key"></i> 切换 2FA 状态
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- 实时 HTTP 响应头监控卡片 -->
                    <div class="app-card">
                        <div class="app-card-title">
                            <span><i class="ph-bold ph-globe-hemisphere-west" style="color:#0ea5e9;"></i> 当前响应 HTTP 头部实时审计</span>
                            <span class="badge" style="background:#e2e8f0; color:#334155;">Network Echo</span>
                        </div>
                        <div style="background:#0f172a; color:#f8fafc; border-radius:10px; padding:14px; font-family:monospace; font-size:12.5px; line-height:1.6;">
                            <div style="color:#94a3b8;">// 当前输出的防点击劫持响应头：</div>
                            <?php if ($defense_mode === 'none'): ?>
                                <div style="color:#ef4444; font-weight:700;">// 警告：未检测到任何 X-Frame-Options 或 CSP frame-ancestors 响应头！</div>
                                <div>X-Frame-Options: <span style="color:#ef4444;">(NOT SET)</span></div>
                                <div>Content-Security-Policy: <span style="color:#ef4444;">(NOT SET)</span></div>
                            <?php elseif ($defense_mode === 'x_frame_sameorigin'): ?>
                                <div style="color:#22c55e;">X-Frame-Options: SAMEORIGIN</div>
                                <div style="color:#94a3b8;">Content-Security-Policy: (NOT SET)</div>
                            <?php elseif ($defense_mode === 'x_frame_deny'): ?>
                                <div style="color:#22c55e;">X-Frame-Options: DENY</div>
                                <div style="color:#94a3b8;">Content-Security-Policy: (NOT SET)</div>
                            <?php elseif ($defense_mode === 'csp'): ?>
                                <div style="color:#94a3b8;">X-Frame-Options: (NOT SET)</div>
                                <div style="color:#38bdf8;">Content-Security-Policy: frame-ancestors 'self'</div>
                            <?php elseif ($defense_mode === 'framebuster'): ?>
                                <div style="color:#f59e0b;">// 前端 JS Framebusting 脚本已嵌入页面顶部：</div>
                                <div style="color:#fde68a;">if (top !== self) top.location = self.location.href;</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- 右栏: 交互式点击劫持视觉欺骗 POC 模拟器 + 审计日志 -->
                <div class="col-md-6">
                    
                    <!-- 内置点击劫持模拟视窗 -->
                    <div class="app-card">
                        <div class="app-card-title">
                            <span><i class="ph-bold ph-eye" style="color:#8b5cf6;"></i> 点击劫持“双层视窗”解密模拟器 (PoC Preview)</span>
                            <span class="badge" style="background:#f3e8ff; color:#7e22ce;">Interactive Sandbox</span>
                        </div>
                        <p style="font-size:13px; color:#64748b; margin-bottom:12px; line-height:1.6;">
                            拖动下方滑块调节透明度，直观揭秘黑客如何通过透明图层将“大奖诱饵按钮”覆盖在真实的“换绑邮箱按钮”之上：
                        </p>

                        <!-- 透明度滑块控制器 -->
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:14px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <label style="font-size:13px; font-weight:700; color:#334155; margin:0;">
                                    目标 iframe 透明度：<span id="slider-pct" style="color:#4f46e5; font-size:15px;">50%</span>
                                </label>
                                <span style="font-size:11.5px; color:#64748b;">
                                    [0% 完全可见] $\longleftrightarrow$ [100% 完全隐形欺骗]
                                </span>
                            </div>
                            <input type="range" id="opacityRange" min="0" max="100" value="50" style="width:100%; cursor:pointer;">
                        </div>

                        <!-- 模拟演练画布 -->
                        <div class="inline-simulator-stage">
                            <!-- 底层: 黑客钓鱼诱导层 -->
                            <div class="inline-decoy-layer">
                                <div style="font-size:20px; font-weight:900; color:#b45309; margin-bottom:6px;">
                                    🎁 恭喜！您获得 Pikachu 云周年庆限量抽奖！
                                </div>
                                <div style="font-size:13px; color:#92400e; margin-bottom:20px;">
                                    点击下方按钮即可 100% 免费领取 Apple iPhone 16 Pro 一台：
                                </div>
                                <button style="background:linear-gradient(135deg, #ef4444, #b91c1c); color:#fff; border:none; padding:14px 44px; font-size:16px; font-weight:900; border-radius:50px; box-shadow:0 4px 15px rgba(239,68,68,0.4); pointer-events:none;">
                                    🎉 立即免费领取大奖 🎉
                                </button>
                                <div style="font-size:11px; color:#b45309; margin-top:12px;">
                                    （当前为诱导层按钮，实际点击事件将被上方的透明敏感按钮截获）
                                </div>
                            </div>

                            <!-- 顶层: 透明嵌套的目标页面 (使用 in_iframe 模式精准对齐按钮) -->
                            <iframe id="sim-iframe" 
                                    src="target.php?in_iframe=1&defense=<?php echo urlencode($defense_mode); ?>" 
                                    class="inline-iframe-layer" 
                                    style="opacity: 0.5;">
                            </iframe>
                        </div>

                        <div style="margin-top:14px; font-size:12.5px; color:#64748b; line-height:1.6; display:flex; justify-content:space-between; align-items:center;">
                            <span>💡 在画布上点击“立即免费领取”，观察受害者邮箱是否瞬间被篡改！</span>
                            <a href="attacker.php" class="btn btn-xs btn-primary" style="border-radius:4px; font-weight:700;">
                                <i class="ph-bold ph-arrow-square-out"></i> 打开独立黑客诱导站
                            </a>
                        </div>
                    </div>

                    <!-- 账户敏感事件审计流水卡片 -->
                    <div class="app-card">
                        <div class="app-card-title">
                            <span><i class="ph-bold ph-clock-counter-clockwise" style="color:#059669;"></i> 账户敏感操作安全审计追踪</span>
                            <span class="badge" style="background:#ecfdf5; color:#047857;"><?php echo count($_SESSION['cj_target_history']); ?> 条记录</span>
                        </div>

                        <div style="max-height:220px; overflow-y:auto; padding-right:6px;">
                            <?php foreach (array_reverse($_SESSION['cj_target_history']) as $hist): ?>
                                <div class="audit-timeline-item">
                                    <span style="font-family:monospace; color:#64748b; font-size:12px; white-space:nowrap;">
                                        <?php echo htmlspecialchars($hist['time']); ?>
                                    </span>
                                    <div style="flex:1;">
                                        <div style="font-weight:600; color:#1e293b;"><?php echo htmlspecialchars($hist['event']); ?></div>
                                    </div>
                                    <span class="badge" style="font-size:11px; background:<?php echo $hist['level']==='danger' ? '#fee2e2' : ($hist['level']==='warning' ? '#fef3c7' : '#dcfce7'); ?>; color:<?php echo $hist['level']==='danger' ? '#991b1b' : ($hist['level']==='warning' ? '#92400e' : '#166534'); ?>;">
                                        <?php echo htmlspecialchars($hist['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 安全防御与代码修复指引 -->
                    <div class="app-card">
                        <div class="app-card-title">
                            <span><i class="ph-bold ph-shield-checkered" style="color:#2563eb;"></i> 蓝队标准修复与配置指引</span>
                        </div>
                        <div style="font-size:13px; color:#475569; line-height:1.7;">
                            <strong>1. Nginx 全局配置防御头 (推荐)：</strong>
                            <pre style="background:#0f172a; color:#38bdf8; border-radius:6px; padding:10px; font-size:12px; margin:6px 0 12px 0;">add_header X-Frame-Options "SAMEORIGIN" always;
add_header Content-Security-Policy "frame-ancestors 'self'" always;</pre>

                            <strong>2. PHP 应用程序层头部设置：</strong>
                            <pre style="background:#0f172a; color:#34d399; border-radius:6px; padding:10px; font-size:12px; margin:6px 0 12px 0;">header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");</pre>

                            <strong>3. 核心业务交互防御原则：</strong>
                            对于“修改绑定邮箱/转账/删除数据”等高危操作，严格推行<strong>二次身份认证（输入原密码 / 短信动态验证码 / 生物认证）</strong>，从业务逻辑根源阻断盲目点击劫持。
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var slider = document.getElementById('opacityRange');
    var iframe = document.getElementById('sim-iframe');
    var pctText = document.getElementById('slider-pct');

    if (slider && iframe && pctText) {
        slider.addEventListener('input', function() {
            var val = slider.value;
            // 当 slider 为 0 时，iframe 100% 可见 (opacity 1.0)
            // 当 slider 为 100 时，iframe 0% 可见 (完全透明隐形)
            var op = (100 - val) / 100;
            iframe.style.opacity = op;
            pctText.textContent = val + '% (透明度 ' + Math.round(op * 100) + '%)';
        });
    }

    // 监听模拟器 iframe 内部的点击与换绑反馈
    window.addEventListener('message', function(e) {
        if (e.data === 'CLICKJACKING_SUCCESS') {
            console.log('[Clickjacking PoC] 捕获到目标 iframe 敏感操作触发！');
            // 刷新页面状态以更新评分与审计日志
            setTimeout(function() {
                window.location.reload();
            }, 600);
        }
    });
});
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
