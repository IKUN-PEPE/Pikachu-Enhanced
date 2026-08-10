<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[280] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSED_L9_Protocol_Reverse_IDA_WinDbg_Vuln}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSED_flags']['flag9'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！掌握逆向分析应用层协议及漏洞挖掘技术。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #f97316); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(249,115,22,0.3); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(249,115,22,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-osed { background: #f97316; color: white; border: none; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; }
.btn-osed:hover { background: #ea580c; color: white; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    
    <div class="ctf-stage-header">
        <h2 style="margin:0; font-size:24px; font-weight:700;">350 PTS · OSED Level 9 - 应用层协议逆向分析</h2>
        <p style="margin:10px 0 0; opacity:0.9;">结合 IDA Pro 与 WinDbg，深度剖析未知网络协议，定位解析逻辑中的安全隐患。</p>
    </div>

    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 静态分析工作流</h3>
        <p>未知应用的攻击面首先位于其与外部交互的网络接口。在 IDA Pro 中的标准起始流程如下：</p>
        <div class="highlight-box">
            1. 加载目标 DLL/EXE 文件，等待初始反汇编与自动分析完成。<br>
            2. 使用 <strong>Strings 窗口</strong> (Shift+F12) 搜寻可疑的协议指示字符串（例如 "HELO", "Auth-Token:", "%s:%d" 等）。<br>
            3. 浏览 <strong>Imports 表</strong>，定位网络操作函数如 <code>recv</code>、<code>recvfrom</code>、<code>WSARecv</code>。<br>
            4. 通过<strong>交叉引用</strong> (Xrefs, 快捷键 X) 逆向追溯，寻找调用 <code>recv</code> 后处理接收缓冲区的核心协议解析函数。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 动态调试追踪协议</h3>
        <p>静态分析难以理清复杂的指针运算与多层回调，因此需要依靠 WinDbg 动态捕获网络数据流向。</p>
        <div class="cmd-box">
<span class="comment"># 在 recv 函数下断点</span>
bp ws2_32!recv
<span class="comment"># 命中后，查看传入的参数（缓冲区地址）</span>
<span class="comment"># 配合内存访问硬件断点追踪何处处理了该数据</span>
ba r1 <buf_address>
<span class="comment"># 在循环处理时，紧盯长度寄存器（如 RDX/R8）</span>
g
        </div>
        <p>通过单步往返跟踪，记录解析分支条件，从而识别出报文长度字段的位置及报文标志位的作用机制。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Wireshark 实际流量与逆向互验</h3>
        <p>在获得了逆向推测出的协议结构后，使用 Wireshark 抓取真实流量进行验证。为了自动化与更直观的分析，开发一个 Lua Dissector 插件是极为高效的途径。</p>
        <div class="highlight-box">
            编写自定义 Wireshark Lua 插件可以实时高亮自定义协议的：<br>
            • <strong>Header Length：</strong> 验证长度计算逻辑。<br>
            • <strong>Flags：</strong> 解析位掩码对应的功能。<br>
            • <strong>Command Types：</strong> 分发至不同漏洞处理模块的依据。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 漏洞挖掘方法论</h3>
        <p>梳理出协议结构后，即刻开启针对性的漏洞挖掘。相比无脑模糊测试，协议感知型的测试（Smart Fuzzing）威力倍增：</p>
        <p>• <strong>长度字段 Fuzzing：</strong> 故意提供超越缓冲区大小的声明长度，检查整数溢出和堆/栈溢出漏洞。<br>
        • <strong>标志位边界测试：</strong> 提供非常规的标志位组合，诱导状态机进入未处理边界异常。<br>
        • <strong>深层路径触发：</strong> 基于逆向确定的通信条件，构造合法包皮但恶意负荷的数据，进入深埋的“未被验证”的秘密处理逻辑。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 协议层安全防御</h3>
        <p>确保协议安全的根本方法是从协议设计与编码实践入手。</p>
        <div class="highlight-box">
            <strong>加固措施：</strong><br>
            • <strong>TLS 包裹：</strong> 在最外层使用成熟的 TLS 层进行加密认证，防止流量被轻易伪造或中间人篡改。<br>
            • <strong>边界检查与验证：</strong> 严格校验每个长度字段的上限，并使用无符号整型对比或检查整数反转。<br>
            • <strong>状态机强同步：</strong> 不允许跳跃式的协议流转，杜绝非法状态引发的逻辑漏洞。
        </div>
    </div>

    <div class="flag-submit-area">
        <h4>提交 Flag 验证学习成果</h4>
        <form method="POST" class="form-inline" style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 flag{...}" style="width:300px;">
            <button type="submit" name="check_flag" class="btn btn-osed">验证</button>
        </form>
        <div class="nav-buttons">
            <a href="osed_l8_format_string.php" class="btn btn-osed">&laquo; 上一关: 格式化字符串</a>
            <a href="osed_l10_wpm_bypass.php" class="btn btn-osed">下一关: WPM 绕过 &raquo;</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
