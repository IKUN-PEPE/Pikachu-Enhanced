<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 3: 任意用户档案横查与 bcrypt 密码哈希泄露
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理 API 请求
if (isset($_GET['api']) || isset($_GET['userName'])) {
    header('Content-Type: application/json; charset=utf-8');
    $targetUser = trim($_GET['userName'] ?? 'admin');
    $users = tscs_get_users();

    if (isset($users[$targetUser])) {
        $u = $users[$targetUser];
        echo json_encode([
            'msg' => '操作成功',
            'code' => 200,
            'data' => [
                'userId' => $u['userId'],
                'deptId' => $u['deptId'],
                'userName' => $u['userName'],
                'nickName' => $u['nickName'],
                'email' => $u['userName'] . '@corp-internal.com',
                'phonenumber' => $u['phonenumber'],
                'idCard' => $u['idCard'],
                'sex' => '0',
                'avatar' => '',
                'password' => $u['password_bcrypt'], // 【核心泄露: 真实接口吐出密码哈希】
                'status' => $u['status'],
                'delFlag' => '0',
                'loginIp' => $u['loginIp'], // 【内网 IP 泄露】
                'loginDate' => $u['loginDate'],
                'dept' => [
                    'deptId' => $u['deptId'],
                    'deptName' => $u['deptName'],
                    'leader' => $u['nickName']
                ],
                'roles' => [
                    ['roleId' => 1, 'roleName' => $u['roleName'], 'roleKey' => $u['role']]
                ]
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'msg' => '用户不存在',
            'code' => 500
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$currentUser = tscs_get_current_user();

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[354] = 'active';

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
.json-display {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.6;
    max-height: 400px;
    overflow-y: auto;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 3: 用户档案与密码哈希横查</li>
            </ul>
        </div>

        <div class="page-content">
            
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#d97706; padding:4px 10px; font-size:12px;">Level 3</span>
                    <span class="badge" style="background:#ea580c; padding:4px 10px; font-size:12px;">CVSS 7.5 高危</span>
                    <span class="badge" style="background:#4f46e5; padding:4px 10px; font-size:12px;">BOLA 水平越权</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    任意用户档案横查与 bcrypt 密码哈希泄露
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>测试人员以低权限账号 <code>low_role</code> 登录后，发现接口 <code>GET /prod-api/system/user/selectUserByUserName/{userName}</code> 未做任何数据权限控制。攻击者只需在 URL 中输入目标用户名（如超级管理员 <code>admin</code>），后端便将完整的数据库实体对象原样序列化输出，泄露了超管的 <strong>bcrypt 密码哈希（可离线破解）、手机号、身份证、部门组织架构以及登录内网真实 IP</strong>！
                </p>
            </div>

            <div class="alert alert-warning" style="border-radius:10px; font-size:13px; line-height:1.6; background:#fffbeb; border-color:#fef3c7; color:#92400e;">
                <strong>🎯 本关演练目标：</strong><br>
                1. 模拟调用 <code>GET /prod-api/system/user/selectUserByUserName/admin</code> 接口。<br>
                2. 观察返回的超管个人敏感信息，提取出 <code>password</code> 字段中的 bcrypt 哈希值与 <code>loginIp</code> 内网地址。<br>
                3. 测试输入其他工号（如 <code>dispatch_mgr</code>、<code>ops_lead</code>），观察是否能够遍历全系统 数万名员工的个人私密档案！
            </div>

            <div class="row">
                
                <div class="col-md-7">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:space-between;">
                            <span><i class="ph-bold ph-identification-card" style="color:#d97706;"></i> 任意用户横查端点 (selectUserByUserName)</span>
                            <span class="badge" style="background:#fef3c7; color:#92400e;">越权遍历</span>
                        </h4>

                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">输入待横查的目标工号 / 用户名 (userName)：</label>
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="targetUserName" class="form-control" value="admin" style="border-radius:8px; padding:10px 12px; height:auto; font-family:monospace;" placeholder="如 admin, dispatch_mgr, ops_lead, test1">
                                <button class="btn btn-warning" onclick="queryUserBola()" style="border-radius:8px; font-weight:700; padding:10px 20px; white-space:nowrap;">
                                    <i class="ph-bold ph-magnifying-glass"></i> 发送越权查询
                                </button>
                            </div>
                            <div style="margin-top:6px; display:flex; gap:6px;">
                                <span style="font-size:12px; color:#64748b;">快捷填入：</span>
                                <a href="javascript:void(0)" onclick="setTarget('admin')" style="font-size:12px; color:#0284c7;">admin (超管)</a> |
                                <a href="javascript:void(0)" onclick="setTarget('dispatch_mgr')" style="font-size:12px; color:#0284c7;">dispatch_mgr (调度长)</a> |
                                <a href="javascript:void(0)" onclick="setTarget('ops_lead')" style="font-size:12px; color:#0284c7;">ops_lead (区域负责人)</a> |
                                <a href="javascript:void(0)" onclick="setTarget('test1')" style="font-size:12px; color:#0284c7;">test1 (外协测试)</a>
                            </div>
                        </div>

                        <div style="margin-top:16px;">
                            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">
                                服务端返回数据 (完整用户实体 DTO)：
                            </div>
                            <pre class="json-display" id="userJsonBox">// 点击“发送越权查询”查看响应...</pre>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:6px;">
                            <i class="ph-bold ph-shield-warning" style="color:#dc2626;"></i> 提取的敏感 PII 与密码哈希
                        </h4>

                        <div id="piiPanel" style="display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:16px; font-size:13px; line-height:1.7;">
                            <div style="font-weight:700; color:#991b1b; margin-bottom:6px;">
                                🚨 严重数据泄露警告：成功截取用户隐私实体！
                            </div>
                            <div><strong>目标用户名：</strong> <span id="piiUser" style="font-weight:700;">-</span></div>
                            <div><strong>手机号码：</strong> <span id="piiPhone" style="font-family:monospace;">-</span></div>
                            <div><strong>身份证号：</strong> <span id="piiCard" style="font-family:monospace;">-</span></div>
                            <div><strong>最近登录内网 IP：</strong> <code id="piiIp" style="background:#fee2e2; color:#991b1b; padding:2px 6px;">-</code></div>
                            <div style="margin-top:8px;">
                                <strong>bcrypt 密码哈希值：</strong>
                                <pre style="background:#7f1d1d; color:#fff; padding:8px; border-radius:6px; font-size:11.5px; word-break:break-all; margin-top:4px;" id="piiHash">-</pre>
                            </div>
                            <div style="font-size:11.5px; color:#7f1d1d; margin-top:6px;">
                                💡 <strong>安全分析：</strong> 响应直接将 <code>password</code> 字段输出到前端！攻击者无需与目标服务器交互即可离线使用 Hashcat 进行字典暴破。
                            </div>
                        </div>

                        <div id="noPiiTip" style="font-size:13px; color:#64748b; padding:16px 0;">
                            请在左侧发起查询以解析 PII 数据。
                        </div>
                    </div>

                    <!-- 下一步行动引导 -->
                    <div class="tscs-card" style="background:#fef2f2; border-color:#fecaca;">
                        <h5 style="margin:0 0 8px 0; font-weight:700; color:#991b1b;">
                            <i class="ph-bold ph-arrow-right"></i> 下一步渗透核心绝杀：
                        </h5>
                        <p style="font-size:12.5px; color:#b91c1c; margin-bottom:12px; line-height:1.6;">
                            即使 admin 采用复杂口令无法轻易破解，我们已在 Level 1 中拿到了 SSO 4位通信密钥！现在前往 <strong>Level 4 BPM/SSO 票据伪造</strong>，无需密码直接伪造票据接管超级管理员！
                        </p>
                        <a href="sso_ticket_forge.php" class="btn btn-sm btn-danger" style="border-radius:6px; font-weight:700;">
                            前往 Level 4 SSO 票据伪造 ➔
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
function setTarget(u) {
    document.getElementById('targetUserName').value = u;
    queryUserBola();
}

function queryUserBola() {
    const u = document.getElementById('targetUserName').value.trim();
    const box = document.getElementById('userJsonBox');
    const piiPanel = document.getElementById('piiPanel');
    const noTip = document.getElementById('noPiiTip');

    box.textContent = '// 正在发起越权请求...';

    fetch('user_bola.php?userName=' + encodeURIComponent(u))
        .then(r => r.json())
        .then(data => {
            box.textContent = JSON.stringify(data, null, 2);
            if (data.code === 200 && data.data) {
                const d = data.data;
                document.getElementById('piiUser').textContent = d.userName + ' (' + d.nickName + ')';
                document.getElementById('piiPhone').textContent = d.phonenumber;
                document.getElementById('piiCard').textContent = d.idCard;
                document.getElementById('piiIp').textContent = d.loginIp;
                document.getElementById('piiHash').textContent = d.password;
                piiPanel.style.display = 'block';
                noTip.style.display = 'none';
            } else {
                piiPanel.style.display = 'none';
                noTip.style.display = 'block';
                noTip.textContent = '该用户不存在或查询失败。';
            }
        })
        .catch(err => {
            box.textContent = '请求异常: ' + err.message;
        });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
