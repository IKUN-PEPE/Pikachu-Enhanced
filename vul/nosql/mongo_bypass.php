<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[162] = 'active open';
$ACTIVE[164] = 'active';
$ACTIVE[162] = 'active open';
$ACTIVE[164] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$message = "";

// 模拟 MongoDB 认证逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 假设这是前端传来的原生 JSON 或 PHP 自动解析的 application/x-www-form-urlencoded 数组
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 【漏洞点】：未校验输入类型！
    // 正常用户应该输入字符串："123456"
    // 攻击者如果在前端传 JSON，或者在 form 中传 password[$ne]=  ，这里的 $password 会变成一个数组：['$ne' => '']
    
    // 模拟 MongoDB 底层驱动处理 $password 变量时的逻辑行为
    $is_login_success = false;

    // 假设数据库里有个 admin 账号，密码非常复杂
    $db_admin_password = "SuperSecretPassword123!@#";

    if ($username === 'admin') {
        if (is_array($password) && isset($password['$ne'])) {
            // MongoDB 执行: db.users.find({username: 'admin', password: {$ne: ''}})
            // 这代表：寻找用户名是 admin，且密码不为空的用户。显然这恒成立！
            if ($db_admin_password !== $password['$ne']) {
                $is_login_success = true;
            }
        } else {
            // 普通字符串比对
            if ($password === $db_admin_password) {
                $is_login_success = true;
            }
        }
    }

    if ($is_login_success) {
        $message = "<div class='alert alert-success'>🎉 登录成功！欢迎回来，尊贵的 admin，您已成功绕过 MongoDB 认证！</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ 登录失败：账号或密码错误。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="nosql.php">新型数据库安全</a></li>
                <li class="active">MongoDB 认证绕过</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🗄️ MongoDB 认证绕过 (NoSQL Injection)</h2>
                <p>在 PHP (特别是使用老的 MongoDB 驱动或现代的 JSON Body 解析时)，如果未强制校验用户输入的数据类型必须是 String，攻击者就可以传入一个数组或 JSON 对象。</p>
                <p>例如，将密码字段传入 <code>{"$ne": ""}</code> (不等于空)。MongoDB 底层引擎将其当作条件操作符执行，从而无需知道真实密码即可绕过登录逻辑！</p>
                <hr>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-sm-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">管理员登录入口 (模拟 MongoDB)</div>
                            <div class="panel-body">
                                <form method="POST" id="loginForm">
                                    <div class="form-group">
                                        <label>用户名 (Username):</label>
                                        <input type="text" class="form-control" name="username" value="admin">
                                    </div>
                                    <div class="form-group">
                                        <label>密码 (Password):</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    <button type="submit" class="btn btn-primary">登录</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="alert alert-warning">
                            <h4>如何攻击？</h4>
                            <p>我们没有 admin 的真实密码。但我们可以利用 PHP 将表单名称转为数组的特性，或抓包修改请求：</p>
                            <p><strong>方法 1 (最简单):</strong> 按 F12 打开开发者工具，将 Password 输入框的 <code>name="password"</code> 修改为 <code>name="password[$ne]"</code>。随便输入点东西，点击登录即可！</p>
                            <p><strong>方法 2 (抓包):</strong> 拦截 POST 请求，将 Body 修改为：<code>username=admin&password[$ne]=1</code></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


