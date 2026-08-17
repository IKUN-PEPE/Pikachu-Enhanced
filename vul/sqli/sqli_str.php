<?php
/**
 * Pikachu-Enhanced v2.0 - 字符型 SQL 注入 (GET) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[38] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';

$link = connect();
$html = '';
$sqli_triggered = false;
$user_name = $_GET['name'] ?? '';

if (isset($_GET['submit']) && !empty($user_name)) {
    // 【核心漏洞点】：字符型注入在 SQL 中被单引号包裹，但未做转义或预编译：WHERE username='$name'
    $query = "select id,email from member where username='$user_name'";
    $result = @mysqli_query($link, $query);
    
    if (preg_match('/\'|union|select|database|schema|flag|#|--/i', $user_name)) {
        $sqli_triggered = true;
    }
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 查询成功：匹配到会员数据</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        while ($data = mysqli_fetch_assoc($result)) {
            $id = htmlspecialchars($data['id'] ?? '');
            $email = htmlspecialchars($data['email'] ?? '');
            $html .= "<p style='margin:4px 0;'><b>会员 ID:</b> <span class='text-primary'>{$id}</span> &nbsp;|&nbsp; <b>电子邮箱:</b> <code>{$email}</code></p>";
        }
        $html .= "</div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 用户名不存在或 SQL 执行错误。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">字符型注入 (GET) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="字符型注入需要用单引号 ' 闭合前部引号，并使用 # 或 -- 注释掉后续单引号。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 2. 字符型 SQL 注入漏洞攻防教学 (GET 方式)
                        <span class="cyber-badge-chip">SQLi · 单引号闭合 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        字符型注入（String-based SQLi）是 Web 应用中最常见的注入类型。后端在组装 SQL 语句时，使用<b>单引号（或双引号）将用户输入的参数包裹起来</b>（例如 <code>WHERE username='$name'</code>）。攻击者必须首先输入单引号 <code>'</code> 闭合左侧引号，构造自身的 SQL 逻辑片段，再使用注释符（如 <code>#</code> 或 <code>-- </code>）将右侧原有的单引号注释掉！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 用户名查询控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入要查询的用户名或闭合 Payload：</p>
                            
                            <form method="GET" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="name_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">用户名 (Username) / Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入用户名，如 kobe 或 vince' or 1=1#" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-search"></i> 查询用户
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('kobe')"><i class="fa fa-user" style="color:var(--accent);"></i> 正常用户名: kobe</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('kobe\' or 1=1#')"><i class="fa fa-bolt"></i> 单引号闭合: kobe' or 1=1#</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('kobe\' union select database(),user()#')"><i class="fa fa-database"></i> 联合注入: 读数据库信息</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 数据库实时响应结果</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 请在上方输入用户名后点击查询
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($sqli_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发字符型 SQL 注入！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功使用单引号闭合与注释符改变了原 SQL 结构。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{SQLI_STRING_ESCAPE_SUCCESS}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 单引号闭合原理</h4>
                                <div class="cyber-principle-box">
                                    <p><b>SQL 模板：</b> <code>select id,email from member where username='$name'</code></p>
                                    <p style="margin-bottom:0;">传入 <code>kobe' or 1=1#</code> ➔ 结构变为：<code>where username='kobe' or 1=1#'</code>，<code>#</code> 将后方的单引号注释掉，条件恒成立！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用测试 Payload</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">kobe' union select database(),user()#</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('kobe\' union select database(),user()#')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御代码 (参数化绑定)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 始终使用 PDO 参数化预编译
$stmt = $pdo->prepare("SELECT id, email FROM member WHERE username = ?");
$stmt->execute([$_GET['name']]);
$data = $stmt->fetchAll();
</pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) { document.getElementById('name_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
