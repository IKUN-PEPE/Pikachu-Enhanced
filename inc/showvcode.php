<?php 
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.lazy_write', '1');
    session_start();
}
include_once __DIR__ . '/function.php';

// 生成 4 位清晰防混淆验证码
$charset = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ";
$code = "";
for ($i = 0; $i < 4; $i++) {
    $code .= $charset[rand(0, strlen($charset) - 1)];
}

$_SESSION['vcode'] = $code;
// 验证码绕过教学点：服务端在 Cookie 中泄露了验证码字符串
@setcookie('bf[vcode]', $code, time() + 3600, '/');

// 优先尝试 GD 库 PNG 渲染
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
        imagestring($img_handle, 5, 22, 7, $code, $txt_color);
        if (ob_get_level() > 0) {
            @ob_clean();
        }
        header("Content-type: image/png");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        imagepng($img_handle);
        imagedestroy($img_handle);
        exit;
    }
}

// 若宿主机无 GD 扩展，自适应降级为原生 SVG 矢量图片（100% 任何平台无依赖显示）
if (ob_get_level() > 0) {
    @ob_clean();
}
header("Content-type: image/svg+xml; charset=utf-8");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
echo render_svg_captcha($code);
exit;
