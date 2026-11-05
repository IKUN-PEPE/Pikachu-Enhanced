<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 6: 调度业务大表导出越权与慢查询 DoS 风险
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理导出 API 请求
if (isset($_GET['api']) && $_GET['api'] === 'export') {
    $mode = $_GET['mode'] ?? 'normal';
    if ($mode === 'dos') {
        // 模拟大表慢查询导致的 504 拒绝服务
        header('HTTP/1.1 504 Gateway Time-out');
        echo json_encode([
            'code' => 504,
            'msg' => '🚨 504 Gateway Time-out: 数据库连接池已耗尽 (Active Connections: 100/100)，查询调度 11,681 条记录超过 60s 阈值触发服务熔断！'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } else {
        // 正常越权拉取 Excel 样本
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 200,
            'msg' => '🎉 越权导出成功！已绕过前端权限控制直接生成全量调度台账',
            'exported_rows' => 11681,
            'file_name' => 'dispatch_runcar_order_30d_full.xlsx',
            'sample_data' => [
                ['orderId' => 'ORD-20261026-001', 'trainNo' => 'SZ-LINE01-08', 'driver' => '李某某', 'status' => '已发车', 'line' => '1号线', 'time' => '2026-10-26 08:30'],
                ['orderId' => 'ORD-20261026-002', 'trainNo' => 'SZ-LINE03-12', 'driver' => '王某某', 'status' => '间断待命', 'line' => '3号线', 'time' => '2026-10-26 09:15'],
                ['orderId' => 'ORD-20261026-003', 'trainNo' => 'SZ-LINE05-04', 'driver' => '张某某', 'status' => '检修完成', 'line' => '5号线', 'time' => '2026-10-26 10:00']
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[357] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 6: 调度大表导出越权与 DoS</li>
            </ul>
        </div>

        <div class="page-content">
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#4f46e5; padding:4px 10px; font-size:12px;">Level 6</span>
                    <span class="badge" style="background:#ea580c; padding:4px 10px; font-size:12px;">CVSS 7.5 高危</span>
                    <span class="badge" style="background:#7c3aed; padding:4px 10px; font-size:12px;">BOLA 越权与 DoS</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    调度大表导出越权与慢查询 DoS 风险
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>在对 <code>/dispatch-api</code> 的 39 个导出端点审计时，测试人员发现大量 <code>/export</code> 接口未挂载权限校验注解（与同名 list 列表接口权限不对齐）。无对应调度权限的普通账号可调用导出端点，拉取 30 天内全量 11,681 行核心列车与车辆运行计划！由于单次导出无数量限制且未设慢查询熔断，在并发调用时极易将后端数据库连接池占满，导致系统整体出现 504 网关超时拒绝服务！
                </p>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="tscs-card" style="background:#fff; border-radius:14px; padding:24px; border:1px solid #e2e8f0;">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px;">
                            <i class="ph-bold ph-file-arrow-down" style="color:#4f46e5;"></i> 调度工单全表导出请求器
                        </h4>

                        <div style="display:flex; gap:10px; margin-bottom:16px;">
                            <button class="btn btn-primary" onclick="triggerExport('normal')" style="border-radius:8px; font-weight:700; padding:10px 18px;">
                                <i class="ph-bold ph-download-simple"></i> 发起越权导出 (拉取 11,681 行台账)
                            </button>
                            <button class="btn btn-danger" onclick="triggerExport('dos')" style="border-radius:8px; font-weight:700; padding:10px 18px;">
                                <i class="ph-bold ph-lightning"></i> 模拟大表并发慢查询 (触发 504 熔断)
                            </button>
                        </div>

                        <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">导出执行响应与审计：</div>
                        <pre id="exportRespBox" style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-family:monospace; font-size:13px; min-height:140px;">// 点击上方按钮发起导出演练...</pre>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="tscs-card" style="background:#fff; border-radius:14px; padding:24px; border:1px solid #e2e8f0;">
                        <h4 style="margin:0 0 12px 0; font-weight:700; font-size:15px; color:#0f172a;">
                            <i class="ph-bold ph-shield-checkered" style="color:#10b981;"></i> 蓝队标准修复加固
                        </h4>
                        <ol style="font-size:13px; color:#475569; line-height:1.7; padding-left:18px;">
                            <li><strong>权限粒度严格对齐</strong>：为全部 39 个 export 导出端点补全 <code>@PreAuthorize</code> 注解，确保无 list 权限即无 export 权限。</li>
                            <li><strong>限制单次最大导出量</strong>：后端强制限制导出上限（如单次不超过 5,000 条），时间跨度不超过 7 天。</li>
                            <li><strong>异步队列与超时熔断</strong>：将全量导出改造为消息队列异步生成，并在数据库层设置 15 秒 SQL 查询超时中断。</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function triggerExport(mode) {
    const box = document.getElementById('exportRespBox');
    box.textContent = '// 正在发起导出请求，请稍候...';

    fetch('export_bola.php?api=export&mode=' + mode)
        .then(r => r.json())
        .then(data => {
            box.textContent = JSON.stringify(data, null, 2);
        })
        .catch(err => {
            box.textContent = '请求异常: ' + err.message;
        });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
