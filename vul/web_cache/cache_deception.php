<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[174] = 'active open';
$ACTIVE[176] = 'active';
$ACTIVE[174] = 'active open';
$ACTIVE[176] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 模拟受害者登录状态和其私密信息
// 在真实场景下，这是通过 SESSION 或 Cookie 读取的当前用户数据
$logged_in_user = "VictimUser_" . rand(1000, 9999);
$private_api_key = md5($logged_in_user . "secret");

// 模拟极其简易的 CDN 层缓存逻辑！
// 如果 URL 中包含 .css / .js / .png 等静态后缀，CDN 会尝试缓存这个页面
$request_uri = $_SERVER['REQUEST_URI'];
$is_cached_by_cdn = false;

// 模拟 CDN 的缓存键匹配 (弱后缀匹配)
if (preg_match('/\.(css|js|png|jpg)$/i', $request_uri)) {
    // 模拟 CDN 命中了缓存逻辑，设置缓存 Header
    header("X-Cache-Status: HIT (Simulated CDN Cache)");
    header("Cache-Control: public, max-age=3600"); // 允许全局缓存1小时
    $is_cached_by_cdn = true;
} else {
    header("X-Cache-Status: MISS");
    header("Cache-Control: private, no-store");
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="web_cache.php">现代 Web 缓存安全</a></li>
                <li class="active">Web 缓存欺骗 (WCD)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🪤 Web 缓存欺骗 (Web Cache Deception)</h2>
                <p>这是一个包含你（受害者）高度敏感信息的页面。通常情况下，这个页面是动态生成的，包含不可预测的数据。</p>
                <p>但是，由于 PHP 的路由特性（默认开启了 <code>cgi.fix_pathinfo</code>），你在 URL 后面追加不存在的文件名，依然会渲染此页面。例如：<code>cache_deception.php/whatever.css</code></p>
                <p><strong>攻击原理：</strong></p>
                <p>尝试在浏览器地址栏的当前 URL 后面加上 <code>/style.css</code>，回车访问。</p>
                <p>虽然内容一样，但请看下方模拟的 CDN 状态！CDN 误以为这是一个静态的 CSS 文件，从而将你的包含敏感信息的整个 HTML 页面**公开缓存**到了公共节点上。此时攻击者只要访问同样的 URL，就能免登录偷看你的敏感信息！</p>
                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">我的私密信息 (My Profile)</div>
                            <div class="panel-body">
                                <ul class="list-unstyled">
                                    <li><i class="ace-icon fa fa-user"></i> 登录用户：<strong><?php echo $logged_in_user; ?></strong></li>
                                    <li><i class="ace-icon fa fa-key"></i> 私密 API Key：<strong class="text-danger"><?php echo $private_api_key; ?></strong></li>
                                    <li><i class="ace-icon fa fa-credit-card"></i> 信用卡尾号：<strong>**** **** **** 8888</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="panel <?php echo $is_cached_by_cdn ? 'panel-danger' : 'panel-success'; ?>">
                            <div class="panel-heading">模拟的前端 CDN 缓存状态</div>
                            <div class="panel-body">
                                <?php if ($is_cached_by_cdn): ?>
                                    <h3 class="text-danger"><i class="fa fa-exclamation-triangle"></i> 危险！此页面已被 CDN 缓存</h3>
                                    <p>CDN 判定：<strong>后缀为静态文件，已缓存到公共池！</strong></p>
                                    <p>这意味着任何未登录的人访问你刚敲入的那个 URL，都将直接从 CDN 看到左侧属于你的私密数据！</p>
                                    <p>响应头：<code>X-Cache-Status: HIT</code></p>
                                <?php else: ?>
                                    <h3 class="text-success"><i class="fa fa-shield"></i> 当前页面安全</h3>
                                    <p>CDN 判定：<strong>动态请求，未缓存。</strong></p>
                                    <p>您可以尝试在 URL 栏后面加上 <code>/test.css</code> 来触发欺骗漏洞。</p>
                                    <p>响应头：<code>X-Cache-Status: MISS</code></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


