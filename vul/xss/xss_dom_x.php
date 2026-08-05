<?php
/**
 * Pikachu-Enhanced DOM XSS (x) Lab
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[7] = 'active open';
$ACTIVE[21] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$html = '';
if(isset($_GET['text'])){
    $html = "<div style='margin-top: 15px; padding: 15px; background: var(--bg-secondary); border-radius: 8px; border: 1px dashed var(--border-color);'><a href='#' onclick='domxss()' style='font-weight:bold; color:#10b981;'>有些话有些事要记在心里,按我</a></div>";
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="vul">
                <h2>DOM型 XSS (x) 演练</h2>
                <p>本关卡演示了前端从 URL 查询参数中提取数据，并将其动态写入 DOM 属性引发的 XSS 攻击场景。</p>
                
                <div id="xssd_main" style="margin-top: 20px;">
                    <script>
                        function domxss(){
                            var str = window.location.search;
                            var txss = decodeURIComponent(str.split("text=")[1] || '');
                            var xss = txss.replace(/\+/g,' ');
                            document.getElementById("dom").innerHTML = "<a href='"+xss+"' style='font-weight:bold; color:#ef4444;'>就让往事随风,都随风</a>";
                        }
                    </script>
                    
                    <form method="get" style="display: flex; gap: 10px; max-width: 600px;">
                        <input id="text" name="text" type="text" class="form-control" placeholder="输入 text 参数..." style="flex: 1;" />
                        <input id="submit" type="submit" class="btn btn-primary" value="提交 URL 参数"/>
                    </form>
                    
                    <?php echo $html; ?>
                    <div id="dom" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
