<?php
/**
 * Pikachu-Enhanced v2.0 - 数据库初始化与环境诊断中枢 (Database Setup & System Diagnostics)
 */
include_once 'inc/config.inc.php';
include_once 'header.php';

$dbhost = defined('DBHOST') ? DBHOST : '127.0.0.1';
$dbuser = defined('DBUSER') ? DBUSER : 'root';
$dbpw   = defined('DBPW') ? DBPW : 'root';
$dbname = defined('DBNAME') ? DBNAME : 'pikachu';
$dbport = defined('DBPORT') ? DBPORT : 3306;

// Pre-flight checks
$php_version = phpversion();
$php_ok = version_compare($php_version, '7.0.0', '>=');
$ext_mysqli = extension_loaded('mysqli');
$ext_openssl = extension_loaded('openssl');
$ext_curl = extension_loaded('curl');
$ext_json = extension_loaded('json');
$ext_mbstring = extension_loaded('mbstring');
$ext_gd = extension_loaded('gd');
$ext_zip = extension_loaded('zip');
$ext_sockets = extension_loaded('sockets');
$ext_bcmath = extension_loaded('bcmath');

$cmd_ping = !empty(shell_exec('which ping 2>/dev/null'));
$cmd_curl = !empty(shell_exec('which curl 2>/dev/null'));
$cmd_nc   = !empty(shell_exec('which nc 2>/dev/null'));

$uploads_writable = is_writable(__DIR__ . '/uploads') || @is_writable('/tmp');

// Test MySQL connection status
$mysql_connected = false;
$mysql_err = '';
$test_link = @mysqli_connect($dbhost, $dbuser, $dbpw, null, $dbport);
if ($test_link) {
    $mysql_connected = true;
    $server_info = mysqli_get_server_info($test_link);
    mysqli_close($test_link);
} else {
    $mysql_err = mysqli_connect_error();
}

$install_logs = [];
$install_success = false;
$install_error = false;

