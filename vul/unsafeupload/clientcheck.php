<?php
/**
 * Pikachu-Enhanced Enterprise Cyber Range
 * Vulnerability Lab: Client-Side File Upload Check Bypass
 * Refactored strictly per landing-page-design & redesign-existing-projects
 */

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);

if ($SELF_PAGE == "clientcheck.php") {
    $ACTIVE = array_fill(0, 300, '');
    $ACTIVE[67] = 'active';
}

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
include_once $PIKA_ROOT_DIR . 'inc/uploadfunction.php';

$html = '';
if (isset($_POST['submit'])) {
    $save_path = 'uploads'; // 指定在当前目录建立 uploads 保存目录
    $upload = upload_client('uploadfile', $save_path); // 调用服务端无过滤保存函数
    if ($upload['return']) {
        $filePath = htmlspecialchars($upload['new_path'], ENT_QUOTES, 'UTF-8');
        $ext = strtolower(pathinfo($upload['new_path'], PATHINFO_EXTENSION));
        $is_img = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico']);

        $html .= "<div class='alert alert-success' style='margin-top:var(--spacing-300); border-radius:var(--radius-xl); border:1px solid rgba(16, 185, 129, 0.3); background:var(--bg-secondary); padding:var(--spacing-300);'>";
        $html .= "<div style='display:flex; align-items:flex-start; gap:var(--spacing-200);'>";
        $html .= "<i class='ph-bold ph-check-circle' style='font-size:26px; color:var(--success); flex-shrink:0; margin-top:2px;'></i>";
        $html .= "<div style='flex:1;'>";
        $html .= "<h4 style='margin:0 0 var(--spacing-50) 0; font-size:16px; font-weight:700; color:var(--text-primary);'>文件上传成功！(Upload Succeeded)</h4>";
        $html .= "<p style='margin:0 0 var(--spacing-200) 0; font-size:13.5px; color:var(--text-secondary); line-height:1.6;'>文件已成功写入服务端。由于服务端未做任何文件扩展名校验，若上传了可执行脚本（如 <code>.php</code>），即可直接达成远程代码执行（RCE）。</p>";
        $html .= "<div style='display:flex; align-items:center; gap:var(--spacing-100); flex-wrap:wrap; margin-bottom:var(--spacing-200);'>";
        $html .= "<span style='font-size:12px; color:var(--text-muted); font-weight:600;'>服务端保存路径：</span>";
        $html .= "<code style='background:var(--bg-card); border:1px solid var(--border-subtle); padding:4px 10px; border-radius:var(--radius-sm); font-family:monospace; color:var(--primary); font-size:13px;'>" . $filePath . "</code>";
        $html .= "</div>";
        $html .= "<div style='display:flex; gap:var(--spacing-100); flex-wrap:wrap;'>";
        $html .= "<a href='" . $filePath . "' target='_blank' class='btn-main' style='font-size:13.5px; padding:6px 14px; background:var(--primary); color:#ffffff !important; border-radius:var(--radius-md); text-decoration:none;'><i class='ph-bold ph-arrow-square-out'></i> 在新窗口访问上传文件</a>";
        $html .= "</div>";

        if ($is_img) {
            $html .= "<div style='margin-top:var(--spacing-200); padding:var(--spacing-100); background:var(--bg-card); border:1px solid var(--border-subtle); border-radius:var(--radius-md); display:inline-block;'>";
            $html .= "<img src='" . $filePath . "' style='max-width:280px; max-height:180px; border-radius:4px; display:block;' alt='Uploaded Image Preview' />";
            $html .= "</div>";
        }
        $html .= "</div></div></div>";
    } else {
        $html .= "<div class='alert alert-danger' style='margin-top:var(--spacing-300); border-radius:var(--radius-xl); border:1px solid rgba(239, 68, 68, 0.3); background:var(--bg-secondary); padding:var(--spacing-300);'>";
        $html .= "<div style='display:flex; align-items:flex-start; gap:var(--spacing-200);'>";
        $html .= "<i class='ph-bold ph-warning-circle' style='font-size:26px; color:var(--danger); flex-shrink:0; margin-top:2px;'></i>";
        $html .= "<div>";
        $html .= "<h4 style='margin:0 0 var(--spacing-50) 0; font-size:15px; font-weight:700; color:var(--text-primary);'>上传中断</h4>";
        $html .= "<p style='margin:0; font-size:13.5px; color:var(--text-secondary);'>" . htmlspecialchars($upload['error'], ENT_QUOTES, 'UTF-8') . "</p>";
        $html .= "</div></div></div>";
    }
}
?>

<style>
/* ==========================================================================
   Client-Side Check Lab Styles (Modern Flat Architecture)
   ========================================================================== */
