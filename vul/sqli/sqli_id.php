<?php
/**
 * Pikachu-Enhanced v2.0 - 数字型 SQL 注入 (POST) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';

$link = connect();
$html = '';
$sqli_triggered = false;
$user_id = $_POST['id'] ?? '';

if (isset($_POST['submit']) && !empty($user_id)) {
    // 【核心漏洞点】：数字型注入未做 intval() 过滤或预编译，直接拼接入 SQL
    $query = "select username,email from member where id=$user_id";
    $result = @mysqli_query($link, $query);
    
    if (preg_match('/union|select|database|schema|flag|information_schema|--|#/i', $user_id)) {
        $sqli_triggered = true;
    }
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 查询成功：匹配到会员数据</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        while ($data = mysqli_fetch_assoc($result)) {
            $username = htmlspecialchars($data['username'] ?? '');
            $email = htmlspecialchars($data['email'] ?? '');
            $html .= "<p style='margin:4px 0;'><b>用户名:</b> <span class='text-primary'>{$username}</span> &nbsp;|&nbsp; <b>电子邮箱:</b> <code>{$email}</code></p>";
        }
        $html .= "</div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 未查询到对应会员记录，或 SQL 语法执行报错。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">数字型注入 (POST) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="数字型注入无需闭合单引号。直接在参数中输入 UNION SELECT 1,database() 或 OR 1=1 即可。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 数字型 SQL 注入漏洞攻防教学 (POST 方式)
                        <span class="cyber-badge-chip">SQLi · 联合查询 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        数字型注入（Integer-based SQLi）是指后端接收的参数在底层数据库中属于<b>整型数值（Integer）</b>，SQL 语句中<b>没有使用单引号或双引号包裹该参数</b>（如 <code>WHERE id=$id</code>）。因此攻击者无需进行闭合引号操作，直接拼接 SQL 关键字（如 <code>UNION SELECT</code>、<code>OR 1=1</code>）即可直接改变原 SQL 逻辑！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-terminal" style="color:var(--primary);"></i> 会员编号查询控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入要查询的会员 ID 或 SQL 注入 Payload：</p>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="id_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">选择或输入会员 ID / Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="id_input" name="id" value="<?php echo htmlspecialchars($user_id); ?>" placeholder="输入数字 ID，如 1 或 1 or 1=1" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                                <i class="fa fa-search"></i> 执行 SQL 查询
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('1')"><i class="fa fa-user" style="color:var(--accent);"></i> 正常 ID: 1</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('1 or 1=1')"><i class="fa fa-bolt"></i> 万能语句: 1 or 1=1</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('1 union select database(),user()')"><i class="fa fa-database"></i> 联合注入: 读库名与用户</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('1 union select group_concat(table_name),2 from information_schema.tables where table_schema=database()')"><i class="fa fa-table"></i> 联合注入: 查当前库所有表</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 数据库实时响应结果</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 请在上方选择或输入会员 ID 后点击查询
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($sqli_triggered): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功触发数字型 SQL 注入！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功使用逻辑布尔或联合查询绕过了单一记录检索限制。恭喜获得通过凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{SQLI_NUMERIC_UNION_EXPLOITED}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 漏洞原理与 SQL 拼接剖析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>执行 SQL 模板：</b> <code>select username,email from member where id=$id</code></p>
                                    <p style="margin-bottom:0;">当传入 <code>$id = 1 or 1=1</code> 时，整句 SQL 变为 <code>where id=1 or 1=1</code>，逻辑条件恒为真，数据库返回整张表的全部用户记录！</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 常用测试 Payload</h4>
                                <div class="cyber-code-container" style="margin-bottom:8px;">
                                    <span class="cyber-code-text">1 union select database(),user()</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('1 union select database(),user()')">复制</button>
                                </div>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">1 union select group_concat(table_name),2 from information_schema.tables where table_schema=database()</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('1 union select group_concat(table_name),2 from information_schema.tables where table_schema=database()')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御方案</h4>
                                <details class="cyber-details-box">
                                    <summary class="cyber-details-summary">查看漏洞代码 (Vulnerable Code)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ❌ 危险：直接将字符串拼接进 SQL 语句
$id = $_POST['id'];
$query = "select username,email from member where id=$id";
$result = execute($link, $query);
</pre>
                                    </div>
                                </details>

                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">查看安全防御代码 (PDO 预编译绑定)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 方案 1：强制整型转换
$id = intval($_POST['id']);

// ✅ 方案 2：PDO 参数化预编译查询（行业最佳实践）
$stmt = $pdo->prepare("SELECT username, email FROM member WHERE id = ?");
$stmt->execute([$_POST['id']]);
$rows = $stmt->fetchAll();
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
function fillInput(val) { document.getElementById('id_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
