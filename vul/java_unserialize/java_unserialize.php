<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[103] = 'active open';
$ACTIVE[104] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #78350f, #451a03);
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
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
    border: 1px solid #f59e0b;
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
                    Java Deserialization Java 反序列化漏洞
                    <span class="overview-badge">企业级灾难靶场</span>
                </h1>
                <p>Java 反序列化漏洞是企业级应用中最严重的安全威胁之一 (如 WebLogic, Shiro, Fastjson)。当应用从不受信任的来源接收序列化字节流 (如 RMI, JMX, HTTP 协议) 时，攻击者可利用 Apache Commons Collections 等第三方依赖链 (Gadget Chains) 在反序列化期间执行任意命令。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #06b6d4;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 捕获序列化流数据</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">寻找以 `aced 0005` (Hex) 或 `rO0AB` (Base64) 开头的 Java 原生反序列化数据包。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 识别目标框架与依赖</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">确认目标是否使用了存在已知利用链的依赖 (如 Commons Collections, Spring, Hibernate)。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #a855f7;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 生成 ysoserial Payload</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">使用 ysoserial 工具生成针对特定依赖链的恶意反序列化对象 Payload。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 发送 Payload 触发 RCE</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">将其注入到目标反序列化入口 (如 Shiro Cookie 或 RMI 端口)，直接控制服务器。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #0891b2;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">⚡ 1. ysoserial 利用链 (Gadget Chains)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">不同于 PHP 的魔术方法，Java 反序列化利用链极其复杂，通常通过动态代理和反射将无害方法串联成代码执行。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #7c3aed;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🧩 2. Fastjson / Jackson 漏洞</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">非原生序列化。利用 `autoType` 特性在解析 JSON 时反序列化恶意的 JdbcRowSetImpl 或 TemplatesImpl 类。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛡️ 3. RASP 与白名单防御</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">防范该漏洞最好的方案是不再信任未经验证的流，使用黑白名单限制可反序列化的类，或使用 RASP 底层拦截。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="fastjson_rce.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. Fastjson 反序列化</div>
                <div class="shortcut-desc">测试通过 JNDI 注入执行命令</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="native_unser.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. 原生 Java 反序列化</div>
                <div class="shortcut-desc">测试 Commons Collections 利用链</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
