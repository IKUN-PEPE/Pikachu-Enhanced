<?php
/**
 * Pikachu-Enhanced v2.0 Intranet & Active Directory Security Master Hub
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[231] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.ad-hero-banner {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.12);
    position: relative;
    overflow: hidden;
}
.ad-hero-banner::after {
    content: '\f0c2';
    font-family: 'FontAwesome';
    position: absolute;
    right: -20px;
    bottom: -30px;
    font-size: 180px;
    color: rgba(255,255,255,0.04);
    pointer-events: none;
}
.ad-hero-title {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.ad-badge {
    background: rgba(129, 140, 248, 0.25);
    color: #a5b4fc;
    border: 1px solid #818cf8;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.ad-hero-desc {
    font-size: 15px;
    color: #e2e8f0;
    line-height: 1.7;
    max-width: 950px;
    margin-bottom: 20px;
}
.ad-stats-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 15px;
}
.ad-stat-chip {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Nav Tabs */
.ad-tab-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
}
.ad-tab-btn {
    background: var(--bg-card);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ad-tab-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.ad-tab-btn.active {
    background: #4338ca !important;
    color: #ffffff !important;
    border-color: #4338ca !important;
    box-shadow: 0 4px 14px rgba(67, 56, 202, 0.3);
}

/* Section Cards */
.pillar-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, border-color 0.2s ease;
}
.pillar-card:hover {
    border-color: #6366f1;
    transform: translateY(-2px);
}
.pillar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}
.pillar-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pillar-icon {
    width: 32px;
    height: 32px;
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.item-tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}
.item-tag {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.item-tag.highlight {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.3);
}
.item-tag.tool {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.3);
}

