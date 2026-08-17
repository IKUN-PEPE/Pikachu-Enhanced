<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[148] = 'active open';
$ACTIVE[149] = 'active';

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
                    Business Logic Flaws 业务逻辑安全
                    <span class="overview-badge">流程设计缺陷</span>
                </h1>
                <p>业务逻辑漏洞不同于传统的 SQL 注入或 XSS，它们通常不会在代码层面抛出异常或被 WAF 拦截。它们是由于程序的设计缺陷、流程状态机混乱导致的安全问题，如并发漏洞、密码找回跳步、完全信任前端传来的商品价格等。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #06b6d4;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 梳理业务工作流</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">抓包分析诸如购物车结算、找回密码、签到领积分等核心业务的完整 HTTP 交互流。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 测试状态机跳跃</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">不按照正常顺序请求，直接请求流程的最后一步（如直接 POST 新密码）。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #a855f7;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 测试并发与数值边界</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">使用并发发包工具同时发送 50 个提现请求，测试余额扣减是否存在多线程锁漏洞。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 逻辑绕过提权</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">成功用 0.01 元购买高价值商品，或重置任意用户密码。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #0891b2;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🛒 1. 价格篡改与前端信任</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">前端 JS 计算商品总价后发给后端，后端未与数据库单价进行二次校验。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #7c3aed;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">⚡ 2. 并发竞争 (Race Condition)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">当多个请求同时处理扣钱逻辑时，由于数据库事务未加排他锁，导致多次发卡。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🔄 3. 密码找回跳步</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">短信验证码校验后未生成有效的中间态凭据，攻击者直接伪造请求修改密码。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="logic_pwd_reset.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 密码找回漏洞</div>
                <div class="shortcut-desc">跳步绕过验证逻辑修改密码</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="logic_pay.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. 支付逻辑篡改</div>
                <div class="shortcut-desc">抓包修改商品金额</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="logic_race.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">3. 并发竞争漏洞</div>
                <div class="shortcut-desc">利用并发漏洞薅羊毛</div>
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