if (isset($_POST['submit_install'])) {
    $link = @mysqli_connect($dbhost, $dbuser, $dbpw, null, $dbport);
    if (!$link) {
        $install_error = true;
        $install_logs[] = ['type' => 'error', 'msg' => "[-] 数据库连接握手失败: " . mysqli_connect_error() . "。请检查 inc/config.inc.php 配置参数。"];
    } else {
        $install_logs[] = ['type' => 'info', 'msg' => "[+] 成功建立与 MySQL 数据库服务端的 TCP 连接 ({$dbhost}:{$dbport})。"];
        
        // 1. Drop old DB if exists
        $drop_db = "DROP DATABASE IF EXISTS `{$dbname}`";
        if (@mysqli_query($link, $drop_db)) {
            $install_logs[] = ['type' => 'warn', 'msg' => "[*] 检测并清理历史数据库 `{$dbname}` (DROP DATABASE IF EXISTS)... 完成。"];
        }
        
        // 2. Create DB with utf8mb4
        $create_db = "CREATE DATABASE `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        if (!@mysqli_query($link, $create_db)) {
            $install_error = true;
            $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建数据库 `{$dbname}` 失败: " . mysqli_error($link)];
        } else {
            $install_logs[] = ['type' => 'success', 'msg' => "[+] 成功创建靶场专属数据库: `{$dbname}` (Charset: utf8mb4)。"];
            
            // Select DB
            mysqli_select_db($link, $dbname);
            mysqli_set_charset($link, "utf8mb4");

            // 3. Create users table
            $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `username` varchar(30) NOT NULL,
                `password` varchar(66) NOT NULL,
                `level` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";
            
            if (@mysqli_query($link, $sql_users)) {
                $insert_users = "INSERT INTO `users` (`id`,`username`,`password`,`level`) VALUES 
                    (1, 'admin', md5('123456'), 1),
                    (2, 'pikachu', md5('000000'), 2),
                    (3, 'test', md5('abc123'), 3)";
                @mysqli_query($link, $insert_users);
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [1/6] 成功创建 `users` 用户鉴权表并填充 3 条初始特权账户 (admin, pikachu, test)。"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `users` 表失败: " . mysqli_error($link)];
            }

            // 4. Create member table
            $sql_member = "CREATE TABLE IF NOT EXISTS `member` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `username` varchar(66) NOT NULL,
                `pw` varchar(128) NOT NULL,
                `sex` char(10) NOT NULL,
                `phonenum` varchar(255) NOT NULL,
                `address` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";

            if (@mysqli_query($link, $sql_member)) {
                $insert_member = "INSERT INTO `member` (`id`, `username`, `pw`, `sex`, `phonenum`, `address`, `email`) VALUES
                    (1, 'vince', md5('123456'), 'boy', '18626545453', 'chain', 'vince@pikachu.com'),
                    (2, 'allen', md5('123456'), 'boy', '13676767767', 'nba 76', 'allen@pikachu.com'),
                    (3, 'kobe', md5('123456'), 'boy', '15988767673', 'nba lakes', 'kobe@pikachu.com'),
                    (4, 'grady', md5('123456'), 'boy', '13676765545', 'nba hs', 'grady@pikachu.com'),
                    (5, 'kevin', md5('123456'), 'boy', '13677676754', 'Oklahoma City Thunder', 'kevin@pikachu.com'),
                    (6, 'lucy', md5('123456'), 'girl', '12345678922', 'usa', 'lucy@pikachu.com'),
                    (7, 'lili', md5('123456'), 'girl', '18656565545', 'usa', 'lili@pikachu.com')";
                @mysqli_query($link, $insert_member);
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [2/6] 成功创建 `member` 核心会员数据库表并导入 7 条测试会员档案。"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `member` 表失败: " . mysqli_error($link)];
            }

            // 5. Create message table (Stored XSS)
            $sql_message = "CREATE TABLE IF NOT EXISTS `message` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `content` varchar(255) NOT NULL,
                `time` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='stored_xss_board' AUTO_INCREMENT=1";

            if (@mysqli_query($link, $sql_message)) {
                $insert_msg = "INSERT INTO `message` (`content`, `time`) VALUES 
                    ('欢迎来到 Pikachu-Enhanced 靶场交流留言板！', NOW()),
                    ('安全测试请严格遵守靶场测试规程。', NOW())";
                @mysqli_query($link, $insert_msg);
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [3/6] 成功创建 `message` 存储型 XSS 交互留言板数据表。"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `message` 表失败: " . mysqli_error($link)];
            }

            // 6. Create xssblind table
            $sql_xssblind = "CREATE TABLE IF NOT EXISTS `xssblind` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `time` datetime NOT NULL,
                `content` text NOT NULL,
                `name` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";

            if (@mysqli_query($link, $sql_xssblind)) {
                $insert_blind = "INSERT INTO `xssblind` (`time`, `content`, `name`) VALUES (NOW(), '系统管理员将定期在此审核用户反馈。', '系统管理员')";
                @mysqli_query($link, $insert_blind);
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [4/6] 成功创建 `xssblind` XSS 盲打意见征集数据表。"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `xssblind` 表失败: " . mysqli_error($link)];
            }

            // 7. Create httpinfo table (Header Injection)
            $sql_httpinfo = "CREATE TABLE IF NOT EXISTS `httpinfo` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userid` int(10) NOT NULL,
                `ipaddress` varchar(255) NOT NULL,
                `useragent` varchar(255) NOT NULL,
                `httpaccept` varchar(255) NOT NULL,
                `remoteport` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";

            if (@mysqli_query($link, $sql_httpinfo)) {
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [5/6] 成功创建 `httpinfo` HTTP Header 注入日志追踪表。"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `httpinfo` 表失败: " . mysqli_error($link)];
            }

            // 8. Create flag_vault table (CTF Flag Center)
            $sql_flag_vault = "CREATE TABLE IF NOT EXISTS `flag_vault` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `flag_name` varchar(66) NOT NULL,
                `flag_val` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";

            if (@mysqli_query($link, $sql_flag_vault)) {
                $insert_flag_vault = "INSERT INTO `flag_vault` (`id`, `flag_name`, `flag_val`) VALUES
                    (1, 'sqli_flag', 'flag{Pikachu_SQLi_Database_Vault_Extracted}'),
                    (2, 'xxe_flag', 'flag{Pikachu_XXE_Local_Entity_Disclosure}'),
                    (3, 'deser_flag', 'flag{Pikachu_Deserialization_POP_Chain_Executed}'),
                    (4, 'idor_flag', 'flag{Pikachu_IDOR_Privilege_Escalation_Master}'),
                    (5, 'jwt_flag', 'flag{Pikachu_JWT_Modern_Auth_Token_Bypassed}')";
                @mysqli_query($link, $insert_flag_vault);
                $install_logs[] = ['type' => 'success', 'msg' => "[+] [6/6] 成功创建 `flag_vault` 全局 CTF 靶标机密金库并装载 5 条专属 Flag！"];
            } else {
                $install_logs[] = ['type' => 'error', 'msg' => "[-] 创建 `flag_vault` 表失败: " . mysqli_error($link)];
            }

            if (!$install_error) {
                $install_success = true;
                $install_logs[] = ['type' => 'success', 'msg' => "[+] 🎉 全部 6 个核心数据表及靶标数据集初始化完成！数据库状态处于就绪 (READY)。"];
            }
        }
        mysqli_close($link);
    }
}
?>

