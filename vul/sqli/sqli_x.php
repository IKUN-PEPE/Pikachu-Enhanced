<?php
/**
 * Pikachu-Enhanced v2.0 - xx 型 SQL 注入 (GET) 教学演练 (带单引号与括号闭合)
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[40] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
$html = '';
$user_name = $_GET['name'] ?? '';

if (isset($_GET['submit']) && $user_name !== '') {
    // 【核心漏洞点】：xx 型注入使用 ('$name') 括号包裹，若未过滤单引号与小括号将导致闭合注入
    $query = "select id,email from member where username=('$user_name')";
    $result = @mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 查询成功：匹配到会员数据</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        while ($data = mysqli_fetch_assoc($result)) {
            $id = htmlspecialchars($data['id'] ?? '');
            $email = htmlspecialchars($data['email'] ?? '');
            $html .= "<p style='margin:4px 0;'><b>UID:</b> <code>{$id}</code> &nbsp;|&nbsp; <b>用户邮箱:</b> <code>{$email}</code></p>";
        }
        $html .= "</div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 未查询到对应会员记录，或 SQL 语法报错（可能括号未正确闭合）。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">xx 型注入 (GET) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 xx 型注入闭合技巧" data-content="后端 SQL 形式为 WHERE username=('$name')。闭合关键：需要同时闭合单引号与右圆括号：') or 1=1 #">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 4. xx 型 SQL 注入漏洞攻防教学 (('name') 复杂结构闭合)
                        <span class="cyber-badge-chip">SQLi · 括号闭合匹配 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        所谓“xx 型注入”，是指开发者在构造 SQL 查询时，使用了包含括号、多层引号等复合结构（例如 <code>WHERE username=('$input')</code> 或 <code>WHERE (username='$input')</code>）。仅输入单引号 <code>'</code> 无法通过 SQL 语法解析（会报右括号缺失错误）。测试人员必须先猜解或报错探测出其外围结构，使用 <code>')</code> 完成闭合！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 会员账号精确查询控制台
                            </h4>

                            <form method="GET" action="sqli_x.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入用户名或 xx 闭合 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入用户名，如 vince" style="font-family:monospace;" required />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit" style="font-weight:700;">
                                                <i class="fa fa-search"></i> 查询数据
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试 xx 型闭合 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince')">
                                            <i class="fa fa-user" style="color:#06b6d4;"></i> 正常查询：<code>vince</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('\') or 1=1 #')">
                                            <i class="fa fa-bolt" style="color:#f59e0b;"></i> 括号闭合：<code>') or 1=1 #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('\') union select database(),user() #')">
                                            <i class="fa fa-database" style="color:#8b5cf6;"></i> 联合查询：<code>') union select database(),user() #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('\') union select 1,group_concat(column_name) from information_schema.columns where table_name=\'member\' #')">
                                            <i class="fa fa-columns" style="color:#ef4444;"></i> 提取 member 表全部字段
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-color); margin:16px 0;" />
                            <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:14px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 查询结果回显
                            </h4>
                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:18px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-info-circle"></i> 请在上方输入用户名或测试闭合载荷
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: SQL Visualizer -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:#10b981;"></i> 底层 SQL 语句拼接动态透视
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12.5px; line-height:1.7; margin-bottom:14px;">
                                <span style="color:#6366f1; font-weight:700;">SELECT</span> id, email <span style="color:#6366f1; font-weight:700;">FROM</span> member<br/>
                                <span style="color:#6366f1; font-weight:700;">WHERE</span> username = (<span style="color:#10b981;">'</span><span style="color:#ef4444; font-weight:700;"><?php echo htmlspecialchars($user_name ?: 'USER_INPUT'); ?></span><span style="color:#10b981;">'</span>)
                            </div>

                            <div style="background:rgba(2,132,199,0.06); border-left:4px solid #0284c7; padding:12px 14px; border-radius:0 8px 8px 0; font-size:12.5px; color:var(--text-secondary); line-height:1.6;">
                                <b>💡 闭合原理深度拆解：</b><br/>
                                当输入 <code>') or 1=1 #</code> 时：<br/>
                                拼接后的 SQL 变为：<br/>
                                <code>WHERE username = ('<span style="color:#ef4444;">') or 1=1 #</span>')</code><br/>
                                前面的 <code>')</code> 恰好闭合了左侧的 <code>('</code>，后面的 <code>#</code> 注释掉了原 SQL 的 <code>')</code>，成功完成闭合！
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_search.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：搜索型注入</a>
                    <a href="sqli_del.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：删除型 / 报错注入 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) {
    document.getElementById('name_input').value = val;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
