<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[168] = 'active open';
$ACTIVE[170] = 'active';
$ACTIVE[168] = 'active open';
$ACTIVE[170] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 模拟当前已登录的受害者用户
$current_logged_in_user = "victim_user";

// 模拟数据库中保存的用户绑定关系
// 假设这里是一个简易的文件或 session 存储来记录绑定关系
if (!isset($_SESSION['oauth_bindings'])) {
    $_SESSION['oauth_bindings'] = [
        'victim_user' => 'none',
        'hacker' => 'none'
    ];
}

$message = "";

// 模拟 OAuth 回调处理逻辑 (类似 /callback?code=xxxx)
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 【漏洞点】：这里没有验证 $_GET['state'] 参数是否与发起请求时的 session 中的 state 一致！
    
    // 模拟用 code 换取 token 并获取第三方用户信息
    // 假设攻击者提供的 code 总是解析为攻击者的第三方账号 (Evil_GitHub_Account)
    $third_party_account = "";
    if ($code === 'evil_code_12345') {
        $third_party_account = "Evil_GitHub_Account";
    } else if ($code === 'normal_code_67890') {
        $third_party_account = "Victim_GitHub_Account";
    } else {
        $third_party_account = "Unknown_Account_" . substr($code, 0, 5);
    }

    // 将当前登录用户与这个第三方账号进行绑定
    $_SESSION['oauth_bindings'][$current_logged_in_user] = $third_party_account;

    $message = "<div class='alert alert-success'>🎉 恭喜！您当前的系统账号 [{$current_logged_in_user}] 已经成功绑定了第三方账号：[{$third_party_account}]。</div>";
}

?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="oauth.php">高级认证体系安全</a></li>
                <li class="active">OAuth State 劫持</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🎭 OAuth 账号绑定劫持 (State Bypass)</h2>
                <p>在这个场景中，您当前已经登录为 <code>victim_user</code>。</p>
                <p>您的系统支持绑定 GitHub 账号。正常流程是：点击绑定 -> 跳转到 GitHub -> 同意授权 -> 携带 <code>code</code> 回跳到本页面完成绑定。</p>
                <p>但由于服务端在处理回跳的请求（Callback）时，<strong>没有校验 <code>state</code> 参数</strong>以防范 CSRF，这导致了什么后果？</p>
                <hr>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">受害者的账号状态</div>
                            <div class="panel-body">
                                <ul>
                                    <li>当前登录用户：<strong><?php echo $current_logged_in_user; ?></strong></li>
                                    <li>已绑定的第三方账号：<strong class="text-danger"><?php echo $_SESSION['oauth_bindings'][$current_logged_in_user]; ?></strong></li>
                                </ul>
                                <a href="?code=normal_code_67890" class="btn btn-success btn-sm">正常去绑定我的 GitHub</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="panel panel-danger">
                            <div class="panel-heading">黑客视角 (Attacker)</div>
                            <div class="panel-body">
                                <p>1. 黑客在自己的电脑上发起绑定请求，但在重定向回来之前拦截请求，获取到了他自己的专属 <code>code=evil_code_12345</code>。</p>
                                <p>2. 黑客将这个恶意链接发送给 <code>victim_user</code>（甚至隐藏在不可见的 img 标签里）。</p>
                                <p>请你模拟受害者，点击下面这个黑客发来的恶意链接：</p>
                                <a href="?code=evil_code_12345" class="btn btn-danger btn-sm">点我领取免费比特币 (恶意链接)</a>
                                <p class="text-muted" style="margin-top:10px;"><em>点击后，你会发现受害者的系统账号被悄悄绑定到了黑客的 GitHub 账号上！随后黑客只要点击"GitHub 登录"，就可以直接登入受害者的账号了！</em></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


