<?php
///**
// * Created by runner.han
// * There is nothing new under the sun
// */


ob_start();

$PIKA_ROOT_DIR = isset($PIKA_ROOT_DIR) ? $PIKA_ROOT_DIR : '';
$ACTIVE = array_pad((array)@$ACTIVE, 200, '');


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

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/bootstrap.min.css" / >
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/font-awesome/4.5.0/css/font-awesome.min.css" />

    <!-- page specific plugin styles -->

    <!-- text fonts -->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/fonts.googleapis.com.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-skins.min.css" />
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/ace-rtl.min.css" />
    <link rel="stylesheet" href="<?php echo $PIKA_ROOT_DIR;?>assets/css/pika_unified.css" />

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
<div id="navbar" class="navbar navbar-default          ace-save-state">
    <div class="navbar-container ace-save-state" id="navbar-container">

        <div class="navbar-header pull-left">
            <a href="<?php echo $PIKA_ROOT_DIR;?>index.php" class="navbar-brand">
                <small>
                    <i class="fa fa-key"></i>
                    Pikachu 漏洞练习平台 pika~pika~
                </small>
            </a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('theme-toggle-btn');
                var icon = document.getElementById('theme-icon');
                
                function updateIcon(theme) {
                    if (theme === 'dark') {
                        icon.className = 'fa fa-sun-o'; // Show sun to switch to light
                    } else {
                        icon.className = 'fa fa-moon-o'; // Show moon to switch to dark
                    }
                }
                
                var current = document.documentElement.getAttribute('data-theme') || 'dark';
                updateIcon(current);
                
                btn.addEventListener('click', function() {
                    var currentTheme = document.documentElement.getAttribute('data-theme');
                    var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('pika_theme', newTheme);
                    updateIcon(newTheme);
                });
            });
        </script>

        <div class="navbar-buttons navbar-header pull-right" role="navigation" style="display: flex; align-items: center;">
            <button id="theme-toggle-btn" class="theme-toggle" title="切换黑夜/白天模式" style="height: 45px; border: none; background: transparent; color: var(--nav-text-muted); font-size: 18px; padding: 0 15px;">
                <i class="fa fa-sun-o" id="theme-icon"></i>
            </button>
            <ul class="nav ace-nav">

                <li class="light-blue dropdown-modal">
                    <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                        <img class="nav-user-photo" src="<?php echo $PIKA_ROOT_DIR;?>assets/images/avatars/pikachu1.png" alt="Jason's Photo" />
                        <span class="user-info">
									<small>欢迎</small>
									骚年
                        </span>
                    </a>

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
        <script type="text/javascript">
            try{ace.settings.loadState('sidebar')}catch(e){}
        </script>

        <ul class="nav nav-list">
                        <li class="<?php echo $ACTIVE[0];?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>index.php">
                    
                    <span class="menu-text" style="font-weight: bold;"> 📖 系统介绍与说明 </span>
                </a>
                <b class="arrow"></b>
            </li>

            <li class="<?php echo isset($ACTIVE[219]) ? $ACTIVE[219] : '';?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>intro.php">
                    
                    <span class="menu-text" style="font-weight: bold;"> 📊 全局漏洞图鉴 (v2.0) </span>
                </a>
                <b class="arrow"></b>
            </li>


            <?php
            $is_classic_active = false;
            foreach (array_merge(range(1, 139), array(208, 209)) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_classic_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_classic_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-classic">
                    <span class="menu-text" style="font-weight: bold;"> 🏛️ 经典 Web 攻防演练 </span><b class="arrow fa fa-angle-down" style="color: #d97706;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

            <li class="<?php echo $ACTIVE[1];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
								暴力破解
							</span>

                    <b class="arrow fa fa-angle-down"></b>
                </a>

                <b class="arrow"></b>

                <ul class="submenu">
                    <li class="<?php echo $ACTIVE[2];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/burteforce.php">
                            概述
                        </a>

                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo $ACTIVE[3];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_form.php">
                            基于表单的暴力破解
                        </a>

                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[4];?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_server.php">
                            验证码绕过(on server)
                        </a>

                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[5];?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_client.php">
                            验证码绕过(on client)
                        </a>

                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[6];?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/burteforce/bf_token.php">
                            token防爆破?
                        </a>

                        <b class="arrow"></b>
                    </li>


<!--                    <li class="--><?php //echo $ACTIVE[7];?><!--">-->
<!--                        <a href="#" class="dropdown-toggle">-->
<!---->
<!---->
<!--                            test-->
<!--                            <b class="arrow fa fa-angle-down"></b>-->
<!--                        </a>-->
<!---->
<!--                        <b class="arrow"></b>-->
<!---->
<!--                        <ul class="submenu">-->
<!--                            <li class="--><?php //echo $ACTIVE[7];?><!--">-->
<!--                                <a href="top-menu.html">-->
<!---->
<!--                                    test sun 01-->
<!--                                </a>-->
<!---->
<!--                                <b class="arrow"></b>-->
<!--                            </li>-->
<!---->
<!--                        </ul>-->
<!--                    </li>-->




                </ul>
            </li>


            <li class="<?php echo $ACTIVE[7];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
								Cross-Site Scripting
							</span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[8];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[9];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_reflected_get.php">
                            反射型xss(get)
                        </a>
                        <b class="arrow"></b>
                    </li>



                    <li class="<?php echo $ACTIVE[10];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xsspost/post_login.php">
                            反射型xss(post)
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[11];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_stored.php">
                            存储型xss
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[12];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_dom.php">
                            DOM型xss
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[21]) ? $ACTIVE[21] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_dom_x.php">DOM型xss-x</a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[13];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xssblind/xss_blind.php">
                            xss之盲打
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[14];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_01.php">
                            xss之过滤
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[15];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_02.php">
                            xss之htmlspecialchars
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[16];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_03.php">
                            xss之href输出
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[17];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xss/xss_04.php">
                            xss之js输出
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo $ACTIVE[25];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
								CSRF
							</span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[26];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrf.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[27];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrfget/csrf_get_login.php">
                            CSRF(get)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[28];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrfpost/csrf_post_login.php">
                            CSRF(post)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[29];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/csrf/csrftoken/token_get_login.php">
                            CSRF Token
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>


            <li class="<?php echo $ACTIVE[35];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
								SQL-Inject
							</span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[36];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[37];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_id.php">
                            数字型注入(post)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[38];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_str.php">
                            字符型注入(get)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[39];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_search.php">
                            搜索型注入
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[40];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_x.php">
                            xx型注入
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[41];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_iu/sqli_login.php">
                            "insert/update"注入
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[42];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_del.php">
                            "delete"注入
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[43];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_header/sqli_header_login.php">
                            "http header"注入
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[44];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_blind_b.php">
                            盲注(base on boolian)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[45];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_blind_t.php">
                            盲注(base on time)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[46];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sqli/sqli_widebyte.php">
                            宽字节注入
                        </a>
                        <b class="arrow"></b>
                    </li>
            </ul>
        </li>



        <li class="<?php echo $ACTIVE[50];?>">
            <a href="#" class="dropdown-toggle">
                
                <span class="menu-text">
                        RCE
                    </span>
                <b class="arrow fa fa-angle-down"></b>
            </a>
            <b class="arrow"></b>
            <ul class="submenu">

                <li class="<?php echo $ACTIVE[51];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce.php">
                        概述
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?php echo $ACTIVE[52];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_ping.php">
                        exec "ping"
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?php echo $ACTIVE[53];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/rce/rce_eval.php">
                        exec "evel"
                    </a>
                    <b class="arrow"></b>
                </li>

            </ul>
        </li>

        <li class="<?php echo $ACTIVE[55];?>">
            <a href="#" class="dropdown-toggle">
                
                <span class="menu-text">
                    File Inclusion
                </span>
                <b class="arrow fa fa-angle-down"></b>
            </a>
            <b class="arrow"></b>
            <ul class="submenu">

                <li class="<?php echo $ACTIVE[56];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fileinclude.php">
                        概述
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?php echo $ACTIVE[57];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fi_local.php">
                        File Inclusion(local)
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?php echo $ACTIVE[58];?>" >
                    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/fileinclude/fi_remote.php"">
                        File Inclusion(remote)
                    </a>
                    <b class="arrow"></b>
                </li>

            </ul>
        </li>


        <li class="<?php echo $ACTIVE[60];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Unsafe Filedownload
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[61];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafedownload/unsafedownload.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[62];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafedownload/down_nba.php">
                            Unsafe Filedownload
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo $ACTIVE[65];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Unsafe Fileupload
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[66];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/upload.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[67];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/clientcheck.php">
                            client check
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[68];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/servercheck.php">
                            MIME type
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[69];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/getimagesize.php">
                            getimagesize
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[208]) ? $ACTIVE[208] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unsafeupload/zip_slip.php">
                            Zip Slip (解压目录穿越)
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo $ACTIVE[73];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Over Permission
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[74];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[75];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op1/op1_login.php">
                            水平越权
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[76];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/op2/op2_login.php">
                            垂直越权
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[77];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/modern/op_bola.php">
                            API IDOR (BOLA)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[78];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/modern/op_mass_assign.php">
                            Mass Assignment (批量赋值)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[79];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/overpermission/modern/op_jwt.php">
                            JWT 伪造 (身份越权)
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>


            <li class="<?php echo $ACTIVE[80];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                ../../
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[81];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dir/dir.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[82];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dir/dir_list.php">
                            目录遍历
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>


            <li class="<?php echo $ACTIVE[85];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                敏感信息泄露
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[86];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/infoleak/infoleak.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>


                    <li class="<?php echo $ACTIVE[87];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/infoleak/findabc.php">
                            IcanseeyourABC
                        </a>
                        <b class="arrow"></b>
                    </li>


                </ul>
            </li>

            <li class="<?php echo $ACTIVE[90];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                        PHP反序列化
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[91];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unserilization/unserilization.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[92];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/unserilization/unser.php">
                            PHP反序列化漏洞
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[220]) ? $ACTIVE[220] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/java_unserialize/java_unserialize.php">
                            Java原生反序列化 (readObject)
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>


            <li class="<?php echo $ACTIVE[95];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                XXE
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[96];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xxe/xxe.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[97];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/xxe/xxe_1.php">
                            XXE漏洞
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo $ACTIVE[100];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                URL重定向
            </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[101];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/urlredirect/unsafere.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[102];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/urlredirect/urlredirect.php">
                            不安全的URL跳转
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo $ACTIVE[105];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                SSRF
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[106];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[107];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_curl.php">
                            SSRF(curl)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo $ACTIVE[108];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_fgc.php">
                            SSRF(file_get_content)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[109]) ? $ACTIVE[109] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_cloud.php">
                            SSRF(Cloud Metadata)
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[209]) ? $ACTIVE[209] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ssrf/ssrf_gopher_redis.php">
                            SSRF (Gopher 打击 Redis)
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>




            <li class="<?php echo $ACTIVE[120];?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                管理工具
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo $ACTIVE[121];?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>pkxss/index.php">
                            XSS后台
                        </a>
                        <b class="arrow"></b>
                    </li>



                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[125]) ? $ACTIVE[125] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Host Header
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo isset($ACTIVE[126]) ? $ACTIVE[126] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/hostheader/hostheader.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[127]) ? $ACTIVE[127] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/hostheader/trust.php">
                            Host Header Trust
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[128]) ? $ACTIVE[128] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Session Fixation
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo isset($ACTIVE[129]) ? $ACTIVE[129] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sessionfixation/sessionfixation.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[130]) ? $ACTIVE[130] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sessionfixation/fixation_login.php">
                            漏洞登录页
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[131]) ? $ACTIVE[131] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sessionfixation/fixation_profile.php">
                            登录后信息页
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[132]) ? $ACTIVE[132] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                CORS Misconfiguration
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo isset($ACTIVE[133]) ? $ACTIVE[133] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[134]) ? $ACTIVE[134] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors_reflect.php">
                            Origin反射
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[135]) ? $ACTIVE[135] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cors/cors_credential.php">
                            Allow-Credentials
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[136]) ? $ACTIVE[136] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Clickjacking
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo isset($ACTIVE[137]) ? $ACTIVE[137] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/clickjacking/clickjacking.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[138]) ? $ACTIVE[138] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/clickjacking/target.php">
                            目标页面
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[139]) ? $ACTIVE[139] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/clickjacking/attacker.php">
                            恶意诱导页
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>


                </ul>
            </li>

            <?php
            $is_cloud_active = false;
            foreach (array_merge(range(140, 164), array(124, 207, 216, 217)) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_cloud_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_cloud_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-cloud">
                    <span class="menu-text" style="font-weight: bold;"> ☁️ 云原生与微服务架构 </span><b class="arrow fa fa-angle-down" style="color: #0891b2;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

            <li class="<?php echo isset($ACTIVE[140]) ? $ACTIVE[140] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Docker Lab
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    <li class="<?php echo isset($ACTIVE[141]) ? $ACTIVE[141] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[142]) ? $ACTIVE[142] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab_check.php">
                            环境检查
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[143]) ? $ACTIVE[143] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/dockerlab_center.php">
                            模板列表
                        </a>
                        <b class="arrow"></b>
                    </li>

                    <li class="<?php echo isset($ACTIVE[207]) ? $ACTIVE[207] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/dockerlab/k8s_token_escape.php">
                            Kubernetes 越权逃逸
                        </a>
                        <b class="arrow"></b>
                    </li>

                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[144]) ? $ACTIVE[144] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                现代 API 安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[145]) ? $ACTIVE[145] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/api_security/api.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[146]) ? $ACTIVE[146] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/api_security/bola.php">
                            BOLA (越权)
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[147]) ? $ACTIVE[147] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/api_security/mass_assignment.php">
                            批量赋值
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[148]) ? $ACTIVE[148] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                业务逻辑安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[149]) ? $ACTIVE[149] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/logic/logic.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[150]) ? $ACTIVE[150] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/logic/price_tamper.php">
                            价格篡改
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[151]) ? $ACTIVE[151] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                前端前沿安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[152]) ? $ACTIVE[152] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/frontend/frontend.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[153]) ? $ACTIVE[153] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/frontend/dom_clobbering.php">
                            DOM Clobbering
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[156]) ? $ACTIVE[156] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/frontend/prototype_pollution.php">
                            Prototype Pollution
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[157]) ? $ACTIVE[157] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                现代身份认证安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[158]) ? $ACTIVE[158] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[124]) ? $ACTIVE[124] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_login.php">
                            JWT认证绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[159]) ? $ACTIVE[159] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_none.php">
                            JWT None 算法绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[216]) ? $ACTIVE[216] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_weak_secret.php">
                            JWT 弱密钥爆破
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[217]) ? $ACTIVE[217] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/jwt/jwt_key_confusion.php">
                            JWT 算法混淆 (RS-to-HS)
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[160]) ? $ACTIVE[160] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                前沿接口协议
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[161]) ? $ACTIVE[161] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/graphql/graphql.php">
                            GraphQL 漏洞
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[162]) ? $ACTIVE[162] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                新型数据库安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[163]) ? $ACTIVE[163] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/nosql/nosql.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[164]) ? $ACTIVE[164] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/nosql/mongo_bypass.php">
                            MongoDB 认证绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>


                </ul>
            </li>

            <?php
            $is_ai_active = false;
            foreach (array_merge(range(165, 182), range(202, 206)) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_ai_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_ai_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-ai">
                    <span class="menu-text" style="font-weight: bold;"> 🤖 AI 与大模型应用安全 </span><b class="arrow fa fa-angle-down" style="color: #7c3aed;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

            <li class="<?php echo isset($ACTIVE[165]) ? $ACTIVE[165] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                AI / LLM 应用安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[166]) ? $ACTIVE[166] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/ai_security.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[167]) ? $ACTIVE[167] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/prompt_injection.php">
                            Prompt 注入绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[202]) ? $ACTIVE[202] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_data_leakage.php">
                            敏感规则与提示词提取
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[203]) ? $ACTIVE[203] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_plugin_rce.php">
                            插件工具命令注入 (RCE)
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[204]) ? $ACTIVE[204] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ai_security/llm_xss.php">
                            不安全渲染 (XSS & SSRF)
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[168]) ? $ACTIVE[168] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                高级认证体系安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[169]) ? $ACTIVE[169] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oauth/oauth.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[170]) ? $ACTIVE[170] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oauth/state_bypass.php">
                            OAuth State 劫持
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[171]) ? $ACTIVE[171] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                业务并发安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[172]) ? $ACTIVE[172] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/race_condition/race_condition.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[173]) ? $ACTIVE[173] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/race_condition/gift_card.php">
                            并发竞争兑换 (Race Condition)
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[174]) ? $ACTIVE[174] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                现代 Web 缓存安全
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[175]) ? $ACTIVE[175] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/web_cache/web_cache.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[176]) ? $ACTIVE[176] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/web_cache/cache_deception.php">
                            Web 缓存欺骗 (WCD)
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[177]) ? $ACTIVE[177] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                底层协议利用
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[178]) ? $ACTIVE[178] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/websocket.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[179]) ? $ACTIVE[179] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/cswsh.php">
                            跨站 WebSocket 劫持
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[205]) ? $ACTIVE[205] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/ws_sqli.php">
                            WS 数据帧 SQL 注入
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[206]) ? $ACTIVE[206] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/websocket/ws_unauth_stream.php">
                            WS 未授权敏感流订阅
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[180]) ? $ACTIVE[180] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                高阶 PHP 反序列化
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[181]) ? $ACTIVE[181] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/phar/phar.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[182]) ? $ACTIVE[182] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/phar/phar_unserialize.php">
                            Phar 伪协议触发
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>


                </ul>
            </li>

            <?php
            $is_proto_active = false;
            foreach (array_merge(range(183, 201), range(210, 215), array(218)) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_proto_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_proto_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-proto">
                    <span class="menu-text" style="font-weight: bold;"> 🌐 前沿协议与数据安全 </span><b class="arrow fa fa-angle-down" style="color: #2563eb;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

            <li class="<?php echo isset($ACTIVE[183]) ? $ACTIVE[183] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                HTTP 请求走私
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[184]) ? $ACTIVE[184] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/http_smuggling/http_smuggling.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[185]) ? $ACTIVE[185] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/http_smuggling/cl_te.php">
                            CL.TE 走私与鉴权绕过
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[186]) ? $ACTIVE[186] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                单点登录 SSO/SAML
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[187]) ? $ACTIVE[187] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sso_saml/sso_saml.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[210]) ? $ACTIVE[210] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/sso_saml/saml_xsw.php">
                            SAML 签名包装 (XSW)
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[188]) ? $ACTIVE[188] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                对象存储 Cloud Storage
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[189]) ? $ACTIVE[189] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cloud_storage/cloud_storage.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[211]) ? $ACTIVE[211] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/cloud_storage/oss_bucket_unauth.php">
                            Bucket 越权读写与覆盖
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[190]) ? $ACTIVE[190] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Serverless 函数计算
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[191]) ? $ACTIVE[191] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/serverless/serverless.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[212]) ? $ACTIVE[212] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/serverless/lambda_env_leak.php">
                            环境变量凭证窃取
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[192]) ? $ACTIVE[192] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                微服务 gRPC 接口
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[193]) ? $ACTIVE[193] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/grpc/grpc.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[213]) ? $ACTIVE[213] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/grpc/grpc_auth_bypass.php">
                            gRPC 越权与参数篡改
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[194]) ? $ACTIVE[194] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                Webhook 异步回调
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[195]) ? $ACTIVE[195] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/webhook/webhook.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[214]) ? $ACTIVE[214] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/webhook/webhook_ssrf.php">
                            回调盲 SSRF & 内网探测
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[196]) ? $ACTIVE[196] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                敏感运维与配置泄露
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[197]) ? $ACTIVE[197] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/misconfig.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[198]) ? $ACTIVE[198] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/env_leak.php">
                            .env 数据库账密泄漏
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[199]) ? $ACTIVE[199] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/git_leak.php">
                            .git 源码仓库遍历
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[218]) ? $ACTIVE[218] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/misconfig/swagger_unauth.php">
                            Swagger UI 在线调试调试
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[200]) ? $ACTIVE[200] : '';?>">
                <a href="#" class="dropdown-toggle">
                    
                    <span class="menu-text">
                多因素认证 (MFA Bypass)
                    </span>
                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[201]) ? $ACTIVE[201] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/mfa_bypass/mfa_bypass.php">
                            概述
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[215]) ? $ACTIVE[215] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/mfa_bypass/mfa_logic_bypass.php">
                            2FA 逻辑绕过与轰炸
                        </a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>


                </ul>
            </li>

            <?php
            $is_defense_active = false;
            foreach (range(220, 229) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_defense_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_defense_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle cat-sidebar-defense">
                    <span class="menu-text" style="font-weight: bold;"> 🛡️ 蓝队防守与实战防御 </span><b class="arrow fa fa-angle-down" style="color: #059669;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[221]) ? $ACTIVE[221] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/defense/defense.php" style="color: #059669 !important; font-weight: bold;">
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
        
            <?php
            $is_ad_active = false;
            foreach (range(230, 248) as $i) {
                if (!empty($ACTIVE[$i]) && strpos($ACTIVE[$i], "active") !== false) {
                    $is_ad_active = true;
                    break;
                }
            }
            ?>
            <li class="<?php echo $is_ad_active ? 'active open' : ''; ?>">
                <a href="#" class="dropdown-toggle" style="background: linear-gradient(90deg, #ede9fe 0%, #e0e7ff 100%) !important; border-left: 4px solid #6366f1 !important; color: #3730a3 !important;">
                    <span class="menu-text" style="font-weight: bold;"> 🌐 内网与 AD 域安全 </span><b class="arrow fa fa-angle-down" style="color: #6366f1;"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[231]) ? $ACTIVE[231] : '';?>" >
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_security.php">
                            📌 概览与总纲大厅
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
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/ad_security/ad_ctf_hub.php" style="color: #4f46e5 !important; font-weight: bold;">
                            🏆 GOAD 域渗透 CTF 夺旗大厅 (2500 PTS)
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
                </ul>
            </li>

            <!-- ===== OSCE³ 三大方向 ===== -->
            <li class="<?php echo isset($ACTIVE[250]) ? $ACTIVE[250] : '';?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_hub.php">
                    <i class="menu-icon fa fa-crosshairs" style="color:#6366f1;"></i>
                    <span class="menu-text" style="font-weight:700;color:#a5b4fc;">🏅 OSEP 内网穿透 CTF</span>
                </a>
                <b class="arrow fa fa-angle-down"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[250]) ? $ACTIVE[250] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_hub.php" style="color:#6366f1!important;font-weight:bold;">
                            🎯 OSEP 大厅 (7关 · 1650 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[253]) ? $ACTIVE[253] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l1_enum.php">🚩 L1 主机侦察与信息收集</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[254]) ? $ACTIVE[254] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l2_phishing.php">🚩 L2 鱼叉攻击仿冒研究</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[253]) ? $ACTIVE[253] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l3_lateral.php">🚩 L3 横向移动技术</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[255]) ? $ACTIVE[255] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l4_pivot.php">🚩 L4 内网穿透隧道</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[256]) ? $ACTIVE[256] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l5_av_evasion.php">🚩 L5 检测架构研究</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[257]) ? $ACTIVE[257] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l6_persistence.php">🚩 L6 持久化机制</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[258]) ? $ACTIVE[258] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l7_exfil.php">🚩 L7 数据外渗分析 [终章]</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[280]) ? $ACTIVE[280] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l8_win_api.php">🚩 L8 Win32 API·WOW64·架构</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[281]) ? $ACTIVE[281] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l9_office_macro.php">🚩 L9 Office宏武器化</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[282]) ? $ACTIVE[282] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l10_process_inject.php">🚩 L10 进程注入与镂空</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[283]) ? $ACTIVE[283] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l11_amsi_bypass.php">🚩 L11 AMSI·UAC绕过</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[284]) ? $ACTIVE[284] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l12_applocker.php">🚩 L12 AppLocker绕过</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[285]) ? $ACTIVE[285] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l13_net_evasion.php">🚩 L13 网络过滤·DNS隧道</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[286]) ? $ACTIVE[286] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l14_cred_attack.php">🚩 L14 凭据攻击·令牌操纵</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[287]) ? $ACTIVE[287] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l15_mssql.php">🚩 L15 MSSQL深度利用</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[288]) ? $ACTIVE[288] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l16_kiosk_escape.php">🚩 L16 Kiosk逃逸技术</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[289]) ? $ACTIVE[289] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l17_linux_postex.php">🚩 L17 Linux后渗透·共享库劫持</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[299]) ? $ACTIVE[299] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osep/osep_l18_ad_deep.php" style="color:#f59e0b!important;font-weight:bold;">🏆 L18 AD深度·ACL·委派·跨林 [终章]</a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[260]) ? $ACTIVE[260] : '';?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_hub.php">
                    <i class="menu-icon fa fa-code" style="color:#06b6d4;"></i>
                    <span class="menu-text" style="font-weight:700;color:#67e8f9;">🏅 OSWE 白盒审计 CTF</span>
                </a>
                <b class="arrow fa fa-angle-down"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[261]) ? $ACTIVE[261] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_hub.php" style="color:#06b6d4!important;font-weight:bold;">
                            🎯 OSWE 大厅 (7关 · 1500 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[262]) ? $ACTIVE[262] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l1_whitebox.php">🚩 L1 白盒审计方法论</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[263]) ? $ACTIVE[263] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l2_auth_bypass.php">🚩 L2 认证绕过逻辑链</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[264]) ? $ACTIVE[264] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l3_sqli_auth.php">🚩 L3 SQL注入认证+提权</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[265]) ? $ACTIVE[265] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l4_deser.php">🚩 L4 反序列化 POP 链</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[266]) ? $ACTIVE[266] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l5_ssti.php">🚩 L5 SSTI 模板注入</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[267]) ? $ACTIVE[267] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l6_xxe_oob.php">🚩 L6 XXE + SSRF 带外</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[268]) ? $ACTIVE[268] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l7_rce_chain.php">🚩 L7 多漏洞 RCE 链 [终章]</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[290]) ? $ACTIVE[290] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l8_sqli_blind.php">🚩 L8 盲注自动化脚本</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[291]) ? $ACTIVE[291] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l9_type_juggling.php">🚩 L9 PHP类型混淆</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[292]) ? $ACTIVE[292] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l10_java_rce.php">🚩 L10 Java反序列化RCE</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[293]) ? $ACTIVE[293] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l11_proto_pollution.php">🚩 L11 JS原型链污染</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[294]) ? $ACTIVE[294] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l12_dotnet_deser.php">🚩 L12 .NET ViewState反序列化</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[295]) ? $ACTIVE[295] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l13_ssrf_rce.php">🚩 L13 SSRF→内网RCE链</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[296]) ? $ACTIVE[296] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/oswe/oswe_l14_csrf_cors.php">🚩 L14 CSRF+CORS绕过 [终章]</a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

            <li class="<?php echo isset($ACTIVE[270]) ? $ACTIVE[270] : '';?>">
                <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_hub.php">
                    <i class="menu-icon fa fa-microchip" style="color:#f97316;"></i>
                    <span class="menu-text" style="font-weight:700;color:#fed7aa;">🏅 OSED 漏洞开发 CTF</span>
                </a>
                <b class="arrow fa fa-angle-down"></b>
                <ul class="submenu">
                    <li class="<?php echo isset($ACTIVE[271]) ? $ACTIVE[271] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_hub.php" style="color:#f97316!important;font-weight:bold;">
                            🎯 OSED 大厅 (6关 · 1350 PTS)
                        </a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[272]) ? $ACTIVE[272] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l1_fuzzing.php">🚩 L1 Fuzzing 与崩溃分析</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[273]) ? $ACTIVE[273] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l2_seh.php">🚩 L2 SEH 异常处理覆盖</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[274]) ? $ACTIVE[274] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l3_dep_bypass.php">🚩 L3 DEP/NX + ROP 原理</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[275]) ? $ACTIVE[275] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l4_aslr.php">🚩 L4 ASLR 随机化研究</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[276]) ? $ACTIVE[276] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l5_egghunter.php">🚩 L5 Egghunter 技术原理</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[277]) ? $ACTIVE[277] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l6_rop.php">🚩 L6 ROP 与 CFG/CET [终章]</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[278]) ? $ACTIVE[278] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l7_asm_shellcode.php">🚩 L7 x86汇编·自定义Shellcode</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[279]) ? $ACTIVE[279] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l8_format_string.php">🚩 L8 格式化字符串漏洞</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[280]) ? $ACTIVE[280] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l9_proto_reverse.php">🚩 L9 协议逆向分析</a>
                        <b class="arrow"></b>
                    </li>
                    <li class="<?php echo isset($ACTIVE[281]) ? $ACTIVE[281] : '';?>">
                        <a href="<?php echo $PIKA_ROOT_DIR;?>vul/osed/osed_l10_wpm_bypass.php">🚩 L10 WPM DEP+ASLR绕过 [终章]</a>
                        <b class="arrow"></b>
                    </li>
                </ul>
            </li>

        </ul><!-- /.nav-list -->

        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
            <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
    </div>













