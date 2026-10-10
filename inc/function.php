<?php 
//验证码
function vcode($width=120,$height=40,$fontSize=30,$countElement=5,$countPixel=100,$countLine=4){
	header('Content-type:image/jpeg');
	$element=array('a','b','c','d','e','f','g','h','i','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');
	$string='';
	for ($i=0;$i<$countElement;$i++){
		$string.=$element[rand(0,count($element)-1)];
	}
	$img=imagecreatetruecolor($width, $height);
	$colorBg=imagecolorallocate($img,rand(200,255),rand(200,255),rand(200,255));
	$colorBorder=imagecolorallocate($img,rand(200,255),rand(200,255),rand(200,255));
	$colorString=imagecolorallocate($img,rand(10,100),rand(10,100),rand(10,100));
	imagefill($img,0,0,$colorBg);
	for($i=0;$i<$countPixel;$i++){
		imagesetpixel($img,rand(0,$width-1),rand(0,$height-1),imagecolorallocate($img,rand(100,200),rand(100,200),rand(100,200)));
	}
	for($i=0;$i<$countLine;$i++){
		imageline($img,rand(0,$width/2),rand(0,$height),rand($width/2,$width),rand(0,$height),imagecolorallocate($img,rand(100,200),rand(100,200),rand(100,200)));
	}
	//imagestring($img,5,0,0,'abcd',$colorString);
	imagettftext($img,$fontSize,rand(-5,5),rand(5,15),rand(30,35),$colorString,'../assets/fonts/ManyGifts.ttf',$string);
	imagejpeg($img);
	imagedestroy($img);
	return $string;
}

// 验证码生成函数（支持 GD 库自动降级至原生 SVG 矢量图片，100% 杜绝缺失扩展报错）
function vcodex(){
    $charset = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ";
    $str = "";
    for($i = 0; $i < 4; $i++){
        $pos = rand(0, strlen($charset) - 1);
        $str .= $charset[$pos];
    }

    if (extension_loaded('gd') && function_exists('imagecreate')) {
        $width = 90;
        $height = 30;
        $img_handle = @imagecreate($width, $height);
        if ($img_handle) {
            $back_color = imagecolorallocate($img_handle, 245, 247, 250);
            $txt_color = imagecolorallocate($img_handle, 30, 41, 59);
            for ($i = 0; $i < 3; $i++) {
                $line = imagecolorallocate($img_handle, rand(160, 220), rand(160, 220), rand(160, 220));
                imageline($img_handle, rand(0, 20), rand(0, $height), rand($width - 30, $width), rand(0, $height), $line);
            }
            for ($i = 0; $i < 40; $i++) {
                $randcolor = imagecolorallocate($img_handle, rand(120, 200), rand(120, 200), rand(120, 200));
                imagesetpixel($img_handle, rand(0, $width), rand(0, $height), $randcolor);
            }
            imagestring($img_handle, 5, 22, 7, $str, $txt_color);
            if (ob_get_level() > 0) {
                @ob_clean();
            }
            header("Content-type: image/png");
            header("Cache-Control: no-cache, no-store, must-revalidate");
            imagepng($img_handle);
            imagedestroy($img_handle);
            return $str;
        }
    }

    // GD 未加载时回退到纯原生 SVG 渲染（任何平台 100% 可用）
    if (ob_get_level() > 0) {
        @ob_clean();
    }
    header("Content-type: image/svg+xml; charset=utf-8");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    echo render_svg_captcha($str);
    return $str;
}

function render_svg_captcha($code) {
    $width = 96;
    $height = 32;
    $chars = str_split($code);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">';
    $svg .= '<rect width="100%" height="100%" fill="#f1f5f9" rx="6" stroke="#cbd5e1" stroke-width="1"/>';
    for ($i = 0; $i < 3; $i++) {
        $x1 = rand(0, $width); $y1 = rand(0, $height);
        $x2 = rand(0, $width); $y2 = rand(0, $height);
        $c = sprintf('#%02x%02x%02x', rand(160, 210), rand(160, 210), rand(160, 210));
        $svg .= '<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="'.$c.'" stroke-width="1.5"/>';
    }
    for ($i = 0; $i < 20; $i++) {
        $cx = rand(5, $width - 5); $cy = rand(5, $height - 5);
        $c = sprintf('#%02x%02x%02x', rand(140, 200), rand(140, 200), rand(140, 200));
        $svg .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="1.5" fill="'.$c.'"/>';
    }
    $step = ($width - 16) / max(1, count($chars));
    $colors = ['#1e293b', '#2563eb', '#0f766e', '#7c3aed', '#b91c1c'];
    foreach ($chars as $idx => $char) {
        $x = 10 + $idx * $step;
        $y = rand(22, 25);
        $rot = rand(-10, 10);
        $fill = $colors[$idx % count($colors)];
        $svg .= '<text x="'.$x.'" y="'.$y.'" font-family="Consolas, Monaco, monospace" font-size="20" font-weight="bold" fill="'.$fill.'" transform="rotate('.$rot.' '.$x.' '.$y.')">'.$char.'</text>';
    }
    $svg .= '</svg>';
    return $svg;
}


