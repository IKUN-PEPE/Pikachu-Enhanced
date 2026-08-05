<?php
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[233] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$output = '';
if (isset($_POST['attack_type'])) {
    $type = $_POST['attack_type'];
    if ($type == 'kerberoasting') {
        $output = '<div class="alert alert-info" style="background: rgba(168, 85, 247, 0.1); border-color: #a855f7; color: var(--text-primary);">';
        $output .= '<h4><i class="fa fa-ticket"></i> Kerberoasting 攻击 Payload 模拟生成</h4>';
        $output .= '<p>捕获到的 SPN (Service Principal Name) 服务账号请求 TGS 票据加密哈希 ( Hash Type: $krb5tgs$23$* ):</p>';
        $output .= '<pre><code>$krb5tgs$23$*MSSQLSvc/sql01.corp.com:1433*CORP.COM*$82fe91a0c...$c15d4fa5882195f0a0491823ab19f01...</code></pre>';
        $output .= '<p><strong>离线爆破说明:</strong> 攻击者可以使用 Hashcat <code>hashcat -m 13100 krb5tgs.txt wordlist.txt</code> 离线爆破获取高权限 SQL 服务账号的明文口令。</p>';
        $output .= '</div>';
    } elseif ($type == 'asrep') {
        $output = '<div class="alert alert-warning" style="background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: var(--text-primary);">';
        $output .= '<h4><i class="fa fa-key"></i> AS-REP Roasting (预认证缺失) 提取</h4>';
        $output .= '<p>检测到域账号 <code>user_backup</code> 开启了 "Do not require Kerberos preauthentication" 属性。</p>';
        $output .= '<pre><code>$krb5asrep$23$user_backup@CORP.COM:780f21a5...$a14f901bc74e92a01...</code></pre>';
        $output .= '<p>攻击者无需已知明文密码，即可向 KDC 请求获取由该用户口令哈希加密的 AS-REP 响应，并离线破解！</p>';
        $output .= '</div>';
    } elseif ($type == 'golden') {
        $output = '<div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: var(--text-primary);">';
        $output .= '<h4><i class="fa fa-trophy"></i> Golden Ticket (黄金票据) 伪造成功！</h4>';
        $output .= '<p><strong>提取的 krbtgt NTLM Hash:</strong> <code>e19e8841a01103ad215321f558291a11</code></p>';
        $output .= '<p><strong>伪造生成的 TGT 票据凭证:</strong> <code>ticket.kirbi (SID: S-1-5-21-362...-500 Administrator)</code></p>';
        $output .= '<p><strong>危害评估:</strong> 持有黄金票据可以在任何时间伪造任意域用户身份，获得全域掌控权，即使修改管理员密码也无法阻挡！</p>';
        $output .= '</div>';
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <div class="page-header">
                <h1>
                    Kerberos 域认证协议攻击与票据伪造演练
                    <small>
                        <i class="ace-icon fa fa-angle-double-right"></i>
                        Kerberoasting / AS-REP / 黄金白银票据
                    </small>
                </h1>
            </div>

            <div class="vul">
                <h3 class="v_title">Kerberos 认证安全测试模拟器</h3>
                <p>Kerberos 是 Windows 域环境中的核心认证协议（基于 Ticket 票据体系）。请选择不同的攻击维度进行原理验证：</p>

                <form method="post" style="max-width: 600px; margin-top: 20px;">
                    <div class="form-group">
                        <label>选择 Kerberos 攻击向量:</label>
                        <select name="attack_type" class="form-control">
                            <option value="kerberoasting">1. Kerberoasting (服务账号 TGS 票据提取离线破解)</option>
                            <option value="asrep">2. AS-REP Roasting (未开启预认证域账号提取)</option>
                            <option value="golden">3. Golden Ticket (基于 krbtgt 密钥伪造黄金票据)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-flash"></i> 发起 Kerberos 漏洞模拟演示
                    </button>
                </form>

                <div style="margin-top: 25px;">
                    <?php echo $output; ?>
                </div>

                <div class="well" style="margin-top: 30px;">
                    <h4>🛡️ 防御与安全防护基线:</h4>
                    <ul>
                        <li>使用强随机长密码（25 位以上）设置 SPN 服务账号口令，抵御 Kerberoasting 爆破。</li>
                        <li>定期重置 <code>krbtgt</code> 账号的密码（需重置两次）以失效所有攻击者伪造的黄金票据。</li>
                        <li>确保域内所有账号强制开启 Kerberos 预认证 (Pre-Authentication)。</li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
