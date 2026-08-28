<?php
/**
 * Pikachu-Enhanced v2.0 Docker Lab Main Overview & Interconnected Hub Page
 * Comprehensive Container Security Architecture, docker.sock Deep-Dive & Visual Workflows
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 400, '');
$ACTIVE[140] = 'active open';
$ACTIVE[141] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #164e63, #083344);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.1);
}
.overview-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.overview-badge {
    background: rgba(8, 145, 178, 0.2);
    color: #06b6d4;
    border: 1px solid #06b6d4;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.overview-hero-card p {
    font-size: 15px;
    color: #e2e8f0;
    line-height: 1.7;
    max-width: 950px;
    margin-bottom: 20px;
}

.workflow-section {
    background-color: var(--bg-card);
    border-radius: 14px;
    padding: 30px;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.workflow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}
.workflow-step {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.2s ease;
}
.workflow-step:hover {
    transform: translateY(-3px);
}
.step-icon-badge {
    width: 36px;
    height: 36px;
    background: #06b6d4;
    color: #ffffff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 12px;
}

/* Docker Sock Deep Dive Diagrams */
.sock-diagram-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.diagram-box {
    border-radius: 12px;
    padding: 22px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.6;
}
.diagram-box-safe {
    background: rgba(16, 185, 129, 0.05);
    border: 1.5px solid #10b981;
}
.diagram-box-danger {
    background: rgba(239, 68, 68, 0.05);
    border: 1.5px solid #ef4444;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.detail-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    border-top: 4px solid #06b6d4;
}

