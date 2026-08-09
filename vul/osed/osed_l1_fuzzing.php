<?php
/**
 * OSED L1: 模糊测试原理与崩溃分析 (100 PTS)
 * 对标 OSED EXP-301 Module 1: Fuzzing & Crash Analysis
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[272] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSED_L1_Fuzzing_Crash_Analysis_EIP}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag1'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSED L1】模糊测试分析已掌握 (+100 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
$quiz_result = '';
if (isset($_POST['submit_quiz'])) {
    $ans = strtolower(trim($_POST['quiz_answer']));
    if (strpos($ans, '4') !== false || strpos($ans, 'eip') !== false) {
        $quiz_result = 'correct';
    } else {
        $quiz_result = 'wrong';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0a0f1e 0%, #1c1206 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(251,146,60,0.3); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #f97316, #ef4444); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(251,146,60,0.08); border: 1px solid rgba(251,146,60,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
.memory-diagram { background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 18px; font-family: monospace; font-size: 12px; color: #94a3b8; margin: 12px 0; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            💥 OSED L1：模糊测试原理与崩溃分析
            <span style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 12px; font-size: 12px;">入门 · 100 PTS</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">学习 Fuzzing 模糊测试的系统方法论：从测试用例生成，到崩溃捕获，再到 EIP 偏移精确定位——理解漏洞分析的第一步。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 工具：WinDbg · Immunity Debugger · mona.py · Spike · Boofuzz</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 栈内存布局：理解溢出的根本</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">在理解缓冲区溢出之前，先理解 x86 Windows 的函数调用栈帧布局：</p>
        <div class="memory-diagram">
高地址 ↑<br>
┌──────────────────────────────┐<br>
│  调用者的参数 (Caller args)    │<br>
├──────────────────────────────┤<br>
│  返回地址 (Return Address)    │ ← EIP 溢出目标：覆盖这里！<br>
├──────────────────────────────┤<br>
│  保存的 EBP (Saved EBP)      │ ← EBP+4 = 返回地址<br>
├──────────────────────────────┤<br>
│  Stack Canary (GS保护时存在)  │ ← GS 编译选项插入的随机值<br>
├──────────────────────────────┤<br>
│  局部变量 (Local Variables)   │<br>
│  ├── char buf[512]            │ ← 用户输入缓冲区<br>
│  ├── int count                │<br>
│  └── ...                      │<br>
└──────────────────────────────┘<br>
低地址 ↓  (栈向低地址增长)<br><br>
💡 溢出原理：buf[512] 接收超长输入 → 覆盖 SavedEBP → 覆盖 ReturnAddr(EIP)
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Fuzzing 工作流：从测试用例到崩溃</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">Fuzzing 是通过向程序发送异常、随机或边界输入来发现崩溃的自动化技术：</p>
        <div class="cmd-box">
<span class="comment"># ==== 阶段 1：基础 Fuzzing 测试用例生成 ====</span><br>
<span class="comment"># Python 简单 Spike 式 Fuzzer（教学理解用）</span><br>
<span class="cmd">buffer = b"A" * 100  # 从100字节开始</span><br>
<span class="cmd">while len(buffer) <= 10000:</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">send_to_target(buffer)  # 发送到目标服务</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">if check_crash():       # 检测是否崩溃</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">print(f"Crash at {len(buffer)} bytes!")</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">break</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">buffer += b"A" * 100   # 每次增加100字节</span><br><br>
<span class="comment"># ==== 阶段 2：精确偏移定位（Metasploit 工具）====</span><br>
<span class="comment"># 生成唯一循环模式（每个4字节子串都唯一）</span><br>
<span class="cmd">/usr/share/metasploit-framework/tools/exploit/pattern_create.rb -l 3000</span><br>
<span class="comment"># 发送此模式 → 程序崩溃 → WinDbg 记录 EIP 值</span><br><br>
<span class="comment"># 根据 EIP 值计算偏移量</span><br>
<span class="cmd">/usr/share/metasploit-framework/tools/exploit/pattern_offset.rb -q 0x41396341 -l 3000</span><br>
<span class="comment"># 输出：[*] Exact match at offset 2003</span>
        </div>
        <div class="highlight-box">
            🔢 <strong>核心概念：EIP 控制</strong><br>
            当模糊测试导致 EIP（指令指针）被填充数据覆盖时，说明找到了可控制的缓冲区溢出漏洞。精确偏移量意味着你可以在正确位置放入任意地址，控制程序执行流。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Immunity Debugger + mona.py：崩溃分析工具链</h3>
        <div class="cmd-box">
<span class="comment"># mona.py 常用命令（在 Immunity Debugger 命令行中执行）</span><br><br>
<span class="comment"># 1. 生成唯一模式（代替 Metasploit pattern_create）</span><br>
<span class="cmd">!mona pattern_create 3000</span><br><br>
<span class="comment"># 2. 计算偏移量（崩溃后执行）</span><br>
<span class="cmd">!mona pattern_offset -q 0x41396341</span><br><br>
<span class="comment"># 3. 查找坏字符（Bad Characters）</span><br>
<span class="cmd">!mona bytearray -b "\x00"</span>  <span class="comment"># 生成除 \x00 外的所有字符</span><br>
<span class="comment"># 发送后对比内存中的 bytearray 与期望值，找出被过滤的字节</span><br><br>
<span class="comment"># 4. 查找 JMP ESP 或其他跳转指令地址</span><br>
<span class="cmd">!mona jmp -r esp -cpb "\x00\x0a\x0d"</span>  <span class="comment"># 排除坏字符</span><br><br>
<span class="comment"># 5. 查找模块安全特性（是否启用 ASLR/DEP/SafeSEH）</span><br>
<span class="cmd">!mona modules</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 互动测验：栈溢出基础</h3>
        <p style="font-size: 14px; color: var(--text-secondary);">一个 x86 Windows 程序中，函数栈帧的返回地址与局部变量 buf[256] 起始地址之间距离为 260 字节。要精确覆盖返回地址，需要在 buf 开头填充多少字节的填充数据（再紧跟4字节的新返回地址）？</p>
        <form method="post">
            <input type="text" name="quiz_answer" class="form-control" placeholder="输入数字（字节数）" style="max-width: 220px; border-radius: 6px; display: inline-block;">
            <button type="submit" name="submit_quiz" class="btn btn-sm" style="background: #f97316; color: #fff; border: none; border-radius: 6px; margin-left: 8px; font-weight: 700;">验证</button>
        </form>
        <?php if ($quiz_result === 'correct'): ?>
            <div style="margin-top: 10px; color: #34d399; font-size: 13px; font-weight: 700;">✅ 正确！偏移为 260 字节（buf[256] + SavedEBP 4字节），紧接 4 字节覆盖返回地址（EIP）。</div>
        <?php elseif ($quiz_result === 'wrong'): ?>
            <div style="margin-top: 10px; color: #f87171; font-size: 13px;">❌ 不正确。提示：buf[256] 占 256 字节，再加上保存的 EBP（4字节），共需 260 字节填充才能到达返回地址。</div>
        <?php endif; ?>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED L1</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            掌握 Fuzzing 工作流、EIP 偏移计算、mona.py 崩溃分析工具链后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L1_Fuzzing_Crash_Analysis_EIP}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="osed_l2_seh.php" class="btn btn-sm" style="border-radius: 6px; background: #f97316; color: #fff; border: none; font-weight: 700;">下一关：SEH 覆盖 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
