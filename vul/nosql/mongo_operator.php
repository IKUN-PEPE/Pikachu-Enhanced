<?php
/**
 * Pikachu-Enhanced v2.0 - 🔴 MongoDB 操作符注入漏洞 (NoSQL Operator Injection)
 */
$ACTIVE = array_fill(0, 400, '');
$ACTIVE[162] = 'active open';
$ACTIVE[350] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$message = "";
$error_msg = "";
$search_results = [];
$is_dumped = false;
$flag = "flag{MongoDB_Mongoose_Operator_Ne_Dump_Exfiltrated_2026}";

// 模拟 MongoDB 的 JSON 数据集合
$users_db = [
    ['id' => 1, 'username' => 'admin', 'role' => 'admin', 'email' => 'admin@pika.com', 'secret' => 'Vault: ' . $flag],
    ['id' => 2, 'username' => 'alice', 'role' => 'finance', 'email' => 'alice@pika.com', 'secret' => 'Payroll Access: active'],
    ['id' => 3, 'username' => 'bob', 'role' => 'it', 'email' => 'bob@pika.com', 'secret' => 'VPN Key: 8f4a2b9d'],
    ['id' => 4, 'username' => 'charlie', 'role' => 'guest', 'email' => 'charlie@pika.com', 'secret' => 'No access'],
    ['id' => 5, 'username' => 'dave', 'role' => 'devops', 'email' => 'dave@pika.com', 'secret' => 'AWS_SECRET: AKIAV...'],
];

