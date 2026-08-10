<?php
/**
 * OSEP L5: 杀软检测架构与防御研究 (300 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[256] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L5_AV_AMSI_ETW_Defense_Arch}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag5'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSEP L5】检测架构已掌握 (+300 PTS)！</div>';
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
.step-num { background: linear-gradient(135deg, #ef4444, #f97316); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.arch-diagram { background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 20px; font-family: monospace; font-size: 12px; color: #94a3b8; margin: 12px 0; line-height: 2; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(239,68,68,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            🛡️ OSEP L5：杀软检测架构与防御研究
            <span style="background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 12px;">高级 · 300 PTS</span>
        </h1>
        <p style="color: #fca5a5; font-size: 14px; margin: 0; line-height: 1.6;">从防御者视角深入理解 AMSI（反恶意软件扫描接口）和 ETW（Windows 事件追踪）的工作机制、调用流程和检测能力边界。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 机制：AMSI · ETW · Sysmon · Windows Defender · EDR 行为分析</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osep_hub.php" style="color: #fca5a5;">← 返回 OSEP 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> AMSI 工作架构：防御者如何检测脚本</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">AMSI（Antimalware Scan Interface）是微软在 Windows 10 引入的通用恶意软件扫描接口，允许任何防病毒产品接入脚本执行点：</p>
        <div class="arch-diagram">
[用户执行 PowerShell 脚本]<br>
        ↓<br>
[PowerShell 宿主 (powershell.exe)]<br>
        ↓ 调用 AmsiScanBuffer() / AmsiScanString()<br>
[AMSI API (amsi.dll)]<br>
        ↓ 通过 COM/RPC 通信<br>
[Windows Defender / 第三方 AV AMSI Provider]<br>
        ↓ 内容扫描 + 签名匹配<br>
[AMSI_RESULT_DETECTED → 阻断执行]<br>
         OR<br>
[AMSI_RESULT_CLEAN → 允许执行]
        </div>
        <div class="highlight-box">
            🔍 <strong>AMSI 覆盖范围：</strong>PowerShell · VBScript · JScript · Windows Script Host · Office VBA · .NET CLR（部分版本）。AMSI 在脚本<strong>解混淆后、执行前</strong>进行扫描，因此静态混淆无法绕过 AMSI 检测。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> ETW (Event Tracing for Windows)：内核级遥测</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">ETW 是 Windows 的内核级事件追踪框架，为 EDR 产品提供丰富的行为遥测数据：</p>
        <div class="cmd-box">
<span class="comment"># ETW Provider 架构</span><br>
<span class="comment"># ┌─────────────────────────────────────────────┐</span><br>
<span class="comment"># │  ETW Provider (事件产生者)                   │</span><br>
<span class="comment"># │  ● Microsoft-Windows-Kernel-Process          │</span><br>
<span class="comment"># │  ● Microsoft-Windows-PowerShell              │</span><br>
<span class="comment"># │  ● Microsoft-Windows-DNS-Client              │</span><br>
<span class="comment"># │  ● Microsoft-Windows-Threat-Intelligence     │  ← EDR 最常用</span><br>
<span class="comment"># └─────────────────────────────────────────────┘</span><br>
<span class="comment">#              ↓ 事件流</span><br>
<span class="comment"># ┌─────────────────────────────────────────────┐</span><br>
<span class="comment"># │  ETW Consumer (事件消费者)                   │</span><br>
<span class="comment"># │  ● Windows Event Log Service                │</span><br>
<span class="comment"># │  ● EDR Kernel Sensor (CrowdStrike/S1/etc)   │</span><br>
<span class="comment"># │  ● Microsoft Defender for Endpoint          │</span><br>
<span class="comment"># └─────────────────────────────────────────────┘</span><br><br>
<span class="comment"># 查看系统中所有 ETW Provider：</span><br>
logman query providers | findstr "Microsoft-Windows"<br><br>
<span class="comment"># PowerShell Script Block Logging（ETW + 事件日志）：</span><br>
<span class="comment"># EventID 4104 记录所有执行的 PowerShell 脚本块（解混淆后的明文）</span>
        </div>
        <div class="highlight-box">
            ⚡ <strong>防御价值：</strong><code>Microsoft-Windows-Threat-Intelligence</code> (TI) Provider 是 EDR 产品最重要的数据来源，它能捕获：进程注入行为、内存写入、远程线程创建等高危操作。这就是为什么现代 EDR 能够在不依赖签名的情况下检测无文件攻击。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 现代 EDR 检测模型：行为分析 vs 签名检测</h3>
        <div class="cmd-box">
<span class="comment"># 传统 AV 检测模型（签名匹配）</span><br>
<span class="comment"># 限制：只能检测已知恶意文件的 Hash / 字符串特征</span><br>
<span class="comment"># 绕过方式：修改编译选项、编码混淆（不适用于 AMSI）</span><br><br>
<span class="comment"># 现代 EDR 检测模型（行为分析）</span><br>
<span class="comment"># 检测维度 1：进程树分析</span><br>
<span class="comment">#   WINWORD.EXE → cmd.exe → powershell.exe → net.exe  ← 异常链</span><br>
<span class="comment"># 检测维度 2：内存行为</span><br>
<span class="comment">#   堆/栈内存中的 Shellcode 特征（PE 头、MZ 标记）</span><br>
<span class="comment"># 检测维度 3：API 调用序列</span><br>
<span class="comment">#   VirtualAlloc + WriteProcessMemory + CreateRemoteThread = 注入</span><br>
<span class="comment"># 检测维度 4：网络行为</span><br>
<span class="comment">#   非标准端口的 TLS 连接、非浏览器进程的 HTTP 流量</span><br>
<span class="comment"># 检测维度 5：文件系统行为</span><br>
<span class="comment">#   LSASS 进程访问、SAM 文件读取、Shadow Copy 删除</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 防御者视角：构建有效的检测规则</h3>
        <div class="cmd-box">
<span class="comment"># Sigma 规则示例：检测 AMSI 被禁用的行为特征</span><br>
title: AMSI Bypass Attempt via PowerShell<br>
status: stable<br>
logsource:<br>
&nbsp;&nbsp;product: windows<br>
&nbsp;&nbsp;service: powershell<br>
detection:<br>
&nbsp;&nbsp;selection:<br>
&nbsp;&nbsp;&nbsp;&nbsp;EventID: 4104<br>
&nbsp;&nbsp;&nbsp;&nbsp;ScriptBlockText|contains:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 'AmsiUtils'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 'amsiInitFailed'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 'System.Management.Automation.AmsiUtils'<br>
&nbsp;&nbsp;condition: selection<br>
falsepositives:<br>
&nbsp;&nbsp;- Security research tools<br>
level: high
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L5</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            深入理解 AMSI 调用链、ETW 遥测框架与 EDR 行为分析模型后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSEP_L5_AV_AMSI_ETW_Defense_Arch}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #ef4444; border-color: #ef4444;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osep_l4_pivot.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
            <a href="osep_l6_persistence.php" class="btn btn-sm" style="border-radius: 6px; background: #ef4444; color: #fff; border: none; font-weight: 700;">下一关：持久化 →</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
