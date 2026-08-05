<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[144] = 'active open';
$ACTIVE[146] = 'active';
$ACTIVE[144] = 'active open';
$ACTIVE[146] = 'active';
$PIKA_ROOT_DIR = "../../";

// Mock Database
$users = array(
    "1" => array("name" => "vince", "role" => "user", "phone" => "13800001111", "balance" => 12.5),
    "2" => array("name" => "admin", "role" => "admin", "phone" => "18888888888", "balance" => 9999999.0)
);

// REST API Handler
if(isset($_GET['action']) && $_GET['action'] === 'get_user'){
    header('Content-Type: application/json');
    $id = isset($_GET['id']) ? (string)$_GET['id'] : '';
    // Vulnerability: No check if the requesting user actually OWNS this ID!
    if(isset($users[$id])){
        echo json_encode(array("status" => "success", "data" => $users[$id]));
    }else{
        echo json_encode(array("status" => "error", "message" => "User not found"));
    }
    exit;
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="api.php">现代 API 安全</a></li>
                <li class="active">BOLA (对象级别越权)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p>BOLA (Broken Object Level Authorization)</p>
                <p>在这个场景中，你登录为普通用户 <b>vince</b> (内部 ID: 1)。</p>
                <p>前端通过请求 <code>/api_security/bola.php?action=get_user&id=1</code> 来获取你的个人信息。</p>
                <p>请尝试通过修改 API 请求，获取管理员 <b>admin</b> (假设 ID: 2) 的隐私数据。</p>
                
                <button class="btn btn-sm btn-primary" onclick="fetchUserData(1)">查看我的个人信息 (ID=1)</button>
                <button class="btn btn-sm btn-danger" onclick="fetchUserData(2)">越权查看管理员信息 (ID=2)</button>

                <div id="user_info_display" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; display: none;">
                    <h4>API 响应数据:</h4>
                    <pre id="json_output"></pre>
                </div>

                <script>
                    function fetchUserData(userId) {
                        fetch(`bola.php?action=get_user&id=${userId}`)
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('user_info_display').style.display = 'block';
                                document.getElementById('json_output').textContent = JSON.stringify(data, null, 4);
                            })
                            .catch(err => alert("请求失败!"));
                    }
                </script>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


