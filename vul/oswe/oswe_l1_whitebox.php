<?php
/**
 * OSWE L1: 白盒代码审计方法论 (100 PTS)
 */
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[262] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSWE_L1_WhiteBox_DataFlow_Audit_Done}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    $submitted = trim($_POST['user_flag']);
    if ($submitted === $correct_flag) {
        $_SESSION['oswe_flags']['flag1'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;font-weight:bold;"><i class="fa fa-check-circle"></i> 通关！【OSWE L1】白盒审计已掌握 (+100 PTS)！</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;font-weight:bold;"><i class="fa fa-times-circle"></i> Flag 错误。</div>';
    }
}
$quiz_result = '';
if (isset($_POST['submit_quiz'])) {
    $ans = strtolower(trim($_POST['quiz_answer']));
    if (strpos($ans, 'eval') !== false || strpos($ans, '危险') !== false || strpos($ans, 'exec') !== false || strpos($ans, 'system') !== false) {
        $quiz_result = 'correct';
    } else {
        $quiz_result = 'wrong';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.ctf-stage-title { color: var(--text-primary) !important; font-size: 22px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
.step-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 22px; }
.step-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.step-num { background: linear-gradient(135deg, #6366f1, #06b6d4); color: #fff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color: #64748b; }
.cmd-box .dangerous { color: #f87171; font-weight: bold; }
.cmd-box .flag-text { color: #fbbf24; font-weight: bold; }
.highlight-box { background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: 8px; padding: 14px 18px; margin: 12px 0; font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
.flag-submit-area { background: var(--bg-card); border: 2px dashed rgba(99,102,241,0.4); border-radius: 12px; padding: 24px; margin-top: 25px; text-align: center; }
.danger-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
.danger-table th { background: rgba(239,68,68,0.12); color: var(--text-primary); padding: 10px 14px; text-align: left; border: 1px solid var(--border-color); }
.danger-table td { padding: 9px 14px; border: 1px solid var(--border-color); color: var(--text-secondary); }
</style>
<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h1 class="ctf-stage-title">🔍 OSWE L1：白盒代码审计方法论
            <span style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 12px; font-size: 12px;">入门 · 100 PTS</span>
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0; line-height: 1.6;">学习白盒代码审计的系统方法论：污点分析（Taint Analysis）、危险函数定位、数据流追踪，以及 Semgrep/CodeQL 自动化工具的使用。</p>
        <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);">🔧 工具：Semgrep · CodeQL · grep · PHP/Python/Java 审计要点</span>
            <span style="background: var(--bg-secondary); padding: 4px 12px; border-radius: 8px; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-color);"><a href="oswe_hub.php" style="color: var(--text-secondary);">← 返回 OSWE 大厅</a></span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 白盒审计 vs 黑盒测试：核心差异</h3>
        <div class="cmd-box">
<span class="comment"># 黑盒测试（渗透测试）</span><br>
<span class="comment"># → 从外部发送请求，观察响应，通过 Fuzzing 发现异常</span><br>
<span class="comment"># → 无法知道代码内部逻辑，依赖工具和经验推断</span><br>
<span class="comment"># → OSCP 考试模式</span><br><br>
<span class="comment"># 白盒审计（OSWE 考试模式）</span><br>
<span class="comment"># → 直接阅读源代码，理解业务逻辑和数据流</span><br>
<span class="comment"># → 能发现"复杂逻辑漏洞"（黑盒很难发现）</span><br>
<span class="comment"># → 必须编写自动化利用脚本（Python/requests）</span><br>
<span class="comment"># → OSWE 考试提供完整应用源代码</span><br><br>
<span class="comment"># OSWE 审计流程：</span><br>
<span class="comment"># 1. 理解应用架构（语言、框架、数据库）</span><br>
<span class="comment"># 2. 识别认证入口（登录、注册、找回密码）</span><br>
<span class="comment"># 3. 寻找无需认证的数据输入点</span><br>
<span class="comment"># 4. 追踪用户输入的数据流向危险函数</span><br>
<span class="comment"># 5. 验证漏洞并编写 PoC/Exploit 脚本</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> PHP 危险函数速查表</h3>
        <table class="danger-table">
            <tr><th>危险函数类型</th><th>函数列表</th><th>触发漏洞</th><th>审计要点</th></tr>
            <tr><td><strong>代码执行</strong></td><td><code style="color:#f87171;">eval() · assert() · preg_replace('/e') · create_function()</code></td><td>RCE</td><td>参数是否包含用户输入</td></tr>
            <tr><td><strong>命令执行</strong></td><td><code style="color:#f87171;">system() · exec() · passthru() · shell_exec() · popen() · proc_open()</code></td><td>OS 命令注入</td><td>是否有 escapeshellarg/cmd</td></tr>
            <tr><td><strong>文件包含</strong></td><td><code style="color:#f87171;">include() · require() · include_once() · require_once()</code></td><td>LFI/RFI</td><td>参数是否含路径遍历字符</td></tr>
            <tr><td><strong>文件操作</strong></td><td><code style="color:#f87171;">file_get_contents() · file_put_contents() · fopen() · unlink()</code></td><td>任意文件读写</td><td>路径是否过滤 ../ 等</td></tr>
            <tr><td><strong>反序列化</strong></td><td><code style="color:#f87171;">unserialize() · yaml_parse() · json_decode(用于对象)</code></td><td>反序列化 RCE</td><td>是否有 POP 链可利用</td></tr>
            <tr><td><strong>数据库</strong></td><td><code style="color:#f87171;">mysql_query() · 未参数化的 PDO/MySQLi</code></td><td>SQL 注入</td><td>是否使用 prepared statement</td></tr>
        </table>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 污点分析（Taint Analysis）实践</h3>
        <div class="cmd-box">
<span class="comment"># 污点分析：追踪用户可控数据（Source）到危险操作（Sink）</span><br><br>
<span class="comment"># Source（数据来源）：</span><br>
<span class="comment"># $_GET · $_POST · $_COOKIE · $_REQUEST · $_FILES · $_SERVER['HTTP_*']</span><br>
<span class="comment"># python: request.args · request.form · request.json</span><br>
<span class="comment"># java: request.getParameter() · request.getHeader()</span><br><br>
<span class="comment"># Sink（危险函数）：eval · system · include · unserialize 等（见上表）</span><br><br>
<span class="comment"># 使用 grep 快速定位危险函数（白盒审计常用）：</span><br>
grep -rn "eval\s*(" . --include="*.php" | grep -v ".min.php"<br>
grep -rn "system\s*(\|exec\s*(\|shell_exec\s*(" . --include="*.php"<br>
grep -rn "unserialize\s*(" . --include="*.php"<br>
grep -rn "\$_GET\|\$_POST\|\$_REQUEST" . --include="*.php" | grep "include\|require"<br><br>
<span class="comment"># Semgrep 自动污点分析：</span><br>
semgrep --config "p/php-security" .<br>
semgrep --config "p/python" --lang python .<br>
semgrep --config "p/java" .<br><br>
<span class="comment"># CodeQL 查询示例（检测 SQL 注入路径）：</span><br>
<span class="comment"># from SqlInjection si where si.flowsToSink() select si, "SQL injection"</span>
        </div>
        <div class="highlight-box">
            💡 <strong>审计技巧：</strong>先搜索危险 Sink 函数，向上反追踪数据来源；同时从认证绕过视角寻找"跳过验证"的路径。OSWE 考试的核心是找到从未认证状态到 RCE 的完整路径。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 互动测验</h3>
        <p style="font-size: 14px; color: var(--text-secondary);">在 PHP 白盒审计中，<code>eval($data)</code> 中的 <code>$data</code> 来自 <code>$_GET['cmd']</code>，这属于哪类漏洞？请描述关键的危险函数名。</p>
        <form method="post">
            <input type="text" name="quiz_answer" class="form-control" placeholder="回答危险函数名或漏洞类型" style="max-width: 320px; border-radius: 6px; display: inline-block;">
            <button type="submit" name="submit_quiz" class="btn btn-sm" style="background: #6366f1; color: #fff; border: none; border-radius: 6px; margin-left: 8px; font-weight: 700;">验证</button>
        </form>
        <?php if ($quiz_result === 'correct'): ?>
            <div style="margin-top: 10px; color: #34d399; font-size: 13px; font-weight: 700;">✅ 正确！eval() 是最高危的代码执行函数，直接执行传入的 PHP 代码。在白盒审计中，这是优先级最高的 Sink。</div>
        <?php elseif ($quiz_result === 'wrong'): ?>
            <div style="margin-top: 10px; color: #f87171; font-size: 13px;">❌ 提示：这是 <strong>远程代码执行（RCE）</strong>漏洞，危险函数是 <code>eval()</code>，它直接执行参数中的 PHP 代码。</div>
        <?php endif; ?>
    </div>

    <div class="flag-submit-area">
        <h4 style="font-weight: 800; color: var(--text-primary); margin-top: 0;">🚩 Flag 验证 — OSWE L1</h4>
        <div class="cmd-box" style="display: block; padding: 12px 20px; margin: 0 auto 16px; text-align: left;">
            <span class="comment"># 白盒代码审计与目标系统 Flag 提取：</span><br>
            <span class="cmd">type C:\Flags\oswe_l1.txt</span>
        </div>
        <form method="post" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="flag{...}" required style="max-width: 420px; border-radius: 8px; font-family: monospace;">
            <button type="submit" name="check_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 验证 Flag</button>
        </form>
        <?php if (!empty($flag_msg)) { echo '<div style="margin-top: 10px;">' . $flag_msg . '</div>'; } ?>
        <div style="margin-top: 16px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="oswe_hub.php" class="btn btn-sm btn-default" style="border-radius: 6px;">OSWE 大厅</a>
            <a href="oswe_l2_auth_bypass.php" class="btn btn-sm" style="border-radius: 6px; background: #6366f1; color: #fff; border: none; font-weight: 700;">下一关：认证绕过 →</a>
        </div>
    </div>
</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
