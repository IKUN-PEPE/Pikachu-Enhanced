<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[188] = 'active open';
$ACTIVE[211] = 'active';
$ACTIVE[188] = 'active open';
$ACTIVE[211] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$api_result = "";
$action_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['s3_action'] ?? 'list';
    $object_key = trim($_POST['object_key'] ?? '');
    $put_content = trim($_POST['put_content'] ?? '');

    if ($action === 'list') {
        $action_type = "list";
        $api_result = '<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
  <Name>pikachu-enhanced-prod-assets</Name>
  <Prefix></Prefix>
  <MaxKeys>1000</MaxKeys>
  <IsTruncated>false</IsTruncated>
  <Contents>
    <Key>index.html</Key>
    <LastModified>2026-06-15T08:11:22.000Z</LastModified>
    <ETag>"b10a8db164e0754105b7a99be72e3fe5"</ETag>
    <Size>4520</Size>
    <StorageClass>STANDARD</StorageClass>
  </Contents>
  <Contents>
    <Key>images/logo.png</Key>
    <LastModified>2026-06-10T12:00:00.000Z</LastModified>
    <Size>18420</Size>
  </Contents>
  <Contents>
    <Key>secret_backup/backup_2026_prod_db.sql</Key>
    <LastModified>2026-06-30T23:59:01.000Z</LastModified>
    <Size>9845120</Size>
  </Contents>
  <Contents>
    <Key>secret_backup/cloud_root_credentials.json</Key>
    <LastModified>2026-06-30T23:59:05.000Z</LastModified>
    <Size>1240</Size>
  </Contents>
</ListBucketResult>';
    } else if ($action === 'get') {
        $action_type = "get";
        if (strpos($object_key, 'backup') !== false || strpos($object_key, 'credentials') !== false || strpos($object_key, 'secret') !== false) {
            $api_result = "=== [DOWNLOADED OBJECT CONTENT: " . htmlspecialchars($object_key) . "] ===\n\n" .
                          "{\n  \"cloud_provider\": \"AWS S3 / Aliyun OSS\",\n  \"admin_access_key_id\": \"AKIA_PROD_SUPER_ADMIN_9988\",\n" .
                          "  \"admin_secret_key\": \"FLAG{OSS_S3_BUCKET_UNAUTH_TRAVERSAL_MASTER}\",\n" .
                          "  \"database_url\": \"mysql://root:Pikachu_Cloud_2026@db.internal:3306/enterprise_db\"\n}";
        } else {
            $api_result = "=== [DOWNLOADED OBJECT CONTENT: " . htmlspecialchars($object_key) . "] ===\n\n<html><head><title>Pikachu Cloud Assets</title></head><body><h1>Welcome to Enterprise Static Storage</h1></body></html>";
        }
    } else if ($action === 'put') {
        $action_type = "put";
        if ($object_key === 'index.html' || strpos($object_key, '.html') !== false) {
            $api_result = "HTTP/1.1 200 OK\r\nx-amz-id-2: LgbTW4M/G90123...\r\nx-amz-request-id: 998877665544\r\nETag: \"6805f2cfc46c0f04559748bb039d69ae\"\r\n\r\n" .
                          "[SUCCESS] Object '" . htmlspecialchars($object_key) . "' successfully uploaded/overwritten!\n" .
                          "[WARNING] You have overwritten the official website homepage! Any visitor to the root bucket URL will now execute your HTML/JS payload:\n\n" . htmlspecialchars($put_content);
        } else {
            $api_result = "[SUCCESS] Object '" . htmlspecialchars($object_key) . "' uploaded to bucket.";
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="cloud_storage.php">对象存储 Cloud Storage</a></li>
                <li class="active">Bucket 越权读写与覆盖</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🪣 S3 / OSS 存储桶未授权访问与主页覆盖实战</h2>
                <p>目标应用使用云存储桶 <code>pikachu-enhanced-prod-assets.s3.amazonaws.com</code> 托管其前端页面与下载文件。由于 DevOps 工程师在 Terraform 中配置权限时疏忽，漏配了 <code>Principal: *</code> 下的 Read/Write 限制。</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-terminal"></i> S3 / OSS REST API 测试台</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="s3_action">选择拟发起的 REST API 操作：</label>
                                <select class="form-control" name="s3_action" id="s3_action" onchange="toggleInputs()">
                                    <option value="list" <?php if(($_POST['s3_action']??'')==='list') echo 'selected'; ?>>GET / (ListObjects - 遍历目录树)</option>
                                    <option value="get" <?php if(($_POST['s3_action']??'')==='get') echo 'selected'; ?>>GET /{ObjectKey} (GetObject - 读取/下载文件)</option>
                                    <option value="put" <?php if(($_POST['s3_action']??'')==='put') echo 'selected'; ?>>PUT /{ObjectKey} (PutObject - 覆盖写入文件)</option>
                                </select>
                            </div>
                            <div class="form-group" id="key_group" style="display:none;">
                                <label for="object_key">对象路径 (Object Key)：</label>
                                <input type="text" class="form-control" name="object_key" id="object_key" value="secret_backup/cloud_root_credentials.json"/>
                            </div>
                            <div class="form-group" id="put_group" style="display:none;">
                                <label for="put_content">写入内容 (Body)：</label>
                                <textarea class="form-control" name="put_content" id="put_content" rows="4">&lt;h1&gt;Hacked by Pikachu Security Team&lt;/h1&gt;&lt;script&gt;alert('FLAG{S3_PUT_OBJECT_OVERWRITE_SUCCESS}')&lt;/script&gt;</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane-o"></i> 发送 S3 API 请求</button>
                        </form>
                        <hr/>
                        <h5>💡 建议攻击链路：</h5>
                        <ol>
                            <li>先选择 <b>ListObjects</b> 遍历桶内全部隐藏对象。</li>
                            <li>选择 <b>GetObject</b> 读取发现的 <code>cloud_root_credentials.json</code> 获取机密 Flag。</li>
                            <li>选择 <b>PutObject</b> 覆盖 <code>index.html</code> 挂载网页钓鱼马！</li>
                        </ol>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-cloud-download"></i> 云存储服务器 API 响应报文</h4>
                        <div style="background: #111; color: #fff; padding: 15px; border-radius: 4px; font-family: monospace; min-height: 260px; max-height: 480px; overflow-y: auto;">
                            <?php if (!empty($api_result)) {
                                if ($action_type === 'list') {
                                    echo "<div style='color: #6a9955;'>// HTTP/1.1 200 OK (ListObjectsV2)</div><pre style='background:transparent; color:#e6db74; border:none; margin:0; padding:0;'>" . htmlspecialchars($api_result) . "</pre>";
                                } else if ($action_type === 'get') {
                                    echo "<div style='color: #a6e22e;'>// HTTP/1.1 200 OK (GetObject)</div><pre style='background:transparent; color:#f8f8f2; border:none; margin:0; padding:0;'>" . htmlspecialchars($api_result) . "</pre>";
                                } else if ($action_type === 'put') {
                                    echo "<div style='color: #ff6600;'>// HTTP/1.1 200 OK (PutObject Overwrite)</div><pre style='background:transparent; color:#fd971f; border:none; margin:0; padding:0;'>" . htmlspecialchars($api_result) . "</pre>";
                                }
                            } else { ?>
                                <span style="color: #6a9955;">// [Target: https://pikachu-enhanced-prod-assets.s3.amazonaws.com]</span><br/>
                                <span style="color: #6a9955;">// 等待发送请求。点击左侧按钮体验对未授权 Bucket 的侦察与夺权。</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function toggleInputs() {
    var val = document.getElementById('s3_action').value;
    if (val === 'list') {
        document.getElementById('key_group').style.display = 'none';
        document.getElementById('put_group').style.display = 'none';
    } else if (val === 'get') {
        document.getElementById('key_group').style.display = 'block';
        document.getElementById('put_group').style.display = 'none';
        document.getElementById('object_key').value = 'secret_backup/cloud_root_credentials.json';
    } else if (val === 'put') {
        document.getElementById('key_group').style.display = 'block';
        document.getElementById('put_group').style.display = 'block';
        document.getElementById('object_key').value = 'index.html';
    }
}
toggleInputs();
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


