<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (GET) 会员资料修改接口
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[27] = 'active';

$link = connect();

// 判断是否登录，没有登录不能访问
if (!check_csrf_login($link)) {
    header("location:csrf_get_login.php");
    exit();
}

$edit_msg = "";

if (isset($_GET['submit'])) {
    if (!empty($_GET['sex']) && !empty($_GET['phonenum']) && !empty($_GET['add']) && !empty($_GET['email'])) {
        $getdata = escape($link, $_GET);
        $username_safe = escape($link, $_SESSION['csrf']['username']);

        $query = "update member set sex='{$getdata['sex']}', phonenum='{$getdata['phonenum']}', address='{$getdata['add']}', email='{$getdata['email']}' where username='{$username_safe}'";
        $result = execute($link, $query);

        if ($result) {
            header("location:csrf_get.php");
            exit();
        } else {
            $edit_msg = "资料修改失败，请重试！";
        }
    } else {
        $edit_msg = "请将所有字段填写完整！";
    }
}

// 获取当前用户信息
$username_safe = escape($link, $_SESSION['csrf']['username']);
$query = "select * from member where username='{$username_safe}'";
$result = execute($link, $query);
$data = mysqli_fetch_assoc($result);

$name = $data['username'] ?? '';
$sex = $data['sex'] ?? 'boy';
$phonenum = $data['phonenum'] ?? '';
$add = $data['address'] ?? '';
$email = $data['email'] ?? '';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.edit-form-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}
.form-field-group {
    margin-bottom: 18px;
}
.form-field-group label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.form-input-control {
    width: 100%;
    padding: 10px 14px !important;
    background: var(--bg-secondary) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 8px !important;
    color: var(--text-primary) !important;
    font-size: 13.5px !important;
    transition: all 0.2s ease !important;
}
.form-input-control:focus {
    outline: none !important;
    border-color: #06b6d4 !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15) !important;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li><a href="csrf_get.php">CSRF (GET) 会员中心</a></li>
                <li class="active">修改个人资料</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ✏️ CSRF (GET) - 个人信息修改表单
                        <span class="cyber-badge-chip">无 CSRF Token · GET 提交</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当前表单使用 <code>method="GET"</code> 传输修改参数。当表单提交时，所有敏感参数将直接暴露在 URL Query 字符串中，后端直接执行更新，未进行任何来源凭证（Token / Referer）校验。
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-md-12" style="margin-bottom:20px;">
                        <div class="edit-form-card">
                            <h4 style="margin:0 0 18px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-user-edit" style="color:#06b6d4;"></i> 编辑会员档案
                            </h4>

                            <?php if (!empty($edit_msg)): ?>
                                <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:18px;">
                                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($edit_msg); ?>
                                </div>
                            <?php endif; ?>

                            <form method="GET" action="csrf_get_edit.php">
                                <div class="form-field-group">
                                    <label><i class="fa fa-user"></i> 用户名 (不可修改)：</label>
                                    <input type="text" class="form-input-control" value="<?php echo htmlspecialchars($name); ?>" disabled style="opacity:0.7; cursor:not-allowed;" />
                                </div>

                                <div class="form-field-group">
                                    <label><i class="fa fa-venus-mars"></i> 性 别：</label>
                                    <select name="sex" class="form-input-control">
                                        <option value="boy" <?php if ($sex === 'boy') echo 'selected'; ?>>boy (男)</option>
                                        <option value="girl" <?php if ($sex === 'girl') echo 'selected'; ?>>girl (女)</option>
                                    </select>
                                </div>

                                <div class="form-field-group">
                                    <label><i class="fa fa-phone"></i> 手机号码：</label>
                                    <input type="text" name="phonenum" class="form-input-control" value="<?php echo htmlspecialchars($phonenum); ?>" required />
                                </div>

                                <div class="form-field-group">
                                    <label><i class="fa fa-map-marker"></i> 住 址：</label>
                                    <input type="text" name="add" class="form-input-control" value="<?php echo htmlspecialchars($add); ?>" required />
                                </div>

                                <div class="form-field-group">
                                    <label><i class="fa fa-envelope"></i> 电子邮箱：</label>
                                    <input type="email" name="email" class="form-input-control" value="<?php echo htmlspecialchars($email); ?>" required />
                                </div>

                                <div style="display:flex; gap:12px; margin-top:24px;">
                                    <button type="submit" name="submit" value="submit" class="btn btn-info" style="flex:1; border-radius:8px; font-weight:700; padding:10px; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none;">
                                        <i class="fa fa-check"></i> 提交保存修改 (GET 传输)
                                    </button>
                                    <a href="csrf_get.php" class="btn btn-default" style="border-radius:8px; padding:10px 18px;">
                                        取消
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Tips -->
                    <div class="col-lg-5 col-md-12" style="margin-bottom:20px;">
                        <div class="edit-form-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-shield" style="color:#10b981;"></i> 安全代码审计视点
                            </h4>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; color:var(--text-secondary); line-height:1.7;">
                                <span style="color:#64748b;">// 危险漏洞代码示例：</span><br/>
                                <span style="color:#ef4444;">if(isset($_GET['submit'])){</span><br/>
                                &nbsp;&nbsp;$update = "UPDATE member SET ...";<br/>
                                &nbsp;&nbsp;mysqli_query($link, $update);<br/>
                                <span style="color:#ef4444;">}</span>
                            </div>
                            <p style="font-size:13px; color:var(--text-secondary); line-height:1.7; margin-top:14px;">
                                ⚠️ <b>代码缺陷审计结论：</b> 服务端直接从 <code>$_GET</code> 中获取参数并执行 <code>UPDATE</code> 数据库操作，没有任何防护机制验证请求发起者的真实意图。
                            </p>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="csrf_get.php" class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-arrow-left"></i> 返回个人会员中心
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
