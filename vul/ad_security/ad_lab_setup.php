<?php
/**
 * Pikachu-Enhanced v2.0 AD Lab Setup Roadmap
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[236] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.setup-step-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    position: relative;
    border-left: 5px solid #6366f1;
}
.setup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}
.setup-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.setup-badge {
    background: #6366f1;
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.step-list-ul {
    padding-left: 20px;
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.8;
}
.code-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    font-family: monospace;
    font-size: 13px;
    color: var(--text-primary);
    margin-top: 10px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    二、AD 域漏洞靶场搭建大纲与实战蓝图
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        从零构建企业级域控实验环境
                    </small>
                </h1>
            </div>

            <!-- 阶段一 -->
            <div class="setup-step-card" style="border-left-color: #3b82f6;">
                <div class="setup-header">
                    <h3 class="setup-title">阶段一：环境规划与拓扑设计</h3>
                    <span class="setup-badge" style="background: #3b82f6;">STAGE 01</span>
                </div>
                <ul class="step-list-ul">
                    <li><strong>虚拟化平台选择</strong>：推荐 VMware Workstation / Proxmox VE 或 VirtualBox，具备高效的虚拟网络隔离能力。</li>
                    <li><strong>网络拓扑设计</strong>：构建包含 外网 Web/DMZ 区域、内网办公网段以及域控核心网段的三层网络拓扑。</li>
                    <li><strong>IP 规划与网段隔离</strong>：
                        <ul>
                            <li>DMZ 网段：192.168.1.0/24 (网关 Web 服务器)</li>
                            <li>内网 Core 网段：10.0.0.0/24 (域控制器 DC: 10.0.0.10)</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- 阶段二 -->
            <div class="setup-step-card" style="border-left-color: #a855f7;">
                <div class="setup-header">
                    <h3 class="setup-title">阶段二：基础环境部署</h3>
                    <span class="setup-badge" style="background: #a855f7;">STAGE 02</span>
                </div>
                <ul class="step-list-ul">
                    <li><strong>域控制器 (DC) 部署</strong>：
                        <br>安装 Windows Server 2019/2022，添加 AD DS (Active Directory Domain Services) 角色，提升为新树林域控 (如 <code>test.local</code>)，创建 OU、域用户与管理员组。
                    </li>
                    <li><strong>成员服务器部署</strong>：
                        <br>安装 Windows Server 2016/2019，加入 <code>test.local</code> 域，配置 IIS 或 SQL Server 服务。
                    </li>
                    <li><strong>域客户端部署</strong>：
                        <br>安装 Windows 10 / 11 虚拟机，加入域并使用域普通员工账号登录。
                    </li>
                    <li><strong>攻击机节点准备</strong>：
                        <br>部署 Kali Linux 与 Windows 运维工具机，预装 Impacket 框架、Mimikatz、BloodHound、CrackMapExec 与 Evil-WinRM。
                    </li>
                </ul>
            </div>

            <!-- 阶段三 -->
            <div class="setup-step-card" style="border-left-color: #ef4444;">
                <div class="setup-header">
                    <h3 class="setup-title">阶段三：漏洞场景构建 (由浅入深)</h3>
                    <span class="setup-badge" style="background: #ef4444;">STAGE 03</span>
                </div>
                <ul class="step-list-ul">
                    <li><strong>弱口令与敏感信息</strong>：设置常见弱口令与桌面备注配置文件泄露。</li>
                    <li><strong>Kerberoasting 场景配置</strong>：为域账户注册 SPN 实例服务。
                        <div class="code-box">setspn -A MSSQLSvc/sql01.test.local:1433 sql_service_user</div>
                    </li>
                    <li><strong>委派攻击场景配置</strong>：勾选非约束委派、约束委派 (s4u2self/s4u2proxy) 或通过 ActiveDirectory 模块配置 RBCD。</li>
                    <li><strong>ACL 错误权限赋值</strong>：使用 PowerView 赋予特定域账号对目标对象的 <code>GenericAll</code> 或 <code>WriteDacl</code> 权限。</li>
                    <li><strong>高危 CVE 漏洞环境</strong>：保留未经补丁修补的 Windows Server 2016 镜像以演练 ZeroLogon (CVE-2020-1472) 与 NoPac。</li>
                    <li><strong>多域与域信任场景 (进阶)</strong>：搭建父子域 (Parent/Child Domain) 演练跨域提权。</li>
                </ul>
            </div>

            <!-- 阶段四 -->
            <div class="setup-step-card" style="border-left-color: #f59e0b;">
                <div class="setup-header">
                    <h3 class="setup-title">阶段四：辅助设施与快照管理</h3>
                    <span class="setup-badge" style="background: #f59e0b;">STAGE 04</span>
                </div>
                <ul class="step-list-ul">
                    <li><strong>日志收集与 SIEM 搭建</strong>：部署 Winlogbeat / Sysmon，将 Windows 审计日志打入 ELK / Splunk 进行攻防对照学习。</li>
                    <li><strong>快照状态管理</strong>：在阶段二基础设施搭建完毕后打下全局 <code>Clean-Base</code> 快照，完成各个漏洞场景配置后再打下专属漏洞快照，确保秒级回滚。</li>
                </ul>
            </div>

            <!-- 阶段五 -->
            <div class="setup-step-card" style="border-left-color: #10b981;">
                <div class="setup-header">
                    <h3 class="setup-title">阶段五：练习闭环与攻防复盘</h3>
                    <span class="setup-badge" style="background: #10b981;">STAGE 05</span>
                </div>
                <ul class="step-list-ul">
                    <li><strong>完整攻击链撰写</strong>：记录从“外网突破 ➔ 凭据提取 ➔ 横向移动 ➔ 域控接管”的标准渗透报告。</li>
                    <li><strong>BloodHound 图形可视化</strong>：对比预设攻击路径与 BloodHound 计算出来的 Graph 最短路径。</li>
                    <li><strong>防御检测复盘</strong>：对照 SIEM 日志验证 4624 (登录)、4768 (AS 请求)、4769 (TGS 请求) 等关键 Event ID。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
