<?php
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[234] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$res = '';
if (isset($_POST['pth_submit'])) {
    $user = trim($_POST['username']);
    $hash = trim($_POST['ntlm_hash']);
    
    if (empty($user) || empty($hash)) {
        $res = '<div class="alert alert-danger">请输入用户名与 NTLM 哈希。</div>';
    } else {
        $res = '<div class="alert alert-success">';
        $res .= '<h4><i class="fa fa-exchange"></i> Pass-the-Hash (哈希传递) 模拟攻击成功！</h4>';
        $res .= '<p><strong>凭据:</strong> ' . htmlspecialchars($user) . ' | NTLM: <code>' . htmlspecialchars($hash) . '</code></p>';
        $res .= '<p><strong>动作:</strong> 成功绕过明文口令输入校验，通过 SMB (445) / WMI 注入认证凭据令牌。</p>';
        $res .= '<p><strong>结果:</strong> 成功在目标机器 <code>SERVER-FINANCE-01</code> 上获得 SYSTEM 权限远程 Shell！</p>';
        $res .= '</div>';
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    Active Directory 域内横向移动与哈希传递 (PtH)
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        Pass-the-Hash / Pass-the-Ticket
                    </small>
                </h1>
            </div>

            <div class="vul">
                <h3 class="v_title">Pass-the-Hash (哈希传递) 攻击演练</h3>
                <p>在 Windows 域中，密码的 NTLM 哈希直接保存在 LSASS 内存中。攻击者提取出 NTLM Hash 后，无需解密出明文密码，即可直接将其传递给网络中的其他服务器进行身份认证。</p>

                <form method="post" style="max-width: 600px; margin-top: 20px;">
                    <div class="form-group">
                        <label>目标域账号:</label>
                        <input type="text" name="username" class="form-control" value="CORP\DomainAdmin">
                    </div>
                    <div class="form-group">
                        <label>提取到的 NTLM 哈希 (LM:NTLM):</label>
                        <input type="text" name="ntlm_hash" class="form-control" value="aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0">
                    </div>
                    <button type="submit" name="pth_submit" class="btn btn-primary">
                        <i class="fa fa-rocket"></i> 执行 Pass-the-Hash 凭据传递
                    </button>
                </form>

                <div style="margin-top: 25px;">
                    <?php echo $res; ?>
                </div>

                <div class="well" style="margin-top: 30px;">
                    <h4>🛠️ 常用域内横向移动工具链:</h4>
                    <ul>
                        <li><code>Impacket psexec.py</code> / <code>wmiexec.py</code>: 传入 <code>-hashes :31d6cfe...</code> 直接弹 Shell。</li>
                        <li><code>Mimikatz</code>: 内存中提取 LSASS 密码凭据 <code>sekurlsa::logonpasswords</code>。</li>
                        <li><code>BloodHound</code>: 图数据库可视化分析域内特权路径与 ACL 赋权缺陷。</li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
