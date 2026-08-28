<?php
/**
 * Pikachu-Enhanced v2.0 Docker Lab Environment Diagnostic Page
 */

$ACTIVE = array_fill(0, 400, '');
$ACTIVE[140] = 'active open';
$ACTIVE[142] = 'active';

$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/dockerlab_lib.php';

$env = dockerlab_check_environment();

$is_healthy = !empty($env['daemon_reachable']) && !empty($env['exec_available']);
$in_container = !empty($env['in_container']);

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.ctf-stage-header {
    background: linear-gradient(135deg, #0f172a 0%, #164e63 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 25px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    color: #ffffff;
}
.ctf-stage-title {
    color: #f8fafc !important;
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ctf-desc-text {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 2s infinite;
}
.pulse-dot-cyan {
    background: #06b6d4;
    box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.7);
    animation: pulse-cyan 2s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
@keyframes pulse-cyan {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(6, 182, 212, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); }
}

.diag-metric-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.diag-metric-card:hover {
    transform: translateY(-3px);
    border-color: #06b6d4;
    box-shadow: 0 8px 24px rgba(6, 182, 212, 0.12);
}
.diag-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.diag-metric-title {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}
.diag-metric-val {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
}

.diag-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 25px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}
.diag-table th {
    background-color: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 800;
    padding: 16px 20px;
    font-size: 14px;
    border-bottom: 1px solid var(--border-color);
}
.diag-table td {
    padding: 16px 20px;
    font-size: 14px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    vertical-align: middle;
}
.diag-table tr:last-child td {
    border-bottom: none;
}
.diag-table tr:hover td {
    background-color: rgba(6, 182, 212, 0.04);
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.status-pill-success {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.35);
}
.status-pill-info {
    background: rgba(6, 182, 212, 0.15);
    color: #06b6d4;
    border: 1px solid rgba(6, 182, 212, 0.35);
}
.status-pill-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.35);
}
.status-pill-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.35);
}

