<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub) 真实攻防实战靶场总览
 * 场景原型: 某大型集团企业企业级综合运营管理系统 (app.corp-internal.com / prod.corp-internal.com / sso.corp-internal.com:8092)
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理身份快速切换 (用于靶场教学演练)
if (isset($_GET['switch_role'])) {
    $role = $_GET['switch_role'];
    $users = tscs_get_users();
    if ($role === 'logout') {
        $_SESSION['tscs_current_user'] = null;
    } elseif (isset($users[$role])) {
        $_SESSION['tscs_current_user'] = $users[$role];
    }
    header("Location: index.php");
    exit;
}

$currentUser = tscs_get_current_user();

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[351] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
/* 核心设计系统规范：Geist/Inter 风格与工控/企业综合业务系统质感 */
.tscs-hero-banner {
    background: linear-gradient(135deg, #090d16 0%, #0c4a6e 45%, #082f49 100%);
    border: 1px solid rgba(14, 165, 233, 0.35);
    border-radius: var(--radius-xl, 16px);
    padding: 34px 38px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.3);
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.tscs-hero-banner::after {
    content: "CORPHUB 2026";
    position: absolute;
    right: 25px;
    bottom: -25px;
    font-size: 110px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    pointer-events: none;
    line-height: 1;
    font-family: monospace;
}
.tscs-hero-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f8fafc;
}
.tscs-badge-tag {
    background: rgba(14, 165, 233, 0.2);
    color: #bae6fd;
    border: 1px solid rgba(186, 230, 253, 0.35);
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
}
.tscs-hero-desc {
    font-size: 14.5px;
    color: #cbd5e1;
    line-height: 1.7;
    max-width: 1020px;
    margin: 0 0 20px 0;
}

/* 角色与状态控制栏 */
.tscs-role-bar {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

/* 关卡卡片网格 */
.challenge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 22px;
    margin-bottom: 28px;
}
.challenge-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}
.challenge-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.08);
    border-color: #0284c7;
}
.challenge-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.challenge-level-badge {
    font-size: 11.5px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}
