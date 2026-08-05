<?php
/**
 * Pikachu-Enhanced RCE Vulnerability Overview & Interactive Workflow
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[50] = 'active open';
$ACTIVE[51] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";
?>

<style>
.rce-hero-card {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
}
.rce-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.rce-badge {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.4);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.rce-hero-card p {
    font-size: 15px;
    color: #94a3b8;
    line-height: 1.7;
    max-width: 900px;
    margin-bottom: 0;
}

.workflow-section {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 30px;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.workflow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}
.workflow-step {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.2s ease;
}
.workflow-step:hover {
    transform: translateY(-3px);
}
.step-icon-badge {
    width: 36px;
    height: 36px;
    background: #2563eb;
    color: #ffffff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 12px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.detail-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    border-top: 4px solid #ef4444;
}
.detail-card.code-type {
    border-top-color: #a855f7;
}
.detail-card.modern-type {
    border-top-color: #10b981;
}

.func-tag {
    display: inline-block;
    background: var(--bg-secondary);
    color: #ec4899;
    border: 1px solid var(--border-color);
    padding: 2px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 13px;
    margin: 3px;
}

.lab-shortcuts {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.shortcut-card {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px 20px;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.shortcut-card:hover {
    border-color: #2563eb;
    transform: translateX(4px);
}
.shortcut-title {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 15px;
}
.shortcut-desc {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 2px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="rce-hero-card">
                <h1>
                    RCE 远程命令/代码执行
                    <span class="rce-badge">高危漏洞 (High Severity)</span>
                </h1>
                <p>
                    RCE (Remote Command / Code Execution) 是一种危害极大的安全漏洞。当应用系统未能对用户提交的输入进行严格的安全过滤与拼接校验，并直接传递给底层系统 Shell 函数、代码解析引擎或模板渲染模块时，攻击者即可在远程服务器上注入并执行任意操作系统命令或脚本代码，从而取得对后端的控制权。
                </p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> RCE 攻击演进与执行流程
                </h3>
                
                <div class="workflow-grid">
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #3b82f6;">1</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">用户构造输入</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">攻击者在前端表单、参数或 API 中注入系统分隔符与命令 (如 <code>; cat /etc/passwd</code> 或 <code>eval()</code> Payload)。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #f59e0b;">2</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">后端缺少过滤</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">应用程序未实施严密的白名单校验，直接将用户可控字符串拼接入系统函数或代码解析执行上下文。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #ef4444;">3</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">系统命令触发</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">Web 服务器进程权限 (如 <code>www-data</code> 或 <code>SYSTEM</code>) 独占式执行恶意命令并捕获回显结果。</div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #10b981;">4</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">服务器全面失陷</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">攻击者进一步上传 Webshell、反弹控制终端 (Reverse Shell) 并内网横向移动。</div>
                    </div>
                </div>
            </div>

            <!-- Three Categorization Cards -->
            <div class="detail-grid">
                <div class="detail-card">
                    <h3>⚡ 1. 系统命令注入 (Command Execution)</h3>
                    <p>
                        为用户提供特定的系统命令操作（如 Ping 测试、网络管理等）。未严格转义时，攻击者可通过逻辑分隔符拼接额外命令。
                    </p>
                    <div style="margin-top: 15px;">
                        <span class="func-tag">system()</span>
                        <span class="func-tag">exec()</span>
                        <span class="func-tag">shell_exec()</span>
                        <span class="func-tag">passthru()</span>
                    </div>
                </div>

                <div class="detail-card code-type">
                    <h3>💻 2. 代码解析注入 (Code Injection)</h3>
                    <p>
                        后端直接将用户输入作为脚本语言（如 PHP/Python）的一部分动态解析执行。常见于动态表达式渲染或不安全反序列化。
                    </p>
                    <div style="margin-top: 15px;">
                        <span class="func-tag">eval()</span>
                        <span class="func-tag">assert()</span>
                        <span class="func-tag">create_function()</span>
                    </div>
                </div>

                <div class="detail-card modern-type">
                    <h3>🛡️ 3. 现代攻防演进 (Modern RCE)</h3>
                    <p>
                        涵盖现代企业防护场景下的 <strong>WAF 绕过</strong> (如 <code>${IFS}</code> / 拼接 / 通配符)、<strong>SSTI 模板沙箱逃逸</strong> 以及 <strong>无回显 OOB 盲注</strong>。
                    </p>
                    <div style="margin-top: 15px;">
                        <span class="func-tag">WAF Bypass</span>
                        <span class="func-tag">SSTI</span>
                        <span class="func-tag">Blind RCE</span>
                    </div>
                </div>
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入 RCE 关卡演练 (5 大关卡)</h3>
                
                <div class="lab-shortcuts">
                    <a href="rce_ping.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">1. exec "ping"</div>
                            <div class="shortcut-desc">基础系统命令注入场景</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
                    </a>

                    <a href="rce_eval.php" class="shortcut-card">
                        <div>
                            <div class="shortcut-title">2. exec "eval"</div>
                            <div class="shortcut-desc">基础 PHP 代码动态解析注入</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
                    </a>

                    <a href="rce_bypass.php" class="shortcut-card" style="border-left: 4px solid #ef4444;">
                        <div>
                            <div class="shortcut-title" style="color: #ef4444;">3. 命令注入 WAF 绕过 ⚡</div>
                            <div class="shortcut-desc">绕过空格与敏感关键字黑名单</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #ef4444;"></i>
                    </a>

                    <a href="rce_ssti.php" class="shortcut-card" style="border-left: 4px solid #a855f7;">
                        <div>
                            <div class="shortcut-title" style="color: #a855f7;">4. SSTI 模板注入 RCE 💻</div>
                            <div class="shortcut-desc">模板渲染引擎沙箱逃逸</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #a855f7;"></i>
                    </a>

                    <a href="rce_blind.php" class="shortcut-card" style="border-left: 4px solid #f59e0b;">
                        <div>
                            <div class="shortcut-title" style="color: #f59e0b;">5. 无回显命令盲注 🙈</div>
                            <div class="shortcut-desc">时间延迟与 OOB 外带数据验证</div>
                        </div>
                        <i class="fa fa-arrow-right" style="color: #f59e0b;"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
