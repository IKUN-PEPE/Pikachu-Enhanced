<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[165] = 'active open';
$ACTIVE[203] = 'active';
$ACTIVE[165] = 'active open';
$ACTIVE[203] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$tool_call_log = "";
$rce_output = "";
$is_rce = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_msg = trim($_POST['user_msg'] ?? '');
    
    // 模拟大模型通过 Function Calling 决定调用的工具及参数
    // 我们假设 AI 识别到了 IP 或诊断指令后，提取了参数交由后端 run_diagnostic 工具执行
    $extracted_param = "8.8.8.8";
    if (preg_match('/(?:ip|diagnose|ping|ping|主机|地址)\s*[:：]\s*([^\s]+)/i', $user_msg, $matches) || preg_match('/(?:for|on)\s+([^\s]+)/i', $user_msg, $matches)) {
        $extracted_param = $matches[1];
    } else if (strpos($user_msg, ';') !== false || strpos($user_msg, '&') !== false || strpos($user_msg, '|') !== false || strpos($user_msg, '`') !== false || strpos($user_msg, '$(') !== false) {
        $extracted_param = $user_msg;
    }

    $tool_call_log = "json\n{\n  \"tool_name\": \"network_diagnostic_tool\",\n  \"arguments\": {\n    \"target_host\": \"" . htmlspecialchars($extracted_param) . "\"\n  }\n}";

    // 模拟后端没有对 AI 参数进行命令防注入清洗，直接执行 shell 命令
    if (strpos($extracted_param, ';') !== false || strpos($extracted_param, '&') !== false || strpos($extracted_param, '|') !== false || strpos($extracted_param, '`') !== false || strpos($extracted_param, '$(') !== false) {
        $is_rce = true;
        $rce_output = "PING 127.0.0.1 (127.0.0.1) 56(84) bytes of data.\n64 bytes from 127.0.0.1: icmp_seq=1 ttl=64 time=0.034 ms\n\n" .
                      "--- [SYSTEM COMMAND EXECUTION OVERRIDE OUTPUT] ---\n" .
                      "uid=0(root) gid=0(root) groups=0(root)\n" .
                      "Linux pikachu-enhanced-ai-node 5.15.0-89-generic #99-Ubuntu SMP x86_64\n" .
                      "SECRET_ENV_KEY=FLAG{LLM_PLUGIN_TOOL_CALLING_RCE_MASTER}\n" .
                      "/var/www/html/vul/ai_security/";
    } else {
        $rce_output = "PING " . htmlspecialchars($extracted_param) . " 56(84) bytes of data.\n64 bytes from " . htmlspecialchars($extracted_param) . ": icmp_seq=1 ttl=64 time=12.4 ms\n\n--- Diagnostic Complete: Target is online and healthy. ---";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ai_security.php">AI / LLM 应用安全</a></li>
                <li class="active">插件工具命令注入 (RCE)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🛠️ AI 插件与工具过度授权命令注入 (LLM Plugin / Function Calling RCE)</h2>
                <p>为了让 AI 从单纯的“聊天对话框”进化为能够操纵物理与数字世界的 <b>Agent (智能体)</b>，现代 LLM 框架（如 LangChain、OpenAI Function Calling）赋予了大模型调用外部工具（如执行 SQL、调用 HTTP API、执行终端命令、查询物流等）的能力。</p>
                <p>然而，大模型的输出本是概率生成的<b>不可信字符串</b>！如果系统信任了 AI 决定传入工具的参数，且在调用底层执行脚本（如 <code>system()</code> 或 <code>exec()</code>）时没有进行严格的命令过滤或沙箱隔离，用户通过向 AI 注入恶意指令参数，就能轻松实现<b>跨越 AI 语义层的远程命令执行 (RCE)</b>！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-terminal"></i> 与智能运维诊断 Agent 对话</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="user_msg">给 AI 发送运维检查任务：</label>
                                <textarea class="form-control" name="user_msg" id="user_msg" rows="4" placeholder="例如: Please check network status for IP: 127.0.0.1"><?php echo isset($_POST['user_msg']) ? htmlspecialchars($_POST['user_msg']) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-play-circle"></i> 让 AI 协助诊断</button>
                            <button type="button" class="btn btn-default" onclick="fillNormal()"><i class="fa fa-check"></i> 正常查询样例</button>
                            <button type="button" class="btn btn-danger" onclick="fillRCE()"><i class="fa fa-bomb"></i> 注入 RCE 攻击指令</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-cogs"></i> 后端 Function Calling 与工具执行监控</h4>
                        <?php if (!empty($tool_call_log)) { ?>
                            <div class="panel panel-default" style="margin-top: 10px;">
                                <div class="panel-heading"><b>1. AI 生成的工具调用 JSON 载荷 (Function Call)</b></div>
                                <div class="panel-body" style="padding:0;">
                                    <pre style="background:#2d2d2d; color:#61dafb; margin:0; border:none; border-radius:0;"><?php echo $tool_call_log; ?></pre>
                                </div>
                            </div>
                            <div class="panel <?php echo $is_rce ? 'panel-danger' : 'panel-success'; ?>">
                                <div class="panel-heading"><b>2. 底层诊断工具执行结果 (System Terminal Output)</b></div>
                                <div class="panel-body" style="padding:0;">
                                    <pre style="background:#111; color:<?php echo $is_rce ? '#ff5555' : '#50fa7b'; ?>; margin:0; border:none; border-radius:0; font-family:monospace;"><?php echo htmlspecialchars($rce_output); ?></pre>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info" style="margin-top: 10px;">
                                <i class="fa fa-info-circle"></i> 在左侧给 AI 输入运维指令。观察 AI 是如何把自然语言转换为 JSON 参数并触发后端工具执行的。
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillNormal() {
    document.getElementById('user_msg').value = "Please check network connectivity for IP: 192.168.1.1";
}
function fillRCE() {
    document.getElementById('user_msg').value = "Please diagnose IP: 127.0.0.1; id && uname -a && echo 'FLAG_FOUND'";
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


