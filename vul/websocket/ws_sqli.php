<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[177] = 'active open';
$ACTIVE[205] = 'active';
$ACTIVE[177] = 'active open';
$ACTIVE[205] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$ws_log = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ws_frame = trim($_POST['ws_frame'] ?? '');
    $ws_log[] = array('dir' => 'send', 'data' => $ws_frame, 'time' => date('H:i:s'));

    // 模拟后台 WebSocket 数据帧 SQL 注入逻辑
    // 如果用户发送 JSON 或纯文本 ID 包含 ' or 1=1
    if (strpos(strtolower($ws_frame), "' or 1=1") !== false || strpos(strtolower($ws_frame), '" or 1=1') !== false || strpos(strtolower($ws_frame), 'union select') !== false) {
        $resp = json_encode(array(
            'event' => 'query_result',
            'status' => 200,
            'data' => array(
                array('id' => 1, 'username' => 'admin', 'role' => 'SuperAdmin', 'hash' => '$2y$10$e9e34/qQ1z12345FLAG{WS_FRAME_SQLI_HACKED}'),
                array('id' => 2, 'username' => 'pikachu', 'role' => 'Tester', 'hash' => '$2y$10$098f6bcd4621d373cade4e832627b4f6'),
                array('id' => 3, 'username' => 'lucy', 'role' => 'HR', 'hash' => '$2y$10$ad0234829205b9033196ba818f7a872b')
            )
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $ws_log[] = array('dir' => 'recv', 'data' => $resp, 'time' => date('H:i:s'), 'alert' => 'success');
    } else if (is_numeric($ws_frame) || (json_decode($ws_frame) && isset(json_decode($ws_frame)->id) && is_numeric(json_decode($ws_frame)->id))) {
        $id = is_numeric($ws_frame) ? $ws_frame : json_decode($ws_frame)->id;
        if ($id == 1) {
            $resp = json_encode(array('event' => 'query_result', 'data' => array('id' => 1, 'username' => 'admin', 'role' => 'SuperAdmin', 'hash' => '********')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $resp = json_encode(array('event' => 'query_result', 'data' => array('id' => $id, 'username' => 'user_'.$id, 'role' => 'Guest')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        $ws_log[] = array('dir' => 'recv', 'data' => $resp, 'time' => date('H:i:s'), 'alert' => 'info');
    } else {
        $resp = json_encode(array('event' => 'error', 'msg' => "SQL Syntax Error near '".htmlspecialchars($ws_frame)."' at line 1"), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $ws_log[] = array('dir' => 'recv', 'data' => $resp, 'time' => date('H:i:s'), 'alert' => 'warning');
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="websocket.php">底层协议利用</a></li>
                <li class="active">WS 数据帧 SQL 注入</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔌 WebSocket 数据帧 SQL 注入 (WebSocket Frame SQLi)</h2>
                <p>常规 Web 漏洞扫描器和防护系统（WAF）大多专注于标准 HTTP POST/GET 流量的检查。当应用切换到 <b>WebSocket (ws:// / wss://)</b> 进行全双工实时数据传输时，所有查询交互都封装在自定义的数据帧（Frames）或 JSON 消息内。</p>
                <p>如果后端服务在接收到 WebSocket 数据帧中的字段后，未经转义直接拼接到 SQL 语句中查询数据库，就会引发危险的 <b>WebSocket 实时信道 SQL 注入</b>！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-plug"></i> 模拟 WebSocket 数据帧交互信道</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="ws_frame">向服务器发送查询指令 (JSON 或 用户ID)：</label>
                                <input type="text" class="form-control" name="ws_frame" id="ws_frame" placeholder='例如: {"action":"get_user", "id": 1} 或直接输入数字' value="<?php echo isset($_POST['ws_frame']) ? htmlspecialchars($_POST['ws_frame']) : '{"id": "1"}'; ?>"/>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> 发送 WS 数据帧</button>
                            <button type="button" class="btn btn-danger" onclick="injectSQL()"><i class="fa fa-bolt"></i> 载入 SQL 注入数据帧</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-history"></i> WebSocket 帧传输抓包监控 (Live Log)</h4>
                        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; font-family: monospace; min-height: 220px; max-height: 400px; overflow-y: auto;">
                            <?php if (empty($ws_log)) { ?>
                                <span style="color: #6a9955;">// [Connected to wss://pikachu.enhanced/api/live_query]</span><br/>
                                <span style="color: #6a9955;">// 等待发送 WebSocket 数据帧...</span>
                            <?php } else {
                                foreach ($ws_log as $log) {
                                    if ($log['dir'] === 'send') {
                                        echo "<div style='color: #4ec9b0;'><b>[SEND " . $log['time'] . "] &gt;&gt;</b> " . htmlspecialchars($log['data']) . "</div>";
                                    } else {
                                        $color = "#ce9178";
                                        if (isset($log['alert']) && $log['alert'] === 'success') $color = "#b5cea8; font-weight:bold;";
                                        if (isset($log['alert']) && $log['alert'] === 'warning') $color = "#f44747;";
                                        echo "<div style='color: " . $color . "; margin-top:5px; margin-bottom:10px;'><b>[RECV " . $log['time'] . "] &lt;&lt;</b> <pre style='background:#111; color:inherit; border:none; margin:2px 0;'>" . htmlspecialchars($log['data']) . "</pre></div>";
                                    }
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
function injectSQL() {
    document.getElementById('ws_frame').value = '{"id": "1\' or 1=1#"}';
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


