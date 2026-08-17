<?php
/**
 * Pikachu-Enhanced v2.0 - 搜索型 SQL 注入 (GET) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[39] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
$html = '';
$search_name = $_GET['name'] ?? '';

if (isset($_GET['submit']) && $search_name !== '') {
    // 【核心漏洞点】：搜索型注入使用 LIKE '%$name%' 模糊匹配，若未过滤单引号与百分号将导致 SQL 注入
    $query = "select username,id,email from member where username like '%$search_name%'";
    $result = @mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 搜索成功：包含 [<b>" . htmlspecialchars($search_name) . "</b>] 的会员数据</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        while ($data = mysqli_fetch_assoc($result)) {
            $username = htmlspecialchars($data['username'] ?? '');
            $uid = htmlspecialchars($data['id'] ?? '');
            $email = htmlspecialchars($data['email'] ?? '');
            $html .= "<p style='margin:4px 0;'><b>UID:</b> <code>{$uid}</code> &nbsp;|&nbsp; <b>用户名:</b> <span class='text-primary font-weight-bold'>{$username}</span> &nbsp;|&nbsp; <b>邮箱:</b> <code>{$email}</code></p>";
        }
        $html .= "</div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 未搜索到匹配的用户记录，或 SQL 查询报错。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">搜索型注入 (GET) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 搜索型注入闭合技巧" data-content="后端 SQL 形式为 WHERE username LIKE '%$name%'。闭合关键：先用 %' 闭合前半部分，再添加注入语句，最后用 %' 闭合后半部分或直接用 #/-- 注释掉后半部分。例如：%' or 1=1 #">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔍 3. 搜索型 SQL 注入漏洞攻防教学 (LIKE '%name%')
                        <span class="cyber-badge-chip">SQLi · 模糊查询闭合 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        搜索型注入常出现在网站的站内检索、商品搜索或用户名模糊查询功能中。后端 SQL 语句通常构造为 <code>SELECT ... WHERE column LIKE '%$input%'</code>。攻击者需要构造 <code>%'</code> 先闭合前面的单引号与通配符，随后拼接 <code>UNION SELECT</code> 或逻辑条件，最后使用 <code>#</code>、<code>--+</code> 或 <code>and '%'='</code> 闭合原语句尾部！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-search" style="color:var(--primary);"></i> 会员模糊搜索控制台
                            </h4>

                            <form method="GET" action="sqli_search.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入搜索关键词或 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="search_input" name="name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="输入用户名关键词，如 vince" style="font-family:monospace;" required />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit" style="font-weight:700;">
                                                <i class="fa fa-search"></i> 模糊搜索
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试搜索型 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince')">
                                            <i class="fa fa-user" style="color:#06b6d4;"></i> 正常搜索：<code>vince</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('%\' or 1=1 #')">
                                            <i class="fa fa-bolt" style="color:#f59e0b;"></i> 万能闭合：<code>%' or 1=1 #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('%\' union select database(),user(),version() #')">
                                            <i class="fa fa-database" style="color:#8b5cf6;"></i> 联合提取：<code>%' union select database(),user(),version() #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('%\' union select group_concat(table_name),2,3 from information_schema.tables where table_schema=database() #')">
                                            <i class="fa fa-table" style="color:#ef4444;"></i> 枚举当前库所有表名
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
                                    <i class="fa fa-info-circle"></i> 请在上方输入搜索词并点击搜索
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
                                <span style="color:#6366f1; font-weight:700;">SELECT</span> username, id, email <span style="color:#6366f1; font-weight:700;">FROM</span> member<br/>
                                <span style="color:#6366f1; font-weight:700;">WHERE</span> username <span style="color:#6366f1; font-weight:700;">LIKE</span> '<span style="color:#10b981;">%</span><span style="color:#ef4444; font-weight:700;"><?php echo htmlspecialchars($search_name ?: 'USER_INPUT'); ?></span><span style="color:#10b981;">%</span>'
                            </div>

                            <div style="background:rgba(2,132,199,0.06); border-left:4px solid #0284c7; padding:12px 14px; border-radius:0 8px 8px 0; font-size:12.5px; color:var(--text-secondary); line-height:1.6;">
                                <b>💡 闭合原理深度拆解：</b><br/>
                                当输入 <code>%' or 1=1 #</code> 时：<br/>
                                拼接后的 SQL 变为：<br/>
                                <code>WHERE username LIKE '%' <span style="color:#ef4444;">or 1=1 #</span>%'</code><br/>
                                <code>#</code> 成功将原 SQL 最后的单引号和百分号完全注释，使得 <code>or 1=1</code> 恒真条件成立，返回全表数据！
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_str.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：字符型 SQL 注入</a>
                    <a href="sqli_x.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：xx 型注入 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillInput(val) {
    document.getElementById('search_input').value = val;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
