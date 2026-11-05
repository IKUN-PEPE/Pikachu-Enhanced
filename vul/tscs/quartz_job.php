<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 7: 若依定时任务白名单组件调用与 RCE 利用链探索
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

$currentUser = tscs_get_current_user();

// 处理添加任务 API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // 检查是否有管理员权限
    if (!$currentUser || $currentUser['role'] !== 'admin') {
        echo json_encode(['code' => 403, 'msg' => '权限不足：仅超级管理员 (admin) 允许操作若依定时任务！请先通过 Level 4 SSO 票据伪造接管超管！'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'add') {
        $raw = file_get_contents('php://input');
        $req = json_decode($raw, true) ?: $_POST;
        $name = trim($req['jobName'] ?? '安全测试探针任务');
        $target = trim($req['invokeTarget'] ?? 'ryTask.ryParams(\'corp_audit\')');
        $cron = trim($req['cronExpression'] ?? '0/20 * * * * ?');

        // 白名单检查 (复现若依机制)
        $isRuoyiBean = (strpos($target, 'ryTask') !== false || strpos($target, 'com.ruoyi') !== false || strpos($target, 'genTable') !== false);
        if (!$isRuoyiBean) {
            echo json_encode(['code' => 500, 'msg' => '操作失败：调用目标不符合若依组件白名单规范 (允许白名单包：com.ruoyi.*)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $newId = count($_SESSION['tscs_jobs']) + 1;
        $newJob = [
            'jobId' => $newId,
            'jobName' => $name,
            'jobGroup' => 'DEFAULT',
            'invokeTarget' => $target,
            'cronExpression' => $cron,
            'misfirePolicy' => '3',
            'concurrent' => '1',
            'status' => '0',
            'createTime' => date('Y-m-d H:i:s')
        ];
        $_SESSION['tscs_jobs'][] = $newJob;
        echo json_encode(['code' => 200, 'msg' => '任务新增成功', 'data' => $newJob], JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'run') {
        $raw = file_get_contents('php://input');
        $req = json_decode($raw, true) ?: $_POST;
        $jobId = intval($req['jobId'] ?? 1);

        // 执行任务
        $target = 'ryTask.ryNoParams';
        foreach ($_SESSION['tscs_jobs'] as $j) {
            if ($j['jobId'] === $jobId) {
                $target = $j['invokeTarget'];
                break;
            }
        }

        $logId = count($_SESSION['tscs_job_logs']) + 101;
        $newLog = [
            'jobLogId' => $logId,
            'jobName' => '定时任务执行',
            'jobGroup' => 'DEFAULT',
            'invokeTarget' => $target,
            'jobMessage' => "{$target} 执行成功！耗时: " . rand(8, 25) . "ms (Method invoked via Spring Reflection)",
            'status' => '0',
            'exceptionInfo' => '',
            'createTime' => date('Y-m-d H:i:s')
        ];
        $_SESSION['tscs_job_logs'][] = $newLog;

        echo json_encode([
            'code' => 200,
            'msg' => "🎉 任务触发成功！已通过 Spring Bean 容器执行方法 [{$target}]",
            'log' => $newLog
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[358] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.tscs-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.job-table th, .job-table td {
    padding: 10px 14px;
    font-size: 13px;
    border-bottom: 1px solid #e2e8f0;
}
.job-table th {
    background: #f8fafc;
    font-weight: 700;
    color: #334155;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 7: 若依定时任务组件调用</li>
            </ul>
        </div>

        <div class="page-content">
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#7e22ce; padding:4px 10px; font-size:12px;">Level 7 · 深度利用</span>
                    <span class="badge" style="background:#9333ea; padding:4px 10px; font-size:12px;">CVSS 9.3 高危</span>
                    <span class="badge" style="background:#4f46e5; padding:4px 10px; font-size:12px;">Spring 反射执行</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    若依定时任务白名单组件调用链探索
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>在通过 Level 4 SSO 票据伪造获得 <code>admin</code> 超管特权后，渗透人员的目标直指远程命令执行 (RCE)。若依自带的 Quartz 定时任务管理功能（<code>/prod-api/monitor/job</code>）允许管理员配置 <code>invokeTarget</code>。虽然新版若依屏蔽了 RMI/LDAP JNDI 注入，但依然保留了 <code>com.ruoyi.*</code> 白名单组件的反射调用能力！攻击者可调用诸如 <code>ryTask.ryParams('...')</code> 等组件，验证方法执行与深入利用链。
                </p>
            </div>

            <!-- 权限提示 -->
            <?php if (!$currentUser || $currentUser['role'] !== 'admin'): ?>
                <div class="alert alert-danger" style="border-radius:10px; font-size:13px; line-height:1.6;">
                    <strong>⚠️ 权限不足警告：</strong> 当前您的模拟身份为 <code><?php echo $currentUser ? htmlspecialchars($currentUser['userName']) : '未登录'; ?></code>，缺乏定时任务管理权限！<br>
                    请先前往 <a href="sso_ticket_forge.php" style="color:#b91c1c; font-weight:700; text-decoration:underline;">Level 4 BPM/SSO 票据伪造</a> 免密接管超级管理员 <code>admin</code>，或在页面右上角切换为 <code>admin</code> 身份！
                </div>
            <?php else: ?>
                <div class="alert alert-success" style="border-radius:10px; font-size:13px; line-height:1.6;">
                    <strong>✅ 超管特权已就绪：</strong> 当前会话为 <code>admin</code> (超级管理员)，已解锁若依 Quartz 定时任务新增、编辑与立即执行权限！
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px;">
                            <i class="ph-bold ph-calendar-plus" style="color:#7e22ce;"></i> 定时任务配置与执行 (POST /prod-api/monitor/job)
                        </h4>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">任务名称：</label>
                            <input type="text" id="jobNameInput" class="form-control" value="CorpHub安全审计探针任务" style="border-radius:6px;">
                        </div>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">调用目标字符串 (invokeTarget)：</label>
                            <input type="text" id="invokeTargetInput" class="form-control" value="ryTask.ryParams('corp_exploit_verified')" style="border-radius:6px; font-family:monospace; font-weight:700; color:#7e22ce;">
                            <div style="margin-top:6px; display:flex; gap:8px;">
                                <span style="font-size:12px; color:#64748b;">候选白名单载荷：</span>
                                <a href="javascript:void(0)" onclick="setPayload('ryTask.ryNoParams')" style="font-size:12px; color:#0284c7;">ryTask.ryNoParams (基线)</a> |
                                <a href="javascript:void(0)" onclick="setPayload('ryTask.ryParams(\'pika_flag_2026\')')" style="font-size:12px; color:#0284c7;">ryTask.ryParams('pika_flag')</a> |
                                <a href="javascript:void(0)" onclick="setPayload('com.ruoyi.quartz.task.RyTask.ryNoParams()')" style="font-size:12px; color:#0284c7;">全限定类名</a>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">Cron 表达式：</label>
                            <input type="text" id="cronInput" class="form-control" value="0/10 * * * * ?" style="border-radius:6px; font-family:monospace;">
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button class="btn btn-purple" onclick="addJob()" style="background:#7e22ce; color:#fff; border-radius:8px; font-weight:700; padding:10px 18px;">
                                <i class="ph-bold ph-plus-circle"></i> 添加白名单组件任务
                            </button>
                            <button class="btn btn-danger" onclick="runJob(1)" style="border-radius:8px; font-weight:700; padding:10px 18px;">
                                <i class="ph-bold ph-play"></i> 立即触发执行任务 (Run Once)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; color:#0f172a;">
                            <i class="ph-bold ph-list-dashes" style="color:#0284c7;"></i> 任务调度执行日志 (JobLog)
                        </h4>

                        <div id="jobLogContainer" style="max-height:280px; overflow-y:auto; background:#0f172a; color:#f8fafc; border-radius:10px; padding:14px; font-family:monospace; font-size:12px; line-height:1.6;">
                            <?php foreach (array_reverse($_SESSION['tscs_job_logs']) as $log): ?>
                                <div style="margin-bottom:8px; border-bottom:1px dashed #334155; padding-bottom:6px;">
                                    <span style="color:#38bdf8;">[<?php echo htmlspecialchars($log['createTime']); ?>]</span>
                                    <span style="color:#a78bfa; font-weight:700;"><?php echo htmlspecialchars($log['invokeTarget']); ?></span><br>
                                    <span style="color:#22c55e;">✔ <?php echo htmlspecialchars($log['jobMessage']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function setPayload(p) {
    document.getElementById('invokeTargetInput').value = p;
}

function addJob() {
    const name = document.getElementById('jobNameInput').value.trim();
    const target = document.getElementById('invokeTargetInput').value.trim();
    const cron = document.getElementById('cronInput').value.trim();

    fetch('quartz_job.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jobName: name, invokeTarget: target, cronExpression: cron })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if (data.code === 200) {
            window.location.reload();
        }
    })
    .catch(err => {
        alert('请求异常: ' + err.message);
    });
}

function runJob(id) {
    fetch('quartz_job.php?action=run', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jobId: id })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if (data.code === 200) {
            window.location.reload();
        }
    })
    .catch(err => {
        alert('请求异常: ' + err.message);
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
