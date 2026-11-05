<?php
///**
// * Created by runner.han
// * There is nothing new under the sun
// */


ob_start();

$PIKA_ROOT_DIR = isset($PIKA_ROOT_DIR) ? $PIKA_ROOT_DIR : '';
$ACTIVE = (array)@$ACTIVE;
include_once __DIR__ . '/inc/nav.inc.php';
$CAT_CLASS = [];
$MOD_CLASS = [];
$LEAF_CLASS = [];
pika_resolve_navigation($ACTIVE, $CAT_CLASS, $MOD_CLASS, $LEAF_CLASS);


//$ACTIVE = array("active open","active","","","");

if (!isset($ACTIVE)){
    $SELF_PAGE = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
    if ($SELF_PAGE = "index.php"){
        //22 title
        $ACTIVE = array_fill(0, 300, '');
        $ACTIVE[0] = "active";

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>Get the pikachu</title>

    <meta name="description" content="overview &amp; stats" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <!-- Google Fonts: Geist & Geist Mono (Modern Craftsmanship) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Phosphor Icons (High Quality Clean Vector Icons) -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>

    <!-- bootstrap & fontawesome fallback -->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/font-awesome/4.5.0/css/font-awesome.min.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-skins.min.css" />
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-rtl.min.css" />
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/pika_unified.css?v=<?php echo filemtime(dirname(__FILE__) . '/assets/css/pika_unified.css'); ?>" />

    <style>
    .sidebar .nav-list > li > a { border-left: none !important; }
    .sidebar .nav-list > li.active > a:after, .sidebar .nav-list > li.active:after { display: none !important; }
    </style>

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- ace settings handler -->
    <script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/ace-extra.min.js"></script>

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/html5shiv.min.js"></script>
    <script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/respond.min.js"></script>
    <![endif]-->
    <script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/jquery-2.1.4.min.js"></script>
    <script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/bootstrap.min.js"></script>
    
    <!-- Theme Manager -->
    <script>
        (function() {
            var currentTheme = localStorage.getItem('pika_theme');
            if (!currentTheme) {
                // Default to dark theme if not set
                currentTheme = 'dark';
                localStorage.setItem('pika_theme', currentTheme);
            }
            document.documentElement.setAttribute('data-theme', currentTheme);
        })();
    </script>
</head>

<body class="no-skin">
<div id="navbar" class="navbar navbar-default ace-save-state">
    <div class="navbar-container ace-save-state" id="navbar-container">

        <div class="navbar-header pull-left">
            <a href="<?php echo $PIKA_ROOT_DIR;?>index.php" class="navbar-brand" style="text-decoration:none;">
                <span class="brand-logo-badge" style="background:#0284c7; color:#fff; border-radius:6px;"><i class="ph-bold ph-shield-check" style="font-size:18px;"></i></span>
                <span class="brand-title" style="letter-spacing:-0.02em;">Pikachu-Enhanced</span>
                <span class="brand-badge" style="font-weight:600; letter-spacing:0.04em;">v3.0 ENTERPRISE</span>
            </a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('theme-toggle-btn');
                var icon = document.getElementById('theme-icon');
                
                function updateIcon(theme) {
                    if (theme === 'dark') {
                        icon.className = 'ph-bold ph-sun';
                    } else {
                        icon.className = 'ph-bold ph-moon';
                    }
                }
                
                var current = document.documentElement.getAttribute('data-theme') || 'dark';
                updateIcon(current);
                
                if (btn) {
                    btn.addEventListener('click', function() {
                        var currentTheme = document.documentElement.getAttribute('data-theme');
                        var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                        document.documentElement.setAttribute('data-theme', newTheme);
                        localStorage.setItem('pika_theme', newTheme);
                        updateIcon(newTheme);
                    });
                }
            });
        </script>

        <div class="navbar-buttons navbar-header pull-right" role="navigation">
            <button id="theme-toggle-btn" class="theme-toggle-btn" title="切换深色/浅色模式">
                <i class="ph-bold ph-sun" id="theme-icon" style="font-size:16px;"></i>
            </button>
            <ul class="nav ace-nav" style="display:flex; align-items:center; background:transparent;">
                <li class="dropdown-modal">
                    <a data-toggle="dropdown" href="#" class="dropdown-toggle user-profile-btn" style="background:transparent; padding:0 8px; display:flex; align-items:center;">
                        <img class="nav-user-photo" src="<?php echo $PIKA_ROOT_DIR;?>assets/images/avatars/pikachu1.png" alt="Pikachu" />
                        <span class="user-info">
                            <span style="font-weight:700; color:var(--text-primary); font-size:13px;">Security Operator</span>
                        </span>
                        <i class="ace-icon fa fa-caret-down" style="color:var(--text-muted); margin-left:4px;"></i>
                    </a>
                    <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close" style="background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-md); box-shadow:var(--shadow-lg); padding:8px 0;">
                        <li>
                            <a href="<?php echo $PIKA_ROOT_DIR;?>index.php" style="color:var(--text-primary); padding:8px 16px; font-weight:600;">
                                <i class="ph-bold ph-house" style="margin-right:6px; font-size:15px; vertical-align:middle;"></i> 平台首页
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $PIKA_ROOT_DIR;?>intro.php" style="color:var(--text-primary); padding:8px 16px; font-weight:600;">
                                <i class="ph-bold ph-map-trifold" style="margin-right:6px; font-size:15px; vertical-align:middle;"></i> 漏洞图鉴
                            </a>
                        </li>
                        <li class="divider" style="background:var(--border-subtle); margin:6px 0;"></li>
                        <li>
                            <a href="<?php echo $PIKA_ROOT_DIR;?>install.php" style="color:var(--danger); padding:8px 16px; font-weight:600;">
                                <i class="ph-bold ph-arrows-clockwise" style="margin-right:6px; font-size:15px; vertical-align:middle;"></i> 初始化环境
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div><!-- /.navbar-container -->
</div>

