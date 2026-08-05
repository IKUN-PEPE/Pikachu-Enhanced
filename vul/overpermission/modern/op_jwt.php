<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[73] = 'active open';
$ACTIVE[79] = 'active';
$ACTIVE[73] = 'active open';
$ACTIVE[79] = 'active';

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once '../api/jwt_helper.php';

// 给访问这个页面的受害者直接颁发一个普通用户的 JWT
$payload = [
    "uid" => 1001,
    "username" => "lucy",
    "role" => "user",
    "iat" => time(),
    "exp" => time() + 3600
];
$jwt = JWTHelper::encode($payload);
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="../op.php">Over Permission</a>
                </li>
                <li class="active">JWT 伪造 (身份越权)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔑 JWT Security (JSON Web Token 身份越权)</h2>
                <p>现代微服务或分离式架构为了做到完全无状态，广泛使用 JWT 进行用户身份和权限认证。</p>
                <p>JWT 虽然使用了 Base64 编码，但默认是<strong>不加密</strong>的，只做了签名。如果后端签名校验存在缺陷（如弱密钥、支持 <code>alg: none</code>），攻击者就能随意篡改令牌里的 <code>role</code> 实现垂直提权。</p>
                <p><strong>攻击挑战：</strong> 系统已经在前端分配了你的个人 JWT，并保存在了下方的输入框中。点击按钮将使用此令牌访问后端的 Admin API。请使用 <a href="https://jwt.io" target="_blank">jwt.io</a> 解码它，将 <code>role</code> 改为 <code>admin</code>。你可以尝试暴力破解它的弱签名密钥，或者使用 <code>alg: none</code> 攻击来伪造一个新的令牌并覆盖下方的内容以进入后台！</p>
                <hr>

                <div class="row">
                    <div class="col-sm-8">
                        <div class="panel panel-info">
                            <div class="panel-heading">鉴权令牌 (JWT) 与请求测试</div>
                            <div class="panel-body">
                                <form id="jwtForm" onsubmit="return false;">
                                    <div class="form-group">
                                        <label>Authorization: Bearer</label>
                                        <textarea class="form-control" id="jwtToken" rows="3"><?php echo $jwt; ?></textarea>
                                    </div>
                                    <button id="accessBtn" class="btn btn-primary"><i class="fa fa-key"></i> 访问 /api/admin_dashboard.php</button>
                                </form>
                                <div id="adminPanel" style="display: none; margin-top: 20px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="well">
                            <h5>💡 实战提示：</h5>
                            <p>1. 弱密钥爆破：使用 hashcat 等工具破解该 JWT（提示：密钥是极其简单的 6 位数字）。破解后利用密钥自己重新签发一个 admin 令牌。</p>
                            <p>2. Alg None 攻击：将 JWT 头部的 <code>"alg"</code> 改为 <code>"none"</code>，删掉第三段签名部分（但保留两个小数点），重新发送。</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('accessBtn').addEventListener('click', function() {
    const token = document.getElementById('jwtToken').value.trim();
    
    fetch('../api/admin_dashboard.php', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(data => {
        const adminPanel = document.getElementById('adminPanel');
        adminPanel.style.display = 'block';
        if(data.status === 'success') {
            adminPanel.innerHTML = `<div class="alert alert-danger">
                <h4>👑 欢迎您，超级管理员！</h4>
                <p>${data.message}</p>
                <p>FLAG: <code>${data.flag}</code></p>
                </div>`;
        } else {
            adminPanel.innerHTML = `<div class="alert alert-warning">❌ 访问被拒绝：${data.message}</div>`;
        }
    })
    .catch(err => console.error(err));
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


