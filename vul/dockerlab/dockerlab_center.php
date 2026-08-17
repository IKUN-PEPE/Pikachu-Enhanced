<?php
/**
 * Pikachu-Enhanced v2.0 Global Microservices & Container Range Console
 */

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[143] = 'active';

$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/dockerlab_lib.php';

$action_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = trim($_POST['lab_id'] ?? '');
    $lab_act = trim($_POST['lab_act'] ?? '');
    
    if ($lab_id !== '' && dockerlab_get_template($lab_id)) {
        $tmpl = dockerlab_get_template($lab_id);
        if ($lab_act === 'start' || $lab_act === 'restart') {
            dockerlab_toggle_container_state($lab_id, 'start');
            $action_msg = "<div class='alert alert-success' style='border-radius:12px; font-weight:700; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#10b981;'><i class='fa fa-check-circle'></i> <b>启动成功！</b> 靶场【" . dockerlab_html($tmpl['name']) . "】已成功拉起，内部服务及通道已准备就绪。</div>";
        } else if ($lab_act === 'stop') {
            dockerlab_toggle_container_state($lab_id, 'stop');
            $action_msg = "<div class='alert alert-warning' style='border-radius:12px; font-weight:700; background:rgba(245,158,11,0.15); border:1px solid #f59e0b; color:#f59e0b;'><i class='fa fa-info-circle'></i> <b>操作成功！</b> 靶场【" . dockerlab_html($tmpl['name']) . "】已成功停止。</div>";
        }
    }
}

$env = dockerlab_check_environment();
$templates = dockerlab_load_templates();

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
.ctf-badge-chip {
    background: rgba(6, 182, 212, 0.2);
    color: #06b6d4;
    border: 1px solid #06b6d4;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.ctf-desc-text {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

.console-stat-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.console-stat-card:hover {
    transform: translateY(-3px);
    border-color: #06b6d4;
    box-shadow: 0 8px 24px rgba(6, 182, 212, 0.12);
}
.console-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 22px;
    margin-bottom: 30px;
}
.template-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.template-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}
.template-card.card-cloudnative::before { background: linear-gradient(90deg, #10b981, #059669); }
.template-card.card-web::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }
.template-card.card-middleware::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
.template-card.card-db::before { background: linear-gradient(90deg, #a855f7, #7c3aed); }

.template-card:hover {
    transform: translateY(-4px);
    border-color: #06b6d4;
    box-shadow: 0 10px 28px rgba(6, 182, 212, 0.15);
}
.template-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}
.template-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.35;
}
.cat-badge {
    padding: 4px 10px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    flex-shrink: 0;
}
.cat-cloudnative { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.cat-web { background: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.3); }
.cat-db { background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); }
.cat-middleware { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }

