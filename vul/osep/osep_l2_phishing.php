<?php
/**
 * OSEP L2: 钓鱼向量与载荷投递机制 (150 PTS)
 * 对标 OSEP PEN-300 Module 4: Client Side Attacks
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[253] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L2_Phishing_Macro_HTA_Delivery}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag2'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 恭喜！【OSEP L2】通关 (+150 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请仔细学习钓鱼载荷投递机制后再尝试。</div>';
    }
}

// Interactive defense selector
$defense_mode = isset($_POST['defense_select']) ? $_POST['defense_select'] : '';
$defense_result = '';
if ($defense_mode === 'email_gateway') {
    $defense_result = '📧 邮件网关过滤：检测附件类型（.docm .hta）、宏文档签名、恶意 URL 重定向。主流 SEG 如 Proofpoint/Mimecast 使用沙箱动态分析。';
} elseif ($defense_mode === 'mark_of_web') {
    $defense_result = '🌐 Mark-of-the-Web (MOTW)：Windows 对从互联网下载的文件添加 Zone.Identifier ADS 标记，Office 会据此决定是否启用"受保护的视图"阻止宏执行。';
} elseif ($defense_mode === 'asr_rules') {
    $defense_result = '🛡️ ASR (Attack Surface Reduction) 规则：Microsoft Defender for Endpoint 提供的基于规则的阻断策略，如"阻止来自 Office 子进程的进程创建"会直接拦截 Macro 启动 cmd.exe。';
}
?>

<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #ec4899, #8b5cf6); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(236,72,153,0.08); border: 1px solid rgba(236,72,153,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.warning-box { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: #fbbf24; line-height: 1.7; }
.delivery-matrix { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.delivery-matrix th { background: rgba(236,72,153,0.15); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.delivery-matrix td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(236,72,153,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">

            <div class="ctf-stage-header">
                <h1 class="ctf-stage-title">
                    📧 OSEP L2：钓鱼向量与载荷投递机制
                    <span style="background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid #3b82f6; padding: 3px 10px; border-radius: 12px; font-size: 12px;">初级 · 150 PTS</span>
                </h1>
                <p style="color: #f9a8d4; font-size: 14px; margin: 0; line-height: 1.6;">
                    研究企业环境中最常见的初始访问向量：钓鱼载荷投递链路。从攻击者和防御者双视角理解 Macro、HTA、MOTW、ASR 规则的完整对抗逻辑。
                </p>
                <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
                    <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 技术：Office Macro · HTA · MOTW · DDE · ASR Rules</span>
                    <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osep_hub.php" style="color: #f9a8d4;">← 返回 OSEP 大厅</a></span>
                </div>
            </div>

            <!-- Step 1: 钓鱼攻击面 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">1</span> 企业钓鱼攻击面全景：常见投递载体</h3>
                <table class="delivery-matrix">
                    <tr>
                        <th>投递载体</th><th>技术机制</th><th>绕过 MOTW</th><th>现代检测难度</th><th>OSEP 考纲</th>
                    </tr>
                    <tr>
                        <td><strong>Office Macro (.docm/.xlsm)</strong></td>
                        <td>VBA 宏代码在文档打开时执行</td>
                        <td>容器格式（ISO/IMG）封装</td>
                        <td>🔴 容易被检测</td>
                        <td>✅ 核心</td>
                    </tr>
                    <tr>
                        <td><strong>HTA (HTML Application)</strong></td>
                        <td>mshta.exe 解析执行 JScript/VBScript</td>
                        <td>URL 参数内联</td>
                        <td>🟡 中等</td>
                        <td>✅ 核心</td>
                    </tr>
                    <tr>
                        <td><strong>DDE (Dynamic Data Exchange)</strong></td>
                        <td>Word 域代码调用 cmd.exe</td>
                        <td>需用户确认弹窗</td>
                        <td>🔴 高检测率</td>
                        <td>⚠️ 了解</td>
                    </tr>
                    <tr>
                        <td><strong>LNK 快捷方式</strong></td>
                        <td>隐藏在 ZIP 内的恶意 LNK 文件</td>
                        <td>ZIP 容器绕过 MOTW</td>
                        <td>🟡 中等</td>
                        <td>✅ 重要</td>
                    </tr>
                    <tr>
                        <td><strong>ISO/IMG 磁盘镜像</strong></td>
                        <td>装载后文件不携带 MOTW 标记</td>
                        <td>原生绕过</td>
                        <td>🟢 较难（Win11 已修复）</td>
                        <td>✅ 重要</td>
                    </tr>
                </table>
            </div>

            <!-- Step 2: Office Macro 机制 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">2</span> Office Macro 宏文档：执行链路分析</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">Office Macro 攻击链路：邮件附件 → 用户启用宏 → VBA 执行 → 生成子进程</p>
                <div class="cmd-box">
<span class="comment">' VBA Macro 基础结构（教学示例，理解执行路径）</span><br>
<span class="cmd">Sub AutoOpen()</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="comment">' AutoOpen: 文档打开时自动触发</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="comment">' 常见执行方式（防御者视角理解）：</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="comment">' 1. Shell() 调用 PowerShell/cmd</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="comment">' 2. WScript.Shell.Run() 执行命令</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="comment">' 3. CreateObject("MSXML2.XMLHTTP") 下载远程载荷</span><br>
<span class="cmd">End Sub</span><br><br>
<span class="comment">' 防御检测点（Sysmon 规则示意）：</span><br>
<span class="comment">' EventID 1: WINWORD.EXE 生成 cmd.exe / powershell.exe 子进程</span><br>
<span class="comment">' EventID 7: 可疑 DLL 被 WINWORD.EXE 加载</span><br>
<span class="comment">' EventID 3: WINWORD.EXE 发起网络连接</span>
                </div>
                <div class="highlight-box">
                    💡 <strong>防御视角：</strong>Microsoft Defender for Endpoint 的 ASR 规则 <code>"Block all Office applications from creating child processes"</code> 可直接阻断 VBA→cmd.exe 进程链路，是当前最有效的防护手段。
                </div>
            </div>

            <!-- Step 3: HTA 载荷 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">3</span> HTA (HTML Application)：mshta.exe 滥用分析</h3>
                <div class="cmd-box">
<span class="comment">&lt;!-- HTA 文件基础结构（教学理解用，分析 mshta 执行路径）--&gt;</span><br>
<span class="cmd">&lt;html&gt;</span><br>
<span class="cmd">&lt;head&gt;</span><br>
&nbsp;&nbsp;&lt;HTA:APPLICATION <br>
&nbsp;&nbsp;&nbsp;&nbsp;APPLICATIONNAME="Legitimate Tool"<br>
&nbsp;&nbsp;&nbsp;&nbsp;WINDOWSTATE="minimize"&gt;<br>
<span class="cmd">&lt;/head&gt;</span><br>
<span class="cmd">&lt;script language="VBScript"&gt;</span><br>
<span class="comment">' 执行链路：mshta.exe → VBScript/JScript → WScript.Shell/Shell()</span><br>
<span class="comment">' 检测要点：mshta.exe 进程的子进程、网络连接、文件写入行为</span><br>
<span class="cmd">&lt;/script&gt;</span><br>
<span class="cmd">&lt;/html&gt;</span><br><br>
<span class="comment"># 防御侧 Sigma 规则示例：</span><br>
<span class="comment"># 检测 mshta.exe 发起的 PowerShell/cmd 子进程</span><br>
<span class="cmd">detection:</span><br>
&nbsp;&nbsp;<span class="cmd">selection:</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">ParentImage|endswith: 'mshta.exe'</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">Image|endswith:</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">- 'cmd.exe'</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">- 'powershell.exe'</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="cmd">- 'wscript.exe'</span><br>
&nbsp;&nbsp;<span class="cmd">condition: selection</span>
                </div>
            </div>

            <!-- Step 4: 防御机制互动 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">4</span> 互动学习：选择防御机制，了解其工作原理</h3>
                <form method="post">
                    <select name="defense_select" class="form-control" style="max-width: 360px; border-radius: 8px; margin-bottom: 12px;">
                        <option value="">-- 选择防御机制 --</option>
                        <option value="email_gateway">邮件安全网关 (SEG / Email Gateway)</option>
                        <option value="mark_of_web">Mark-of-the-Web (MOTW) 标记</option>
                        <option value="asr_rules">Microsoft Defender ASR 规则</option>
                    </select>
                    <button type="submit" class="btn btn-sm" style="background: #ec4899; color: #fff; border: none; border-radius: 6px; font-weight: 700;">查看详解</button>
                </form>
                <?php if (!empty($defense_result)): ?>
                <div class="highlight-box" style="border-color: rgba(236,72,153,0.3); background: rgba(236,72,153,0.06);">
                    <?php echo $defense_result; ?>
                </div>
                <?php endif; ?>
                <div class="warning-box">
                    ⚠️ <strong>OSEP 考试要点：</strong>理解这些防御机制的目的是帮助你在构建测试载荷时预判哪些路径会被拦截。考试中不会要求你绕过真实的 EDR，但需要你理解检测逻辑，能够解释为什么某个载荷会失败。
                </div>
            </div>

            <!-- Flag 区域 -->
            <div class="flag-submit-area">
                <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L2</h4>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
                    完成学习后，你已理解：Office Macro 执行链 → HTA mshta.exe 滥用 → MOTW/ASR 防御机制<br><br>
                    <strong style="color: #ec4899;">此关卡的 Flag 为：</strong>
                </p>
                <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
                    <span class="flag-text">flag{OSEP_L2_Phishing_Macro_HTA_Delivery}</span>
                </div>
                <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
                    <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #ec4899; border-color: #ec4899;">
                        <i class="fa fa-check"></i> 验证 Flag
                    </button>
                </form>
                <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 15px;">' . $flag_msg . '</div>'; } ?>
                <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="osep_l1_enum.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
                    <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
                    <a href="osep_l3_lateral.php" class="btn btn-sm" style="border-radius: 6px; background: #ec4899; color: #fff; border: none; font-weight: 700;">下一关：横向移动 →</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