.challenge-title {
    font-size: 16.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.challenge-desc {
    font-size: 13.5px;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 16px;
    flex: 1;
}
.challenge-meta {
    background: #f8fafc;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-family: monospace;
}
.btn-enter-challenge {
    background: #0284c7;
    color: #ffffff !important;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
    font-weight: 700;
    font-size: 13.5px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}
.btn-enter-challenge:hover {
    background: #0369a1;
}

/* 攻击链路线图 */
.attack-path-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px 28px;
    margin-bottom: 28px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.timeline-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 16px;
}
.timeline-step {
    flex: 1;
    min-width: 140px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    position: relative;
}
.timeline-step.active {
    border-color: #0284c7;
    background: #f0f9ff;
}
.timeline-step-num {
    font-size: 12px;
    font-weight: 800;
    color: #0284c7;
    margin-bottom: 4px;
}
.timeline-step-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
}
.timeline-step-desc {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.4;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li class="active">企业级综合运营管理系统 (CorpHub) 实战靶场</li>
            </ul>
        </div>

        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="tscs-hero-banner">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                    <span class="tscs-badge-tag"><i class="ph-bold ph-buildings"></i> 真实企业级实战案例</span>
                    <span class="tscs-badge-tag" style="background:rgba(239,68,68,0.25); color:#fca5a5; border-color:rgba(252,165,165,0.4);">
                        48 项漏洞全真复刻
                    </span>
                    <span class="tscs-badge-tag" style="background:rgba(245,158,11,0.25); color:#fde68a; border-color:rgba(253,230,138,0.4);">
                        CVSS 9.8 绝杀链条
                    </span>
                    <span class="tscs-badge-tag" style="background:rgba(34,197,94,0.25); color:#bbf7d0; border-color:rgba(187,247,208,0.4);">
                        RuoYi 3.x + Spring + MinIO + BPM
                    </span>
                </div>
                <h1 class="tscs-hero-title">
                    🏗️ 企业级协同办公与运营调度系统 · 48 项漏洞全真攻防演练靶场 (CorpHub Lab)
                </h1>
                <p class="tscs-hero-desc">
                    本靶场完整复现了 2026 年 10 月对大型轨道交通企业“企业级协同办公与运营调度系统”进行的高强度攻防实战过程。真实目标横跨 <strong>若依定制版核心框架 (/prod-api)</strong>、<strong>独立资源调度服务 (/dispatch-api)</strong>、<strong>MinIO 对象存储 (/media)</strong> 与 <strong>企业联邦 BPM/SSO SOAP 单点服务 (:8092)</strong> 四大业务系统。学习者可按照白帽黑客在渗透测试中的真实足迹，一步步体验从“配置泄露与弱口令”到“SSO 票据伪造直接拿下管理员特权”的完整攻防链路！
                </p>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="sso_ticket_forge.php" class="btn btn-danger" style="border-radius:8px; font-weight:700; padding:10px 20px;">
                        <i class="ph-bold ph-lightning"></i> 立即体验 CVSS 9.8 绝杀：BPM/SSO 票据伪造免密登录
                    </a>
                    <a href="config_leak.php" class="btn btn-info" style="border-radius:8px; font-weight:700; padding:10px 20px;">
                        <i class="ph-bold ph-key"></i> 从起点开始：系统配置与 RSA 1024 私钥泄露
                    </a>
                </div>
            </div>

            <!-- 当前角色状态切换控制条 -->
            <div class="tscs-role-bar">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size:13.5px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-user-circle" style="color:#0284c7; font-size:18px;"></i> 当前模拟会话身份：
                    </div>
                    <?php if ($currentUser): ?>
                        <span class="badge" style="background:<?php echo $currentUser['role']==='admin' ? '#fee2e2' : '#e0e7ff'; ?>; color:<?php echo $currentUser['role']==='admin' ? '#991b1b' : '#3730a3'; ?>; font-size:13px; padding:6px 12px; border-radius:6px; font-weight:700;">
                            <?php echo htmlspecialchars($currentUser['nickName']); ?> (<?php echo htmlspecialchars($currentUser['userName']); ?>) · <?php echo htmlspecialchars($currentUser['roleName']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:13px; padding:6px 12px; border-radius:6px;">
                            未登录 (访客 / 匿名外网视角)
                        </span>
                    <?php endif; ?>
                </div>

                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:12.5px; color:#64748b;">快速切换靶场身份测试越权：</span>
                    <a href="index.php?switch_role=logout" class="btn btn-xs btn-default" style="border-radius:4px;">匿名 (访客)</a>
                    <a href="index.php?switch_role=low_role" class="btn btn-xs btn-primary" style="border-radius:4px;">low_role (外部员工)</a>
                    <a href="index.php?switch_role=ops_lead" class="btn btn-xs btn-warning" style="border-radius:4px;">ops_lead (区域负责人)</a>
                    <a href="index.php?switch_role=dispatch_mgr" class="btn btn-xs btn-info" style="border-radius:4px;">dispatch_mgr (调度长)</a>
                    <a href="index.php?switch_role=admin" class="btn btn-xs btn-danger" style="border-radius:4px;">admin (超级管理员)</a>
                </div>
            </div>

            <!-- 渗透测试真实全量突破链条 (The Complete Attack Kill-Chain) -->
            <div class="attack-path-card">
                <h3 style="margin:0 0 6px 0; font-size:17px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-git-fork" style="color:#0284c7;"></i> 渗透实战漏洞闭环攻击链 (The CorpHub Penetration Kill-Chain)
                </h3>
                <p style="font-size:13px; color:#64748b; margin:0;">
                    真实渗透测试中，白帽黑客并不是孤立挖掘单点漏洞，而是通过漏洞组合拳形成致命一击：
                </p>

                <div class="timeline-stepper">
                    <div class="timeline-step active">
                        <div class="timeline-step-num">Step 01 · 信息泄露</div>
                        <div class="timeline-step-title">全局参数配置泄露</div>
                        <div class="timeline-step-desc">/system/config/list 吐出初始密码 <code>Corp@2026!</code>、RSA 1024 私钥与 SSO 4位密钥 <code>corp</code></div>
                    </div>
                    <div class="timeline-step active">
                        <div class="timeline-step-num">Step 02 · 弱口令接管</div>
                        <div class="timeline-step-title">初始口令批量碰撞</div>
                        <div class="timeline-step-desc">利用枚举出的用户名批量匹配初始密码，成功登录 ops_lead/test1 等高权账号，发现全系统 90%+ 未改密</div>
                    </div>
                    <div class="timeline-step active">
                        <div class="timeline-step-num">Step 03 · 越权横查</div>
                        <div class="timeline-step-title">员工档案与哈希横查</div>
                        <div class="timeline-step-desc">selectUserByUserName/{user} 越权拉取超级管理员 admin 的 bcrypt 哈希与手机号、最后登录内网 IP</div>
                    </div>
                    <div class="timeline-step active" style="border-color:#ef4444; background:#fef2f2;">
                        <div class="timeline-step-num" style="color:#dc2626;">Step 04 · 致命突破 (9.8)</div>
                        <div class="timeline-step-title" style="color:#b91c1c;">SSO 票据伪造</div>
                        <div class="timeline-step-desc">向 BPM SOAP 发送 SSOSystemUrl 请求，用 4 位弱密钥为 <code>admin</code> 伪造签发票据，免密直通超管！</div>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-step-num">Step 05 · 后台深挖</div>
                        <div class="timeline-step-title">定时任务白名单</div>
                        <div class="timeline-step-desc">在 若依 monitor/job 添加组件调用，调用 <code>ryTask.ryParams</code> 执行指令验证执行流</div>
                    </div>
                </div>
            </div>

            <!-- 7 个核心实战关卡卡片网格 -->
            <div class="challenge-grid">
                
                <!-- Level 1: 系统配置与 RSA 私钥泄露 -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#e0f2fe; color:#0369a1;">Level 1 · 信息泄露</span>
                            <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700;">CVSS 8.2 高危</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-key" style="color:#0284c7;"></i> 系统配置全量泄露与 RSA 私钥暴露
                        </h4>
                        <div class="challenge-desc">
                            低权限账号访问 <code>/prod-api/system/config/list</code>，接口未按权限过滤，将包含统一初始密码、SSO 4位共享密钥以及 <strong>全平台通用的 1024 位 RSA 私钥 (848 字符)</strong> 一次性泄露！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>CWE-200 (配置泄露) + CWE-321 (私钥暴露)</div>
                            <div><strong>影响端点：</strong>GET /prod-api/system/config/list</div>
                            <div><strong>攻击目标：</strong>提取初始密码、SSO 密钥并解密前端提交的密码密文</div>
                        </div>
                    </div>
                    <a href="config_leak.php" class="btn-enter-challenge">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

                <!-- Level 2: 弱口令与初始密码批量接管 -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#fee2e2; color:#b91c1c;">Level 2 · 身份认证</span>
                            <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700;">CVSS 9.8 紧急</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-lock-key-open" style="color:#dc2626;"></i> 初始口令与全量用户接管
                        </h4>
                        <div class="challenge-desc">
                            登录接口无验证码，且存在特征明显的“用户名枚举”错误文案差异。攻击者利用 Level 1 获得的默认初始口令，成功批量登录 ops_lead、test1 等核心账号，全系统 数万账号 90%+ 沦陷！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>CWE-521 (弱口令) + CWE-204 (账户枚举)</div>
                            <div><strong>影响端点：</strong>POST /prod-api/login</div>
                            <div><strong>攻击目标：</strong>利用文案差异枚举账号并用初始密码完成后台登录</div>
                        </div>
                    </div>
                    <a href="weak_password.php" class="btn-enter-challenge" style="background:#dc2626;">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

                <!-- Level 3: 用户档案与密码哈希横查 -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#fef3c7; color:#92400e;">Level 3 · 越权访问</span>
                            <span class="badge" style="background:#fed7aa; color:#9a3412; font-weight:700;">CVSS 7.5 高危</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-identification-card" style="color:#d97706;"></i> 任意用户档案与密码哈希横查
                        </h4>
                        <div class="challenge-desc">
                            用户档案查询接口缺少垂直越权校验。低权限用户在 URL 路径传入任意用户名（如 <code>admin</code>），接口直接返回该用户的 <strong>bcrypt 密码哈希、手机号、身份证号、最后登录内网 IP</strong>！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>CWE-639 (BOLA 越权) + CWE-257 (哈希泄露)</div>
                            <div><strong>影响端点：</strong>GET /prod-api/system/user/selectUserByUserName/{user}</div>
                            <div><strong>攻击目标：</strong>越权窃取超级管理员完整身份数据与密码哈希值</div>
                        </div>
                    </div>
                    <a href="user_bola.php" class="btn-enter-challenge" style="background:#d97706;">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

                <!-- Level 4: BPM/SSO SOAP 票据伪造致 Admin 免密接管 -->
                <div class="challenge-card" style="border: 2px solid #ef4444; background:rgba(254, 242, 242, 0.4);">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#dc2626; color:#ffffff;">Level 4 · 核心绝杀</span>
                            <span class="badge" style="background:#991b1b; color:#ffffff; font-weight:800;">CVSS 9.8 紧急</span>
                        </div>
                        <h4 class="challenge-title" style="color:#b91c1c;">
                            <i class="ph-bold ph-lightning" style="color:#dc2626;"></i> SSO 票据伪造免密接管 Admin
                        </h4>
                        <div class="challenge-desc">
                            企业联邦 BPM WebServices (SOAP) 接口仅依赖 4 位弱静态密钥校验身份。攻击者构造 SOAP 请求伪造目标工号为 <code>admin</code>，成功签发合法 Token，一键免密直接登录超级管理员！
                        </div>
                        <div class="challenge-meta" style="background:#fff;">
                            <div><strong>关联弱点：</strong>(SSO 伪造免密登录，双环境成立)</div>
                            <div><strong>影响端点：</strong>POST :8092/SSOService + POST /ssoLogin</div>
                            <div><strong>攻击目标：</strong>为 admin 签发票据并在 /ssoLogin 达成管理员完全接管</div>
                        </div>
                    </div>
                    <a href="sso_ticket_forge.php" class="btn-enter-challenge" style="background:#b91c1c;">
                        <i class="ph-bold ph-play-circle"></i> 进入核心绝杀关卡 ➔
                    </a>
                </div>

                <!-- Level 5: MinIO 路径点段穿越读文件 -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#f1f5f9; color:#475569;">Level 5 · 路径穿越</span>
                            <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700;">CVSS 4.3 中危</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-folder-notch-open" style="color:#64748b;"></i> MinIO 路径点段穿越读 Web 根
                        </h4>
                        <div class="challenge-desc">
                            Nginx 代理 MinIO 时未对点段穿越进行规范化。攻击者通过 <code>/media/..%2F</code> 可绕过常规路径限制，匿名读取 Web 根目录与前端编译目录下的敏感版本文件与系统源码！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>CWE-22 (路径点段穿越) + CWE-209 (存储桶报错泄露)</div>
                            <div><strong>影响端点：</strong>GET /media/..%2Fversion.txt</div>
                            <div><strong>攻击目标：</strong>利用点段穿越读取 Web 根目录敏感文件</div>
                        </div>
                    </div>
                    <a href="minio_traversal.php" class="btn-enter-challenge" style="background:#475569;">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

                <!-- Level 6: 调度大表导出越权与 DoS -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#e0e7ff; color:#3730a3;">Level 6 · BOLA 越权</span>
                            <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:700;">CVSS 7.5 高危</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-file-arrow-down" style="color:#4f46e5;"></i> 调度业务大表导出越权与 DoS
                        </h4>
                        <div class="challenge-desc">
                            列表查询与文件导出权限校验不一致（BOLA）。无对应权限账号可调用 <code>/export</code> 导出上万总控度列车运行台账，且因无分页与超时熔断，直接拖垮数据库连接池致系统 504 崩溃！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>CWE-862 (导出接口越权) + CWE-400 (无分页资源耗尽)</div>
                            <div><strong>影响端点：</strong>POST /dispatch-api/dispatchRuncarOrder/export</div>
                            <div><strong>攻击目标：</strong>无权限越权拉取核心调度台账并观察连接池负载</div>
                        </div>
                    </div>
                    <a href="export_bola.php" class="btn-enter-challenge" style="background:#4f46e5;">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

                <!-- Level 7: 若依定时任务白名单组件调用与 RCE 探索 -->
                <div class="challenge-card">
                    <div>
                        <div class="challenge-header">
                            <span class="challenge-level-badge" style="background:#f3e8ff; color:#6b21a8;">Level 7 · 深度利用</span>
                            <span class="badge" style="background:#f3e8ff; color:#6b21a8; font-weight:700;">CVSS 9.3 高危</span>
                        </div>
                        <h4 class="challenge-title">
                            <i class="ph-bold ph-terminal-window" style="color:#7e22ce;"></i> 若依定时任务白名单组件调用链探索
                        </h4>
                        <div class="challenge-desc">
                            在接管管理员权限后，攻击者利用 RuoYi Quartz 定时任务管理模块添加任务，通过注入白名单组件调用（如 <code>ryTask.ryParams('...')</code>），探索任意代码执行与指令流执行可能！
                        </div>
                        <div class="challenge-meta">
                            <div><strong>关联弱点：</strong>(定时任务白名单调用)</div>
                            <div><strong>影响端点：</strong>POST /prod-api/monitor/job + run</div>
                            <div><strong>攻击目标：</strong>配置目标方法并观察执行日志与组件调用链路</div>
                        </div>
                    </div>
                    <a href="quartz_job.php" class="btn-enter-challenge" style="background:#7e22ce;">
                        <i class="ph-bold ph-play-circle"></i> 进入关卡演练 ➔
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