/* Stage Steps */
.stage-timeline {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.stage-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 18px;
    border-top: 4px solid #6366f1;
}
.stage-num {
    font-size: 12px;
    font-weight: 800;
    color: #6366f1;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}
.stage-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}
.stage-detail {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="ad-hero-banner">
                <h1 class="ad-hero-title">
                    🌐 内网与 Active Directory 域安全学习 + 靶场建设总纲
                    <span class="ad-badge">知识体系与实战蓝图</span>
                </h1>
                <p class="ad-hero-desc">
                    全面梳理企业级内网横向、Active Directory 域控制器攻防、凭据传递与权限维持的全套知识体系，并规划了从虚拟化环境搭建、域控角色部署到高级漏洞场景构建与日志 SIEM 审计的标准靶场落地蓝图。
                </p>
                <div class="ad-stats-row">
                    <div class="ad-stat-chip"><i class="fa fa-book" style="color:#a5b4fc;"></i> 6 大内网安全知识模块</div>
                    <div class="ad-stat-chip"><i class="fa fa-cubes" style="color:#34d399;"></i> 5 阶段靶场建设流程</div>
                    <div class="ad-stat-chip"><i class="fa fa-bug" style="color:#f87171;"></i> 12+ 经典域漏洞场景</div>
                    <div class="ad-stat-chip"><i class="fa fa-shield" style="color:#60a5fa;"></i> EDR / SIEM 攻防检测闭环</div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="ad-tab-nav">
                <button class="ad-tab-btn active" onclick="switchTab('knowledge')">
                    <i class="fa fa-sitemap"></i> 一、内网安全 6 大知识体系
                </button>
                <button class="ad-tab-btn" onclick="switchTab('lab')">
                    <i class="fa fa-cogs"></i> 二、AD 域漏洞靶场搭建大纲
                </button>
            </div>

            <!-- Tab 1: Knowledge Tree -->
            <div id="tab-knowledge" class="tab-content-block">
                
                <!-- 1. 基础侦察与信息收集 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-search"></i></span>
                            1. 基础侦察与信息收集 (Reconnaissance & Enumeration)
                        </h3>
                        <span class="item-tag tool">侦察阶段</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">进入内网后的首要任务是建立全景网络态势拓扑，定位域控制器 (DC)、关键域成员服务器与特权管理员账号。</p>
                    <div class="item-tag-list">
                        <span class="item-tag">ICMP / ARP 存活扫描</span>
                        <span class="item-tag">端口扫描与服务识别</span>
                        <span class="item-tag">域用户与组枚举</span>
                        <span class="item-tag">域管理员定位</span>
                        <span class="item-tag">域控制器 (DC) 寻找</span>
                        <span class="item-tag">域信任关系探查</span>
                        <span class="item-tag tool">BloodHound</span>
                        <span class="item-tag tool">PowerView</span>
                        <span class="item-tag tool">ADRecon</span>
                        <span class="item-tag tool">SharpHound</span>
                    </div>
                </div>

                <!-- 2. 域内认证机制 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-key"></i></span>
                            2. 域内认证机制 (Authentication Mechanics)
                        </h3>
                        <span class="item-tag tool">核心底层协议</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">深入理解 Windows NTLM 与 Kerberos 协议的工作细节，是成功进行中继与票据攻击的基石。</p>
                    <div class="item-tag-list">
                        <span class="item-tag">LM / NTLM Hash 生成原理</span>
                        <span class="item-tag">NTLM 挑战/响应 (Challenge/Response)</span>
                        <span class="item-tag highlight">NTLM 中继 (NTLM Relay)</span>
                        <span class="item-tag">Kerberos AS-REQ / AS-REP 阶段</span>
                        <span class="item-tag">Kerberos TGS-REQ / TGS-REP 阶段</span>
                        <span class="item-tag">AP-REQ 最终服务验证</span>
                        <span class="item-tag">TGT (票据授权票据) 结构</span>
                    </div>
                </div>

                <!-- 3. 域内攻击手法 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-bolt"></i></span>
                            3. 域内高危攻击手法 (Domain Attack Vectors)
                        </h3>
                        <span class="item-tag highlight">核心利用链</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">涵盖票据伪造、委派提权、经典 CVE 漏洞以及 Active Directory 权限配置错误的深度利用。</p>
                    <div class="item-tag-list">
                        <span class="item-tag highlight">黄金票据 (Golden Ticket)</span>
                        <span class="item-tag highlight">白银票据 (Silver Ticket)</span>
                        <span class="item-tag highlight">钻石票据 (Diamond Ticket)</span>
                        <span class="item-tag">非约束委派 (Unconstrained)</span>
                        <span class="item-tag">约束委派 (Constrained)</span>
                        <span class="item-tag">基于资源的约束委派 (RBCD)</span>
                        <span class="item-tag highlight">Kerberoasting</span>
                        <span class="item-tag highlight">AS-REP Roasting</span>
                        <span class="item-tag">哈希传递 (PtH) / 票据传递 (PtT)</span>
                        <span class="item-tag">DCSync / DCShadow 凭据提取</span>
                        <span class="item-tag highlight">ZeroLogon (CVE-2020-1472)</span>
                        <span class="item-tag highlight">NoPac (CVE-2021-42278)</span>
                        <span class="item-tag">PrintNightmare</span>
                        <span class="item-tag">PetitPotam 强制强制认证</span>
                        <span class="item-tag">组策略滥用 (GPO Abuse)</span>
                        <span class="item-tag">ACL 权限滥用 (GenericAll / GenericWrite)</span>
                    </div>
                </div>

                <!-- 4. 横向移动 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-arrows"></i></span>
                            4. 域内横向移动 (Lateral Movement)
                        </h3>
                        <span class="item-tag tool">内网扩张</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">利用已获取的凭据或票据，在内网不同的主机和服务节点间进行远程执行与控制拓展。</p>
                    <div class="item-tag-list">
                        <span class="item-tag">WMI 远程命令执行</span>
                        <span class="item-tag">PsExec 服务提权执行</span>
                        <span class="item-tag">SMB 共享与管道调用</span>
                        <span class="item-tag">WS-Management (WinRM) 端口 5985/5986</span>
                        <span class="item-tag">Pass-the-Hash (PtH)</span>
                        <span class="item-tag">Pass-the-Ticket (PtT)</span>
                        <span class="item-tag">RDP 会话劫持 (Tscon)</span>
                    </div>
                </div>

                <!-- 5. 权限维持 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-anchor"></i></span>
                            5. 域内权限维持 (Persistence Mechanisms)
                        </h3>
                        <span class="item-tag tool">长期控制</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">在获取域控制器高权限后，建立隐蔽、持久且不易被管理员重置密码影响的后门机制。</p>
                    <div class="item-tag-list">
                        <span class="item-tag highlight">黄金/白银票据长期维持</span>
                        <span class="item-tag highlight">Skeleton Key 内存万能密码</span>
                        <span class="item-tag">DSRM 还原模式密码利用</span>
                        <span class="item-tag">SSP (Security Support Provider) 内存后门</span>
                        <span class="item-tag">AdminSDHolder / SDProp 隐蔽提权</span>
                    </div>
                </div>

                <!-- 6. 防御与检测 -->
                <div class="pillar-card">
                    <div class="pillar-header">
                        <h3 class="pillar-title">
                            <span class="pillar-icon"><i class="fa fa-shield"></i></span>
                            6. 防御、检测与蓝队监控 (Defense & Threat Detection)
                        </h3>
                        <span class="item-tag tool">蓝队主动防御</span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">结合 Windows 审计日志、蜜罐陷阱与企业级 SIEM/EDR，建立全方位的内网检测与分层防御。</p>
                    <div class="item-tag-list">
                        <span class="item-tag">Windows 关键事件日志审计 (4624/4625/4768/4769/4672)</span>
                        <span class="item-tag">蜜罐账号 / 蜜罐 SPN 票据诱捕</span>
                        <span class="item-tag">Tier 模型 (Tier 0 / Tier 1 / Tier 2) 分层防护</span>
                        <span class="item-tag">SIEM 规则编写 (Sigma / ELK Rules)</span>
                        <span class="item-tag">EDR 行为异常检测与 Hook 监控</span>
                    </div>
                </div>

            </div>

            <!-- Tab 2: Lab Setup Blueprint -->
            <div id="tab-lab" class="tab-content-block" style="display: none;">
                
                <div class="pillar-card">
                    <h3 style="margin-top:0; font-size:20px; font-weight:700; color:var(--text-primary);">
                        🏗️ AD 域漏洞靶场搭建 5 大阶段大纲
                    </h3>
                    <p style="font-size:14px; color:var(--text-secondary);">按照标准化、可重复演练的流程逐步构建安全靶场，方便后续接入攻防测试。</p>
                </div>

                <div class="stage-timeline">
                    <!-- 阶段一 -->
                    <div class="stage-box" style="border-top-color: #3b82f6;">
                        <div class="stage-num">STAGE 01</div>
                        <div class="stage-name">环境规划与拓扑设计</div>
                        <div class="stage-detail">
                            • <strong>虚拟化平台</strong>：VMware / VirtualBox / Proxmox<br>
                            • <strong>网络隔离</strong>：划分 DMZ 外部网段、办公网段、域控核心网段<br>
                            • <strong>IP 规划</strong>：固定 IP 地址与内网 DNS 解析设置
                        </div>
                    </div>

                    <!-- 阶段二 -->
                    <div class="stage-box" style="border-top-color: #a855f7;">
                        <div class="stage-num">STAGE 02</div>
                        <div class="stage-name">基础环境角色部署</div>
                        <div class="stage-detail">
                            • <strong>域控制器 (DC)</strong>：Windows Server 2019/2022 安装 AD DS 角色，创建 test.local 树林<br>
                            • <strong>成员服务器</strong>：配置 Web/IIS/MSSQL 加入域<br>
                            • <strong>域客户端</strong>：Win10/11 加入域<br>
                            • <strong>攻击机</strong>：Kali / Windows 集成 Impacket/Mimikatz
                        </div>
                    </div>

                    <!-- 阶段三 -->
                    <div class="stage-box" style="border-top-color: #ef4444;">
                        <div class="stage-num">STAGE 03</div>
                        <div class="stage-name">漏洞场景配置 (由浅入深)</div>
                        <div class="stage-detail">
                            • <strong>基础</strong>：弱口令、服务账号 SPN 绑定 (Kerberoasting)<br>
                            • <strong>进阶</strong>：非约束委派 / 约束委派 / RBCD 场景<br>
                            • <strong>高危 CVE</strong>：配置 ZeroLogon / NoPac 环境<br>
                            • <strong>权限</strong>：错误配置 GenericAll ACL 权限
                        </div>
                    </div>

                    <!-- 阶段四 -->
                    <div class="stage-box" style="border-top-color: #f59e0b;">
                        <div class="stage-num">STAGE 04</div>
                        <div class="stage-num">辅助设施与审计</div>
                        <div class="stage-detail">
                            • <strong>SIEM / ELK</strong>：部署 Winlogbeat 收集 Windows 安全日志<br>
                            • <strong>快照管理</strong>：为初始干净环境及各漏洞节点打 Snapshot 快照，支持一秒回滚复原
                        </div>
                    </div>

                    <!-- 阶段五 -->
                    <div class="stage-box" style="border-top-color: #10b981;">
                        <div class="stage-num">STAGE 05</div>
                        <div class="stage-name">练习闭环与复盘</div>
                        <div class="stage-detail">
                            • <strong>攻击链编写</strong>：信息收集 ➔ 提权 ➔ 横向 ➔ 维持<br>
                            • <strong>BloodHound 可视化</strong>：导出 JSON 数据绘制最短提权路径<br>
                            • <strong>攻防复盘</strong>：对照检测点与日志 Event ID
                        </div>
                    </div>
                </div>

                <!-- Action Notice -->
                <div class="well" style="margin-top: 30px; border-left: 4px solid #6366f1;">
                    <h4 style="margin-top:0; font-weight:700; color:var(--text-primary);"><i class="fa fa-info-circle" style="color:#6366f1;"></i> 后续规划提示</h4>
                    <p style="margin-bottom:0; font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        当前页面已完成对<strong>内网与 AD 域安全完整知识体系与靶场搭建大纲</strong>的前端可视化展示。根据规划指令，后端靶场底层镜像及自动化利用脚本将在大纲确定后开启落地构建！
                    </p>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.ad-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content-block').forEach(block => block.style.display = 'none');
    
    if (tabName === 'knowledge') {
        event.currentTarget.classList.add('active');
        document.getElementById('tab-knowledge').style.display = 'block';
    } else if (tabName === 'lab') {
        event.currentTarget.classList.add('active');
        document.getElementById('tab-lab').style.display = 'block';
    }
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
