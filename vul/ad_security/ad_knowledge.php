<?php
/**
 * Pikachu-Enhanced v2.0 Intranet Security Knowledge Architecture
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[235] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.section-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.subitem-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.subitem-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
}
.subitem-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.subitem-desc {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
}
.badge-tag {
    display: inline-block;
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 6px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    一、内网安全知识体系全景图
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        6 大核心知识领域与工具链
                    </small>
                </h1>
            </div>

            <!-- 1. 基础侦察与信息收集 -->
            <div class="section-card">
                <h3 class="section-title"><i class="fa fa-search" style="color: #3b82f6;"></i> 1. 基础侦察与信息收集</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">在内网渗透测试中，信息收集决定了攻击的上限。涵盖主机探测、域结构梳理与图形化分析。</p>
                <div class="subitem-grid">
                    <div class="subitem-card">
                        <div class="subitem-name">内网存活主机探测</div>
                        <div class="subitem-desc">通过 ICMP 协议、ARP 扫描、Nmap 端口扫描以及 NetBIOS 快速发现网段内活跃设备。</div>
                        <span class="badge-tag">ICMP / ARP / PortScan</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">域内信息深入收集</div>
                        <div class="subitem-desc">查询域用户、域管理员、域控制器 (DC)、组织单位 (OU) 及跨域信任关系。</div>
                        <span class="badge-tag">LDAP / Net-Commands</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">经典图论分析工具集</div>
                        <div class="subitem-desc">利用 SharpHound 采集数据，使用 BloodHound 可视化分析最短提权路径。</div>
                        <span class="badge-tag">BloodHound / PowerView / ADRecon</span>
                    </div>
                </div>
            </div>

            <!-- 2. 域内认证机制 -->
            <div class="section-card">
                <h3 class="section-title"><i class="fa fa-key" style="color: #a855f7;"></i> 2. 域内认证机制原理</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">深入剖析 Windows 两种主流认证协议的加密算法与通信握手图谱。</p>
                <div class="subitem-grid">
                    <div class="subitem-card">
                        <div class="subitem-name">NTLM 认证与中继</div>
                        <div class="subitem-desc">解析 NTLM Challenge/Response 机制，演练响应捕获与 NTLM Relay 跨协议中继。</div>
                        <span class="badge-tag">LM / NTLM / NTLMv2</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">Kerberos 认证流程</div>
                        <div class="subitem-desc">掌握 KDC 认证交互流程：AS-REQ/AS-REP (获得 TGT) ➔ TGS-REQ/TGS-REP (获得 ST) ➔ AP-REQ 服务验证。</div>
                        <span class="badge-tag">AS / TGS / AP 阶段</span>
                    </div>
                </div>
            </div>

            <!-- 3. 域内攻击手法 -->
            <div class="section-card">
                <h3 class="section-title"><i class="fa fa-bolt" style="color: #ef4444;"></i> 3. 域内高危攻击手法</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">涵盖票据伪造、委派漏洞、DCSync 导出以及高危 CVE 洞利用。</p>
                <div class="subitem-grid">
                    <div class="subitem-card">
                        <div class="subitem-name">票据伪造三大件</div>
                        <div class="subitem-desc">黄金票据 (Golden Ticket)、白银票据 (Silver Ticket) 与钻石票据 (Diamond Ticket) 伪造原理。</div>
                        <span class="badge-tag">Ticket Forgery</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">委派攻击链</div>
                        <div class="subitem-desc">非约束委派 (Unconstrained)、约束委派 (Constrained) 以及基于资源的约束委派 (RBCD)。</div>
                        <span class="badge-tag">Delegation Abuse</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">Roasting 离线爆破</div>
                        <div class="subitem-desc">Kerberoasting (SPN 服务票据爆破) 与 AS-REP Roasting (未开启预认证账号提取)。</div>
                        <span class="badge-tag">Kerberoasting / AS-REP</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">DCSync & DCShadow</div>
                        <div class="subitem-desc">利用 DRSUAPI 模拟域控同步密码 Hash，或者注册伪造 DC 修改域属性。</div>
                        <span class="badge-tag">DCSync / Mimikatz</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">经典 CVE 域漏洞</div>
                        <div class="subitem-desc">ZeroLogon (CVE-2020-1472)、NoPac (CVE-2021-42278)、PrintNightmare 与 PetitPotam。</div>
                        <span class="badge-tag">ZeroLogon / NoPac</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">GPO 与 ACL 权限滥用</div>
                        <div class="subitem-desc">组策略对象 (GPO) 强制推送后门，以及滥用 GenericAll / GenericWrite 等错误控制权限提权。</div>
                        <span class="badge-tag">GPO / ACL Abuse</span>
                    </div>
                </div>
            </div>

            <!-- 4. 横向移动 -->
            <div class="section-card">
                <h3 class="section-title"><i class="fa fa-arrows" style="color: #f59e0b;"></i> 4. 横向移动</h3>
                <div class="subitem-grid">
                    <div class="subitem-card">
                        <div class="subitem-name">远程协议控制</div>
                        <div class="subitem-desc">利用 WMI、PsExec、SMB 管道以及 WinRM (5985/5986) 远程发起命令执行。</div>
                        <span class="badge-tag">WMI / PsExec / WinRM</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">凭据与票据传递</div>
                        <div class="subitem-desc">哈希传递 (Pass-the-Hash / PtH)、票据传递 (Pass-the-Ticket / PtT) 与 RDP 会话劫持。</div>
                        <span class="badge-tag">PtH / PtT / Tscon</span>
                    </div>
                </div>
            </div>

            <!-- 5. 权限维持与 6. 防御检测 -->
            <div class="section-card">
                <h3 class="section-title"><i class="fa fa-shield" style="color: #10b981;"></i> 5. 权限维持 & 6. 防御检测</h3>
                <div class="subitem-grid">
                    <div class="subitem-card">
                        <div class="subitem-name">权限持久化维持</div>
                        <div class="subitem-desc">包含 Skeleton Key 内存万能密码、DSRM 还原密码利用、SSP 内存后门等持久化手段。</div>
                        <span class="badge-tag">Skeleton Key / DSRM / SSP</span>
                    </div>
                    <div class="subitem-card">
                        <div class="subitem-name">日志审计与蜜罐防范</div>
                        <div class="subitem-desc">审计 4624/4625/4768/4769 事件 ID，部署蜜罐 SPN 账户，建立 Tier 0 分层模型与 SIEM 检测。</div>
                        <span class="badge-tag">Event Log / Tier Model / SIEM</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
