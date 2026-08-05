<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[165] = 'active open';
$ACTIVE[204] = 'active';
$ACTIVE[165] = 'active open';
$ACTIVE[204] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$ai_output = "";
$xss_triggered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_text = trim($_POST['input_text'] ?? '');
    
    // 模拟大模型将用户输入的文本整理成富文本 / Markdown 摘要
    if (strpos(strtolower($input_text), '<script') !== false || strpos(strtolower($input_text), '<img') !== false || strpos(strtolower($input_text), 'onerror=') !== false || strpos(strtolower($input_text), 'javascript:') !== false || strpos(strtolower($input_text), '<svg') !== false || strpos(strtolower($input_text), '<iframe') !== false) {
        $xss_triggered = true;
        // 模拟大模型直接把标签输出在文本中，前端应用未经 DOMPurify 消毒直接渲染
        $ai_output = "<h3>📊 AI 自动生成的前端研究报告摘要：</h3>\n" .
                     "<p>根据用户提供的数据资料，系统自动生成了相关技术评估说明图表如下：</p>\n" .
                     "<div style='border:1px dashed #ccc; padding:10px; background:#fff;'>" . $input_text . "</div>\n" .
                     "<p><i>注：本摘要由大模型生成，系统未对 HTML/Markdown 特殊字符进行安全过滤。</i></p>";
    } else {
        if (empty($input_text)) {
            $ai_output = "<p>请在左侧输入需要 AI 总结处理的资料或文章片段。</p>";
        } else {
            $ai_output = "<h3>📊 AI 自动摘要结果：</h3><p>" . htmlspecialchars($input_text) . "</p><p><b>分析评价：</b>文本内容结构严谨，属于常规安全的技术交流说明。</p>";
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="ai_security.php">AI / LLM 应用安全</a></li>
                <li class="active">不安全渲染 (XSS &amp; SSRF)</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🎨 LLM 不安全输出渲染与跨站脚本 (LLM Unsafe Output Rendering / XSS)</h2>
                <p>在基于大语言模型的 AI 应用中，模型返回的结果常常包含丰富的 Markdown 语法、表格、甚至是内嵌的 HTML 代码。为了让界面看起来美观，大部分前端会使用 <code>innerHTML</code> 或没有严格启用白名单过滤的 Markdown 转换引擎把 AI 返回的文本直接渲染到 DOM 树上。</p>
                <p>这就是常见的 <b>LLM 不安全输出渲染 (OWASP LLM02:2025 Unsafe Output Handling)</b>！攻击者可以通过提示词注入，让模型生成带有恶意 JavaScript 或盲加载链接的语句（如 <code>&lt;img src=x onerror=alert(1)&gt;</code> 或 Markdown 图片链诱导发起 SSRF），任何查看该 AI 生成报告的用户都会遭到 XSS 攻击或会话劫持！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-pencil-square-o"></i> 提交待 AI 处理的文档素材</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="input_text">输入文本、报告或恶意 Markdown 标签：</label>
                                <textarea class="form-control" name="input_text" id="input_text" rows="5" placeholder="输入你想让 AI 总结并排版展示的内容..."><?php echo isset($_POST['input_text']) ? htmlspecialchars($_POST['input_text']) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-magic"></i> 生成富文本报告</button>
                            <button type="button" class="btn btn-default" onclick="fillSafe()"><i class="fa fa-file-text-o"></i> 正常排版样例</button>
                            <button type="button" class="btn btn-danger" onclick="fillXSS()"><i class="fa fa-code"></i> 注入 XSS 弹窗载荷</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-eye"></i> 前端报告预览界面 (DOM Render Box)</h4>
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; min-height: 180px;">
                            <?php if (!empty($ai_output)) {
                                echo $ai_output; // 注意：此处模拟不过滤直接渲染，触发 XSS
                                if ($xss_triggered) {
                                    echo "<div class='alert alert-warning' style='margin-top:15px; margin-bottom:0;'><b><i class='fa fa-flag'></i> 漏洞利用成功提示：</b> 浏览器 DOM 已渲染恶意标签！<br/>恭喜获得认证 Key：<code>FLAG{LLM_UNSAFE_RENDERING_DOM_XSS_EXPLOITED}</code></div>";
                                }
                            } else { ?>
                                <div style="color: #6c757d; font-style: italic;">
                                    // 左侧提交数据后，此处将直接显示 AI 返回并经前端 DOM 渲染后的内容。
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillSafe() {
    document.getElementById('input_text').value = "# 2026 年度安全态势分析\n1. 企业应当构建零信任网络。\n2. 加密全部核心通信通道。";
}
function fillXSS() {
    document.getElementById('input_text').value = "此处插入了一张隐藏图表：<img src=invalid_image.jpg onerror=\"alert('FLAG{LLM_UNSAFE_RENDERING_DOM_XSS_EXPLOITED}'); console.log('Hacked by LLM XSS!');\" style=\"max-width:100%; border:2px red solid;\" alt=\"恶意 XSS 触发图\"/>";
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


