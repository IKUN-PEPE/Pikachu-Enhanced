<?php
/**
 * Created by runner.han
 * There is nothing new under the sun
 */

$PIKA_ROOT_DIR =  "../../";
include_once $PIKA_ROOT_DIR.'inc/config.inc.php';
include_once $PIKA_ROOT_DIR.'inc/mysql.inc.php';


$SELF_PAGE = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
if ($SELF_PAGE == "bf_server.php"){
    $ACTIVE = array_fill(0, 300, '');
    $ACTIVE[4] = 'active';
}

include_once $PIKA_ROOT_DIR.'header.php';

$link=connect();

$html="";
if(isset($_POST['submit'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';

    if (empty($username)) {
        $html .= "<div class='alert alert-warning' style='margin-top:15px;'><i class='ace-icon fa fa-exclamation-triangle'></i> 用户名不能为空</div>";
    } elseif (empty($password)) {
        $html .= "<div class='alert alert-warning' style='margin-top:15px;'><i class='ace-icon fa fa-exclamation-triangle'></i> 密码不能为空</div>";
    } elseif (empty($vcode)) {
        $html .= "<div class='alert alert-warning' style='margin-top:15px;'><i class='ace-icon fa fa-exclamation-triangle'></i> 验证码不能为空哦！</div>";
    } else {
        $sess_vcode = isset($_SESSION['vcode']) ? trim($_SESSION['vcode']) : '';
        // 验证验证码是否正确（不区分大小写，自动去空格）
        if (empty($sess_vcode) || strtolower($vcode) !== strtolower($sess_vcode)) {
            $html .= "<div class='alert alert-danger' style='margin-top:15px;'><i class='ace-icon fa fa-times-circle'></i> 验证码输入错误哦！</div>";
        } else {
            // 漏洞机制：验证通过后未销毁 $_SESSION['vcode']，使得验证码可被重复利用！
            $sql = "select * from users where username=? and password=md5(?)";
            $line_pre = $link->prepare($sql);
            $line_pre->bind_param('ss', $username, $password);

            if($line_pre->execute()){
                $line_pre->store_result();
                if($line_pre->num_rows() == 1){
                    $html .= "<div class='alert alert-success' style='margin-top:15px;'><i class='ace-icon fa fa-check-circle'></i> <strong>登录成功！</strong> login success</div>";
                } else {
                    $html .= "<div class='alert alert-danger' style='margin-top:15px;'><i class='ace-icon fa fa-times-circle'></i> 用户名或密码不存在 (username or password is not exists～)</div>";
                }
            } else {
                $html .= "<div class='alert alert-danger' style='margin-top:15px;'><i class='ace-icon fa fa-exclamation-circle'></i> 执行错误: ".$line_pre->errno." 错误信息: ".htmlspecialchars($line_pre->error)."</div>";
            }
        }
    }
}
?>

<div class="main-content" xmlns="http://www.w3.org/1999/html">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="burteforce.php">暴力破解</a>
                </li>
                <li class="active">验证码绕过(on server)</li>
            </ul>

            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="漏洞点：服务端在校验验证码后没有将其从 Session 中销毁，导致同一个验证码可以被无限次复用。常用测试账号：admin / 123456、pikachu / 000000。">
                <i class="ace-icon fa fa-lightbulb-o yellow"></i> 点一下提示~
            </a>
        </div>

        <div class="page-content">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                    <div class="widget-box widget-color-blue2" style="margin-top:20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <div class="widget-header">
                            <h4 class="widget-title lighter smaller">
                                <i class="ace-icon fa fa-shield"></i>
                                服务端验证码复用测试 (Server-Side Captcha Bypass)
                            </h4>
                        </div>

                        <div class="widget-body">
                            <div class="widget-main" style="padding: 24px;">
                                <div class="alert alert-info" style="font-size: 13px; margin-bottom: 20px; line-height: 1.6;">
                                    <i class="ace-icon fa fa-info-circle"></i> <strong>实验说明：</strong><br>
                                    正常业务中，验证码完成一次比对后应立即销毁。本场景中服务端验证通过后未清理 Session，使验证码可无限次重放爆破。<br>
                                    <small style="color: #4b6482;">默认测试账户：<code>admin / 123456</code> | <code>pikachu / 000000</code></small>
                                </div>

                                <form method="post" action="bf_server.php" class="form-horizontal">
                                    <div class="form-group" style="margin-bottom: 16px;">
                                        <div class="input-group col-xs-12">
                                            <span class="input-group-addon"><i class="ace-icon fa fa-user" style="width:16px;"></i></span>
                                            <input type="text" name="username" class="form-control" placeholder="用户名 (Username)" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required />
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 16px;">
                                        <div class="input-group col-xs-12">
                                            <span class="input-group-addon"><i class="ace-icon fa fa-lock" style="width:16px;"></i></span>
                                            <input type="password" name="password" class="form-control" placeholder="密码 (Password)" required />
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 16px;">
                                        <div class="row" style="margin: 0;">
                                            <div class="col-xs-6" style="padding-left: 0; padding-right: 8px;">
                                                <div class="input-group col-xs-12">
                                                    <span class="input-group-addon"><i class="ace-icon fa fa-check-square-o" style="width:16px;"></i></span>
                                                    <input type="text" name="vcode" class="form-control" placeholder="验证码" autocomplete="off" required />
                                                </div>
                                            </div>
                                            <div class="col-xs-6" style="padding-left: 8px; padding-right: 0; display: flex; align-items: center; gap: 8px;">
                                                <img id="vcode-img" src="../../inc/showvcode.php" style="border: 1px solid #d5e0eb; border-radius: 4px; height: 34px; cursor: pointer; background: #fff;" onclick="this.src='../../inc/showvcode.php?t='+new Date().getTime();" title="点击刷新验证码" alt="验证码" />
                                                <a href="javascript:void(0);" onclick="document.getElementById('vcode-img').src='../../inc/showvcode.php?t='+new Date().getTime();" style="font-size: 12px; color: #438eb9; text-decoration: none;" title="换一张">
                                                    <i class="ace-icon fa fa-refresh"></i> 换一张
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top: 24px;">
                                        <button type="submit" name="submit" value="Login" class="btn btn-primary btn-block" style="border-radius: 4px; font-weight: 600; padding: 8px 16px;">
                                            <i class="ace-icon fa fa-sign-in"></i> 立即登录 (Login)
                                        </button>
                                    </div>
                                </form>

                                <?php echo $html; ?>
                            </div><!-- /.widget-main -->
                        </div><!-- /.widget-body -->
                    </div><!-- /.widget-box -->
                </div>
            </div>



</div><!-- /.page-content -->
</div>
</div><!-- /.main-content -->



<?php
include_once $PIKA_ROOT_DIR.'footer.php';
?>
