<?php
/**
 * Pikachu-Enhanced Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[90] = 'active open';
$ACTIVE[92] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #3b0764, #1e1b4b);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.1);
}
.overview-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.overview-badge {
    background: rgba(168, 85, 247, 0.2);
    color: #c084fc;
    border: 1px solid #c084fc;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.overview-hero-card p {
    font-size: 15px;
    color: #e2e8f0;
    line-height: 1.7;
    max-width: 950px;
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
    border-top: 4px solid #2563eb;
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
            <div class="overview-hero-card">
                <h1>
                    PHP Deserialization PHP 反序列化漏洞 (POP 链与魔术方法)
                    <span class="overview-badge">代码执行高危靶场</span>
                </h1>
                <p>PHP 反序列化漏洞发生于应用程序直接对外部可控的数据调用 `unserialize()` 函数时。由于 PHP 类中定义了各种魔术方法（Magic Methods，如 `__destruct()`, `__wakeup()`, `__toString()`），攻击者可精心构造包含特定对象属性序列化字符串（POP 链），在对象销毁或触发时自动执行恶意代码。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 寻找反序列化点</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">找到接收字符串并传入 `unserialize()` 的接口（如 Cookie、POST 参数）。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #f59e0b;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 分析可利用类与 POP 链</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">审查后端源码，寻找定义了敏感操作（如文件写入、命令执行）的类及其魔术方法。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #ef4444;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 构造序列化 Payload</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">在本地编写 PHP 脚本实例化目标类，设置对象属性，调用 `serialize()` 输出 Payload 字符串。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 传入 Payload 触发</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">将 Payload 传入服务端，触发 `__destruct()` 等魔术方法，成功执行注入代码。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #2563eb;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🪄 1. 魔术方法 (Magic Methods)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">PHP 对象的生命周期函数。如 `__construct()`, `__destruct()`, `__wakeup()`, `__toString()`, `__get()`, `__call()`。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #ef4444;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🔗 2. POP 链 (Property-Oriented Programming)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">将多个不同类中的魔术方法和常规方法像链条一样串联起来，最终导向高危函数。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛡️ 3. 核心防御措施</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">严禁对未经校验的外部数据调用 `unserialize()`；优先使用安全的 `json_encode()` / `json_decode()`。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="unserilization.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. PHP 反序列化演练</div>
                <div class="shortcut-desc">测试构造对象 Payload 触发魔术方法</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #2563eb;"></i>
        </a>
        
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
