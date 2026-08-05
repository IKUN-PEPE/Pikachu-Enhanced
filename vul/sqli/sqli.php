<?php
/**
 * Pikachu-Enhanced SQL Injection Overview & Interactive Workflow
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[35] = 'active open';
$ACTIVE[36] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.sqli-hero-card {
    background: linear-gradient(135deg, #064e3b, #022c22);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.1);
}
.sqli-hero-card h1 {
    font-size: 28px;
    font-weight: 800;
    margin-top: 0;
    color: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}
.sqli-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.4);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.sqli-hero-card p {
    font-size: 15px;
    color: #a7f3d0;
    line-height: 1.7;
    max-width: 900px;
    margin-bottom: 0;
}

.sqli-workflow-section {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 30px;
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.sqli-workflow-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}
.sqli-step {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
}
.sqli-step-badge {
    width: 36px;
    height: 36px;
    background: #10b981;
    color: #ffffff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 12px;
}

.sqli-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.sqli-type-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    border-top: 4px solid #10b981;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="sqli-hero-card">
                <h1>
                    SQL 注入攻击 (SQL Injection)
                    <span class="sqli-badge">数据安全顶级威胁</span>
                </h1>
                <p>
                    SQL 注入（SQLi）被称为数据库安全领域的“头号杀手”。当 Web 应用程序直接将未经安全校验与过滤的用户输入拼接入底层数据库查询语句 (SQL Query) 中时，攻击者可通过恶意构造的 SQL 片段改变原有的查询逻辑，进而非法拖库、修改数据、甚至在特定权限下直接提权获取服务器系统权限。
                </p>
            </div>

            <!-- Workflow Visual Section -->
            <div class="sqli-workflow-section">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 25px;">
                    <i class="fa fa-database" style="color: #10b981;"></i> SQL 注入漏洞执行流程链
                </h3>
                
                <div class="sqli-workflow-grid">
                    <div class="sqli-step">
                        <div class="sqli-step-badge" style="background: #3b82f6;">1</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">传入单引号/闭合符</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">攻击者在搜索框或 ID 参数中植入 <code>1' OR '1'='1</code> 或 <code>UNION SELECT</code> 等片段。</div>
                    </div>
                    
                    <div class="sqli-step">
                        <div class="sqli-step-badge" style="background: #f59e0b;">2</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">后端字符串拼接</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">代码使用简单的字符串连接 (如 <code>"SELECT * FROM users WHERE id=" . $id</code>)，未采用预编译参数绑定。</div>
                    </div>
                    
                    <div class="sqli-step">
                        <div class="sqli-step-badge" style="background: #ef4444;">3</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">数据库语义改变</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">MySQL/PostgreSQL 引擎误将注入的文本解析为控制指令，改变原本条件判断或执行联合查询。</div>
                    </div>
                    
                    <div class="sqli-step">
                        <div class="sqli-step-badge" style="background: #10b981;">4</div>
                        <div style="font-weight:700; color:var(--text-primary); margin-bottom:8px;">敏感数据泄露/脱库</div>
                        <div style="font-size:13px; color:var(--text-secondary); line-height:1.6;">成功倒出全部管理员哈希、个人隐私明文，或者写入 Webshell (<code>INTO OUTFILE</code>)。</div>
                    </div>
                </div>
            </div>

            <!-- SQLi Types -->
            <div class="sqli-type-grid">
                <div class="sqli-type-card" style="border-top-color: #3b82f6;">
                    <h3 style="margin-top:0; font-size:18px;">📊 1. 显错与联合查询注入 (Union-based)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        页面直接回显数据库结果或报错信息。通过 <code>ORDER BY</code> 确认列数后，使用 <code>UNION SELECT 1,2,database()</code> 一次性读取目标表结构与敏感字段。
                    </p>
                </div>

                <div class="sqli-type-card" style="border-top-color: #f59e0b;">
                    <h3 style="margin-top:0; font-size:18px;">🙈 2. 盲注类型 (Boolean & Time-based Blind)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        页面不回显数据或报错。需要通过页面真假逻辑（布尔盲注）或数据库延迟响应函数（如 <code>sleep(5)</code> 时间盲注）按字符逐位爆破。
                    </p>
                </div>

                <div class="sqli-type-card" style="border-top-color: #8b5cf6;">
                    <h3 style="margin-top:0; font-size:18px;">🛡️ 3. 核心防御：预编译参数化 (PDO / Prepared)</h3>
                    <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;">
                        彻底根除 SQL 注入的唯一标准方法：使用 PDO 或 MySQLi 预编译机制 (<code>prepare()</code> + <code>bind_param()</code>)，将数据与命令彻底解耦。
                    </p>
                </div>
            </div>

            <div class="vul" style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 16px;">🎯 开始 SQL 注入关卡演练</h3>
                    <p style="margin: 0; font-size: 14px;">可以通过左侧菜单进入数字型、字符型、搜索型、盲注、宽字节等 10+ 关卡测试！</p>
                </div>
                <a href="sqli_id.php" class="btn btn-primary" style="flex-shrink: 0;">进入数字型注入 (GET) →</a>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
