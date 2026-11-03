<?php
/**
 * Pikachu-Enhanced Enterprise Cyber Range
 * Business Logic Security: Price Tampering & Negative Quantity Arbitrage
 * Standardized per landing-page-design & redesign-existing-projects
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "price_tamper.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[148] = 'active open';
    $ACTIVE[150] = 'active';
}

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100.00; // 默认初始体验余额 100 元
}
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

// 模拟高端数码商城商品目录 (标准库)
$products = [
    '101' => [
        'id' => '101',
        'name' => 'Apple iPhone 15 Pro Max',
        'subname' => '原色钛金属 · 1TB 超大存储 · 旗舰机皇',
        'sku' => 'APL-IP15PM-1TB-TI',
        'price' => 8999.00,
        'badge' => '数码机皇',
        'icon' => 'ph-device-mobile',
        'desc' => 'A17 Pro 3nm 工艺芯片 / 航空级钛金属边框 / 潜望式 5 倍光学变焦 / 4800 万像素计算摄影',
        'specs' => ['1TB 存储', '钛金属', 'A17 Pro', 'USB-C 10Gbps']
    ],
    '102' => [
        'id' => '102',
        'name' => 'MacBook Pro 16" (M3 Max)',
        'subname' => '深空黑色 · 64GB 统一内存 · 2TB SSD',
        'sku' => 'APL-MBP16-M3MAX-64G',
        'price' => 27999.00,
        'badge' => '性能怪兽',
        'icon' => 'ph-laptop',
        'desc' => '16 核 CPU / 40 核 GPU / 液态视网膜 XDR 屏 / 22 小时超强续航 / 专业级音响矩阵',
        'specs' => ['64GB 内存', '2TB SSD', 'M3 Max', '120Hz XDR']
    ],
    '103' => [
        'id' => '103',
        'name' => 'Apple Watch Ultra 2',
        'subname' => '49 毫米钛金属表壳 · 橙色海洋表带',
        'sku' => 'APL-AWU2-OCEAN',
        'price' => 6499.00,
        'badge' => '极限探险',
        'icon' => 'ph-watch',
        'desc' => '双频精密 GPS / 3000 尼特最高峰值亮度 / 100 米防水深度认证 / 专为极限环境打造',
        'specs' => ['49mm 钛金属', '3000 尼特', '水深计', '蜂窝网络']
    ]
];

$message = null;
$msg_type = 'info';
$flag = null;

// 处理重置
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $_SESSION['balance'] = 100.00;
    $_SESSION['orders'] = [];
    header("Location: price_tamper.php");
    exit;
}

// 处理结算提交
if (isset($_POST['buy'])) {
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '101';
    $product = $products[$product_id] ?? $products['101'];
    
    // 【核心漏洞逻辑】：服务端轻信客户端提交的单价、数量或折扣！
    // 正规系统应该仅读取 product_id 并从数据库安全查询价格，严禁客户端参与价格运算。
    $submitted_price = isset($_POST['price']) ? (float)$_POST['price'] : $product['price'];
    $submitted_quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $submitted_discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0.00;
    
    // 计算实付总价: (单价 * 数量) - 折扣
    $calculated_total = ($submitted_price * $submitted_quantity) - $submitted_discount;
    
    // 攻防特征判定
    $is_tampered = false;
    $tamper_types = [];
    
    if (abs($submitted_price - $product['price']) > 0.001) {
        $is_tampered = true;
        $tamper_types[] = "单价篡改 (原价 ¥" . number_format($product['price'], 2) . " ➔ 篡改价 ¥" . number_format($submitted_price, 2) . ")";
    }
    if ($submitted_quantity <= 0) {
        $is_tampered = true;
        $tamper_types[] = "负数/零数量套利 (恶意数量: {$submitted_quantity})";
    }
    if ($submitted_discount > 0) {
        $is_tampered = true;
        $tamper_types[] = "未授权优惠券溢出 (恶意抵扣: ¥" . number_format($submitted_discount, 2) . ")";
    }
    
    // 支付逻辑分支
    if ($calculated_total < 0 && $is_tampered) {
        // 经典的负数订单反向充值套利漏洞！
        $refund_amount = abs($calculated_total);
        $_SESSION['balance'] += $refund_amount;
        $order_id = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
        $_SESSION['orders'][] = [
            'order_id' => $order_id,
            'product_name' => $product['name'],
            'price' => $submitted_price,
            'quantity' => $submitted_quantity,
            'total' => $calculated_total,
            'tampered' => true,
            'types' => $tamper_types,
            'time' => date('Y-m-d H:i:s')
        ];
        $message = "🚨 <strong>【触发严重业务漏洞：负数反向充值套利】</strong> 服务端未校验数量与总额的正数性，计算得出订单总价为负数 (<strong>¥" . number_format($calculated_total, 2) . "</strong>)！扣款逻辑 <code>balance -= total</code> 负负得正，系统不仅没扣钱，反向给你的钱包转账了 <strong>¥" . number_format($refund_amount, 2) . "</strong>！";
        $msg_type = "danger";
        $flag = "flag{Pikachu_Negative_Price_Arbitrage_Exploited_2026}";
    } elseif ($calculated_total <= 0 && !$is_tampered) {
        $message = "订单金额异常，防欺诈系统阻断！";
        $msg_type = "warning";
    } elseif ($_SESSION['balance'] >= $calculated_total) {
        $_SESSION['balance'] -= $calculated_total;
        $order_id = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
        $_SESSION['orders'][] = [
            'order_id' => $order_id,
            'product_name' => $product['name'],
            'price' => $submitted_price,
            'quantity' => $submitted_quantity,
            'total' => $calculated_total,
            'tampered' => $is_tampered,
            'types' => $tamper_types,
            'time' => date('Y-m-d H:i:s')
        ];
        
        if ($is_tampered) {
            $message = "🎉 <strong>【价格篡改越权成功】</strong> 你成功通过逻辑漏洞以 <strong>¥" . number_format($calculated_total, 2) . "</strong> 超低成本购得价值 <strong>¥" . number_format($product['price'] * max(1, $submitted_quantity), 2) . "</strong> 的 {$product['name']}！";
            $msg_type = "success";
            $flag = "flag{Pikachu_Business_Logic_Price_Tamper_Bypassed_2026}";
        } else {
            $message = "✅ 购买成功！支付 ¥" . number_format($calculated_total, 2) . "，商品已成功发货。";
            $msg_type = "info";
        }
    } else {
        $need = $calculated_total - $_SESSION['balance'];
        $message = "❌ <strong>余额不足！</strong> 订单结算需要 <strong>¥" . number_format($calculated_total, 2) . "</strong>，但你的账户余额仅有 <strong>¥" . number_format($_SESSION['balance'], 2) . "</strong> (还差 ¥" . number_format($need, 2) . ")。<br><span style='font-size:12.5px; opacity:0.85;'>💡 提示：官方原价无法负担，请按 F12 审查元素修改隐藏字段 <code>price</code>，或使用 Burp Suite 拦截修改！</span>";
        $msg_type = "warning";
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
/* 核心设计系统规范：Geist/Inter 风格与扁平化层次 */
.price-hero-banner {
    background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #451a03 100%);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: var(--radius-xl, 16px);
    padding: 32px 36px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.price-hero-banner::after {
    content: "LOGIC";
    position: absolute;
    right: 20px;
    bottom: -25px;
    font-size: 130px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    pointer-events: none;
    line-height: 1;
    font-family: monospace;
}
.price-hero-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f8fafc;
}
.price-badge-tag {
    background: rgba(245, 158, 11, 0.18);
    color: #fde68a;
    border: 1px solid rgba(253, 230, 138, 0.3);
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.price-hero-desc {
    font-size: 14.5px;
    color: #cbd5e1;
    line-height: 1.7;
    max-width: 960px;
    margin: 0 0 20px 0;
}

/* 账户状态控制条 */
.wallet-status-bar {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 18px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.wallet-info-group {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.wallet-metric {
    display: flex;
    flex-direction: column;
}
.wallet-metric-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 2px;
}
.wallet-metric-value {
    font-size: 22px;
    font-weight: 800;
    font-family: monospace;
    color: #0f172a;
}
.wallet-metric-value.balance {
    color: #059669;
}

/* 交互卡片容器 */
.card-surface {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.card-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* 商品卡片设计 */
.product-showcase {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 18px;
    align-items: flex-start;
}
.product-icon-box {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.product-meta {
    flex: 1;
}
.product-name-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.product-name {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.product-badge {
    font-size: 11px;
    font-weight: 600;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    padding: 2px 8px;
    border-radius: 6px;
}
.product-subname {
    font-size: 12.5px;
    color: #64748b;
    margin: 0 0 8px 0;
}
.product-specs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.spec-chip {
    font-size: 11.5px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: monospace;
}
.product-price-row {
    display: flex;
    align-items: baseline;
    gap: 10px;
}
.price-symbol {
    font-size: 16px;
    font-weight: 700;
    color: #e11d48;
}
.price-amount {
    font-size: 26px;
    font-weight: 800;
    color: #e11d48;
    font-family: monospace;
}
.price-original-label {
    font-size: 12px;
    color: #94a3b8;
}

/* 结算明细表 */
.checkout-table {
    width: 100%;
    margin-bottom: 18px;
    font-size: 13.5px;
}
.checkout-table td {
    padding: 8px 0;
    border-bottom: 1px dashed #e2e8f0;
}
.checkout-table td.label-col {
    color: #64748b;
}
.checkout-table td.val-col {
    text-align: right;
    font-family: monospace;
    font-weight: 600;
    color: #0f172a;
}
.checkout-table tr.total-row td {
    border-bottom: none;
    border-top: 2px solid #e2e8f0;
    padding-top: 12px;
    font-size: 15px;
}
.checkout-table tr.total-row td.val-col {
    font-size: 20px;
    color: #e11d48;
    font-weight: 800;
}

/* 抓包观察器 */
.inspector-panel {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.6;
    overflow-x: auto;
    margin-top: 10px;
}
.inspector-title {
    color: #94a3b8;
    font-size: 12px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

/* 快捷篡改实验台 */
.sandbox-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 18px;
}
.sandbox-title {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sandbox-btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.sandbox-chip-btn {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.sandbox-chip-btn:hover {
    border-color: #f59e0b;
    color: #d97706;
    background: #fffbeb;
}

/* 选项卡导航 */
.nav-tabs-custom {
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px;
    display: flex;
    gap: 8px;
}
.nav-tabs-custom button {
    background: none;
    border: none;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}
.nav-tabs-custom button.active {
    color: #d97706;
    border-bottom-color: #d97706;
}
.tab-pane-custom {
    display: none;
}
.tab-pane-custom.active {
    display: block;
}

/* 订单历史台账 */
.order-table {
    width: 100%;
    font-size: 13px;
    border-collapse: collapse;
}
.order-table th {
    background: #f8fafc;
    color: #475569;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}
.order-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="<?php echo $PIKA_ROOT_DIR;?>vul/logic/logic.php">业务逻辑安全</a></li>
                <li class="active">价格篡改与订单逻辑漏洞</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 头部 Hero Banner -->
            <div class="price-hero-banner">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                    <span class="price-badge-tag"><i class="ph-bold ph-shield-warning"></i> OWASP Business Logic Top 10</span>
                    <span class="price-badge-tag" style="background:rgba(16, 185, 129, 0.15); color:#6ee7b7; border-color:rgba(110, 231, 183, 0.3);"><i class="ph-bold ph-shopping-bag-open"></i> 电商与支付结算风控靶标</span>
                    <span class="price-badge-tag" style="background:rgba(239, 68, 68, 0.15); color:#fca5a5; border-color:rgba(252, 165, 165, 0.3);"><i class="ph-bold ph-bug"></i> 越权改价 / 负数充值</span>
                </div>
                <h1 class="price-hero-title">
                    <i class="ph-bold ph-currency-cny" style="color:#fbbf24;"></i>
                    价格篡改与订单逻辑漏洞实战 (Price & Order Tampering)
                </h1>
                <p class="price-hero-desc">
                    在电商商城、在线支付与计费接口中，如果服务端未在后端重新校验商品真实单价，而是直接<strong>盲目信任客户端提交的金额（如隐藏表单 <code>price</code>、<code>total</code>、数量 <code>quantity</code> 或优惠抵扣 <code>discount</code>）</strong>，攻击者即可利用浏览器的 F12 审查元素或 Burp Suite 拦截数据包，以 0.01 元或 1 元任意购买数万元的贵重商品，甚至通过提交负数数量引发“反向充值套利”漏洞！
                </p>
            </div>

            <!-- 用户账户状态与余额 Bar -->
            <div class="wallet-status-bar">
                <div class="wallet-info-group">
                    <div class="wallet-metric">
                        <span class="wallet-metric-label"><i class="ph-bold ph-wallet"></i> 我的钱包余额 (Wallet Balance)</span>
                        <span class="wallet-metric-value balance">¥ <?php echo number_format($_SESSION['balance'], 2); ?></span>
                    </div>
                    <div style="width:1px; height:32px; background:#e2e8f0;"></div>
                    <div class="wallet-metric">
                        <span class="wallet-metric-label"><i class="ph-bold ph-user"></i> 用户身份级别</span>
                        <span class="wallet-metric-value" style="font-size:16px;">普通体验用户 (VIP 1)</span>
                    </div>
                    <div style="width:1px; height:32px; background:#e2e8f0;"></div>
                    <div class="wallet-metric">
                        <span class="wallet-metric-label"><i class="ph-bold ph-receipt"></i> 历史完成订单</span>
                        <span class="wallet-metric-value" style="font-size:16px;"><?php echo count($_SESSION['orders']); ?> 笔</span>
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="?action=reset" class="btn btn-warning btn-sm" style="border-radius:8px; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-arrows-counter-clockwise"></i> 一键重置测试数据 (恢复 ¥100.00)
                    </a>
                </div>
            </div>

            <!-- 提示与返回消息 -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $msg_type; ?>" style="border-radius:12px; margin-bottom:24px; font-size:14px; line-height:1.7;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($flag)): ?>
                <div class="alert alert-success" style="border-radius:12px; margin-bottom:24px; border:2px dashed #10b981; background:#ecfdf5;">
                    <div style="font-size:16px; font-weight:800; color:#065f46; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                        <i class="ph-bold ph-trophy" style="font-size:22px; color:#f59e0b;"></i> 恭喜捕获逻辑漏洞靶标 Flag！
                    </div>
                    <div style="font-family:monospace; font-size:15px; font-weight:700; color:#047857; background:#ffffff; padding:8px 14px; border-radius:6px; display:inline-block; margin-top:4px;">
                        <?php echo $flag; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- 左侧: 数码商城收银台与实时抓包调试 -->
                <div class="col-md-7">
                    <!-- 结算表单卡片 -->
                    <div class="card-surface">
                        <h4 class="card-title">
                            <i class="ph-bold ph-shopping-cart-simple" style="color:#d97706;"></i>
                            数码旗舰商城官方收银台 (Checkout Counter)
                        </h4>

                        <!-- 商品展示 -->
                        <div class="product-showcase">
                            <div class="product-icon-box">
                                <i class="ph-bold <?php echo $products['101']['icon']; ?>"></i>
                            </div>
                            <div class="product-meta">
                                <div class="product-name-row">
                                    <h3 class="product-name"><?php echo $products['101']['name']; ?></h3>
                                    <span class="product-badge"><?php echo $products['101']['badge']; ?></span>
                                </div>
                                <p class="product-subname"><?php echo $products['101']['subname']; ?></p>
                                <div class="product-specs">
                                    <?php foreach ($products['101']['specs'] as $spec): ?>
                                        <span class="spec-chip"><?php echo $spec; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="product-price-row">
                                    <span class="price-symbol">¥</span>
                                    <span class="price-amount" id="displayUnitPrice"><?php echo number_format($products['101']['price'], 2); ?></span>
                                    <span class="price-original-label">(官方统一指导零售价)</span>
                                </div>
                            </div>
                        </div>

                        <!-- 快捷攻防测试 Payload 注入台 -->
                        <div class="sandbox-card">
                            <div class="sandbox-title">
                                <span><i class="ph-bold ph-lightning" style="color:#d97706;"></i> 快捷攻防调试台 (一键模拟篡改 Payload):</span>
                                <span style="font-size:11.5px; font-weight:normal; color:#64748b;">支持直接在输入框修改或点击快捷填充</span>
                            </div>
                            <div class="sandbox-btn-group">
                                <button type="button" class="sandbox-chip-btn" onclick="applyTamperPayload(8999, 1, 0)">
                                    <i class="ph-bold ph-arrows-clockwise"></i> 正常官方原价 (¥8,999)
                                </button>
                                <button type="button" class="sandbox-chip-btn" onclick="applyTamperPayload(1, 1, 0)">
                                    <i class="ph-bold ph-tag"></i> 漏洞方案1: 改单价为 1.00 元
                                </button>
                                <button type="button" class="sandbox-chip-btn" onclick="applyTamperPayload(0.01, 1, 0)">
                                    <i class="ph-bold ph-coin"></i> 漏洞方案2: 改单价为 0.01 元
                                </button>
                                <button type="button" class="sandbox-chip-btn" onclick="applyTamperPayload(8999, -1, 0)">
                                    <i class="ph-bold ph-arrow-fat-lines-up" style="color:#dc2626;"></i> 漏洞方案3: 数量篡改为 -1 (反向充值)
                                </button>
                                <button type="button" class="sandbox-chip-btn" onclick="applyTamperPayload(8999, 1, 8998)">
                                    <i class="ph-bold ph-ticket"></i> 漏洞方案4: 恶意优惠券抵扣 ¥8998
                                </button>
                            </div>
                        </div>

                        <!-- 真实提交表单 -->
                        <form method="POST" action="price_tamper.php" id="orderCheckoutForm" onsubmit="updateWirePayload()">
                            <input type="hidden" name="product_id" id="form_product_id" value="101">

                            <!-- 脆弱的隐藏字段：服务端直接读取此字段作为计费单价！ -->
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-size:13px; font-weight:600; color:#334155; margin-bottom:4px; display:flex; justify-content:space-between;">
                                    <span>商品结算单价 (Hidden Parameter: <code>price</code>):</span>
                                    <span style="color:#dc2626; font-size:11.5px;"><i class="ph-bold ph-warning"></i> 漏洞核心：该值原为隐藏字段，现已透传为可调试控件</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon" style="background:#f8fafc; font-weight:700;">¥</span>
                                    <input type="number" step="0.01" name="price" id="form_price" class="form-control" value="8999.00" required oninput="recalcOrder()">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group" style="margin-bottom:14px;">
                                        <label style="font-size:13px; font-weight:600; color:#334155; margin-bottom:4px; display:block;">
                                            购买数量 (Parameter: <code>quantity</code>):
                                        </label>
                                        <input type="number" name="quantity" id="form_quantity" class="form-control" value="1" required oninput="recalcOrder()">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group" style="margin-bottom:14px;">
                                        <label style="font-size:13px; font-weight:600; color:#334155; margin-bottom:4px; display:block;">
                                            优惠抵扣金额 (Parameter: <code>discount</code>):
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon" style="background:#f8fafc; font-weight:700;">-¥</span>
                                            <input type="number" step="0.01" name="discount" id="form_discount" class="form-control" value="0.00" oninput="recalcOrder()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 结算订单汇总清单 -->
                            <table class="checkout-table">
                                <tr>
                                    <td class="label-col">商品官方标价</td>
                                    <td class="val-col">¥ 8,999.00</td>
                                </tr>
                                <tr>
                                    <td class="label-col">当前表单单价 (Submitted Unit Price)</td>
                                    <td class="val-col" id="calcUnitPrice">¥ 8,999.00</td>
                                </tr>
                                <tr>
                                    <td class="label-col">选购数量 (Quantity)</td>
                                    <td class="val-col" id="calcQty">1 件</td>
                                </tr>
                                <tr>
                                    <td class="label-col">优惠抵扣抵减 (Coupon Discount)</td>
                                    <td class="val-col" id="calcDiscount">- ¥ 0.00</td>
                                </tr>
                                <tr class="total-row">
                                    <td class="label-col"><strong>本次实付总结算金额 (Total Payable)</strong></td>
                                    <td class="val-col" id="calcTotal">¥ 8,999.00</td>
                                </tr>
                            </table>

                            <button type="submit" name="buy" class="btn btn-primary btn-block" style="border-radius:10px; font-weight:700; padding:12px; font-size:15px; background:linear-gradient(135deg, #d97706, #b45309); border:none; box-shadow:0 4px 12px rgba(217,119,6,0.3);">
                                <i class="ph-bold ph-credit-card"></i> 确认结算并提交支付 (Submit Order)
                            </button>
                        </form>
                    </div>

                    <!-- 实时抓包网络请求流观察器 -->
                    <div class="card-surface" style="padding:20px;">
                        <h4 class="card-title" style="margin-bottom:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i>
                            实时 HTTP 抓包数据流观察器 (Burp Suite Wire Inspector)
                        </h4>
                        <p style="font-size:12.5px; color:#64748b; margin:0 0 10px 0;">
                            实时监测当前表单在发出 POST 请求时，网卡发出的真实 HTTP 请求正文。在实战中打开 Burp Suite 拦截到的也是相同的数据结构：
                        </p>
                        <div class="inspector-title"><i class="ph-bold ph-paper-plane-tilt"></i> HTTP POST 请求报文</div>
                        <div class="inspector-panel" id="inspPostWire">POST /vul/logic/price_tamper.php HTTP/1.1
Host: 127.0.0.1:8765
Content-Type: application/x-www-form-urlencoded

product_id=101&price=8999.00&quantity=1&discount=0.00&buy=1</div>
                    </div>

                    <!-- 历史订单台账 -->
                    <?php if (!empty($_SESSION['orders'])): ?>
                        <div class="card-surface">
                            <h4 class="card-title">
                                <i class="ph-bold ph-receipt" style="color:#059669;"></i>
                                我的已购订单台账 (Purchase Order Ledger)
                            </h4>
                            <div style="overflow-x:auto;">
                                <table class="order-table">
                                    <thead>
                                        <tr>
                                            <th>订单编号</th>
                                            <th>购买商品</th>
                                            <th>结算单价</th>
                                            <th>数量</th>
                                            <th>实付金额</th>
                                            <th>审计状态</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($_SESSION['orders']) as $ord): ?>
                                            <tr>
                                                <td style="font-family:monospace;"><?php echo $ord['order_id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($ord['product_name']); ?></strong></td>
                                                <td style="font-family:monospace;">¥<?php echo number_format($ord['price'], 2); ?></td>
                                                <td style="font-family:monospace;"><?php echo $ord['quantity']; ?></td>
                                                <td style="font-family:monospace; font-weight:700; color:<?php echo $ord['total'] < 0 ? '#dc2626' : '#059669'; ?>;">
                                                    ¥<?php echo number_format($ord['total'], 2); ?>
                                                </td>
                                                <td>
                                                    <?php if ($ord['tampered']): ?>
                                                        <span class="label label-danger" style="border-radius:4px;"><i class="ph-bold ph-warning"></i> 逻辑篡改</span>
                                                    <?php else: ?>
                                                        <span class="label label-success" style="border-radius:4px;"><i class="ph-bold ph-check"></i> 正常支付</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 右侧: 渗透测试与深度安全指引 -->
                <div class="col-md-5">
                    <div class="card-surface">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-cursor-click"></i> F12 前端修改</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-wrench"></i> Burp 拦截篡改</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-trend-down"></i> 负数反向充值</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 业务安全防御</button>
                        </div>

                        <!-- Tab 1: F12 前端修改 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 浏览器开发者工具 (F12) 零门槛改价</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:18px;">
                                <li><strong>审查隐藏字段：</strong>在真实开发中，价格通常被放在隐藏表单中：
                                    <pre style="margin-top:6px; background:#f8fafc; border-radius:6px; padding:10px; font-size:12px; color:#0f172a;">&lt;input type="hidden" name="price" value="8999.00"&gt;</pre>
                                </li>
                                <li><strong>DOM 节点修改：</strong>在 Chrome/Edge 中按 <kbd>F12</kbd>，点击左上角箭头审查该字段，双击 <code>value="8999.00"</code> 修改为 <code>1.00</code>。</li>
                                <li><strong>提交结算：</strong>由于浏览器提交的是当前的 DOM 属性值，修改后的价格 <code>1.00</code> 将直接被 POST 发送给服务端完成结算！</li>
                            </ol>
                            <div class="alert alert-warning" style="font-size:12.5px; border-radius:8px; margin-top:14px;">
                                💡 <strong>核心教训</strong>：前端的任何 HTML 限制、disabled 属性、hidden 属性对黑客来说都是完全透明可控的！
                            </div>
                        </div>

                        <!-- Tab 2: Burp 拦截篡改 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite 中间人拦截实战</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                当系统使用了前端 JavaScript 校验（例如前端 JS 强制检查价格不得小于 8000）时，F12 修改可能被前端拦截，此时必须使用 <strong>Burp Suite</strong> 绕过前端限制：
                            </p>
                            <ol style="font-size:13px; color:#334155; line-height:1.7; padding-left:18px;">
                                <li>在浏览器配置代理至 <code>127.0.0.1:8080</code>。</li>
                                <li>打开 Burp Suite -> <strong>Proxy -> Intercept is on</strong>。</li>
                                <li>在商城页面点击「立即购买」，请求将被 Burp 挂起。</li>
                                <li>在截获的原始 HTTP 请求体中，将 <code>price=8999.00</code> 修改为 <code>price=0.01</code>。</li>
                                <li>点击 <strong>Forward</strong> 放行，请求绕过了所有前端 JS 检验，直接抵达服务端达成利用！</li>
                            </ol>
                        </div>

                        <!-- Tab 3: 负数反向充值 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🚨 负数金额与反向充值套利 (Integer/Logic Bug)</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                这类漏洞在真实金融和电商系统中屡见不鲜：
                            </p>
                            <ul style="font-size:13px; color:#475569; line-height:1.7;">
                                <li><strong>公式缺陷</strong>：系统结算公式通常为：<br>
                                    <code>实付 = (单价 × 数量) - 优惠金额</code>
                                </li>
                                <li><strong>未校验正数</strong>：如果攻击者将 <code>quantity</code> 篡改为 <code>-1</code>，或者购买 1 件昂贵商品同时购买 <code>-100</code> 件便宜配件，总额就会变成负数（如 <code>-¥8,999</code>）。</li>
                                <li><strong>扣款逻辑反转</strong>：后端的钱包扣除代码为：<br>
                                    <code>$balance = $balance - $total</code><br>
                                    当 <code>$total = -8999</code> 时，<code>balance - (-8999) = balance + 8999</code>！服务端逻辑不仅没扣钱，反向给攻击者账户充值了巨额资金！
                                </li>
                            </ul>
                        </div>

                        <!-- Tab 4: 业务安全防御 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 生产级支付系统架构防御规范</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>价格绝不信任客户端</strong>：客户端提交订单时，仅允许上报 <code>sku_id</code> 与 <code>quantity</code>。商品单价、折扣规则必须<strong>由服务端在事务中实时从数据库检索</strong>并计算。<br><br>
                                2. <strong>强类型与严格边界断言</strong>：
                                <pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; line-height:1.5;"><code>if (!is_int($quantity) || $quantity <= 0 || $quantity > 99) {
    throw new InvalidOrderException("购买数量异常");
}</code></pre>
                                3. <strong>订单摘要防篡改签名 (HMAC)</strong>：若涉及跨系统支付网关回调，订单关键要素必须附带 HMAC-SHA256 签名，服务端验签失败立刻废弃交易。<br><br>
                                4. <strong>分布式事务与原子扣款</strong>：支付扣款与库存扣减必须置于数据库严格事务与行级排他锁（悲观锁/乐观锁）中，杜绝并发竞争超卖。
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function applyTamperPayload(price, qty, discount) {
    document.getElementById('form_price').value = parseFloat(price).toFixed(2);
    document.getElementById('form_quantity').value = parseInt(qty);
    document.getElementById('form_discount').value = parseFloat(discount).toFixed(2);
    recalcOrder();
}

function recalcOrder() {
    const price = parseFloat(document.getElementById('form_price').value) || 0;
    const qty = parseInt(document.getElementById('form_quantity').value) || 0;
    const discount = parseFloat(document.getElementById('form_discount').value) || 0;

    const total = (price * qty) - discount;

    document.getElementById('calcUnitPrice').textContent = '¥ ' + price.toFixed(2);
    document.getElementById('calcQty').textContent = qty + ' 件';
    document.getElementById('calcDiscount').textContent = '- ¥ ' + discount.toFixed(2);

    const totalElem = document.getElementById('calcTotal');
    totalElem.textContent = '¥ ' + total.toFixed(2);
    if (total < 0) {
        totalElem.style.color = '#dc2626';
        totalElem.innerHTML = '¥ ' + total.toFixed(2) + ' <small style="color:#dc2626; font-size:12px;">(负数反向充值!)</small>';
    } else if (total <= 100) {
        totalElem.style.color = '#059669';
    } else {
        totalElem.style.color = '#e11d48';
    }

    updateWirePayload();
}

function updateWirePayload() {
    const prodId = document.getElementById('form_product_id').value;
    const price = document.getElementById('form_price').value;
    const qty = document.getElementById('form_quantity').value;
    const discount = document.getElementById('form_discount').value;

    const wireText = `POST /vul/logic/price_tamper.php HTTP/1.1\nHost: 127.0.0.1:8765\nContent-Type: application/x-www-form-urlencoded\n\nproduct_id=${prodId}&price=${price}&quantity=${qty}&discount=${discount}&buy=1`;
    document.getElementById('inspPostWire').textContent = wireText;
}

// 初始化
recalcOrder();
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