.meta-item {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 7px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.meta-item code {
    background: var(--bg-secondary);
    color: #06b6d4;
    padding: 2px 7px;
    border-radius: 4px;
    font-family: 'Fira Code', monospace;
    font-size: 11px;
    border: 1px solid var(--border-color);
}

.template-footer {
    border-top: 1px solid var(--border-color);
    padding-top: 16px;
    margin-top: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-tab-btn {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    padding: 7px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-right: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.filter-tab-btn:hover, .filter-tab-btn.active {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: #ffffff;
    border-color: #06b6d4;
    box-shadow: 0 4px 12px rgba(6,182,212,0.25);
}

.search-input-box {
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 7px 18px;
    font-size: 13px;
    width: 260px;
    transition: all 0.2s ease;
}
.search-input-box:focus {
    border-color: #06b6d4;
    box-shadow: 0 0 12px rgba(6, 182, 212, 0.25);
    outline: none;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">主页</a></li>
                <li class="active">⚡ 容器与微服务运行控制台 (Container Console)</li>
            </ul>
        </div>

        <div class="page-content">
            
            <!-- Hero Stage Header Banner -->
            <div class="ctf-stage-header">
                <h1 class="ctf-stage-title">
                    ⚡ 容器与微服务运行控制台 (Container Console)
                    <span class="ctf-badge-chip">已集成 <?php echo count($templates); ?> 套全平台靶场</span>
                </h1>
                <p class="ctf-desc-text">
                    集中管控平台下所有的微服务、数据库及云原生容器靶场。涵盖 4 大 Docker 容器逃逸关卡、K8s Token 越权、Flask SSTI、Log4j2 JNDI 注入、Fastjson 反序列化及 MySQL / Redis 场景，支持一键启动、停止、重置与通道跳转。
                </p>
            </div>

            <?php if (!empty($action_msg)) echo $action_msg; ?>

            <!-- Top 4 Summary Stat Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 25px;">
                <div class="console-stat-card">
                    <div class="console-icon-box" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4; box-shadow: 0 4px 12px rgba(6,182,212,0.2);">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary);">装载靶场总数</div>
                        <div style="font-size:16px; font-weight:800; color:var(--text-primary);"><?php echo count($templates); ?> 套全平台靶场</div>
                    </div>
                </div>

                <div class="console-stat-card">
                    <div class="console-icon-box" style="background: rgba(16, 185, 129, 0.12); color: #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                        <i class="fa fa-power-off"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary);">运行状态模式</div>
                        <div style="font-size:16px; font-weight:800; color:#10b981;">按需拉起 / 独立沙箱</div>
                    </div>
                </div>

                <div class="console-stat-card">
                    <div class="console-icon-box" style="background: rgba(168, 85, 247, 0.12); color: #a855f7; box-shadow: 0 4px 12px rgba(168,85,247,0.2);">
                        <i class="fa fa-shield"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary);">安全隔离保护</div>
                        <div style="font-size:16px; font-weight:800; color:var(--text-primary);">Docker 容器防护</div>
                    </div>
                </div>

                <div class="console-stat-card">
                    <div class="console-icon-box" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; box-shadow: 0 4px 12px rgba(245,158,11,0.2);">
                        <i class="fa fa-compass"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary);">攻防领域涵盖</div>
                        <div style="font-size:16px; font-weight:800; color:var(--text-primary);">4 大核心演练领域</div>
                    </div>
                </div>
            </div>

            <!-- Action Bar & Filter Tabs & Live Search -->
            <div style="margin-bottom: 22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <button class="filter-tab-btn active" onclick="filterCat('all', this)">全部靶场 (<?php echo count($templates); ?>)</button>
                    <button class="filter-tab-btn" onclick="filterCat('cloudnative', this)">☁️ 云原生与逃逸</button>
                    <button class="filter-tab-btn" onclick="filterCat('web', this)">🌐 Web 漏洞</button>
                    <button class="filter-tab-btn" onclick="filterCat('middleware', this)">⚙️ 中间件与微服务</button>
                    <button class="filter-tab-btn" onclick="filterCat('db', this)">🛢️ 数据库安全</button>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" id="labSearch" class="search-input-box" placeholder="🔍 快速搜索靶场名称、镜像或漏洞..." onkeyup="doSearch()">
                    <a href="dockerlab_check.php" class="btn btn-xs btn-info" style="border-radius:14px; padding:6px 14px; background:#0891b2; border-color:#0891b2; font-weight:700;">
                        <i class="fa fa-stethoscope"></i> 容器诊断
                    </a>
                </div>
            </div>

            <!-- Template Cards Grid -->
            <div class="template-grid" id="templateGrid">
                <?php foreach($templates as $template): ?>
                    <?php 
                    $status = dockerlab_get_container_status($template); 
                    $entry_url = dockerlab_build_entry_url($template);
                    $cat = strtolower($template['category'] ?? 'web');
                    
                    $cat_class = 'cat-web';
                    $card_class = 'card-web';
                    $cat_label = 'Web 漏洞';
                    if (strpos($cat, 'cloud') !== false || strpos($cat, 'k8s') !== false || strpos($cat, 'docker') !== false) {
                        $cat_class = 'cat-cloudnative';
                        $card_class = 'card-cloudnative';
                        $cat_label = '云原生逃逸';
                        $cat_group = 'cloudnative';
                    } else if (strpos($cat, 'db') !== false || strpos($cat, 'sql') !== false) {
                        $cat_class = 'cat-db';
                        $card_class = 'card-db';
                        $cat_label = '数据库安全';
                        $cat_group = 'db';
                    } else if (strpos($cat, 'mid') !== false || strpos($cat, 'service') !== false) {
                        $cat_class = 'cat-middleware';
                        $card_class = 'card-middleware';
                        $cat_label = '中间件/服务';
                        $cat_group = 'middleware';
                    } else {
                        $cat_group = 'web';
                    }

                    $is_running = ($status['state'] === 'running');
                    ?>

                    <div class="template-card <?php echo $card_class; ?>" data-cat="<?php echo $cat_group; ?>" data-name="<?php echo htmlspecialchars(strtolower($template['name'] . ' ' . $template['id'] . ' ' . $template['image'])); ?>">
                        <div>
                            <div class="template-header">
                                <h4 class="template-title"><?php echo dockerlab_html($template['name']); ?></h4>
                                <span class="cat-badge <?php echo $cat_class; ?>">
                                    <?php echo dockerlab_html($cat_label); ?>
                                </span>
                            </div>

                            <p style="font-size:12px; color:var(--text-secondary); line-height:1.5; margin-bottom:14px; min-height:36px;">
                                <?php echo dockerlab_html($template['notes'] ?? '提供标准的隔离容器实战场景。'); ?>
                            </p>

                            <div class="meta-item">
                                <i class="fa fa-tag" style="color:#06b6d4; width:14px;"></i>
                                <span>模板 ID：</span>
                                <code><?php echo dockerlab_html($template['id']); ?></code>
                            </div>

                            <div class="meta-item">
                                <i class="fa fa-file-image-o" style="color:#a855f7; width:14px;"></i>
                                <span>镜像 Image：</span>
                                <code><?php echo dockerlab_html($template['image']); ?></code>
                            </div>

                            <div class="meta-item">
                                <i class="fa fa-cube" style="color:#3b82f6; width:14px;"></i>
                                <span>容器 Name：</span>
                                <code><?php echo dockerlab_html($template['container_name']); ?></code>
                            </div>

                            <div class="meta-item">
                                <i class="fa fa-exchange" style="color:#f59e0b; width:14px;"></i>
                                <span>端口 Ports：</span>
                                <code><?php echo dockerlab_h(dockerlab_build_port_text($template)); ?></code>
                            </div>
                        </div>

                        <div class="template-footer">
                            <div>
                                <?php if ($is_running): ?>
                                    <span class="status-pill status-pill-success" style="font-size:12px; padding:3px 10px;">
                                        <i class="fa fa-play-circle"></i> 运行中
                                    </span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-warning" style="font-size:12px; padding:3px 10px; background:rgba(148,163,184,0.15); color:var(--text-secondary); border-color:var(--border-color);">
                                        <i class="fa fa-stop-circle"></i> 已就绪
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <form method="POST" style="display:inline; margin:0;">
                                    <input type="hidden" name="lab_id" value="<?php echo dockerlab_h($template['id']); ?>">
                                    <?php if ($is_running): ?>
                                        <button type="submit" name="lab_act" value="stop" class="btn btn-xs btn-warning" style="border-radius:4px;" title="停止容器">
                                            <i class="fa fa-stop"></i> 停止
                                        </button>
                                        <button type="submit" name="lab_act" value="restart" class="btn btn-xs btn-default" style="border-radius:4px;" title="重启容器">
                                            <i class="fa fa-refresh"></i> 重置
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="lab_act" value="start" class="btn btn-xs btn-success" style="border-radius:4px; background:linear-gradient(135deg, #10b981, #059669); border:none; font-weight:700; box-shadow:0 3px 8px rgba(16,185,129,0.3);" title="启动容器靶场">
                                            <i class="fa fa-play"></i> 启动
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <?php if($entry_url !== ''): ?>
                                    <a href="<?php echo dockerlab_h($entry_url); ?>" target="_blank" class="btn btn-xs btn-info" style="border-radius:4px; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none; font-weight:700; box-shadow:0 3px 8px rgba(6,182,212,0.3);">
                                        进入演练 <i class="fa fa-external-link"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="dockerlab_logs.php?id=<?php echo dockerlab_h($template['id']); ?>" class="btn btn-xs btn-default" style="border-radius:4px;" title="查看日志">
                                    <i class="fa fa-file-text-o"></i> 日志
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <!-- Interconnected Action Bar Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:22px;">
                <div>
                    <a href="dockerlab.php" class="btn btn-default" style="border-radius:8px; margin-right:10px;">
                        <i class="fa fa-arrow-left"></i> Docker Lab 概述
                    </a>
                    <a href="dockerlab_check.php" class="btn btn-info" style="border-radius:8px; background:linear-gradient(135deg, #0891b2, #0e7490); border:none; font-weight:700;">
                        <i class="fa fa-stethoscope"></i> 容器诊断面板
                    </a>
                </div>
                <div>
                    <a href="docker_privileged_escape.php" class="btn btn-success" style="border-radius:8px; background:linear-gradient(135deg, #10b981, #059669); border:none; padding:10px 18px; font-weight:700; box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        进入 Docker 特权模式逃逸关卡 <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
let currentCat = 'all';

function filterCat(cat, btn) {
    currentCat = cat;
    document.querySelectorAll('.filter-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function doSearch() {
    applyFilters();
}

function applyFilters() {
    const searchVal = document.getElementById('labSearch').value.toLowerCase().trim();
    
    document.querySelectorAll('.template-card').forEach(card => {
        const catMatch = (currentCat === 'all' || card.getAttribute('data-cat') === currentCat);
        const nameData = card.getAttribute('data-name') || '';
        const searchMatch = (searchVal === '' || nameData.includes(searchVal));
        
        if (catMatch && searchMatch) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