<div class="main-container ace-save-state" id="main-container">
    <script type="text/javascript">
        try{ace.settings.loadState('main-container')}catch(e){}
    </script>


    <div id="sidebar" class="sidebar                  responsive                    ace-save-state">
        <!-- Pikachu Enhanced v3.0 - 侧边栏现代检索中枢 -->
        <div class="sidebar-search-container">
            <div class="sidebar-search-wrapper" id="pika-search-wrapper">
                <i class="ph-bold ph-magnifying-glass sidebar-search-icon"></i>
                <input type="text" id="pika-sidebar-filter" class="sidebar-search-input" placeholder="搜索关卡 / 漏洞 / 协议..." autocomplete="off">
                <span class="sidebar-search-kbd" id="pika-search-kbd">
                    <kbd>/</kbd>
                </span>
                <button type="button" id="pika-sidebar-filter-clear" class="sidebar-search-clear" title="清空搜索 (Esc)">
                    <i class="ph-bold ph-x-circle"></i>
                </button>
            </div>
            <div id="pika-search-counter" class="sidebar-search-counter" style="display: none;"></div>
        </div>

        <script type="text/javascript">
            try{ace.settings.loadState('sidebar')}catch(e){}
        </script>

        <ul class="nav nav-list">
            <li class="sidebar-section-divider">
                <span class="sidebar-section-title"><i class="ph-bold ph-compass"></i> 平台概览与指南</span>
            </li>
            <li class="<?php echo pika_is_active('index.php', 0);?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>index.php">
                    <span class="menu-text">📖 系统介绍与说明</span>
                </a>
            </li>

            <li class="<?php echo pika_is_active('intro.php', 330);?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>intro.php">
                    <span class="menu-text">📊 全局漏洞图鉴 (v2.0)</span>
                </a>
            </li>


            <li class="sidebar-section-divider">
                <span class="sidebar-section-title"><i class="ph-bold ph-shield"></i> Web 应用安全攻防</span>
            </li>
            <li class="<?php echo !empty($CAT_CLASS['classic']) ? $CAT_CLASS['classic'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-classic">
                    <span class="menu-text" style="font-weight: bold;"> 🏛️ 经典 Web 基础攻防 </span>
                    <span class="sidebar-cat-badge badge-web">12 模块</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[1]) ? $ACTIVE[1] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">💥 暴力破解与字典攻击</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[2]) ? $ACTIVE[2] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/burteforce.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[3]) ? $ACTIVE[3] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_form.php">基于表单的暴力破解</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[4]) ? $ACTIVE[4] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_server.php">验证码绕过(on server)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[5]) ? $ACTIVE[5] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_client.php">验证码绕过(on client)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[6]) ? $ACTIVE[6] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_token.php">token防爆破?</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[7]) ? $ACTIVE[7] : (!empty($ACTIVE[120]) ? $ACTIVE[120] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">⚡ XSS 跨站脚本与钓鱼平台</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[8]) ? $ACTIVE[8] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[9]) ? $ACTIVE[9] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_reflected_get.php">反射型xss(get)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[10]) ? $ACTIVE[10] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xsspost/post_login.php">反射型xss(post)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[11]) ? $ACTIVE[11] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_stored.php">存储型xss</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[12]) ? $ACTIVE[12] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_dom.php">DOM型xss</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[21]) ? $ACTIVE[21] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_dom_x.php">DOM型xss-x</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[13]) ? $ACTIVE[13] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xssblind/xss_blind.php">xss之盲打</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[14]) ? $ACTIVE[14] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_01.php">xss之过滤</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[15]) ? $ACTIVE[15] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_02.php">xss之htmlspecialchars</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[16]) ? $ACTIVE[16] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_03.php">xss之href输出</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[17]) ? $ACTIVE[17] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_04.php">xss之js输出</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[121]) ? $ACTIVE[121] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>pkxss/index.php">🎯 XSS 钓鱼与管理后台</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[25]) ? $ACTIVE[25] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔄 CSRF 跨站请求伪造</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[26]) ? $ACTIVE[26] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[27]) ? $ACTIVE[27] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrfget/csrf_get_login.php">CSRF(get)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[28]) ? $ACTIVE[28] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrfpost/csrf_post_login.php">CSRF(post)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[29]) ? $ACTIVE[29] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrftoken/token_get_login.php">CSRF Token</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[30]) ? $ACTIVE[30] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf_referer/csrf_referer.php">CSRF Referer 绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[31]) ? $ACTIVE[31] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf_token_pool/csrf_token_pool.php">CSRF Token池未绑定</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[32]) ? $ACTIVE[32] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf_json/csrf_json.php">CSRF JSON 表单混淆</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[33]) ? $ACTIVE[33] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf_double_cookie/csrf_double_cookie.php">CSRF 双重Cookie绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[34]) ? $ACTIVE[34] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf_samesite/csrf_samesite.php">CSRF SameSite Lax 绕过</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[35]) ? $ACTIVE[35] : (!empty($ACTIVE[162]) ? $ACTIVE[162] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">💉 SQL 与 NoSQL 数据库注入</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[36]) ? $ACTIVE[36] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[37]) ? $ACTIVE[37] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_id.php">数字型注入(post)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[38]) ? $ACTIVE[38] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_str.php">字符型注入(get)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[39]) ? $ACTIVE[39] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_search.php">搜索型注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[40]) ? $ACTIVE[40] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_x.php">xx型注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[41]) ? $ACTIVE[41] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_iu/sqli_login.php">"insert/update"注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[42]) ? $ACTIVE[42] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_del.php">"delete"注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[43]) ? $ACTIVE[43] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_header/sqli_header_login.php">"http header"注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[44]) ? $ACTIVE[44] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_blind_b.php">盲注(base on boolian)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[45]) ? $ACTIVE[45] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_blind_t.php">盲注(base on time)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[46]) ? $ACTIVE[46] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_widebyte.php">宽字节注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[164]) ? $ACTIVE[164] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/nosql/mongo_bypass.php">🍃 MongoDB 认证绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[319]) ? $ACTIVE[319] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/nosql/mongo_operator.php">🍃 MongoDB 操作符盲注</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[50]) ? $ACTIVE[50] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">💻 RCE 远程命令与模板注入</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[51]) ? $ACTIVE[51] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[52]) ? $ACTIVE[52] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_ping.php">exec "ping"</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[53]) ? $ACTIVE[53] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_eval.php">exec "eval"</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[54]) ? $ACTIVE[54] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_bypass.php">命令过滤与 WAF 绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[227]) ? $ACTIVE[227] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_blind.php">无回显盲 RCE (OOB)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[228]) ? $ACTIVE[228] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_ssti.php">服务端模板注入 (SSTI)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[55]) ? $ACTIVE[55] : (!empty($ACTIVE[60]) ? $ACTIVE[60] : (!empty($ACTIVE[80]) ? $ACTIVE[80] : ''));?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">📁 文件包含、下载与目录穿越</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[56]) ? $ACTIVE[56] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fileinclude.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[57]) ? $ACTIVE[57] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fi_local.php">本地文件包含 (LFI)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[58]) ? $ACTIVE[58] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fi_remote.php">远程文件包含 (RFI)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[62]) ? $ACTIVE[62] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafedownload/down_nba.php">📥 任意文件下载漏洞</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[82]) ? $ACTIVE[82] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dir/dir_list.php">📂 目录遍历与路径穿越</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[65]) ? $ACTIVE[65] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">📤 不安全的文件上传</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[66]) ? $ACTIVE[66] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/upload.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[67]) ? $ACTIVE[67] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/clientcheck.php">client check (前端校验)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[68]) ? $ACTIVE[68] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/servercheck.php">MIME type (类型校验)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[69]) ? $ACTIVE[69] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/getimagesize.php">getimagesize (图片木马)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[208]) ? $ACTIVE[208] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/zip_slip.php">Zip Slip (解压目录穿越)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[73]) ? $ACTIVE[73] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">👥 越权访问控制 (BAC / BOLA)</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[74]) ? $ACTIVE[74] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[75]) ? $ACTIVE[75] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op1/op1_login.php">水平越权</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[76]) ? $ACTIVE[76] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op2/op2_login.php">垂直越权</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[77]) ? $ACTIVE[77] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/modern/op_bola.php">API IDOR (BOLA 对象越权)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[78]) ? $ACTIVE[78] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/modern/op_mass_assign.php">Mass Assignment (批量赋值)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[85]) ? $ACTIVE[85] : (!empty($ACTIVE[196]) ? $ACTIVE[196] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔓 敏感信息与配置泄露</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[86]) ? $ACTIVE[86] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/infoleak/infoleak.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[87]) ? $ACTIVE[87] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/infoleak/findabc.php">前端注释与敏感信息泄露</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[198]) ? $ACTIVE[198] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/env_leak.php">.env 敏感配置泄露</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[199]) ? $ACTIVE[199] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/git_leak.php">.git 源码泄露利用</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[318]) ? $ACTIVE[318] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/swagger_unauth.php">Swagger UI 接口未授权泄露</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[90]) ? $ACTIVE[90] : (!empty($ACTIVE[180]) ? $ACTIVE[180] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">📦 序列化与反序列化 (PHP/Java)</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[91]) ? $ACTIVE[91] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unserilization/unserilization.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[92]) ? $ACTIVE[92] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unserilization/unser.php">PHP 原生反序列化利用</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[182]) ? $ACTIVE[182] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/phar/phar_unserialize.php">Phar 归档反序列化利用</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[220]) ? $ACTIVE[220] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/java_unserialize/java_unserialize.php">Java 反序列化与 Fastjson 探测</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[94]) ? $ACTIVE[94] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/java_unserialize/fastjson_rce.php">Fastjson 远程代码执行 (1.2.24/47)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[98]) ? $ACTIVE[98] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/java_unserialize/native_unser.php">Java Commons Collections 利用</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[105]) ? $ACTIVE[105] : (!empty($ACTIVE[194]) ? $ACTIVE[194] : (!empty($ACTIVE[95]) ? $ACTIVE[95] : ''));?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🌐 SSRF 与 XXE 服务端请求注入</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[106]) ? $ACTIVE[106] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[107]) ? $ACTIVE[107] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_curl.php">SSRF (curl)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[108]) ? $ACTIVE[108] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_fgc.php">SSRF (file_get_content)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[109]) ? $ACTIVE[109] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_cloud.php">SSRF (Cloud Metadata)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[209]) ? $ACTIVE[209] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_gopher_redis.php">SSRF (Gopher 打击 Redis)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[219]) ? $ACTIVE[219] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_dns_rebinding.php">SSRF (DNS 重绑定与 IP 变形)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[314]) ? $ACTIVE[314] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/webhook/webhook_ssrf.php">📨 Webhook 回调盲 SSRF</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[97]) ? $ACTIVE[97] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xxe/xxe_1.php">📄 XXE 外部实体注入</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[132]) ? $ACTIVE[132] : (!empty($ACTIVE[136]) ? $ACTIVE[136] : (!empty($ACTIVE[100]) ? $ACTIVE[100] : (!empty($ACTIVE[151]) ? $ACTIVE[151] : '')));?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🌍 客户端跨域、重定向与前端安全</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[133]) ? $ACTIVE[133] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors.php">CORS 跨域概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[134]) ? $ACTIVE[134] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors_reflect.php">CORS Origin 任意反射</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[135]) ? $ACTIVE[135] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors_credential.php">CORS Allow-Credentials 窃取</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[138]) ? $ACTIVE[138] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/clickjacking/target.php">🖱️ 点击劫持：目标页面</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[139]) ? $ACTIVE[139] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/clickjacking/attacker.php">🖱️ 点击劫持：恶意诱导页</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[102]) ? $ACTIVE[102] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/urlredirect/urlredirect.php">🔗 不安全的 URL 跳转</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[153]) ? $ACTIVE[153] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/frontend/dom_clobbering.php">🧬 DOM Clobbering 污染</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[156]) ? $ACTIVE[156] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/frontend/prototype_pollution.php">🧬 JavaScript 原型链污染</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['auth']) ? $CAT_CLASS['auth'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-auth">
                    <span class="menu-text" style="font-weight: bold;"> 🔑 身份认证与访问控制 </span>
                    <span class="sidebar-cat-badge badge-auth">3 模块</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo !empty($ACTIVE[157]) ? $ACTIVE[157] : (!empty($ACTIVE[168]) ? $ACTIVE[168] : (!empty($ACTIVE[186]) ? $ACTIVE[186] : ''));?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🎟️ 现代令牌与单点登录 (JWT/OAuth/SAML)</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[158]) ? $ACTIVE[158] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[124]) ? $ACTIVE[124] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_login.php">JWT 认证绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[159]) ? $ACTIVE[159] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_none.php">JWT None 算法绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[316]) ? $ACTIVE[316] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_weak_secret.php">JWT 弱密钥爆破</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[317]) ? $ACTIVE[317] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_key_confusion.php">JWT 算法混淆 (RS-to-HS)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[170]) ? $ACTIVE[170] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oauth/state_bypass.php">🔑 OAuth 2.0 State 劫持</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[310]) ? $ACTIVE[310] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sso_saml/saml_xsw.php">🏢 SAML 签名包装 (XSW)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[200]) ? $ACTIVE[200] : (!empty($ACTIVE[128]) ? $ACTIVE[128] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔢 会话管理与多因素认证 (Session/MFA)</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[201]) ? $ACTIVE[201] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/mfa_bypass/mfa_bypass.php">MFA 概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[315]) ? $ACTIVE[315] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/mfa_bypass/mfa_logic_bypass.php">2FA 逻辑绕过与轰炸</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[130]) ? $ACTIVE[130] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sessionfixation/fixation_login.php">⏳ 会话固定：漏洞登录页</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[131]) ? $ACTIVE[131] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sessionfixation/fixation_profile.php">⏳ 会话固定：登录后信息页</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[340]) ? $ACTIVE[340] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔐 前端加解密与签名对抗</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[341]) ? $ACTIVE[341] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/encrypt.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[342]) ? $ACTIVE[342] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/aes.php">AES 固定 Key</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[343]) ? $ACTIVE[343] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/aesserver.php">AES 服务端获取 Key</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[344]) ? $ACTIVE[344] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/rsa.php">RSA 非对称加密</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[345]) ? $ACTIVE[345] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/aesrsa.php">AES + RSA 混合加密</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[346]) ? $ACTIVE[346] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/des.php">DES 规律 Key</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[347]) ? $ACTIVE[347] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/signdata.php">明文加签防篡改</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[348]) ? $ACTIVE[348] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/signdataserver.php">服务端动态加签</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[349]) ? $ACTIVE[349] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/norepeater.php">防止重放测试</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['business']) ? $CAT_CLASS['business'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-business">
                    <span class="menu-text" style="font-weight: bold;"> ⚡ 现代业务逻辑与并发 </span>
                    <span class="sidebar-cat-badge badge-biz">2 模块</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[148]) ? $ACTIVE[148] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">💳 业务逻辑与越权支付</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[149]) ? $ACTIVE[149] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/logic/logic.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[150]) ? $ACTIVE[150] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/logic/price_tamper.php">价格与数量篡改</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php echo isset($ACTIVE[171]) ? $ACTIVE[171] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">⚡ 条件竞争与并发漏洞 (TOCTOU)</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[172]) ? $ACTIVE[172] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/race_condition/race_condition.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[173]) ? $ACTIVE[173] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/race_condition/gift_card.php">并发竞争兑换 (Race Condition)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="sidebar-section-divider">
                <span class="sidebar-section-title"><i class="ph-bold ph-cloud"></i> 云原生与前沿架构</span>
            </li>
            <li class="<?php echo !empty($CAT_CLASS['cloud']) ? $CAT_CLASS['cloud'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-cloud">
                    <span class="menu-text" style="font-weight: bold;"> ☁️ 云原生与容器安全 </span>
                    <span class="sidebar-cat-badge badge-cloud">3 模块</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[140]) ? $ACTIVE[140] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🐳 容器逃逸与 K8s 集群安全</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[141]) ? $ACTIVE[141] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab.php">概述与架构</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[142]) ? $ACTIVE[142] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab_check.php">环境检查与诊断</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[143]) ? $ACTIVE[143] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab_center.php">⚡ 容器靶场控制中心</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[210]) ? $ACTIVE[210] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_privileged_escape.php">🐳 1. Docker 特权模式逃逸</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[211]) ? $ACTIVE[211] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_sock_escape.php">🐳 2. Docker Socket 挂载逃逸</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[212]) ? $ACTIVE[212] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_caps_escape.php">🐳 3. Linux Capabilities 逃逸</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[213]) ? $ACTIVE[213] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_cve_escape.php">🐳 4. runc 容器逃逸 (CVE)</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[207]) ? $ACTIVE[207] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/k8s_token_escape.php">☸️ 5. Kubernetes Token 逃逸</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[144]) ? $ACTIVE[144] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">📦 容器化中间件真实靶场</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[214]) ? $ACTIVE[214] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_redis_lab.php">🛢️ 6. Redis 7.0 未授权与提权</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[215]) ? $ACTIVE[215] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_fastjson_lab.php">☕ 7. Fastjson 反序列化 JNDI</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[216]) ? $ACTIVE[216] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_log4j2_lab.php">🔥 8. Log4j2 JNDI 远程代码执行</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[217]) ? $ACTIVE[217] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_flask_ssti_lab.php">🐍 9. Python Flask SSTI 模板注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[218]) ? $ACTIVE[218] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/docker_mysql_lab.php">🛢️ 10. MySQL 8.0 弱口令与 UDF</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[188]) ? $ACTIVE[188] : (!empty($ACTIVE[190]) ? $ACTIVE[190] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🪣 云存储 OSS/S3 与 Serverless</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[189]) ? $ACTIVE[189] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cloud_storage/cloud_storage.php">云存储安全概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[311]) ? $ACTIVE[311] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cloud_storage/oss_bucket_unauth.php">Bucket 越权读写与覆盖</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[191]) ? $ACTIVE[191] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/serverless/serverless.php">Serverless 函数安全概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[312]) ? $ACTIVE[312] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/serverless/lambda_env_leak.php">⚡ FaaS 环境变量凭证窃取</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['proto']) ? $CAT_CLASS['proto'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-proto">
                    <span class="menu-text" style="font-weight: bold;"> 🌐 现代网络协议与 API </span>
                    <span class="sidebar-cat-badge badge-proto">3 模块</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo !empty($ACTIVE[183]) ? $ACTIVE[183] : (!empty($ACTIVE[125]) ? $ACTIVE[125] : (!empty($ACTIVE[174]) ? $ACTIVE[174] : ''));?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔀 HTTP 走私、缓存与网关安全</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[184]) ? $ACTIVE[184] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/http_smuggling/http_smuggling.php">HTTP 走私概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[185]) ? $ACTIVE[185] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/http_smuggling/cl_te.php">CL.TE 走私与鉴权绕过</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[127]) ? $ACTIVE[127] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/hostheader/trust.php">🏷️ Host Header 注入投毒</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[176]) ? $ACTIVE[176] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/web_cache/cache_deception.php">📦 Web 缓存欺骗 (WCD)</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo !empty($ACTIVE[192]) ? $ACTIVE[192] : (!empty($ACTIVE[160]) ? $ACTIVE[160] : '');?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">⚡ 微服务 gRPC 与 GraphQL 接口</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[193]) ? $ACTIVE[193] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/grpc/grpc.php">gRPC 接口安全概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[313]) ? $ACTIVE[313] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/grpc/grpc_auth_bypass.php">gRPC 越权与参数篡改</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[161]) ? $ACTIVE[161] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/graphql/graphql.php">🕸️ GraphQL 内省泄露与注入</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php echo isset($ACTIVE[177]) ? $ACTIVE[177] : '';?>">
                        <a href="#" class="dropdown-toggle">
                            <span class="menu-text">🔄 WebSocket 实时通信安全</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="<?php echo isset($ACTIVE[178]) ? $ACTIVE[178] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/websocket.php">概述</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[179]) ? $ACTIVE[179] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/cswsh.php">跨站 WebSocket 劫持</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[205]) ? $ACTIVE[205] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/ws_sqli.php">WS 数据帧 SQL 注入</a>
                                <b class="arrow"></b>
                            </li>
                            <li class="<?php echo isset($ACTIVE[206]) ? $ACTIVE[206] : '';?>">
                                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/ws_unauth_stream.php">WS 未授权敏感流订阅</a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="sidebar-section-divider">
                <span class="sidebar-section-title"><i class="ph-bold ph-cpu"></i> 内网实战与纵深防御</span>
            </li>
            <li class="<?php echo !empty($CAT_CLASS['ai']) ? $CAT_CLASS['ai'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-ai">
                    <span class="menu-text" style="font-weight: bold;"> 🤖 AI 大模型应用安全 </span>
                    <span class="sidebar-cat-badge badge-ai">4 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[166]) ? $ACTIVE[166] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/ai_security.php">📌 AI 大模型安全总览</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[167]) ? $ACTIVE[167] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/prompt_injection.php">🧠 [关卡 1] Prompt 注入绕过</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[202]) ? $ACTIVE[202] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_data_leakage.php">🔓 [关卡 2] 敏感规则与提示词提取</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[203]) ? $ACTIVE[203] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_plugin_rce.php">💻 [关卡 3] 插件工具命令注入 (RCE)</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[204]) ? $ACTIVE[204] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_xss.php">⚡ [关卡 4] 不安全渲染 (XSS & SSRF)</a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['defense']) ? $CAT_CLASS['defense'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-defense">
                    <span class="menu-text" style="font-weight: bold;"> 🛡️ 蓝队防守与实战防御 </span>
                    <span class="sidebar-cat-badge badge-defense">5 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
