<?php
if (!isset($PIKA_ROOT_DIR)){
    $PIKA_ROOT_DIR = '';
}
?>

<footer class="footer" style="position:relative !important; clear:both !important; height:auto !important; width:100% !important; margin-top:var(--spacing-700) !important; padding:0 !important; background:transparent !important;">
    <div class="footer-inner" style="position:relative !important; left:auto !important; right:auto !important; bottom:auto !important; width:100% !important; margin:0 !important;">
        <div class="footer-content" style="position:relative !important; left:auto !important; right:auto !important; bottom:auto !important; width:100% !important; background:transparent !important; border-top:1px solid var(--border-subtle) !important; padding:var(--spacing-300) var(--spacing-400) !important; display:flex !important; justify-content:space-between !important; align-items:center !important; flex-wrap:wrap !important; gap:var(--spacing-200) !important;">
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; padding:4px 10px; border-radius:var(--radius-md); background:var(--bg-secondary); border:1px solid var(--border-subtle); color:var(--text-primary);">
                    <i class="ph-bold ph-shield-check" style="color:var(--primary); font-size:14px;"></i> Pikachu-Enhanced
                </span>
                <span style="color:var(--text-muted); font-size:13px;">Enterprise Cyber Range &copy; <?php echo date('Y'); ?> · MIT License</span>
            </div>
            <div style="color:var(--text-muted); font-size:13px; display:flex; align-items:center; gap:var(--spacing-300); flex-wrap:wrap;">
                <a href="<?php echo $PIKA_ROOT_DIR;?>index.php" style="color:var(--text-secondary); text-decoration:none; font-weight:500; transition:color 0.2s;"><i class="ph ph-terminal-window"></i> 靶场概览</a>
                <a href="<?php echo $PIKA_ROOT_DIR;?>intro.php" style="color:var(--text-secondary); text-decoration:none; font-weight:500; transition:color 0.2s;"><i class="ph ph-map-trifold"></i> 战术图鉴</a>
                <span style="display:inline-flex; align-items:center; gap:6px;"><i class="ph-bold ph-git-fork" style="color:var(--accent);"></i> 226+ 实战关卡</span>
                <span style="color:var(--text-muted);">|</span>
                <span style="font-size:12px; color:var(--text-muted);">本地离线沙箱 · 零数据外发</span>
            </div>
        </div>
    </div>
</footer>
</div>

<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse" style="border-radius:var(--radius-full); width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
    <i class="ph-bold ph-arrow-up" style="font-size:16px;"></i>
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


<!-- Pikachu-Enhanced v2.0 - 侧边栏智能滚动位置保持与实时过滤引擎 -->
<script>
(function($) {
    if (!$) return;
    
    // 1. 侧边栏滚动位置记忆与激活项自适应居中展示
    var sidebar = document.getElementById('sidebar');
    if (sidebar) {
        var savedScroll = sessionStorage.getItem('pika_sidebar_scroll_pos');
        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        }
        
        sidebar.addEventListener('scroll', function() {
            sessionStorage.setItem('pika_sidebar_scroll_pos', sidebar.scrollTop);
        }, { passive: true });
        
        // 当页面加载完成时，若激活项不在当前视口内，平滑滚动至居中位置
        setTimeout(function() {
            var activeLeaf = sidebar.querySelector('li.active:not(.open) > a') || sidebar.querySelector('li.active > a');
            if (activeLeaf) {
                var itemRect = activeLeaf.getBoundingClientRect();
                var sbRect = sidebar.getBoundingClientRect();
                if (itemRect.top < sbRect.top + 50 || itemRect.bottom > sbRect.bottom - 50) {
                    activeLeaf.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }
            }
        }, 120);
    }
    
    // 2. 实时侧边栏关卡全局检索过滤
    $(function() {
        var $input = $('#pika-sidebar-filter');
        var $clear = $('#pika-sidebar-filter-clear');
        if (!$input.length) return;
        
        // 缓存初始各分类折叠状态
        var defaultState = [];
        $('#sidebar .nav-list > li').each(function(idx) {
            defaultState[idx] = $(this).hasClass('open');
        });
        
        $clear.on('click', function() {
            $input.val('').trigger('input').focus();
        });
        
        $input.on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC 键清除
                $(this).val('').trigger('input');
            }
        });
        
        $input.on('input keyup', function() {
            var query = $.trim($(this).val()).toLowerCase();
            var $sidebar = $('#sidebar');
            
            if (query !== '') {
                $clear.show();
            } else {
                $clear.hide();
            }
            
            if (query === '') {
                // 恢复默认展开/折叠状态
                $sidebar.find('li').show();
                $sidebar.find('.nav-list > li').each(function(idx) {
                    if (defaultState[idx]) {
                        $(this).addClass('open').children('.submenu').show();
                    } else {
                        $(this).removeClass('open').children('.submenu').hide();
                    }
                });
                $sidebar.find('.nav-list > li > .submenu > li').each(function() {
                    if ($(this).hasClass('active') && $(this).hasClass('open')) {
                        $(this).addClass('open').children('.submenu').show();
                    } else if (!$(this).hasClass('open')) {
                        $(this).children('.submenu').hide();
                    }
                });
                return;
            }
            
            // 先隐藏所有 li
            $sidebar.find('.nav-list li').hide();
            
            // 模糊匹配包含关键字的菜单
            var matchCount = 0;
            $sidebar.find('.nav-list a').each(function() {
                var $a = $(this);
                // 排除纯目录折叠项本身的标签
                if ($a.hasClass('dropdown-toggle') && $a.parent().children('.submenu').length > 0) {
                    return;
                }
                var text = $a.text().toLowerCase();
                var href = ($a.attr('href') || '').toLowerCase();
                
                if (text.indexOf(query) !== -1 || href.indexOf(query) !== -1) {
                    matchCount++;
                    // 显示自身
                    $a.closest('li').show();
                    // 显示并展开所有祖先节点与子菜单
                    $a.parents('li').show().addClass('open').children('.submenu').show();
                }
            });
        });
    });
})(window.jQuery);
</script>

</body>
</html>
