<?php
/**
 * Pikachu-Enhanced Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[80] = 'active open';
$ACTIVE[81] = 'active';

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
                    Directory Traversal 目录遍历与路径跨越
                    <span class="overview-badge">文件系统暴露</span>
                </h1>
                <p>目录遍历漏洞是指应用系统在列出文件列表或加载指定文件夹内容时，未对输入的路径进行有效限制，或者 Web 服务器配置了允许目录索引浏览（Directory Listing）。攻击者可以遍历整个服务器目录树，查看到系统中的所有文件结构。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 寻找路径输入点</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">找到用于指定文件夹或静态资源路径的参数（如 `?dir=uploads`）。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #f59e0b;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 注入路径分隔符</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">输入 `?dir=../../` 或 `?title=../../` 尝试上开一层或多层目录。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #ef4444;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. 目录树遍历</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">观察返回的列表或报错信息，构造路径窥探全站源代码文件与系统核心配置。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #2563eb;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📁 1. 目录索引浏览 (Directory Listing)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">Web 服务器（Nginx/Apache）配置开启了 `autoindex on`，导致用户直接访问文件夹时泄露所有文件名。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #ef4444;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📂 2. 路径穿越 (Path Traversal)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">应用程序在处理路径时缺乏 `realpath()` 和白名单防范，导致 `../` 突破受限的根目录。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="dir_list.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. 目录遍历演练</div>
                <div class="shortcut-desc">测试利用 ../ 穿越读取目录树结构</div>
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
