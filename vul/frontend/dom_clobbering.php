<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[151] = 'active open';
$ACTIVE[153] = 'active';
$ACTIVE[151] = 'active open';
$ACTIVE[153] = 'active';
$PIKA_ROOT_DIR = "../../";

$html = '';
if(isset($_POST['content'])){
    $html = $_POST['content'];
}
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="frontend.php">前端前沿安全</a></li>
                <li class="active">DOM Clobbering</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <p>DOM Clobbering (DOM 破坏)</p>
                <p>如果前端代码依赖某些全局变量（例如 <code>window.configURL</code>），由于浏览器的遗留特性，页面中带有特定 id 的 DOM 元素会自动成为 window 对象的属性。</p>
                <p>在这个例子中，我们使用严格的 DOMPurify 过滤了你的输入，所以普通的 <code>&lt;script&gt;</code> 标签是无法执行的。</p>
                <p>但是，前端稍后会根据 <code>window.configURL</code> 去拉取一些安全配置并执行其中的脚本逻辑。</p>

                <hr>
                <form method="post">
                    <textarea class="form-control" name="content" rows="4" placeholder="输入富文本内容（比如 <a> 标签）..."><?php echo htmlspecialchars($html); ?></textarea>
                    <br>
                    <button class="btn btn-sm btn-primary" type="submit">发表留言</button>
                </form>

                <hr>
                <h4>留言内容区：</h4>
                <div id="user_content" style="border: 1px solid #ccc; padding: 10px; background: #fff;"></div>

                <script>
                    // 1. 严格净化用户的输入
                    let rawContent = <?php echo json_encode($html); ?>;
                    let cleanContent = DOMPurify.sanitize(rawContent);
                    document.getElementById('user_content').innerHTML = cleanContent;
                </script>

                <script>
                    // 2. 模拟前端组件延迟加载配置
                    setTimeout(function() {
                        // 漏洞点：如果原本没定义 configURL，它可能会被 DOM 元素覆盖！
                        // a 标签的 toString() 方法会返回它的 href 属性。
                        let url = window.configURL || 'data:text/plain,{"welcome": "欢迎光临!"}';
                        
                        console.log("Fetching config from: ", url.toString());
                        
                        fetch(url)
                            .then(res => res.text())
                            .then(data => {
                                // 模拟危险的执行点（比如配置里带有需要执行的回调函数或恶意的解析逻辑）
                                // 这里为了演示，直接判断有没有攻击字符串
                                if(data.includes("alert(1)")){
                                    alert(1);
                                } else {
                                    let d = document.createElement('div');
                                    d.textContent = "加载到的配置: " + data;
                                    document.getElementById('user_content').appendChild(d);
                                }
                            }).catch(e => {
                                console.log("加载配置失败", e);
                            });
                    }, 1000);
                </script>
                
                <p class="notice">提示：想办法通过注入一个特定的 HTML 标签，篡改 <code>window.configURL</code>，让 fetch 请求去读取带有 <code>alert(1)</code> 的数据（例如利用 data URI: <code>data:text/plain,alert(1)</code>）。</p>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


