<?php
/**
 * Pikachu-Enhanced v2.0 - SSRF 云服务器元数据凭证窃取演练 (AWS / 阿里云 IMDS)
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[109] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';

$html_result = '';
$requested_url = $_POST['url'] ?? '';

if (isset($_POST['submit']) && !empty($requested_url)) {
    $url = trim($requested_url);
    
    // 阿里云 ECS 元数据模拟 (100.100.100.200)
    if (str_contains($url, '100.100.100.200/latest/meta-data/ram/security-credentials/AlitestECS')) {
        $aliyun_sts = [
            "AccessKeyId" => "STS.LTAI5t" . strtoupper(substr(md5($url), 0, 16)),
            "AccessKeySecret" => "uWbJalrXUtnFEMI/K7MDENG/bPxRfiCY" . substr(sha1($url), 0, 10),
            "Expiration" => date('Y-m-d\TH:i:s\Z', time() + 3600),
            "SecurityToken" => "CAIS" . base64_encode("ALIYUN_STS_TOKEN_" . rand(1000, 9999)) . "==",
            "LastUpdated" => date('Y-m-d\TH:i:s\Z'),
            "Code" => "Success"
        ];
        $html_result = "<div class='alert alert-danger' style='border-radius:8px; font-weight:700; margin-bottom:12px;'>
            <i class='fa fa-exclamation-triangle'></i> 🚨 阿里云 ECS 实例 RAM 角色临时凭证（AK/SK）已被成功窃取！
        </div>
        <pre style='background:#090d16; color:#38bdf8; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>" . htmlspecialchars(json_encode($aliyun_sts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</pre>";
    } elseif (str_contains($url, '100.100.100.200/latest/meta-data/ram/security-credentials')) {
        $html_result = "<div class='alert alert-warning' style='border-radius:8px; font-weight:700; margin-bottom:12px;'>
            <i class='fa fa-list'></i> 发现绑定的 RAM 实例角色名称：
        </div>
        <pre style='background:#090d16; color:#f59e0b; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>AlitestECS</pre>";
    } elseif (str_contains($url, '100.100.100.200/latest/meta-data')) {
        $html_result = "<pre style='background:#090d16; color:#a5b4fc; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>image-id\ninstance-id\nmac\nnetwork/\nntp-conf/\nram/security-credentials/\nvpc-id\nvpc-cidr-block</pre>";
    }
    // AWS EC2 元数据模拟 (169.254.169.254)
    elseif (str_contains($url, '169.254.169.254/latest/meta-data/iam/security-credentials/admin-role')) {
        $aws_sts = [
            "Code" => "Success",
            "LastUpdated" => date('Y-m-d\TH:i:s\Z'),
            "Type" => "AWS-HMAC",
            "AccessKeyId" => "ASIAIOSFODNN7" . strtoupper(substr(md5($url), 0, 7)),
            "SecretAccessKey" => "wJalrXUtnFEMI/K7MDENG/bPxRfiCY" . substr(sha1($url), 0, 10),
            "Token" => "IQoJb3JpZ2luX2Vj" . base64_encode("AWS_SECURITY_TOKEN_" . rand(1000, 9999)),
            "Expiration" => date('Y-m-d\TH:i:s\Z', time() + 21600)
        ];
        $html_result = "<div class='alert alert-danger' style='border-radius:8px; font-weight:700; margin-bottom:12px;'>
            <i class='fa fa-exclamation-triangle'></i> 🚨 AWS EC2 IAM Role 临时凭证提取成功！攻击者可使用 AWS CLI 接管 S3、EC2 等云资源！
        </div>
        <pre style='background:#090d16; color:#34d399; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>" . htmlspecialchars(json_encode($aws_sts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</pre>";
    } elseif (str_contains($url, '169.254.169.254/latest/meta-data/iam/security-credentials')) {
        $html_result = "<pre style='background:#090d16; color:#34d399; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>admin-role</pre>";
    } elseif (str_contains($url, '169.254.169.254/latest/meta-data')) {
        $html_result = "<pre style='background:#090d16; color:#a5b4fc; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>ami-id\nhostname\niam/security-credentials/\ninstance-action\ninstance-id\ninstance-type\nlocal-ipv4\npublic-ipv4</pre>";
    } else {
        $html_result = "<div class='alert alert-info' style='border-radius:8px; margin-bottom:12px;'>
            <i class='fa fa-info-circle'></i> 正在尝试获取目标 URL: <code>" . htmlspecialchars($url) . "</code>
        </div>
        <pre style='background:#090d16; color:#94a3b8; border:1px solid #1e293b; border-radius:8px; padding:14px; font-family:monospace;'>HTTP/1.1 200 OK\nServer: Mock-Web-Proxy\nContent-Type: text/plain\n\n(靶场模拟环境：为防止真实网络滥用，已拦截外部公网请求，仅对云元数据地址 100.100.100.200 与 169.254.169.254 进行真实模拟回显)</pre>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF</a></li>
                <li class="active">云服务器元数据凭证窃取</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ☁️ 关卡 3: 云服务器元数据 (Cloud Metadata) 凭证窃取
                        <span class="cyber-badge-chip" style="border-color:#8b5cf6; color:#c084fc; background:rgba(139,92,246,0.15);">云安全 · IMDSv1 漏洞利用 · 250 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在 AWS、阿里云、腾讯云、GCP 等公有云环境中，部署在虚拟机（如 EC2 / ECS）上的应用可通过 link-local 私有地址（如 <code>169.254.169.254</code> 或 <code>100.100.100.200</code>）查询实例元数据。如果云服务器上的 Web 应用存在 SSRF 漏洞，攻击者可向这些元数据地址发起请求，<b>直接窃取绑定的 IAM/RAM 角色临时访问密钥（AccessKeyId / SecretAccessKey / SecurityToken）</b>，进而完全接管受害者的云上资产！
                    </p>
                </div>

                <div class="row">
                    <!-- Left: Control & Input -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-terminal" style="color:#8b5cf6;"></i> SSRF 远程数据拉取终端
                            </h4>

                            <form method="POST" action="ssrf_cloud.php">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">目标请求 URL：</label>
                                    <input type="text" id="url_input" name="url" class="form-control" value="<?php echo htmlspecialchars($requested_url); ?>" placeholder="输入要抓取的目标 URL" style="font-family:monospace;" required />
                                </div>

                                <div style="margin-bottom:18px;">
                                    <label style="font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:6px; display:block;">
                                        ⚡ 常用云元数据探测快捷 Payload：
                                    </label>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setUrl('http://100.100.100.200/latest/meta-data/ram/security-credentials/AlitestECS')">
                                            <i class="fa fa-cloud" style="color:#f59e0b;"></i> <b>阿里云 ECS：</b> 直接窃取 RAM 角色 STS 凭证 (AK/SK)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setUrl('http://100.100.100.200/latest/meta-data/ram/security-credentials')">
                                            <i class="fa fa-list" style="color:#06b6d4;"></i> <b>阿里云 ECS：</b> 枚举绑定的 RAM 角色名称列表
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setUrl('http://169.254.169.254/latest/meta-data/iam/security-credentials/admin-role')">
                                            <i class="fa fa-amazon" style="color:#ff9900;"></i> <b>AWS EC2：</b> 提取 IAM admin-role 凭证 (AK/SK/Token)
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default text-left" onclick="setUrl('http://169.254.169.254/latest/meta-data/')">
                                            <i class="fa fa-folder-open" style="color:#8b5cf6;"></i> <b>AWS EC2：</b> 遍历根元数据目录
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" value="submit" class="btn btn-purple btn-block" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #8b5cf6, #7c3aed); border:none; padding:10px;">
                                    <i class="fa fa-download"></i> 发起服务端抓取 (SSRF Fetch)
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Response Window -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-desktop" style="color:#06b6d4;"></i> 服务端响应回显结果 (Response Body)
                            </h4>

                            <?php if (!empty($html_result)): ?>
                                <?php echo $html_result; ?>
                            <?php else: ?>
                                <div style="background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:8px; padding:30px; text-align:center; color:var(--text-muted); font-size:13px;">
                                    <i class="fa fa-arrow-left" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                                    请在左侧选择或输入云元数据 URL 并点击抓取
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="ssrf_fgc.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：SSRF (FGC)</a>
                    <a href="ssrf_gopher_redis.php" class="btn btn-danger" style="border-radius:8px; font-weight:700;">下一关：Gopher 协议打 Redis (RCE) <i class="fa fa-arrow-right"></i></a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function setUrl(u) {
    document.getElementById('url_input').value = u;
    document.forms[0].submit();
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
