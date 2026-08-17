<?php
/**
 * Pikachu-Enhanced v2.0 - 存储型 XSS 教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'header.php';

$link = connect();
$html = '';

// 清空留言功能
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    execute($link, "delete from message");
    header("location:xss_stored.php");
    exit();
}

// 提交留言
if (isset($_POST['submit'])) {
    if (!empty($_POST['message'])) {
        $message = escape($link, $_POST['message']);
        $query = "insert into message(content,time) values('$message',now())";
        $result = execute($link, $query);
        if (mysqli_affected_rows($link) >= 1) {
            $html = "<div class='alert alert-success' style='margin:0;'><i class='fa fa-check-circle'></i> 留言发表成功并已持久化存入数据库！</div>";
        }
    } else {
        $html = "<div class='alert alert-warning' style='margin:0;'><i class='fa fa-info-circle'></i> 留言内容不能为空！</div>";
    }
}

// 读取历史留言
$query = "select * from message order by id desc limit 20";
$result = execute($link, $query);
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="xss.php">Cross-Site Scripting</a></li>
                <li class="active">存储型 XSS (Stored XSS) 教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="提交的内容保存至数据库，每当页面加载时从数据库取出未经转义直接展示，任何访问者均会触发。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 3. 存储型 XSS 漏洞攻防教学 (持久化跨站)
                        <span class="cyber-badge-chip">高危持久化注入 · 留言板场景 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        存储型跨站脚本（Stored / Persistent XSS）是最危险的 XSS 类型之一。攻击者提交的恶意脚本会<b>直接持久化保存至服务端数据库、文件或缓存中</b>。之后每当其他受害者或管理员浏览该页面时，恶意脚本都会自动从数据库取出并直接在受害者浏览器中解析执行，极易造成大规模会话劫持、蠕虫传播或后台暗桩钓鱼！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h4 class="cyber-step-title" style="margin:0;"><i class="fa fa-edit" style="color:var(--primary);"></i> 留言板发表控制台</h4>
                                <a href="xss_stored.php?clear=1" class="btn btn-xs btn-danger" onclick="return confirm('确定清空所有历史留言吗？');"><i class="fa fa-trash"></i> 清空留言</a>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="message_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">留言内容 (Content)：</label>
                                    <textarea class="form-control" name="message" id="message_input" rows="3" placeholder="写下你的留言或 XSS 攻击 Payload..." style="font-family:monospace;"></textarea>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                    <button class="btn btn-primary" type="submit" name="submit" value="submit">
                                        <i class="fa fa-paper-plane"></i> 发表留言
                                    </button>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillInput('这是一条文明交流的正常留言。')"><i class="fa fa-star" style="color:var(--warning);"></i> 正常留言</button>
                                    <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillInput('<script>alert(\'FLAG{XSS_STORED_PERSISTENT_CRACKED}\')</script>')"><i class="fa fa-bolt"></i> 存储型 Script Payload</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillInput('<img src=x onerror=alert(document.cookie)>')"><i class="fa fa-image"></i> 存储型 Img Payload</button>
                                </div>
                            </form>

                            <?php if (!empty($html)) echo "<div style='margin-bottom:14px;'>$html</div>"; ?>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-comments" style="color:var(--accent);"></i> 留言板展示区 (实时从数据库读取)</h4>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:14px; max-height:260px; overflow-y:auto;">
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    while ($data = mysqli_fetch_assoc($result)) {
                                        echo "<div style='border-bottom:1px dashed var(--border-subtle); padding:8px 0;'>";
                                        echo "<span style='font-size:11.5px; color:var(--text-muted);'><i class='fa fa-clock-o'></i> " . htmlspecialchars($data['time']) . "</span>";
                                        echo "<div style='margin-top:4px; font-size:13.5px; color:var(--text-primary);'>";
                                        echo $data['content'];
                                        echo "</div></div>";
                                    }
                                } else {
                                    echo "<p style='color:var(--text-muted); margin:0; text-align:center; font-size:13px;'>暂无历史留言，请在上方提交测试。</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 存储型 XSS 的核心危害</h4>
                                <div class="cyber-principle-box">
                                    <p>存储型 XSS 无需诱导受害者点击构造的 URL，只需正常浏览页面便会触发。</p>
                                    <p style="margin-bottom:0;">在企业级攻防中，存储型 XSS 常用于<b>盗取管理员 Cookie、插入网页钓鱼弹窗、挂马或构造 XSS 蠕虫</b>。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 推荐实战 Payload</h4>
                                <div class="cyber-code-container">
                                    <span class="cyber-code-text">&lt;script&gt;alert('FLAG{XSS_STORED_PERSISTENT_CRACKED}')&lt;/script&gt;</span>
                                    <button class="cyber-copy-btn" onclick="copyToClipboard('&lt;script&gt;alert(\'FLAG{XSS_STORED_PERSISTENT_CRACKED}\')&lt;/script&gt;')">复制</button>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与防御加固</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全输出防御代码</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 渲染输出时强制使用 htmlspecialchars 转义
echo htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8');
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
function fillInput(val) { document.getElementById('message_input').value = val; }
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() { alert('Payload 已复制到剪贴板！'); });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
