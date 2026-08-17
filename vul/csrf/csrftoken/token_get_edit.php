<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (Token) 会员资料修改与令牌防御校验
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[29] = 'active';

$link = connect();

// 判断是否登录，没有登录不能访问
if (!check_csrf_login($link)) {
    header("location:token_get_login.php");
    exit();
}

$security_alert = "";
$edit_msg = "";

if (isset($_GET['submit'])) {
    $submitted_token = $_GET['token'] ?? '';
    $session_token = $_SESSION['token'] ?? '';

    // 核心 Token 严格比对
    if (!empty($submitted_token) && !empty($session_token) && hash_equals($session_token, $submitted_token)) {
        if (!empty($_GET['sex']) && !empty($_GET['phonenum']) && !empty($_GET['add']) && !empty($_GET['email'])) {
            $getdata = escape($link, $_GET);
            $username_safe = escape($link, $_SESSION['csrf']['username']);

            $query = "update member set sex='{$getdata['sex']}', phonenum='{$getdata['phonenum']}', address='{$getdata['add']}', email='{$getdata['email']}' where username='{$username_safe}'";
            $result = execute($link, $query);

            if ($result) {
                // 每次操作后刷新 Token (One-time Token 最佳安全实践)
                set_token();
                header("location:token_get.php");
                exit();
            } else {
                $edit_msg = "资料修改失败，请重试！";
            }
        } else {
            $edit_msg = "请将所有字段填写完整！";
        }
    } else {
        // Token 校验失败（拦截 CSRF 伪造攻击）
        $security_alert = "🛡️ <b>Anti-CSRF Token 拦截触发！</b> 提交的 Token [" . htmlspecialchars($submitted_token) . "] 与服务器 Session Token 不匹配或缺失，跨站伪造请求已被成功阻断！";
        // 遭遇攻击或错误后刷新 Token
        set_token();
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

// 确保存在最新的合法 Token
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    set_token();
}
$current_valid_token = $_SESSION['token'];

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
    border-color: #10b981 !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li><a href="token_get.php">CSRF Token 会员中心</a></li>
                <li class="active">修改个人资料 (Token 保护)</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ✏️ CSRF Token - 个人信息修改表单
                        <span class="cyber-badge-chip" style="border-color:#10b981; color:#34d399; background:rgba(16,185,129,0.15);">Token 完整性校验</span>
                    </h1>
                    <p class="cyber-desc-text">
                        当前表单包含隐藏字段 <code>&lt;input type="hidden" name="token" value="..." /&gt;</code>。当表单提交时，后端会使用安全哈希严格比对 <code>$_GET['token']</code> 与 <code>$_SESSION['token']</code>。如果伪造请求未包含合法 Token，服务端将直接终止执行！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-md-12" style="margin-bottom:20px;">
                        <div class="edit-form-card">
                            <h4 style="margin:0 0 18px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-user-edit" style="color:#10b981;"></i> 编辑会员档案 (Token 保护)
                            </h4>

                            <?php if (!empty($security_alert)): ?>
                                <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:12px 16px; margin-bottom:18px;">
                                    <?php echo $security_alert; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($edit_msg)): ?>
                                <div class="alert alert-warning" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:18px;">
                                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($edit_msg); ?>
                                </div>
                            <?php endif; ?>

                            <form method="GET" action="token_get_edit.php">
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

                                <div class="form-field-group" style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px;">
                                    <label style="color:#10b981;"><i class="fa fa-key"></i> 自动注入的 Anti-CSRF Token 参数：</label>
                                    <input type="text" name="token" class="form-input-control" value="<?php echo htmlspecialchars($current_valid_token); ?>" style="font-family:monospace; color:#10b981; font-weight:700;" />
                                    <small style="color:var(--text-muted); font-size:11.5px; display:block; margin-top:4px;">
                                        * 此参数通常设置为 <code>type="hidden"</code> 隐藏域，此处为了教学演示公开展示。
                                    </small>
                                </div>

                                <div style="display:flex; gap:12px; margin-top:24px;">
                                    <button type="submit" name="submit" value="submit" class="btn btn-success" style="flex:1; border-radius:8px; font-weight:700; padding:10px; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                                        <i class="fa fa-check"></i> 提交保存修改 (带 Token 校验)
                                    </button>
                                    <a href="token_get.php" class="btn btn-default" style="border-radius:8px; padding:10px 18px;">
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
                                <i class="fa fa-shield" style="color:#10b981;"></i> 安全防御核心逻辑
                            </h4>
                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; font-family:'Fira Code', monospace; font-size:12px; color:var(--text-secondary); line-height:1.7;">
                                <span style="color:#64748b;">// 服务端严格 Token 校验：</span><br/>
                                <span style="color:#10b981;">if (hash_equals($_SESSION['token'], $_GET['token'])) {</span><br/>
                                &nbsp;&nbsp;<span style="color:#64748b;">// 1. 校验通过，执行更新</span><br/>
                                &nbsp;&nbsp;mysqli_query($link, $update);<br/>
                                &nbsp;&nbsp;<span style="color:#64748b;">// 2. 刷新一次性令牌</span><br/>
                                &nbsp;&nbsp;set_token();<br/>
                                <span style="color:#10b981;">} else {</span><br/>
                                &nbsp;&nbsp;<span style="color:#ef4444;">// 拦截并拒绝非法跨站请求！</span><br/>
                                &nbsp;&nbsp;die("CSRF Attack Blocked!");<br/>
                                <span style="color:#10b981;">}</span>
                            </div>
                            <p style="font-size:13px; color:var(--text-secondary); line-height:1.7; margin-top:14px;">
                                ✅ <b>安全收益：</b> 恶意第三方网站因同源策略无法获取当前用户的合法 Token，伪造请求因缺少/错配 Token 必定被服务端直接拦截。
                            </p>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="token_get.php" class="btn btn-default" style="border-radius:8px;">
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