<li class="<?php echo isset($ACTIVE[221]) ? $ACTIVE[221] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense.php">
                            📌 蓝队防守实战总控大厅
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[222]) ? $ACTIVE[222] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense_waf.php">
                            🛡️ [关卡 1] WAF 流量拦截与规则检测
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[223]) ? $ACTIVE[223] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense_rasp.php">
                            ⚡ [关卡 2] RASP 运行时 Hook 监控
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[224]) ? $ACTIVE[224] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense_log_forensics.php">
                            🔍 [关卡 3] Web 入侵日志取证排查
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[225]) ? $ACTIVE[225] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense_honeypot.php">
                            🍯 [关卡 4] 蜜罐欺骗与 Canary 蜜标
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[226]) ? $ACTIVE[226] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense_siem.php">
                            📊 [关卡 5] SIEM & Sysmon Sigma 规则
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['ad']) ? $CAT_CLASS['ad'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-ad">
                    <span class="menu-text" style="font-weight: bold;"> 🏢 Active Directory 域安全 </span>
                    <span class="sidebar-cat-badge badge-ad">15 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
<li class="<?php echo isset($ACTIVE[231]) ? $ACTIVE[231] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_security.php">
                            📌 概览与总纲大厅
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_knowledge.php', 235); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_knowledge.php">
                            📖 内网渗透与域安全知识全景
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[237]) ? $ACTIVE[237] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_env_check.php">
                            🔍 GOAD 依赖智能识别中心
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[236]) ? $ACTIVE[236] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_lab_setup.php">
                            📐 3台/5台 GOAD 拓扑与部署蓝图
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[238]) ? $ACTIVE[238] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_hub.php">
                            🏆 GOAD 域渗透 CTF 夺旗大厅 (4600 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[239]) ? $ACTIVE[239] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_recon.php">
                            🚩 [关卡 1] 侦察与 BloodHound 测绘
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[240]) ? $ACTIVE[240] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_asrep.php">
                            🚩 [关卡 2] AS-REP Roasting 预认证爆破
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[241]) ? $ACTIVE[241] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_kerberoast.php">
                            🚩 [关卡 3] Kerberoasting 票据离线破解
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[242]) ? $ACTIVE[242] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_mssql.php">
                            🚩 [关卡 4] MSSQL 模拟特权与 xp_cmdshell
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[243]) ? $ACTIVE[243] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_adcs.php">
                            🚩 [关卡 5] AD CS 证书 ESC1 模板滥用
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[244]) ? $ACTIVE[244] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_delegation.php">
                            🚩 [关卡 6] 约束性委派 S4U2Proxy 提权
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[245]) ? $ACTIVE[245] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_rbcd.php">
                            🚩 [关卡 7] 基于资源的约束委派 (RBCD)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[246]) ? $ACTIVE[246] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_esc8.php">
                            🚩 [关卡 8] AD CS ESC8 NTLM HTTP 中继
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[247]) ? $ACTIVE[247] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_shadow_cred.php">
                            🚩 [关卡 9] 影子凭据 (Shadow Credentials)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[248]) ? $ACTIVE[248] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_acl.php">
                            🚩 [关卡 10] ACL 链式滥用与林根接管
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_ctf_coerce.php', 249); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_coerce.php">
                            🚩 [关卡 11] 强制认证与 NTLM Relay
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_ctf_nopac.php', 232); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_nopac.php">
                            🚩 [关卡 12] noPac 欺骗与高级 Kerberos
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_ctf_domain_trust.php', 233); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_domain_trust.php">
                            🚩 [关卡 13] 父子域信任与 SID History
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_ctf_forest_trust.php', 234); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_forest_trust.php">
                            🚩 [关卡 14] 跨林攻击与外域组/数据库信任
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo pika_is_active('vul/ad_security/ad_ctf_gpo.php', 235); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_gpo.php">
                            🚩 [关卡 15] GPO 组策略滥用与横向提权 [终章]
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="sidebar-section-divider">
                <span class="sidebar-section-title"><i class="ph-bold ph-trophy"></i> OffSec 认证与企业实战</span>
            </li>
            <li class="<?php echo !empty($CAT_CLASS['osep']) ? $CAT_CLASS['osep'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-osep">
                    <span class="menu-text" style="font-weight: bold;"> 🎯 OSEP 内网穿透红队 </span>
                    <span class="sidebar-cat-badge badge-offsec">18 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
