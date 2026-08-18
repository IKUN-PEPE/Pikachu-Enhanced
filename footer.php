<?php
if (!isset($PIKA_ROOT_DIR)){
    $PIKA_ROOT_DIR = '';
}
?>

<div class="footer" style="position:relative !important; clear:both !important; height:auto !important; width:100% !important; margin-top:50px !important; padding:0 !important; background:transparent !important;">
    <div class="footer-inner" style="position:relative !important; left:auto !important; right:auto !important; bottom:auto !important; width:100% !important; margin:0 !important;">
        <div class="footer-content" style="position:relative !important; left:auto !important; right:auto !important; bottom:auto !important; width:100% !important; background:transparent !important; border-top:1px solid var(--border-subtle) !important; padding:18px 24px !important; display:flex !important; justify-content:space-between !important; align-items:center !important; flex-wrap:wrap !important; gap:12px !important;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="label label-info" style="font-size:11px; padding:3px 8px; border-radius:4px;"><i class="fa fa-shield"></i> Pikachu-Enhanced</span>
                <span style="color:var(--text-muted); font-size:12.5px;">v2.0 Next-Gen Cyber-Range &copy; <?php echo date('Y'); ?></span>
            </div>
            <div style="color:var(--text-muted); font-size:12px; display:flex; gap:16px;">
                <span><i class="fa fa-terminal" style="color:var(--primary);"></i> 封闭演练靶场</span>
                <span><i class="fa fa-code-fork" style="color:var(--accent);"></i> 170+ 实战关卡</span>
            </div>
        </div>
    </div>
</div>

<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse" style="border-radius:var(--radius-full); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
    <i class="ace-icon fa fa-angle-double-up icon-only"></i>
</a>
</div><!-- /.main-container -->

<!-- basic scripts -->
<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/jquery-ui.custom.min.js"></script>
<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/ace-elements.min.js"></script>
<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/ace.min.js"></script>

<script>
    $(function (){
        $("[data-toggle='popover']").popover();
    });
</script>

</body>
</html>
