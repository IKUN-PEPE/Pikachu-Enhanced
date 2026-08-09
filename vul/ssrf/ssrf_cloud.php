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
    
    // Cloud Metadata SSRF Simulation - Aliyun and AWS
    
    // Aliyun Case 3: Fetching the AK/SK for the AlitestECS role
    if(strpos($url, '100.100.100.200/latest/meta-data/ram/security-credentials/AlitestECS') !== false){
        $mock_aliyun_keys = array(
            "AccessKeyId" => "STS.NUQEXAMPLEKEYIDFORALIYUN",
            "AccessKeySecret" => "uWbJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPSECRET",
            "Expiration" => "2026-08-09T12:00:00Z",
            "SecurityToken" => "CAIS...<mocked_aliyun_security_token>...w==",
            "LastUpdated" => "2026-08-08T12:00:00Z",
            "Code" => "Success"
        );
        $html = "<pre>".htmlspecialchars(json_encode($mock_aliyun_keys, JSON_PRETTY_PRINT))."</pre>";
        $html .= "<div class='alert alert-danger'>🚨 危险！阿里云 ECS 实例临时凭证（AK/SK）已被窃取！攻击者可以使用这些凭证完全接管你的云资源！</div>";
    
    // Aliyun Case 2: Listing the RAM roles
    } elseif(strpos($url, '100.100.100.200/latest/meta-data/ram/security-credentials') !== false) {
        $html = "<pre>AlitestECS</pre>";
    
    // Aliyun Case 1: Listing the root meta-data endpoints
    } elseif(strpos($url, '100.100.100.200/latest/meta-data') !== false) {
        $html = "<pre>ram/\nnetwork/\nimage-id\ninstance-id\nmac\nntp-conf/\nvpc-id</pre>";
        
    // AWS Case: Fetching the AK/SK for the admin role
    } elseif(strpos($url, '169.254.169.254/latest/meta-data/iam/security-credentials/admin') !== false){
        $mock_aws_keys = array(
            "Code" => "Success",
            "LastUpdated" => "2026-08-08T12:00:00Z",
            "Type" => "AWS-HMAC",
            "AccessKeyId" => "ASIAIOSFODNN7EXAMPLE",
            "SecretAccessKey" => "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
            "Token" => "IQoJb3JpZ2luX2VjEJv...<mocked_token>...",
            "Expiration" => "2026-08-09T18:00:00Z"
        );
        $html = "<pre>".htmlspecialchars(json_encode($mock_aws_keys, JSON_PRETTY_PRINT))."</pre>";
        $html .= "<div class='alert alert-danger'>🚨 危险：AWS 云服务器实例凭证已被窃取！</div>";
    
    // Fallback/Generic Mock fetch
    } else {
        $html = "<pre>试图获取: " . htmlspecialchars($url) . "\n(为防止真实网络滥用，靶场仅模拟云元数据的读取，其余外部链接不真正发起请求)</pre>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ssrf.php">SSRF</a></li>
                <li class="active">SSRF (Cloud Metadata 云实例元数据泄露)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p><strong>云原生时代的 SSRF：Metadata 凭证窃取</strong></p>
                <p>什么是元数据？云实例元数据是云厂商提供给每台虚拟机的内部信息接口。它运行在实例内部，通过固定内网地址（如 <code>169.254.169.254</code> 或 <code>100.100.100.200</code>）访问，不需要任何认证。接口自动返回该实例的配置信息、标识、以及最重要的临时访问密钥（AK/SK）。</p>
                <p>SSRF 攻击元数据的致命点：如果机器绑定了 RAM/IAM 角色，元数据接口会直接返回实例绑定的临时凭证。攻击者通过 SSRF 读取元数据 = 拿到实例临时凭证 = 获得云资源控制权 = 账号接管。</p>

                <hr>
                <p><strong>【靶场实战：以阿里云为例】</strong></p>
                <p>这是一个存在 SSRF 漏洞的接口。尝试通过如下步骤，窃取阿里云主机的临时 AK/SK：</p>
                <ol>
                    <li>发现 SSRF：首先探测阿里云元数据根目录：<br><code>http://100.100.100.200/latest/meta-data/</code></li>
                    <li>列出绑定的 RAM 角色名称：<br><code>http://100.100.100.200/latest/meta-data/ram/security-credentials/</code></li>
                    <li>拼接角色名称，窃取临时 AK/SK：<br><code>http://100.100.100.200/latest/meta-data/ram/security-credentials/这里填入上一步获取到的角色名</code></li>
                </ol>

                <form method="post">
                    <input class="form-control" type="text" name="url" placeholder="请输入你要获取的 URL" style="width:500px; display:inline-block;" value="http://100.100.100.200/latest/meta-data/">
                    <button class="btn btn-sm btn-primary" type="submit" name="submit">获取数据</button>
                </form>
                <br>
                <?php echo $html; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


