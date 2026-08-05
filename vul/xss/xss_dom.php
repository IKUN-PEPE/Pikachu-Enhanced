<?php
/**
 * Pikachu-Enhanced DOM XSS Lab
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[7] = 'active open';
$ACTIVE[12] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="vul">
                <h2>DOM型 XSS 漏洞演练</h2>
                <p>DOM (Document Object Model) 型 XSS 是一种通过客户端前端 JavaScript 脚本直接修改 DOM 树结构而触发的跨站脚本漏洞。</p>
                
                <div id="xssd_main" style="margin-top: 20px;">
                    <script>
                        function domxss(){
                            var str = document.getElementById("text").value;
                            document.getElementById("dom").innerHTML = "<a href='"+str+"' target='_blank' style='font-weight:bold; font-size:16px; color:#2563eb;'>what do you see? (点击测试 payload)</a>";
                        }
                    </script>
                    
                    <div style="display: flex; gap: 10px; max-width: 600px; margin-bottom: 20px;">
                        <input id="text" name="text" type="text" class="form-control" placeholder="输入 payload，如: ' onclick='alert(1)'" style="flex: 1;" />
                        <input id="button" type="button" class="btn btn-primary" value="生成 DOM 链接" onclick="domxss()" />
                    </div>
                    
                    <div id="dom" style="padding: 15px; background: var(--bg-secondary); border-radius: 8px; border: 1px dashed var(--border-color); min-height: 50px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
