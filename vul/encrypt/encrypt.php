<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密与防重放对抗靶场 (Frontend Cryptography & Anti-Replay Lab)
 * 学习并融合 encrypt-labs 全部 8 种高频实战加解密对抗场景
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "encrypt.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[341] = 'active';
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.encrypt-hero-banner {
    background: linear-gradient(135deg, #090d16 0%, #111827 50%, #1e1b4b 100%);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: var(--radius-xl, 16px);
    padding: 36px 40px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
}
.encrypt-hero-banner::after {
    content: "CRYPTO";
    position: absolute;
    right: 24px;
    bottom: -24px;
    font-size: 130px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    pointer-events: none;
    line-height: 1;
    font-family: monospace;
}
.encrypt-hero-title {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 14px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f8fafc;
}
.encrypt-badge-tag {
    background: rgba(99, 102, 241, 0.18);
    color: #a5b4fc;
    border: 1px solid rgba(165, 180, 252, 0.28);
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.encrypt-hero-desc {
    font-size: 14.5px;
    color: #cbd5e1;
    line-height: 1.7;
    max-width: 960px;
    margin: 0 0 20px 0;
}
.encrypt-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 24px;
}
.encrypt-stat-box {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 16px 20px;
    backdrop-filter: blur(8px);
}
.encrypt-stat-num {
    font-size: 26px;
    font-weight: 800;
    color: #60a5fa;
    margin-bottom: 4px;
    font-family: monospace;
}
.encrypt-stat-label {
    font-size: 13px;
    color: #94a3b8;
}

.matrix-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}
.scenario-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, rgba(226, 232, 240, 0.8));
    border-radius: 14px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}
