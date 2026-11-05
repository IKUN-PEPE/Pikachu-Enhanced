<?php
/**
 * Pikachu-Enhanced v3.0 - 水平越权 (Horizontal Privilege Escalation) 会员个人中心
 */
ob_start();
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';

$link = @connect();

// 内置兜底会员数据（保证数据库未初始化时依然可完整演练）
$FALLBACK_MEMBERS = [
    'lucy' => ['username' => 'lucy', 'pw' => md5('123456'), 'sex' => 'girl', 'phonenum' => '13666666666', 'address' => 'USA',   'email' => 'lucy@pikachu.com'],
    'lili' => ['username' => 'lili', 'pw' => md5('123456'), 'sex' => 'girl', 'phonenum' => '13888888888', 'address' => 'China', 'email' => 'lili@pikachu.com'],
    'kobe' => ['username' => 'kobe', 'pw' => md5('123456'), 'sex' => 'boy',  'phonenum' => '13999999999', 'address' => 'LA',    'email' => 'kobe@pikachu.com'],
];

// 处理退出登录（必须在 header.php 输出 HTML 之前执行）
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    unset($_SESSION['op']);
    header("Location: op1_login.php");
    exit;
}

// 校验登录态
$is_logged = false;
if (!empty($_SESSION['op']['username']) && !empty($_SESSION['op']['password'])) {
    $sess_u = $_SESSION['op']['username'];
    if ($link) {
        $res = @execute($link, "SELECT * FROM member WHERE username='" . escape($link, $sess_u) . "' AND sha1(pw)='{$_SESSION['op']['password']}'");
        if ($res && mysqli_num_rows($res) === 1) {
            $is_logged = true;
        }
    }
    if (!$is_logged && isset($FALLBACK_MEMBERS[$sess_u]) && sha1($FALLBACK_MEMBERS[$sess_u]['pw']) === $_SESSION['op']['password']) {
        $is_logged = true;
    }
}

if (!$is_logged) {
    header("Location: op1_login.php");
    exit;
}

$session_user = $_SESSION['op']['username'];
$queried_user = isset($_GET['username']) ? trim($_GET['username']) : '';
$member_data = null;
$not_found_msg = '';
$idor_triggered = false;

