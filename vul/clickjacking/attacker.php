<?php
/**
 * Pikachu-Enhanced v2.0 - Clickjacking (点击劫持) 恶意诱导钓鱼页面 (Attacker Decoy)
 * 模拟攻击者搭建的恶意第三方诱导网站，通过透明 iframe 覆盖在受害者敏感按钮之上
 */
$ACTIVE = array_fill(0, 150, '');
$ACTIVE[136] = 'active open';
$ACTIVE[139] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 当前测试的防御模式
$defense_mode = isset($_GET['defense']) ? $_GET['defense'] : (isset($_SESSION['cj_target_defense']) ? $_SESSION['cj_target_defense'] : 'none');
?>

<style>
/* 核心设计系统 */
.attacker-hero-banner {
    background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 50%, #991b1b 100%);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: var(--radius-xl, 16px);
    padding: 30px 34px;
    color: #ffffff;
    box-shadow: 0 14px 35px rgba(127, 29, 29, 0.25);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.attacker-hero-banner::after {
    content: "ATTACK";
    position: absolute;
    right: 25px;
    bottom: -20px;
    font-size: 110px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.04);
    pointer-events: none;
    line-height: 1;
    font-family: monospace;
}
.attacker-hero-title {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fef2f2;
}
.attacker-badge-tag {
    background: rgba(239, 68, 68, 0.25);
    color: #fecaca;
    border: 1px solid rgba(254, 202, 202, 0.3);
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
}
.attacker-hero-desc {
    font-size: 14px;
    color: #fecaca;
    line-height: 1.65;
    max-width: 980px;
    margin: 0;
}

