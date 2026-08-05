<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[144] = 'active open';
$ACTIVE[147] = 'active';
$ACTIVE[144] = 'active open';
$ACTIVE[147] = 'active';
$PIKA_ROOT_DIR = "../../";

session_start();

// Initialize dummy session data if not exists
if(!isset($_SESSION['ma_user'])){
    $_SESSION['ma_user'] = array(
        "username" => "vince",
        "email" => "vince@pikachu.local",
        "age" => 25,
        "is_admin" => false
    );
}

$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

if($method === 'POST'){
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if(is_array($data)){
        // Vulnerability: Mass Assignment!
        // The server merges all provided fields directly into the user object
        // instead of picking only the allowed ones (e.g. email, age).
        foreach($data as $key => $value){
            $_SESSION['ma_user'][$key] = $value;
        }
        header('Content-Type: application/json');
        echo json_encode(array("status" => "success", "message" => "Profile updated", "user" => $_SESSION['ma_user']));
        exit;
    }
}

if(isset($_GET['action']) && $_GET['action'] === 'reset'){
    session_destroy();
    header("Location: mass_assignment.php");
    exit;
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="api.php">现代 API 安全</a></li>
                <li class="active">批量赋值 (Mass Assignment)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p>批量赋值 (Mass Assignment)</p>
                <p>许多现代后端框架允许将前端传入的 JSON 数据自动映射（绑定）到数据库对象或 Session 对象中。</p>
                <p>如果开发者没有显式声明“哪些字段可以被修改”，攻击者就可以在 JSON 中注入敏感字段，比如 <code>"is_admin": true</code>。</p>
                
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h4>更新个人资料</h4>
                        <div class="form-group">
                            <label>邮箱:</label>
                            <input type="text" id="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['ma_user']['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>年龄:</label>
                            <input type="number" id="age" class="form-control" value="<?php echo (int)$_SESSION['ma_user']['age']; ?>">
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="updateProfile()">保存更改</button>
                        <br><br>
                        <p class="notice">提示：正常使用表单只能修改 email 和 age。使用 Burp 或浏览器控制台，在 JSON 中加入 <code>"is_admin": true</code> 试试！</p>
                    </div>
                    <div class="col-md-6">
                        <h4>当前账号状态</h4>
                        <pre id="status_output"><?php echo json_encode($_SESSION['ma_user'], JSON_PRETTY_PRINT); ?></pre>
                        <?php if($_SESSION['ma_user']['is_admin']): ?>
                            <div class="alert alert-danger">
                                🎉 恭喜！你已成功利用批量赋值漏洞成为管理员！
                            </div>
                        <?php endif; ?>
                        <a href="?action=reset" class="btn btn-xs btn-warning">重置环境</a>
                    </div>
                </div>

                <script>
                    function updateProfile() {
                        const payload = {
                            email: document.getElementById('email').value,
                            age: parseInt(document.getElementById('age').value, 10)
                        };
                        
                        fetch('mass_assignment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success'){
                                alert('更新成功！');
                                location.reload();
                            }
                        });
                    }
                </script>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


