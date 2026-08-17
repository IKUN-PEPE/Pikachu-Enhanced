<?php
/**
 * Pikachu-Enhanced v2.0 GOAD Intranet Lab Blueprint & Architecture Center
 * Comprehensive guide for 3-Machine (GOAD-Light) & 5-Machine (Full GOAD) Topologies
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[236] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.bp-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 26px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.bp-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.topo-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
}
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cred-table {
    width: 100%;
    margin-bottom: 0;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    📐 GOAD 企业级 Active Directory 内网靶场架构蓝图
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        3 台精简版 (GOAD-Light) vs 5 台完整版 (Full GOAD) 全景对照
                    </small>
                </h1>
            </div>

            <!-- Intro Overview -->
            <div class="bp-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff;">
                <h2 style="margin-top: 0; font-weight: 800; font-size: 22px; color: #f8fafc;">
                    🌐 GOAD (Game of Active Directory) 内网靶场蓝图设计
                </h2>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.8; margin-bottom: 0;">
                    GOAD 是全球最受欢迎的自动化域渗透测试靶场。结合了 Vagrant 基础镜像编排与 Ansible 自动化攻防场景下发。
                    为了适应不同硬件配置（8G/16G/32G 内存）的用户需求，GOAD 提供了 **3 台精简版 (GOAD-Light)** 与 **5 台完整版 (Full GOAD)** 两种运行模式。
                </p>
            </div>

            <!-- Architecture Comparison Cards -->
            <div class="row">
                
                <!-- 3-Machine Light Architecture -->
                <div class="col-md-6">
                    <div class="bp-card" style="border-top: 4px solid #3b82f6;">
                        <div class="bp-title">
                            <span class="topo-badge" style="background: #3b82f6;">轻量首选</span>
                            3 台精简版 (GOAD-Light) 架构
                        </div>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.7;">
                            适合 <strong>16GB 物理内存</strong> 宿主机。包含 1 个森林根域、1 个子域、1 个成员服务器，完美支持 80% 的经典 AD 域渗透攻击链。
                        </p>
                        
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 15px;">全网拓扑示意图：</h4>
                        <div class="well" style="background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
<pre style="background: transparent; border: none; font-size: 12px; margin: 0; color: var(--text-primary); font-family: monospace;">
[ 攻击者机 (Kali / WSL) ] ---> (VMnet8 虚拟网卡 192.168.56.0/24)
       |
       +---> DC01 (kingslanding.sevenkingdoms.local)
       |      IP: 192.168.56.134 | Win2019 | 根域控 + ADCS
       |
       +---> DC02 (winterfell.north.sevenkingdoms.local)
       |      IP: 192.168.56.136 | Win2019 | 子域控 (父子域信任)
       |
       +---> SRV02 (castelblack.north.sevenkingdoms.local)
              IP: 192.168.56.138 | Win2019 | MSSQL + WebDAV
</pre>
                        </div>

                        <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 15px;">资源开销：</h4>
                        <ul style="color: var(--text-secondary); font-size: 13px; padding-left: 20px;">
                            <li><strong>内存开销：</strong> 运行时约需 6.5 GB ~ 7.0 GB 物理内存</li>
                            <li><strong>磁盘占用：</strong> 约 37.5 GB 磁盘空间</li>
                        </ul>
                    </div>
                </div>

                <!-- 5-Machine Full Architecture -->
                <div class="col-md-6">
                    <div class="bp-card" style="border-top: 4px solid #ef4444;">
                        <div class="bp-title">
                            <span class="topo-badge" style="background: #ef4444;">豪华全拓扑</span>
                            5 台完整版 (Full GOAD) 架构
                        </div>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.7;">
                            适合 <strong>24GB ~ 32GB 物理内存</strong> 宿主机。包含 <strong>2 个独立森林</strong>、<strong>3 个 Active Directory 域</strong>、双向跨林信任及跨林 ADCS 攻击。
                        </p>
                        
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 15px;">全网拓扑示意图：</h4>
                        <div class="well" style="background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
<pre style="background: transparent; border: none; font-size: 12px; margin: 0; color: var(--text-primary); font-family: monospace;">
[ 森林 1: sevenkingdoms.local ]          [ 森林 2: essos.local ]
       |                                         |
 (DC01 - 根域控) <=========================> (DC03 - 外部林域控)
 192.168.56.10/134    (双向跨林信任)         192.168.56.12 (Win2016)
       |                                         |
 (DC02 - 子域控)                            (SRV03 - 林成员服务)
 192.168.56.11/136                          192.168.56.23 (ADCS HTTP)
       |
 (SRV02 - 成员服务器)
 192.168.56.22/138 (MSSQL)
</pre>
                        </div>

                        <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 15px;">资源开销：</h4>
                        <ul style="color: var(--text-secondary); font-size: 13px; padding-left: 20px;">
                            <li><strong>内存开销：</strong> 运行时约需 14.0 GB ~ 16.0 GB 物理内存</li>
                            <li><strong>磁盘占用：</strong> 约 65.0 GB ~ 75.0 GB 磁盘空间</li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Detailed Node Layout & Credential Matrix -->
            <div class="bp-card">
                <h3 class="bp-title"><i class="fa fa-server" style="color: #6366f1;"></i> 详细节点布局与默认凭据矩阵 (Credential Matrix)</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped cred-table">
                        <thead>
                            <tr style="background: var(--bg-secondary);">
                                <th>主机名</th>
                                <th>包含于</th>
                                <th>操作系统</th>
                                <th>静态 IP</th>
                                <th>FQDN / 域名</th>
                                <th>预置关键账户与口令</th>
                                <th>代表性漏洞场景</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>DC01</strong></td>
                                <td><span class="label label-primary">3台 / 5台</span></td>
                                <td>Win2019</td>
                                <td><code>192.168.56.134</code> (或 <code>.10</code>)</td>
                                <td><code>kingslanding.sevenkingdoms.local</code></td>
                                <td><code>administrator</code> / <code>Passw0rd123!</code><br><code>tywin.lannister</code> / <code>Passw0rd123!</code></td>
                                <td>AD CS ESC1 模板滥用、ACL 7 级跃迁链终点、DCSync</td>
                            </tr>
                            <tr>
                                <td><strong>DC02</strong></td>
                                <td><span class="label label-primary">3台 / 5台</span></td>
                                <td>Win2019</td>
                                <td><code>192.168.56.136</code> (或 <code>.11</code>)</td>
                                <td><code>winterfell.north.sevenkingdoms.local</code></td>
                                <td><code>eddard.stark</code> / <code>winterishere</code><br><code>jon.snow</code> / <code>iknownothing</code></td>
                                <td>AS-REP Roasting (无预认证)、父子域信任跨域提权</td>
                            </tr>
                            <tr>
                                <td><strong>SRV02</strong></td>
                                <td><span class="label label-primary">3台 / 5台</span></td>
                                <td>Win2019</td>
                                <td><code>192.168.56.138</code> (或 <code>.22</code>)</td>
                                <td><code>castelblack.north.sevenkingdoms.local</code></td>
                                <td><code>sql_svc</code> / <code>MYpassword123#</code><br><code>samwell.tarly</code> / <code>password</code></td>
                                <td>WebDAV 上传点、Kerberoasting SPN、MSSQL 模拟特权</td>
                            </tr>
                            <tr>
                                <td><strong>DC03</strong></td>
                                <td><span class="label label-danger">仅5台完整版</span></td>
                                <td>Win2016</td>
                                <td><code>192.168.56.12</code></td>
                                <td><code>meereen.essos.local</code></td>
                                <td><code>administrator</code> / <code>Passw0rd123!</code><br><code>daenerys.targaryen</code> / <code>Passw0rd123!</code></td>
                                <td><strong>ZeroLogon (CVE-2020-1472)</strong>、跨林双向信任穿透</td>
                            </tr>
                            <tr>
                                <td><strong>SRV03</strong></td>
                                <td><span class="label label-danger">仅5台完整版</span></td>
                                <td>Win2016</td>
                                <td><code>192.168.56.23</code></td>
                                <td><code>braavos.essos.local</code></td>
                                <td><code>khal.drogo</code> / <code>Passw0rd123!</code></td>
                                <td><strong>AD CS ESC8 NTLM HTTP 中继</strong> (Web 证书端点)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deployment Commands Guide -->
            <div class="bp-card">
                <h3 class="bp-title"><i class="fa fa-terminal" style="color: #10b981;"></i> 快速部署与模式切换指令集</h3>
                
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary);">方式 A：启动 3 台精简版 (GOAD-Light)</h4>
                <div class="cmd-box">
cd /mnt/c/Users/Administrator/VScode/Pikachu-Enhanced/docker/goad
./goad.sh -t start -l GOAD-Light -p vmware
./goad.sh -t provide -l GOAD-Light -p vmware
                </div>

                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-top: 20px;">方式 B：启动 5 台完整版 (Full GOAD)</h4>
                <div class="cmd-box">
cd /mnt/c/Users/Administrator/VScode/Pikachu-Enhanced/docker/goad
./goad.sh -t start -l GOAD -p vmware
./goad.sh -t provide -l GOAD -p vmware
                </div>

                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-top: 20px;">方式 C：一键暂停释放 CPU / 内存</h4>
                <div class="cmd-box">
cd C:\Users\Administrator\VScode\Pikachu-Enhanced\docker\goad\ad\GOAD-Light\providers\vmware
vagrant suspend
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
