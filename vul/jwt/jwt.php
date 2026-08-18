<?php
/**
 * Pikachu-Enhanced v2.0 - 现代身份认证安全 (Modern Identity & Authentication Security Hub)
 */
include_once '../../inc/config.inc.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[158] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.jwt-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 16px;
    padding: 32px 36px;
    color: #ffffff;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.jwt-hero-banner::after {
    content: "JWT";
    position: absolute;
    right: 20px;
    bottom: -20px;
    font-size: 140px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    pointer-events: none;
    line-height: 1;
}
.jwt-hero-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f8fafc;
}
.jwt-badge-tag {
    background: rgba(99, 102, 241, 0.2);
    color: #a5b4fc;
    border: 1px solid rgba(165, 180, 252, 0.3);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.jwt-hero-desc {
    font-size: 14.5px;
    color: #cbd5e1;
    line-height: 1.7;
    max-width: 980px;
    margin: 0;
}

/* Three-segment color codes */
.jwt-part-header { color: #ef4444; font-weight: 700; }
.jwt-part-payload { color: #a855f7; font-weight: 700; }
.jwt-part-sig { color: #06b6d4; font-weight: 700; }

.jwt-debugger-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 28px;
    box-shadow: var(--shadow-sm);
}
.jwt-segment-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 16px;
    height: 100%;
}
.jwt-segment-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.level-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.level-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 22px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.level-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.12);
}
.level-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.level-num-badge {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border: 1px solid rgba(37, 99, 235, 0.25);
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
}
.level-pts-badge {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
}
.level-card-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--text-primary);
}
.level-card-desc {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 18px;
}
.level-card-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    background: var(--primary);
    color: #ffffff !important;
    text-decoration: none !important;
    transition: all 0.2s ease;
}
.level-card-btn:hover {
    background: var(--primary-hover);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.matrix-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
}
.matrix-table th {
    background: var(--bg-secondary);
    color: var(--text-primary);
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid var(--border-subtle);
}
.matrix-table td {
    padding: 12px 16px;
    font-size: 13px;
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
    background: var(--bg-card);
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">模块概述与靶场大厅</li>
            </ul>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Hero Banner -->
            <div class="jwt-hero-banner">
                <div class="jwt-hero-title">
                    <i class="fa fa-id-card-o" style="color:#818cf8;"></i> 现代身份认证安全 (Modern Authentication & JWT Security)
                    <span class="jwt-badge-tag">RFC 7519 规范</span>
                    <span class="jwt-badge-tag">OWASP API Security Top 10</span>
                </div>
                <p class="jwt-hero-desc">
                    在微服务架构与云原生应用中，<b>JSON Web Token (JWT)</b> 已成为最主流的无状态身份凭证。然而，当开发者对密码学原理理解不深或验签逻辑存在缺陷时，便会引入灾难性的身份伪造与越权漏洞。本模块深入剖析客户端状态篡改、None 算法绕过、HMAC 弱密钥暴力破解、非对称算法混淆 (RS256 &rarr; HS256) 等前沿攻防场景。
                </p>
            </div>

            <!-- Live JWT Visual Inspector / Debugger -->
            <div class="jwt-debugger-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:17px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                        <i class="fa fa-code" style="color:var(--primary);"></i> 实时 JWT 交互式三段解析器 (Interactive Inspector)
                    </h3>
                    <span style="font-size:12px; color:var(--text-muted);"><i class="fa fa-info-circle"></i> 在下方修改 Raw Token，右侧将实时动态解码</span>
                </div>

                <div class="row">
                    <!-- Left: Encoded Token String -->
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <div class="jwt-segment-box">
                            <div class="jwt-segment-title" style="color:var(--text-primary);">
                                <span>Encoded Token 原始字符串</span>
                                <div>
                                    <button type="button" class="btn btn-xs btn-default" onclick="loadSampleToken('standard')">标准 HS256</button>
                                    <button type="button" class="btn btn-xs btn-default" onclick="loadSampleToken('none')">None 算法</button>
                                    <button type="button" class="btn btn-xs btn-default" onclick="loadSampleToken('rs256')">RS256 证书</button>
                                </div>
                            </div>
                            <textarea id="jwt_raw_input" style="width:100%; height:190px; background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:8px; font-family:monospace; font-size:12.5px; padding:12px; line-height:1.5; color:var(--text-primary); word-break:break-all;" oninput="debugJWT()">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwidXNlcm5hbWUiOiJwaWthY2h1Iiwicm9sZSI6InVzZXIiLCJsZXZlbCI6MiwiaWF0IjoxNTE2MjM5MDIyfQ.4x7H_ZJ8M9u0x1G2v3B4C5D6E7F8G9H0I1J2K3L4M5N</textarea>
                            <div style="margin-top:10px; font-size:12px; display:flex; gap:16px;">
                                <span class="jwt-part-header">■ Header (头部)</span>
                                <span class="jwt-part-payload">■ Payload (载荷)</span>
                                <span class="jwt-part-sig">■ Signature (数字签名)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Decoded JSON Parts -->
                    <div class="col-md-6" style="margin-bottom:15px;">
                        <div class="jwt-segment-box">
                            <div style="margin-bottom:10px;">
                                <div class="jwt-segment-title" style="color:#ef4444;">
                                    <span>HEADER: Algorithm & Token Type</span>
                                    <span style="font-size:11px; font-weight:normal; color:var(--text-muted);">Base64URL Decoded</span>
                                </div>
                                <pre id="jwt_debug_header" style="background:var(--bg-app); border:1px solid rgba(239,68,68,0.25); border-radius:6px; padding:8px 12px; font-size:12px; color:#ef4444; margin:0; min-height:55px;">{ "alg": "HS256", "typ": "JWT" }</pre>
                            </div>
                            <div>
                                <div class="jwt-segment-title" style="color:#a855f7;">
                                    <span>PAYLOAD: Data Claims</span>
                                    <span style="font-size:11px; font-weight:normal; color:var(--text-muted);">Base64URL Decoded</span>
                                </div>
                                <pre id="jwt_debug_payload" style="background:var(--bg-app); border:1px solid rgba(168,85,247,0.25); border-radius:6px; padding:8px 12px; font-size:12px; color:#a855f7; margin:0; min-height:80px;">{ "username": "pikachu", "role": "user", "level": 2 }</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Level Challenge Cards -->
            <div style="margin-bottom:20px;">
                <h3 style="margin:0 0 16px 0; font-size:18px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-shield" style="color:var(--primary);"></i> 现代身份认证实战演练关卡 (Challenge Stages)
                </h3>
            </div>

            <div class="level-grid">
                <!-- Level 1 -->
                <div class="level-card">
                    <div>
                        <div class="level-card-header">
                            <span class="level-num-badge">STAGE 01</span>
                            <span class="level-pts-badge">100 PTS</span>
                        </div>
                        <h4 class="level-card-title">JWT 客户端状态修改与认证绕过</h4>
                        <p class="level-card-desc">
                            分析无状态会话机制。在服务端未对 Token 完整性进行严格校验或仅由前端 Base64 解码信任 Claim 的场景下，篡改角色实现垂直越权。
                        </p>
                    </div>
                    <a href="jwt_login.php" class="level-card-btn">
                        <i class="fa fa-play-circle"></i> 进入关卡演练
                    </a>
                </div>

                <!-- Level 2 -->
                <div class="level-card">
                    <div>
                        <div class="level-card-header">
                            <span class="level-num-badge">STAGE 02</span>
                            <span class="level-pts-badge">150 PTS</span>
                        </div>
                        <h4 class="level-card-title">JWT None 算法免签绕过</h4>
                        <p class="level-card-desc">
                            深入 CVE-2015-9235 漏洞机理。将算法置为 <code>none</code> 并剔除签名段，欺骗缺陷验签库，实现任意身份伪造与 VIP 资源提取。
                        </p>
                    </div>
                    <a href="jwt_none.php" class="level-card-btn">
                        <i class="fa fa-play-circle"></i> 进入关卡演练
                    </a>
                </div>

                <!-- Level 3 -->
                <div class="level-card">
                    <div>
                        <div class="level-card-header">
                            <span class="level-num-badge">STAGE 03</span>
                            <span class="level-pts-badge">200 PTS</span>
                        </div>
                        <h4 class="level-card-title">JWT 弱密钥离线爆破</h4>
                        <p class="level-card-desc">
                            针对 HS256 对称签名机制，结合 Hashcat (模式 16500) 与离线字典暴力破解服务端 Secret Key，重签 Token 获取超级管理员权限。
                        </p>
                    </div>
                    <a href="jwt_weak_secret.php" class="level-card-btn">
                        <i class="fa fa-play-circle"></i> 进入关卡演练
                    </a>
                </div>

                <!-- Level 4 -->
                <div class="level-card">
                    <div>
                        <div class="level-card-header">
                            <span class="level-num-badge">STAGE 04</span>
                            <span class="level-pts-badge">250 PTS</span>
                        </div>
                        <h4 class="level-card-title">JWT 算法混淆 (RS256 &rarr; HS256)</h4>
                        <p class="level-card-desc">
                            高级密码学利用。利用验签库对非对称 RSA 公钥文本的误用，将其降级为 HMAC 对称密钥，在仅持有公钥的情况下自主签发合法凭据。
                        </p>
                    </div>
                    <a href="jwt_key_confusion.php" class="level-card-btn">
                        <i class="fa fa-play-circle"></i> 进入关卡演练
                    </a>
                </div>
            </div>

            <!-- Vulnerability Comparison Matrix & Defense -->
            <div class="jwt-debugger-card">
                <h3 style="margin:0 0 16px 0; font-size:17px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-table" style="color:var(--primary);"></i> JWT 核心安全漏洞威胁矩阵与防御方案
                </h3>

                <div class="table-responsive">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>漏洞类型</th>
                                <th>攻击原理 (Exploitation Principle)</th>
                                <th>典型利用手法</th>
                                <th>防御加固对策 (Defense Strategy)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b>1. 客户端状态直接篡改</b></td>
                                <td>服务端仅依赖前端传递的 Claim 做权限判断，未在服务端二次校验数据库或验签失效。</td>
                                <td>解码修改 <code>role=admin</code>、<code>level=1</code> 并回填 Cookie/Header。</td>
                                <td>服务端必须对签名做强校验；关键权限操作需结合服务端 Session / 数据库双重鉴权。</td>
                            </tr>
                            <tr>
                                <td><b>2. None 算法免签绕过</b></td>
                                <td>验签库未强制限定可用算法列表，信任客户端在 Header 中声明的 <code>alg: none</code>。</td>
                                <td>修改 Header 为 <code>{"alg":"none"}</code>，保留格式 <code>header.payload.</code> 提交。</td>
                                <td>在验签方法中显式指定白名单算法（如 <code>['HS256']</code>），拒绝任何 <code>none</code> 算法。</td>
                            </tr>
                            <tr>
                                <td><b>3. 对称弱密钥爆破</b></td>
                                <td>使用简单单词、常见字典或默认口令作为 HMAC-SHA256 签名的 Secret Key。</td>
                                <td>使用 <code>hashcat -m 16500</code>、<code>jwt_tool</code> 进行离线极速字典碰撞。</td>
                                <td>强制使用高强度随机字符串（至少 256 位 / 32 字节高熵密钥），定期轮转密钥。</td>
                            </tr>
                            <tr>
                                <td><b>4. 算法混淆 (RS256&rarr;HS256)</b></td>
                                <td>非对称架构中，验签函数根据 Header 的 HS256 将原本传入的 RSA 公钥当做 HMAC 对称密钥。</td>
                                <td>提取公开的 <code>public.pem</code>，使用 <code>hash_hmac('sha256', ..., pub_key)</code> 伪造签名。</td>
                                <td>严格绑定验证算法与密钥类型，公钥验签禁止回退到对称 HMAC 算法。</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function b64url_decode_str(str) {
    str = str.replace(/-/g, '+').replace(/_/g, '/');
    while (str.length % 4) {
        str += '=';
    }
    try {
        return decodeURIComponent(escape(window.atob(str)));
    } catch(e) {
        return window.atob(str);
    }
}

function debugJWT() {
    var raw = document.getElementById('jwt_raw_input').value.trim();
    var parts = raw.split('.');
    var headerPre = document.getElementById('jwt_debug_header');
    var payloadPre = document.getElementById('jwt_debug_payload');

    if (parts.length >= 1 && parts[0]) {
        try {
            var h = JSON.parse(b64url_decode_str(parts[0]));
            headerPre.textContent = JSON.stringify(h, null, 2);
        } catch(e) {
            headerPre.textContent = "[Error: Invalid Header Base64]";
        }
    } else {
        headerPre.textContent = "{}";
    }

    if (parts.length >= 2 && parts[1]) {
        try {
            var p = JSON.parse(b64url_decode_str(parts[1]));
            payloadPre.textContent = JSON.stringify(p, null, 2);
        } catch(e) {
            payloadPre.textContent = "[Error: Invalid Payload Base64]";
        }
    } else {
        payloadPre.textContent = "{}";
    }
}

function loadSampleToken(type) {
    var input = document.getElementById('jwt_raw_input');
    if (type === 'standard') {
        input.value = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwidXNlcm5hbWUiOiJwaWthY2h1Iiwicm9sZSI6InVzZXIiLCJsZXZlbCI6MiwiaWF0IjoxNTE2MjM5MDIyfQ.4x7H_ZJ8M9u0x1G2v3B4C5D6E7F8G9H0I1J2K3L4M5N";
    } else if (type === 'none') {
        input.value = "eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwibGV2ZWwiOjEsImlhdCI6MTYyMDAwMDAwMH0.";
    } else if (type === 'rs256') {
        input.value = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InBpa2FjaHUiLCJyb2xlIjoidXNlciIsImVtYWlsIjoicGlrYWNodUBleGFtcGxlLmNvbSJ9.dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk";
    }
    debugJWT();
}

// Initial Run
window.addEventListener('DOMContentLoaded', function() {
    debugJWT();
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
