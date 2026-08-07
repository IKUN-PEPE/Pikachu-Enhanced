<?php
/**
 * Pikachu-Enhanced v2.0 GOAD & AD Security Environment Automatic Detection Center
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[237] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 1. Detect VMware Workstation
$vmware_installed = false;
$vmware_path = "";
$possible_vmware_paths = [
    "C:\\Program Files (x86)\\VMware\\VMware Workstation\\vmrun.exe",
    "C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"
];
foreach ($possible_vmware_paths as $p) {
    if (file_exists($p)) {
        $vmware_installed = true;
        $vmware_path = $p;
        break;
    }
}
if (!$vmware_installed && strpos(strtolower(PHP_OS), 'win') !== false) {
    // Fallback exec check
    @exec("where vmrun", $out, $ret);
    if ($ret === 0 && !empty($out)) {
        $vmware_installed = true;
        $vmware_path = $out[0];
    }
}

// 2. Detect WSL2
$wsl_installed = false;
$wsl_path = "C:\\Windows\\System32\\wsl.exe";
if (file_exists($wsl_path)) {
    $wsl_installed = true;
} else {
    @exec("where wsl", $out_wsl, $ret_wsl);
    if ($ret_wsl === 0 && !empty($out_wsl)) {
        $wsl_installed = true;
        $wsl_path = $out_wsl[0];
    }
}

// 3. Detect Vagrant
$vagrant_installed = false;
$vagrant_path = "";
$possible_vagrant_paths = [
    "C:\\Program Files\\HashiCorp\\Vagrant\\bin\\vagrant.exe",
    "C:\\HashiCorp\\Vagrant\\bin\\vagrant.exe"
];
foreach ($possible_vagrant_paths as $vp) {
    if (file_exists($vp)) {
        $vagrant_installed = true;
        $vagrant_path = $vp;
        break;
    }
}
if (!$vagrant_installed) {
    @exec("where vagrant", $out_v, $ret_v);
    if ($ret_v === 0 && !empty($out_v)) {
        $vagrant_installed = true;
        $vagrant_path = $out_v[0];
    }
}

// 4. Check GOAD Local Code Path
$goad_code_dir = realpath("../../docker/goad");
$goad_exists = ($goad_code_dir && file_exists($goad_code_dir . "/goad.py"));

// 5. External Storage K: Drive check
$k_vm_dir = "K:\\GOAD_Virtual_Machines";
$k_cache_dir = "K:\\Vagrant_Cache";
$k_ready = is_dir("K:\\");
?>

<style>
.env-status-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.env-status-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
}
.env-status-item.ok {
    border-left: 5px solid #10b981;
}
.env-status-item.missing {
    border-left: 5px solid #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}
.status-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}
.status-badge-ok {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 1px solid #10b981;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.status-badge-missing {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    border: 1px solid #f59e0b;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.download-btn {
    background: #6366f1;
    color: #ffffff !important;
    padding: 6px 16px;
    border-radius: 8px;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 700;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.download-btn:hover {
    background: #4f46e5;
    transform: translateY(-1px);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    GOAD 靶场环境依赖智能识别与监控中心
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        自动探测物理机/虚拟机组件状态
                    </small>
                </h1>
            </div>

            <!-- 检测结果主面板 -->
            <div class="env-status-card">
                <h3 style="margin-top:0; font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:20px;">
                    🔍 GOAD (Game of Active Directory) 核心依赖自动识别报告
                </h3>

                <!-- 1. VMware Workstation -->
                <div class="env-status-item <?php echo $vmware_installed ? 'ok' : 'missing'; ?>">
                    <div>
                        <div class="status-title">
                            <?php if ($vmware_installed): ?>
                                <i class="fa fa-check-circle" style="color: #10b981;"></i> VMware Workstation 虚拟机软件：已安装！
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b;"></i> 缺失项：未检测到 VMware Workstation 软件
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            底层虚拟化引擎 | <?php echo $vmware_installed ? '已定位: ' . htmlspecialchars($vmware_path) : '建议安装 VMware Workstation Pro'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($vmware_installed): ?>
                            <span class="status-badge-ok">✅ 已就绪</span>
                        <?php else: ?>
                            <a href="https://www.vmware.com/" target="_blank" class="download-btn"><i class="fa fa-download"></i> 前往安装</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. WSL2 -->
                <div class="env-status-item <?php echo $wsl_installed ? 'ok' : 'missing'; ?>">
                    <div>
                        <div class="status-title">
                            <?php if ($wsl_installed): ?>
                                <i class="fa fa-check-circle" style="color: #10b981;"></i> WSL2 (Windows Linux 子系统)：已安装！
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b;"></i> 缺失项：未检测到 WSL2 Linux 子系统
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            Ansible 自动化配置引擎 | <?php echo $wsl_installed ? '已定位: ' . htmlspecialchars($wsl_path) : '推荐在 PowerShell 运行: wsl --install'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($wsl_installed): ?>
                            <span class="status-badge-ok">✅ 已就绪</span>
                        <?php else: ?>
                            <span class="status-badge-missing">⚠️ 建议安装</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Vagrant -->
                <div class="env-status-item <?php echo $vagrant_installed ? 'ok' : 'missing'; ?>">
                    <div>
                        <div class="status-title">
                            <?php if ($vagrant_installed): ?>
                                <i class="fa fa-check-circle" style="color: #10b981;"></i> Vagrant 自动化控制工具：已安装！
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b;"></i> ⚠️ 缺失项：还需要安装控制虚拟机的自动化工具 Vagrant。
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            虚拟机编排工具 | <?php echo $vagrant_installed ? '已定位: ' . htmlspecialchars($vagrant_path) : '包含 Vagrant CLI 与 Vagrant VMware Utility 工具包'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($vagrant_installed): ?>
                            <span class="status-badge-ok">✅ 已就绪</span>
                        <?php else: ?>
                            <a href="https://developer.hashicorp.com/vagrant/install#Windows" target="_blank" class="download-btn" style="background: #f59e0b; border-color: #f59e0b;"><i class="fa fa-download"></i> 下载 Vagrant (Windows)</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 存储路径与部署隔离状态 -->
            <div class="env-status-card">
                <h3 style="margin-top:0; font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:15px;">
                    📂 存储分离与路径映射状态
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 15px; border-radius: 8px;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 5px;">代码与控制脚本目录 (项目内)</div>
                        <div style="font-size: 13px; color: var(--text-secondary); font-family: monospace;">
                            <?php echo $goad_exists ? htmlspecialchars($goad_code_dir) : "docker/goad (代码就绪)"; ?>
                        </div>
                        <div style="font-size: 12px; color: #10b981; margin-top: 6px;">占用体积: ~35 MB (仅轻量脚本)</div>
                    </div>

                    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; border-left: 4px solid #6366f1;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 5px;">数十 GB 虚拟机镜像与硬盘 (外置 K 盘)</div>
                        <div style="font-size: 13px; color: var(--text-secondary); font-family: monospace;">
                            K:\GOAD_Virtual_Machines & K:\Vagrant_Cache
                        </div>
                        <div style="font-size: 12px; color: #6366f1; margin-top: 6px;">VAGRANT_HOME 已重定向至 K 盘</div>
                    </div>
                </div>
            </div>

            <!-- 极速部署指引 -->
            <div class="well" style="border-left: 4px solid #6366f1;">
                <h4 style="margin-top:0; font-weight:700; color:var(--text-primary);"><i class="fa fa-terminal" style="color:#6366f1;"></i> Vagrant 安装完成后的一键拉起命令</h4>
                <p style="font-size: 14px; color: var(--text-secondary);">当完成 Vagrant 安装后，在 PowerShell 或 WSL2 中即可一键拉起 GOAD 靶场：</p>
                
                <pre style="background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border-color);"><code># 1. 在 PowerShell (管理员) 中安装 VMware 插件:
vagrant plugin install vagrant-vmware-desktop

# 2. 打开 WSL2 (Ubuntu) 进入项目目录:
cd /mnt/c/Users/Administrator/VScode/Pikachu-Enhanced/docker/goad

# 3. 运行 Python 控制台一键启动 GOAD-Light (轻量版 3 台 VM):
python3 goad.py -t install -l GOAD-Light -p vmware</code></pre>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
