<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[294] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L12_DotNet_ViewState_Cookie_Deser_RCE}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSWE_flags']['flag12'] = true;
        $flag_msg = '<div class="alert alert-success" style="background:#06b6d4;color:#fff;border-radius:8px;padding:15px;">✅ 通关！成功掌握.NET反序列化及ViewState机制利用！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="background:#f43f5e;color:#fff;border-radius:8px;padding:15px;">❌ Flag 错误，请检查您的利用链。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #06b6d4); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(6,182,212,0.3); }
.step-box { background:var(--bg-card, #1e293b); border:1px solid var(--border-color, #334155); border-radius:12px; padding:24px; margin-bottom:22px; color: #cbd5e1; }
.step-title { font-size:16px; font-weight:700; color:#06b6d4; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#0891b2); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#94a3b8; line-height:1.7; }
.flag-submit-area { background:var(--bg-card, #1e293b); border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    
    <div class="ctf-stage-header">
        <h2 style="margin:0 0 10px 0;font-size:24px;font-weight:700;">OSWE L12: .NET 反序列化与 ViewState 利用研究</h2>
        <p style="margin:0;opacity:0.9;">掌握 .NET 中的序列化机制、ViewState 结构解析，以及基于 machineKey 的签名伪造和利用链构造。</p>
        <div style="margin-top:15px;">
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;margin-right:10px;">350 PTS</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">.NET</span>
            <span style="background:rgba(255,255,255,0.2);padding:4px 10px;border-radius:4px;font-size:12px;">Deserialization</span>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> .NET 序列化基础与 Gadget</div>
        <p>在 .NET 框架中，存在多种序列化和反序列化器，如 <code>BinaryFormatter</code>, <code>XmlSerializer</code>, 和 <code>DataContractSerializer</code>。不同序列化器对类型处理的严格程度不同，其中 <code>BinaryFormatter</code> 和 <code>ObjectStateFormatter</code> 因为包含完整的类型信息（Type Name），最容易受到反序列化攻击。</p>
        <div class="highlight-box">
            <strong>核心原理：</strong><br>
            当反序列化器允许实例化任意类型，并且应用类路径中存在危险的类（即 Gadget 类），攻击者可以控制序列化数据中的类名和属性，触发这些类的 <code>OnDeserialized</code> 方法或特定接口方法，从而造成任意代码执行。
        </div>
        <div class="cmd-box">
<span class="comment">// 典型的危险反序列化代码 (C#)</span>
using System.Runtime.Serialization.Formatters.Binary;
using System.IO;

public void UnsafeDeserialize(byte[] data) {
    BinaryFormatter formatter = new BinaryFormatter();
    using (MemoryStream ms = new MemoryStream(data)) {
        <span class="comment">// 如果 data 可控，此处将触发 RCE</span>
        object obj = formatter.Deserialize(ms); 
    }
}
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> ViewState 序列化原理</div>
        <p>ASP.NET WebForms 广泛使用 ViewState 来维护页面状态。ViewState 数据通常经过 Base64 编码，并存储在 <code>__VIEWSTATE</code> 隐藏字段中。ViewState 的序列化主要依赖于 <code>ObjectStateFormatter</code> 或 <code>LosFormatter</code>（底层同样存在反序列化风险）。</p>
        <p>为了防止 ViewState 被篡改，ASP.NET 提供了 <code>EnableViewStateMac</code> 机制（在较新版本中默认强制开启）。开启后，ViewState 数据会使用服务器上的 <code>machineKey</code> 进行签名和/或加密。</p>
        <div class="cmd-box">
<span class="comment"># 抓包看到的 ViewState 参数示例</span>
__VIEWSTATE=/wEPDwUKLT... (Base64编码数据)
__VIEWSTATEGENERATOR=B97B4E27
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> ViewState 验证密钥泄露与利用</div>
        <p>如果由于配置不当导致 <code>machineKey</code> 泄露（例如存在目录遍历漏洞读取了 <code>web.config</code>，或者使用了弱的/默认的 <code>machineKey</code>），攻击者就可以使用 <code>ysoserial.net</code> 工具生成恶意的 ViewState 数据，并使用相同的 <code>machineKey</code> 进行签名，从而绕过 MAC 验证。</p>
        <div class="highlight-box">
            常见的利用链包括 <code>TypeConfuseDelegate</code> 和 <code>TextFormattingRunProperties</code>，这些 Gadget 可以实现在反序列化期间执行命令。
        </div>
        <div class="cmd-box">
<span class="comment"># 假设从 web.config 中获取到了 machineKey</span>
<span class="comment"># &lt;machineKey validationKey="32E...18" decryptionKey="3CB...DD" validation="SHA1" decryption="AES" /&gt;</span>

<span class="comment"># 使用 ysoserial.net 生成带有正确签名的恶意的 ViewState</span>
ysoserial.exe -p ViewState -g TypeConfuseDelegate -c "calc.exe" --generator=B97B4E27 \
--validationalg="SHA1" --validationkey="32E...18"
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> Cookie 和 Session 反序列化调试</div>
        <p>在大型 .NET 应用（如 DotNetNuke）中，不仅 ViewState 存在反序列化风险，某些业务逻辑可能会直接从 Cookie 或 GET 请求参数中反序列化复杂对象。</p>
        <p>研究此类漏洞时，通常使用 <strong>dnSpy</strong> 结合 Visual Studio 附加到 IIS 进程（<code>w3wp.exe</code>）进行动态调试。通过在 <code>BinaryFormatter.Deserialize</code> 或自定义序列化代理上设置断点，跟踪数据流。</p>
        <div class="cmd-box">
<span class="comment"># 模拟反序列化驱动的 GET 请求攻击</span>
GET /api/userProfile?data=AAEAAAD..... HTTP/1.1
Host: target.local
Cookie: DotNetNukePersonalization=&lt;Malicious_Serialized_Object&gt;

<span class="comment"># 调试时寻找的调用堆栈：</span>
<span class="comment"># -> System.Runtime.Serialization.Formatters.Binary.BinaryFormatter.Deserialize()</span>
<span class="comment"># -> DotNetNuke.Services.Personalization.PersonalizationController.LoadProfile()</span>
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御方法与修复建议</div>
        <p>防范 .NET 反序列化的核心原则是：<strong>永远不要反序列化不可信的数据</strong>。</p>
        <ul>
            <li><strong>弃用不安全的序列化器：</strong> 在 .NET 5+ 中，<code>BinaryFormatter</code> 已被标记为过时且默认禁用。应迁移到 <code>System.Text.Json</code> 等安全的序列化工具。</li>
            <li><strong>强制类型白名单：</strong> 如果必须使用 <code>BinaryFormatter</code>，请实现自定义的 <code>SerializationBinder</code>，严格限制允许反序列化的类型。</li>
            <li><strong>保护 machineKey：</strong> 确保 <code>machineKey</code> 是随机生成的并且得到妥善保护，避免硬编码或配置泄露。不同应用应使用独立的 <code>machineKey</code>。</li>
            <li><strong>启用静态扫描：</strong> 使用 SCA 工具扫描代码，防止引入已知的反序列化 Gadget 库。</li>
        </ul>
        <div class="cmd-box">
<span class="flag-text">Flag获取:</span> flag{OSWE_L12_DotNet_ViewState_Cookie_Deser_RCE}
        </div>
    </div>

    <div class="flag-submit-area">
        <?=$flag_msg?>
        <form method="POST" action="">
            <input type="text" name="user_flag" placeholder="输入 Flag: flag{...}" style="width:300px;padding:10px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#fff;">
            <button type="submit" name="check_flag" style="background:#06b6d4;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:bold;">提交 Flag</button>
        </form>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
