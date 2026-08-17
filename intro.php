<?php
/**
 * Created by runner.han
 * There is nothing new under the sun
 * 
 * Pikachu-Enhanced v2.0 Modern Cyber-Security Command Center
 */

include_once 'inc/config.inc.php';

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[219] = 'active';

include 'header.php';

$html = '';
try {
    @mysqli_report(MYSQLI_REPORT_OFF);
    $link = @mysqli_connect(DBHOST, DBUSER, DBPW, DBNAME, DBPORT);
    if(!$link){
        $html .= "<div class='alert alert-danger' style='margin-bottom: 20px; border-radius: 8px;'><i class='fa fa-exclamation-triangle'></i> <strong>提示：</strong>系统尚未初始化或数据库连接失败，请点击 <a href='install.php' style='color: #fff; text-decoration: underline; font-weight: bold;'>这里完成数据库安装与初始化</a>。</div>";
    }else{
        @mysqli_set_charset($link, 'utf8');
        @mysqli_close($link);
    }
} catch (Throwable $e) {
    $html .= "<div class='alert alert-danger' style='margin-bottom: 20px; border-radius: 8px;'><i class='fa fa-exclamation-triangle'></i> <strong>提示：</strong>系统尚未初始化或数据库连接失败，请点击 <a href='install.php' style='color: #fff; text-decoration: underline; font-weight: bold;'>这里完成数据库安装与初始化</a>。</div>";
}
?>


