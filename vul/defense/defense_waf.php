<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Level 1: WAF Rules & Traffic Inspection Simulator
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[222] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// Interactive WAF engine
$waf_mode = isset($_POST['waf_mode']) ? $_POST['waf_mode'] : 'standard';
$test_payload = isset($_POST['payload']) ? $_POST['payload'] : '';

$waf_rules = [
    'sqli' => [
        'name' => 'SQLi 防御规则 (Rule-1001)',
        'pattern' => '/(union\s+select|select\s+.*?\s+from|sleep\(|benchmark\(|--\s|;\s*drop)/i',
        'desc' => '匹配 UNION SELECT、字符串拼接与注入函数调用'
    ],
    'xss' => [
        'name' => 'XSS 跨站脚本防御 (Rule-2001)',
        'pattern' => '/(<script.*?>|javascript:|onload=|onerror=|eval\(|document\.cookie)/i',
        'desc' => '匹配事件响应函数、DOM 操作与 Script 标签'
    ],
    'rce' => [
        'name' => 'RCE 远程命令执行 (Rule-3001)',
        'pattern' => '/(system\(|passthru\(|exec\(|shell_exec\(|whoami|cat\s+\/etc\/passwd|ipconfig|\$\(/i',
        'desc' => '匹配敏感系统命令与危险函数调用'
    ]
];

$inspection_result = null;

if (!empty($test_payload)) {
    $blocked = false;
    $matched_rule = null;

    if ($waf_mode !== 'disabled') {
        foreach ($waf_rules as $key => $rule) {
            if ($waf_mode === 'strict' || $key === $_POST['rule_type']) {
                if (preg_match($rule['pattern'], $test_payload)) {
                    $blocked = true;
                    $matched_rule = $rule;
                    break;
                }
            }
        }
    }

    $inspection_result = [
        'blocked' => $blocked,
        'mode' => $waf_mode,
        'rule' => $matched_rule,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'time' => date('Y-m-d H:i:s')
    ];
}
?>

