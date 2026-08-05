<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[177] = 'active open';
$ACTIVE[206] = 'active';
$ACTIVE[177] = 'active open';
$ACTIVE[206] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$stream_logs = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $channel = trim($_POST['channel'] ?? '');
    
    if (stripos($channel, 'admin') !== false || stripos($channel, 'audit') !== false || stripos($channel, 'secret') !== false || stripos($channel, 'root') !== false) {
        $stream_logs[] = "[SYSLOG-STREAM] Subscribed successfully to channel: <b style='color:#f00;'>" . htmlspecialchars($channel) . "</b> (UNAUTHORIZED ACCESS TRIGGERED)";
        $stream_logs[] = "[AUDIT] [10:01:05] Admin User logged in from IP 10.10.251.50 using SSH key.";
        $stream_logs[] = "[AUDIT] [10:02:11] Database backup dumped to /var/backups/db_backup.sql.gz";
        $stream_logs[] = "[ALERT] [10:03:45] System secret token refreshed: FLAG{WS_UNAUTH_STREAM_LISTEN_MASTER}";
        $stream_logs[] = "[AUDIT] [10:05:00] API Key assigned to service: ak_prod_998877665544";
    } else {
        $stream_logs[] = "[SYSLOG-STREAM] Subscribed to public channel: <b>" . htmlspecialchars($channel) . "</b>";
        $stream_logs[] = "[NOTICE] [10:00:00] Welcome to Pikachu-Enhanced internal messaging service.";
        $stream_logs[] = "[NOTICE] [10:01:00] System maintenance scheduled for this Friday at 02:00 UTC.";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="websocket.php">底层协议利用</a></li>
                <li class="active">WS 未授权敏感流订阅</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📡 WebSocket 未授权敏感数据流订阅 (Unauthorized Stream Subscription)</h2>
                <p>在基于发布 / 订阅 (Pub/Sub) 模式的架构中，客户端通过 WebSocket 连接连接到实时广播推送接口。如果服务器在响应用户发送的订阅指令（如 <code>subscribe_channel</code>）时，没有验证当前会话的权限与角色，就会导致<b>任意用户订阅核心管理员事件流</b>。</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-rss"></i> 订阅实时事件频段 (Pub/Sub)</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="channel">输入要监听的事件流频道名称：</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="channel" id="channel" value="<?php echo isset($_POST['channel']) ? htmlspecialchars($_POST['channel']) : 'public_notice'; ?>"/>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-play"></i> 开始监听</button>
                                    </span>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" onclick="setChannel('public_notice')">监听公共广播 (public_notice)</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="setChannel('admin_system_audit')"><i class="fa fa-user-secret"></i> 越权监听系统审计流 (admin_system_audit)</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-terminal"></i> 实时推送流终端输出 (WebSocket Output Stream)</h4>
                        <div style="background: #000; color: #00ff00; padding: 15px; border-radius: 4px; font-family: monospace; min-height: 200px;">
                            <?php if (empty($stream_logs)) { ?>
                                <span>// 尚未订阅任何频道。点击上方按钮发送订阅指令。</span>
                            <?php } else {
                                foreach ($stream_logs as $log) {
                                    echo "<div style='margin-bottom: 8px; border-bottom: 1px dashed #222; padding-bottom: 4px;'>" . $log . "</div>";
                                }
                            } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function setChannel(ch) {
    document.getElementById('channel').value = ch;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


