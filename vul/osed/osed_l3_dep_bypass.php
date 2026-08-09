<?php
/**
 * OSED L3: DEP/NX 防御机制与 ROP 原理 (200 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[274] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L3_DEP_NX_ROP_Gadget_Chain}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag3'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSED L3】DEP/ROP 机制已掌握 (+200 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0a0f0a 0%, #0f1a0f 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(251,146,60,0.3); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #16a34a, #f97316); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(22,163,74,0.08); border: 1px solid rgba(22,163,74,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.dep-diagram { background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 18px; font-family: monospace; font-size: 12px; color: #94a3b8; margin: 12px 0; line-height: 1.9; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🛡️ OSED L3：DEP/NX 防御机制与 ROP 原理研究
            <span style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 3px 10px; border-radius: 12px; font-size: 12px;">中级 · 200 PTS</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">从硬件与操作系统层面理解 DEP（数据执行保护）的工作原理，研究 ROP（Return-Oriented Programming）的理论基础，以及 CFG/CET 如何从根本上阻断 ROP 攻击。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 防御：DEP · NX bit · CFG · CET 影子栈 · VirtualProtect 检测</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> DEP/NX 工作机制：硬件级防御</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">DEP（Data Execution Prevention）利用 CPU 的 NX（No-eXecute）位标记内存页属性：</p>
        <div class="dep-diagram">
x86/x64 内存页表项（PTE）中的 NX 位：<br><br>
物理地址 [63:12] | 各种标志位 | [0] Present<br>
                              ↑<br>
                        bit[63] = NX 位<br>
                        NX=1 → 该内存页不可执行<br>
                        NX=0 → 可读/写/执行<br><br>
Windows 内存区域 NX 属性：<br>
┌─────────────────────────────────────────────────────────┐<br>
│ 区域              │ 默认 NX │ 说明                      │<br>
├─────────────────────────────────────────────────────────┤<br>
│ .text (代码段)    │  N (可执行) │ 程序代码                │<br>
│ .data (数据段)    │  Y (不可执行) │ 全局/静态变量         │<br>
│ 栈 (Stack)        │  Y (不可执行) │ 局部变量/返回地址     │<br>
│ 堆 (Heap)         │  Y (不可执行) │ 动态内存分配          │<br>
│ 系统 DLL .text    │  N (可执行) │ ntdll, kernel32 等     │<br>
└─────────────────────────────────────────────────────────┘<br><br>
DEP 效果：即使攻击者将 Shellcode 注入栈/堆，CPU 拒绝执行，触发 STATUS_ACCESS_VIOLATION
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> ROP (Return-Oriented Programming) 理论基础</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
            ROP 是绕过 DEP 的技术理论：不注入新的 Shellcode，而是将已有代码片段（Gadgets）串联起来执行攻击逻辑：
        </p>
        <div class="cmd-box">
<span class="comment"># ROP Gadget 概念：以 RET 结尾的代码片段</span><br>
<span class="comment"># 在已加载的 DLL 中大量存在，且地址已知（如不启用 ASLR 的情况下）</span><br><br>
<span class="comment"># 典型 Gadget 类型：</span><br>
<span class="comment"># POP EAX; RET          → 控制 EAX 寄存器值</span><br>
<span class="comment"># MOV [ESP], EAX; RET   → 写内存</span><br>
<span class="comment"># ADD ESP, 8; RET       → 调整栈指针</span><br>
<span class="comment"># PUSH ESP; POP EAX; RET → 获取当前 ESP 值到 EAX</span><br><br>
<span class="comment"># ROP 链绕过 DEP 的常见目标：</span><br>
<span class="comment"># 方法 1: 调用 VirtualProtect() → 将 Shellcode 所在内存页设为可执行</span><br>
<span class="comment"># 方法 2: 调用 VirtualAlloc() → 申请新的 RWX 内存区域</span><br>
<span class="comment"># 方法 3: 调用 WriteProcessMemory() → 写入代码段（已可执行的区域）</span><br><br>
<span class="comment"># ROPgadget 工具搜索示例：</span><br>
ROPgadget --binary target.exe --rop --nojop<br>
ROPgadget --binary kernel32.dll --string "VirtualProtect"
        </div>
        <div class="highlight-box">
            ⚠️ <strong>重要理解：</strong>ROP 不执行任何"新代码"，它只是重排了程序已有的合法代码片段的执行顺序。这就是为什么 DEP 无法阻止 ROP——所有 Gadget 都在合法的可执行内存中。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> CFG/CET：现代防御机制如何根本阻断 ROP</h3>
        <div class="cmd-box">
<span class="comment"># CFG (Control Flow Guard) - Windows 8.1+</span><br>
<span class="comment"># 编译时：MSVC 生成一个位图，记录所有合法的间接调用目标</span><br>
<span class="comment"># 运行时：每次间接调用（call [eax]）前插入：</span><br>
<span class="comment">#   call ntdll!_guard_check_icall_nop → 验证目标地址是否在白名单中</span><br>
<span class="comment"># 效果：ROP 链中的 Gadget 不在 CFG 白名单中 → 访问违例 → 进程终止</span><br><br>
<span class="comment"># CET (Control-flow Enforcement Technology) - Intel Tiger Lake + Win11</span><br>
<span class="comment"># 影子栈（Shadow Stack）工作原理：</span><br>
<span class="comment"># CALL 指令执行时：</span><br>
<span class="comment">#   1. 返回地址压入普通栈（ESP）</span><br>
<span class="comment">#   2. 同时压入影子栈（SSP，Shadow Stack Pointer）</span><br>
<span class="comment"># RET 指令执行时：</span><br>
<span class="comment">#   1. 从普通栈弹出返回地址</span><br>
<span class="comment">#   2. 从影子栈弹出返回地址</span><br>
<span class="comment">#   3. 比较两者是否一致 → 不一致 → #CP 异常 → 进程终止</span><br><br>
<span class="comment"># CET 效果：ROP 链需要覆盖普通栈上的返回地址，</span><br>
<span class="comment"># 但影子栈中的副本未被修改 → 检测到 ROP → 终止</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED L3</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L3_DEP_NX_ROP_Gadget_Chain}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_l2_seh.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="osed_l4_aslr.php" class="btn btn-sm" style="border-radius: 6px; background: #f97316; color: #fff; border: none; font-weight: 700;">下一关：ASLR 机制 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