// 核心处理逻辑：模拟 Express req.query 配合 Mongoose 查询的行为
if (isset($_GET['search'])) {
    $role_query = $_GET['role'] ?? '';
    
    // PHP 的 $_GET 自动把 ?role[$ne]=guest 转换成 ['ne' => 'guest'] (PHP会剥离$)，
    // 为了更贴近 Node.js Express 原生体验，我们需要从原生 QUERY_STRING 中解析。
    $raw_query = $_SERVER['QUERY_STRING'];
    
    // 手动解析，支持带 $ 的键，如 role[$testing]=1
    $role_parsed = '';
    $operator = '';
    $op_value = '';
    
    if (preg_match('/role\[\$([^\]]+)\]=([^&]*)/', $raw_query, $matches)) {
        // 发现类似 role[$testing]=1 或 role[$ne]=guest 的操作符查询
        $operator = $matches[1];
        $op_value = urldecode($matches[2]);
        $role_parsed = ['$' . $operator => $op_value];
    } else {
        $role_parsed = $_GET['role'] ?? '';
    }

    if (is_array($role_parsed)) {
        // 检查操作符是否合法，模拟 Mongoose Schema 检查
        $allowed_ops = ['$ne', '$eq', '$gt', '$lt', '$in'];
        $op_key = key($role_parsed);
        
        if (!in_array($op_key, $allowed_ops)) {
            // Mongoose CastError 仿真报错
            $error_msg = 'MongoServerError: unknown operator: ' . htmlspecialchars($op_key) . '<br>';
            $error_msg .= 'CastError: Cast to string failed for value "{ \'' . htmlspecialchars($op_key) . '\': \'' . htmlspecialchars(current($role_parsed)) . '\' }" (type Object) at path "role" for model "User"';
        } else {
            // 模拟 MongoDB 执行查询
            if ($op_key === '$ne') {
                $is_dumped = true; // 因为 $ne 通常会匹配几乎所有记录
                foreach ($users_db as $user) {
                    if ($user['role'] !== current($role_parsed)) {
                        $search_results[] = $user;
                    }
                }
            } else if ($op_key === '$eq') {
                foreach ($users_db as $user) {
                    if ($user['role'] === current($role_parsed)) {
                        $search_results[] = $user;
                    }
                }
            }
        }
    } else {
        // 普通字符串查询
        foreach ($users_db as $user) {
            if ($role_parsed === '' || $user['role'] === $role_parsed) {
                $search_results[] = $user;
            }
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="nosql.php">新型数据库安全</a></li>
                <li class="active">🔴 MongoDB 操作符注入与全库导出</li>
            </ul>
        </div>
        <div class="page-content">

            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header smaller lighter blue" style="font-weight:bold; font-family:'Microsoft YaHei', sans-serif;">
                        <i class="ace-icon fa fa-database"></i> MongoDB NoSQL 操作符注入实战 (Operator Injection)
                    </h3>
                    
                    <div class="alert alert-info" style="border-radius:10px; background:linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%); border-left:4px solid #3b82f6;">
                        <strong><i class="fa fa-info-circle"></i> 漏洞说明：</strong><br>
                        当 Node.js 后端使用 Express 的 <code>qs</code> 模块解析查询字符串，并将未经验证的用户输入直接传递给 <b>Mongoose/MongoDB</b> 查询驱动时，攻击者可以通过构造特殊的 URL 参数（如：<code>?role[$ne]=guest</code>）来改变查询语义。<br>
                        🎯 <b>任务目标：</b><br>
                        1. 构造无效的操作符（如：<code>?role[$testing]=1</code>）触发 Mongoose 的 <code>CastError</code> 报错，确认漏洞存在。<br>
                        2. 构造 <code>$ne</code>（Not Equal）操作符绕过常规检索限制，实现<b>全量用户数据导出</b>，寻找隐藏在管理员账户中的 Flag！
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="widget-box" style="border-radius:12px; box-shadow:0 6px 16px rgba(0,0,0,0.1); border:none;">
                                <div class="widget-header widget-header-flat" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                    <h4 class="widget-title"><i class="fa fa-search"></i> 员工目录检索 (API Simulation)</h4>
                                </div>
                                <div class="widget-body" style="background:#f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                                    <div class="widget-main" style="padding: 24px;">
                                        <form class="form-horizontal" method="GET" action="">
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label no-padding-right" style="font-weight:bold; color:#475569;"> 部门角色 (Role): </label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="role" class="col-xs-10 col-sm-8" placeholder="例如：it, finance, guest" value="<?php echo htmlspecialchars($_GET['role'] ?? ''); ?>" style="border-radius:6px; border:1px solid #cbd5e1; padding:6px 12px;" />
                                                </div>
                                            </div>
                                            <div class="space-4"></div>
                                            <div class="form-group">
                                                <div class="col-sm-offset-3 col-sm-9">
                                                    <button class="btn btn-sm btn-primary" type="submit" name="search" value="1" style="border-radius:6px; border:none; background: linear-gradient(90deg, #3b82f6, #2563eb);">
                                                        <i class="ace-icon fa fa-search bigger-110"></i> 执行查询
                                                    </button>
                                                    
                                                    <a href="?role[$testing]=1&search=1" class="btn btn-sm btn-warning" style="border-radius:6px; border:none; margin-left: 5px;">
                                                        <i class="ace-icon fa fa-bug bigger-110"></i> 触发 Mongoose 报错
                                                    </a>
                                                    
                                                    <a href="?role[$ne]=guest&search=1" class="btn btn-sm btn-danger" style="border-radius:6px; border:none; margin-left: 5px;">
                                                        <i class="ace-icon fa fa-bomb bigger-110"></i> 导出全库 ($ne)
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <?php if (isset($_GET['search'])): ?>
                                        <div style="margin-top:20px; padding:15px; background:#1e1e1e; border-radius:8px; color:#a3be8c; font-family:Consolas, monospace; font-size:13px; overflow-x:auto;">
                                            <span style="color:#81a1c1;">// Node.js Express Request Query Parser</span><br>
                                            <span style="color:#d8dee9;">req.query = <?php 
                                                $req_query = [];
                                                if (isset($op_key) && $op_key !== '') {
                                                    $req_query = ['role' => [$op_key => $op_value]];
                                                } else {
                                                    $req_query = ['role' => $_GET['role'] ?? ''];
                                                }
                                                echo json_encode($req_query, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                                            ?>;</span><br><br>
                                            <span style="color:#81a1c1;">// Mongoose MongoDB Query Executed</span><br>
                                            <span style="color:#d8dee9;">db.users.find(req.query).exec();</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="widget-box" style="border-radius:12px; box-shadow:0 6px 16px rgba(0,0,0,0.1); border:none;">
                                <div class="widget-header widget-header-flat" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                    <h4 class="widget-title"><i class="fa fa-list"></i> MongoDB 查询结果响应</h4>
                                </div>
                                <div class="widget-body" style="background:#f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                                    <div class="widget-main" style="padding: 20px;">
                                        
                                        <?php if ($error_msg !== ""): ?>
                                            <div class="alert alert-danger" style="border-radius:8px; font-family:Consolas, monospace;">
                                                <i class="fa fa-exclamation-triangle"></i> <b>Unhandled Promise Rejection:</b><br><br>
                                                <?php echo $error_msg; ?>
                                            </div>
                                        <?php elseif (isset($_GET['search'])): ?>
                                            <?php if ($is_dumped && count($search_results) > 2): ?>
                                                <div class="alert alert-success" style="border-radius:8px; font-weight:bold;">
                                                    <i class="fa fa-check-circle"></i> 成功通过 $ne 绕过查询条件！全库数据已导出！
                                                </div>
                                            <?php endif; ?>
                                            
                                            <table class="table table-striped table-bordered table-hover" style="background:white; border-radius:6px; overflow:hidden;">
                                                <thead style="background:#f1f5f9;">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Username</th>
                                                        <th>Role</th>
                                                        <th>Email</th>
                                                        <th>Secret Data</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (count($search_results) > 0): ?>
                                                        <?php foreach ($search_results as $row): ?>
                                                            <tr>
                                                                <td><?php echo $row['id']; ?></td>
                                                                <td style="font-weight:bold; color:#0ea5e9;"><?php echo htmlspecialchars($row['username']); ?></td>
                                                                <td><span class="label label-<?php echo ($row['role']=='admin')?'danger':'info';?>"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                                <td style="color:#ef4444; font-family:monospace;"><?php echo htmlspecialchars($row['secret']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center" style="color:#94a3b8;">未找到匹配的用户，或者查询条件为空。</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="text-center" style="color:#94a3b8; padding:30px;">
                                                <i class="fa fa-database fa-3x" style="color:#cbd5e1; margin-bottom:15px;"></i><br>
                                                等待发起查询，检索 MongoDB 集合...
                                            </div>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /row -->
                    
                    <div class="space-12"></div>
                    
                    <div class="widget-box transparent">
                        <div class="widget-header">
                            <h4 class="widget-title lighter"><i class="fa fa-shield"></i> NoSQL 注入防御与修复方案</h4>
                        </div>
                        <div class="widget-body">
                            <div class="widget-main padding-12">
                                <div>
                                    <b>1. 使用 <code>mongo-sanitize</code> 清理输入：</b><br>
                                    Node.js 应用可使用 <code>express-mongo-sanitize</code> 库剥离键名中的 <code>$</code> 和 <code>.</code> 字符，阻止操作符注入。
                                </div>
                                <div style="margin-top:10px;">
                                    <b>2. 强制类型转换 (Schema Type Casting)：</b><br>
                                    在将 <code>req.query.role</code> 传递给 Mongoose 前，强制将其转为字符串：<code>db.users.find({ role: String(req.query.role) })</code>，使其成为字面量而失去操作符意义。
                                </div>
                                <div style="margin-top:10px;">
                                    <b>3. 关闭 Express 的 extended 查询解析：</b><br>
                                    设置 <code>app.use(express.urlencoded({ extended: false }))</code>，确保 <code>req.query</code> 和 <code>req.body</code> 只包含字符串，拒绝解析复杂的嵌套对象数组。
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /row -->

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