.lab-container {
    max-width: 920px;
    margin: 0 auto;
    padding: var(--spacing-200) 0 var(--spacing-700) 0;
}

.lab-header-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-400) var(--spacing-500);
    margin-bottom: var(--spacing-400);
}

.lab-badge-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-100);
    margin-bottom: var(--spacing-100);
    flex-wrap: wrap;
}

.lab-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    line-height: 16px;
    font-weight: 700;
    color: var(--primary);
    background: var(--primary-light);
    border: 1px solid rgba(2, 132, 199, 0.25);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.lab-title {
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-100) 0;
    text-wrap: balance;
}

.lab-description {
    font-size: 14.5px;
    line-height: 22px;
    color: var(--text-secondary);
    margin: 0;
    text-wrap: pretty;
}

/* Upload Panel Card */
.upload-panel-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-400) var(--spacing-500);
    margin-bottom: var(--spacing-400);
}

.upload-dropzone {
    border: 2px dashed var(--border-strong);
    border-radius: var(--radius-xl);
    background: var(--bg-secondary);
    padding: var(--spacing-500) var(--spacing-300);
    text-align: center;
    cursor: pointer;
    transition: border-color var(--duration-color) var(--ease-base), background-color var(--duration-color) var(--ease-base);
    position: relative;
    margin-bottom: var(--spacing-300);
}
.upload-dropzone:hover, .upload-dropzone.dragover {
    border-color: var(--primary);
    background: var(--bg-tertiary);
}

.upload-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-full);
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: var(--spacing-200);
}

.upload-prompt-text {
    font-size: 15px;
    line-height: 22px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-50) 0;
}

.upload-hint-subtext {
    font-size: 13px;
    line-height: 18px;
    color: var(--text-muted);
    margin: 0;
}

.upload-hint-subtext code {
    background: var(--bg-card);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-subtle);
    font-family: 'Geist Mono', monospace;
    font-size: 12px;
}

.file-input-hidden {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* File Selected Status Box */
.selected-file-card {
    display: none;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: var(--spacing-200) var(--spacing-300);
    margin-bottom: var(--spacing-300);
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-200);
}

.file-info-col {
    display: flex;
    align-items: center;
    gap: var(--spacing-100);
    overflow: hidden;
}

.file-name-tag {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-primary);
    font-family: 'Geist Mono', monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size-tag {
    font-size: 12px;
    color: var(--text-muted);
    font-family: 'Geist Mono', monospace;
}

.file-status-pill {
    font-size: 11.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.status-pill-ok {
    background: var(--success-light);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-pill-danger {
    background: var(--danger-light);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Form Actions Row */
.form-actions-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--spacing-200);
}

.btn-upload-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    border-radius: var(--radius-lg);
    background: var(--primary);
    color: #ffffff !important;
    font-size: 15px;
    line-height: 22px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: transform var(--duration-movement) var(--ease-base), background-color var(--duration-color) var(--ease-base);
}
.btn-upload-submit:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
}
.btn-upload-submit:active {
    transform: scale(0.98);
}
.btn-upload-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.js-bypass-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: var(--radius-md);
    font-size: 12.5px;
    font-weight: 600;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}
.js-bypass-toggle-btn:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border-color: var(--border-strong);
}
.js-bypass-toggle-btn.bypassed {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.35);
}

/* Client-Side Block Banner */
#clientBlockBanner {
    display: none;
    background: var(--danger-light);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: var(--radius-lg);
    padding: var(--spacing-200);
    margin-bottom: var(--spacing-300);
    color: #ef4444;
    font-size: 13.5px;
    line-height: 20px;
}

/* Theory & Walkthrough Guide */
.lab-guide-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-400) var(--spacing-500);
}

.guide-title {
    font-size: 16px;
    line-height: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-200) 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.guide-step-item {
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: var(--spacing-200);
    margin-bottom: var(--spacing-100);
}