if (isset($_GET['submit']) && $queried_user !== '') {
    // 漏洞核心：未使用 $_SESSION['op']['username'] 校验归属，直接信任前端传入的 $_GET['username']
    if ($link) {
        $u_esc = escape($link, $queried_user);
        $query = "SELECT * FROM member WHERE username='$u_esc'";
        $result = @execute($link, $query);
        if ($result && mysqli_num_rows($result) === 1) {
            $member_data = mysqli_fetch_assoc($result);
        }
    }
    if (!$member_data && isset($FALLBACK_MEMBERS[$queried_user])) {
        $member_data = $FALLBACK_MEMBERS[$queried_user];
    }

    if ($member_data) {
        if (strcasecmp($member_data['username'], $session_user) !== 0) {
            $idor_triggered = true;
        }
    } else {
        $not_found_msg = "未查询到用户 <code>" . htmlspecialchars($queried_user) . "</code> 的档案记录（可尝试查询 <code>kobe</code>、<code>lili</code> 或 <code>lucy</code>）。";
    }
}

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[73] = 'active open';
$ACTIVE[75] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../op.php">越权访问控制</a></li>
                <li><a href="op1_login.php?switch=1">水平越权登录</a></li>
                <li class="active">会员个人中心 (op1_mem.php)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="点击查询按钮后，将 URL 中的 username 参数改为 kobe 或 lili，即可越权查看其他会员的隐私档案！">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <!-- 头部卡片 -->
                <div class="cyber-header-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                        <div>
                            <h1 class="cyber-header-title" style="margin-bottom:8px;">
                                👤 会员个人中心 · 水平越权演练台
                                <span class="cyber-badge-chip">IDOR / BOLA · 150 PTS</span>
                            </h1>
                            <p class="cyber-desc-text" style="margin-bottom:0;">
                                当前已通过身份认证。本页面通过隐藏表单字段 <code>&lt;input type="hidden" name="username" value="<?php echo htmlspecialchars($session_user); ?>"&gt;</code> 发送 GET 请求拉取个人资料，但服务端未核对请求参数是否与当前 Session 一致。
                            </p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; background:var(--bg-secondary); padding:8px 14px; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                            <span style="font-size:13px; color:var(--text-secondary);">
                                <i class="fa fa-check-circle" style="color:var(--success);"></i> 当前会话：<strong style="color:var(--text-primary); font-family:monospace;"><?php echo htmlspecialchars($session_user); ?></strong>
                            </span>
                            <a href="op1_mem.php?logout=1" class="btn btn-xs btn-danger" style="border-radius:var(--radius-sm); padding:3px 10px; font-weight:600;">
                                <i class="fa fa-sign-out"></i> 退出 / 切换账号
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- 左侧：交互与越权回显控制台 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 档案查询与参数篡改控制台
                            </h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">
                                正常业务下，前端通过隐藏字段提交当前登录用户名 <code><?php echo htmlspecialchars($session_user); ?></code>；你可以在下方直接修改 <code>username</code> 参数模拟 Burp 抓包或地址栏篡改：
                            </p>

                            <form class="msg1" method="GET" action="op1_mem.php" id="op1QueryForm">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="query_username" style="font-weight:600; color:var(--text-primary); font-size:13px;">
                                        GET 请求参数 <code>username</code> <span style="font-weight:400; color:var(--text-muted);">(原为 hidden 隐藏域)</span>：
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="query_username" name="username" value="<?php echo htmlspecialchars($queried_user !== '' ? $queried_user : $session_user); ?>" style="font-family:monospace;" />
                                        <span class="input-group-btn">
                                            <button type="submit" name="submit" value="点击查看个人信息" class="btn btn-primary">
                                                <i class="fa fa-search"></i> 点击查看个人信息
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="tamperAndSubmit('<?php echo htmlspecialchars($session_user); ?>')">
                                        <i class="fa fa-user" style="color:var(--primary);"></i> 正常查询本人 (<?php echo htmlspecialchars($session_user); ?>)
                                    </button>
                                    <?php foreach (['kobe', 'lili', 'lucy'] as $target_u): ?>
                                        <?php if (strcasecmp($target_u, $session_user) !== 0): ?>
                                            <button type="button" class="payload-chip-btn payload-chip-danger" onclick="tamperAndSubmit('<?php echo $target_u; ?>')">
                                                <i class="fa fa-bolt"></i> 越权查询他人档案: <?php echo $target_u; ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title">
                                <i class="fa fa-address-card-o" style="color:var(--accent);"></i> 服务端档案数据回显
                            </h4>

                            <?php if ($member_data): ?>
                                <div id="per_info" style="background:var(--bg-secondary); border:1px solid <?php echo $idor_triggered ? '#ef4444' : 'var(--border-subtle)'; ?>; border-radius:var(--radius-md); padding:16px; margin-top:10px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid var(--border-subtle);">
                                        <span style="font-weight:700; font-size:14.5px; color:var(--text-primary);">
                                            <i class="fa fa-user-circle"></i> Hello, <code><?php echo htmlspecialchars($member_data['username']); ?></code> 的个人档案明细
                                        </span>
                                        <?php if ($idor_triggered): ?>
                                            <span class="label label-danger">⚠️ 跨账号水平越权读取</span>
                                        <?php else: ?>
                                            <span class="label label-success">✅ 本人合法访问</span>
                                        <?php endif; ?>
                                    </div>
                                    <table class="table table-bordered" style="margin-bottom:0; background:var(--bg-card); font-size:13px;">
                                        <tbody>
                                            <tr>
                                                <th style="width:110px; background:var(--bg-secondary);">姓名 / 账号</th>
                                                <td class="per_name"><code><?php echo htmlspecialchars($member_data['username']); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">性别 (Sex)</th>
                                                <td class="per_sex"><?php echo htmlspecialchars($member_data['sex']); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">手机号码</th>
                                                <td class="per_phone"><strong style="color:var(--danger); font-family:monospace;"><?php echo htmlspecialchars($member_data['phonenum']); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">家庭住址</th>
                                                <td class="per_add"><?php echo htmlspecialchars($member_data['address']); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">电子邮箱</th>
                                                <td class="per_email"><code><?php echo htmlspecialchars($member_data['email']); ?></code></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($idor_triggered): ?>
                                    <div class="cyber-flag-card" style="margin-top:16px;">
                                        <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;">
                                            <i class="fa fa-trophy"></i> 🎉 成功触发水平越权 (Horizontal BAC / IDOR)！
                                        </h4>
                                        <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">
                                            你当前登录的 Session 身份为 <code><?php echo htmlspecialchars($session_user); ?></code>，但成功通过篡改 <code>username=<?php echo htmlspecialchars($member_data['username']); ?></code> 横向窃取了其他平级用户的私密档案！
                                        </p>
                                        <div>
                                            <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{HORIZONTAL_BAC_IDOR_PWNED}</span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php elseif (!empty($not_found_msg)): ?>
                                <div class="alert alert-warning" style="margin-top:10px; margin-bottom:0;">
                                    <i class="fa fa-warning"></i> <?php echo $not_found_msg; ?>
                                </div>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:18px; text-align:center; color:var(--text-muted); font-size:13px; margin-top:10px;">
                                    <i class="fa fa-info-circle"></i> 请点击上方 <b>“点击查看个人信息”</b> 或快捷越权按钮发起档案查询
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 右侧：实时鉴权审计对比面板 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 服务端鉴权状态实时透视</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="margin-bottom:10px; font-size:12.5px;">
                                        <tbody>
                                            <tr>
                                                <th style="width:170px; background:var(--bg-secondary);">服务端 Session 身份</th>
                                                <td><code>$_SESSION['op']['username'] = "<?php echo htmlspecialchars($session_user); ?>"</code></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">客户端 GET 请求参数</th>
                                                <td><code>$_GET['username'] = "<?php echo htmlspecialchars($queried_user ?: '(未提交)'); ?>"</code></td>
                                            </tr>
                                            <tr>
                                                <th style="background:var(--bg-secondary);">对象归属一致性校验</th>
                                                <td>
                                                    <?php if ($queried_user === ''): ?>
                                                        <span class="label label-default">等待请求</span>
                                                    <?php elseif ($idor_triggered): ?>
                                                        <span class="label label-danger">❌ 身份不匹配（服务端缺失校验，放行越权！）</span>
                                                    <?php else: ?>
                                                        <span class="label label-success">✅ 身份一致</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="cyber-principle-box" style="margin-bottom:0;">
                                    <p style="margin-bottom:0;">
                                        <b>核心教训：</b>前端表单中的 <code>type="hidden"</code> 隐藏域或只读属性 <code>readonly</code> <b>绝不等同于安全控制</b>。任何来自客户端的参数（GET、POST、Cookie、HTTP Header）均可被攻击者任意篡改。
                                    </p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 越权攻击 Payload 示例</h4>
                                <div style="margin-bottom:10px;">
                                    <span style="font-size:12.5px; color:var(--text-secondary); font-weight:600;">直接篡改 URL 查询参数读取 kobe 档案：</span>
                                    <div class="cyber-code-container">
                                        <span class="cyber-code-text">op1_mem.php?username=kobe&amp;submit=点击查看个人信息</span>
                                        <button class="cyber-copy-btn" onclick="copyToClipboard('op1_mem.php?username=kobe&submit=点击查看个人信息')">复制</button>
                                    </div>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 漏洞代码与安全修复对比</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">查看安全修复代码 (Remediated Code)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ❌ 错误写法：直接用 $_GET['username'] 去查库
$username = escape($link, $_GET['username']);

// ✅ 正确写法：查询当前个人资料时，强制以服务端 Session 中的身份为准
$username = escape($link, $_SESSION['op']['username']);
$query = "SELECT * FROM member WHERE username='$username'";
</pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function tamperAndSubmit(targetUser) {
    document.getElementById('query_username').value = targetUser;
    document.getElementById('op1QueryForm').submit();
}
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('已复制到剪贴板！');
    });
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
