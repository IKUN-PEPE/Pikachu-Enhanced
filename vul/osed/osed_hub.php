<?php
/**
 * OSED CTF Hub - Exploit Development (10 Stages, 2850 PTS)
 * OSCE³ - OSED Direction: Windows Exploit Development (EXP-301)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[270] = 'active open';
$ACTIVE[271] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

if (!isset($_SESSION['osed_flags'])) {
    $_SESSION['osed_flags'] = [];
}

$flags_db = [
    'flag1' => ['flag' => 'flag{OSED_L1_Fuzzing_Crash_Analysis_EIP}', 'name' => 'L1: 模糊测试原理与崩溃分析', 'points' => 100, 'file' => 'osed_l1_fuzzing.php', 'difficulty' => '入门'],
    'flag2' => ['flag' => 'flag{OSED_L2_SEH_Overflow_nSEH_Handler}', 'name' => 'L2: SEH 异常处理器覆盖机制', 'points' => 150, 'file' => 'osed_l2_seh.php', 'difficulty' => '初级'],
    'flag3' => ['flag' => 'flag{OSED_L3_DEP_NX_ROP_Gadget_Chain}', 'name' => 'L3: DEP/NX 防御机制与 ROP 原理', 'points' => 200, 'file' => 'osed_l3_dep_bypass.php', 'difficulty' => '中级'],
    'flag4' => ['flag' => 'flag{OSED_L4_ASLR_InfoLeak_BaseAddr}', 'name' => 'L4: ASLR 随机化与信息泄露利用', 'points' => 250, 'file' => 'osed_l4_aslr.php', 'difficulty' => '高级'],
    'flag5' => ['flag' => 'flag{OSED_L5_Egghunter_WoW64_TEB}', 'name' => 'L5: Egghunter 技术原理研究', 'points' => 300, 'file' => 'osed_l5_egghunter.php', 'difficulty' => '高级'],
    'flag6' => ['flag' => 'flag{OSED_L6_ROP_CFG_CET_Stack_Defense}', 'name' => 'L6: ROP 链构造原理与 CFG/CET 防御', 'points' => 350, 'file' => 'osed_l6_rop.php', 'difficulty' => '专家'],
    \'flag7\' => [\'flag\' => \'flag{OSED_L7_ASM_Custom_Shellcode}\', \'name\' => \'L7: x86 汇编与自定义 Shellcode\', \'points\' => 350, \'file\' => \'osed_l7_asm_shellcode.php\', \'difficulty\' => \'专家\'],
    \'flag8\' => [\'flag\' => \'flag{OSED_L8_Format_String_Exploit}\', \'name\' => \'L8: 格式化字符串漏洞利用\', \'points\' => 400, \'file\' => \'osed_l8_format_string.php\', \'difficulty\' => \'专家\'],
    \'flag9\' => [\'flag\' => \'flag{OSED_L9_Protocol_Reverse_Vuln_Hunt}\', \'name\' => \'L9: 协议逆向与漏洞挖掘\', \'points\' => 350, \'file\' => \'osed_l9_proto_reverse.php\', \'difficulty\' => \'专家\'],
    \'flag10\' => [\'flag\' => \'flag{OSED_L10_WPM_DEP_ASLR_Bypass}\', \'name\' => \'L10: WPM DEP+ASLR 联合绕过 [终章]\', \'points\' => 400, \'file\' => \'osed_l10_wpm_bypass.php\', \'difficulty\' => \'专家\'],
];

$submit_msg = '';
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input']);
    $matched = false;
    foreach ($flags_db as $k => $item) {
        if ($user_flag === $item['flag']) {
            $_SESSION['osed_flags'][$k] = true;
            $submit_msg = '<div class="alert alert-success" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！成功提交【' . $item['name'] . '】Flag，获得 ' . $item['points'] . ' 积分！🎉</div>';
            $matched = true;
            break;
        }
    }
    if (!$matched && !empty($user_flag)) {
        $submit_msg = '<div class="alert alert-danger" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 错误，请仔细学习关卡内容后再提交！</div>';
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $_SESSION['osed_flags'] = [];
    header("Location: osed_hub.php");
    exit;
}

$total_score = 0;
$captured_count = 0;
foreach ($flags_db as $k => $item) {
    if (isset($_SESSION['osed_flags'][$k]) && $_SESSION['osed_flags'][$k]) {
        $total_score += $item['points'];
        $captured_count++;
    }
}
$progress_pct = count($flags_db) > 0 ? round(($captured_count / count($flags_db)) * 100) : 0;
?>

<style>
.osed-hero-banner {
    background: linear-gradient(135deg, #0a0f1e 0%, #111827 50%, #1c2333 100%);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 40px rgba(251, 146, 60, 0.2);
    margin-bottom: 25px;
    border: 1px solid rgba(251, 146, 60, 0.3);
    position: relative;
    overflow: hidden;
}
.osed-hero-banner::before {
    content: 'OSED';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 100px;
    font-weight: 900;
    color: rgba(251, 146, 60, 0.07);
    letter-spacing: -2px;
    pointer-events: none;
}
.osed-title { font-size: 26px; font-weight: 800; margin-top: 0; color: #f8fafc; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.osed-badge { background: rgba(251, 146, 60, 0.2); color: #fed7aa; border: 1px solid #f97316; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.diff-badge { padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.diff-入门 { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid #10b981; }
.diff-初级 { background: rgba(59,130,246,0.15); color: #93c5fd; border: 1px solid #3b82f6; }
.diff-中级 { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid #f59e0b; }
.diff-高级 { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid #ef4444; }
.diff-专家 { background: rgba(139,92,246,0.15); color: #c4b5fd; border: 1px solid #8b5cf6; }
.level-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 20px; transition: all 0.25s ease; }
.level-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(251, 146, 60, 0.12); border-color: #f97316; }
.level-card.completed { border-left: 6px solid #10b981; }
.level-card.uncompleted { border-left: 6px solid #f97316; }
.level-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.level-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 10px; }
.points-tag { background: rgba(251, 146, 60, 0.15); color: #f97316; font-weight: 800; padding: 4px 12px; border-radius: 8px; font-size: 14px; }
.level-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 14px; }
.level-actions { display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid var(--border-color); }
.progress-bar-custom { background: var(--bg-secondary); border-radius: 10px; height: 14px; overflow: hidden; margin: 15px 0; border: 1px solid var(--border-color); }
.progress-bar-fill { background: linear-gradient(90deg, #f97316, #ef4444); height: 100%; border-radius: 10px; transition: width 0.5s ease; }
.stat-pill { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; font-size: 13px; color: #e2e8f0; }
.compliance-box { background: rgba(251,146,60,0.06); border: 1px solid rgba(251,146,60,0.25); border-radius: 12px; padding: 20px; margin-bottom: 25px; }
.mitigation-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 0; }
.mitigation-table th { background: rgba(251,146,60,0.1); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.mitigation-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">

            <div class="osed-hero-banner">
                <h1 class="osed-title">
                    💥 OSCE³ · OSED 漏洞利用开发 CTF 靶场
                    <span class="osed-badge">10 大关卡 · 2850 PTS</span>
                    <span class="osed-badge" style="background: rgba(239,68,68,0.2); color: #fca5a5; border-color: #ef4444;">内存安全研究方向</span>
                </h1>
                <p style="font-size: 15px; color: #fed7aa; line-height: 1.7; max-width: 950px; margin: 15px 0 20px 0;">
                    对标 Offensive Security OSED (EXP-301) 考纲，从防御者视角理解 <strong style="color: #ffedd5;">栈溢出崩溃分析 → SEH 异常处理 → DEP/NX 机制 → ASLR 随机化 → Egghunter → ROP链与 CFG/CET 防御</strong>。深入操作系统内存安全机制研究。
                </p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="stat-pill"><i class="fa fa-flag" style="color: #f97316;"></i> 通关进度：<strong><?php echo $captured_count; ?> / <?php echo count($flags_db); ?></strong> 关</div>
                    <div class="stat-pill"><i class="fa fa-trophy" style="color: #fbbf24;"></i> 当前积分：<strong><?php echo $total_score; ?> / 2850</strong> PTS</div>
                    <div class="stat-pill"><i class="fa fa-certificate" style="color: #34d399;"></i> 目标认证：<strong>OSED (EXP-301)</strong></div>
                    <div class="stat-pill"><i class="fa fa-shield" style="color: #93c5fd;"></i> 防御机制：<strong>DEP · ASLR · CFG · CET · SafeSEH</strong></div>
                </div>
            </div>

            <!-- 合规声明 -->
            <div class="compliance-box">
                <h5 style="color: #f97316; font-weight: 700; margin-top: 0;"><i class="fa fa-info-circle"></i> 教学定位说明</h5>
                <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.7;">
                    本方向所有关卡仅从 <strong style="color: var(--text-primary);">防御架构理解与安全机制研究</strong> 角度展开教学。内容包含：操作系统防御机制原理（DEP/NX/ASLR/CFG/CET）、崩溃分析方法论（Fuzzing → Crash Triage → Root Cause Analysis）、以及现代编译器安全特性（SafeSEH/GS/CFG）。
                    <strong style="color: #f97316;">不包含</strong>：功能性 Shellcode 生成、完整 ROP 链 exploit 代码、实际内存破坏漏洞利用载荷。
                </p>
            </div>

            <!-- 防御机制全景表 -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; margin-bottom: 25px;">
                <h4 style="margin-top:0; font-weight: 700; color: var(--text-primary);">
                    <i class="fa fa-shield" style="color: #f97316;"></i> Windows 内存安全防御机制全景（防御者视角）
                </h4>
                <table class="mitigation-table">
                    <tr><th>防御机制</th><th>引入版本</th><th>工作原理</th><th>防御目标</th></tr>
                    <tr><td><strong>Stack Canary (GS)</strong></td><td>MSVC /GS 编译选项</td><td>在函数局部变量与返回地址之间插入随机值，函数返回前校验</td><td>栈溢出覆盖返回地址</td></tr>
                    <tr><td><strong>SafeSEH</strong></td><td>Windows XP SP2+</td><td>编译时记录合法 SEH handler 白名单，运行时校验</td><td>SEH 覆盖攻击</td></tr>
                    <tr><td><strong>DEP / NX</strong></td><td>Windows XP SP2+ / 硬件 NX 位</td><td>标记数据区域（栈/堆）不可执行，任何试图执行数据段代码的行为触发异常</td><td>直接 Shellcode 注入执行</td></tr>
                    <tr><td><strong>ASLR</strong></td><td>Vista+ (全随机 Win8+)</td><td>系统 DLL、可执行文件、堆、栈基地址每次启动随机化</td><td>固定地址 ROP gadget / 跳板</td></tr>
                    <tr><td><strong>CFG (Control Flow Guard)</strong></td><td>Windows 8.1 / MSVC 2015+</td><td>编译时生成合法间接调用目标位图，运行时通过 ntdll!_guard_check_icall 验证</td><td>劫持间接调用/函数指针</td></tr>
                    <tr><td><strong>CET (影子栈)</strong></td><td>Windows 11 + Intel Tiger Lake</td><td>影子栈存储返回地址副本，RET 指令校验影子栈与主栈地址是否一致</td><td>ROP 链 & 栈溢出 RET 劫持</td></tr>
                </table>
            </div>

            <!-- Flag Submit -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 25px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h4 style="margin: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-paper-plane" style="color: #f97316;"></i> OSED Flag 验证中心</h4>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-secondary);">完成每关防御机制研究后，提交 flag{...} 格式 Flag 累积积分。</p>
                    </div>
                    <form method="post" style="display: flex; gap: 10px; flex-grow: 1; max-width: 500px;">
                        <input type="text" name="flag_input" class="form-control" placeholder="flag{...}" required style="border-radius: 8px; font-family: monospace;">
                        <button type="submit" name="submit_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; min-width: 110px; background: #f97316; border-color: #f97316;"><i class="fa fa-check"></i> 提交</button>
                        <a href="osed_hub.php?reset=1" class="btn btn-default" style="border-radius: 8px;" onclick="return confirm('确定重置 OSED 进度？');"><i class="fa fa-refresh"></i></a>
                    </form>
                </div>
                <?php if (!empty($submit_msg)) { echo '<div style="margin-top: 15px;">' . $submit_msg . '</div>'; } ?>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?php echo $progress_pct; ?>%;"></div>
                </div>
            </div>

            <!-- 6 Levels -->
            <div class="row">
                <?php
                $descriptions = [
                    'flag1' => '学习 Fuzzing 模糊测试的系统方法：文件格式 Fuzzer、网络协议 Fuzzer、崩溃捕获（WinDbg/mona.py）、EIP/RIP 偏移计算（Metasploit pattern_create/offset）。',
                    'flag2' => '理解 SEH（结构化异常处理）链表结构与 nSEH/Handler 覆盖原理，学习 SafeSEH 如何防御 SEH 覆盖攻击，理解 PEB unlinking 检测机制。',
                    'flag3' => '从操作系统层面深入理解 DEP（数据执行保护）与 NX 位工作机制，研究 ROP（Return-Oriented Programming）的理论基础与 CFG/CET 防御架构。',
                    'flag4' => '理解 ASLR（地址空间布局随机化）的随机化范围与信息泄露利用路径：格式字符串泄露、侧信道、非 ASLR 模块 rebasing 等研究视角。',
                    'flag5' => '研究 Egghunter 技术的工作原理：WinAPI/SEH 两种实现路径、系统调用地址空间搜索机制、在内存碎片化场景下定位 Shellcode 的原理。',
                    'flag6' => '深入 ROP 链构造的理论基础（Gadget 搜索、ROPgadget/ropper 工具原理），理解 CFG（控制流防护）和 CET（影子栈）如何从根本上阻断 ROP 攻击。',
                    \'flag7\' => \'深入学习 x86 汇编语言，编写并优化高度定制的 Shellcode 以满足特定利用场景。\',
                    \'flag8\' => \'探究格式化字符串漏洞的底层原理，通过任意地址读写实现执行流劫持。\',
                    \'flag9\' => \'通过逆向工程分析私有网络协议，挖掘协议解析实现中的内存破坏漏洞。\',
                    \'flag10\' => \'综合应用 WriteProcessMemory 技术，实战环境下联合绕过 DEP 和 ASLR。\',
                ];
                foreach ($flags_db as $key => $item) {
                    $is_done = isset($_SESSION['osed_flags'][$key]) && $_SESSION['osed_flags'][$key];
                ?>
                <div class="col-md-6">
                    <div class="level-card <?php echo $is_done ? 'completed' : 'uncompleted'; ?>">
                        <div class="level-header">
                            <h3 class="level-title">
                                <span style="background: rgba(251, 146, 60, 0.1); color: #f97316; width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800;">
                                    <?php echo str_replace('flag', '', $key); ?>
                                </span>
                                <?php echo $item['name']; ?>
                            </h3>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <span class="diff-badge diff-<?php echo $item['difficulty']; ?>"><?php echo $item['difficulty']; ?></span>
                                <span class="points-tag"><?php echo $item['points']; ?> PTS</span>
                            </div>
                        </div>
                        <p class="level-desc"><?php echo $descriptions[$key]; ?></p>
                        <div class="level-actions">
                            <div>
                                <?php if ($is_done) { ?>
                                    <span class="label label-success" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-check"></i> 已通关</span>
                                <?php } else { ?>
                                    <span class="label label-default" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-clock-o"></i> 待挑战</span>
                                <?php } ?>
                            </div>
                            <a href="<?php echo $item['file']; ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700; background: #f97316; border-color: #f97316;">
                                进入关卡 <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
