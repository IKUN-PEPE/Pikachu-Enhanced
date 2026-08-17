<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[192] = 'active open';
$ACTIVE['proto_213'] = 'active';
$ACTIVE[192] = 'active open';
$ACTIVE['proto_213'] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$rpc_response = "";
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rpc_method = $_POST['rpc_method'] ?? '';
    $rpc_payload = trim($_POST['rpc_payload'] ?? '');
    $data = @json_decode($rpc_payload, true);

    if ($rpc_method === 'UserService/UpdateProfile') {
        $uid = $data['user_id'] ?? 1002;
        $role = $data['role_id'] ?? 1;
        $uname = $data['username'] ?? 'unknown';

        if ($role == 99 || $role == 0 || strval($role) === "admin" || $uid == 1000) {
            $is_success = true;
            $rpc_response = "HTTP/2 200 OK\ncontent-type: application/grpc+proto\ngrpc-status: 0\n\n" .
                            "=== [Protobuf Decoded Response Buffer] ===\n" .
                            "{\n  \"status\": \"SUCCESS\",\n  \"updated_user_id\": " . intval($uid) . ",\n" .
                            "  \"new_role_assigned\": \"SUPER_ADMINISTRATOR\",\n" .
                            "  \"system_flag\": \"FLAG{GRPC_PROTOBUF_IDOR_ROLE_BYPASS_CHAMPION}\",\n" .
                            "  \"msg\": \"Profile updated via RPC stream without authorization ownership check.\"\n}";
        } else {
            $rpc_response = "HTTP/2 200 OK\ncontent-type: application/grpc+proto\ngrpc-status: 0\n\n" .
                            "{\n  \"status\": \"SUCCESS\",\n  \"updated_user_id\": " . intval($uid) . ",\n" .
                            "  \"new_role_assigned\": \"Standard_User\",\n" .
                            "  \"msg\": \"Profile updated successfully. No administrative privileges requested.\"\n}";
        }
    } else if ($rpc_method === 'AdminService/DumpSystemSecrets') {
        $is_success = true;
        $rpc_response = "HTTP/2 200 OK\ncontent-type: application/grpc+proto\ngrpc-status: 0\n\n" .
                        "=== [Protobuf Reflection Secret Dump] ===\n" .
                        "{\n  \"total_records\": 2,\n  \"secrets\": [\n    { \"key\": \"JWT_SECRET\", \"val\": \"Pikachu_Super_Secret_Key_2026\" },\n" .
                        "    { \"key\": \"MASTER_FLAG\", \"val\": \"FLAG{GRPC_PROTOBUF_IDOR_ROLE_BYPASS_CHAMPION}\" }\n  ]\n}";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="grpc.php">微服务 gRPC 接口</a></li>
                <li class="active">gRPC 越权与参数篡改</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>⚙️ gRPC 微服务接口 IDOR 越权与 Protobuf 参数提权</h2>
                <p>一个由 Golang 编写的核心鉴权微服务提供了基于 HTTP/2 的远程过程调用（gRPC）接口。客户端通过将 Protobuf 数据包发送给服务器实现用户基本信息的更新操作。在这个过程中，微服务服务端存在以下设计缺陷：</p>
                <ol>
                    <li>未在 gRPC 拦截器（Interceptor）中校对发送该请求的 Metadata Token 与目标待修改的 <code>user_id</code> 是否属于同一主体（<b>水平越权 BOLA</b>）。</li>
                    <li>在 Protobuf 结构定义 <code>message UpdateRequest</code> 中暴漏了内部预留的高权限属性 <code>int32 role_id = 3;</code>（其中 <code>1=User</code>, <code>99=Admin</code>），且服务端未校验普通员工修改该字段的权限（<b>垂直越权 BFLA</b>）。</li>
                </ol>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-retweet"></i> 伪造 gRPC 远程调用指令</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="rpc_method">目标远程 RPC 方法名 (Method)：</label>
                                <select class="form-control" name="rpc_method" id="rpc_method" onchange="switchPayload()">
                                    <option value="UserService/UpdateProfile" <?php if(($POST['rpc_method']??'')==='UserService/UpdateProfile') echo 'selected'; ?>>UserService / UpdateProfile (更新个人资料)</option>
                                    <option value="AdminService/DumpSystemSecrets" <?php if(($POST['rpc_method']??'')==='AdminService/DumpSystemSecrets') echo 'selected'; ?>>AdminService / DumpSystemSecrets (内部调试反射接口)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rpc_payload">Protobuf 序列化参数 (JSON 解码展示格式)：</label>
                                <textarea class="form-control" name="rpc_payload" id="rpc_payload" rows="6" style="font-family:monospace; background:#222; color:#a9b7c6;"><?php echo isset($_POST['rpc_payload']) ? htmlspecialchars($_POST['rpc_payload']) : "{\n  \"user_id\": 1002,\n  \"username\": \"pikachu_tester\",\n  \"role_id\": 1\n}"; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> 发送 gRPC 请求包</button>
                            <button type="button" class="btn btn-danger" onclick="fillAdminRole()"><i class="fa fa-arrow-up"></i> 越权提升 role_id=99</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-list-alt"></i> HTTP/2 Stream Protobuf 解包响应</h4>
                        <div class="panel <?php echo $is_success ? 'panel-danger' : 'panel-default'; ?>" style="margin-top: 10px;">
                            <div class="panel-heading"><b>gRPC 客户端解包流输出：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:<?php echo $is_success ? '#ff5555' : '#61dafb'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($rpc_response) ? htmlspecialchars($rpc_response) : "// [Connected to grpc.pikachu.local:50051]\n// 准备发送 HTTP/2 RPC Stream... 点击发送按钮执行调用。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function switchPayload() {
    var method = document.getElementById('rpc_method').value;
    if (method === 'AdminService/DumpSystemSecrets') {
        document.getElementById('rpc_payload').value = "{\n  \"debug_key\": \"internal_audit\",\n  \"limit\": 100\n}";
    } else {
        document.getElementById('rpc_payload').value = "{\n  \"user_id\": 1002,\n  \"username\": \"pikachu_tester\",\n  \"role_id\": 1\n}";
    }
}
function fillAdminRole() {
    document.getElementById('rpc_method').value = 'UserService/UpdateProfile';
    document.getElementById('rpc_payload').value = "{\n  \"user_id\": 1000,\n  \"username\": \"hacked_admin\",\n  \"role_id\": 99\n}";
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


