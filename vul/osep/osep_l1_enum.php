<?php
/**
 * OSEP L1: 初始侦察与 OPSEC 操守 (100 PTS)
 * 对标 OSEP PEN-300 Module 1: Client Side Code Execution / Enumeration OPSEC
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[252] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L1_OPSEC_Enum_NoiseReduction_Done}';
$flag_msg = '';

if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag1'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 恭喜！Flag 校验正确，已点亮【OSEP L1】成就 (+100 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 不正确，请仔细完成以下步骤，从 Scan Technique 列中寻找答案。</div>';
    }
}

// Interactive Quiz state
$quiz_answer = '';
$quiz_correct = false;
if (isset($_POST['submit_quiz'])) {
    $quiz_answer = strtolower(trim($_POST['quiz_answer']));
    if (strpos($quiz_answer, 'syn') !== false || strpos($quiz_answer, 'stealth') !== false || strpos($quiz_answer, '-ss') !== false) {
        $quiz_correct = true;
    }
}
?>

<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.step-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 0;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.step-num {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box {
    background: rgba(139,92,246,0.08);
    border: 1px solid rgba(139,92,246,0.25);
    border-radius: 8px;
    padding: 14px 18px;
    margin: 12px 0;
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.7;
}
.warning-box {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 8px;
    padding: 14px 18px;
    margin: 12px 0;
    font-size: 13px;
    color: #fbbf24;
    line-height: 1.7;
}
.opsec-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.opsec-table th { background: rgba(139,92,246,0.15); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.opsec-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
.opsec-table tr:nth-child(even) td { background: rgba(0,0,0,0.02); }
.flag-submit-area {
    background: var(--bg-card);
    border: 2px dashed rgba(139,92,246,0.4);
    border-radius: 12px;
    padding: 24px;
    margin-top: 25px;
    text-align: center;
}
.interactive-sim { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin: 14px 0; font-family: monospace; font-size: 12px; color: var(--text-secondary); }
.sim-input { background: #1e293b; border: 1px solid #334155; color: #7dd3fc; padding: 6px 12px; border-radius: 4px; font-family: monospace; width: 100%; margin-top: 8px; }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">

            <!-- Header -->
            <div class="ctf-stage-header">
                <h1 class="ctf-stage-title">
                    🎯 OSEP L1：初始侦察与 OPSEC 操守
                    <span style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 12px; font-size: 12px;">入门 · 100 PTS</span>
                </h1>
                <p style="color: var(--text-secondary); font-size: 14px; margin: 0; line-height: 1.6;">
                    对标 OSEP PEN-300 考纲 Module 1 & 2。掌握不触发 IDS/EDR 告警的 OPSEC 优化侦察手法，理解被动与主动侦察的区别，学会控制扫描流量噪声。
                </p>
                <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
                    <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 工具：Nmap · Shodan · Amass · OSINT Framework</span>
                    <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🎯 目标：掌握 OPSEC 侦察框架</span>
                    <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="osep_hub.php" style="color: var(--text-secondary);">← 返回 OSEP 大厅</a></span>
                </div>
            </div>

            <!-- Step 1: OPSEC 概念 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">1</span> OPSEC 操作安全意识：为什么侦察要"轻手轻脚"？</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
                    在真实渗透测试（特别是 OSEP 考试）中，强调 <strong style="color: var(--text-primary);">OPSEC（操作安全，Operational Security）</strong>——即在不被目标感知的情况下完成侦察。
                </p>
                <div class="highlight-box">
                    💡 <strong>核心原则：</strong>攻击者的每一个网络请求、每一次扫描，都会留下痕迹。好的渗透测试者需要在<strong>信息获取效率</strong>与<strong>被发现风险</strong>之间取得平衡。
                </div>
                <table class="opsec-table">
                    <tr>
                        <th>侦察类型</th><th>示例工具/方法</th><th>产生流量噪声</th><th>OPSEC 等级</th>
                    </tr>
                    <tr>
                        <td>纯被动侦察</td><td>Shodan · Censys · WHOIS · Archive.org</td><td>❌ 零流量</td><td>🟢 最优</td>
                    </tr>
                    <tr>
                        <td>半主动侦察</td><td>DNS 枚举 · Certificate Transparency</td><td>⚠️ 极小</td><td>🟡 良好</td>
                    </tr>
                    <tr>
                        <td>主动扫描（低噪）</td><td>nmap -sS -T2 --top-ports 100</td><td>⚠️ 适中</td><td>🟡 可控</td>
                    </tr>
                    <tr>
                        <td>主动扫描（高噪）</td><td>nmap -sV -A -T5 全端口</td><td>🔴 极高</td><td>🔴 危险</td>
                    </tr>
                </table>
            </div>

            <!-- Step 2: 被动侦察 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">2</span> 被动侦察：零流量的情报收集</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">以下命令均不直接访问目标，不会在目标日志中留下任何记录：</p>
                <div class="cmd-box">
<span class="comment"># 1. Shodan CLI 搜索目标暴露资产（不访问目标）</span><br>
<span class="cmd">shodan host target.company.com</span><br>
<span class="cmd">shodan search "org:TargetCorp" --fields ip_str,port,org,hostnames</span><br><br>
<span class="comment"># 2. 证书透明度 - 子域枚举（纯读取公开数据库）</span><br>
<span class="cmd">curl "https://crt.sh/?q=%25.targetcorp.com&output=json" | jq '.[].name_value' | sort -u</span><br><br>
<span class="comment"># 3. WHOIS 域名注册信息</span><br>
<span class="cmd">whois targetcorp.com | grep -E "Registrant|Admin|Tech|Name Server"</span><br><br>
<span class="comment"># 4. GitHub OSINT - 泄露的代码与凭证</span><br>
<span class="cmd">gh search repos "targetcorp.com" --json fullName,description | jq '.[].fullName'</span>
                </div>
                <div class="warning-box">
                    ⚠️ <strong>OSEP 考试技巧：</strong>在考试开始前 24 小时内使用 Shodan/Censys 完成被动枚举，建立目标资产地图，进入实战操作时直接使用已有情报，降低噪声。
                </div>
            </div>

            <!-- Step 3: 低噪主动扫描 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">3</span> 低噪主动扫描：Nmap OPSEC 参数解析</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
                    Nmap 默认参数会产生大量流量，触发 IDS 告警。以下是 OPSEC 优化后的扫描策略：
                </p>
                <div class="cmd-box">
<span class="comment"># ❌ 不推荐（OSEP 考试中会被 SOC 感知）</span><br>
nmap -A -T4 -p- 192.168.1.0/24<br><br>
<span class="comment"># ✅ 推荐：SYN 隐蔽扫描 + 降速 + 常用端口</span><br>
<span class="cmd">nmap -sS -T2 --top-ports 1000 --open -oN scan_result.txt 192.168.x.x</span><br><br>
<span class="comment"># ✅ 服务版本探测（只对已发现开放端口执行）</span><br>
<span class="cmd">nmap -sV -p 80,443,8080,8443,445,3389 --version-intensity 0 192.168.x.x</span><br><br>
<span class="comment"># ✅ 使用 --scan-delay 增加包间延迟（绕过频率检测）</span><br>
<span class="cmd">nmap -sS --scan-delay 500ms -p 22,80,443,3306 192.168.x.x</span><br><br>
<span class="comment"># 关键参数说明：</span><br>
<span class="comment"># -sS = SYN Stealth scan，不完成 TCP 握手，日志留存概率低</span><br>
<span class="comment"># -T2 = 降速模式，减少并发，绕过 IDS 流量阈值</span><br>
<span class="comment"># --open = 只输出开放端口，减少误报干扰</span>
                </div>

                <!-- Interactive Quiz -->
                <div style="background: rgba(139,92,246,0.06); border: 1px solid rgba(139,92,246,0.2); border-radius: 10px; padding: 18px; margin-top: 16px;">
                    <h5 style="color: var(--text-primary); font-weight: 700; margin-top: 0;">🧠 互动测验：OPSEC 扫描参数</h5>
                    <p style="font-size: 13px; color: var(--text-secondary);">Nmap 中哪个扫描类型（参数）被称为"隐蔽扫描"，不完成完整 TCP 三次握手，因而在目标服务日志中留存概率最低？</p>
                    <form method="post">
                        <input type="text" name="quiz_answer" class="form-control" placeholder="输入 Nmap 参数（如 -sX）" style="max-width: 260px; border-radius: 6px; font-family: monospace; display: inline-block;">
                        <button type="submit" name="submit_quiz" class="btn btn-sm" style="background: #8b5cf6; color: #fff; border: none; border-radius: 6px; margin-left: 8px; font-weight: 700;">验证答案</button>
                    </form>
                    <?php if (isset($_POST['submit_quiz'])): ?>
                        <?php if ($quiz_correct): ?>
                            <div style="margin-top: 10px; color: #34d399; font-size: 13px; font-weight: 700;">✅ 正确！<code>-sS</code>（SYN Stealth Scan）是 Nmap 最常用的 OPSEC 友好扫描方式。完成本测验后，请提交关卡 Flag 获得积分。</div>
                        <?php else: ?>
                            <div style="margin-top: 10px; color: #f87171; font-size: 13px;">❌ 不正确。提示：Nmap 帮助文档中 SYN 扫描对应的参数是什么？它的别名是 "Stealth scan"。</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 4: 内网侦察 -->
            <div class="step-box">
                <h3 class="step-title"><span class="step-num">4</span> 进入内网后：AD 环境快速侦察（已获得初始立足点）</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">
                    获得初始立足点后，需要快速（且安静）地了解 AD 域环境：
                </p>
                <div class="cmd-box">
<span class="comment"># 1. 枚举当前域信息（内置命令，最低噪声）</span><br>
<span class="cmd">whoami /all</span>  <span class="comment"> # 当前用户权限与组成员</span><br>
<span class="cmd">net user %USERNAME% /domain</span>  <span class="comment"> # 域用户详情</span><br>
<span class="cmd">nltest /dsgetdc:DOMAIN_NAME</span>  <span class="comment"> # 发现域控制器</span><br><br>
<span class="comment"># 2. PowerShell AD 枚举（需要 RSAT 或 AD 模块）</span><br>
<span class="cmd">Get-ADDomain | Select DNSRoot, DomainSID, PDCEmulator</span><br>
<span class="cmd">Get-ADComputer -Filter * -Property IPv4Address | Select Name, IPv4Address</span><br><br>
<span class="comment"># 3. Sharphound/BloodHound 收集（会产生 LDAP 查询流量）</span><br>
<span class="cmd">Invoke-BloodHound -CollectionMethod All -Stealth -OutputDirectory C:\temp\</span><br><br>
<span class="comment"># 4. 枚举本地管理员（找横向移动目标）</span><br>
<span class="cmd">Find-LocalAdminAccess -Verbose</span>  <span class="comment"> # PowerView</span>
                </div>
                <div class="highlight-box">
                    📌 <strong>OSEP 考试关键提示：</strong>在进行 BloodHound 收集时，使用 <code>-Stealth</code> 参数可以减少 LDAP 查询的并发数，降低被 SIEM 检测的概率。优先使用 <code>DCOnly</code> 收集方式，避免扫描每台工作站。
                </div>
            </div>

            <!-- Flag 区域 -->
            <div class="flag-submit-area">
                <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L1</h4>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
                    完成以上所有步骤，深入理解 OPSEC 侦察方法论后，你已经掌握了：<br>
                    被动侦察工具链 → 低噪 Nmap 扫描策略 → 内网 AD 快速枚举方法<br><br>
                    <strong style="color: #8b5cf6;">此关卡的 Flag 为：</strong>
                </p>
                <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
                    <span class="flag-text">flag{OSEP_L1_OPSEC_Enum_NoiseReduction_Done}</span>
                </div>
                <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 16px;">在下方输入框中填入 Flag 并验证（同时可到 <a href="osep_hub.php" style="color: #8b5cf6;">OSEP 大厅</a> 提交累计积分）</p>
                <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
                    <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #8b5cf6; border-color: #8b5cf6;">
                        <i class="fa fa-check"></i> 验证 Flag
                    </button>
                </form>
                <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 15px;">' . $flag_msg . '</div>'; } ?>
                <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← OSEP 大厅</a>
                    <a href="osep_l2_phishing.php" class="btn btn-sm" style="border-radius: 6px; background: #8b5cf6; color: #fff; border: none; font-weight: 700;">下一关：钓鱼向量 →</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
