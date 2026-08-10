<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[289] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L17_Linux_PostEx_SharedLib_DevOps_Kerberos}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag17'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;">✅ 通关！OSEP L17 Linux 后渗透已掌握 (+300 PTS)</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;">❌ Flag 错误，继续研究 Linux 后渗透技术。</div>';
    }
}
?>
<style>
.ctf-stage-header{background:linear-gradient(135deg,#0c1218 0%,#1a1030 100%);border-radius:14px;padding:25px 30px;color:#fff;margin-bottom:25px;border:1px solid rgba(99,102,241,0.35);}
.ctf-stage-title{color:#fff!important;font-size:22px;font-weight:800;margin:0 0 10px 0;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.step-box{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;margin-bottom:22px;}
.step-title{font-size:16px;font-weight:700;color:var(--text-primary);margin-top:0;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.step-num{background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.cmd-box{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:14px 18px;font-family:monospace;font-size:13px;color:#7dd3fc;margin:12px 0;overflow-x:auto;line-height:1.9;}
.cmd-box .comment{color:#64748b;}
.cmd-box .flag-text{color:#fbbf24;font-weight:bold;}
.cmd-box .key{color:#a78bfa;font-weight:bold;}
.cmd-box .warn{color:#f87171;}
.highlight-box{background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.3);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.def-box{background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.3);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.flag-submit-area{background:var(--bg-card);border:2px dashed rgba(99,102,241,0.4);border-radius:12px;padding:24px;margin-top:25px;text-align:center;}
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">

<div class="ctf-stage-header">
  <h1 class="ctf-stage-title">🐧 OSEP L17：Linux 后渗透、共享库劫持与 DevOps 横向移动
    <span style="background:rgba(99,102,241,0.2);color:#a5b4fc;border:1px solid #6366f1;padding:3px 10px;border-radius:12px;font-size:12px;">Linux 后渗透 · 300 PTS</span>
  </h1>
  <p style="color:#c4b5fd;font-size:14px;margin:0;line-height:1.6;">从蓝队视角理解 Linux 环境下的后渗透技术：用户配置文件持久化机制、共享库劫持攻击链、Linux Kerberos 凭据复用，以及 DevOps 基础设施（Jenkins/GitLab/Docker/K8s）中常见的权限升级路径与检测方法。</p>
  <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
    <span style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#e2e8f0;">🔧 Linux · LD_PRELOAD · ccache · Jenkins · Docker · K8s</span>
    <a href="osep_hub.php" style="background:rgba(255,255,255,0.08);padding:3px 10px;border-radius:8px;font-size:12px;color:#a5b4fc;text-decoration:none;">← OSEP 大厅</a>
  </div>
</div>

<!-- Step 1 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">1</span> Linux 用户配置文件持久化机制分析</h3>
  <div class="highlight-box">
    📚 <strong>攻击者视角：</strong>Linux 用户目录下存在多种自动执行的配置文件，攻击者在获取低权限 Shell 后常利用这些文件实现持久化。
  </div>
  <div class="cmd-box">
<span class="comment"># 常见持久化位置（防御者审计重点）：</span><br>
~/.bash_profile    <span class="comment">← 交互式登录 Shell 启动时执行</span><br>
~/.bashrc          <span class="comment">← 非登录交互式 Shell 启动时执行</span><br>
~/.bash_logout     <span class="comment">← 注销时执行（可用于清理痕迹）</span><br>
~/.profile         <span class="comment">← sh/dash 兼容登录脚本</span><br>
~/.config/autostart/ <span class="comment">← 图形桌面自启动（GNOME/KDE）</span><br><br>
<span class="comment"># 检测思路：</span><br>
<span class="comment"># 1. 对比 ~/.bashrc 与标准模板的差异</span><br>
diff ~/.bashrc /etc/skel/.bashrc<br><br>
<span class="comment"># 2. 检查 PATH 变量是否被篡改（恶意目录优先级更高）</span><br>
echo $PATH | tr ':' '\n'  <span class="comment">← 查找非标准路径</span><br><br>
<span class="comment"># 3. Cron 任务审计（攻击者常见隐藏位置）：</span><br>
crontab -l                  <span class="comment">← 当前用户</span><br>
ls -la /etc/cron.d/         <span class="comment">← 系统级 cron</span><br>
ls -la /var/spool/cron/     <span class="comment">← 各用户 cron 存储目录</span>
  </div>
  <div class="def-box">
    🛡️ <strong>防御建议：</strong>使用 auditd 监控对 ~/.bashrc、~/.profile 等文件的写操作（-w ~/.bashrc -p wa）。Wazuh/OSSEC 可配置基于文件完整性监控的告警规则。
  </div>
</div>

<!-- Step 2 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">2</span> 共享库劫持（Shared Library Hijacking）原理与检测</h3>
  <div class="cmd-box">
<span class="comment"># Linux 动态链接库加载顺序（优先级从高到低）：</span><br>
<span class="key">1.</span> RPATH/RUNPATH（编译时嵌入二进制）<br>
<span class="key">2.</span> LD_PRELOAD 环境变量<br>
<span class="key">3.</span> /etc/ld.so.cache（ldconfig 生成的缓存）<br>
<span class="key">4.</span> /lib /usr/lib（默认系统库路径）<br><br>
<span class="comment"># 攻击向量 1：LD_PRELOAD 劫持</span><br>
<span class="comment"># 原理：指定的 .so 文件在所有其他库之前加载</span><br>
<span class="comment"># 可以 hook libc 函数（如 getuid）返回篡改值</span><br>
<span class="comment"># 检测方法：审计 LD_PRELOAD 环境变量设置</span><br>
env | grep LD_PRELOAD<br>
cat /proc/&lt;PID&gt;/environ | tr '\0' '\n' | grep LD_<br><br>
<span class="comment"># 攻击向量 2：可写路径注入</span><br>
<span class="comment"># 检查 RPATH 指向的目录是否可写：</span><br>
readelf -d /usr/sbin/apache2 | grep -E "RPATH|RUNPATH"<br>
ls -la /opt/apache2/lib/  <span class="comment">← 检查 RPATH 目录权限</span><br><br>
<span class="comment"># 攻击向量 3：ldconfig 路径注入</span><br>
<span class="comment"># 在 /etc/ld.so.conf.d/ 中添加可控目录</span><br>
ls -la /etc/ld.so.conf.d/   <span class="comment">← 谁有写权限？</span>
  </div>
  <div class="def-box">
    🛡️ <strong>防御建议：</strong>① 确保 /etc/ld.so.conf.d/ 仅 root 可写；② 对 SUID 程序使用 nosuid 挂载选项；③ 使用 AppArmor/SELinux 限制共享库加载路径；④ 定期运行 ldd 检查关键二进制的库依赖完整性。
  </div>
</div>

<!-- Step 3 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">3</span> Linux 内存驻留执行技术（蓝队检测视角）</h3>
  <div class="cmd-box">
<span class="comment"># 攻击者目标：在不写磁盘的情况下执行代码</span><br><br>
<span class="comment"># 技术1：memfd_create() 匿名文件描述符</span><br>
<span class="comment"># 原理：创建一个不关联磁盘文件的文件描述符</span><br>
<span class="comment"># 在 /proc/&lt;PID&gt;/fd/ 可以看到 (deleted) 或空路径</span><br>
<span class="comment"># 检测命令：</span><br>
ls -la /proc/*/fd/ 2&gt;/dev/null | grep "memfd"<br>
cat /proc/*/maps 2&gt;/dev/null | grep -v "\.so\|vdso\|vsyscall" | grep "rwxp"<br><br>
<span class="comment"># 技术2：/proc/self/mem 注入</span><br>
<span class="comment"># 通过写 /proc/PID/mem 修改运行中进程的内存</span><br>
<span class="comment"># 需要 ptrace 权限，或者是同 UID 的进程</span><br>
<span class="comment"># 检测：auditd 监控 /proc/*/mem 的写操作</span><br><br>
<span class="comment"># 蓝队检测工具：</span><br>
<span class="comment"># Falco 规则：检测 memfd_create 系统调用</span><br>
<span class="comment"># - rule: Shellcode Execution via memfd_create</span><br>
<span class="comment">#   condition: evt.type=memfd_create and proc.name != (expected_procs)</span><br><br>
<span class="comment"># 内存取证：找无磁盘文件对应的可执行映射区域</span><br>
cat /proc/&lt;PID&gt;/smaps | grep -A 5 "rwx"
  </div>
</div>

<!-- Step 4 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">4</span> Linux Kerberos 凭据复用与横向移动</h3>
  <div class="cmd-box">
<span class="comment"># Linux 上的 Kerberos Ticket Cache</span><br>
<span class="key">KRB5CCNAME</span>=/tmp/krb5cc_1000  <span class="comment">← 默认路径格式</span><br>
ls -la /tmp/krb5cc_*           <span class="comment">← 查找所有用户的 ticket 文件</span><br>
klist -l                       <span class="comment">← 列出所有缓存的 TGT</span><br><br>
<span class="comment"># 攻击原理：直接复制 ccache 文件即可使用对应用户的凭据</span><br>
<span class="comment"># 无需知道密码——Pass-the-Cache 技术</span><br>
export KRB5CCNAME=/tmp/stolen_krb5cc_admin<br>
<span class="comment"># 之后即可以 admin 身份访问 Kerberos 服务</span><br><br>
<span class="comment"># Linux → Windows 横向移动（概念性流程）：</span><br>
<span class="comment"># 1. 获取 Linux 上的 TGT/TGS 文件</span><br>
<span class="comment"># 2. 使用 impacket 工具套件进行 AD 操作</span><br>
<span class="comment"># 3. SMB/WMI/DCOM 连接目标 Windows 系统</span><br><br>
<span class="comment"># 防御检测：</span><br>
<span class="comment"># 1. /tmp/krb5cc_* 文件权限应为 600（仅属主可读）</span><br>
ls -la /tmp/krb5cc_*  <span class="comment">← 检查是否有其他用户可读的 ccache</span><br>
<span class="comment"># 2. auditd 监控 ccache 文件的读取（尤其是跨用户）</span><br>
<span class="comment"># 3. 短 Ticket 生命期 + 定期轮换降低横向移动窗口</span>
  </div>
</div>

<!-- Step 5 -->
<div class="step-box">
  <h3 class="step-title"><span class="step-num">5</span> DevOps 基础设施横向移动路径分析</h3>
  <div class="cmd-box">
<span class="comment"># ===== Jenkins =====</span><br>
<span class="comment"># 高危：管理员控制台 Script Console 可直接执行 Groovy</span><br>
<span class="comment"># 路径：http://jenkins-host:8080/script</span><br>
<span class="comment"># 检测：Jenkins 审计日志中的 scriptText 调用</span><br>
<span class="comment"># 防御：启用矩阵授权，限制 Overall/Administer 权限</span><br><br>
<span class="comment"># ===== GitLab CI Runner =====</span><br>
<span class="comment"># Runner 注册 Token 泄露 → 注入恶意 .gitlab-ci.yml</span><br>
<span class="comment"># Runner 以低权限用户或 Docker 容器运行</span><br>
<span class="comment"># 检测：CI/CD pipeline 执行的命令日志审计</span><br>
<span class="comment"># 防御：Protected Branches/Tags 限制 pipeline 触发</span><br><br>
<span class="comment"># ===== Docker Socket 逃逸 =====</span><br>
<span class="comment"># /var/run/docker.sock 可写 = 容器逃逸到宿主机</span><br>
<span class="comment"># 检测：</span><br>
docker inspect --format='{{.HostConfig.Binds}}' $(docker ps -q)<br>
<span class="comment"># 查找挂载了 /var/run/docker.sock 或 / 的容器</span><br><br>
<span class="comment"># ===== Kubernetes 服务账户 =====</span><br>
<span class="comment"># 默认挂载路径：</span><br>
<span class="comment"># /run/secrets/kubernetes.io/serviceaccount/token</span><br>
<span class="comment"># 检测：审计使用 ServiceAccount Token 的 API 调用</span><br>
<span class="comment"># 防御：automountServiceAccountToken: false（非必要不挂载）</span>
  </div>
  <div class="def-box">
    🛡️ <strong>综合防御建议：</strong>① Jenkins/GitLab 启用 RBAC 最小权限；② Docker 运行时使用 rootless 模式；③ K8s 启用 Pod Security Admission（PSA）限制特权容器；④ 定期使用 kube-bench 扫描 K8s 配置合规性；⑤ 网络层面隔离 DevOps 工具的管理端口（不对外暴露 8080/6443 等）。
  </div>
</div>

<div class="flag-submit-area">
  <h4 style="font-weight:800;color:var(--text-primary);margin-top:0;">🚩 Flag 验证 — OSEP L17</h4>
  <div class="cmd-box" style="display:inline-block;padding:10px 24px;margin:0 auto 16px;">
    <span class="flag-text">flag{OSEP_L17_Linux_PostEx_SharedLib_DevOps_Kerberos}</span>
  </div>
  <form method="post" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
    <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width:460px;border-radius:8px;font-family:monospace;">
    <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius:8px;font-weight:700;background:#6366f1;border-color:#6366f1;">✔ 验证 Flag</button>
  </form>
  <?php if(!empty($flag_msg)){echo '<div style="margin-top:10px;">'.$flag_msg.'</div>';}?>
  <div style="margin-top:16px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
    <a href="osep_l16_kiosk_escape.php" class="btn btn-sm btn-default" style="border-radius:6px;">← L16</a>
    <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius:6px;">OSEP 大厅</a>
    <a href="osep_l18_ad_deep.php" class="btn btn-sm" style="border-radius:6px;background:#f59e0b;color:#000;border:none;font-weight:800;">🏆 L18 终章 →</a>
  </div>
</div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