<style>
.install-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.install-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.term-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.7;
    color: #38bdf8;
    max-height: 320px;
    overflow-y: auto;
}
.badge-check {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}
.badge-check-ok {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.badge-check-fail {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.table-matrix-item {
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="index.php">Pikachu-Enhanced</a></li>
                <li class="active">系统管理 & 数据库初始化</li>
            </ul>
        </div>

        <div class="page-content" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Hero Banner -->
            <div class="install-hero">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div style="font-size:22px; font-weight:800; margin:0 0 8px 0; display:flex; align-items:center; gap:12px;">
                            <i class="fa fa-database" style="color:#818cf8;"></i> Pikachu-Enhanced 数据库初始化与环境诊断中枢
                            <span class="label label-primary" style="border-radius:12px; font-size:11px; padding:3px 10px;">v2.0 NEXT-GEN</span>
                        </div>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                            一键完成靶场底层 MySQL 数据库重构、六大核心靶标数据表部署及 CTF 动态 Flag 载入，保障各漏洞模块开箱即用。
                        </p>
                    </div>
                    <?php if ($install_success) { ?>
                        <a href="index.php" class="btn btn-success btn-lg" style="border-radius:10px; font-weight:700; box-shadow:0 0 20px rgba(16,185,129,0.4);">
                            <i class="fa fa-rocket"></i> 立即进入靶场首页
                        </a>
                    <?php } ?>
                </div>
            </div>

            <?php if ($install_success) { ?>
                <div class="alert alert-success" style="border-radius:12px; font-size:14px; padding:16px 20px; margin-bottom:24px; box-shadow:0 4px 15px rgba(16,185,129,0.15);">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <i class="fa fa-check-circle" style="font-size:24px;"></i>
                        <div>
                            <b>🎉 数据库初始化与数据填充全部成功！</b><br>
                            靶场已完全就绪，包含 6 个核心数据表与全量测试账号，点击上方按钮或 <a href="index.php" style="font-weight:bold; color:#065f46; text-decoration:underline;">点击此处进入首页</a> 开启实战演练！
                        </div>
                    </div>
                </div>
            <?php } elseif ($install_error) { ?>
                <div class="alert alert-danger" style="border-radius:12px; font-size:14px; padding:16px 20px; margin-bottom:24px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <i class="fa fa-times-circle" style="font-size:24px;"></i>
                        <div>
                            <b>❌ 数据库初始化失败</b><br>
                            请根据下方终端日志中的错误提示，检查 <code>inc/config.inc.php</code> 中的数据库账号密码与连接权限。
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
                <!-- Left Column: Environment Diagnostics & DB Config -->
                <div class="col-md-5">
                    
                    <!-- Pre-flight Diagnostics Card -->
                    <div class="install-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-heartbeat" style="color:var(--primary);"></i> 运行环境体检诊断
                        </h4>

                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">PHP 运行版本 (>= 7.0)：</span>
                                <span class="badge-check <?php echo $php_ok ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $php_ok ? 'fa-check' : 'fa-times'; ?>"></i> PHP <?php echo $php_version; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">MySQL 联通性 (<?php echo htmlspecialchars($dbhost . ':' . $dbport); ?>)：</span>
                                <span class="badge-check <?php echo $mysql_connected ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $mysql_connected ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $mysql_connected ? '连接正常' : '连接失败'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">MySQLi 扩展：</span>
                                <span class="badge-check <?php echo $ext_mysqli ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_mysqli ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_mysqli ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">OpenSSL 扩展 (JWT/Crypto 必需)：</span>
                                <span class="badge-check <?php echo $ext_openssl ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_openssl ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_openssl ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">cURL 扩展 (SSRF/API 必需)：</span>
                                <span class="badge-check <?php echo $ext_curl ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_curl ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_curl ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">GD 图像库 (验证码/图片马测试)：</span>
                                <span class="badge-check <?php echo $ext_gd ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_gd ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_gd ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">ZipArchive 扩展 (ZipSlip/解压安全)：</span>
                                <span class="badge-check <?php echo $ext_zip ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_zip ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_zip ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">Sockets 扩展 (反弹 Shell / Socket 演练)：</span>
                                <span class="badge-check <?php echo $ext_sockets ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $ext_sockets ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $ext_sockets ? '已加载' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">系统网络工具 (ping / ifconfig / nc)：</span>
                                <span class="badge-check <?php echo $cmd_ping ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $cmd_ping ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $cmd_ping ? '环境完备' : '缺失'; ?>
                                </span>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-secondary);">文件上传目录可写 (uploads/)：</span>
                                <span class="badge-check <?php echo $uploads_writable ? 'badge-check-ok' : 'badge-check-fail'; ?>">
                                    <i class="fa <?php echo $uploads_writable ? 'fa-check' : 'fa-times'; ?>"></i> <?php echo $uploads_writable ? '可写' : '只读'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Config Parameters Card -->
                    <div class="install-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sliders" style="color:var(--primary);"></i> 数据库连接配置 (inc/config.inc.php)
                        </h4>

                        <table class="table table-bordered" style="font-size:12.5px; margin:0;">
                            <tbody>
                                <tr>
                                    <td style="width:40%; font-weight:600; background:var(--bg-secondary);">主机地址 (DBHOST)</td>
                                    <td style="font-family:monospace;"><?php echo htmlspecialchars($dbhost); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">端口号 (DBPORT)</td>
                                    <td style="font-family:monospace;"><?php echo htmlspecialchars($dbport); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">登录用户 (DBUSER)</td>
                                    <td style="font-family:monospace;"><?php echo htmlspecialchars($dbuser); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">密码 (DBPW)</td>
                                    <td style="font-family:monospace;">●●●●●● (已安全掩码)</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600; background:var(--bg-secondary);">数据库名 (DBNAME)</td>
                                    <td style="font-family:monospace; color:#3b82f6; font-weight:bold;"><?php echo htmlspecialchars($dbname); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column: Table Matrix & Real-time Install Console -->
                <div class="col-md-7">
                    
                    <!-- Pre-installed Table Topology -->
                    <div class="install-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-table" style="color:var(--primary);"></i> 预装数据表拓扑矩阵 (Table Schema Registry)
                        </h4>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`users`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">用户身份与鉴权数据中心 (包含 admin, pikachu, test 账户)</span>
                            </div>
                            <span class="label label-info" style="border-radius:4px; font-size:11px;">鉴权核心</span>
                        </div>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`member`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">会员敏感信息库 (SQL 注入、越权、信息泄露靶标)</span>
                            </div>
                            <span class="label label-warning" style="border-radius:4px; font-size:11px;">注入靶标</span>
                        </div>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`message`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">存储型 XSS 实时交互留言板</span>
                            </div>
                            <span class="label label-danger" style="border-radius:4px; font-size:11px;">XSS 演练</span>
                        </div>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`xssblind`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">盲打 XSS 管理员后台意见征集表</span>
                            </div>
                            <span class="label label-danger" style="border-radius:4px; font-size:11px;">XSS 盲打</span>
                        </div>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`httpinfo`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">HTTP Header 报头注入与日志追踪表</span>
                            </div>
                            <span class="label label-default" style="border-radius:4px; font-size:11px;">报头注入</span>
                        </div>

                        <div class="table-matrix-item">
                            <div>
                                <b style="color:var(--text-primary); font-family:monospace; font-size:13px;">`flag_vault`</b>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">全局 CTF 靶标机密金库 (存储全阶段 Flag 凭条)</span>
                            </div>
                            <span class="label label-success" style="border-radius:4px; font-size:11px;">CTF 金库</span>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Install Action Form -->
                        <form method="POST" onsubmit="return confirm('警告：执行安装/初始化将重建数据库并覆盖现有数据。是否继续？');">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                                <div style="font-size:12.5px; color:var(--text-muted);">
                                    <i class="fa fa-info-circle"></i> 点击下方按钮即可一键重建数据表结构并装填默认数据集。
                                </div>
                                <button type="submit" name="submit_install" class="btn btn-danger btn-lg" style="border-radius:8px; font-weight:700; padding:10px 24px;">
                                    <i class="fa fa-bolt"></i> ⚡ 立即安装 / 重置数据库
                                </button>
                            </div>
                        </form>

                    </div>

                    <!-- Real-time Install Console Log -->
                    <?php if (!empty($install_logs)) { ?>
                        <div class="install-card">
                            <h4 style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                                <i class="fa fa-terminal" style="color:var(--primary);"></i> 初始化执行实时日志
                            </h4>
                            <div class="term-box">
                                <?php 
                                foreach ($install_logs as $log) {
                                    $color = '#38bdf8';
                                    if ($log['type'] === 'success') $color = '#10b981';
                                    elseif ($log['type'] === 'warn') $color = '#f59e0b';
                                    elseif ($log['type'] === 'error') $color = '#ef4444';
                                    echo "<div style='color:{$color};'>" . htmlspecialchars($log['msg']) . "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>