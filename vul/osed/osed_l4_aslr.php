<?php
/**
 * OSED L4: ASLR 随机化与信息泄露 (250 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[275] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSED_L4_ASLR_InfoLeak_BaseAddr}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osed_flags']['flag4'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSED L4】ASLR 机制已掌握 (+250 PTS)！</div>';
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
.step-num { background: linear-gradient(135deg, #7c3aed, #f97316); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(124,58,237,0.08); border: 1px solid rgba(124,58,237,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(251,146,60,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🎲 OSED L4：ASLR 随机化机制与信息泄露研究
            <span style="background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 12px;">高级 · 250 PTS</span>
        </h1>
        <p style="color: #fed7aa; font-size: 14px; margin: 0; line-height: 1.6;">深入理解 ASLR 的随机化范围与熵，分析信息泄露如何打破 ASLR 防护，理解现代 Windows 的全 ASLR 实现与偏移计算技术。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 机制：ASLR · 强制 ASLR · HEASLR · 信息泄露 · 格式字符串</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osed_hub.php" style="color: #fed7aa;">← 返回 OSED 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> ASLR 随机化范围分析</h3>
        <div class="cmd-box">
<span class="comment"># ASLR 在 Windows 各版本的随机化熵</span><br>
<span class="comment"># ┌────────────────┬────────────────┬──────────────────────────┐</span><br>
<span class="comment"># │ Windows 版本   │ x86 随机化位数 │ x64 随机化位数           │</span><br>
<span class="comment"># ├────────────────┼────────────────┼──────────────────────────┤</span><br>
<span class="comment"># │ XP SP2         │ 8 bits (256)   │ N/A                      │</span><br>
<span class="comment"># │ Vista/7 (x86)  │ 8 bits (256)   │ 17 bits (131072) for x64 │</span><br>
<span class="comment"># │ Win 8/10 (x86) │ 8 bits         │ 19 bits                  │</span><br>
<span class="comment"># │ Win 10 (x64)   │ N/A            │ 19+ bits (更高熵)         │</span><br>
<span class="comment"># └────────────────┴────────────────┴──────────────────────────┘</span><br><br>
<span class="comment"># x86 ASLR：只有 8 位随机熵（256种可能）→ 理论上可暴力破解</span><br>
<span class="comment"># 随机化发生时机：系统启动时（大部分系统 DLL），进程创建时（堆/栈/可执行文件）</span><br><br>
<span class="comment"># 检查模块是否启用 ASLR（PowerShell）</span><br>
Get-Process -Name "target" | Select-Object -ExpandProperty Modules | ForEach-Object {<br>
&nbsp;&nbsp;$_.ModuleName + " Base: " + $_.BaseAddress<br>
}
        </div>
        <div class="highlight-box">
            📌 <strong>关键认知：</strong>ASLR 的核心假设是攻击者不知道模块的基地址。一旦攻击者通过任何途径（格式字符串泄露、越界读、类型混淆）获得某个模块的实际基地址，就可以通过"基地址 + 已知偏移"精确定位 Gadget 地址，ASLR 保护即告失效。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 信息泄露技术：如何绕过 ASLR</h3>
        <div class="cmd-box">
<span class="comment"># 方法 1：格式字符串漏洞泄露栈地址</span><br>
<span class="comment"># 易受攻击的代码：printf(user_input)  ← 无格式化字符串参数</span><br>
<span class="comment"># 攻击者输入：%p.%p.%p.%p.%p.%p.%p.%p</span><br>
<span class="comment"># 输出：0x7fff5804b1a0.0x100.0xfffffff0.0x7fff5804b160...</span><br>
<span class="comment"># ↑ 这些值是栈上的返回地址/指针，可以推算出模块基地址</span><br><br>
<span class="comment"># 方法 2：越界读（Out-of-Bounds Read）</span><br>
<span class="comment"># 读取超出缓冲区范围的内存 → 泄露相邻内存中的指针值</span><br>
<span class="comment"># 如 C 语言 strlen() 不检查边界，可能泄露数组后面的指针</span><br><br>
<span class="comment"># 方法 3：使用非 ASLR 模块作为跳板</span><br>
<span class="comment"># 系统中仍存在部分未启用 ASLR 的旧 DLL（如旧版第三方软件）</span><br>
<span class="cmd">!mona modules</span>  <span class="comment"># 查找 ASLR=False 的模块</span><br><br>
<span class="comment"># 方法 4：堆喷射（Heap Spray）配合 ASLR</span><br>
<span class="comment"># 大量分配包含特定数据的堆块，使特定地址大概率包含期望内容</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 强制 ASLR 与 HEASLR：现代防御增强</h3>
        <div class="cmd-box">
<span class="comment"># 强制 ASLR (Mandatory ASLR)</span><br>
<span class="comment"># 即使模块编译时未启用 /DYNAMICBASE，也强制随机化其基地址</span><br>
<span class="comment"># 配置：Windows Defender Exploit Protection → 强制随机化图像</span><br><br>
<span class="comment"># High Entropy ASLR (HEASLR) - x64 程序</span><br>
<span class="comment"># 使用 64 位地址空间的更大随机化熵（19+ bits），约 50 万种可能</span><br>
<span class="comment"># /DYNAMICBASE /HIGHENTROPYVA 链接选项</span><br><br>
<span class="comment"># 配合 PIE（Position Independent Executable）检查：</span><br>
checksec --file=target.exe  # Linux ELF<br>
dumpbin /headers target.exe | findstr "Dynamic Base"  # Windows PE
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSED L4</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSED_L4_ASLR_InfoLeak_BaseAddr}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osed_l3_dep_bypass.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osed_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSED 大厅</a>
            <a href="osed_l5_egghunter.php" class="btn btn-sm" style="border-radius: 6px; background: #f97316; color: #fff; border: none; font-weight: 700;">下一关：Egghunter →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
