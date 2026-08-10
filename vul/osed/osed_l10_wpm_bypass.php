<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[281] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSED_L10_WriteProcessMemory_DEP_ASLR_Combined}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSED_flags']['flag10'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！理解并掌握了 WPM 结合 ROP 的高级漏洞利用与防护绕过原理。</div>';
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
        <h2 style="margin:0; font-size:24px; font-weight:700;">400 PTS · OSED Level 10 - WriteProcessMemory DEP 联合 ASLR 绕过</h2>
        <p style="margin:10px 0 0; opacity:0.9;">在严格的 DEP 环境中，探究如何借助系统级 API 实现代码注入，并结合 ASLR 泄露打造完整的执行链。</p>
    </div>

    <?php echo $flag_msg; ?>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> WriteProcessMemory API 原理</h3>
        <p><code>WriteProcessMemory</code> (WPM) 是 Windows 提供用于写入指定进程内存空间的 API。在漏洞利用中，它常常作为 <code>VirtualProtect</code> 方案的替代品。</p>
        <div class="highlight-box">
            <strong>函数签名：</strong><br>
            <code>BOOL WriteProcessMemory(HANDLE hProcess, LPVOID lpBaseAddress, LPCVOID lpBuffer, SIZE_T nSize, SIZE_T *lpNumberOfBytesWritten);</code><br><br>
            <strong>与 VirtualProtect 对比：</strong><br>
            VP 关注于修改已存在页面的执行权限以绕过 DEP。而 WPM 则是将攻击者的 Payload（Shellcode）直接强行写入到某个当前已具备“可读写+可执行”（或利用该进程特定上下文可写入的区段）属性的空间内，避免了页属性修改过程中的安全检测拦截。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> WPM ROP 链构造思路</h3>
        <p>在 DEP 启用的环境下，栈上的代码不可执行。为了使用 WPM 执行 Shellcode，必须巧妙构造 ROP 链。</p>
        <div class="cmd-box">
<span class="comment"># 典型 WPM ROP 阶段：</span>
<span class="comment">1. 寻址：找寻一段具备可执行属性（且可写，如特定段）的目标地址。</span>
<span class="comment">2. ROP 压栈构造：将 hProcess=-1 (伪句柄代表自身)，并准备其他 4 个参数。</span>
<span class="comment">3. 调用 WPM：</span>
   call WriteProcessMemory
<span class="comment">4. 跳转执行：利用 ROP 尾声将 EIP 转移至新写入的地址，完成 Shellcode 激活。</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> ASLR + DEP 联合防御挑战</h3>
        <p>在现代系统中，ASLR 与 DEP 共同构筑防线。即使你掌握了 WPM 技术，如果没有确定的地址，也是徒劳。</p>
        <p>攻击者首先需要找到突破口，例如利用格式化字符串漏洞或越界读取实现<strong>信息泄露</strong>，计算出基址。若应用中存在未开启 ASLR 的三方模块（non-ASLR 模块），将极大简化 ROP gadgets 的搜集工作。否则，必须谨慎规划每一步调试，确保泄露链不会导致进程过早崩溃或被 EDR 阻断。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> x64 应用的差异与挑战</h3>
        <p>当目标为 x64 架构时，情况变得更加复杂：</p>
        <div class="highlight-box">
            • <strong>调用约定改变：</strong> 必须使用 RCX, RDX, R8, R9 寄存器传递前 4 个参数，剩下的推入栈中。<br>
            • <strong>Shadow Space (影子空间)：</strong> Caller 必须为 Callee 预留至少 32 字节的空间（即影子栈区），这要求构造的 ROP 链在调用 API 前必须手动通过 <code>sub rsp, 20h</code> 平衡或留出这片区域，极大地增加了寻找可用 gadgets 的难度。<br>
            • <strong>堆栈 16 字节对齐：</strong> 在进入系统 API 之前，RSP 必须保证 16 字节对齐，否则将直接抛出异常终止。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 防御演进视角</h3>
        <p>尽管 WPM ROP 曾经威力巨大，但安全体系也在进化。</p>
        <p>• <strong>Intel CET (Control-flow Enforcement Technology)：</strong> 引入了硬件级影子栈。当 ROP 试图利用 <code>ret</code> 跳转到非正常的返回路径时，处理器会发现硬件栈与内存栈不匹配，直接触发中断，彻底阻断 ROP。<br>
        • <strong>CFG (Control Flow Guard)：</strong> 限制了间接跳转（如函数指针劫持）的目标地址范围。<br>
        依赖于简单的防御终将被深层次的系统架构加固所终结。</p>
    </div>

    <div class="flag-submit-area">
        <h4>提交 Flag 验证学习成果</h4>
        <form method="POST" class="form-inline" style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 flag{...}" style="width:300px;">
            <button type="submit" name="check_flag" class="btn btn-osed">验证</button>
        </form>
        <div class="nav-buttons">
            <a href="osed_l9_proto_reverse.php" class="btn btn-osed">&laquo; 上一关: 协议逆向</a>
            <a href="#" class="btn btn-osed" style="background:#475569; cursor:not-allowed;">模块完成，干得漂亮！</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