<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="ace-icon fa fa-home home-icon"></i>
                    <a href="index.php">主页</a>
                </li>
                <li class="active">🗺️ 全站漏洞学习路线图 (Roadmap)</li>
            </ul>
        </div>

        <div class="page-content">
            <?php echo $html; ?>

            <div class="dashboard-wrapper">
                <!-- Hero Banner -->
                <div class="hero-banner">
                    <div class="hero-title">
                        <i class="fa fa-map-signs"></i>
                        Pikachu-Enhanced v2.0 全站漏洞学习路线图 (Roadmap)
                    </div>
                    <div class="hero-subtitle">
                        系统化攻防全景图谱！涵盖 5 大核心演练方向（经典 Web、现代 Web / RCE、内网渗透、AD 域控安全、云原生与微服务），帮助攻防人员清晰掌握漏洞演进路线与体系化技能树。
                    </div>
                    <div class="hero-badges">
                        <div class="hero-badge"><i class="fa fa-bug"></i> 41 个漏洞大类图鉴</div>
                        <div class="hero-badge"><i class="fa fa-crosshairs"></i> 170+ 独立实战关卡</div>
                        <div class="hero-badge"><i class="fa fa-check-circle"></i> 全量零报错运行验证</div>
                        <div class="hero-badge"><i class="fa fa-rocket"></i> 前沿架构 & 经典安全深度融合</div>
                    </div>
                </div>

                <!-- Control Bar (Search & Filters) -->
                <div class="control-bar">
                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="searchInput" placeholder="🔍 搜索任意漏洞、技术词条、协议或参数（如：JWT, RCE, K8s, Prompt, 越权...）" />
                    </div>
                    <div class="filter-tabs" id="filterTabs">
                        <button class="filter-btn active" data-target="all"><i class="fa fa-th-large"></i> 全部展现 (41)</button>
                        <button class="filter-btn" data-target="ai"><i class="fa fa-robot"></i> 🤖 AI 与大模型应用 (4)</button>
                        <button class="filter-btn" data-target="cloud"><i class="fa fa-cloud"></i> ☁️ 云原生与微服务 (9)</button>
                        <button class="filter-btn" data-target="auth"><i class="fa fa-lock"></i> 🛡️ 现代认证与业务逻辑 (12)</button>
                        <button class="filter-btn" data-target="proto"><i class="fa fa-globe"></i> 🌐 前沿协议与缓存 (10)</button>
                        <button class="filter-btn" data-target="classic"><i class="fa fa-fire"></i> 🏛️ 经典 Web 攻防演练 (16)</button>
                    </div>
                </div>

                <div id="noResults" class="no-results">
                    <i class="fa fa-search-minus"></i>
                    <h3>未找到匹配的漏洞关卡</h3>
                    <p>尝试更换关键词或点击上方卡片查看所有分类</p>
                </div>

                <!-- ================= SECTION 1: AI & LLM ================= -->
                <div class="category-section" data-section="ai">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon cat-icon-ai"><i class="fa fa-robot"></i></div>
                            <h2 class="category-title">一、 AI 与大模型应用安全 (AI / LLM Security)</h2>
                        </div>
                        <span class="category-count">4 大核心场景</span>
                    </div>
                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="ai">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Prompt 注入与防御绕过</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">利用上下文分隔符与特殊指令符覆盖预设 System Prompt，诱导 AI 忽略安全限制，越权输出非预期系统信息。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-ai">OWASP LLM-01</span>
                                <a href="vul/ai_security/prompt_injection.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="ai">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">敏感规则与提示词提取</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">通过社工提示词诱骗大模型逐步打印、倒序输出或编码转换，向用户泄露内部商业秘密规则与预设 System Prompt。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-ai">OWASP LLM-06</span>
                                <a href="vul/ai_security/llm_data_leakage.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="ai">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">插件工具命令注入 (RCE)</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">滥用 AI 调用的命令行插件，通过在自然语言中注入分号 `;` 或管道 `|` 拼接系统命令，实现服务器底层 Shell RCE 控机。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-ai">极危 RCE</span>
                                <a href="vul/ai_security/llm_plugin_rce.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="ai">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">不安全渲染 (XSS & SSRF)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">构造恶意 Prompt 促使 AI 返回带 <code>&lt;script&gt;</code> 或 Markdown 外部恶意链接标签，在客服端触发 DOM 渲染劫持或盲 SSRF。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-ai">OWASP LLM-02</span>
                                <a href="vul/ai_security/llm_xss.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= SECTION 2: Cloud Native ================= -->
                <div class="category-section" data-section="cloud">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon cat-icon-cloud"><i class="fa fa-cloud"></i></div>
                            <h2 class="category-title">二、 云原生、容器化与微服务架构安全 (Cloud Native & Microservices)</h2>
                        </div>
                        <span class="category-count">13 大核心场景</span>
                    </div>
                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">1. Docker 特权模式逃逸 (--privileged)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">当开启 `--privileged` 特权模式启动容器时，硬件设备 `/dev/vda1` 裸露暴露，通过 `mount` 重挂载宿主机根磁盘并 `chroot` 切入 Host OS。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">特权逃逸 100 PTS</span>
                                <a href="vul/dockerlab/docker_privileged_escape.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">2. Docker Socket 挂载逃逸 (docker.sock)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">容器挂载 `/var/run/docker.sock` 后，攻击者可用 cURL 向宿主机 REST API 发送请求创建挂载宿主机根目录的新特权容器 `escape_pod`。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">Socket 逃逸 150 PTS</span>
                                <a href="vul/dockerlab/docker_sock_escape.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">3. Linux Capabilities 逃逸 (CAP_SYS_ADMIN)</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">利用 `CAP_SYS_ADMIN` 特权能力挂载 cgroups v1 控制树，注入 `release_agent` 回调脚本，使宿主机内核在进程终止时以 Host Root 执行提权命令。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">Capabilities 200 PTS</span>
                                <a href="vul/dockerlab/docker_caps_escape.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">4. runc & Dirty Pipe CVE 逃逸</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">针对 CVE-2019-5736 (runc 句柄泄露反向覆写宿主机 `/usr/bin/runc`) 与 CVE-2022-0847 (脏管道内存强写只读 `/etc/passwd`) 进行漏洞利用实操。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">CVE 逃逸 250 PTS</span>
                                <a href="vul/dockerlab/docker_cve_escape.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">5. Kubernetes ServiceAccount 越权逃逸</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">利用 Pod 内自动挂载的 ServiceAccount Token，向 K8s API Server 发起越权请求，部署特权 Pod 挂载宿主机根目录夺取 Master 节点控制权。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">K8s 越权 300 PTS</span>
                                <a href="vul/dockerlab/k8s_token_escape.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Serverless 函数计算环境泄露</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">利用 Serverless 边缘代码执行缺陷，读取 `$_SERVER` 与 `getenv()` 中的云厂商 `AWS_ACCESS_KEY_ID` 等高度敏感凭证。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">Serverless</span>
                                <a href="vul/serverless/lambda_env_leak.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">对象存储 Bucket 越权读写</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">云存储 Bucket 策略配置错误（Public-Read-Write），导致未授权用户不仅能遍历存储桶文件，还能覆写并篡改核心业务页面。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">云存储 OSS</span>
                                <a href="vul/cloud_storage/oss_bucket_unauth.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">微服务 gRPC 越权与篡改</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">针对基于 HTTP/2 Protobuf 协议的 RPC 微服务，伪造 Metadata Headers 绕过拦截器鉴权并对请求序列化参数实现 BOLA 越权。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">gRPC / RPC</span>
                                <a href="vul/grpc/grpc_auth_bypass.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Webhook 异步回调盲 SSRF</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">滥用系统对外部 Webhook URL 的回调功能，将目标篡改为 `127.0.0.1` 或内网 IP，实施内网端口探测与盲 SSRF 数据打捞。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">微服务回调</span>
                                <a href="vul/webhook/webhook_ssrf.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Flask / Jinja2 SSTI 模板注入 (15000端口)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">针对 Python Flask / Jinja2 模板引擎的 SSTI 漏洞。利用 `{{7*7}}` 探测，通过类继承链 `__mro__` / `__subclasses__` 绕过沙箱执行系统命令。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">Flask SSTI (15000)</span>
                                <a href="vul/rce/rce_ssti.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">.env 数据库账密泄露</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">Web 容器未正确配置对以点开头的 dotfiles 隐藏文件的拒绝访问，直接暴露生产环境 `.env` 文件中的数据库账号密码与密钥。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">配置泄露</span>
                                <a href="vul/misconfig/env_leak.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">.git 源码仓库泄露 (GitHack)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">开发部署时未清理 `.git` 版本控制目录，攻击者可利用 GitHack 工具还原整站完整源代码及开发者历史敏感提交记录。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">源码泄露</span>
                                <a href="vul/misconfig/git_leak.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Swagger UI 在线调试暴露</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">Spring Boot / OpenAPI 接口文档及 Swagger 在线调试界面未授权暴露在公网上，直接成为黑客进行全站接口发包攻击的天然面板。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">API 文档</span>
                                <a href="vul/misconfig/swagger_unauth.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="cloud">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Docker Lab 镜像与靶场演练中心</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">专属 Docker 容器化实验模块，学习容器运行环境检测、Docker API 未授权调用与 10+ 容器靶场模板管理。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-cloud">Docker 实验室</span>
                                <a href="vul/dockerlab/dockerlab_center.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= SECTION 3: Modern Auth & Logic ================= -->
                <div class="category-section" data-section="auth">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon cat-icon-auth"><i class="fa fa-lock"></i></div>
                            <h2 class="category-title">三、 现代认证、授权与业务逻辑安全 (Modern Auth & Business Logic)</h2>
                        </div>
                        <span class="category-count">12 大核心场景</span>
                    </div>
                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">JWT None 算法绕过</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">将 JWT Header 中的 `alg` 设置为 `None`，剔除第三段 Signature 验签内容，直接欺骗后端验签逻辑取得管理员身份。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">JWT 认证</span>
                                <a href="vul/jwt/jwt_none.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">JWT 弱密钥离线爆破</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">针对使用 HMAC-SHA256 (HS256) 的 JWT 凭据，使用 hashcat 或 John 结合密码字典，离线暴力破解服务器对称签名密钥。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">JWT 认证</span>
                                <a href="vul/jwt/jwt_weak_secret.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">JWT 算法混淆 (RS-to-HS)</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">将非对称算法 RS256 篡改为 HS256，利用公开的服务端 RSA 公钥作为 HMAC 对称加密密钥，伪造任意用户的合法签名。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">高阶 JWT</span>
                                <a href="vul/jwt/jwt_key_confusion.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">SAML XML 签名包装 (XSW)</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">利用 SSO SAML 解析器与验签模块对 XML 节点顺序解析的差异，克隆并篡改 `<Assertion>` 身份断言节点，实现任意用户伪造。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">SSO / SAML</span>
                                <a href="vul/sso_saml/saml_xsw.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">多因素认证 (MFA Bypass)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">多步骤登录验证中（账号->验证码->进入系统），直接越步请求终态 API，或对 4 位数弱验证码实施高频接口轰炸绕过 2FA。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">MFA 多因素</span>
                                <a href="vul/mfa_bypass/mfa_logic_bypass.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">OAuth 2.0 State 劫持</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">OAuth 登录流程缺失 `state` 随机防伪参数校验，或第三方回调 URL 过滤不严，导致授权码被窃取并绑定黑客控制的第三方账号。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">OAuth 授权</span>
                                <a href="vul/oauth/oauth.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">API IDOR / BOLA 越权</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">现代 REST API 接口依赖可预测的资源 ID（如 `/api/user/1001`）拉取数据，未严格校验当前 Token 对该资源的所有权，导致跨账号泄露。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">现代 API</span>
                                <a href="vul/overpermission/api/admin_dashboard.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Mass Assignment (批量赋值)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">个人资料修改接口将前端 JSON 对象直接绑定绑定后端实体模型，通过强行插入 `"role":"admin"` 或 `"balance":9999` 字段实现权限提升。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">现代 API</span>
                                <a href="vul/api_security/mass_assignment.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">并发竞争兑换 (Race Condition)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">使用多线程发包工具同时发送同一笔优惠券核销或转账提现请求，利用数据库“查询余额与扣减库存”的时间差突破单次限制。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">并发竞争</span>
                                <a href="vul/race_condition/race_condition.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">商品价格与数量参数篡改</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">在订单提交表单或支付 API 中，抓包修改 `price` 为 `0.01`，或将购买数量修改为负数，利用盲信前端传参的业务逻辑刷取余额。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">业务逻辑</span>
                                <a href="vul/logic/price_tamper.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">水平越权与垂直越权</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">传统页面开发中，同级普通用户相互修改 ID 查看私密笔记（水平越权），以及普通用户通过猜测后台 URL 强行进管理员系统（垂直越权）。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">经典越权</span>
                                <a href="vul/overpermission/op1/op1_login.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="auth">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Session Fixation (会话固定)</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">用户登录前服务器就分配了一个 Session ID，成功登录后未更换新 ID。黑客将其诱导给受害者使用后，直接复用该 ID 窃取会话。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">会话安全</span>
                                <a href="vul/sessionfixation/sessionfixation.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= SECTION 4: Advanced Protocols ================= -->
                <div class="category-section" data-section="proto">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon cat-icon-proto"><i class="fa fa-globe"></i></div>
                            <h2 class="category-title">四、 前沿协议、数据交互与缓存安全 (Advanced Protocols & Cache)</h2>
                        </div>
                        <span class="category-count">10 大核心场景</span>
                    </div>
                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">HTTP 请求走私 (CL.TE)</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">利用前端反向代理（CL）与后端应用服务器（TE）对 HTTP Content-Length 与 Chunked 长度头解析差异，走私第二段请求绕过网关鉴权。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">HTTP 走私</span>
                                <a href="vul/http_smuggling/cl_te.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">跨站 WebSocket 劫持 (CSWSH)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">WS 握手建连阶段服务端未严格校验 `Origin` 源头，黑客通过恶意网页诱导受害者浏览器自动携带 Cookie 建立持久化通道并窃听流数据。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">WebSocket</span>
                                <a href="vul/websocket/ws_unauth_stream.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">WebSocket 数据帧 SQL 注入</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">建立 WebSocket 双向连接后，传统 WAF 无法检测通信流量，攻击者在发送的 JSON 文本数据帧（Data Frame）中直接注入恶意 SQL 语句。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">WebSocket</span>
                                <a href="vul/websocket/ws_sqli.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Web 缓存欺骗 (WCD)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">构造访问 `/profile.php/nonexistent.css`，利用 CDN/Nginx 与应用的 URL 路由判定差异，欺骗缓存服务器将包含私密个人信息的页面按 CSS 长期缓存。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">Web 缓存</span>
                                <a href="vul/web_cache/web_cache.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">GraphQL 内省与越权查询</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">利用 GraphQL 未关闭内省查询（Introspection），一次性 Dump 整个后端数据接口 Schema 及隐藏字段，并实施批量嵌套查询发起 DOS 或越权拉取。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">GraphQL</span>
                                <a href="vul/graphql/graphql.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">MongoDB NoSQL 认证绕过</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">在登录表单中传入 JSON 数组 `{"username":"admin", "password":{"$ne":""}}`，利用 MongoDB 查询语法的非等操作符直接绕过账号密码鉴权。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">NoSQL 注入</span>
                                <a href="vul/nosql/nosql.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">CORS Misconfig 跨域错配</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">服务端对 `Origin` 头盲目反射并开启 `Allow-Credentials: true`，恶意网页只需发包即可跨域读取受害者在该系统的隐私接口数据响应。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">CORS 跨域</span>
                                <a href="vul/cors/cors_api.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Host Header 劫持与密码重置</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">在找回密码邮件发送接口中，抓包将 HTTP 头部的 `Host: target.com` 篡改为黑客域名，系统生成的密码重置链接将被发往黑客服务器从而截获凭证。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">Host Header</span>
                                <a href="vul/hostheader/hostheader.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Clickjacking 页面点击劫持</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">系统响应头缺失 `X-Frame-Options` 或 CSP 帧限制，黑客将目标系统用 `opacity: 0` 的 iframe 嵌套在诱导性抽奖网页上方，诱骗受害者点击隐藏按钮。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">点击劫持</span>
                                <a href="vul/clickjacking/clickjacking.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="proto">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">前端 DOM Clobbering (破坏)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">利用浏览器将带 `id` 或 `name` 的 HTML 标签自动映射为 window 全局变量的特性（如 `<a id="config">`），覆盖全局 JS 配置对象篡改脚本执行流。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-proto">现代前端</span>
                                <a href="vul/frontend/frontend.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                
                
                <!-- ================= SECTION: Intranet & AD Security ================= -->
                <div class="category-section" data-section="auth">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon" style="background: linear-gradient(135deg, #6366f1, #312e81);"><i class="fa fa-sitemap"></i></div>
                            <h2 class="category-title">内网与 Active Directory 域安全 (Intranet & AD Security)</h2>
                        </div>
                        <span class="category-count">5 大关卡与纲领</span>
                    </div>

                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="auth" data-title="goad 依赖 智能识别 自动检测 vmware wsl2 vagrant 环境" data-desc="goad 依赖 自动识别 vmware wsl2 vagrant 环境变量 重定向">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">🔍 GOAD 依赖智能识别中心</h3>
                                    <div class="card-stars">★★★★★</div>
                                </div>
                                <p class="card-desc">自动扫描检测物理机中的 VMware Workstation、WSL2 以及 Vagrant 自动化编排依赖状态。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">依赖识别中心</span>
                                <a href="vul/ad_security/ad_env_check.php" class="launch-btn">⚡ 打开面板 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="vuln-card" data-cat="auth" data-title="内网安全 6 大知识体系 全景图 侦察 认证 攻击 横向 维持 防御" data-desc="内网 知识体系 6 大模块 侦察 认证 攻击 横向 维持 防御">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">一、内网安全 6 大知识体系全景图</h3>
                                    <div class="card-stars">★★★★★</div>
                                </div>
                                <p class="card-desc">覆盖基础侦察、NTLM/Kerberos 域认证机制、票据与委派攻击、横向移动、权限维持及 EDR/SIEM 蓝队防御。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">知识体系全景</span>
                                <a href="vul/ad_security/ad_knowledge.php" class="launch-btn">⚡ 查看体系 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="vuln-card" data-cat="auth" data-title="ad 域漏洞靶场搭建大纲 5 阶段 环境规划 部署 场景 辅助 闭环" data-desc="ad 域 靶场搭建 5 阶段 环境规划 部署 场景 辅助 闭环">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">二、AD 域漏洞靶场搭建 5 阶段蓝图</h3>
                                    <div class="card-stars">★★★★★</div>
                                </div>
                                <p class="card-desc">规范化的 AD 靶场建设指南：环境规划、DC与成员机部署、多阶漏洞场景配置、快照 SIEM 审计与练习闭环。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">靶场建设蓝图</span>
                                <a href="vul/ad_security/ad_lab_setup.php" class="launch-btn">⚡ 查看蓝图 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="vuln-card" data-cat="auth" data-title="winrm ws-management 探测" data-desc="ws-management winrm 5985 5986 远程管理服务 端口握手 凭据泄露">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">三、内网侦察与 BloodHound 拓扑图论</h3>
                                    <div class="card-stars">★★★★☆</div>
                                </div>
                                <p class="card-desc">非侵入性枚举域用户、域信任与对象 ACL，导出 ZIP 使用 BloodHound 寻找最短域管提权路径。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">BloodHound/LDAP</span>
                                <a href="vul/ad_security/ad_ctf_recon.php" class="launch-btn">⚡ 启动演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="vuln-card" data-cat="auth" data-title="kerberos kerberoasting asrep 黄金票据 白银票据" data-desc="kerberos 票据攻击 离线爆破 spn 黄金票据 krbtgt as-rep">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">四、Kerberos 域认证与票据攻击</h3>
                                    <div class="card-stars">★★★★★</div>
                                </div>
                                <p class="card-desc">演练 Kerberoasting 离线爆破 SPN 服务账号口令、AS-REP Roasting 提取以及伪造 Golden Ticket (黄金票据)。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">Kerberos/TGT</span>
                                <a href="vul/ad_security/ad_ctf_kerberoast.php" class="launch-btn">⚡ 启动演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>

                        <div class="vuln-card" data-cat="auth" data-title="ad 域内横向移动 约束性委派 RBCD 影子凭据" data-desc="委派攻击 约束性委派 RBCD 影子凭据 凭据传递 NTLM Relay">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">五、Kerberos 委派攻击与 AD CS 证书中继</h3>
                                    <div class="card-stars">★★★★★</div>
                                </div>
                                <p class="card-desc">演练 S4U2proxy 约束委派、RBCD 基于资源的委派、AD CS ESC8 NTLM HTTP 中继与 Shadow Credentials 维持。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-auth">Delegation/ADCS</span>
                                <a href="vul/ad_security/ad_ctf_delegation.php" class="launch-btn">⚡ 启动演练 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

<!-- ================= SECTION 5: Classic Web ================= -->
                <div class="category-section" data-section="classic">
                    <div class="category-header">
                        <div class="category-title-wrap">
                            <div class="category-icon cat-icon-classic"><i class="fa fa-fire"></i></div>
                            <h2 class="category-title">五、 经典 Web 攻防演练 (Classic Web Vulnerabilities)</h2>
                        </div>
                        <span class="category-count">16 大核心系列 (86+ 关卡)</span>
                    </div>
                    <div class="vuln-grid">
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">SQL-Inject (10关综合演练)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">深入研习数字型、字符型、搜索型、XX型、Insert/Update/Delete 报错注入、布尔盲注、时间盲注及宽字节盲注的闭合绕过与 Dump 技巧。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">SQL 注入</span>
                                <a href="vul/sqli/sqli_str.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Cross-Site Scripting (10关演练)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">全面实操反射型 GET/POST、存储型留言板、DOM型及 DOM-X、盲打管理员后台 XSS、实体编码防御绕过及不同 JS 上下文中的输出逃逸。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">XSS 跨站</span>
                                <a href="vul/xss/xss_reflected_get.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Zip Slip 解压目录穿越 RCE</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">突破常规文件上传检查，制作包含 `../../../../var/www/html/shell.php` 特殊路径的 ZIP 压缩包，解压时跨目录直接覆盖站点脚本执行 RCE。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">高阶上传</span>
                                <a href="vul/unsafeupload/zip_slip.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">传统文件上传绕过演练</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">练习使用 Burp Suite 拦截抓包绕过前端 JS 扩展名验证、篡改 MIME `Content-Type` 头，以及利用 `getimagesize()` 插入 GIF 伪装文件头上传一句话木马。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">文件上传</span>
                                <a href="vul/unsafeupload/clientcheck.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">SSRF Gopher 打击 Redis RCE</h3>
                                    <span class="card-stars">★★★★★</span>
                                </div>
                                <p class="card-desc">利用 cURL 支持 Gopher 协议的特性，构造原生的 Redis 协议通信数据包，直接向内网未授权 Redis 写入定时任务或 WebShell 取得系统最高权限。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">极危 SSRF</span>
                                <a href="vul/ssrf/ssrf_gopher_redis.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">SSRF AWS Metadata 凭证窃取</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">通过服务器 SSRF 漏洞，构造 HTTP 请求访问云主机固定地址 `169.254.169.254`，窃取 EC2 实例绑定 RAM 角色的 IAM 临时访问密钥对与 Session Token。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">云 SSRF</span>
                                <a href="vul/ssrf/ssrf_curl.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">高阶 Phar 伪协议自动解包</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">即使目标系统中没有显式调用 `unserialize()`，只需在任何诸如 `file_exists('phar://test.png/test.txt')` 文件操作中传入 phar 协议即可自动触发反序列化！</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">Phar 反序列化</span>
                                <a href="vul/phar/phar.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">PHP 反序列化漏洞</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">深入理解 PHP `serialize()` 与 `unserialize()` 的原理，构造带魔术方法（如 `__wakeup`, `__destruct`）的自定义对象字符串实现对象属性篡改与代码执行。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">PHP 反序列化</span>
                                <a href="vul/unserilization/unser.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Java 原生反序列化 (readObject)</h3>
                                    <span class="card-stars">★★★★☆</span>
                                </div>
                                <p class="card-desc">剖析 Java `ObjectInputStream.readObject()` 序列化流格式（魔数 `0xACED0005` / `rO0AB`），演练 URLDNS、Custom Gadget 命令执行利用链。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">Java 反序列化</span>
                                <a href="vul/java_unserialize/java_unserialize.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">RCE 命令执行 (`exec ping/eval`)</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">后端调用系统 ping 或 eval 函数时过滤不严，通过输入 `127.0.0.1; cat /etc/passwd` 或利用管道符 `|`、`&&` 拼接任意系统指令控制服务器。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">RCE 命令注入</span>
                                <a href="vul/rce/rce_ping.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">XXE XML 外部实体任意读取</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">解析器开启 XML 外部实体（External Entity）支持，构造 `<!ENTITY xxe SYSTEM "file:///etc/passwd">` 将外部实体引用置于 XML 体中实现文件读取或内网探测。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">XXE 注入</span>
                                <a href="vul/xxe/xxe_1.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">CSRF (GET/POST/Token防范)</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">诱导已登录受害者点击链接或访问暗藏表单的网页，在受害者浏览器自动携带合法 Cookie 的背景下静默向服务器发送修改密码、转账等请求。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">CSRF 伪造</span>
                                <a href="vul/csrf/csrfget/csrf_get_edit.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">File Inclusion 本地/远程包含</h3>
                                    <span class="card-stars">★★★☆☆</span>
                                </div>
                                <p class="card-desc">动态文件包含参数 `$filename` 过滤不严，利用 `../../../../etc/passwd` 跨目录包含本地系统配置，或传入远程 Web 网址直接加载并执行木马脚本。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">文件包含</span>
                                <a href="vul/fileinclude/fi_local.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">../../ (目录遍历 Traversal)</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">针对提供下载或展示文件内容的接口，未过滤 `../` 路径跳转符号，导致用户可无限层极向上跳出 Web 根目录，读取操作系统系统底层的任意敏感文件。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">目录遍历</span>
                                <a href="vul/dir/dir_list.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">Unsafe Filedownload 任意下载</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">URL 参数指定下载文件名 `filename=test.png` 时未限制下载目录，将其篡改为 `../../../../inc/config.inc.php` 即可直接将数据库配置文件或源码打包下载。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">任意下载</span>
                                <a href="vul/unsafedownload/execdownload.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">URL重定向 (任意跳转劫持)</h3>
                                    <span class="card-stars">★★☆☆☆</span>
                                </div>
                                <p class="card-desc">登录或广告点击跳转接口中未校验 `url` 参数指向的目的域名，被黑客利用作为官方受信域名的信任跳板，诱骗用户点击跳转至假冒的钓鱼诈骗页面。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">URL 跳转</span>
                                <a href="vul/urlredirect/urlredirect.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="vuln-card" data-cat="classic">
                            <div>
                                <div class="card-top">
                                    <h3 class="card-title">敏感信息泄露 (Info Leak)</h3>
                                    <span class="card-stars">★☆☆☆☆</span>
                                </div>
                                <p class="card-desc">前端 HTML/JS 源码中遗留测试账号、硬编码 API Key 与注释，或由于未屏蔽后端详细报错，导致 SQL 查询语句、报错绝对路径直接暴露给攻击者。</p>
                            </div>
                            <div class="card-bottom">
                                <span class="card-tag tag-classic">信息泄露</span>
                                <a href="vul/infoleak/infoleak.php" class="launch-btn">⚡ 启动关卡 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- /.dashboard-wrapper -->
        </div><!-- /.page-content -->
    </div>
</div><!-- /.main-content -->

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const sections = document.querySelectorAll('.category-section');
    const cards = document.querySelectorAll('.vuln-card');
    const noResults = document.getElementById('noResults');

    let currentFilter = 'all';

    function updateDisplay() {
        const query = searchInput.value.toLowerCase().trim();
        let totalVisibleCards = 0;

        sections.forEach(section => {
            const sectionCat = section.getAttribute('data-section');
            const sectionCards = section.querySelectorAll('.vuln-card');
            let visibleCardsInSection = 0;

            // Check if section matches category filter
            const matchCategory = (currentFilter === 'all' || currentFilter === sectionCat);

            sectionCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const desc = card.querySelector('.card-desc').textContent.toLowerCase();
                const tag = card.querySelector('.card-tag').textContent.toLowerCase();

                const matchQuery = !query || title.includes(query) || desc.includes(query) || tag.includes(query);

                if (matchCategory && matchQuery) {
                    card.style.display = 'flex';
                    visibleCardsInSection++;
                    totalVisibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show or hide section header based on whether it has visible cards
            if (visibleCardsInSection > 0) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Show empty state if nothing found
        if (totalVisibleCards === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    // Event listeners for tabs
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-target');
            updateDisplay();
        });
    });

    // Event listener for search
    searchInput.addEventListener('input', updateDisplay);
});
</script>

<?php
include 'footer.php';
?>


