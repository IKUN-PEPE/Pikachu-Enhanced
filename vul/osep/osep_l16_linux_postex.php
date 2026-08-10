<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[289] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L16_Linux_PostEx_SharedLib_Profile_Kerberos}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag16'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！Linux 后渗透技术研究。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #6366f1); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(99,102,241,0.3); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(99,102,241,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 16: Linux 后渗透技术研究 <span class="badge badge-warning">300 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡研究 Linux 环境下的持久化、杀软规避、Kerberos 横向移动以及 DevOps 工具链的滥用。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> Linux 用户配置文件利用</h4>
        <p>攻击者通过篡改 <code>.bash_profile</code>, <code>.bashrc</code>, <code>.bash_logout</code> 等用户配置文件植入持久化后门。此外，还可以通过劫持 <code>PATH</code> 环境变量、恶意替换 <code>alias</code> 等手段伪造常用命令（如 sudo），或者滥用 <code>cron job</code> 机制定时反弹 Shell。</p>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> 共享库劫持</h4>
        <p>通过配置 <code>LD_PRELOAD</code> 环境变量或修改 <code>/etc/ld.so.conf.d/</code> 劫持共享库加载路径，以及利用 rpath 篡改，攻击者可以使合法程序加载恶意动态链接库 (so)。如果目标是 SUID 二进制文件，这可以实现本地提权。</p>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> Linux 下的杀软规避</h4>
        <p>现代 Linux 杀软会监控磁盘文件执行，因此无文件内存执行成为主流：</p>
        <ul>
            <li>利用 <code>memfd_create</code> 系统调用在内存中创建匿名文件并执行 ELF。</li>
            <li>通过 <code>/proc/self/mem</code> 注入代码。</li>
            <li>利用 Python <code>ctypes</code> 库在内存中分配空间并执行 shellcode。</li>
        </ul>
        <div class="cmd-box">
<span class="comment">-- 获取 flag</span>
<span class="flag-text">echo 'flag{OSEP_L16_Linux_PostEx_SharedLib_Profile_Kerberos}'</span>
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> Linux Kerberos 横向移动</h4>
        <p>在加入域的 Linux 机器上，凭证通常以 <code>ccache</code> 文件形式保存在 <code>/tmp</code> 目录下，或者由 <code>KRB5CCNAME</code> 环境变量指定。攻击者可以窃取并复用这些票据（如使用 <code>kinit</code> 和 <code>klist</code> 操作），实现 Linux-to-Windows 的 Kerberos 横向移动。</p>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> DevOps 横向移动</h4>
        <p>企业基础架构中的 DevOps 工具往往拥有高权限并掌握大量凭据：</p>
        <ul>
            <li><strong>Jenkins CI：</strong>通过 Groovy Console 或构建脚本实现远程代码执行。</li>
            <li><strong>GitLab Runner：</strong>滥用 runner 注册令牌或配置的 shell/docker 执行器。</li>
            <li><strong>Docker & Kubernetes：</strong>通过挂载 Docker socket 逃逸至宿主机，或利用 pod 中泄露的服务账户令牌 (Service Account Token) 接管 Kubernetes 集群。</li>
        </ul>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline" style="justify-content: center;">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="输入Flag，例如 flag{...}" style="width: 300px;">
            </div>
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#6366f1; border-color:#4f46e5; margin-left:10px;">提交验证</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="osep_l15_mssql.php" class="btn btn-default">上一关</a>
            <a href="osep_l17_kiosk_escape.php" class="btn btn-default" style="margin-left:10px;">下一关</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
