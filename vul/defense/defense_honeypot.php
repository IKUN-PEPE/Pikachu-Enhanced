<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Level 4: Honeypot Deception & Canary Tokens
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[225] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$honeypot_alert = null;

if (isset($_POST['trigger_honeypot']) || isset($_GET['trap_file'])) {
    $trap_name = isset($_GET['trap_file']) ? $_GET['trap_file'] : 'fake_admin_login';
    $honeypot_alert = [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'ua' => $_SERVER['HTTP_USER_AGENT'],
        'trap' => $trap_name,
        'time' => date('Y-m-d H:i:s'),
        'payload' => isset($_POST['username']) ? $_POST['username'] . ':' . $_POST['password'] : 'GET Download Request'
    ];
}
?>

<style>
.honey-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #d97706 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
}
.honey-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.alert-terminal {
    background: #0f172a;
    color: #fef08a;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.8;
    margin-top: 15px;
    border-left: 4px solid #f59e0b;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="honey-hero-banner">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <span class="label label-warning" style="font-size: 14px; border-radius: 6px;">LEVEL 4</span>
                            🍯 蜜罐 (Honeypot) 网络欺骗与 Canary 蜜标追踪实验室
                        </h1>
                        <p style="margin: 0; color: #fef3c7; font-size: 14px;">
                            <strong>防守维度：</strong> Cyber Deception (主动网络欺骗)、Canary Tokens (蜜标) 与攻击者指纹追踪
                        </p>
                    </div>
                    <a href="defense.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回蓝队总控大厅
                    </a>
                </div>
            </div>

            <!-- Theory -->
            <div class="honey-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-shield" style="color: #f59e0b;"></i> 主动网络欺骗 (Cyber Deception) 的防守价值</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    传统防御是被动的，而 **蜜罐 (Honeypot)** 与 **蜜标 (Canary Tokens)** 是一种主动诱捕技术。
                    蓝队故意在网站中暴露看似极具价值的“虚假资源”（如伪造的后台登录框 <code>/admin_login.php</code>、看似包含数据库密码的 <code>db_backup.sql.bak</code> 诱饵文件）。正常的业务流量绝不会触碰这些伪造路径，**任何触碰该路径的请求 100% 为入侵扫描者**，蓝队可实现零误报的实时报警与高维溯源！
                </p>
            </div>

            <div class="row">
                <!-- Trap Section -->
                <div class="col-md-6">
                    <div class="honey-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-crosshairs" style="color: #ef4444;"></i> 部署在内网中的蜜罐陷阱与 Canary 蜜标</h3>
                        <p style="color: var(--text-secondary); font-size: 13px;">请尝试模拟攻击者点击下载诱饵文件或提交伪造后台登录：</p>
                        
                        <!-- Trap 1: Fake Config Link -->
                        <div style="background: var(--bg-secondary); border: 1px dashed #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="margin-top:0; font-size: 15px; font-weight: bold; color: #d97706;"><i class="fa fa-file-code-o"></i> 陷阱 1: 敏感备份文件 (Canary Token File)</h4>
                            <p style="font-size: 13px; color: var(--text-secondary);">看似泄露的系统数据库口令备份文件：</p>
                            <a href="defense_honeypot.php?trap_file=db_backup_passwords.sql.bak" class="btn btn-warning btn-sm" style="border-radius: 6px; font-weight: bold;">
                                <i class="fa fa-download"></i> 模拟下载 db_backup_passwords.sql.bak
                            </a>
                        </div>

                        <!-- Trap 2: Fake Admin Portal -->
                        <div style="background: var(--bg-secondary); border: 1px dashed #ef4444; padding: 15px; border-radius: 8px;">
                            <h4 style="margin-top:0; font-size: 15px; font-weight: bold; color: #dc2626;"><i class="fa fa-lock"></i> 陷阱 2: 虚假核心系统管理员登录入口</h4>
                            <form method="post">
                                <input type="hidden" name="trigger_honeypot" value="1">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control" placeholder="管理员账号 (如: admin)" required style="border-radius: 6px;">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="管理员密码" required style="border-radius: 6px;">
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 6px; font-weight: bold;">
                                    <i class="fa fa-key"></i> 提交爆破登录请求
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Alert Dashboard -->
                <div class="col-md-6">
                    <div class="honey-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-bell" style="color: #f59e0b;"></i> SOC 蜜罐威胁告警控制台 (Zero False Positive)</h3>
                        
                        <?php if ($honeypot_alert === null) { ?>
                            <div class="alert alert-info" style="border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-info-circle"></i> 蜜罐捕获引擎正在静默监控中。一旦黑客触碰左侧诱饵资源，此处将触发极速高危告警。
                            </div>
                        <?php } else { ?>

                            <div class="alert alert-danger" style="border-radius: 8px; background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #dc2626;">
                                <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-warning"></i> 🚨 零误报警告: 蜜罐陷阱已捕获高危扫描行为！</h4>
                                <p style="margin-bottom: 0;">检测到未授权的非法攻击流量访问了纯欺骗诱饵路径 [<?php echo htmlspecialchars($honeypot_alert['trap']); ?>]！</p>
                            </div>

                            <div class="alert-terminal">
<span style="color: #ef4444;">[HONEYPOT_TRIGGER]</span> Timestamp: <?php echo $honeypot_alert['time']; ?>
<span style="color: #ef4444;">[HONEYPOT_TRIGGER]</span> Attacker IP: <?php echo $honeypot_alert['ip']; ?>
<span style="color: #fbbf24;">[TRAP_HIT]</span> Decoy Target: <?php echo htmlspecialchars($honeypot_alert['trap']); ?>
<span style="color: #38bdf8;">[FINGERPRINT]</span> User-Agent: <?php echo htmlspecialchars($honeypot_alert['ua']); ?>
<span style="color: #38bdf8;">[CAPTURED_INPUT]</span> Data: <?php echo htmlspecialchars($honeypot_alert['payload']); ?>
<span style="color: #34d399;">[AUTOMATED_ACTION]</span> Added IP <?php echo $honeypot_alert['ip']; ?> to Blacklist Firewall. Sent Alert to SOC Team.
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
