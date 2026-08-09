<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Master Hub
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

.level-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.level-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-decoration: none !important;
    position: relative;
    overflow: hidden;
}
.level-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.level-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.level-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
}
.level-desc {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 18px;
}
.level-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="overview-hero-card">
                <h1>
                    🛡️ 蓝队主动防御与应急响应实战大厅
                    <span class="overview-badge">Blue Team Defense Hub</span>
                </h1>
                <p>知己知彼，百战不殆。蓝队防守模块提供了真正的流量层 WAF 规则分析、RASP 字节码插桩 Hook、Web 日志应急响应取证、蜜罐欺骗诱捕与 SIEM / Sigma 审计分析。深入理解防御边界，才能写出真正无懈可击的安全代码。</p>
            </div>

            <!-- 5 Interactive Levels Grid -->
            <h3 style="font-size: 20px; font-weight: 800; color: var(--text-primary); margin-bottom: 20px;">
                🎯 蓝队防守 5 大交互式关卡目录
            </h3>

            <div class="level-grid">
                
                <!-- Level 1 -->
                <a href="defense_waf.php" class="level-card" style="border-top: 4px solid #10b981;">
                    <div>
                        <div class="level-card-header">
                            <span class="label label-success" style="font-size: 12px; border-radius: 6px;">LEVEL 1</span>
                            <span style="font-weight: bold; color: #10b981; font-size: 13px;">流量层防御</span>
                        </div>
                        <h4 class="level-title">🛡️ WAF 流量拦截与规则检测</h4>
                        <p class="level-desc">测试 WAF 正则匹配引擎、解析 HTTP 数据包载荷、体验 403 阻断与自定规则校验。</p>
                    </div>
                    <div class="level-footer">
                        <span style="font-size: 13px; font-weight: 700; color: #10b981;">进入实验 <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>

                <!-- Level 2 -->
                <a href="defense_rasp.php" class="level-card" style="border-top: 4px solid #a855f7;">
                    <div>
                        <div class="level-card-header">
                            <span class="label label-danger" style="background-color: #a855f7; font-size: 12px; border-radius: 6px;">LEVEL 2</span>
                            <span style="font-weight: bold; color: #a855f7; font-size: 13px;">内存与 Hook 拦截</span>
                        </div>
                        <h4 class="level-title">⚡ RASP 运行时 Hook 监控</h4>
                        <p class="level-desc">体验突破 WAF 的变形 Payload 如何在调用底层 system() 时被 RASP 切断并抓取 StackTrace 堆栈。</p>
                    </div>
                    <div class="level-footer">
                        <span style="font-size: 13px; font-weight: 700; color: #a855f7;">进入实验 <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>

                <!-- Level 3 -->
                <a href="defense_log_forensics.php" class="level-card" style="border-top: 4px solid #0284c7;">
                    <div>
                        <div class="level-card-header">
                            <span class="label label-info" style="font-size: 12px; border-radius: 6px;">LEVEL 3</span>
                            <span style="font-weight: bold; color: #0284c7; font-size: 13px;">取证与溯源</span>
                        </div>
                        <h4 class="level-title">🔍 Web 入侵日志取证排查</h4>
                        <p class="level-desc">分析 Nginx access.log，提取黑客攻击 IP、Getshell 路径与被篡改数据，完成应急响应考核。</p>
                    </div>
                    <div class="level-footer">
                        <span style="font-size: 13px; font-weight: 700; color: #0284c7;">进入实验 <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>

                <!-- Level 4 -->
                <a href="defense_honeypot.php" class="level-card" style="border-top: 4px solid #f59e0b;">
                    <div>
                        <div class="level-card-header">
                            <span class="label label-warning" style="font-size: 12px; border-radius: 6px;">LEVEL 4</span>
                            <span style="font-weight: bold; color: #f59e0b; font-size: 13px;">主动欺骗</span>
                        </div>
                        <h4 class="level-title">🍯 蜜罐欺骗与 Canary 蜜标</h4>
                        <p class="level-desc">部署假后台与 db_backup 诱饵文件，触碰陷阱时触发零误报即时告警与攻击者指纹捕捉。</p>
                    </div>
                    <div class="level-footer">
                        <span style="font-size: 13px; font-weight: 700; color: #f59e0b;">进入实验 <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>

                <!-- Level 5 -->
                <a href="defense_siem.php" class="level-card" style="border-top: 4px solid #2563eb;">
                    <div>
                        <div class="level-card-header">
                            <span class="label label-primary" style="font-size: 12px; border-radius: 6px;">LEVEL 5</span>
                            <span style="font-weight: bold; color: #2563eb; font-size: 13px;">审计与规则</span>
                        </div>
                        <h4 class="level-title">📊 SIEM & Sysmon Sigma 规则</h4>
                        <p class="level-desc">分析 Windows Sysmon Event ID 1 进程创建，编写 Sigma 通用检测 YAML 规则捕获隐蔽威胁。</p>
                    </div>
                    <div class="level-footer">
                        <span style="font-size: 13px; font-weight: 700; color: #2563eb;">进入实验 <i class="fa fa-arrow-right"></i></span>
                    </div>
                </a>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
