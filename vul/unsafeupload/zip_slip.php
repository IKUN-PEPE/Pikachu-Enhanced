<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[65] = 'active open';
$ACTIVE[208] = 'active';
$ACTIVE[65] = 'active open';
$ACTIVE[208] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$upload_res = "";
$is_slip = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_type = $_POST['package_type'] ?? 'normal';
    $custom_filename = trim($_POST['custom_filename'] ?? '');
    
    if ($package_type === 'slip' || strpos($custom_filename, '../') !== false || strpos($custom_filename, '..\\') !== false) {
        $is_slip = true;
        $target_path = !empty($custom_filename) ? $custom_filename : "../../../../var/www/html/shell.php";
        $upload_res = "=== [Simulating Unzip Archive Extraction: theme_addon.zip] ===\n" .
                      "[INFO] Extracting archive to destination directory: /var/www/html/uploads/addons/\n" .
                      "[EXTRACT] Entry: 'manifest.json' -> /var/www/html/uploads/addons/manifest.json (OK)\n" .
                      "[EXTRACT] Entry: 'style.css' -> /var/www/html/uploads/addons/style.css (OK)\n" .
                      "[EXTRACT] Entry: '" . htmlspecialchars($target_path) . "' -> /var/www/html/shell.php (CRITICAL: TRAVERSAL ESCAPED!)\n\n" .
                      "🚀 [ZIP SLIP EXPLOIT SUCCESSFUL] Directory traversal inside archive caused file to overwrite Web Root!\n" .
                      "HTTP GET http://pikachu.enhanced.local/shell.php?cmd=id\n" .
                      "uid=33(www-data) gid=33(www-data) groups=33(www-data)\n" .
                      "SYSTEM_FLAG=FLAG{ZIP_SLIP_DIRECTORY_TRAVERSAL_RCE_CHAMPION}";
    } else {
        $upload_res = "=== [Simulating Unzip Archive Extraction: default_theme.zip] ===\n" .
                      "[INFO] Extracting archive to destination directory: /var/www/html/uploads/addons/\n" .
                      "[EXTRACT] Entry: 'index.html' -> /var/www/html/uploads/addons/index.html (OK)\n" .
                      "[EXTRACT] Entry: 'config.json' -> /var/www/html/uploads/addons/config.json (OK)\n\n" .
                      "[SUCCESS] Theme package extracted safely within target directory.";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="clientcheck.php">不安全的文件上传</a></li>
                <li class="active">Zip Slip 目录穿越覆盖</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📦 压缩包解压目录穿越与任意文件覆盖 (Zip Slip RCE)</h2>
                <p>许多企业应用（如 WordPress 插件管理、OA 系统升级包上传、主题皮肤安装）允许用户上传 ZIP、TAR、GZ 或 RAR 压缩包，并在服务器后台自动调用解压缩函数（如 PHP 的 <code>ZipArchive::extractTo()</code>、Java 的 <code>ZipInputStream</code>、Python 的 <code>zipfile</code>）将其解压至指定目录。</p>
                <p>在压缩文件格式规范中，压缩包内部保存的每个文件路径条目（Entry Name）实际上是一个标准的字符串！攻击者可以使用自定义工具（如 <code>evilarc</code> 或 Python 脚本）构造一个畸形的压缩包，将内部文件的名字写成带有大量目录跳转符号的路径（例如 <code>../../../../var/www/html/shell.php</code>）。</p>
                <p>如果解压服务在写入文件前，<b>未检查 Entry 路径是否包含了 `../` 或超出目标基础目录</b>，解压引擎就会顺着相对路径直接跨越目标文件夹，<b>把恶意 WebShell 文件直接覆盖写入到系统根目录或 Web 主站目录，完成从文件上传到 RCE 的致命一击！</b></p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-cloud-upload"></i> 模拟插件压缩包解压测试台</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label>选择要上传并解压的插件压缩包：</label>
                                <div class="radio">
                                    <label><input type="radio" name="package_type" value="normal" <?php if(($POST['package_type']??'normal')==='normal') echo 'checked'; ?>> <span class="label label-success">安全包</span> <code>default_theme.zip</code> (标准合法皮肤包)</label>
                                </div>
                                <div class="radio">
                                    <label><input type="radio" name="package_type" value="slip" <?php if(($POST['package_type']??'')==='slip') echo 'checked'; ?>> <span class="label label-danger">恶意包</span> <code>evilarc_shell.zip</code> (内含 Zip Slip 穿越后门)</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="custom_filename">自定义压缩包内后门文件路径 (Optional Entry Name)：</label>
                                <input type="text" class="form-control" name="custom_filename" id="custom_filename" placeholder="例如: ../../../../var/www/html/shell.php" value="<?php echo isset($_POST['custom_filename']) ? htmlspecialchars($_POST['custom_filename']) : '../../../../var/www/html/shell.php'; ?>"/>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-cogs"></i> 提交压缩包给服务器执行在线解压</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-terminal"></i> 后端 ZipArchive 解压引擎操作日志</h4>
                        <div class="panel <?php echo $is_slip ? 'panel-danger' : 'panel-default'; ?>" style="margin-top:0;">
                            <div class="panel-heading"><b>服务器系统解压与 I/O 监控：</b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:<?php echo $is_slip ? '#ff5555' : '#50fa7b'; ?>; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($upload_res) ? htmlspecialchars($upload_res) : "// 点击左侧提交按钮，模拟在服务器解压带有 ../ 路径的畸形 Zip 文件。"; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


