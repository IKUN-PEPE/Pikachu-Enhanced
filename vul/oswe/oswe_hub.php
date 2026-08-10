<?php
/**
 * OSWE CTF Hub - Advanced Web Attacks (14 Stages, 3550 PTS)
 * OSCE³ - OSWE Direction
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[260] = 'active open';
$ACTIVE[261] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

if (!isset($_SESSION['oswe_flags'])) {
    $_SESSION['oswe_flags'] = [];
}

$flags_db = [
    'flag1' => ['flag' => 'flag{OSWE_L1_WhiteBox_DataFlow_Audit_Done}', 'name' => 'L1: 白盒代码审计方法论', 'points' => 100, 'file' => 'oswe_l1_whitebox.php', 'difficulty' => '入门'],
    'flag2' => ['flag' => 'flag{OSWE_L2_AuthBypass_Logic_Chain_Broken}', 'name' => 'L2: 认证绕过逻辑漏洞链', 'points' => 150, 'file' => 'oswe_l2_auth_bypass.php', 'difficulty' => '初级'],
    'flag3' => ['flag' => 'flag{OSWE_L3_SQLi_Auth_Bypass_Union_RCE}', 'name' => 'L3: SQL注入认证绕过+提权', 'points' => 200, 'file' => 'oswe_l3_sqli_auth.php', 'difficulty' => '中级'],
    'flag4' => ['flag' => 'flag{OSWE_L4_PHP_Deserialization_POP_Chain}', 'name' => 'L4: PHP/Java 反序列化 RCE', 'points' => 250, 'file' => 'oswe_l4_deser.php', 'difficulty' => '中级'],
    'flag5' => ['flag' => 'flag{OSWE_L5_SSTI_Jinja2_OS_Command_Exec}', 'name' => 'L5: SSTI 服务端模板注入', 'points' => 300, 'file' => 'oswe_l5_ssti.php', 'difficulty' => '高级'],
    'flag6' => ['flag' => 'flag{OSWE_L6_XXE_OOB_SSRF_File_Disclosure}', 'name' => 'L6: XXE + SSRF 带外数据提取', 'points' => 300, 'file' => 'oswe_l6_xxe_oob.php', 'difficulty' => '高级'],
    'flag7' => ['flag' => 'flag{OSWE_L7_MultiVuln_Chain_RCE_Complete}', 'name' => 'L7: 多漏洞组合 RCE 利用链', 'points' => 400, 'file' => 'oswe_l7_rce_chain.php', 'difficulty' => '专家'],
    \'flag8\' => [\'flag\' => \'flag{OSWE_L8_SQLi_Blind}\', \'name\' => \'L8: 盲注自动化脚本编写\', \'points\' => 250, \'file\' => \'oswe_l8_sqli_blind.php\', \'difficulty\' => \'中级\'],
    \'flag9\' => [\'flag\' => \'flag{OSWE_L9_PHP_Type_Juggling}\', \'name\' => \'L9: PHP 类型混淆攻击\', \'points\' => 200, \'file\' => \'oswe_l9_type_juggling.php\', \'difficulty\' => \'中级\'],
    \'flag10\' => [\'flag\' => \'flag{OSWE_L10_Java_Deser_JDWP_UDF}\', \'name\' => \'L10: Java 反序列化 · JDWP · UDF\', \'points\' => 300, \'file\' => \'oswe_l10_java_rce.php\', \'difficulty\' => \'高级\'],
    \'flag11\' => [\'flag\' => \'flag{OSWE_L11_JS_Proto_Pollution}\', \'name\' => \'L11: JavaScript 原型链污染\', \'points\' => 300, \'file\' => \'oswe_l11_proto_pollution.php\', \'difficulty\' => \'高级\'],
    \'flag12\' => [\'flag\' => \'flag{OSWE_L12_DotNet_ViewState_Deser}\', \'name\' => \'L12: .NET ViewState 反序列化\', \'points\' => 350, \'file\' => \'oswe_l12_dotnet_deser.php\', \'difficulty\' => \'专家\'],
    \'flag13\' => [\'flag\' => \'flag{OSWE_L13_SSRF_Internal_RCE}\', \'name\' => \'L13: SSRF → 内网 RCE 链\', \'points\' => 350, \'file\' => \'oswe_l13_ssrf_rce.php\', \'difficulty\' => \'专家\'],
    \'flag14\' => [\'flag\' => \'flag{OSWE_L14_CSRF_CORS_Bypass}\', \'name\' => \'L14: CSRF + CORS 认证绕过 [终章]\', \'points\' => 300, \'file\' => \'oswe_l14_csrf_cors.php\', \'difficulty\' => \'高级\'],
];

$submit_msg = '';
if (isset($_POST['submit_flag'])) {
    $user_flag = trim($_POST['flag_input']);
    $matched = false;
    foreach ($flags_db as $k => $item) {
        if ($user_flag === $item['flag']) {
            $_SESSION['oswe_flags'][$k] = true;
            $submit_msg = '<div class="alert alert-success" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-check-circle"></i> 恭喜！成功提交【' . $item['name'] . '】Flag，获得 ' . $item['points'] . ' 积分！🎉</div>';
            $matched = true;
            break;
        }
    }
    if (!$matched && !empty($user_flag)) {
        $submit_msg = '<div class="alert alert-danger" style="border-radius: 10px; font-weight: bold;"><i class="fa fa-times-circle"></i> Flag 错误，请根据关卡教学内容仔细推演！</div>';
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $_SESSION['oswe_flags'] = [];
    header("Location: oswe_hub.php");
    exit;
}

$total_score = 0;
$captured_count = 0;
foreach ($flags_db as $k => $item) {
    if (isset($_SESSION['oswe_flags'][$k]) && $_SESSION['oswe_flags'][$k]) {
        $total_score += $item['points'];
        $captured_count++;
    }
}
$progress_pct = count($flags_db) > 0 ? round(($captured_count / count($flags_db)) * 100) : 0;
?>

<style>
.oswe-hero-banner {
    background: linear-gradient(135deg, #0c1829 0%, #1a2744 50%, #0f3460 100%);
    border-radius: 16px;
    padding: 35px;
    color: #ffffff;
    box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
    margin-bottom: 25px;
    border: 1px solid rgba(99, 102, 241, 0.3);
    position: relative;
    overflow: hidden;
}
.oswe-hero-banner::before {
    content: 'OSWE';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 100px;
    font-weight: 900;
    color: rgba(99, 102, 241, 0.07);
    letter-spacing: -2px;
    pointer-events: none;
}
.oswe-title { font-size: 26px; font-weight: 800; margin-top: 0; color: #f8fafc; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.oswe-badge { background: rgba(99, 102, 241, 0.25); color: #a5b4fc; border: 1px solid #6366f1; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.diff-badge { padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.diff-入门 { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid #10b981; }
.diff-初级 { background: rgba(59,130,246,0.15); color: #93c5fd; border: 1px solid #3b82f6; }
.diff-中级 { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid #f59e0b; }
.diff-高级 { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid #ef4444; }
.diff-专家 { background: rgba(139,92,246,0.15); color: #c4b5fd; border: 1px solid #8b5cf6; }
.level-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: all 0.25s ease; }
.level-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15); border-color: #6366f1; }
.level-card.completed { border-left: 6px solid #10b981; }
.level-card.uncompleted { border-left: 6px solid #6366f1; }
.level-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.level-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 10px; }
.points-tag { background: rgba(99, 102, 241, 0.15); color: #6366f1; font-weight: 800; padding: 4px 12px; border-radius: 8px; font-size: 14px; }
.level-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 14px; }
.level-actions { display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid var(--border-color); }
.progress-bar-custom { background: var(--bg-secondary); border-radius: 10px; height: 14px; overflow: hidden; margin: 15px 0; border: 1px solid var(--border-color); }
.progress-bar-fill { background: linear-gradient(90deg, #6366f1, #06b6d4); height: 100%; border-radius: 10px; transition: width 0.5s ease; }
.stat-pill { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; font-size: 13px; color: #e2e8f0; }
.exam-info { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; margin-bottom: 25px; }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">

            <div class="oswe-hero-banner">
                <h1 class="oswe-title">
                    🕵️ OSCE³ · OSWE 高级 Web 攻防 CTF 靶场
                    <span class="oswe-badge">14 大关卡 · 3550 PTS</span>
                    <span class="oswe-badge" style="background: rgba(6,182,212,0.2); color: #67e8f9; border-color: #06b6d4;">白盒审计方向</span>
                </h1>
                <p style="font-size: 15px; color: #a5b4fc; line-height: 1.7; max-width: 950px; margin: 15px 0 20px 0;">
                    对标 Offensive Security OSWE (WEB-300) 考纲，聚焦 <strong style="color: #e0e7ff;">白盒代码审计 → 认证绕过 → SQL注入RCE → 反序列化 → SSTI → XXE/SSRF → 多漏洞组合链</strong>。每关提供源码分析视角与 Flag 教学。
                </p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="stat-pill"><i class="fa fa-flag" style="color: #6366f1;"></i> 通关进度：<strong><?php echo $captured_count; ?> / <?php echo count($flags_db); ?></strong> 关</div>
                    <div class="stat-pill"><i class="fa fa-trophy" style="color: #fbbf24;"></i> 当前积分：<strong><?php echo $total_score; ?> / 3550</strong> PTS</div>
                    <div class="stat-pill"><i class="fa fa-certificate" style="color: #34d399;"></i> 目标认证：<strong>OSWE (WEB-300)</strong></div>
                    <div class="stat-pill"><i class="fa fa-clock-o" style="color: #93c5fd;"></i> 考试时长：<strong>47.75 小时</strong></div>
                </div>
            </div>

            <!-- OSWE 考试信息 -->
            <div class="exam-info">
                <h4 style="margin-top:0; font-weight: 700; color: var(--text-primary);">
                    <i class="fa fa-info-circle" style="color: #6366f1;"></i> OSWE WEB-300 考纲说明
                </h4>
                <div class="row">
                    <div class="col-md-6">
                        <ul style="font-size: 13px; color: var(--text-secondary); line-height: 2; padding-left: 20px;">
                            <li>考试提供 <strong style="color: var(--text-primary);">2 个独立 Web 应用靶标</strong>，需获取每个靶标的完整访问权限</li>
                            <li>必须提交完整的 <strong style="color: var(--text-primary);">自动化利用脚本</strong>（通常为 Python）</li>
                            <li>强调 <strong style="color: var(--text-primary);">白盒代码审计</strong>，通常提供应用源代码</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul style="font-size: 13px; color: var(--text-secondary); line-height: 2; padding-left: 20px;">
                            <li>需掌握 PHP、Python、Java、JavaScript 四种语言的审计能力</li>
                            <li>多漏洞 <strong style="color: var(--text-primary);">组合链条</strong>是考试核心（单一漏洞通常不能直接 RCE）</li>
                            <li>报告需包含漏洞代码行定位与完整利用截图</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Flag Submit -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 25px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h4 style="margin: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-paper-plane" style="color: #6366f1;"></i> OSWE Flag 验证中心</h4>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-secondary);">完成每关学习后，提交 flag{...} 格式 Flag 累积积分。</p>
                    </div>
                    <form method="post" style="display: flex; gap: 10px; flex-grow: 1; max-width: 500px;">
                        <input type="text" name="flag_input" class="form-control" placeholder="flag{...}" required style="border-radius: 8px; font-family: monospace;">
                        <button type="submit" name="submit_flag" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; min-width: 110px; background: #6366f1; border-color: #6366f1;"><i class="fa fa-check"></i> 提交</button>
                        <a href="oswe_hub.php?reset=1" class="btn btn-default" style="border-radius: 8px;" onclick="return confirm('确定重置所有 OSWE 进度吗？');"><i class="fa fa-refresh"></i></a>
                    </form>
                </div>
                <?php if (!empty($submit_msg)) { echo '<div style="margin-top: 15px;">' . $submit_msg . '</div>'; } ?>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?php echo $progress_pct; ?>%;"></div>
                </div>
            </div>

            <!-- 7 Levels -->
            <div class="row">
                <?php
                $descriptions = [
                    'flag1' => '学习白盒代码审计的系统方法论：数据流追踪、污点分析、危险函数定位（eval/system/exec/include）、认证逻辑梳理与权限边界分析。',
                    'flag2' => '分析认证绕过的典型模式：类型混淆（PHP 弱类型比较 == vs ===）、哈希比较绕过、密码找回逻辑缺陷、多步认证流程竞争条件。',
                    'flag3' => '深入 SQL 注入到认证绕过的完整利用链：错误注入、基于时间的盲注、二次注入、文件写入 (INTO OUTFILE) 到 RCE 路径。',
                    'flag4' => '研究 PHP unserialize() 与 Java 反序列化的 POP 链构造原理：魔术方法调用链、Gadget Chain、ysoserial 工具链分析。',
                    'flag5' => '分析 SSTI 服务端模板注入：Jinja2/Twig/Smarty 各引擎注入语法差异，从 {{7*7}} 探测到 os.system() 执行的完整路径。',
                    'flag6' => '深入 XXE 外部实体注入：带外数据提取（OOB XXE）、XXE to SSRF 组合、Blind XXE via Error、参数实体与外部 DTD。',
                    'flag7' => '综合前6关技术，分析真实 CMS/框架的多漏洞 RCE 利用链：文件上传+路径穿越、SSRF+反序列化、XSS+CSRF+文件写入等组合路径。',
                    \'flag8\' => \'编写自动化脚本以高效利用基于时间的盲注漏洞。\',
                    \'flag9\' => \'深度分析 PHP 弱类型比较带来的漏洞，学习如何构造特定的 Payload 实现认证绕过。\',
                    \'flag10\' => \'利用 Java 反序列化漏洞，结合 JDWP 调试端口和 MySQL UDF 提权进行复合攻击。\',
                    \'flag11\' => \'理解 JavaScript 的原型链机制，并学习如何通过污染原型链来修改应用逻辑甚至导致 RCE。\',
                    \'flag12\' => \'针对 .NET 应用，剖析 ViewState 机制及其在缺乏完整性校验时的反序列化漏洞利用。\',
                    \'flag13\' => \'构建从 SSRF 探测内网服务到发现并利用内网 RCE 漏洞的完整攻击链。\',
                    \'flag14\' => \'综合利用 CSRF 攻击与 CORS 配置不当，绕过严格的认证机制。\',
                ];
                foreach ($flags_db as $key => $item) {
                    $is_done = isset($_SESSION['oswe_flags'][$key]) && $_SESSION['oswe_flags'][$key];
                ?>
                <div class="col-md-6">
                    <div class="level-card <?php echo $is_done ? 'completed' : 'uncompleted'; ?>">
                        <div class="level-header">
                            <h3 class="level-title">
                                <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800;">
                                    <?php echo str_replace('flag', '', $key); ?>
                                </span>
                                <?php echo $item['name']; ?>
                            </h3>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <span class="diff-badge diff-<?php echo $item['difficulty']; ?>"><?php echo $item['difficulty']; ?></span>
                                <span class="points-tag"><?php echo $item['points']; ?> PTS</span>
                            </div>
                        </div>
                        <p class="level-desc"><?php echo $descriptions[$key]; ?></p>
                        <div class="level-actions">
                            <div>
                                <?php if ($is_done) { ?>
                                    <span class="label label-success" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-check"></i> 已通关</span>
                                <?php } else { ?>
                                    <span class="label label-default" style="border-radius: 6px; padding: 4px 10px;"><i class="fa fa-clock-o"></i> 待挑战</span>
                                <?php } ?>
                            </div>
                            <a href="<?php echo $item['file']; ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700; background: #6366f1; border-color: #6366f1;">
                                进入关卡 <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