<li class="<?php echo isset($ACTIVE[250]) ? $ACTIVE[250] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_hub.php">
                            📌 概览与夺旗大厅 (4750 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[252]) ? $ACTIVE[252] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l1_enum.php">
                            🚩 [关卡 1] 主机侦察与信息收集
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[253]) ? $ACTIVE[253] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l2_phishing.php">
                            🚩 [关卡 2] 鱼叉钓鱼与载荷投递
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[254]) ? $ACTIVE[254] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l3_lateral.php">
                            🚩 [关卡 3] 横向移动 WMI/Remoting
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[255]) ? $ACTIVE[255] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l4_pivot.php">
                            🚩 [关卡 4] 内网穿透 SOCKS5 隧道
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[256]) ? $ACTIVE[256] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l5_av_evasion.php">
                            🚩 [关卡 5] 杀软检测架构与防御
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[257]) ? $ACTIVE[257] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l6_persistence.php">
                            🚩 [关卡 6] 系统持久化机制研究
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[258]) ? $ACTIVE[258] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l7_exfil.php">
                            🚩 [关卡 7] 隐蔽数据外渗通道分析
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[280]) ? $ACTIVE[280] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l8_win_api.php">
                            🚩 [关卡 8] Win32 API·WOW64·注册表
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[281]) ? $ACTIVE[281] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l9_office_macro.php">
                            🚩 [关卡 9] Office 宏武器与 VBA 免杀
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[282]) ? $ACTIVE[282] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l10_process_inject.php">
                            🚩 [关卡 10] 进程注入与 Process Hollow
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[283]) ? $ACTIVE[283] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l11_amsi_bypass.php">
                            🚩 [关卡 11] AMSI 深入绕过与 UAC 提权
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[284]) ? $ACTIVE[284] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l12_applocker.php">
                            🚩 [关卡 12] AppLocker 与 LOLBIN 绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[285]) ? $ACTIVE[285] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l13_net_evasion.php">
                            🚩 [关卡 13] 网络过滤·DNS隧道·域前置
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[286]) ? $ACTIVE[286] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l14_cred_attack.php">
                            🚩 [关卡 14] 凭据攻击·Token操纵·Kerberos
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[287]) ? $ACTIVE[287] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l15_mssql.php">
                            🚩 [关卡 15] AD 环境下 MSSQL 深度利用
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[288]) ? $ACTIVE[288] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l16_kiosk_escape.php">
                            🚩 [关卡 16] Kiosk 受限桌面逃逸技术
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[289]) ? $ACTIVE[289] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l17_linux_postex.php">
                            🚩 [关卡 17] Linux 后渗透·共享库·DevOps
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[299]) ? $ACTIVE[299] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l18_ad_deep.php">
                            🚩 [关卡 18] AD 深度·ACL·委派·跨林
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['oswe']) ? $CAT_CLASS['oswe'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-oswe">
                    <span class="menu-text" style="font-weight: bold;"> 🔍 OSWE 高级代码审计 </span>
                    <span class="sidebar-cat-badge badge-offsec">14 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
<li class="<?php echo isset($ACTIVE[261]) ? $ACTIVE[261] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_hub.php">
                            📌 概览与夺旗大厅 (3450 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[262]) ? $ACTIVE[262] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l1_whitebox.php">
                            🚩 [关卡 1] 白盒代码审计方法论
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[263]) ? $ACTIVE[263] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l2_auth_bypass.php">
                            🚩 [关卡 2] 认证绕过与逻辑漏洞链
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[264]) ? $ACTIVE[264] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l3_sqli_auth.php">
                            🚩 [关卡 3] SQL 注入认证绕过与 RCE
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[265]) ? $ACTIVE[265] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l4_deser.php">
                            🚩 [关卡 4] PHP / Java 反序列化 POP 链
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[266]) ? $ACTIVE[266] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l5_ssti.php">
                            🚩 [关卡 5] SSTI 服务端模板注入 RCE
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[267]) ? $ACTIVE[267] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l6_xxe_oob.php">
                            🚩 [关卡 6] XXE 盲注与 SSRF 带外提取
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[268]) ? $ACTIVE[268] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l7_rce_chain.php">
                            🚩 [关卡 7] 多漏洞组合深度 RCE 利用链
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[290]) ? $ACTIVE[290] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l8_sqli_blind.php">
                            🚩 [关卡 8] 盲注自动化 Python 脚本
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[291]) ? $ACTIVE[291] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l9_type_juggling.php">
                            🚩 [关卡 9] PHP 类型混淆与 0e 哈希
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[292]) ? $ACTIVE[292] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l10_java_rce.php">
                            🚩 [关卡 10] Java 反序列化·JDWP·UDF
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[293]) ? $ACTIVE[293] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l11_proto_pollution.php">
                            🚩 [关卡 11] JavaScript 原型链污染
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[294]) ? $ACTIVE[294] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l12_dotnet_deser.php">
                            🚩 [关卡 12] .NET ViewState 反序列化
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[295]) ? $ACTIVE[295] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l13_ssrf_rce.php">
                            🚩 [关卡 13] SSRF → 微服务内网 RCE 链
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[296]) ? $ACTIVE[296] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l14_csrf_cors.php">
                            🚩 [关卡 14] CSRF + CORS 认证绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo !empty($CAT_CLASS['osed']) ? $CAT_CLASS['osed'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-osed">
                    <span class="menu-text" style="font-weight: bold;"> 🔬 OSED 二进制漏洞利用 </span>
                    <span class="sidebar-cat-badge badge-offsec">10 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
<li class="<?php echo isset($ACTIVE[271]) ? $ACTIVE[271] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_hub.php">
                            📌 概览与夺旗大厅 (2850 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[272]) ? $ACTIVE[272] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l1_fuzzing.php">
                            🚩 [关卡 1] 模糊测试 Fuzzing 与崩溃分析
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[273]) ? $ACTIVE[273] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l2_seh.php">
                            🚩 [关卡 2] SEH 异常处理覆盖机制
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[274]) ? $ACTIVE[274] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l3_dep_bypass.php">
                            🚩 [关卡 3] DEP / NX 防御与 ROP 链原理
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[275]) ? $ACTIVE[275] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l4_aslr.php">
                            🚩 [关卡 4] ASLR 随机化与信息泄露利用
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[276]) ? $ACTIVE[276] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l5_egghunter.php">
                            🚩 [关卡 5] Egghunter 技术与内存检索
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[277]) ? $ACTIVE[277] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l6_rop.php">
                            🚩 [关卡 6] ROP 链精炼与 CFG/CET 防御
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[278]) ? $ACTIVE[278] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l7_asm_shellcode.php">
                            🚩 [关卡 7] x86 汇编与 NULL-Free Shellcode
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[279]) ? $ACTIVE[279] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l8_format_string.php">
                            🚩 [关卡 8] 格式化字符串漏洞读写原语
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[301]) ? $ACTIVE[301] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l9_proto_reverse.php">
                            🚩 [关卡 9] 复杂应用协议逆向与漏洞挖掘
                        </a>
                        <b class="arrow"></b>
                    </li>
