<?php
/**
 * OSED L6: ROP 链构造原理与 CFG/CET 防御 (350 PTS) - 终章
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[277] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L6_ROP_CFG_CET_Stack_Defense}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag6'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 🎉 恭喜！【OSED L6】终章通关！OSED 方向满分 1350 PTS！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #1a0a0a 0%, #2a0505 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(251,146,60,0.4); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #dc2626, #f97316); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.completion-banner { background: linear-gradient(135deg, #0c3a0c, #1a3a1a); border: 1px solid rgba(52,211,153,0.4); border-radius: 14px; padding: 24px; margin-bottom: 24px; text-align: center; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">⛓️ OSED L6：ROP 链构造原理与 CFG/CET 防御 · 终章
            <span style="background: rgba(139,92,246,0.2); color: #c4b5fd; border: 1px solid #8b5cf6; padding: 3px 10px; border-radius: 12px; font-size: 12px;">专家 · 350 PTS · 终章</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">深入 ROP 链构造的理论基础与工具使用，理解 CFG（控制流防护）和 CET（影子栈）如何从硬件和操作系统层面根本阻断 ROP 攻击。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 工具/机制：ROPgadget · ropper · CFG · CET 影子栈 · VirtualProtect 链</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> ROP Gadget 搜索与分类</h3>
        <div class="cmd-box">
<span class="comment"># ROPgadget 工具使用（分析目标二进制文件）</span><br>
<span class="comment"># 不执行任何利用，仅用于理解目标中存在哪些 Gadget</span><br><br>
<span class="comment"># 搜索所有 ROP Gadgets</span><br>
ROPgadget --binary target.exe --rop<br><br>
<span class="comment"># 搜索特定类型 Gadgets</span><br>
ROPgadget --binary ntdll.dll --only "pop|ret"<br>
ROPgadget --binary kernel32.dll --string "VirtualProtect"<br><br>
<span class="comment"># ropper 工具（另一个 Gadget 搜索工具）</span><br>
ropper -f target.exe --type rop<br>
ropper -f target.exe --search "pop eax; ret"<br><br>
<span class="comment"># Gadget 分类（理解其在 ROP 链中的作用）：</span><br>
<span class="comment"># ---- 寄存器控制 ----</span><br>
<span class="comment"># pop eax; ret           → 控制 EAX（从栈上弹出值）</span><br>
<span class="comment"># pop ecx; ret           → 控制 ECX</span><br>
<span class="comment"># xchg eax, esp; ret     → 栈旋转（调整 ESP 到可控内存）</span><br>
<span class="comment"># ---- 内存写入 ----</span><br>
<span class="comment"># mov [eax], ecx; ret    → 向 EAX 指向的地址写入 ECX 值</span><br>
<span class="comment"># ---- 函数调用 ----</span><br>
<span class="comment"># call eax; ret          → 调用 EAX 指向的函数</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> VirtualProtect ROP 链结构分析（理论）</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
            当 DEP 启用时，攻击者需要通过 ROP 链调用 VirtualProtect() 将包含 Shellcode 的内存页标记为可执行（PAGE_EXECUTE_READWRITE = 0x40）。以下是其理论结构：
        </p>
        <div class="cmd-box">
<span class="comment"># VirtualProtect() 函数签名（Windows API）</span><br>
<span class="comment"># BOOL VirtualProtect(</span><br>
<span class="comment">#   LPVOID lpAddress,        → 要修改的内存地址</span><br>
<span class="comment">#   SIZE_T dwSize,           → 修改的大小（字节数）</span><br>
<span class="comment">#   DWORD  flNewProtect,     → 新的保护属性（0x40 = RWX）</span><br>
<span class="comment">#   PDWORD lpflOldProtect    → 保存旧属性的指针</span><br>
<span class="comment"># );</span><br><br>
<span class="comment"># ROP 链需要在栈上构造这些参数并调用 VirtualProtect 地址</span><br>
<span class="comment"># 栈布局（理论）：</span><br>
<span class="comment"># [VirtualProtect 地址]  ← EIP 跳转到这里</span><br>
<span class="comment"># [RET 地址，VP执行后跳这里]  ← 通常是 Shellcode 所在地址</span><br>
<span class="comment"># [lpAddress]            ← Shellcode 地址</span><br>
<span class="comment"># [dwSize]               ← 0x201（足够大的 Shellcode 空间）</span><br>
<span class="comment"># [flNewProtect]         ← 0x40 (PAGE_EXECUTE_READWRITE)</span><br>
<span class="comment"># [lpflOldProtect]       ← 可写内存地址（保存旧属性）</span>
        </div>
        <div class="highlight-box">
            ⚠️ <strong>理论仅供研究：</strong>以上为 DEP 绕过技术的学术描述，用于理解操作系统安全机制的局限性。实际上，CFG 和 CET 等现代防御机制已从根本上改变了这一攻击面的可行性。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> CFG 与 CET：ROP 的终结者</h3>
        <div class="cmd-box">
<span class="comment"># CFG 如何阻断 ROP 中的间接调用：</span><br>
<span class="comment"># 传统 ROP：劫持间接调用 → call eax（EAX = 任意 Gadget 地址）</span><br>
<span class="comment"># CFG 保护：call eax 前插入 → call __guard_check_icall(eax)</span><br>
<span class="comment"># → 如果 EAX 不在 CFG 位图中 → STATUS_ACCESS_VIOLATION</span><br><br>
<span class="comment"># CET 影子栈如何阻断 ROP 的 RET 滑动：</span><br>
<span class="comment"># 传统 ROP：溢出覆盖返回地址 → ret 跳转到 Gadget</span><br>
<span class="comment"># CET 保护：ret 执行时检查影子栈 → 返回地址不匹配 → #CP 异常</span><br><br>
<span class="comment"># 查看程序是否编译了 CFG 支持（MSVC）：</span><br>
dumpbin /headers target.exe | findstr "Guard"<br>
<span class="comment"># 输出：       4000 DLL characteristics</span><br>
<span class="comment">#                    Guard</span><br><br>
<span class="comment"># 查看系统是否启用了 CET：</span><br>
Get-ProcessMitigation -System | Where-Object { $_.UserShadowStack -match "Enable" }
        </div>
        <div class="highlight-box">
            🎯 <strong>总结：防御演进视角</strong><br>
            栈溢出 → Stack Canary 防御 → SEH 绕过 → SafeSEH 防御 → DEP 防御 → ROP 绕过 DEP → CFG 防御 ROP 间接调用 → CET 影子栈防御 ROP RET 滑动 → 理论上 CET 已大幅提升现代漏洞利用门槛
        </div>
    </div>

    <div class="completion-banner">
        <div style="font-size: 48px; margin-bottom: 10px;">🏆</div>
        <h3 style="color: #34d399; font-weight: 800; margin-top: 0;">OSED 方向即将完成！</h3>
        <p style="color: #6ee7b7; font-size: 14px; margin-bottom: 0;">提交此关 Flag 即完成 OSED 全部 6 关卡，累计 1350 PTS！<br>OSCE³ 三大方向全部通关，总分 <strong>4500 PTS</strong>！</p>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED 终章 L6</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L6_ROP_CFG_CET_Stack_Defense}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_l5_egghunter.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="../osep/osep_hub.php" class="btn btn-sm" style="border-radius: 6px; background: #8b5cf6; color: #fff; border: none; font-weight: 700;">OSEP 大厅</a>
            <a href="../oswe/oswe_hub.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">OSWE 大厅</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
