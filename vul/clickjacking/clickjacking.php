<?php
/**
 * Pikachu-Enhanced v2.0 Clickjacking UI Redressing Interactive Laboratory
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[136] = 'active open';
$ACTIVE[137] = 'active';

$PIKA_ROOT_DIR = "../../";

// Protection Mode Handling
$defense_mode = isset($_GET['defense']) ? $_GET['defense'] : 'none';

// If in target iframe context, send defense headers
if (isset($_GET['iframe_target'])) {
    if ($defense_mode === 'x_frame') {
        header('X-Frame-Options: SAMEORIGIN');
    } elseif ($defense_mode === 'csp') {
        header("Content-Security-Policy: frame-ancestors 'self'");
    }
    
    // Render Target Page inside Iframe
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: sans-serif; background: #fff; margin: 0; padding: 20px; text-align: center; }
            .target-card { border: 2px dashed #ef4444; border-radius: 10px; padding: 20px; background: #fef2f2; }
            .btn-danger-custom { background: #dc2626; color: #fff; border: none; padding: 12px 24px; font-weight: bold; border-radius: 6px; cursor: pointer; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="target-card">
            <h3 style="color: #dc2626; margin-top:0;">⚠️ 银行业务 - 敏感操作确认页</h3>
            <p>确定向黑客账户 (6222****8888) 转账 <strong>￥5,000.00</strong> 吗？</p>
            <form method="post" action="clickjacking.php?iframe_target=1&defense=<?php echo $defense_mode; ?>">
                <button type="submit" name="transfer_confirm" class="btn-danger-custom">【确认转账 5000 元】</button>
            </form>
            <?php if (isset($_POST['transfer_confirm'])) { ?>
                <script>
                    window.parent.postMessage('CLICKJACKING_SUCCESS', '*');
                </script>
            <?php } ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.cj-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.1);
}
.cj-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.stage-container {
    position: relative;
    width: 100%;
    height: 320px;
    background: #f8fafc;
    border: 2px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
}
/* Phishing Decoy Layer (Background) */
.decoy-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1;
}
/* Target Iframe Layer (Overlaid with Opacity) */
.iframe-overlay {
    position: absolute;
    top: 50px;
    left: 50%;
    transform: translateX(-50%);
    width: 450px;
    height: 220px;
    border: none;
    z-index: 2;
    transition: opacity 0.2s ease;
}
.control-panel {
    background: var(--bg-secondary);
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
    border: 1px solid var(--border-color);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="cj-hero-card">
                <h1>
                    🖼️ Clickjacking 点击劫持漏洞 & 视觉欺骗 POC 实验室
                    <span class="label label-primary" style="font-size: 13px; border-radius: 20px;">UI Redressing</span>
                </h1>
                <p>点击劫持是一种视觉欺骗攻击。攻击者利用透明 <code>&lt;iframe&gt;</code> 覆盖在钓鱼诱饵网页（如“免费抽奖按钮”）之上。受害者点击“抽奖”时，实则触发了透明框架内部真实的“转账/删除账号”敏感操作。</p>
            </div>

            <!-- Controls -->
            <div class="control-panel">
                <div class="row" style="align-items: center;">
                    <div class="col-md-6">
                        <label style="font-weight: 700; color: var(--text-primary);">🛡️ 选择目标站点的安全防御模式：</label>
                        <div class="btn-group" style="display: flex; width: 100%;">
                            <a href="clickjacking.php?defense=none" class="btn btn-default <?php echo $defense_mode==='none'?'btn-danger active':'';?>" style="flex: 1; border-radius: 6px 0 0 6px; font-weight: bold;">
                                ❌ 无防御 (脆弱允许嵌入)
                            </a>
                            <a href="clickjacking.php?defense=x_frame" class="btn btn-default <?php echo $defense_mode==='x_frame'?'btn-success active':'';?>" style="flex: 1; font-weight: bold;">
                                🛡️ X-Frame-Options (SAMEORIGIN)
                            </a>
                            <a href="clickjacking.php?defense=csp" class="btn btn-default <?php echo $defense_mode==='csp'?'btn-info active':'';?>" style="flex: 1; border-radius: 0 6px 6px 0; font-weight: bold;">
                                🔒 CSP (frame-ancestors 'self')
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="font-weight: 700; color: var(--text-primary);">
                            👁️ 点击劫持透明度解密滑块 (<span id="opacity-val">50%</span> 透明):
                        </label>
                        <input type="range" id="opacity-slider" min="0" max="100" value="50" style="width: 100%; cursor: pointer;">
                        <span style="font-size: 12px; color: var(--text-secondary);">
                            滑动体验：<strong>0% (暴露隐藏转账表单)</strong> $\longleftrightarrow$ <strong>100% (黑客完全隐形欺骗)</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Attack Simulator Canvas -->
            <div class="cj-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary); display: flex; justify-content: space-between;">
                    <span><i class="fa fa-eye" style="color: #2563eb;"></i> 黑客钓鱼页面 vs 隐藏银行转账页 重叠演练画布</span>
                    <span class="label label-warning" style="font-size: 12px;">防御状态: <?php echo strtoupper($defense_mode); ?></span>
                </h3>

                <div class="stage-container">
                    
                    <!-- Background Phishing Decoy -->
                    <div class="decoy-layer">
                        <h2 style="color: #d97706; font-weight: 900; margin-top: 0;">🎁 恭喜！您获得一次【免费抽取 iPhone 16 Pro】机会！</h2>
                        <p style="color: #92400e; font-weight: bold; margin-bottom: 20px;">点击下方大按钮立即领取大奖：</p>
                        <button style="background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; border: none; padding: 16px 40px; font-size: 18px; font-weight: 900; border-radius: 50px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); cursor: pointer;">
                            🎉 点击此处 免费领取大奖 🎉
                        </button>
                    </div>

                    <!-- Overlay Target Iframe -->
                    <iframe id="target-iframe" src="clickjacking.php?iframe_target=1&defense=<?php echo $defense_mode; ?>" class="iframe-overlay" style="opacity: 0.5;"></iframe>

                </div>

                <!-- Incident Notification -->
                <div id="attack-result" style="display: none; margin-top: 20px;" class="alert alert-danger">
                    <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-warning"></i> 🚨 警告: 点击劫持攻击成功利用！</h4>
                    <p style="margin-bottom: 0;">受害者以为点击了“免费领取大奖”，实际上被透明 <code>&lt;iframe&gt;</code> 截获，成功触发了银行资金转账请求！</p>
                </div>
            </div>

            <!-- Defense Documentation -->
            <div class="cj-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-shield" style="color: #10b981;"></i> 蓝队防御加固代码实施方案</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div style="background: #0f172a; color: #34d399; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px;">
<div style="color: #94a3b8;">// 方案 1: HTTP 响应头 X-Frame-Options</div>
header('X-Frame-Options: SAMEORIGIN'); <div style="color: #94a3b8;">// 仅允许同源嵌套</div>
<div style="color: #94a3b8;">// 或直接完全禁止任何嵌套</div>
header('X-Frame-Options: DENY');
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px;">
<div style="color: #94a3b8;">// 方案 2: W3C CSP frame-ancestors</div>
header("Content-Security-Policy: frame-ancestors 'self'");
<div style="color: #94a3b8;">// 支持指定安全白名单域名</div>
header("Content-Security-Policy: frame-ancestors 'self' https://trusted.com");
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var slider = document.getElementById('opacity-slider');
    var iframe = document.getElementById('target-iframe');
    var valDisplay = document.getElementById('opacity-val');

    slider.addEventListener('input', function() {
        var op = (100 - slider.value) / 100;
        iframe.style.opacity = op;
        valDisplay.textContent = slider.value + '%';
    });

    window.addEventListener('message', function(e) {
        if (e.data === 'CLICKJACKING_SUCCESS') {
            document.getElementById('attack-result').style.display = 'block';
        }
    });
});
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