<li class="<?php echo isset($ACTIVE[302]) ? $ACTIVE[302] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l10_wpm_bypass.php">
                            🚩 [关卡 10] WPM DEP+ASLR 联合绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

        
            <li class="<?php echo !empty($CAT_CLASS['tscs']) ? $CAT_CLASS['tscs'] : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-tscs">
                    <span class="menu-text" style="font-weight: bold;"> 🏗️ CorpHub 集团实战靶场 </span>
                    <span class="sidebar-cat-badge badge-corp">7 关卡</span>
                    <b class="arrow fa fa-angle-right"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo pika_is_active('vul/tscs/index.php', 351); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/index.php">
                            🌟 CorpHub 实战靶场全景大盘
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/config_leak.php', 352); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/config_leak.php">
                            🔑 [关卡 1] 系统全局配置与 RSA 私钥泄露
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/weak_password.php', 353); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/weak_password.php">
                            🔓 [关卡 2] 用户名枚举与初始口令批量接管
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/user_bola.php', 354); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/user_bola.php">
                            👤 [关卡 3] 员工档案横查与密码哈希泄露
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/sso_ticket_forge.php', 355); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/sso_ticket_forge.php">
                            ⚡ [关卡 4] SSO 单点登录票据伪造免密接管
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/minio_traversal.php', 356); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/minio_traversal.php">
                            📁 [关卡 5] 对象存储网关路径点段穿越
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/export_bola.php', 357); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/export_bola.php">
                            📊 [关卡 6] 业务大表越权导出与慢查询 DoS
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo pika_is_active('vul/tscs/quartz_job.php', 358); ?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/tscs/quartz_job.php">
                            💻 [关卡 7] 后台定时任务白名单组件调用
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

        </ul><!-- /.nav-list -->

        <div id="pika-search-empty" class="sidebar-search-empty" style="display: none;">
            <i class="ph-bold ph-magnifying-glass"></i>
            <div>未找到匹配关卡</div>
            <div style="font-size: 11px; margin-top: 4px; opacity: 0.7;">可尝试搜索 SQL, RCE, JWT, AD, OSEP 等</div>
        </div>

        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
            <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
    </div>













