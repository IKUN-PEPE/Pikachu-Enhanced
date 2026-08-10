<?php
/**
 * OSED L5: Egghunter 技术原理研究 (300 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[276] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L5_Egghunter_WoW64_TEB}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag5'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSED L5】Egghunter 已掌握 (+300 PTS)！</div>';
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
.step-num { background: linear-gradient(135deg, #d97706, #f97316); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(217,119,6,0.08); border: 1px solid rgba(217,119,6,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🥚 OSED L5：Egghunter 技术原理研究
            <span style="background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 12px;">高级 · 300 PTS</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">研究 Egghunter 技术的工作原理：通过 NtAccessCheckAndAuditAlarm 系统调用安全搜索进程地址空间，在内存碎片化场景下定位 Shellcode 的机制。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 概念：Egghunter · NtAccessCheck · TEB · WoW64 · 地址搜索</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> Egghunter 产生背景：小缓冲区问题</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
            当溢出缓冲区太小，无法容纳完整 Shellcode（如只有 50-100 字节），但应用程序在内存的其他位置仍持有用户输入数据时，Egghunter 提供了一种优雅的解决方案：
        </p>
        <div class="cmd-box">
<span class="comment"># 场景：</span><br>
<span class="comment"># 溢出点缓冲区：只有 60 字节可用空间</span><br>
<span class="comment"># 之前的 HTTP 请求头（User-Agent）仍在内存中：有充足空间</span><br><br>
<span class="comment"># 解决方案：</span><br>
<span class="comment"># 1. 在大缓冲区（User-Agent）中放：Egg 标记 + 真实 Shellcode</span><br>
<span class="comment">#    数据结构：\x57\x30\x30\x54\x57\x30\x30\x54 + [Shellcode]</span><br>
<span class="comment">#                ↑ "W00T" × 2 (唯一 Egg 标记，重复以降低误报)</span><br><br>
<span class="comment"># 2. 在小缓冲区（溢出点）放：~30 字节 Egghunter 代码</span><br>
<span class="comment">#    Egghunter 负责搜索进程内存，找到 Egg 标记后跳转执行</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Egghunter 工作原理：系统调用安全搜索</h3>
        <div class="cmd-box">
<span class="comment"># Egghunter 工作流程（理论分析）：</span><br><br>
<span class="comment"># 关键问题：如何遍历进程内存而不因访问无效页面而崩溃？</span><br>
<span class="comment"># 答案：利用系统调用的参数验证机制！</span><br><br>
<span class="comment"># Egghunter 方法 1：NtAccessCheckAndAuditAlarm (x86 syscall #0xED)</span><br>
<span class="comment"># 系统调用会在进入内核前验证用户态指针的可读性</span><br>
<span class="comment"># 如果地址无效 → 返回 STATUS_ACCESS_VIOLATION (0xC0000005)</span><br>
<span class="comment"># 如果地址有效 → 返回其他错误码（参数无效等）</span><br><br>
<span class="comment"># 搜索算法伪代码：</span><br>
for addr in range(0x0, 0x7FFFFFFF, 0x1000):  # 每次跳 4KB（内存页大小）<br>
&nbsp;&nbsp;if NtAccessCheckAndAuditAlarm(addr) != 0xC0000005:<br>
&nbsp;&nbsp;&nbsp;&nbsp;# 内存页有效，逐字节搜索 Egg 标记<br>
&nbsp;&nbsp;&nbsp;&nbsp;for offset in range(0, 4096):<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if mem[addr+offset:addr+offset+8] == b'\x57\x30\x30\x54' * 2:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;jmp addr+offset+8  # 跳转到 Egg 后面的 Shellcode<br><br>
<span class="comment"># Egghunter 方法 2：基于 SEH</span><br>
<span class="comment"># 注册临时 SEH Handler → 尝试读内存 → 如果异常 → Handler 捕获 → 继续搜索</span>
        </div>
        <div class="highlight-box">
            🔬 <strong>研究意义：</strong>Egghunter 是利用操作系统系统调用的"副作用"（指针验证）来安全遍历内存的创新设计。它展示了在有限空间约束下，如何通过理解操作系统内部机制找到突破口。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> WoW64 与 TEB：64 位进程中的 Egghunter</h3>
        <div class="cmd-box">
<span class="comment"># WoW64（Windows on Windows 64-bit）：在 x64 系统上运行 x86 程序</span><br>
<span class="comment"># WoW64 Egghunter 的挑战：系统调用号码不同</span><br><br>
<span class="comment"># TEB（Thread Environment Block）：线程环境块</span><br>
<span class="comment"># 存储线程相关信息，包括 SEH 链、栈限制等</span><br>
<span class="comment"># FS:[0x00] → SEH 链 (x86)    GS:[0x00] → TEB (x64)</span><br>
<span class="comment"># FS:[0x04] → 栈顶地址         FS:[0x08] → 栈底地址</span><br><br>
<span class="comment"># 在 WoW64 环境中，正确的 Egghunter 系统调用号：</span><br>
<span class="comment"># x86 原生：NtAccessCheckAndAuditAlarm = 0xED</span><br>
<span class="comment"># WoW64 x86：需要使用不同的调用约定，调用号通过 Heaven's Gate 转换</span><br><br>
<span class="comment"># 实际调试：使用 WinDbg 查看 TEB</span><br>
dt ntdll!_TEB<br>
!teb<br>
<span class="comment"># 查看 FS 段基地址：</span><br>
dg fs
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED L5</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L5_Egghunter_WoW64_TEB}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_l4_aslr.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="osed_l6_rop.php" class="btn btn-sm" style="border-radius: 6px; background: #f97316; color: #fff; border: none; font-weight: 700;">最终关：ROP 与 CFG/CET →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