.scenario-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    border-color: #6366f1;
}
.scenario-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 14px;
}
.scenario-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    background: #eef2ff;
    color: #4f46e5;
}
.scenario-chip {
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
    text-transform: uppercase;
}
.chip-aes { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.chip-rsa { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.chip-des { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.chip-sign { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.chip-replay { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.scenario-title {
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--text-primary, #0f172a);
}
.scenario-desc {
    font-size: 13.5px;
    color: var(--text-secondary, #64748b);
    line-height: 1.6;
    margin-bottom: 16px;
    flex: 1;
}
.scenario-meta {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 18px;
    font-size: 12.5px;
    color: #475569;
}
.scenario-meta div {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}
.scenario-meta div:last-child {
    margin-bottom: 0;
}
.scenario-meta span.key-label {
    color: #94a3b8;
    font-weight: 500;
}
.scenario-meta code {
    font-family: monospace;
    font-size: 12px;
    background: #ffffff;
    padding: 2px 6px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    color: #0f172a;
}
.btn-enter {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #4f46e5;
    color: #ffffff !important;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    padding: 10px 18px;
    border-radius: 8px;
    transition: background 0.2s;
}
.btn-enter:hover {
    background: #4338ca;
}

.guide-section {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 40px;
}
.guide-title {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 16px 0;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.method-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 18px;
    margin-top: 20px;
}
.method-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    background: #fafafa;
}
.method-card h5 {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 10px 0;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.method-card p {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="#">现代身份认证与访问控制</a></li>
                <li class="active">前端加解密与防重放对抗</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 头部 Hero Banner -->
            <div class="encrypt-hero-banner">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                    <span class="encrypt-badge-tag"><i class="ph-bold ph-shield-check"></i> 企业级攻防对抗专区</span>
                    <span class="encrypt-badge-tag" style="background:rgba(16, 185, 129, 0.15); color:#6ee7b7; border-color:rgba(110, 231, 183, 0.3);"><i class="ph-bold ph-cpu"></i> 8大实战关卡全覆盖</span>
                    <span class="encrypt-badge-tag" style="background:rgba(245, 158, 11, 0.15); color:#fde68a; border-color:rgba(253, 230, 138, 0.3);"><i class="ph-bold ph-wrench"></i> Burp AutoDecoder & JSRpc</span>
                </div>
                <h1 class="encrypt-hero-title">
                    <i class="ph-bold ph-lock-key" style="color:#818cf8;"></i>
                    前端加解密与防重放实战靶场 (Frontend Cryptography Lab)
                </h1>
                <p class="encrypt-hero-desc">
                    在企业级安全测试、红蓝对抗与渗透实战中，越来越多的现代 Web 系统与移动端小程序在客户端采用 JS 加密、哈希签名与动态时间戳防重放机制，导致常规的抓包篡改、Burp Suite 暴力破解与自动化扫描工具失效。本模块深度吸收 <code>encrypt-labs</code> 经典实战体系，完整还原从固定密钥、服务端动态密钥协商、RSA/AES 混合加密、规律密钥逆向到数据防篡改加签与防重放的 8 大对抗场景，助你掌握从逆向分析到插件自动化的完整渗透技能链。
                </p>
                <div class="encrypt-stat-grid">
                    <div class="encrypt-stat-box">
                        <div class="encrypt-stat-num">8 个</div>
                        <div class="encrypt-stat-label">真实加密防御实战场景</div>
                    </div>
                    <div class="encrypt-stat-box">
                        <div class="encrypt-stat-num">4 种</div>
                        <div class="encrypt-stat-label">现代自动化解密渗透方案</div>
                    </div>
                    <div class="encrypt-stat-box">
                        <div class="encrypt-stat-num">100%</div>
                        <div class="encrypt-stat-label">附带完整 Python/JS PoC 脚本</div>
                    </div>
                    <div class="encrypt-stat-box">
                        <div class="encrypt-stat-num">Zero-Fail</div>
                        <div class="encrypt-stat-label">标准化开箱即用免编译</div>
                    </div>
                </div>
            </div>

            <!-- 关卡全景矩阵 Grid -->
            <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a;">
                    🎯 靶场对抗关卡矩阵 (Scenario Matrix)
                </h3>
                <span style="font-size: 13px; color: #64748b;">默认测试凭据：<code>admin / 123456</code> 或 <code>pikachu / 000000</code></span>
            </div>

            <div class="matrix-grid">
                <!-- Level 1: AES 固定 Key -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-key"></i></div>
                            <span class="scenario-chip chip-aes">AES-128-CBC</span>
                        </div>
                        <h4 class="scenario-title">1. AES 固定 Key 加密</h4>
                        <p class="scenario-desc">
                            前端表单提交时，使用硬编码于 JS 脚本中的静态 Key 与 IV 对登录 JSON 载荷执行 AES-128-CBC 加密。逆向难度低，最容易通过全局搜索提取密钥。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">加密模式:</span> <code>AES-128-CBC / Pkcs7</code></div>
                            <div><span class="key-label">静态密钥:</span> <code>1234567890123456</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>Burp AutoDecoder 静态解密</span></div>
                        </div>
                    </div>
                    <a href="aes.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 2: AES 服务端动态 Key -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-arrows-clockwise"></i></div>
                            <span class="scenario-chip chip-aes">Dynamic AES</span>
                        </div>
                        <h4 class="scenario-title">2. AES 服务端动态 Key</h4>
                        <p class="scenario-desc">
                            前端在提交前预先请求 <code>server_generate_key.php</code> 获取会话绑定的动态随机 Key 与 IV，密钥生命周期短，静态搜索失效。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">密钥派生:</span> <code>Session-Scoped API 拉取</code></div>
                            <div><span class="key-label">生命周期:</span> <code>随 Session 动态轮转</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>Burp 宏请求 / Python 脚本交互</span></div>
                        </div>
                    </div>
                    <a href="aesserver.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 3: RSA 非对称加密 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-lock"></i></div>
                            <span class="scenario-chip chip-rsa">RSA-1024</span>
                        </div>
                        <h4 class="scenario-title">3. RSA 非对称加密</h4>
                        <p class="scenario-desc">
                            前端内置 1024 位 RSA 公钥，使用 JSEncrypt 对账号密码进行非对称公钥加密。仅服务端私钥可解密，无法通过逆向公钥完成数据包还原。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">加密方式:</span> <code>RSA-1024 Public Key</code></div>
                            <div><span class="key-label">单向特性:</span> <code>公钥加密 / 私钥解密</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>公钥重构加密爆破 / JSRpc</span></div>
                        </div>
                    </div>
                    <a href="rsa.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 4: AES + RSA 混合加密 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-shield-star"></i></div>
                            <span class="scenario-chip chip-rsa">Hybrid Crypto</span>
                        </div>
                        <h4 class="scenario-title">4. AES + RSA 混合加密 (数字信封)</h4>
                        <p class="scenario-desc">
                            现代银行金融级防御标准：前端每次生成随机 AES 密钥加密业务数据，再利用 RSA 公钥加密 AES 密钥与 IV。双层密文一并提交。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">数据层:</span> <code>AES-128-CBC (随机密钥)</code></div>
                            <div><span class="key-label">信封层:</span> <code>RSA-1024 公钥加密 Key/IV</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>自定义 Python 加密流水线 / JSRpc</span></div>
                        </div>
                    </div>
                    <a href="aesrsa.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 5: DES 规律 Key 加密 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-calculator"></i></div>
                            <span class="scenario-chip chip-des">DES Algorithm</span>
                        </div>
                        <h4 class="scenario-title">5. DES 规律 Key 加密</h4>
                        <p class="scenario-desc">
                            加密 Key 与 IV 并不直接硬编码，而是由业务参数（如 username）经特定字符串切片、填充（如 padEnd）动态计算派生。考验算法逆向还原能力。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">加密算法:</span> <code>DES-CBC (Hex Output)</code></div>
                            <div><span class="key-label">派生规则:</span> <code>Key=user.padEnd(8,'6')</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>JS 逆向算法复现 / Python 爆破</span></div>
                        </div>
                    </div>
                    <a href="des.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 6: 明文加签防篡改 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-fingerprint"></i></div>
                            <span class="scenario-chip chip-sign">HMAC-SHA256</span>
                        </div>
                        <h4 class="scenario-title">6. 明文数据加签防篡改</h4>
                        <p class="scenario-desc">
                            数据主体保持明文传输，但附带 Nonce、Timestamp 与 HMAC-SHA256 签名。服务端比对签名并校验时间窗口，若篡改请求任何字段即拒绝服务。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">签名算法:</span> <code>HMAC-SHA256 (Hex)</code></div>
                            <div><span class="key-label">签名要素:</span> <code>user+pwd+nonce+time</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>提取 SecretKey 后自动化重算签名</span></div>
                        </div>
                    </div>
                    <a href="signdata.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 7: 服务端动态加签 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-cloud-arrow-down"></i></div>
                            <span class="scenario-chip chip-sign">Server Sign</span>
                        </div>
                        <h4 class="scenario-title">7. 服务端动态加签防篡改</h4>
                        <p class="scenario-desc">
                            加签密钥不暴露于前端代码中，而是由前端异步调用 <code>get-signature.php</code> 获取签名后再提交主请求。无法直接从 JS 提取签名秘钥。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">密钥存储:</span> <code>服务端安全保存</code></div>
                            <div><span class="key-label">加签接口:</span> <code>/get-signature.php</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>中间人双阶请求链 / Python 编排</span></div>
                        </div>
                    </div>
                    <a href="signdataserver.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>

                <!-- Level 8: 防重放测试 -->
                <div class="scenario-card">
                    <div>
                        <div class="scenario-card-header">
                            <div class="scenario-icon"><i class="ph-bold ph-timer"></i></div>
                            <span class="scenario-chip chip-replay">Anti-Replay</span>
                        </div>
                        <h4 class="scenario-title">8. 防重放攻击综合测试</h4>
                        <p class="scenario-desc">
                            前端将毫秒时间戳使用 RSA 加密作为 Nonce 提交。服务端解密后校验 3 秒极窄时间窗口，并在数据库中记录 RequestID，彻底拦截 Repeater 重放。
                        </p>
                        <div class="scenario-meta">
                            <div><span class="key-label">防御机制:</span> <code>RSA(Timestamp) + RequestID</code></div>
                            <div><span class="key-label">时间窗口:</span> <code>3000 ms (毫秒级失效)</code></div>
                            <div><span class="key-label">对抗方法:</span> <span>毫秒级自动化实时加密发包</span></div>
                        </div>
                    </div>
                    <a href="norepeater.php" class="btn-enter"><i class="ph-bold ph-arrow-right"></i> 进入挑战</a>
                </div>
            </div>

            <!-- 攻防方法论指南 Section -->
            <div class="guide-section">
                <h4 class="guide-title">
                    <i class="ph-bold ph-terminal-window" style="color:#4f46e5;"></i>
                    前端加解密通用渗透对抗方法论 (Penetration Methodology)
                </h4>
                <p style="font-size:14px; color:#64748b; line-height:1.7; margin-bottom:20px;">
                    面对前端加密，攻击者的目标不是破解现代加密算法本身（如 AES/RSA 的密码学安全性），而是<strong>攻击密钥分发缺陷、实现脆弱性，或借助自动化工具将加密环节融入测试流水线</strong>：
                </p>

                <div class="method-card-grid">
                    <div class="method-card">
                        <h5><i class="ph-bold ph-magnifying-glass" style="color:#2563eb;"></i> 1. Chrome DevTools 快速断点逆向</h5>
                        <p>
                            通过搜索常见敏感关键字（如 <code>CryptoJS</code>, <code>encrypt</code>, <code>setPublicKey</code>, <code>signature</code>），或通过 <code>Event Listener Breakpoints -> Click / Submit</code>、XHR/fetch Breakpoints，拦截加密函数入口，观察参数结构与密钥。
                        </p>
                    </div>
                    <div class="method-card">
                        <h5><i class="ph-bold ph-wrench" style="color:#7c3aed;"></i> 2. Burp Suite 插件化自动化解密</h5>
                        <p>
                            安装 <strong>AutoDecoder</strong>、<strong>BurpCrypto</strong> 等扩展，配置 AES/DES 密钥、IV、模式与填充。在 Proxy 与 Repeater 中实现“明文输入 -> 插件自动加密发包 -> 服务端密文响应 -> 插件自动解密展示”，完全恢复明文渗透体验。
                        </p>
                    </div>
                    <div class="method-card">
                        <h5><i class="ph-bold ph-brackets-curly" style="color:#059669;"></i> 3. JSRpc / Sekiro 远程调用技术</h5>
                        <p>
                            当加密算法高度混淆、包含复杂控制流平坦化或 WASM 难以还原时，直接注入 JSRpc 客户端。在 Burp Suite 发包时通过 HTTP RPC 调用真实浏览器上下文中的原生 JS 加密函数，免去逆向复现成本。
                        </p>
                    </div>
                    <div class="method-card">
                        <h5><i class="ph-bold ph-code" style="color:#ea580c;"></i> 4. Python 脚本独立流水线爆破</h5>
                        <p>
                            利用 <code>pycryptodome</code> 库复现对应算法（AES、RSA、DES、HMAC）。结合 <code>requests.Session()</code> 先拉取动态 Key/签名/时间戳，再加密字典逐条提交，实现高效的多线程自动化撞库与脆弱凭据暴力破解。
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
