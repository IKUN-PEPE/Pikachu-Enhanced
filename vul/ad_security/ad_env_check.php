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

// =========================================================================
// ROBUST ENVIRONMENT DETECTION ALGORITHM (Compatible with 32-bit & 64-bit PHP)
// =========================================================================

// 1. Detect VMware Workstation
$vmware_installed = false;
$vmware_path = "";
$possible_vmware_files = [
    "C:\\Program Files (x86)\\VMware\\VMware Workstation\\vmware.exe",
    "C:\\Program Files (x86)\\VMware\\VMware Workstation\\vmrun.exe",
    "C:\\Program Files\\VMware\\VMware Workstation\\vmware.exe",
    "C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"
];
foreach ($possible_vmware_files as $f) {
    if (@file_exists($f)) {
        $vmware_installed = true;
        $vmware_path = $f;
        break;
    }
}
if (!$vmware_installed) {
    // Registry query fallbacks
    @exec('reg query "HKLM\\SOFTWARE\\WOW6432Node\\VMware, Inc.\\VMware Workstation" /v InstallPath 2>&1', $out_reg1, $ret_reg1);
    if ($ret_reg1 === 0 && !empty($out_reg1)) {
        foreach ($out_reg1 as $line) {
            if (strpos($line, 'InstallPath') !== false) {
                $vmware_installed = true;
                $vmware_path = trim(substr($line, strpos($line, 'REG_SZ') + 6)) . "vmware.exe";
                break;
            }
        }
    }
}
if (!$vmware_installed) {
    @exec('reg query "HKLM\\SOFTWARE\\VMware, Inc.\\VMware Workstation" /v InstallPath 2>&1', $out_reg2, $ret_reg2);
    if ($ret_reg2 === 0 && !empty($out_reg2)) {
        foreach ($out_reg2 as $line) {
            if (strpos($line, 'InstallPath') !== false) {
                $vmware_installed = true;
                $vmware_path = trim(substr($line, strpos($line, 'REG_SZ') + 6)) . "vmware.exe";
                break;
            }
        }
    }
}
if (!$vmware_installed) {
    @exec('sc query "VMAuthdService" 2>&1', $out_sc, $ret_sc);
    if ($ret_sc === 0) {
        $vmware_installed = true;
        $vmware_path = "Windows 服务: VMAuthdService (VMware Workstation)";
    }
}

// 2. Detect WSL2 (Bypassing 32-bit PHP SysWOW64 redirection using SysNative & Registry)
$wsl_installed = false;
$wsl_path = "";
$possible_wsl_files = [
    "C:\\Windows\\System32\\wsl.exe",
    "C:\\Windows\\SysNative\\wsl.exe"
];
foreach ($possible_wsl_files as $wf) {
    if (@file_exists($wf)) {
        $wsl_installed = true;
        $wsl_path = $wf;
        break;
    }
}
if (!$wsl_installed) {
    @exec('reg query "HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Lxss" 2>&1', $out_lxss, $ret_lxss);
    if ($ret_lxss === 0) {
        $wsl_installed = true;
        $wsl_path = "C:\\Windows\\System32\\wsl.exe (已通过注册表 Lxss 确认)";
    }
}
if (!$wsl_installed) {
    @exec('wsl --status 2>&1', $out_wsl_cmd, $ret_wsl_cmd);
    if ($ret_wsl_cmd === 0) {
        $wsl_installed = true;
        $wsl_path = "C:\\Windows\\System32\\wsl.exe";
    }
}

