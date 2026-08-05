<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[196] = 'active open';
$ACTIVE[198] = 'active';
$ACTIVE[196] = 'active open';
$ACTIVE[198] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$file_content = "";
$is_leaked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_file = trim($_POST['target_file'] ?? '');
    
    if ($target_file === '.env' || strpos($target_file, '.env') !== false) {
        $is_leaked = true;
        $file_content = "# Pikachu-Enhanced Enterprise Production Environment Config\n" .
                        "APP_NAME=PikachuEnhancedCloud\n" .
                        "APP_ENV=production\n" .
                        "APP_KEY=base64:FLAG{ENV_CONFIGURATION_SECRET_FILE_LEAK_MASTER}\n" .
                        "APP_DEBUG=true\n\n" .
                        "DB_CONNECTION=mysql\n" .
                        "DB_HOST=10.10.251.50\n" .
                        "DB_PORT=3306\n" .
                        "DB_DATABASE=pikachu_prod_db\n" .
                        "DB_USERNAME=root\n" .
                        "DB_PASSWORD=Pikachu_ENV_Master_2026\n\n" .
                        "REDIS_HOST=10.10.251.51\n" .
                        "REDIS_PASSWORD=Super_Redis_Secret_9988\n" .
                        "ALIYUN_OSS_ACCESS_ID=LTAI5t889900112233\n" .
                        "ALIYUN_OSS_ACCESS_KEY=aliyun_secret_FLAG_OSS_8877";
    } else if (strpos($target_file, '.bak') !== false || strpos($target_file, '.old') !== false) {
        $file_content = "<?php\n// Backup of database connection\n\$host = 'localhost';\n\$user = 'admin';\n\$pass = 'old_password_123';\n?>";
    } else if ($target_file === 'index.php') {
        $file_content = "<?php\n// Access Denied: Cannot read PHP source code directly via Web Server without leak vulnerability.\n?>";
    } else {
        $file_content = "HTTP/1.1 404 Not Found\n\n<h1>404 Not Found</h1>\nThe requested URL /" . htmlspecialchars($target_file) . " was not found on this server.";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="misconfig.php">配置泄露与调试监控</a></li>
                <li class="active">.env / Config 敏感凭证泄露</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📑 .env 与系统敏感配置文件未授权下载下载 (Env Configuration Leak)</h2>
                <p>使用现代后端框架（如 Laravel、Django、Spring Boot、Vue/React CI 环境变量）开发时，系统通常会将数据库账户密码、云服务 API Secret、JWT 密钥等核心环境变量保存在根目录下的 <code>.env</code> 或 <code>config.yml</code> 文件中。</p>
                <p>在运维部署运维 Nginx / Apache 站点时，如果没有通过正则配置禁止对以点开头的隐藏文件（dotfiles，如 <code>/.env</code>）的 Web 访问，攻击者只需在网站根域名后拼接 <code>/.env</code> 即可下载完整企业生产环境凭证！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-folder-open"></i> 敏感文件字典路径探测 (File Fuzzer)</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="target_file">选择或输入待探测的文件名：</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="target_file" id="target_file" placeholder="例如: .env" value="<?php echo isset($_POST['target_file']) ? htmlspecialchars($_POST['target_file']) : '.env'; ?>"/>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i> 发起 GET 请求</button>
                                    </span>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger btn-sm" onclick="setFile('.env')"><i class="fa fa-key"></i> 探测 .env 配置文件</button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="setFile('config.inc.php.bak')"><i class="fa fa-file-code-o"></i> 探测 .bak 备份文件</button>
                                <button type="button" class="btn btn-default btn-sm" onclick="setFile('index.php')">常规页面 (index.php)</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-file-text"></i> HTTP 响应报文内容展示 (HTTP Response Body)</h4>
                        <div class="panel <?php echo $is_leaked ? 'panel-danger' : 'panel-default'; ?>" style="margin-top: 10px;">
                            <div class="panel-heading"><b>服务器文件读取响应：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#1e1e1e; color:<?php echo $is_leaked ? '#50fa7b' : '#d4d4d4'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($file_content) ? htmlspecialchars($file_content) : "// 输入要下载的敏感文件名并发送请求。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function setFile(f) {
    document.getElementById('target_file').value = f;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


