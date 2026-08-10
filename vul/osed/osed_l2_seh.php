<?php
/**
 * OSED L2: SEH 异常处理器覆盖机制 (150 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[273] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L2_SEH_Overflow_nSEH_Handler}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag2'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSED L2】SEH 机制已掌握 (+150 PTS)！</div>';
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
.step-num { background: linear-gradient(135deg, #f97316, #ef4444); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(251,146,60,0.08); border: 1px solid rgba(251,146,60,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.memory-diagram { background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 18px; font-family: monospace; font-size: 12px; color: #94a3b8; margin: 12px 0; line-height: 1.9; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🔗 OSED L2：SEH 异常处理器覆盖机制
            <span style="background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid #3b82f6; padding: 3px 10px; border-radius: 12px; font-size: 12px;">初级 · 150 PTS</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">深入理解 Windows SEH（结构化异常处理）链表结构、nSEH/Handler 字段布局，以及 SafeSEH 编译选项如何从根本上阻断 SEH 覆盖攻击。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 防御：SafeSEH · SEHOP · Exception Handler 白名单</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> SEH 链表结构：Windows 异常处理框架</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">Windows 使用链表结构管理异常处理程序（Exception Handler），每个 __try/__except 块在栈上注册一个 _EXCEPTION_REGISTRATION_RECORD：</p>
        <div class="memory-diagram">
TEB.ExceptionList → SEH_Record_1 → SEH_Record_2 → 0xFFFFFFFF (末尾)<br><br>
SEH Record 结构（_EXCEPTION_REGISTRATION_RECORD）：<br>
┌─────────────────────────────────────┐<br>
│ +0x00  nSEH (Next SEH Record)       │ ← 指向链表中下一个 SEH 记录<br>
│ +0x04  Handler (Exception Handler)  │ ← 异常发生时调用此函数地址<br>
└─────────────────────────────────────┘<br><br>
SEH 覆盖攻击原理：<br>
缓冲区溢出 → 覆盖栈上的 SEH Record<br>
├── nSEH = \xeb\x06\x90\x90  (短跳转指令，跳过 Handler 字段)<br>
└── Handler = POP POP RET 地址  (执行后返回到 nSEH，触发短跳转)<br>
         ↓<br>
跳转到 nSEH 之后 → Shellcode
        </div>
        <div class="highlight-box">
            🔍 <strong>SEH 覆盖攻击的精妙之处：</strong>Handler 字段被 POP POP RET 地址覆盖后，当异常被触发（如访问违例），操作系统调用 Handler（实际上是 PPR 序列）→ PPR 执行后 RET 返回到调用者，而 ESP 此时正好指向 nSEH 字段 → 执行 nSEH 中的短跳转 → 跳到 Shellcode。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> SafeSEH：编译时防护机制</h3>
        <div class="cmd-box">
<span class="comment"># SafeSEH 工作原理（/SAFESEH 链接器选项）</span><br>
<span class="comment"># 1. 编译时，链接器在 PE 文件头写入合法 Exception Handler 白名单</span><br>
<span class="comment"># 2. 运行时，OS 异常分发器（RtlDispatchException）在调用 Handler 前：</span><br>
<span class="comment">#    a. 验证 Handler 地址是否在当前模块的 SEH 白名单中</span><br>
<span class="comment">#    b. 验证 SEH Record 是否在有效的栈地址范围内</span><br>
<span class="comment">#    c. 如果不在白名单中 → 触发进程终止</span><br><br>
<span class="comment"># 使用 mona.py 查找无 SafeSEH 保护的模块（POP POP RET 跳板）</span><br>
<span class="cmd">!mona seh -cpb "\x00\x0a\x0d"  </span><span class="comment"># 查找安全的 PPR 地址</span><br><br>
<span class="comment"># mona modules 输出字段说明：</span><br>
<span class="comment"># Rebase ASLR SafeSEH SEHOP NXCompat OS DLL</span><br>
<span class="comment"># False  False False   False  False    False  module.dll</span><br>
<span class="comment"># ↑ 这样的模块才能被用作 SEH 覆盖的跳板</span><br><br>
<span class="comment"># SEHOP（SEH Overwrite Protection，Windows Vista+）</span><br>
<span class="comment"># 在分发异常前遍历整个 SEH 链，验证链表末尾指向 ntdll!FinalExceptionHandler</span><br>
<span class="comment"># 如果链表被破坏（覆盖了 nSEH），SEHOP 直接终止进程</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> WinDbg 崩溃分析：SEH 相关命令</h3>
        <div class="cmd-box">
<span class="comment"># WinDbg 中 SEH 分析常用命令</span><br><br>
<span class="comment"># 显示当前 SEH 链</span><br>
<span class="cmd">.exchain</span><br>
<span class="comment"># 输出示例：</span><br>
<span class="comment"># 0019f334: ntdll!ExecuteHandler2+0x26</span><br>
<span class="comment"># 0019f6b0: CRASHME!_except_handler3+0x61</span><br>
<span class="comment"># 0019ff70: ntdll!FinalExceptionHandler</span><br><br>
<span class="comment"># 查看 SEH 链被覆盖的状态（g + 触发异常后）</span><br>
<span class="cmd">!exchain</span><br><br>
<span class="comment"># 查看 ESP 指向的内存（PPR 执行后）</span><br>
<span class="cmd">dd esp L4</span><br><br>
<span class="comment"># 检查特定内存区域内容</span><br>
<span class="cmd">db 0019f334 L20</span>
        </div>
        <div class="highlight-box">
            📌 <strong>防御建议：</strong>所有新开发的 Windows 应用程序应使用 <code>/SAFESEH</code> 链接选项和 <code>/GS</code> 编译选项（Stack Canary）。Windows Defender Exploit Protection 可对未开启 SafeSEH 的第三方软件额外启用 SEHOP 保护。
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED L2</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L2_SEH_Overflow_nSEH_Handler}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_l1_fuzzing.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="osed_l3_dep_bypass.php" class="btn btn-sm" style="border-radius: 6px; background: #f97316; color: #fff; border: none; font-weight: 700;">下一关：DEP/NX 机制 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
