<?php
$ACTIVE = array_fill(0, 400, '');
$ACTIVE[314] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$delivery_log = "";
$is_ssrf = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_url = trim($_POST['webhook_url'] ?? '');
    
    // 模拟服务端没有对 Webhook URL 过滤私有地址 (RFC 1918) 和云主机元数据地址
    if (stripos($target_url, '127.0.0.1') !== false || stripos($target_url, 'localhost') !== false || stripos($target_url, '169.254.169.254') !== false || stripos($target_url, '10.') === 0 || stripos($target_url, '192.168.') !== false || stripos($target_url, 'file://') === 0 || stripos($target_url, 'gopher://') === 0) {
        $is_ssrf = true;
        if (stripos($target_url, '169.254.169.254') !== false) {
            $delivery_log = "HTTP/1.1 200 OK (AWS Metadata Response)\nContent-Type: text/plain\n\n" .
                            "role-name: s3-enterprise-root-access\n" .
                            "access-key-id: AKIA_META_ROOT_SECRET_8899\n" .
                            "secret-access-key: FLAG{WEBHOOK_CALLBACK_SSRF_INTERNAL_EXPLOITED}\n" .
                            "security-token: IQoJb3JpZ2luX2VjEFAaCXVz...";
        } else {
            $delivery_log = "HTTP/1.1 200 OK (Internal Admin Console Response)\nContent-Type: text/html\n\n" .
                            "<html><head><title>Pikachu Intranet SuperAdmin Portal</title></head>\n" .
                            "<body><h1>Welcome Administrator</h1>\n<p>System Master Secret: FLAG{WEBHOOK_CALLBACK_SSRF_INTERNAL_EXPLOITED}</p>\n" .
                            "<div class='config-dump'>Database Host: 10.10.251.50:3306</div></body></html>";
        }
    } else if (filter_var($target_url, FILTER_VALIDATE_URL)) {
        $delivery_log = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n{\"status\": \"received\", \"msg\": \"Webhook delivery test successful for " . htmlspecialchars($target_url) . "\"}";
    } else {
        $delivery_log = "[ERROR] Invalid URL scheme or format. Please provide a valid http/https endpoint.";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="webhook.php">Webhook 自动化回调</a></li>
                <li class="active">Webhook 回调 SSRF 与内网探针</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🪝 Webhook 自动化事件推送 SSRF 与内网资产穿透</h2>
                <p>目标 SaaS 平台提供了一个“项目构建完成通知”功能。当用户配置好 Webhook 接收地址后，每次系统触发构建完毕事件，后台服务器就会主动使用 HTTP Client 向该地址发出一封 POST 报文报喜。</p>
                <p>后端代码实现时，开发人员仅仅使用 <code>curl_exec()</code> 发送数据，却<b>没有任何黑白名单、私有地址段屏蔽或 DNS 解析验证 (DNS Rebinding 防护)</b>！攻击者将 Webhook URL 指向公司内部高权管理系统或 AWS/阿里云元数据服务器 IP，即可将平台变成免费的 SSRF 扫描器与机密窃取跳板！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-cog"></i> 配置项目构建 Webhook 回调地址</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="webhook_url">回调通知目标 URL (Target URL)：</label>
                                <input type="text" class="form-control" name="webhook_url" id="webhook_url" placeholder="例如: http://external-logger.pikachu.local/hook" value="<?php echo isset($_POST['webhook_url']) ? htmlspecialchars($_POST['webhook_url']) : 'http://external-logger.pikachu.local/hook'; ?>"/>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-bell"></i> 发送模拟 Webhook 事件推送</button>
                            <button type="button" class="btn btn-danger" onclick="fillSSRF('local')"><i class="fa fa-home"></i> 探针 1：读取内网管理员后台</button>
                            <button type="button" class="btn btn-warning" onclick="fillSSRF('meta')" style="margin-top:5px;"><i class="fa fa-cloud"></i> 探针 2：窃取 AWS 实例元数据</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-exchange"></i> Webhook 推送历史与响应状态 (Delivery Logs)</h4>
                        <div class="panel <?php echo $is_ssrf ? 'panel-danger' : 'panel-default'; ?>" style="margin-top: 10px;">
                            <div class="panel-heading"><b>服务器 cURL 执行日志与响应体抓取：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:<?php echo $is_ssrf ? '#ff5555' : '#61dafb'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($delivery_log) ? htmlspecialchars($delivery_log) : "// 等待触发 Webhook 事件... 点击左侧按钮发起测试。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillSSRF(type) {
    if (type === 'meta') {
        document.getElementById('webhook_url').value = "http://169.254.169.254/latest/meta-data/iam/security-credentials/admin";
    } else {
        document.getElementById('webhook_url').value = "http://127.0.0.1:8080/admin/system_config.php";
    }
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