/* 控制栏 */
.attacker-control-bar {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

/* 核心演练大画布 */
.stage-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.stage-wrapper {
    position: relative;
    width: 100%;
    max-width: 720px;
    height: 380px;
    margin: 0 auto;
    border-radius: 14px;
    overflow: hidden;
    border: 3px solid #cbd5e1;
    background: #f8fafc;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* 底层诱饵 (视觉欺骗层) */
.decoy-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fed7aa 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1;
    padding: 24px;
    text-align: center;
    user-select: none;
}
.decoy-title {
    font-size: 26px;
    font-weight: 900;
    color: #c2410c;
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(194, 65, 12, 0.15);
}
.decoy-subtitle {
    font-size: 14px;
    color: #9a3412;
    margin-bottom: 28px;
    font-weight: 500;
}
.decoy-fake-btn {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    color: #ffffff;
    font-size: 18px;
    font-weight: 900;
    padding: 14px 42px;
    border-radius: 50px;
    box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
    border: 3px solid #ffedd5;
    pointer-events: none; /* 让鼠标点击穿透至上层 iframe */
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* 顶层透明受害者 iframe (点击热区重叠层) */
.overlay-iframe {
    position: absolute;
    top: 50px;
    left: 50%;
    transform: translateX(-50%);
    width: 480px;
    height: 270px;
    border: none;
    z-index: 2;
    transition: opacity 0.1s ease;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="clickjacking.php">Clickjacking 点击劫持</a></li>
                <li class="active">恶意诱导页 (Attacker Phishing Portal)</li>
            </ul>
            <div style="float:right; display:flex; align-items:center; gap:8px;">
                <a href="target.php" class="btn btn-xs btn-primary" style="border-radius:4px; font-weight:700;">
                    <i class="ph-bold ph-crosshair"></i> 查看受害者业务中心 (target.php)
                </a>
            </div>
        </div>

        <div class="page-content">
            
            <!-- Hero Banner -->
            <div class="attacker-hero-banner">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                    <span class="attacker-badge-tag"><i class="ph-bold ph-skull"></i> 攻击者诱导端 (Phishing Decoy)</span>
                    <span class="attacker-badge-tag">UI Redressing</span>
                    <span class="attacker-badge-tag">透明 Iframe 覆盖劫持</span>
                </div>
                <h1 class="attacker-hero-title">
                    🏴‍☠️ 黑客恶意钓鱼诱导网站 (Clickjacking Attack Decoy)
                </h1>
                <p class="attacker-hero-desc">
                    在此场景中，受害者在社交媒体或钓鱼邮件中点击了一个所谓的“免费领取周年大奖”网页。受害者肉眼只能看到底层绚丽的“立即领取”大奖按钮，但攻击者在上方覆盖了一个<strong>透明度极高的受害者业务系统 iframe</strong>。受害者的一次随手点击，实际上触发了其已登录账户的“换绑安全邮箱”接口！
                </p>
            </div>

            <!-- 控制条: 透明度滑块与防御模式 -->
            <div class="attacker-control-bar">
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; flex:1;">
                    <div style="min-width:320px; flex:1;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <label style="font-size:13px; font-weight:700; color:#1e293b; margin:0;">
                                <i class="ph-bold ph-sliders" style="color:#ea580c;"></i> 点击劫持透明度透视：<span id="sliderVal" style="color:#ea580c; font-size:15px;">90% 透明</span>
                            </label>
                            <span style="font-size:11.5px; color:#64748b;">
                                [0% 揭露真实目标] $\longleftrightarrow$ [100% 黑客完全隐形]
                            </span>
                        </div>
                        <input type="range" id="opacitySlider" min="0" max="100" value="90" style="width:100%; cursor:pointer;">
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:13px; font-weight:700; color:#334155;">当前目标防御：</span>
                        <span class="badge" style="background:<?php echo $defense_mode==='none' ? '#fee2e2' : '#dcfce7'; ?>; color:<?php echo $defense_mode==='none' ? '#991b1b' : '#166534'; ?>; font-size:12px; padding:6px 12px; border-radius:6px;">
                            <?php 
                            if ($defense_mode === 'none') echo '❌ 无防御 (可被顺利劫持)';
                            elseif ($defense_mode === 'x_frame_sameorigin') echo '🛡️ X-Frame-Options: SAMEORIGIN';
                            elseif ($defense_mode === 'x_frame_deny') echo '🔒 X-Frame-Options: DENY';
                            elseif ($defense_mode === 'csp') echo '🌐 CSP: frame-ancestors';
                            elseif ($defense_mode === 'framebuster') echo '📜 JS Framebuster';
                            ?>
                        </span>
                    </div>
                </div>

                <div>
                    <a href="target.php?action=reset&defense=<?php echo urlencode($defense_mode); ?>" class="btn btn-sm btn-default" style="border-radius:6px; font-weight:600;">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i> 重置受害者状态
                    </a>
                </div>
            </div>

            <!-- 攻击捕获成功浮动横幅 -->
            <div id="attackAlert" style="display:none; margin-bottom:20px;" class="alert alert-danger">
                <div style="font-size:15px; font-weight:800; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-check-fat" style="font-size:20px; color:#dc2626;"></i> 🎯 成功达成点击劫持！受害者账户已被接管！
                </div>
                <div style="font-size:13px;">
                    受害者以为点击的是“立即免费领取”，实际上透明 iframe 截获了点击，并向 <code>target.php?action=change_email</code> 发起了请求！<br>
                    <strong>攻击结果：</strong>受害者安全邮箱已被强行篡改为 <code>attacker@pikachu.com</code>！
                    <a href="target.php" style="color:#b91c1c; font-weight:700; text-decoration:underline; margin-left:8px;">前往受害者中心核对 ➔</a>
                </div>
            </div>

            <!-- 点击劫持演练主舞台 -->
            <div class="stage-card">
                <div style="text-align:center; margin-bottom:16px;">
                    <h3 style="margin:0 0 6px 0; font-weight:800; color:#0f172a; font-size:18px;">
                        🎯 视觉欺骗重叠攻击仿真舞台 (Clickjacking Alignment Stage)
                    </h3>
                    <p style="font-size:13px; color:#64748b; margin:0;">
                        试试点击下方的“立即免费领取”按钮，体验在毫无防备下被劫持敏感操作的全过程：
                    </p>
                </div>

                <div class="stage-wrapper">
                    <!-- 底层: 诱导图层 -->
                    <div class="decoy-canvas">
                        <div class="decoy-title">🎉 恭喜获得 Pikachu 限量绝版福利！</div>
                        <div class="decoy-subtitle">恭喜您成为本月幸运用户！点击下方按钮立即兑换 <strong>Apple iPhone 16 Pro (1TB)</strong></div>
                        <div class="decoy-fake-btn">
                            <i class="ph-bold ph-gift"></i> 立即免费领取大奖 🎁
                        </div>
                        <div style="font-size:11.5px; color:#9a3412; margin-top:20px;">
                            * 礼品由活动方免费顺丰包邮，数量有限先到先得
                        </div>
                    </div>

                    <!-- 顶层: 透明嵌套的 target.php (使用 in_iframe 纯净模式完美对齐敏感换绑按钮) -->
                    <iframe id="attackerIframe" 
                            src="target.php?in_iframe=1&defense=<?php echo urlencode($defense_mode); ?>" 
                            class="overlay-iframe" 
                            style="opacity: 0.1;">
                    </iframe>
                </div>

                <div style="margin-top:18px; text-align:center;">
                    <span style="font-size:12.5px; color:#64748b;">
                        💡 <strong>原理剖析</strong>：通过 CSS <code>opacity: 0.1</code>（或 0.001），iframe 完全不可见，但其 DOM 依然处于顶层并可以接收鼠标点击事件！
                    </span>
                </div>
            </div>

            <!-- 原理与防御方案 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="stage-card">
                        <h4 style="margin:0 0 12px 0; font-weight:700; font-size:15px; color:#0f172a;">
                            <i class="ph-bold ph-crosshair-simple" style="color:#ef4444;"></i> 点击劫持与 CSRF 的本质区别
                        </h4>
                        <ul style="font-size:13px; color:#475569; line-height:1.8; padding-left:18px; margin:0;">
                            <li><strong>CSRF (跨站请求伪造)：</strong>受害者通常<strong>不需要任何感知与交互</strong>，页面通过隐蔽的 <code>&lt;img src&gt;</code> 或表单自动提交利用受害者 Cookie 发送请求。</li>
                            <li><strong>Clickjacking (点击劫持 / UI Redressing)：</strong>利用<strong>视觉欺骗与鼠标点击拦截</strong>。当目标页面具备 CSRF Token 且防护严密时，CSRF 会失效，但点击劫持依然能强迫用户“亲手点击”，完全绕过传统的 CSRF Token 防护！</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stage-card">
                        <h4 style="margin:0 0 12px 0; font-weight:700; font-size:15px; color:#0f172a;">
                            <i class="ph-bold ph-shield-check" style="color:#10b981;"></i> 现代标准防御三件套
                        </h4>
                        <ol style="font-size:13px; color:#475569; line-height:1.8; padding-left:18px; margin:0;">
                            <li><strong>X-Frame-Options 响应头：</strong>设置 <code>SAMEORIGIN</code> 仅限同源嵌套，或 <code>DENY</code> 完全禁止嵌套。</li>
                            <li><strong>CSP frame-ancestors 指令：</strong><code>Content-Security-Policy: frame-ancestors 'self'</code>，现代浏览器防嵌套最强标准。</li>
                            <li><strong>高危操作二次认证与 SameSite Cookie：</strong>要求用户二次输入口令或短信验证码，使用 <code>SameSite=Strict</code> 限制跨站凭据携带。</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var slider = document.getElementById('opacitySlider');
    var iframe = document.getElementById('attackerIframe');
    var sliderText = document.getElementById('sliderVal');
    var alertBox = document.getElementById('attackAlert');

    if (slider && iframe && sliderText) {
        slider.addEventListener('input', function() {
            var val = slider.value;
            var op = (100 - val) / 100;
            iframe.style.opacity = op;
            sliderText.textContent = val + '% 透明 (可见度 ' + Math.round(op * 100) + '%)';
        });
    }

    // 捕获受害者 iframe 触发敏感操作的消息传递
    window.addEventListener('message', function(e) {
        if (e.data === 'CLICKJACKING_SUCCESS') {
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
