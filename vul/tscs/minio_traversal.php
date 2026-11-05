<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 5: MinIO 路径点段穿越读 Web 根敏感文件
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理 /media/ 路径穿越模拟接口
if (isset($_GET['path'])) {
    $reqPath = $_GET['path'];
    
    // 检查是否包含点段穿越: ..%2F 或 ../
    if (strpos($reqPath, '..') !== false) {
        $filename = basename($reqPath);
        if ($filename === 'version.txt') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "2.5.11";
            exit;
        } elseif ($filename === 'robots.txt') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "User-agent: *\nDisallow: /prod-api/\nDisallow: /dispatch-api/\nDisallow: /media/\n";
            exit;
        } elseif ($filename === 'env.js' || $filename === 'config.js') {
            header('Content-Type: application/javascript; charset=utf-8');
            echo "window.g = { VUE_APP_BASE_API: '/prod-api', VUE_APP_DISPATCH_API: '/dispatch-api', MINIO_SERVER: 'http://172.16.10.50:9000' };";
            exit;
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            echo "[Web Root File Echo] Content of {$filename} from Frontend Dist Directory";
            exit;
        }
    } else {
        // 模拟普通访问 MinIO 桶返回 S3 XML 403 错误 (复现存储桶错误回显)
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>
<Error>
  <Code>AccessDenied</Code>
  <Message>Access Denied.</Message>
  <BucketName>technical-notice</BucketName>
  <Key>' . htmlspecialchars($reqPath) . '</Key>
  <HostId>minio-prod-node-01.corp-internal.internal</HostId>
</Error>';
        exit;
    }
}

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[356] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 5: MinIO 路径点段穿越</li>
            </ul>
        </div>

        <div class="page-content">
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#64748b; padding:4px 10px; font-size:12px;">Level 5</span>
                    <span class="badge" style="background:#475569; padding:4px 10px; font-size:12px;">CVSS 4.3 中危</span>
                    <span class="badge" style="background:#0284c7; padding:4px 10px; font-size:12px;">路径穿越与 S3 泄露</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    MinIO 路径点段穿越读 Web 根敏感文件
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>系统前端将 <code>/media/*</code> 反向代理给 MinIO 存储桶。测试人员测试发现，Nginx 在对 URL 解码后未规范化处理点段（<code>..%2F</code>），导致攻击者能够以点段穿越逃逸出 MinIO 存储前缀，直接读取到 Web 服务器前端 dist 根目录下的 <code>version.txt</code>、<code>robots.txt</code> 以及内嵌配置代码！同时，普通非法访问 MinIO 会原样吐出带 <code>BucketName</code> 的 S3 XML 报错信息。
                </p>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="tscs-card" style="background:#fff; border-radius:14px; padding:24px; border:1px solid #e2e8f0;">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px;">
                            <i class="ph-bold ph-terminal-window" style="color:#0284c7;"></i> MinIO 代理请求发送器
                        </h4>

                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600; color:#334155;">测试路径 (相对 /media/)：</label>
                            <input type="text" id="traversalPath" class="form-control" value="..%2Fversion.txt" style="border-radius:8px; font-family:monospace; padding:10px;">
                            <div style="margin-top:6px; display:flex; gap:8px;">
                                <span style="font-size:12px; color:#64748b;">测试用例：</span>
                                <a href="javascript:void(0)" onclick="setPath('..%2Fversion.txt')" style="font-size:12px; color:#0284c7;">..%2Fversion.txt</a> |
                                <a href="javascript:void(0)" onclick="setPath('..%2Frobots.txt')" style="font-size:12px; color:#0284c7;">..%2Frobots.txt</a> |
                                <a href="javascript:void(0)" onclick="setPath('..%2Fenv.js')" style="font-size:12px; color:#0284c7;">..%2Fenv.js</a> |
                                <a href="javascript:void(0)" onclick="setPath('random_secret_object')" style="font-size:12px; color:#dc2626;">S3报错探测 (Bucket Info)</a>
                            </div>
                        </div>

                        <button class="btn btn-primary" onclick="sendTraversal()" style="border-radius:8px; font-weight:700; padding:10px 20px;">
                            <i class="ph-bold ph-paper-plane-tilt"></i> 发送请求
                        </button>

                        <div style="margin-top:16px;">
                            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">响应数据：</div>
                            <pre id="traversalResp" style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-family:monospace; font-size:13px; min-height:100px;">// 点击上方按钮发送测试...</pre>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="tscs-card" style="background:#fff; border-radius:14px; padding:24px; border:1px solid #e2e8f0;">
                        <h4 style="margin:0 0 12px 0; font-weight:700; font-size:15px; color:#0f172a;">
                            <i class="ph-bold ph-shield-checkered" style="color:#10b981;"></i> Nginx 防御与加固规范
                        </h4>
                        <p style="font-size:13px; color:#475569; line-height:1.7;">
                            1. <strong>Nginx 路径规范化</strong>：在配置反代时，严禁使用未规范化的 <code>$uri</code>，应结合 <code>merge_slashes on;</code> 与路径前缀严密校验。<br>
                            2. <strong>拦截点段请求</strong>：对包含 <code>..</code>、<code>%2e%2e</code> 的请求直接在反代层阻断并返回 403/404。<br>
                            3. <strong>MinIO 错误脱敏</strong>：不要将 MinIO S3 XML 响应暴露给客户端，统一由统一错误页面拦截。
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function setPath(p) {
    document.getElementById('traversalPath').value = p;
    sendTraversal();
}

function sendTraversal() {
    const p = document.getElementById('traversalPath').value.trim();
    const box = document.getElementById('traversalResp');
    box.textContent = '// 正在发送请求...';

    fetch('minio_traversal.php?path=' + encodeURIComponent(p))
        .then(r => r.text())
        .then(text => {
            box.textContent = text;
        })
        .catch(err => {
            box.textContent = '请求异常: ' + err.message;
        });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
