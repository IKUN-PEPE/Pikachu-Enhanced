<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[73] = 'active open';
$ACTIVE[77] = 'active';
$ACTIVE[73] = 'active open';
$ACTIVE[77] = 'active';

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="../op.php">Over Permission</a>
                </li>
                <li class="active">BOLA (API 越权)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🌐 BOLA (Broken Object Level Authorization) - 现代水平越权</h2>
                <p>在现代前后端分离架构中，前端常常通过 AJAX 或 Fetch 调用 RESTful API 获取数据。</p>
                <p>这里的 "获取个人信息" 不再是重定向到一个新的 PHP 页面，而是向后端 API 发送了一个形如 <code>/api/user_info.php?uid=1001</code> 的请求。</p>
                <p><strong>攻击挑战：</strong>打开浏览器的开发者工具 (F12) -> Network (网络) 面板。点击下方按钮获取你的数据，并观察 XHR 请求。尝试篡改 <code>uid</code> 获取其他用户的敏感信息（如隐藏的 API Key 和高额余额）！</p>
                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">我的账户面板 (当前登录 UID: 1001)</div>
                            <div class="panel-body">
                                <button id="fetchDataBtn" class="btn btn-primary"><i class="fa fa-cloud-download"></i> 加载我的数据</button>
                                <div id="userDataPanel" style="display: none; margin-top: 20px;">
                                    <ul class="list-group">
                                        <li class="list-group-item"><strong>UID:</strong> <span id="u_uid"></span></li>
                                        <li class="list-group-item"><strong>用户名:</strong> <span id="u_name"></span></li>
                                        <li class="list-group-item"><strong>邮箱:</strong> <span id="u_email"></span></li>
                                        <li class="list-group-item list-group-item-warning"><strong>账户余额:</strong> $<span id="u_balance"></span></li>
                                        <li class="list-group-item list-group-item-danger"><strong>API Key:</strong> <code id="u_apikey"></code></li>
                                    </ul>
                                </div>
                                <div id="errorPanel" class="alert alert-danger" style="display: none; margin-top: 20px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="well">
                            <h5>💡 实战提示：</h5>
                            <p>在真实的渗透测试中，如果后端仅凭传入的 ID 查询数据库，而<strong>没有校验该 ID 是否属于当前会话的持有者</strong>，就会产生 BOLA (水平越权)。</p>
                            <p>你除了在浏览器 Network 里重放请求，也可以使用 Burp Suite 的 Intruder 模块对 <code>uid</code> 参数进行爆破 (如 1000 - 1010)。</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fetchDataBtn').addEventListener('click', function() {
    // 模拟现代前端通过 API 获取数据，这里假设当前用户的 uid 是 1001
    const currentUid = 1001; 
    
    fetch(`../api/user_info.php?uid=${currentUid}`)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('userDataPanel').style.display = 'block';
                document.getElementById('errorPanel').style.display = 'none';
                document.getElementById('u_uid').innerText = data.data.uid;
                document.getElementById('u_name').innerText = data.data.username;
                document.getElementById('u_email').innerText = data.data.email;
                document.getElementById('u_balance').innerText = data.data.balance;
                document.getElementById('u_apikey').innerText = data.data.api_key;
            } else {
                document.getElementById('userDataPanel').style.display = 'none';
                document.getElementById('errorPanel').style.display = 'block';
                document.getElementById('errorPanel').innerText = data.message;
            }
        })
        .catch(err => {
            console.error(err);
        });
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


