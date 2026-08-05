<?php
/**
 * Pikachu-Enhanced v2.0 Modern Overview Page
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[165] = 'active open';
$ACTIVE[166] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.overview-hero-card {
    background: linear-gradient(135deg, #4c1d95, #2e1065);
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
    background: rgba(124, 58, 237, 0.2);
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
                    AI & LLM Application Security 大模型与智能体安全
                    <span class="overview-badge">2026 前沿 AI 安全实战</span>
                </h1>
                <p>随着生成式人工智能 (LLM) 和 AI 智能体 (Agent) 的爆火，针对大模型应用的新型攻击层出不穷。根据 OWASP Top 10 for LLM，提示词注入 (Prompt Injection)、RAG 知识库污染以及 AI Agent 工具滥用 (Tool RCE) 已成为全行业关注的核心威胁。</p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-sitemap" style="color: #2563eb;"></i> 漏洞原理与攻击演进流程链
                </h3>
                
                <div class="workflow-grid">
                    
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #06b6d4;">1</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">1. 越狱与提示词注入</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">构造越狱 Prompt (如 DAN 模式或系统指令重置)，强行突破 LLM 安全对齐防护。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #3b82f6;">2</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">2. 间接提示词注入 (Indirect)</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">在网页或 PDF 知识库中嵌入隐蔽指令，当 RAG 检索该文档时自动触发恶性指令。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #a855f7;">3</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">3. AI Agent 工具劫持</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">欺骗拥有 Exec/Shell 工具权限的 Agent，诱导其替攻击者执行 `rm -rf` 或反弹 Shell。</div>
        </div>
        
        <div class="workflow-step">
            <div class="step-icon-badge" style="background: #10b981;">4</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">4. 训练数据与隐私泄露</div>
            <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">提取 LLM 系统提示词 (System Prompt Extractor) 或无保护知识库中的敏感商业机密。</div>
        </div>
        
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="detail-grid">
                
        <div class="detail-card" style="border-top-color: #0891b2;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">🤖 1. Prompt 注入绕过 (Prompt Injection)</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">利用语义混淆与角色扮演绕过安全限制，诱导 LLM 输出恶意攻击代码或忽略系统防御规范。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #7c3aed;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">📄 2. RAG 知识库检索污染</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">在企业知识库上传包含隐蔽控制字符的文档，当用户提问触发检索时自动接管 LLM 的响应逻辑。</p>
        </div>
        
        <div class="detail-card" style="border-top-color: #10b981;">
            <h3 style="margin-top:0; font-size:18px; color:var(--text-primary);">💻 3. AI Agent 辅助代码执行 RCE</h3>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6; margin-bottom:0;">AI 智能体绑定了 Terminal 命令执行工具，攻击者利用提示词注入控制 Agent 运行恶意 Shell 命令。</p>
        </div>
        
            </div>

            <!-- Interactive Lab Shortcuts -->
            <div class="vul">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">🎯 快速进入实战关卡演练</h3>
                <div class="lab-shortcuts">
                    
        <a href="prompt_injection.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">1. Prompt 注入绕过关卡</div>
                <div class="shortcut-desc">测试通过 Prompt 越狱获取隐藏 Key</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="llm_xss.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">2. LLM XSS 渲染突破</div>
                <div class="shortcut-desc">测试诱导 LLM 渲染恶意 Markdown XSS</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="llm_plugin_rce.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">3. LLM 插件 RCE 逃逸</div>
                <div class="shortcut-desc">测试控制 AI Agent 工具执行系统命令</div>
            </div>
            <i class="fa fa-arrow-right" style="color: #0891b2;"></i>
        </a>
        
        <a href="llm_data_leakage.php" class="shortcut-card">
            <div>
                <div class="shortcut-title">4. LLM 敏感数据泄露</div>
                <div class="shortcut-desc">测试逆向提取 LLM 系统提示词与密钥</div>
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
