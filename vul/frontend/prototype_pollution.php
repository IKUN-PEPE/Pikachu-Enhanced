<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[151] = 'active open';
$ACTIVE[156] = 'active';
$ACTIVE[151] = 'active open';
$ACTIVE[156] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="frontend.php">前端前沿安全</a></li>
                <li class="active">Prototype Pollution</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🌐 Prototype Pollution (原型链污染)</h2>
                <p>在 JavaScript 中，所有的对象都继承自 <code>Object.prototype</code>。如果在深度合并（Deep Merge）或克隆对象时没有过滤特定的键（如 <code>__proto__</code>），攻击者可以将属性注入到全局的原型链上。</p>
                <p>一旦原型链被污染，所有未定义该属性的对象都会“凭空”拥有这个属性，从而改变程序的执行流程！</p>
                <hr>

                <div class="alert alert-warning">
                    <p><strong>实战挑战：</strong>下面是一个前端生成个性化卡片的工具。输入你的偏好 JSON，系统会和默认配置合并。</p>
                    <p>系统默认你是一个<strong>普通用户</strong>（由于后台没定义 isAdmin，默认被当作 false）。</p>
                    <p>你的目标是：构造一个带有原型的恶意 JSON，使得页面把你当作 <strong>管理员 (Admin)</strong>！</p>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <h4>输入配置 (JSON)</h4>
                        <textarea id="userInput" class="form-control" rows="6" placeholder='{"color": "red", "title": "Hello"}'>{"color": "red", "title": "Hello"}</textarea>
                        <br>
                        <button id="renderBtn" class="btn btn-primary">生成卡片</button>
                    </div>
                    <div class="col-sm-6">
                        <h4>卡片预览区</h4>
                        <div id="cardPreview" style="border: 2px dashed #ccc; padding: 20px; border-radius: 8px;">
                            等待生成...
                        </div>
                    </div>
                </div>

                <script>
                    // 有缺陷的深度合并函数
                    function merge(target, source) {
                        for (let key in source) {
                            if (typeof source[key] === 'object' && source[key] !== null) {
                                if (!target[key]) {
                                    target[key] = {};
                                }
                                merge(target[key], source[key]);
                            } else {
                                target[key] = source[key];
                            }
                        }
                        return target;
                    }

                    document.getElementById('renderBtn').addEventListener('click', function() {
                        try {
                            let userConfig = JSON.parse(document.getElementById('userInput').value);
                            
                            // 默认安全配置
                            let config = {
                                "baseStyle": "modern"
                            };

                            // 合并用户输入
                            merge(config, userConfig);

                            // 模拟从后台获取的当前用户信息（没定义 isAdmin）
                            let currentUser = {
                                "username": "guest"
                            };

                            let preview = document.getElementById('cardPreview');
                            let cardHtml = `<h3 style="color:${config.color || 'black'}">${config.title || 'Untitled'}</h3>`;

                            // 漏洞利用点：如果 __proto__.isAdmin = true，那么 currentUser.isAdmin 就会变成 true！
                            if (currentUser.isAdmin) {
                                cardHtml += `<div class="alert alert-success">🎉 恭喜！你已成功利用原型链污染，获取了管理员特权卡片！</div>`;
                            } else {
                                cardHtml += `<div class="alert alert-info">👤 这是一个普通用户的卡片。想办法成为 Admin！</div>`;
                            }

                            preview.innerHTML = cardHtml;

                        } catch (e) {
                            document.getElementById('cardPreview').innerHTML = `<div class="alert alert-danger">JSON 解析错误：${e.message}</div>`;
                        }
                    });
                </script>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


