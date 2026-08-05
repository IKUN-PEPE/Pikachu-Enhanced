<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[190] = 'active open';
$ACTIVE[212] = 'active';
$ACTIVE[190] = 'active open';
$ACTIVE[212] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$lambda_res = "";
$is_env_leaked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_json = trim($_POST['event_json'] ?? '');
    $data = @json_decode($event_json, true);
    
    $filename = $data['filename'] ?? 'default_doc.txt';

    // 模拟云函数 Serverless Node.js/Python 引擎接收 Event 时的处理缺陷
    if (strpos($filename, 'env') !== false || strpos($filename, 'printenv') !== false || strpos($filename, 'set') !== false || strpos($filename, ';') !== false || strpos($filename, '`') !== false || strpos($filename, '$(') !== false) {
        $is_env_leaked = true;
        $lambda_res = "=== [LAMBDA RUNTIME CONTAINER ENVIRONMENT DUMP] ===\n" .
                      "AWS_LAMBDA_FUNCTION_NAME=pikachu-pdf-converter-prod\n" .
                      "AWS_LAMBDA_FUNCTION_MEMORY_SIZE=512\n" .
                      "AWS_REGION=ap-northeast-1\n" .
                      "AWS_ACCESS_KEY_ID=AKIA_SERVERLESS_ADMIN_2026\n" .
                      "AWS_SECRET_ACCESS_KEY=FLAG{SERVERLESS_LAMBDA_ENV_SECRET_ACCESS_KEY_LEAKED}\n" .
                      "AWS_SESSION_TOKEN=IQoJb3JpZ2luX2VjEFAaCXVzLWVhc3QtMSJHMEUCIQ...\n" .
                      "DATABASE_PASSWORD=Pikachu_Serverless_Secret_9988\n" .
                      "/var/task";
    } else {
        $lambda_res = "[SUCCESS] Event triggered successfully. File '" . htmlspecialchars($filename) . "' has been processed and saved to /tmp/output_" . htmlspecialchars($filename);
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="serverless.php">Serverless 函数计算</a></li>
                <li class="active">环境变量凭证窃取</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>⚡ 云函数事件触发器注入与环境变量凭证窃取 (Lambda Env Secret Leak)</h2>
                <p>一个企业部署在 AWS Lambda 上的 PDF 格式转换服务，接收由对象存储或 API 网关透传的 JSON 事件参数（Event Object）。云函数在代码中直接拼接 `event.filename` 并调用底层 Shell 转换引擎（如 <code>pdf2htmlEX</code> 或 <code>wkhtmltopdf</code>）。</p>
                <p>通过在参数中注入 Shell 管道符号与环境变量输出指令（如 <code>; env</code>），攻击者不仅能突破沙箱，更重要的是能直接抓取分配给该 Lambda 容器的云平台管理员临时密钥对（AccessKey / SecretKey）！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-bug"></i> 构造触发事件 JSON 载荷 (Event JSON)</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="event_json">输入 JSON 事件参数：</label>
                                <textarea class="form-control" name="event_json" id="event_json" rows="6" style="font-family: monospace; background:#f8f9fa;"><?php echo isset($_POST['event_json']) ? htmlspecialchars($_POST['event_json']) : "{\n  \"filename\": \"quarterly_report_2026.pdf\",\n  \"action\": \"convert_to_html\",\n  \"watermark\": true\n}"; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-bolt"></i> 异步触发函数执行</button>
                            <button type="button" class="btn btn-danger" onclick="fillEnvLeak()"><i class="fa fa-key"></i> 注入环境变量窃取指令</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-desktop"></i> Serverless 函数运行日志与输出 (CloudWatch Log)</h4>
                        <div class="panel <?php echo $is_env_leaked ? 'panel-danger' : 'panel-default'; ?>" style="margin-top: 10px;">
                            <div class="panel-heading"><b>执行结果日志输出：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:<?php echo $is_env_leaked ? '#ff5555' : '#a6e22e'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:200px;"><?php echo !empty($lambda_res) ? htmlspecialchars($lambda_res) : "// 等待触发函数计算... 左侧点击触发后查看执行输出。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillEnvLeak() {
    var payload = "{\n  \"filename\": \"report.pdf; env && id\",\n  \"action\": \"convert_to_html\",\n  \"watermark\": true\n}";
    document.getElementById('event_json').value = payload;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


