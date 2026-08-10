<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[279] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSED_L8_FormatStr_Read_Write_EIP_Control}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSED_flags']['flag8'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！成功掌握格式化字符串漏洞的深度利用。</div>';
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
        <h2 style="margin:0; font-size:24px; font-weight:700;">400 PTS · OSED Level 8 - 格式化字符串漏洞利用</h2>
        <p style="margin:10px 0 0; opacity:0.9;">掌握使用格式化字符串漏洞进行任意内存读写，以及绕过 ASLR 实现 EIP 控制的技术。</p>
    </div>

    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 格式化字符串漏洞识别</h3>
        <p>格式化字符串漏洞往往发生在开发者直接将不可信的用户输入作为 `printf` 系列函数的格式化参数时。</p>
        <div class="highlight-box">
            <strong>脆弱代码：</strong> <code>printf(user_input);</code><br>
            <strong>安全代码：</strong> <code>printf("%s", user_input);</code><br><br>
            <strong>漏洞特征：</strong> 当应用直接回显用户输入的 <code>%x</code>、<code>%p</code> 时输出大量栈内存地址，这便是典型的格式化字符串漏洞。<code>%x</code> 和 <code>%p</code> 可用于泄露栈内容，而 <code>%n</code> 则能将已输出的字符总数写入由参数指针指定的内存中。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 格式化字符串读原语</h3>
        <p>读原语允许攻击者探查进程内存空间。可以通过 <code>%X$p</code> 语法直接访问位于堆栈上的第 X 个参数。通常需要通过二分法或递进探测，找到我们在输入缓冲区中放置的值（例如 <code>AAAA</code> -> <code>0x41414141</code>）在栈上的确切偏移量。</p>
        <div class="cmd-box">
<span class="comment"># 探测载荷：寻找 41414141 在堆栈上的第几个位置</span>
AAAA%1$p-%2$p-%3$p-%4$p...
<span class="comment"># 利用泄露的栈地址计算模块基址偏移：</span>
<span class="comment">1. 泄露某个已知函数的栈上返回地址</span>
<span class="comment">2. Module_Base = Leaked_Address - Fixed_Offset</span>
        </div>
        <p>通过泄露的地址来计算动态链接库或主模块的基址，可以成功绕过 ASLR，为后续的 ROP 链构造提供精确地址。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 格式化字符串写原语</h3>
        <p>写原语则是通过 <code>%n</code> 或其变体（如 <code>%hn</code> 写半字，<code>%hhn</code> 写单字节）向目标内存地址写入数据。利用前面计算的基址，我们可以选择重写特定的函数指针以劫持 EIP。</p>
        <div class="highlight-box">
            <strong>写入目标选择：</strong><br>
            • GOT (Global Offset Table) 表项<br>
            • 栈上的函数返回地址<br>
            • SEH (Structured Exception Handler) 异常处理回调<br><br>
            为了提高稳定性，避免一次性输出巨大数量的空格，通常将一个 32 位地址拆分成四个单字节写入（<code>%hhn</code>）。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> Tivoli 类复杂应用分析</h3>
        <p>在面对如 IBM Tivoli 此类复杂网络服务时，漏洞往往不直观。需要对协议进行深度逆向分析以定位格式化字符串交互端点。</p>
        <p>由于应用层可能限制了输入的直接回显，我们需要另辟蹊径，例如读取应用的事件日志（Event Log）或错误回显来捕获 <code>printf</code> 执行后的泄露数据，从而辅助 ASLR 的绕过。这要求设计完整的利用链条：先盲注或半盲注触发读泄露，随后构造精确载荷触发写劫持。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 防御机制与应对</h3>
        <p>随着安全标准的提升，格式化字符串漏洞在现代应用中已大幅减少。现代编译器引入了多种缓解措施。</p>
        <div class="highlight-box">
            • <strong>FORTIFY_SOURCE：</strong> GCC/Clang 的编译选项，替换不安全的调用。在 Windows 上对应 MSVC 的安全函数（如 <code>_printf_s</code>），强制要求格式化字符串不可写入（存储在只读数据段）。<br>
            • <strong>静态代码分析：</strong> 现代 IDE 与代码审计工具可以在编译阶段直接标记非静态的格式化参数警告。<br>
            • <strong>输入验证：</strong> 过滤包含 <code>%</code> 及特定说明符（如 <code>%n</code>）的输入。
        </div>
    </div>

    <div class="flag-submit-area">
        <h4>提交 Flag 验证学习成果</h4>
        <form method="POST" class="form-inline" style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 flag{...}" style="width:300px;">
            <button type="submit" name="check_flag" class="btn btn-osed">验证</button>
        </form>
        <div class="nav-buttons">
            <a href="osed_l7_asm_shellcode.php" class="btn btn-osed">&laquo; 上一关: x86 Shellcode</a>
            <a href="osed_l9_proto_reverse.php" class="btn btn-osed">下一关: 协议逆向 &raquo;</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
