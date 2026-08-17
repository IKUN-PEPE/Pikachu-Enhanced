<?php
/**
 * Pikachu-Enhanced v2.0 - 宽字节注入 (Wide-Byte SQL Injection / GBK %df) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[46] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
// 设置数据库连接编码为 GBK，触发宽字节转义吞并漏洞
@mysqli_set_charset($link, "gbk");

$html = '';
$user_name = $_POST['name'] ?? '';

if (isset($_POST['submit']) && $user_name !== '') {
    // 模拟服务端开启 magic_quotes_gpc 或使用 addslashes() 对单引号自动转义为 \'
    $escaped_name = addslashes($user_name);
    
    // 【核心漏洞点】：GBK 编码下，%df 与转义添加的反斜杠 \ (0x5c) 组合成汉字 0xdf5c (運)，从而使单引号 ' (0x27) 成功逃逸！
    $query = "select id,email from member where username='$escaped_name'";
    $result = @mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html .= "<div class='alert alert-success' style='margin:0;'>";
        $html .= "<h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 查询成功：宽字节注入匹配数据</h4><hr style='margin:8px 0; border-color:rgba(16,185,129,0.3);' />";
        while ($data = mysqli_fetch_assoc($result)) {
            $id = htmlspecialchars($data['id'] ?? '');
            $email = htmlspecialchars($data['email'] ?? '');
            $html .= "<p style='margin:4px 0;'><b>UID:</b> <code>{$id}</code> &nbsp;|&nbsp; <b>用户邮箱:</b> <code>{$email}</code></p>";
        }
        $html .= "</div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-exclamation-circle'></i> 未查询到对应会员记录，或 SQL 语法报错。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">宽字节注入 (Wide-Byte SQLi)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 宽字节注入核心原理" data-content="当输入 %df' 时，addslashes() 转义为 %df\' (即 %df%5c%27)。在 GBK 双字节编码下，%df%5c 被识别为一个汉字「運」，使得单引号 %27 摆脱了转义反斜杠的束缚成功闭合！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔤 10. 宽字节 SQL 注入漏洞攻防教学 (GBK %df 反斜杠吞并)
                        <span class="cyber-badge-chip">SQLi · GBK 字符集 · 转义字符逃逸 · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        许多历史系统为了防御 SQL 注入，会对用户输入的单引号强制使用 <code>addslashes()</code> 转义为 <code>\'</code>（十六进制为 <code>0x5c 0x27</code>）。然而如果 MySQL 连接字符集设置为 <b>GBK</b> 等双字节编码，攻击者在单引号前输入高位字节 <code>%df</code>，GBK 就会将 <code>%df%5c</code> 解析为一个完整的汉字（如「運」），<b>转义用的反斜杠 <code>\</code> 被直接“吃掉”，单引号 <code>'</code> 成功逃逸闭合原 SQL 语句！</b>
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Input & Payload Chips -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> GBK 字符集注入控制端
                            </h4>

                            <form method="POST" action="sqli_widebyte.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入用户名或宽字节 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入 %df' or 1=1 #" style="font-family:monospace;" required />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit" style="font-weight:700;">
                                                <i class="fa fa-search"></i> 发起查询
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试宽字节逃逸 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince')">
                                            <i class="fa fa-user" style="color:#06b6d4;"></i> 正常查询：<code>vince</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' or 1=1 #')">
                                            <i class="fa fa-times" style="color:#ef4444;"></i> 常规单引号注入：<code>vince' or 1=1 #</code> (被 addslashes 转义失败)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('%df\' or 1=1 #')">
                                            <i class="fa fa-bolt" style="color:#f59e0b;"></i> 宽字节万能闭合：<code>%df' or 1=1 #</code> (成功吞并反斜杠)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('%df\' union select database(),user() #')">
                                            <i class="fa fa-database" style="color:#8b5cf6;"></i> 宽字节联合提取：<code>%df' union select database(),user() #</code>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-color); margin:16px 0;" />
                            <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:14px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 数据库响应回显
                            </h4>
                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:18px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-info-circle"></i> 请在上方选择或输入 Payload 观察转义字符吞并效果
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Encoding Mechanism Explainer -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:#10b981;"></i> GBK 字节编码转换底层机理图解
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; line-height:1.8; margin-bottom:14px;">
                                1. 攻击者提交: <span style="color:#ef4444;">%df</span><span style="color:#f59e0b;">'</span> (0xdf 0x27)<br/>
                                2. addslashes 转义: <span style="color:#ef4444;">%df</span><span style="color:#38bdf8;">\</span><span style="color:#f59e0b;">'</span> (0xdf <span style="color:#38bdf8;">0x5c</span> 0x27)<br/>
                                3. GBK 字符集解析: [<span style="color:#ef4444;">0xdf</span> <span style="color:#38bdf8;">0x5c</span>] -> 汉字「<b>運</b>」<br/>
                                4. 最终 SQL 语句: <code>WHERE username='<span style="color:#10b981;">運</span><span style="color:#ef4444; font-weight:700;">' or 1=1 #</span>'</code>
                            </div>

                            <div style="background:rgba(239,68,68,0.06); border-left:4px solid #ef4444; padding:12px 14px; border-radius:0 8px 8px 0; font-size:12.5px; color:var(--text-secondary); line-height:1.6;">
                                <b>🛡️ 彻底防御方案：</b><br/>
                                1. 全面改用 <b>UTF-8 / UTF8MB4</b> 字符集，杜绝 GBK 等双字节编码。<br/>
                                2. 使用 <code>mysqli_set_charset($link, "utf8mb4")</code> 而非 <code>SET NAMES</code>。<br/>
                                3. 采用 <b>参数化预编译绑定（Prepared Statements）</b>，彻底分离数据与代码！
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_blind_t.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：时间盲注</a>
                    <a href="sqli.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回 SQL 注入演练大厅 <i class="fa fa-th-large"></i></a>
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