// 3. Detect Vagrant
$vagrant_installed = false;
$vagrant_path = "";
$possible_vagrant_files = [
    "C:\\Program Files\\Vagrant\\bin\\vagrant.exe",
    "C:\\Program Files (x86)\\Vagrant\\bin\\vagrant.exe",
    "C:\\Program Files\\HashiCorp\\Vagrant\\bin\\vagrant.exe",
    "C:\\HashiCorp\\Vagrant\\bin\\vagrant.exe",
    "C:\\Program Files (x86)\\HashiCorp\\Vagrant\\bin\\vagrant.exe"
];
foreach ($possible_vagrant_files as $vf) {
    if (@file_exists($vf)) {
        $vagrant_installed = true;
        $vagrant_path = $vf;
        break;
    }
}
if (!$vagrant_installed) {
    @exec('reg query "HKLM\\SOFTWARE\\HashiCorp\\Vagrant" 2>&1', $out_v_reg, $ret_v_reg);
    if ($ret_v_reg === 0) {
        $vagrant_installed = true;
        $vagrant_path = "注册表已定位 HashiCorp Vagrant";
    }
}
if (!$vagrant_installed) {
    @exec('vagrant --version 2>&1', $out_v_cmd, $ret_v_cmd);
    if ($ret_v_cmd === 0 && !empty($out_v_cmd)) {
        $vagrant_installed = true;
        $vagrant_path = "Vagrant CLI (" . trim($out_v_cmd[0]) . ")";
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
    background: rgba(16, 185, 129, 0.05);
}
.env-status-item.missing {
    border-left: 5px solid #f59e0b;
    background: rgba(245, 158, 11, 0.08);
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
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.status-badge-missing {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
    border: 1px solid #f59e0b;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}
.download-btn {
    background: #f59e0b;
    color: #ffffff !important;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 700;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}
.download-btn:hover {
    background: #d97706;
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
                                <i class="fa fa-check-circle" style="color: #10b981; font-size: 20px;"></i> 
                                <span>VMware Workstation 虚拟机软件：已安装！</span>
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b; font-size: 20px;"></i> 
                                <span>缺失项：未检测到 VMware Workstation 软件</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; padding-left: 30px;">
                            底层虚拟化引擎 | <?php echo $vmware_installed ? '已成功定位: <code>' . htmlspecialchars($vmware_path) . '</code>' : '建议安装 VMware Workstation Pro'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($vmware_installed): ?>
                            <span class="status-badge-ok"><i class="fa fa-check"></i> 已就绪</span>
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
                                <i class="fa fa-check-circle" style="color: #10b981; font-size: 20px;"></i> 
                                <span>WSL2 (Windows Linux 子系统)：已安装！</span>
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b; font-size: 20px;"></i> 
                                <span>缺失项：未检测到 WSL2 Linux 子系统</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; padding-left: 30px;">
                            Ansible 自动化配置引擎 | <?php echo $wsl_installed ? '已成功定位: <code>' . htmlspecialchars($wsl_path) . '</code>' : '推荐在 PowerShell 运行: wsl --install'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($wsl_installed): ?>
                            <span class="status-badge-ok"><i class="fa fa-check"></i> 已就绪</span>
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
                                <i class="fa fa-check-circle" style="color: #10b981; font-size: 20px;"></i> 
                                <span>Vagrant 自动化控制工具：已安装！</span>
                            <?php else: ?>
                                <i class="fa fa-exclamation-triangle" style="color: #f59e0b; font-size: 20px;"></i> 
                                <span>⚠️ 缺失项：还需要安装控制虚拟机的自动化工具 Vagrant。</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; padding-left: 30px;">
                            虚拟机编排控制端 | <?php echo $vagrant_installed ? '已成功定位: <code>' . htmlspecialchars($vagrant_path) . '</code>' : '需要安装 Vagrant CLI 与 Vagrant VMware Utility 工具包'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($vagrant_installed): ?>
                            <span class="status-badge-ok"><i class="fa fa-check"></i> 已就绪</span>
                        <?php else: ?>
                            <a href="https://developer.hashicorp.com/vagrant/install#Windows" target="_blank" class="download-btn"><i class="fa fa-download"></i> 下载 Vagrant (Windows)</a>
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
                <h4 style="margin-top:0; font-weight:700; color:var(--text-primary);"><i class="fa fa-terminal" style="color:#6366f1;"></i> Vagrant 安装完成后的 3 步拉起指南</h4>
                <p style="font-size: 14px; color: var(--text-secondary);">当您完成 Vagrant 下载安装后，按以下 3 步即可在本地拉起完整的 GOAD 靶场：</p>
                
                <pre style="background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border-color);"><code># 步骤 1：在 PowerShell (管理员) 中安装 VMware 驱动插件:
vagrant plugin install vagrant-vmware-desktop

# 步骤 2：打开 WSL2 (Ubuntu 终端) 进入 GOAD 目录:
cd /mnt/c/Users/Administrator/VScode/Pikachu-Enhanced/docker/goad

# 步骤 3：运行 Python 控制台一键构建 GOAD-Light 靶场 (3台虚拟机):
python3 goad.py -t install -l GOAD-Light -p vmware</code></pre>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