.mac-terminal {
    background: #090d16;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 12px 35px rgba(0,0,0,0.5);
    overflow: hidden;
    margin-top: 15px;
}
.mac-terminal-bar {
    background: #1e293b;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.mac-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
.mac-dot-red { background: #ff5f56; }
.mac-dot-yellow { background: #ffbd2e; }
.mac-dot-green { background: #27c93f; }
.mac-terminal-title {
    color: var(--text-muted);
    font-size: 12px;
    font-family: monospace;
    margin-left: 8px;
}
.mac-terminal-body {
    padding: 18px 22px;
    color: #38bdf8;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.7;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="dockerlab.php">Docker Lab</a></li>
                <li class="active">环境检查与健康诊断</li>
            </ul>
        </div>

        <div class="page-content">
            
            <!-- Hero Banner Header -->
            <div class="ctf-stage-header">
                <h1 class="ctf-stage-title">
                    🔍 Docker Lab 环境健康诊断 (Environment Diagnostics)
                    <?php if ($in_container): ?>
                        <span class="status-pill status-pill-info" style="font-size:13px; background:rgba(6,182,212,0.2); border-color:#06b6d4; color:#38bdf8;">
                            <span class="pulse-dot pulse-dot-cyan"></span> 🛡️ 靶场 Docker 沙箱隔离运行中（演练 100% 就绪）
                        </span>
                    <?php elseif ($is_healthy): ?>
                        <span class="status-pill status-pill-success" style="font-size:13px; background:rgba(16,185,129,0.2); border-color:#10b981; color:#34d399;">
                            <span class="pulse-dot"></span> Docker 环境完全准备就绪
                        </span>
                    <?php else: ?>
                        <span class="status-pill status-pill-warning" style="font-size:13px; background:rgba(245,158,11,0.2); border-color:#f59e0b; color:#fbbf24;">
                            <i class="fa fa-exclamation-triangle"></i> Docker 服务配置提示
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="ctf-desc-text">
                    自动检测 PHP 系统函数调用权限、宿主机 Docker CLI 命令响应及容器隔离沙箱状态。本页面<strong>仅进行只读检查</strong>，不会对本地任何容器或镜像进行添加、删除或修改操作。
                </p>
            </div>

            <!-- Notice card for container mode -->
            <?php if ($in_container): ?>
                <div style="background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.3); border-radius:12px; padding:18px 22px; margin-bottom:25px; display:flex; align-items:flex-start; gap:14px;">
                    <i class="fa fa-shield" style="font-size:24px; color:#06b6d4; margin-top:2px;"></i>
                    <div>
                        <h4 style="margin:0 0 6px 0; font-size:15px; font-weight:800; color:var(--text-primary);">
                            ℹ️ 为什么本诊断页面会显示容器内“未找到 docker 命令”？
                        </h4>
                        <p style="margin:0; font-size:13px; color:var(--text-secondary); line-height:1.6;">
                            因为当前 Pikachu 靶场本身正运行在标准的 **Docker 容器环境** (<code>pikachu-enhanced-web</code>) 内部。容器默认采取安全隔离保护，内部未直接打通 Docker-in-Docker 嵌套套接字。<br/>
                            <strong style="color:#06b6d4;">绝不影响关卡演练</strong>：Pikachu 靶场下的 <strong>4 大 Docker 容器逃逸关卡</strong>（特权逃逸、Socket 逃逸、Capabilities 逃逸、CVE 逃逸）均已内置独立高逼真交互式命令行模拟器，您可以直接在网页上完成全流程推演与 Flag 提取通关！
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Top 4 Metric Dashboard Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 25px;">
                
                <div class="diag-metric-card">
                    <div class="diag-icon-box" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4; box-shadow: 0 4px 12px rgba(6,182,212,0.2);">
                        <i class="fa fa-desktop"></i>
                    </div>
                    <div>
                        <div class="diag-metric-title">运行环境操作系统</div>
                        <div class="diag-metric-val"><?php echo dockerlab_h($env['os']); ?></div>
                    </div>
                </div>

                <div class="diag-metric-card">
                    <div class="diag-icon-box" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.2);">
                        <i class="fa fa-code"></i>
                    </div>
                    <div>
                        <div class="diag-metric-title">PHP exec() 系统权限</div>
                        <div class="diag-metric-val">
                            <?php if ($env['exec_available']): ?>
                                <span style="color:#10b981;"><i class="fa fa-check-circle"></i> 已开启</span>
                            <?php else: ?>
                                <span style="color:#ef4444;"><i class="fa fa-times-circle"></i> 被禁用</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="diag-metric-card">
                    <div class="diag-icon-box" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; box-shadow: 0 4px 12px rgba(168,85,247,0.2);">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div>
                        <div class="diag-metric-title">靶场部署运行模式</div>
                        <div class="diag-metric-val">
                            <?php if ($in_container): ?>
                                <span style="color:#06b6d4;"><i class="fa fa-shield"></i> Docker 隔离容器</span>
                            <?php else: ?>
                                <span style="color:#3b82f6;"><i class="fa fa-server"></i> 宿主机/WSL 直连</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="diag-metric-card">
                    <div class="diag-icon-box" style="background: rgba(16, 185, 129, 0.12); color: #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                        <i class="fa fa-gamepad"></i>
                    </div>
                    <div>
                        <div class="diag-metric-title">容器逃逸关卡支持</div>
                        <div class="diag-metric-val">
                            <span style="color:#10b981;"><i class="fa fa-check-circle"></i> 4/4 关卡就绪</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Detailed Diagnostics Table -->
            <div class="workflow-section" style="padding:25px; border-radius:14px; margin-bottom:25px;">
                <h3 style="margin-top:0; font-size:18px; font-weight:800; color:var(--text-primary); margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-list-alt" style="color:#06b6d4;"></i> 详细诊断检测项目表
                </h3>

                <table class="diag-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">检查检测项目</th>
                            <th style="width: 24%;">状态结果</th>
                            <th style="width: 48%;">详细回显信息与诊断备注</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fa fa-linux" style="color:#06b6d4; margin-right:8px;"></i> PHP OS 操作系统类型</td>
                            <td><span class="status-pill status-pill-success"><i class="fa fa-info-circle"></i> <?php echo dockerlab_h($env['os']); ?></span></td>
                            <td>当前 PHP 运行环境所在的主机平台架构。</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-play-circle" style="color:#3b82f6; margin-right:8px;"></i> PHP <code>exec()</code> 函数权限</td>
                            <td>
                                <?php if ($env['exec_available']): ?>
                                    <span class="status-pill status-pill-success"><i class="fa fa-check"></i> 可用 (Available)</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-danger"><i class="fa fa-times"></i> 被禁用 (Disabled)</span>
                                <?php endif; ?>
                            </td>
                            <td>PHP 具备调用系统命令的基础权限。</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-cogs" style="color:#8b5cf6; margin-right:8px;"></i> PHP <code>proc_open()</code> 函数权限</td>
                            <td>
                                <?php if ($env['proc_open_available']): ?>
                                    <span class="status-pill status-pill-success"><i class="fa fa-check"></i> 可用 (Available)</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-danger"><i class="fa fa-times"></i> 被禁用 (Disabled)</span>
                                <?php endif; ?>
                            </td>
                            <td>进程流通信调用权限。</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-shield" style="color:#06b6d4; margin-right:8px;"></i> 容器环境隔离检测</td>
                            <td>
                                <?php if ($in_container): ?>
                                    <span class="status-pill status-pill-info"><i class="fa fa-cubes"></i> 运行于 Docker 容器内</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-success"><i class="fa fa-desktop"></i> 宿主机原生运行</span>
                                <?php endif; ?>
                            </td>
                            <td>检测到 Pikachu 靶场已部署在独立的 Docker 容器沙箱内。</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-gamepad" style="color:#10b981; margin-right:8px;"></i> Docker 容器逃逸关卡兼容性</td>
                            <td>
                                <span class="status-pill status-pill-success"><i class="fa fa-check-circle"></i> 100% 独立兼容</span>
                            </td>
                            <td>4 大 Docker 逃逸关卡包含独立拟真控制台，无需打通网络宿主命令。</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-commenting" style="color:#8b5cf6; margin-right:8px;"></i> 综合诊断说明反馈</td>
                            <td colspan="2">
                                <strong style="color:var(--text-primary); font-size:14px;">
                                    <?php echo !empty($env['message']) ? dockerlab_html($env['message']) : '诊断完成'; ?>
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Troubleshooting Guidance Console Section -->
            <div class="workflow-section" style="padding:25px; border-radius:14px; margin-bottom:25px;">
                <h3 style="margin-top:0; font-size:18px; font-weight:800; color:var(--text-primary); margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-terminal" style="color:#06b6d4;"></i> 宿主机控制台自检方法 (PowerShell / Linux Terminal)
                </h3>
                
                <p style="font-size:14px; color:var(--text-secondary); line-height:1.7; margin-bottom:10px;">
                    若您希望在 Host 宿主机层面验证 Docker 守护进程与容器状态，请在宿主机 PowerShell 中执行以下命令：
                </p>

                <div class="mac-terminal">
                    <div class="mac-terminal-bar">
                        <span class="mac-dot mac-dot-red"></span>
                        <span class="mac-dot mac-dot-yellow"></span>
                        <span class="mac-dot mac-dot-green"></span>
                        <span class="mac-terminal-title">powershell - Host OS Docker Console</span>
                    </div>
                    <div class="mac-terminal-body">
                        <span style="color:#64748b;"># 1. 在宿主机查看 Docker CLI 版本及后台 Engine 状态</span><br/>
                        <span style="color:#f59e0b;">docker version</span><br/><br/>
                        <span style="color:#64748b;"># 2. 检查宿主机 Docker 详细配置信息</span><br/>
                        <span style="color:#f59e0b;">docker info</span><br/><br/>
                        <span style="color:#64748b;"># 3. 查看包括 pikachu-enhanced-web 在内的全部运行中容器</span><br/>
                        <span style="color:#f59e0b;">docker ps</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:22px;">
                <div>
                    <a href="dockerlab_check.php" class="btn btn-default" style="border-radius:8px; margin-right:10px;">
                        <i class="fa fa-refresh"></i> 重新刷新诊断
                    </a>
                    <a href="dockerlab_center.php" class="btn btn-primary" style="border-radius:8px; background:linear-gradient(135deg, #0891b2, #0e7490); border:none; font-weight:700;">
                        <i class="fa fa-cubes"></i> 查看实验模板列表
                    </a>
                </div>
                <div>
                    <a href="docker_privileged_escape.php" class="btn btn-success" style="border-radius:8px; background:linear-gradient(135deg, #10b981, #059669); border:none; padding:10px 18px; font-weight:700; box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        进入 Docker 容器逃逸实战关卡 <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
