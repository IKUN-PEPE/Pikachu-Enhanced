<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[177] = 'active open';
$ACTIVE[179] = 'active';
$ACTIVE[177] = 'active open';
$ACTIVE[179] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 模拟 WebSocket 握手接口
// 在真实的 PHP/Swoole/Node.js 中，这里是一个 Upgrade: websocket 请求处理
// 为了展示漏洞，我们用普通的 HTTP 接口模拟 WebSocket 握手校验逻辑
$message = "";
$chat_history = "";

if (isset($_POST['simulate_websocket'])) {
    $origin = $_POST['origin'] ?? 'http://127.0.0.1';
    
    // 【漏洞点】：服务端根本没有检查 Origin 请求头是否在白名单内！
    // 正确的做法应该是：
    // $allowed_origins = ['http://127.0.0.1:8765'];
    // if (!in_array($origin, $allowed_origins)) { die("Origin Not Allowed"); }

    // 假设 Cookie 正确（由于 CSWSH 浏览器会自动带上 Cookie，所以这里必定验证通过）
    $is_cookie_valid = true;

    if ($is_cookie_valid) {
        $chat_history = "[System] WebSocket 连接成功！\n[Admin] 你的上月薪资是 50,000 元。\n[Admin] 你的年终奖密码是: FLAG{CSWSH_1S_AW3S0ME}";
        $message = "<div class='alert alert-danger'>🚨 警告：服务端接收了来自 Origin: <strong>" . htmlspecialchars($origin) . "</strong> 的连接请求，并下发了私密数据！</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="websocket.php">底层协议利用</a></li>
                <li class="active">跨站 WebSocket 劫持</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🪢 跨站 WebSocket 劫持 (CSWSH)</h2>
                <p>我们用这个页面模拟一个存在缺陷的 WebSocket 服务端点。</p>
                <p>由于缺乏 <code>Origin</code> 校验，任何第三方网站都可以通过 JavaScript 发起 <code>new WebSocket('ws://localhost/vul/websocket/cswsh.php')</code>。浏览器会自动带上你在 localhost 的 Cookie，从而导致你在该站点的 WebSocket 数据被第三方网站完全截获。</p>
                <hr>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-success">
                            <div class="panel-heading">正常用户访问 (本站)</div>
                            <div class="panel-body">
                                <form method="POST">
                                    <input type="hidden" name="simulate_websocket" value="1">
                                    <input type="hidden" name="origin" value="http://127.0.0.1:8765">
                                    <button type="submit" class="btn btn-primary">建立 WebSocket 连接</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="panel panel-danger">
                            <div class="panel-heading">黑客恶意网站模拟 (evil.com)</div>
                            <div class="panel-body">
                                <form method="POST">
                                    <input type="hidden" name="simulate_websocket" value="1">
                                    <!-- 模拟黑客页面发出的跨域请求，Origin 会被浏览器自动设置为 evil.com -->
                                    <input type="hidden" name="origin" value="http://evil.com">
                                    <button type="submit" class="btn btn-danger">在 evil.com 上请求建立连接</button>
                                </form>
                                <p class="text-muted" style="margin-top:10px;">点击此按钮，模拟你在浏览 evil.com 时，该网站恶意脚本向靶场发起的 WebSocket 握手。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($chat_history): ?>
                <div class="row" style="margin-top:20px;">
                    <div class="col-sm-12">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h4 class="widget-title">WebSocket 数据流传输内容</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <pre><?php echo $chat_history; ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


