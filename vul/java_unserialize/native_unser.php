<?php
/**
 * Pikachu-Enhanced v2.0 - Java 原生反序列化 (ObjectInputStream.readObject / ysoserial) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[90] = 'active open';
$ACTIVE[226] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

$output_trace = "";
$status_type = "";
$base64_data = $_POST['payload_b64'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($base64_data)) {
    $raw_b64 = trim($base64_data);
    $binary = base64_decode($raw_b64);
    
    // 检查 Java 序列化魔数 (Magic Bytes: 0xAC 0xED 0x00 0x05 -> rO0AB in Base64)
    if ($binary !== false && str_starts_with($binary, "\xac\xed\x00\x05")) {
        $status_type = "danger";
        $output_trace = "☕ [JVM ObjectInputStream.readObject() 执行追踪]\n" .
                        "[+] 校验成功: 识别到合法的 Java 序列化 Magic Header [0xAC 0xED 0x00 0x05]\n" .
                        "[+] 解析序列化流中的类定义: org.apache.commons.collections.map.LazyMap\n" .
                        "[+] 触发动态代理 InvocationHandler: sun.reflect.annotation.AnnotationInvocationHandler\n" .
                        "[+] 触发 Transformer 链 (ChainedTransformer):\n" .
                        "    1. ConstantTransformer(Runtime.class)\n" .
                        "    2. InvokerTransformer(\"getMethod\", new Class[]{String.class, Class[].class})\n" .
                        "    3. InvokerTransformer(\"invoke\", new Object[]{null, new Object[0]})\n" .
                        "    4. InvokerTransformer(\"exec\", new Object[]{\"id\"})\n" .
                        "[+] 最终调用: java.lang.Runtime.getRuntime().exec(\"id\")\n\n" .
                        "🚀 [JAVA NATIVE DESERIALIZATION RCE SUCCESSFUL]\n" .
                        "    uid=0(root) gid=0(root) groups=0(root)\n" .
                        "    FLAG{JAVA_NATIVE_OBJECT_INPUT_STREAM_CC1_PWNED}";
    } else {
        $status_type = "warning";
        $output_trace = "❌ [解析失败] 提交的数据不是有效的 Java 原生序列化字节流！\nJava 序列化流必须以 0xAC 0xED 0x00 0x05 开头（Base64 编码后通常为 'rO0AB...' 开头）。";
    }
}

// 预设一个标准的 ysoserial CommonsCollections1 Payload (Base64 格式)
$sample_cc1_b64 = 'rO0ABXNyADFvcmcuYXBhY2hlLmNvbW1vbnMuY29sbGVjdGlvbnMubWFwLkxhenlNYXBtYXBwZWRCeXF+AAJ4c3IAJmphdmEudXRpbC5Db2xsZWN0aW9ucyRTaW5nbGV0b25NYXDDA/oZ5A475wIAAUwACGVsZW1lbnR0ABJMamF2YS9sYW5nL09iamVjdDt4cHQABWNoZWNr';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../unserilization/unserilization.php">反序列化</a></li>
                <li class="active">Java 原生反序列化 (readObject)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 原生反序列化特征" data-content="Java 原生序列化数据以 0xAC 0xED 0x00 0x05 (Hex) 或 rO0AB (Base64) 开头！常用利用工具为 ysoserial。">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ☕ Java 原生反序列化漏洞与 ysoserial Gadget 链 (readObject RCE)
                        <span class="cyber-badge-chip">Java 安全 · readObject · CommonsCollections 链 · 350 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        Java 原生反序列化通过 <code>ObjectInputStream.readObject()</code> 还原对象。当目标应用使用了存在安全缺陷的类库（如 Apache Commons Collections、Spring Beans、Commons BeanUtils 等）时，攻击者可借助 <code>ysoserial</code> 工具生成精心构造的恶意对象序列化流（以 <code>rO0AB...</code> 开头），在服务端还原对象时无需用户干预直接执行任意系统命令！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Payload Generator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 原生序列化字节流接收端
                            </h4>

                            <form method="POST" action="native_unser.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">Base64 编码的序列化字节流 (以 rO0AB 开头)：</label>
                                    <textarea class="form-control" id="b64_input" name="payload_b64" rows="6" placeholder="rO0AB..." style="font-family:'Fira Code', monospace; font-size:12px;" required><?php echo htmlspecialchars($base64_data); ?></textarea>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速填入 ysoserial 生成的 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillB64('<?php echo addslashes($sample_cc1_b64); ?>')">
                                            <i class="fa fa-bolt" style="color:#ef4444;"></i> <b>ysoserial CommonsCollections1：</b> 执行 <code>id</code> 命令
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillB64('aGVsbG8gd29ybGQ=')">
                                            <i class="fa fa-times" style="color:#f59e0b;"></i> <b>非法字节流测试：</b> <code>hello world</code> (缺少 Java Magic)
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 触发 ObjectInputStream.readObject()
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: JVM Trace -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> JVM 反序列化调用栈追踪 (Stack Trace)
                            </h4>

                            <?php if (!empty($output_trace)): ?>
                                <pre style="background:#090d16; color:<?php echo $status_type === 'danger' ? '#10b981' : '#f59e0b'; ?>; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; line-height:1.6; max-height:280px; overflow-y:auto;"><?php echo htmlspecialchars($output_trace); ?></pre>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-coffee" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    在左侧填入以 rO0AB 开头的序列化流测试反序列化漏洞
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="fastjson_rce.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：Fastjson 反序列化</a>
                    <a href="../unserilization/unserilization.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回反序列化大厅 <i class="fa fa-th-large"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillB64(b) {
    document.getElementById('b64_input').value = b;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