.guide-step-header {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.guide-step-desc {
    font-size: 13px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ph-bold ph-house" style="font-size:14px; vertical-align:middle;"></i>
                    <a href="<?php echo $PIKA_ROOT_DIR;?>index.php">主页</a>
                </li>
                <li>
                    <a href="upload.php">Unsafe File Upload</a>
                </li>
                <li class="active">客户端 Check 绕过</li>
            </ul>

            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="漏洞点：文件后缀校验代码写在前端页面的 JavaScript 函数 checkFileExt 中。一切在前端做的安全措施都是不靠谱的，可通过抓包篡改或禁用 JS 轻松突破。">
                <i class="ph-bold ph-lightbulb" style="color:var(--warning); vertical-align:middle;"></i> 点一下提示~
            </a>
        </div>

        <div class="page-content">
            <div class="lab-container">

                <!-- 1. Lab Header Overview -->
                <section class="lab-header-card">
                    <div class="lab-badge-row">
                        <span class="lab-category-badge"><i class="ph-bold ph-shield-warning"></i> CWE-602: CLIENT-SIDE SECURITY</span>
                        <span style="font-size:12px; color:var(--text-muted); font-family:'Geist Mono', monospace;">难度评级: ★★☆☆☆</span>
                    </div>
                    <h1 class="lab-title">客户端验证绕过 (Client-Side Check)</h1>
                    <p class="lab-description">
                        本关卡在表单中通过 JavaScript <code>onchange</code> 事件监听用户选择的文件，仅在客户端比对后缀名白名单。当浏览器与服务端之间的传输未设防时，攻击者可绕过该限制，将任意 WebShell 上传至服务器。
                    </p>
                </section>

                <!-- 2. Interactive Upload Panel -->
                <section class="upload-panel-card">
                    <div id="clientBlockBanner">
                        <i class="ph-bold ph-x-circle" style="font-size:18px; vertical-align:middle; margin-right:4px;"></i>
                        <strong>客户端脚本拦截：</strong>检测到选择的文件后缀非图片白名单（<code>jpg</code>, <code>png</code>, <code>gif</code>）。上传已被前端 <code>checkFileExt()</code> 函数直接拦截！
                    </div>

                    <form id="uploadForm" class="upload" method="post" enctype="multipart/form-data" action="">
                        <div class="upload-dropzone" id="dropzoneBox" onclick="document.getElementById('fileInput').click();">
                            <input class="file-input-hidden" id="fileInput" type="file" name="uploadfile" onchange="checkFileExt(this.value)" />
                            <div class="upload-icon-circle">
                                <i class="ph-bold ph-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-prompt-text" id="dropzonePrompt">点击此处或拖拽文件至此选择待上传文件</div>
                            <p class="upload-hint-subtext">仅客户端前端允许：<code>.jpg</code> · <code>.png</code> · <code>.gif</code></p>
                        </div>

                        <!-- Selected File Preview Card -->
                        <div class="selected-file-card" id="selectedFileCard">
                            <div class="file-info-col">
                                <i class="ph-bold ph-file" style="font-size:22px; color:var(--primary); flex-shrink:0;"></i>
                                <div>
                                    <div class="file-name-tag" id="displayFileName">未选择文件</div>
                                    <div class="file-size-tag" id="displayFileSize">0 KB</div>
                                </div>
                            </div>
                            <div id="fileValidationStatus"></div>
                        </div>

                        <div class="form-actions-row">
                            <button type="button" id="bypassJsBtn" class="js-bypass-toggle-btn" onclick="toggleJsBypass();">
                                <i class="ph-bold ph-toggle-left" id="bypassIcon"></i>
                                <span id="bypassText">前端 JS 校验: 已开启 (正常模式)</span>
                            </button>

                            <button class="btn-upload-submit" id="submitBtn" type="submit" name="submit" value="开始上传">
                                <i class="ph-bold ph-upload-simple"></i> 开始上传 (Submit)
                            </button>
                        </div>
                    </form>

                    <!-- Server Result Feedback -->
                    <?php echo $html; ?>
                </section>

                <!-- 3. Penetration Methodology Walkthrough -->
                <section class="lab-guide-card">
                    <h3 class="guide-title"><i class="ph-bold ph-terminal-window"></i> 漏洞攻击与绕过验证思路</h3>
                    
                    <div class="guide-step-item">
                        <div class="guide-step-header">
                            <i class="ph-bold ph-number-circle-one" style="color:var(--primary);"></i>
                            方法一：Burp Suite 抓包篡改文件名（最贴合实战的攻击链）
                        </div>
                        <p class="guide-step-desc">
                            先在本地准备一个木马脚本并将后缀更名为合规的 <code>shell.jpg</code>，选择文件时顺利通过前端 <code>checkFileExt()</code> 函数校验；开启 Burp Suite 拦截，点击“开始上传”，在 HTTP POST 请求报文中将 <code>filename="shell.jpg"</code> 更改回 <code>filename="shell.php"</code> 后放行。
                        </p>
                    </div>

                    <div class="guide-step-item">
                        <div class="guide-step-header">
                            <i class="ph-bold ph-number-circle-two" style="color:var(--primary);"></i>
                            方法二：浏览器开发者工具移除 onchange 监听（DOM 级绕过）
                        </div>
                        <p class="guide-step-desc">
                            按 F12 打开浏览器检查器，选中上传输入框 <code>&lt;input type="file" ...&gt;</code>，直接在 DOM 树中双击删除 <code>onchange="checkFileExt(this.value)"</code> 属性，或在 Console 控制台输入 <code>checkFileExt = function(){ return true; }</code> 覆盖函数，即可直接选 <code>.php</code> 文件提交。
                        </p>
                    </div>

                    <div class="guide-step-item">
                        <div class="guide-step-header">
                            <i class="ph-bold ph-number-circle-three" style="color:var(--primary);"></i>
                            方法三：使用 Python / Curl 直发原始数据包（脱离浏览器）
                        </div>
                        <p class="guide-step-desc">
                            前端的所有代码校验均在浏览器运行。使用 Python <code>requests.post()</code> 直接向 <code>clientcheck.php</code> 发送带有 <code>multipart/form-data</code> 载荷的请求，完全不受任何前端脚本限制。
                        </p>
                    </div>
                </section>

            </div><!-- /.lab-container -->
        </div><!-- /.page-content -->
    </div>
</div><!-- /.main-content -->

<script type="text/javascript">
// 全局状态：是否处于绕过前端校验模式
var isBypassActive = false;

// 1. 保留原汁原味的原版函数 checkFileExt，确保教学教程与经典教材 100% 兼容
function checkFileExt(filename) {
    if (isBypassActive) {
        updateFileCard(filename, true);
        return true;
    }

    var flag = false;
    var arr = ["jpg", "png", "gif", "jpeg"];
    var index = filename.lastIndexOf(".");
    var ext = filename.substr(index + 1).toLowerCase();

    for (var i = 0; i < arr.length; i++) {
        if (ext === arr[i]) {
            flag = true;
            break;
        }
    }

    if (!flag) {
        // 展示视觉拦截横幅
        var banner = document.getElementById('clientBlockBanner');
        if (banner) banner.style.display = 'block';

        // 触发经典弹窗提示（保留经典教材体验）
        alert("上传的文件不符合要求，请重新选择！");

        // 清空当前文件选择，避免提交
        var fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';

        document.getElementById('selectedFileCard').style.display = 'none';
        return false;
    } else {
        var banner = document.getElementById('clientBlockBanner');
        if (banner) banner.style.display = 'none';
        updateFileCard(filename, false);
        return true;
    }
}

// 更新已选文件卡片展示
function updateFileCard(filename, isBypassed) {
    var card = document.getElementById('selectedFileCard');
    var nameEl = document.getElementById('displayFileName');
    var sizeEl = document.getElementById('displayFileSize');
    var statusEl = document.getElementById('fileValidationStatus');
    var fileInput = document.getElementById('fileInput');

    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        card.style.display = 'none';
        return;
    }

    var file = fileInput.files[0];
    card.style.display = 'flex';
    nameEl.textContent = file.name;
    sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

    var ext = file.name.split('.').pop().toLowerCase();
    var isWhite = ['jpg', 'png', 'gif', 'jpeg'].indexOf(ext) !== -1;

    if (isWhite) {
        statusEl.innerHTML = '<span class="file-status-pill status-pill-ok"><i class="ph-bold ph-check"></i> 前端合规 (' + ext + ')</span>';
    } else if (isBypassed) {
        statusEl.innerHTML = '<span class="file-status-pill status-pill-danger"><i class="ph-bold ph-warning"></i> 前端已绕过 (' + ext + ')</span>';
    } else {
        statusEl.innerHTML = '<span class="file-status-pill status-pill-danger"><i class="ph-bold ph-x"></i> 拦截格式 (' + ext + ')</span>';
    }
}

// 2. 提供一键前端 JS 绕过开关，方便在浏览器内直接实操演示
function toggleJsBypass() {
    isBypassActive = !isBypassActive;
    var btn = document.getElementById('bypassJsBtn');
    var icon = document.getElementById('bypassIcon');
    var text = document.getElementById('bypassText');
    var banner = document.getElementById('clientBlockBanner');

    if (isBypassActive) {
        btn.classList.add('bypassed');
        icon.className = 'ph-bold ph-toggle-right';
        text.textContent = '前端 JS 校验: 已临时解除 (绕过模式)';
        if (banner) banner.style.display = 'none';
    } else {
        btn.classList.remove('bypassed');
        icon.className = 'ph-bold ph-toggle-left';
        text.textContent = '前端 JS 校验: 已开启 (正常模式)';
    }

    var fileInput = document.getElementById('fileInput');
    if (fileInput && fileInput.value) {
        checkFileExt(fileInput.value);
    }
}

// 3. 拖拽支持
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('dropzoneBox');
    if (!dropzone) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', function(e) {
        var dt = e.dataTransfer;
        var files = dt.files;
        var fileInput = document.getElementById('fileInput');
        if (files && files.length > 0 && fileInput) {
            fileInput.files = files;
            checkFileExt(fileInput.value);
        }
    }, false);
});
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
