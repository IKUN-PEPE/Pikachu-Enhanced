<?php
/**
 * Pikachu-Enhanced v2.0 - JWT 管理后台 (JWT Admin Central Dashboard)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[124] = 'active';

if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    jwt_logout();
    setcookie('jwt_token', '', time() - 3600, '/');
    header('location:jwt_login.php');
    exit();
}

$link = connect();
$payload = false;
$username = 'unknown';
$role = '';
$level = 0;

if (isset($_COOKIE['jwt_token'])) {
    $payload = jwt_decode_insecure($_COOKIE['jwt_token']);
}

if (is_array($payload)) {
    $username = isset($payload['username']) ? $payload['username'] : (isset($payload['user']) ? $payload['user'] : 'unknown');
    $role = isset($payload['role']) ? $payload['role'] : 'guest';
    $level = isset($payload['level']) ? intval($payload['level']) : 0;
}

$is_admin = ($role === 'admin' || $role === 'superadmin' || $role === 'root' || $level === 1);

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">JWT 管理后台</li>
            </ul>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <?php if (!$payload) { ?>
                <div class="alert alert-warning" style="border-radius:10px;">
                    <h4><i class="fa fa-exclamation-triangle"></i> 未检测到有效 JWT 会话凭证</h4>
                    <p>请先前往 <a href="jwt_login.php" class="btn btn-primary btn-xs" style="margin-left:8px;">JWT 登录入口</a> 进行身份认证。</p>
                </div>
            <?php } elseif (!$is_admin) { ?>
                <div class="alert alert-danger" style="border-radius:10px;">
                    <h4><i class="fa fa-ban"></i> 403 Forbidden 权限不足</h4>
                    <p>当前识别身份：用户 <code><?php echo htmlspecialchars($username); ?></code> | 角色：<code><?php echo htmlspecialchars($role); ?></code> | 等级：<code><?php echo $level; ?></code></p>
                    <p>该管理中枢仅限管理员访问。请通过篡改 JWT 中的 <code>role=admin</code> 或 <code>level=1</code> 绕过鉴权！</p>
                    <p><a href="jwt_login.php" class="btn btn-default btn-xs">返回登录与篡改控制台</a></p>
                </div>
            <?php } else { ?>
                <div style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:14px; padding:24px; box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-subtle); padding-bottom:16px; margin-bottom:20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:800; color:var(--text-primary);">
                                <i class="fa fa-tachometer" style="color:var(--primary);"></i> Pikachu-Enhanced 核心管理控制台
                            </h3>
                            <span style="font-size:13px; color:var(--text-muted);">
                                登录身份：<b style="color:var(--primary);"><?php echo htmlspecialchars($username); ?></b> (Role: <?php echo htmlspecialchars($role); ?>, Level: <?php echo $level; ?>)
                            </span>
                        </div>
                        <a href="jwt_admin.php?logout=1" class="btn btn-danger btn-sm" style="border-radius:6px;">
                            <i class="fa fa-power-off"></i> 退出管理会话
                        </a>
                    </div>

                    <div class="alert alert-success" style="border-radius:10px; font-weight:600;">
                        <i class="fa fa-check-circle"></i> 越权访问成功！当前会话已通过 JWT 认证绕过进入超级管理员管理后台。
                    </div>

                    <h4 style="font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:14px;">用户全库机密列表：</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="font-size:13px;">
                            <thead>
                                <tr style="background:var(--bg-secondary);">
                                    <th>用户名</th>
                                    <th>性别</th>
                                    <th>手机号</th>
                                    <th>邮箱</th>
                                    <th>地址</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "select * from member";
                                $result = execute($link, $query);
                                while ($data = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($data['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($data['sex']) . "</td>";
                                    echo "<td>" . htmlspecialchars($data['phonenum']) . "</td>";
                                    echo "<td>" . htmlspecialchars($data['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($data['address']) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
