<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[148] = 'active open';
$ACTIVE[150] = 'active';
$ACTIVE[148] = 'active open';
$ACTIVE[150] = 'active';
$PIKA_ROOT_DIR = "../../";

session_start();

if(!isset($_SESSION['balance'])){
    $_SESSION['balance'] = 100.0; // Initial balance
}

$message = '';
$product_name = "iPhone 15 Pro Max";
$real_price = 8999.0;

if(isset($_POST['buy'])){
    // Vulnerability: Trusting client input for price!
    // The server should ONLY read the product ID from the client, 
    // and query the real price from the database.
    $submitted_price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    
    if($submitted_price <= 0){
        $message = "价格异常，防黑客拦截生效！";
    } elseif($_SESSION['balance'] >= $submitted_price){
        $_SESSION['balance'] -= $submitted_price;
        $message = "🎉 购买成功！你花了 {$submitted_price} 元买到了 {$product_name}！剩余余额：{$_SESSION['balance']}";
    } else {
        $message = "❌ 余额不足！你需要 {$submitted_price} 元，但只有 {$_SESSION['balance']} 元。";
    }
}

if(isset($_GET['action']) && $_GET['action'] === 'reset'){
    $_SESSION['balance'] = 100.0;
    header("Location: price_tamper.php");
    exit;
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="logic.php">业务逻辑安全</a></li>
                <li class="active">价格篡改</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p>价格篡改 (Price Tampering)</p>
                <p>你的当前余额为：<strong><?php echo $_SESSION['balance']; ?> 元</strong></p>
                <?php if($message !== ''): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="thumbnail">
                            <div class="caption">
                                <h3><?php echo $product_name; ?></h3>
                                <p>官方原价：<?php echo $real_price; ?> 元</p>
                                <form method="POST" action="price_tamper.php">
                                    <input type="hidden" name="product_id" value="101">
                                    <!-- Vulnerability: Price is hidden but modifiable by client -->
                                    <input type="hidden" name="price" value="<?php echo $real_price; ?>">
                                    <p>由于你的余额只有 100 元，正常点击肯定买不起。</p>
                                    <p>提示：你可以使用浏览器的 F12 开发者工具，把这个隐藏的 price 字段改小一点（比如改成 1 元）。</p>
                                    <p><button type="submit" name="buy" class="btn btn-primary" role="button">立即购买</button></p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <p><a href="?action=reset" class="btn btn-xs btn-warning">重置余额为 100</a></p>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


