<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[105] = 'active open';
$ACTIVE[109] = 'active';
$ACTIVE[105] = 'active open';
$ACTIVE[109] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$html = '';
if(isset($_POST['url']) && $_POST['url'] !== ''){
    $url = trim($_POST['url']);
    
    // Cloud Metadata SSRF Simulation
    // In a real environment, the PHP server would execute `curl $url` or `file_get_contents($url)`.
    // If the attacker specifies http://169.254.169.254/latest/meta-data/iam/security-credentials/admin,
    // the cloud provider's metadata service responds with the temporary AWS credentials.
    
    if(strpos($url, '169.254.169.254/latest/meta-data/iam/security-credentials') !== false){
        $mock_aws_keys = array(
            "Code" => "Success",
            "LastUpdated" => "2026-06-20T12:00:00Z",
            "Type" => "AWS-HMAC",
            "AccessKeyId" => "ASIAIOSFODNN7EXAMPLE",
            "SecretAccessKey" => "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
            "Token" => "IQoJb3JpZ2luX2VjEJv...<mocked_token>...",
            "Expiration" => "2026-06-20T18:00:00Z"
        );
        $html = "<pre>".htmlspecialchars(json_encode($mock_aws_keys, JSON_PRETTY_PRINT))."</pre>";
        $html .= "<div class='alert alert-danger'>🚨 警告：云服务器实例凭证已被窃取！攻击者可使用这些凭证接管你的 AWS 账号！</div>";
    } elseif(strpos($url, '169.254.169.254/latest/meta-data/') !== false) {
        $html = "<pre>iam/\nmac/\nnetwork/\npublic-ipv4</pre>";
    } else {
        // Mock generic fetch
        $html = "<pre>试图获取: " . htmlspecialchars($url) . "\n(为防止真实网络滥用，靶场仅模拟云元数据的读取，其余外部链接不真正发起请求)</pre>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF</a></li>
                <li class="active">SSRF (Cloud Metadata)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p>云原生时代的 SSRF：Metadata 凭证窃取</p>
                <p>在云环境（AWS, 阿里云, 腾讯云等）中，虚拟机可以通过访问一个特殊 IP <code>169.254.169.254</code> 来获取自身的元数据（Metadata），其中甚至包含了绑定在实例上的高权限临时密钥。</p>
                <p>如果 Web 存在 SSRF 漏洞，攻击者可以要求服务器去请求这个特殊 IP，从而把云厂商分发给机器的最高权限密钥“偷”出来。</p>

                <hr>
                <p>这是一个存在 SSRF 漏洞的图片提取接口。正常情况下你传入一个图片 URL，服务器去下载。</p>
                <p>请尝试传入 AWS 的经典 metadata 地址：<br>
                <code>http://169.254.169.254/latest/meta-data/iam/security-credentials/admin</code>
                </p>

                <form method="post">
                    <input class="form-control" type="text" name="url" placeholder="请输入你要获取的 URL" style="width:500px; display:inline-block;" value="http://169.254.169.254/latest/meta-data/">
                    <button class="btn btn-sm btn-primary" type="submit" name="submit">获取数据</button>
                </form>
                <br>
                <?php echo $html; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


