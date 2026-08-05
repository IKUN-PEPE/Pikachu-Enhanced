<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[73] = 'active open';
$ACTIVE[78] = 'active';
$ACTIVE[73] = 'active open';
$ACTIVE[78] = 'active';

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
                <li class="active">批量赋值 (垂直越权)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📝 Mass Assignment (批量赋值) - 现代垂直越权</h2>
                <p>在使用 ORM (对象关系映射) 和现代框架开发时，开发者为了方便，常常将前端传来的 JSON 数据一键绑定到后端的 User 对象上并保存。</p>
                <p>这种机制如果不加白名单限制，攻击者可以在 JSON 负载中偷偷混入高权限字段（如 <code>role: "admin"</code>），直接在修改资料的同时完成提权操作！</p>
                <p><strong>攻击挑战：</strong>请使用 Burp Suite 拦截下方的“保存修改”请求。请求体是一个 JSON 数据。试着在 JSON 里面追加一个 <code>role</code> 字段并将其值设为 <code>admin</code>。如果你提权成功，你将会看到隐藏的 Flag！</p>
                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-warning">
                            <div class="panel-heading">个人资料修改</div>
                            <div class="panel-body">
                                <form id="profileForm" onsubmit="return false;">
                                    <div class="form-group">
                                        <label>邮箱地址：</label>
                                        <input type="email" class="form-control" id="email" value="lucy@pikachu.local">
                                    </div>
                                    <div class="form-group">
                                        <label>联系电话：</label>
                                        <input type="text" class="form-control" id="phone" value="13800138000">
                                    </div>
                                    <button id="saveBtn" class="btn btn-warning"><i class="fa fa-save"></i> 保存修改 (发送 JSON)</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div id="resultPanel" style="display: none;"></div>
                        <div class="well">
                            <h5>💡 API 文档分析：</h5>
                            <p>正常的前端发送的内容是：</p>
                            <pre><code>{
    "email": "xxx@xxx.com",
    "phone": "138xxx"
}</code></pre>
                            <p>但数据库里，用户的结构可能是：</p>
                            <pre><code>{
    "uid": 1001,
    "email": "...",
    "phone": "...",
    "role": "user" 
}</code></pre>
                            <p>你能想到怎么做吗？</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('saveBtn').addEventListener('click', function() {
    const payload = {
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value
    };
    
    fetch('../api/update_profile.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        const resPanel = document.getElementById('resultPanel');
        resPanel.style.display = 'block';
        if(data.status === 'success') {
            let html = `<div class="alert alert-success">✅ 更新成功！当前角色: <strong>${data.user.role}</strong></div>`;
            if(data.user.role === 'admin') {
                html += `<div class="alert alert-danger">🚨 越权提权成功！你已成为超级管理员。<br>请收取你的年终奖 Flag: <code>FLAG{M4SS_A551GNM3NT_R00T}</code></div>`;
            }
            resPanel.innerHTML = html;
        } else {
            resPanel.innerHTML = `<div class="alert alert-danger">❌ 更新失败。</div>`;
        }
    })
    .catch(err => console.error(err));
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


