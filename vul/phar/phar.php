<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page (Auto-Generated)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
// Auto-matched generic active indexes if missing
$ACTIVE[180] = 'active open';
$ACTIVE[181] = 'active';
$ACTIVE[180] = 'active open';
$ACTIVE[181] = 'active'; 

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #1e3a8a, #172554);
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
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid #60a5fa;
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
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="overview-hero-card">
                <h1>
                    Phar Overview
                    <span class="overview-badge">专项演练</span>
                </h1>
                <p>传统的 PHP 反序列化漏洞需要目标代码中存在一个用户可控的 unserialize() 函数调用。但随着安全意识的提高，开发者已经很少直接对外暴露这个函数了。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 攻击与防御流程
                </h3>
                
                <div class="workflow-grid">
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #3b82f6;">1</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">搜集信息</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">分析目标架构，确定请求输入点与可控参数。</div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #f59e0b;">2</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">构造载荷</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">根据漏洞特性生成对应的攻击 Payload 或欺骗配置。</div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #ef4444;">3</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">触发执行</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">发送特制请求绕过后端校验或执行代码。</div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-icon-badge" style="background: #10b981;">4</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">实施防御</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">通过白名单过滤、最新补丁以及权限隔离从根本上修复。</div>
                    </div>
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                <div class="detail-card" style="border-top-color: #2563eb;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">核心攻击原理</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">针对特定业务或底层组件的协议与逻辑缺陷，突破数据边界控制。</p>
                </div>
                <div class="detail-card" style="border-top-color: #10b981;">
                    <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">修复与加固建议</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">始终校验输入并使用零信任架构设计网络交互层。</p>
                </div>
            </div>

            <div class="vul">
                <p style="color: var(--text-secondary); font-size: 14px;">请使用左侧菜单栏选择具体关卡进行演练。</p>
            </div>
            
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