<style>
.waf-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #047857 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
}
.waf-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.cmd-box {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    padding: 14px 18px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    margin: 10px 0;
    overflow-x: auto;
    border-left: 4px solid #10b981;
}
.log-display {
    background: #1e1e2e;
    color: #a6accd;
    border-radius: 8px;
    padding: 14px;
    font-family: monospace;
    font-size: 13px;
    margin-top: 15px;
    border: 1px solid #2d2d3f;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="waf-hero-banner">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <span class="label label-success" style="font-size: 14px; border-radius: 6px;">LEVEL 1</span>
                            🛡️ WAF 流量包检测与规则引擎模拟试验场
                        </h1>
                        <p style="margin: 0; color: #a7f3d0; font-size: 14px;">
                            <strong>防守维度：</strong> Web Application Firewall (WAF) 流量层正则匹配与 HTTP 协议解包过滤
                        </p>
                    </div>
                    <a href="defense.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回蓝队总控大厅
                    </a>
                </div>
            </div>

            <!-- Theory -->
            <div class="waf-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-shield" style="color: #10b981;"></i> WAF 防御机理剖析</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    Web 应用防火墙 (WAF) 位于客户端与 Web 服务器之间，对传入的 HTTP/HTTPS 流量进行解包。
                    WAF 会检查 <code>URI</code>、<code>Header (User-Agent, Cookie)</code> 以及 <code>POST Body</code>，将其与内置的特征规则库（Regex Patterns）或语义树比较。一旦命中威胁特征，立即返回 <code>403 Forbidden</code> 响应并中断连接。
                </p>
            </div>

            <!-- Interactive Tester -->
            <div class="row">
                <div class="col-md-6">
                    <div class="waf-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-flask" style="color: #6366f1;"></i> WAF 规则实时检测测试终端</h3>
                        
                        <form method="post">
                            <div class="form-group">
                                <label style="font-weight: 700;">WAF 拦截引擎模式：</label>
                                <select name="waf_mode" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                                    <option value="standard" <?php echo $waf_mode==='standard'?'selected':'';?>>✅ 标准模式 (Standard WAF - 单项检测)</option>
                                    <option value="strict" <?php echo $waf_mode==='strict'?'selected':'';?>>🛡️ 严格模式 (Strict WAF - 规则全库检测)</option>
                                    <option value="disabled" <?php echo $waf_mode==='disabled'?'selected':'';?>>❌ 禁用 WAF (Bypass Direct Pass)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700;">测试攻击向量类型：</label>
                                <select name="rule_type" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                                    <option value="sqli">1. SQL 注入向量 (SQL Injection)</option>
                                    <option value="xss">2. XSS 跨站脚本向量 (Cross-Site Scripting)</option>
                                    <option value="rce">3. RCE 命令执行向量 (Command Execution)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700;">输入测试 Payload 数据包：</label>
                                <textarea name="payload" class="form-control" rows="4" style="border-radius: 8px; font-family: monospace;" placeholder="请输入待检测的 Payload，例如: ' UNION SELECT 1, user(), version() -- " required><?php echo htmlspecialchars($test_payload); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success btn-block" style="border-radius: 8px; font-weight: 700;">
                                <i class="fa fa-play"></i> 发送数据包并触发 WAF 流量检测
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="waf-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-desktop" style="color: #3b82f6;"></i> WAF 流量解析与阻断决策面板</h3>
                        
                        <?php if ($inspection_result === null) { ?>
                            <div class="alert alert-info" style="border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-info-circle"></i> 请在左侧选择模式并输入测试 Payload 发送检测请求。
                            </div>
                        <?php } else { ?>
                            
                            <?php if ($inspection_result['blocked']) { ?>
                                <div class="alert alert-danger" style="border-radius: 8px; background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #dc2626;">
                                    <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-ban"></i> HTTP/1.1 403 Forbidden (请求已被 WAF 阻断)</h4>
                                    <p style="margin-bottom: 0;">警告：WAF 规则引擎在当前数据包中检测到了恶意入侵特征，已丢弃请求并记录攻击日志！</p>
                                </div>

                                <div class="log-display">
<span style="color: #ef4444;">[WAF_ALERT]</span> Timestamp: <?php echo $inspection_result['time']; ?>
<span style="color: #ef4444;">[WAF_ALERT]</span> Client IP: <?php echo $inspection_result['ip']; ?>
<span style="color: #ef4444;">[WAF_ALERT]</span> Mode: <?php echo strtoupper($inspection_result['mode']); ?>
<span style="color: #fbbf24;">[MATCHED_RULE]</span> Name: <?php echo $inspection_result['rule']['name']; ?>
<span style="color: #fbbf24;">[MATCHED_RULE]</span> Pattern: <?php echo htmlspecialchars($inspection_result['rule']['pattern']); ?>
<span style="color: #34d399;">[ACTION]</span> Response: 403 Access Denied (Connection Reset)
                                </div>

                            <?php } else { ?>
                                <div class="alert alert-success" style="border-radius: 8px; background: rgba(16, 185, 129, 0.1); border-color: #10b981; color: #059669;">
                                    <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-check-circle"></i> HTTP/1.1 200 OK (数据包无威胁，直接放行)</h4>
                                    <p style="margin-bottom: 0;">数据包未命中当前开启的 WAF 特征规则，已安全转发至后端 Web 应用程序进行处理。</p>
                                </div>

                                <div class="log-display">
<span style="color: #34d399;">[WAF_PASS]</span> Timestamp: <?php echo $inspection_result['time']; ?>
<span style="color: #34d399;">[WAF_PASS]</span> Status: Clean Request
<span style="color: #34d399;">[ACTION]</span> Forwarded to Backend PHP-FPM Engine
                                </div>
                            <?php } ?>

                        <?php } ?>

                    </div>
                </div>
            </div>

            <!-- Rules Reference -->
            <div class="waf-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-list-alt" style="color: #f59e0b;"></i> 当前加载的在线 WAF 正则特征库</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: var(--bg-secondary);">
                                <th>规则编号</th>
                                <th>规则名称</th>
                                <th>正则匹配式 (Regex Pattern)</th>
                                <th>防护说明</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($waf_rules as $key => $rule) { ?>
                            <tr>
                                <td><code>Rule-<?php echo strtoupper($key); ?></code></td>
                                <td><strong><?php echo $rule['name']; ?></strong></td>
                                <td><code style="color: #ef4444;"><?php echo htmlspecialchars($rule['pattern']); ?></code></td>
                                <td><?php echo $rule['desc']; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