.lab-shortcuts {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.shortcut-card {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px 20px;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.shortcut-card:hover {
    border-color: #06b6d4;
    transform: translateX(4px);
}
.shortcut-title {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 15px;
}
.shortcut-desc {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 2px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="overview-hero-card">
                <h1>
                    🐳 Docker 容器安全与云原生逃逸总览 (Docker Container Security)
                    <span class="overview-badge">云原生与基础设施安全</span>
                </h1>
                <p>Docker 容器逃逸是现代云原生攻防的核心重点。当容器共享宿主机内核、挂载了敏感宿主机套接字 (<code>/var/run/docker.sock</code>)、配置了特权模式 (<code>--privileged</code>) 或包含高危 CVE 漏洞时，攻击者可突破隔离屏障，直接掌控宿主机 Root 权限。</p>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="dockerlab_center.php" class="btn btn-sm btn-info" style="border-radius:6px; background:#06b6d4; border-color:#06b6d4; font-weight:700;">
                        <i class="fa fa-cubes"></i> ⚡ 容器与微服务运行控制台 (10套靶场)
                    </a>
                    <a href="dockerlab_check.php" class="btn btn-sm btn-default" style="border-radius:6px; background:rgba(255,255,255,0.15); border-color:rgba(255,255,255,0.3); color:#fff; font-weight:700;">
                        <i class="fa fa-stethoscope"></i> 🔍 容器环境诊断面板
                    </a>
                </div>
            </div>

            <!-- Deep Dive Section: What is docker.sock & Why it Causes 100% Escape -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 12px;">
                    <i class="fa fa-key" style="color: #ef4444;"></i> 核心原理拆解：什么是 <code>docker.sock</code>？为什么挂载它会导致 100% 逃逸？
                </h3>
                <p style="font-size:14px; color:var(--text-secondary); line-height:1.7; margin-bottom:20px;">
                    <code>/var/run/docker.sock</code> 是 Docker 守护进程（Docker Daemon）在 Unix 域套接字上的通信 API 接口。终端里执行的 <code>docker run</code>、<code>docker exec</code> 本质上都是向这个套接字发送 HTTP REST API 请求。把宿主机的 <code>docker.sock</code> 挂载进容器，<b>等同于把宿主机物理机的 Root 权限钥匙直接交给了容器</b>！
                </p>

                <!-- Diagrams Side-by-Side Comparison -->
                <div class="sock-diagram-container">
                    <!-- Safe Mode Box -->
                    <div class="diagram-box diagram-box-safe">
                        <div style="font-weight:800; color:#10b981; font-size:15px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-shield"></i> 【模式 A】正常安全隔离模式 (未挂载 docker.sock)
                        </div>
                        <pre style="background:transparent; border:none; padding:0; color:var(--text-primary); font-size:12px; line-height:1.6; margin:0;">
物理宿主机 (你的电脑 Host OS)
 ┌──────────────────────────────────────────────┐
 │  Docker 守护进程 (拥有宿主机最高 Root 权限)    │
 │          │                                   │
 │          ▼ (docker.sock 藏在宿主机内部)        │
 │     [🔒 安全隔离防护墙 / 阻断未挂载套接字]      │
 │          │                                   │
 │          ▼                                   │
 │   ┌──────────────┐                           │
 │   │ Pikachu 容器 │ 💥 攻击者攻破 Web 拿 Webshell│
 │   │ (被困在墙内) │ ❌ 无法触碰 docker.sock API  │
 │   └──────────────┘                           │
 └──────────────────────────────────────────────┘
 [防护效果] 黑客只能在当前 Web 容器内活动，物理电脑绝对安全！</pre>
                    </div>

                    <!-- High Risk Escape Mode Box -->
                    <div class="diagram-box diagram-box-danger">
                        <div style="font-weight:800; color:#ef4444; font-size:15px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-exclamation-triangle"></i> 【模式 B】高危挂载逃逸模式 (挂载了 docker.sock)
                        </div>
                        <pre style="background:transparent; border:none; padding:0; color:var(--text-primary); font-size:12px; line-height:1.6; margin:0;">
物理宿主机 (你的电脑 Host OS)
 ┌──────────────────────────────────────────────┐
 │  Docker 守护进程 (拥有宿主机最高 Root 权限)    │
 │          ▲                                   │
 │          │ 🔑 通信通道打通！钥匙直接送入容器     │
 │   ┌──────┴───────┐                           │
 │   │ Pikachu 容器 │ 💥 攻击者利用 docker.sock   │
 │   │ (已拿到钥匙) │ 👉 向 Daemon 发 API 拉新容器│
 │   └──────────────┘                           │
 │          │                                   │
 │          ▼ 挂载宿主机根物理磁盘 /              │
 │   ┌──────────────┐                           │
 │   │  新特权容器  │ ═══> chroot /host_root    │
 │   └──────────────┘      💥 沦陷物理宿主机 Root!│
 └──────────────────────────────────────────────┘
 [逃逸后果] 攻击者秒级突破容器，直接拿到物理宿主机 Root 命令行！</pre>
                    </div>
                </div>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #06b6d4;"></i> 容器逃逸攻击演进四步流程图解
                </h3>
                
                <div class="workflow-grid">
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #06b6d4;">1</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 识别容器环境</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">查看 <code>/.dockerenv</code> 或 <code>/proc/1/cgroup</code> 确认目标处于 Docker 容器中。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #3b82f6;">2</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 搜集挂载与特权位</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">检索高危挂载（如 <code>/var/run/docker.sock</code>、<code>/dev/vda1</code>）或 <code>CAP_SYS_ADMIN</code> 特权。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #a855f7;">3</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 触发逃逸 Payload</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">利用 Docker Socket 发起 API 请求拉起特权容器，或注入 cgroups <code>release_agent</code> 内核钩子。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #10b981;">4</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 掌控宿主 Root 节点</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">使用 <code>chroot /host_root</code> 成功突破容器隔离屏障，直接获取宿主机 Host OS Root 命令行！</div>
                    </div>
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                <div class="detail-card" style="border-top-color: #0891b2;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">⚙️ 1. 特权模式逃逸 (--privileged)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">禁用全套安全隔离与 Capabilities 过滤，容器可直接挂载 <code>/dev/vda1</code> 物理磁盘或修改宿主机 crontab 逃逸。</p>
                </div>
                
                <div class="detail-card" style="border-top-color: #06b6d4;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🔌 2. Docker Socket 敏感挂载逃逸</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">当 <code>/var/run/docker.sock</code> 被挂载进容器时，容器内程序可直接调用 Daemon API 拉起挂载根目录的新特权容器。</p>
                </div>

                <div class="detail-card" style="border-top-color: #7c3aed;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛡️ 3. Linux Capabilities 逃逸</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">拥有 <code>CAP_SYS_ADMIN</code> 时挂载 cgroups v1，配置 <code>release_agent</code> 回调脚本在宿主机 Root 上下文中盲执行。</p>
                </div>

                <div class="detail-card" style="border-top-color: #ef4444;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">⚙️ 4. 组件与内核 CVE 漏洞逃逸</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">覆盖宿主机 runc 二进制 (CVE-2019-5736) 或利用脏管道 (Dirty Pipe CVE-2022-0847) 强行覆写宿主机只读凭证。</p>
                </div>
                
                <div class="detail-card" style="border-top-color: #10b981;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">☸️ 5. K8s Service Account 越权</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">容器内部挂载的 <code>/var/run/secrets/kubernetes.io/serviceaccount/token</code> 可用于接管 Kubernetes 集群 API Server。</p>
                </div>
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="workflow-section">
                <h3 style="margin: 0 0 18px 0; font-size: 18px; font-weight: 700; color:var(--text-primary);">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    <a href="docker_privileged_escape.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">1. Docker 特权模式逃逸 (--privileged)</div>
                            <div class="shortcut-desc">测试设备挂载与宿主机磁盘 Chroot 接管 (100 PTS)</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
                    </a>

                    <a href="docker_sock_escape.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">2. Docker Socket 敏感挂载逃逸</div>
                            <div class="shortcut-desc">测试 Unix Socket API 交互与拉起特权容器 (150 PTS)</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
                    </a>

                    <a href="docker_caps_escape.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">3. Linux Capabilities 逃逸 (CAP_SYS_ADMIN)</div>
                            <div class="shortcut-desc">测试 cgroups v1 release_agent 内核钩子注入 (200 PTS)</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
                    </a>

                    <a href="docker_cve_escape.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">4. Docker 组件与内核 CVE 漏洞逃逸</div>
                            <div class="shortcut-desc">测试 runc 覆盖与 Dirty Pipe 脏管道逃逸 (250 PTS)</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
                    </a>
                    
                    <a href="k8s_token_escape.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">5. K8s SA Token 越权集群逃逸关卡</div>
                            <div class="shortcut-desc">测试提取 Kubernetes Token 接管 API Server</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
                    </a>
                    
                    <a href="dockerlab_center.php" class="shortcut-card" style="border-color:#06b6d4; background:rgba(6,182,212,0.08);">
                        <div>
                            <div class="shortcut-title" style="color:#06b6d4;">📋 全部 10 套实战靶场模板管理中心</div>
                            <div class="shortcut-desc">包含 Flask SSTI、Log4j2、Fastjson、MySQL 等场景</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #06b6d4;"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
