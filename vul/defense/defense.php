<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[221] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #064e3b, #022c22);
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
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid #34d399;
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
                    Blue Team Defensive & WAF/RASP Active Defense 蓝队防守与主动防御机制
                    <span class="overview-badge">主动防御与安全基线</span>
                </h1>
                <p>知己知彼，百战不殆。蓝队防守模块提供了真实的 WAF 规则分析、RASP (Runtime Application Self-Protection) 运行时自我保护原理研究、以及日志取证演练。深入理解防御边界，才能写出真正无懈可击的安全代码。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #06b6d4;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 识别防御组件</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">辨识应用前置的 WAF (Web Application Firewall) 流量层检测与后置 RASP 字节码插桩机制。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 规则冲突与绕过分析</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">分析正则表达式规则缺陷与 Hook 点上下文差异，探究防护死角。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #a855f7;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 实施纵深防御 (Defense-in-Depth)</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">结合输入清洗、参数化预编译、输出实体转义与最小权限进程隔离。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 日志审计与应急响应</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">提取 Nginx/PHP-FPM/WAF 审计日志，还原攻击者 Payload 链路并闭环修复。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #0891b2;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛡️ 1. WAF 流量层防护剖析</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">学习基于 HTTP 请求头、URL 路径、Body 载荷的正则匹配与语义分析防御机制。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #7c3aed;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">⚡ 2. RASP 运行时自我保护</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">通过 JVM Bytecode Instrumentation 或 PHP Extension Hook 直接在危险函数入口实施实时拦截。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🔍 3. 日志取证与安全基线</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">掌握从海量 Web 访问日志中提取攻击特征、判断漏洞利用成功与否的蓝队必备取证技能。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="defense.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 蓝队防守与 WAF/RASP 实践大厅</div>
                <div class="shortcut-desc">查看现代主动防御与攻防实战对抗</div>
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
