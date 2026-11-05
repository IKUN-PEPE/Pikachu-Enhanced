<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 2: 用户名枚举与初始密码批量接管
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理 POST 登录接口
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['username']) || isset($_GET['api']))) {
    header('Content-Type: application/json; charset=utf-8');
    $rawInput = file_get_contents('php://input');
    $reqData = json_decode($rawInput, true);
    
    $username = trim($_POST['username'] ?? ($reqData['username'] ?? ''));
    $password = trim($_POST['password'] ?? ($reqData['password'] ?? ''));

    $loginRes = tscs_login($username, $password);
    echo json_encode($loginRes, JSON_UNESCAPED_UNICODE);
    exit;
}

$currentUser = tscs_get_current_user();

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[353] = 'active';

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
.batch-table th, .batch-table td {
    padding: 10px 14px;
    font-size: 13px;
    border-bottom: 1px solid #e2e8f0;
}
.batch-table th {
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
                <li class="active">Level 2: 初始口令与全量用户接管</li>
            </ul>
        </div>

        <div class="page-content">
            
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#dc2626; padding:4px 10px; font-size:12px;">Level 2</span>
                    <span class="badge" style="background:#dc2626; padding:4px 10px; font-size:12px;">CVSS 9.8 紧急</span>
                    <span class="badge" style="background:#ea580c; padding:4px 10px; font-size:12px;">CWE-521 弱密码</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    用户名枚举与初始密码批量接管
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>在真实渗透中，主站 <code>/prod-api/login</code> 既无验证码也无防爆破锁定。更严重的是，登录接口错误文案存在<strong>回显反向差异</strong>（用户不存在时报“密码错误”，用户存在但密码错时报“用户不存在/密码错误”），使攻击者能 100% 确认有效用户名。配合 Level 1 获得的初始密码 <code>Corp@2026!</code>，攻击者发现全系统 数万名企业员工因缺少“首登强制改密策略”，超过 90% 依然使用初始密码，导致批量越权接管！
                </p>
            </div>

            <!-- 演练目标提示 -->
            <div class="alert alert-danger" style="border-radius:10px; font-size:13px; line-height:1.6; background:#fef2f2; border-color:#fecaca; color:#991b1b;">
                <strong>🎯 本关演练目标：</strong><br>
                1. <strong>验证用户名回显差异枚举</strong>：输入存在用户（如 <code>ops_lead</code>）与随机不存在用户（如 <code>not_exist_user_888</code>），输入错误密码，观察回显文案差异！<br>
                2. <strong>利用初始密码登录高权账号</strong>：输入用户名 <code>ops_lead</code> 或 <code>dispatch_mgr</code>，密码输入 <code>Corp@2026!</code>，成功登录并接管具有高阶权限的业务主管后台！<br>
                3. <strong>体验初始口令批量碰撞</strong>：在右侧点击“批量初始密码撞库”，观察高命中率与资产接管效果。
            </div>

            <div class="row">
                
                <!-- 左侧: 交互式登录认证挑战 -->
                <div class="col-md-6">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:space-between;">
                            <span><i class="ph-bold ph-sign-in" style="color:#dc2626;"></i> 登录接口交互探测 (POST /prod-api/login)</span>
                            <span class="badge" style="background:#fee2e2; color:#991b1b;">无验证码保护</span>
                        </h4>

                        <div id="loginAlertBox" style="display:none; margin-bottom:14px; padding:12px; border-radius:8px; font-size:13px;"></div>

                        <form id="tscsLoginForm" onsubmit="handleTscsLogin(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-size:13px; font-weight:600; color:#334155;">测试工号 / 用户名 (Username)：</label>
                                <input type="text" id="loginUsername" class="form-control" value="ops_lead" required style="border-radius:8px; padding:10px 12px; height:auto;" placeholder="如 ops_lead, dispatch_mgr, test1, not_exist_999">
                                <span style="font-size:11.5px; color:#64748b; margin-top:4px; display:block;">
                                    💡 试探：输入 <code>ops_lead</code> 与 <code>not_exist_999</code> 配合任意错误密码，观察回显对比。
                                </span>
                            </div>

                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-size:13px; font-weight:600; color:#334155;">登录密码 (Password)：</label>
                                <input type="password" id="loginPassword" class="form-control" value="Corp@2026!" required style="border-radius:8px; padding:10px 12px; height:auto;" placeholder="请输入待测试密码">
                                <span style="font-size:11.5px; color:#0284c7; margin-top:4px; display:block;">
                                    💡 默认填入 Level 1 泄露的系统统一初始口令：<code>Corp@2026!</code>
                                </span>
                            </div>

                            <button type="submit" id="loginSubmitBtn" class="btn btn-danger btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#dc2626; border-color:#dc2626;">
                                <i class="ph-bold ph-paper-plane-tilt"></i> 提交认证请求 (Send Login POST)
                            </button>
                        </form>

                        <!-- 当前会话接管详情展示 -->
                        <?php if ($currentUser): ?>
                            <div style="margin-top:20px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px;">
                                <div style="font-weight:700; color:#065f46; font-size:14px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-bold ph-check-circle"></i> 当前会话已成功接管用户：[<?php echo htmlspecialchars($currentUser['userName']); ?>]
                                </div>
                                <div style="font-size:12.5px; color:#047857; line-height:1.6;">
                                    <div><strong>真实姓名：</strong> <?php echo htmlspecialchars($currentUser['nickName']); ?></div>
                                    <div><strong>所属部门：</strong> <?php echo htmlspecialchars($currentUser['deptName']); ?></div>
                                    <div><strong>分配角色：</strong> <?php echo htmlspecialchars($currentUser['roleName']); ?></div>
                                    <div><strong>授权权限项：</strong> <code><?php echo implode(', ', $currentUser['permissions']); ?></code></div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- 右侧: 初始口令批量碰撞沙盒 -->
                <div class="col-md-6">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; justify-content:space-between;">
                            <span><i class="ph-bold ph-users-four" style="color:#ea580c;"></i> 初始口令批量碰撞沙盒 (Batch Audit)</span>
                            <span class="badge" style="background:#fed7aa; color:#9a3412;">数万员工抽样</span>
                        </h4>

                        <p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:14px;">
                            在实际渗透中，测试团队对系统 <code>system:user:listByRole</code> 接口枚举的账号抽样 100+ 个，发现未改密比例高达 <strong>90%+</strong>！下方为 5 个核心典型账号的批量核验模拟：
                        </p>

                        <button class="btn btn-warning btn-block" onclick="runBatchTest()" id="batchBtn" style="border-radius:8px; font-weight:700; padding:10px; margin-bottom:16px;">
                            <i class="ph-bold ph-play"></i> 开始对工号字典批量核验初始口令 (Corp@2026!)
                        </button>

                        <div style="overflow-x:auto;">
                            <table class="batch-table" style="width:100%; text-align:left;">
                                <thead>
                                    <tr>
                                        <th>测试工号</th>
                                        <th>身份与角色</th>
                                        <th>核验口令</th>
                                        <th>命中状态</th>
                                        <th>一键接管</th>
                                    </tr>
                                </thead>
                                <tbody id="batchTableBody">
                                    <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding:20px;">点击上方按钮开始批量核验...</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- 下一步行动引导 -->
                    <div class="tscs-card" style="background:#fef2f2; border-color:#fecaca;">
                        <h5 style="margin:0 0 8px 0; font-weight:700; color:#991b1b;">
                            <i class="ph-bold ph-arrow-right"></i> 下一步渗透闭环行动：
                        </h5>
                        <p style="font-size:12.5px; color:#b91c1c; margin-bottom:12px; line-height:1.6;">
                            成功以 <code>low_role</code> 或 <code>ops_lead</code> 身份登录后，前往 <strong>Level 3 任意用户档案横查</strong>，尝试直接拉取超级管理员 <code>admin</code> 的 bcrypt 密码哈希与个人敏感信息！
                        </p>
                        <a href="user_bola.php" class="btn btn-sm btn-danger" style="border-radius:6px; font-weight:700;">
                            前往 Level 3 用户横查 ➔
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
function handleTscsLogin(e) {
    e.preventDefault();
    const u = document.getElementById('loginUsername').value.trim();
    const p = document.getElementById('loginPassword').value.trim();
    const btn = document.getElementById('loginSubmitBtn');
    const alertBox = document.getElementById('loginAlertBox');

    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在验证...';

    const formData = new URLSearchParams();
    formData.append('username', u);
    formData.append('password', p);

    fetch('weak_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        alertBox.style.display = 'block';
        if (data.code === 200) {
            alertBox.style.background = '#ecfdf5';
            alertBox.style.border = '1px solid #a7f3d0';
            alertBox.style.color = '#065f46';
            alertBox.innerHTML = '<strong>🎉 登录成功！</strong> 成功以用户 <code>' + u + '</code> 身份进入系统！正在刷新权限...';
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            alertBox.style.background = '#fef2f2';
            alertBox.style.border = '1px solid #fecaca';
            alertBox.style.color = '#991b1b';
            alertBox.innerHTML = '<strong>❌ 认证失败！</strong> 服务端返回错误回显：<code>' + data.msg + '</code><br><span style="font-size:11.5px; opacity:0.9;">💡 观察回显文本：若提示"用户不存在/密码错误"，说明该工号在系统中真实存在！</span>';
        }
    })
    .catch(err => {
        alertBox.style.display = 'block';
        alertBox.innerHTML = '请求异常: ' + err.message;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> 提交认证请求 (Send Login POST)';
    });
}