//生成一个token,以当前微妙时间+一个5位的前缀
function set_token(){
    if(isset($_SESSION['token'])){
       unset($_SESSION['token']);
    }
    $_SESSION['token']=str_replace('.','',uniqid(mt_rand(10000,99999),true));
}




//跳转页面
function skip($notice,$url){
$html=<<<A
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="2;URL={$url}" />
<title>notice</title>
<link rel="stylesheet" type="text/css" href="../../../style/header.css"/>
</head>
<body>
<p id='op_notice'>{$notice} | <a href='{$url}'>点击快速返回</a></p>
</body>
</html>
A;
echo $html;
exit();
}

//在访问一个页面时，先验证是否登录,csrf里面，使用的是session验证
function check_csrf_login($link){
    if(isset($_SESSION['csrf']['username']) && isset($_SESSION['csrf']['password'])){
        $query="select * from member where username='{$_SESSION['csrf']['username']}' and sha1(pw)='{$_SESSION['csrf']['password']}'";
        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}


//在访问一个页面时，先验证是否登录,sqli的insert,update问题里面，使用的是session验证
function check_sqli_session($link){
    if(isset($_SESSION['sqli']['username']) && isset($_SESSION['sqli']['password'])){
        $query="select * from member where username='{$_SESSION['sqli']['username']}' and sha1(pw)='{$_SESSION['sqli']['password']}'";
        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}


//在访问一个页面时，先验证是否登录,sqli里面，使用的是cookie验证
function check_sqli_login($link){
    if(isset($_COOKIE['ant']['uname']) && isset($_COOKIE['ant']['pw'])){
        //这里如果不对获取的cookie进行转义，则会存在SQL注入漏洞，也会导致验证被绕过
        //$username=escape($link, $_COOKIE['ant']['username']);
        //$password=escape($link, $_COOKIE['ant']['password']);
        $username=$_COOKIE['ant']['uname'];
        $password=$_COOKIE['ant']['pw'];


        $query="select * from users where username='$username' and sha1(password)='$password'";

        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            $data=mysqli_fetch_assoc($result);
            return $data['id'];
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/*xss里面的logincheck*/
function check_xss_login($link){
    if(isset($_COOKIE['ant']['uname']) && isset($_COOKIE['ant']['pw'])){
        //这里如果不对获取的cookie进行转义，则会存在SQL注入漏洞，也会导致验证被绕过
        $username=escape($link, $_COOKIE['ant']['uname']);
        $password=escape($link, $_COOKIE['ant']['pw']);
//         $username=$_COOKIE['ant']['uname'];
//         $password=$_COOKIE['ant']['pw'];
        $query="select * from users where username='$username' and sha1(password)='$password'";
        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            $data=mysqli_fetch_assoc($result);
            return $data['id'];
        }else{
            return false;
        }
    }else{
        return false;
    }
}
/*op1的check login*/
function check_op_login($link){
    if(isset($_SESSION['op']['username']) && isset($_SESSION['op']['password'])){
        $query="select * from member where username='{$_SESSION['op']['username']}' and sha1(pw)='{$_SESSION['op']['password']}'";
        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/*op2的check login*/
function check_op2_login($link){
    if(isset($_SESSION['op2']['username']) && isset($_SESSION['op2']['password'])){
        $query="select * from users where username='{$_SESSION['op2']['username']}' and sha1(password)='{$_SESSION['op2']['password']}'";
        $result=execute($link,$query);
        if(mysqli_num_rows($result)==1){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}
function jwt_get_secret(){
    return 'pikachu-jwt-secret';
}

function jwt_base64url_encode($data){
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwt_base64url_decode($data){
    $remainder = strlen($data) % 4;
    if($remainder){
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_create_token($payload, $secret=null){
    if($secret===null){
        $secret=jwt_get_secret();
    }
    $header=array('typ'=>'JWT','alg'=>'HS256');
    $header_encoded=jwt_base64url_encode(json_encode($header));
    $payload_encoded=jwt_base64url_encode(json_encode($payload));
    $signature=hash_hmac('sha256',$header_encoded.'.'.$payload_encoded,$secret,true);
    $signature_encoded=jwt_base64url_encode($signature);
    return $header_encoded.'.'.$payload_encoded.'.'.$signature_encoded;
}

function jwt_decode_insecure($token, $secret=null){
    if($secret===null){
        $secret=jwt_get_secret();
    }
    $parts=explode('.',$token);
    if(count($parts)!=3){
        return false;
    }

    $header=json_decode(jwt_base64url_decode($parts[0]),true);
    $payload=json_decode(jwt_base64url_decode($parts[1]),true);

    if(!is_array($header) || !is_array($payload)){
        return false;
    }

    if(isset($header['alg']) && strtolower($header['alg'])==='none'){
        return $payload;
    }

    $signature=jwt_base64url_encode(hash_hmac('sha256',$parts[0].'.'.$parts[1],$secret,true));
    if($signature!==$parts[2]){
        return false;
    }

    return $payload;
}

function jwt_logout(){
    setcookie('jwt_token','',time()-3600,'/');
}
