<?php
if (!isset($PIKA_ROOT_DIR)){
    $PIKA_ROOT_DIR = '';
}
?>

<footer class="footer">
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
    
    // 2. 实时侧边栏关卡全局检索过滤 3.0 (高灵敏度 + 快捷键 + 完整状态恢复 + 动态计数校准)
    $(function() {
        var $sidebar = $('#sidebar');
        var $input = $('#pika-sidebar-filter');
        var $clear = $('#pika-sidebar-filter-clear');
        var $wrapper = $('#pika-search-wrapper');
        var $counter = $('#pika-search-counter');
        var $empty = $('#pika-search-empty');
        if (!$input.length) return;

        // 自动校准各主分类徽章数字，新增模块或关卡时无需手动维护数字
        $sidebar.find('.nav-list > li').each(function() {
            var $li = $(this);
            var $badge = $li.find('> a > .sidebar-cat-badge');
            if (!$badge.length) return;
            var $submods = $li.find('> .submenu > li > a.dropdown-toggle');
            if ($submods.length > 1) {
                $badge.text($submods.length + ' 模块');
            } else {
                var levelMatches = 0;
                $li.find('.submenu a').not('.dropdown-toggle').each(function() {
                    if (/\[关卡\s*\d+\]/.test($(this).text())) {
                        levelMatches++;
                    }
                });
                if (levelMatches > 0) {
                    $badge.text(levelMatches + ' 关卡');
                } else {
                    var totalLeaves = $li.find('.submenu a').not('.dropdown-toggle').length;
                    if (totalLeaves > 0) {
                        $badge.text(totalLeaves + ' 关卡');
                    }
                }
            }
        });

        // 全局键盘快捷键: 按斜杠(/)聚焦搜索框 (不劫持浏览器原生 Ctrl+K)
        $(document).on('keydown', function(e) {
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            var el = document.activeElement;
            var tag = el ? el.tagName : '';
            var isEditable = el && (el.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(tag) !== -1);
            if (e.key === '/' && !isEditable) {
                e.preventDefault();
                $input.focus().select();
            }
        });

        // 完整缓存初始一级与二级分类折叠状态
        var defaultLevel1State = [];
        var defaultLevel2State = [];
        $sidebar.find('.nav-list > li').each(function(idx) {
            defaultLevel1State[idx] = $(this).hasClass('open');
        });
        $sidebar.find('.nav-list > li > .submenu > li').each(function(idx) {
            defaultLevel2State[idx] = $(this).hasClass('open');
        });

        $clear.on('click', function() {
            $input.val('').trigger('input').focus();
        });

        $input.on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC 键清除并失焦
                $(this).val('').trigger('input').blur();
            }
        });

        $input.on('input keyup', function() {
            var query = $.trim($(this).val()).toLowerCase();
            var $dividers = $sidebar.find('.sidebar-section-divider');

            if (query !== '') {
                $wrapper.addClass('has-value');
                $clear.css('display', 'inline-flex');
                $dividers.hide();
            } else {
                $wrapper.removeClass('has-value');
                $clear.hide();
                $counter.hide();
                $empty.hide();
                $dividers.show();
            }

            if (query === '') {
                // 完整恢复一级与二级默认展开/折叠状态
                $sidebar.find('li').show();
                $sidebar.find('.nav-list > li').each(function(idx) {
                    if (defaultLevel1State[idx]) {
                        $(this).addClass('open').children('.submenu').show();
                    } else {
                        $(this).removeClass('open').children('.submenu').hide();
                    }
                });
                $sidebar.find('.nav-list > li > .submenu > li').each(function(idx) {
                    if (defaultLevel2State[idx]) {
                        $(this).addClass('open').children('.submenu').show();
                    } else {
                        $(this).removeClass('open').children('.submenu').hide();
                    }
                });
                return;
            }

            // 过滤匹配：先隐藏所有非分组标记项
            $sidebar.find('.nav-list li').not('.sidebar-section-divider').hide();

            // 模糊匹配包含关键字的菜单 (剥离 vul/ 与 .php 避免搜 php/vul 命中全表)
            var matchCount = 0;
            $sidebar.find('.nav-list a').each(function() {
                var $a = $(this);
                if ($a.hasClass('dropdown-toggle') && $a.parent().children('.submenu').length > 0) {
                    return;
                }
                var text = $a.text().toLowerCase();
                var rawHref = ($a.attr('href') || '').toLowerCase();
                var slug = rawHref.replace(/^.*?(?:vul\/|pkxss\/)/, '').replace(/\.php.*$/, '').replace(/[_\/]/g, ' ');

                if (text.indexOf(query) !== -1 || slug.indexOf(query) !== -1) {
                    matchCount++;
                    $a.closest('li').show();
                    $a.parents('li').show().addClass('open').children('.submenu').show();
                }
            });

            if (matchCount > 0) {
                $counter.html('<span>匹配结果:</span> <span>共 <strong>' + matchCount + '</strong> 项</span>').show();
                $empty.hide();
            } else {
                $counter.hide();
                $empty.show();
            }
        });
    });
})(window.jQuery);
</script>

</body>
</html>