function runBatchTest() {
    const btn = document.getElementById('batchBtn');
    const tbody = document.getElementById('batchTableBody');
    btn.disabled = true;
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:16px;">正在对 5 个候选账号逐一发送探测包...</td></tr>';

    const sampleUsers = [
        { u: 'ops_lead', role: '区域业务负责人 (中阶管理权限)', hit: true },
        { u: 'dispatch_mgr', role: '运营调度主管 (高阶管理权限)', hit: true },
        { u: 'test1', role: '外协测试人员 (基础业务权限)', hit: true },
        { u: 'admin', role: '超级管理员 (已改强口令)', hit: false },
        { u: 'low_role', role: '外部员工临时账号', hit: false }
    ];

    setTimeout(() => {
        let html = '';
        sampleUsers.forEach(item => {
            const statusHtml = item.hit 
                ? '<span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700;">🚨 挂初始口令 (命中!)</span>'
                : '<span class="badge" style="background:#f1f5f9; color:#64748b;">已修改独立密码</span>';
            const actionHtml = item.hit
                ? '<a href="index.php?switch_role=' + item.u + '" class="btn btn-xs btn-danger" style="border-radius:4px; font-weight:700;">一键接管</a>'
                : '<span style="color:#94a3b8; font-size:11.5px;">不可接管</span>';
            
            html += `<tr>
                <td><strong>${item.u}</strong></td>
                <td>${item.role}</td>
                <td><code>Corp@2026!</code></td>
                <td>${statusHtml}</td>
                <td>${actionHtml}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
        btn.disabled = false;
    }, 800);
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
