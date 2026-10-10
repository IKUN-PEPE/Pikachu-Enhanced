<?php

declare(strict_types=1);

namespace Pikachu\Challenge;

/**
 * Global Challenge Metadata & Registry Catalog.
 */
class ChallengeRegistry
{
    /**
     * @var array<string, array{name: string, icon: string, description: string, tag: string}>
     */
    private array $categories = [
        'classic' => [
            'name' => '经典 Web 攻防演练',
            'icon' => '🏛️',
            'description' => 'OWASP Top 10 核心靶场：SQLi、XSS、CSRF、RCE、文件包含、文件上传、SSRF 等',
            'tag' => 'web'
        ],
        'cloud' => [
            'name' => '现代云原生与微服务安全',
            'icon' => '☁️',
            'description' => 'Docker 逃逸、云存储桶接管、GraphQL 深度注入、Serverless 安全',
            'tag' => 'cloud'
        ],
        'auth' => [
            'name' => '现代身份认证与访问控制',
            'icon' => '🔑',
            'description' => 'OAuth2.0、SAML 2.0、JWT 弱口令与 None 算法、MFA 竞争绕过、垂直越权',
            'tag' => 'auth'
        ],
        'proto' => [
            'name' => '现代网络协议与新型数据接口',
            'icon' => '🌐',
            'description' => 'HTTP 请求走私 (CL-TE/TE-CL)、gRPC 反射、Webhook SSRF、WebSocket 劫持',
            'tag' => 'proto'
        ],
        'ai' => [
            'name' => 'AI 与大模型应用安全',
            'icon' => '🤖',
            'description' => 'Prompt Injection 提示词注入、Agent 权限逃逸、大模型 SSRF 与敏感信息提取',
            'tag' => 'ai'
        ],
        'defense' => [
            'name' => '蓝队防守与实战防御',
            'icon' => '🛡️',
            'description' => 'WAF 绕过对抗、动态蜜罐识别、全流量告警研判、自动化防御阻断',
            'tag' => 'defense'
        ],
        'ad' => [
            'name' => '内网与 Active Directory 域安全',
            'icon' => '🌐',
            'description' => 'Kerberos 黄金/白银票据、Kerberoasting、AS-REP Roasting、ACL 访问控制链',
            'tag' => 'ad'
        ],
        'osep' => [
            'name' => 'OSEP 内网穿透与红队评估',
            'icon' => '🎯',
            'description' => 'Win32 API 免杀、AMSI/UAC 绕过、进程镂空、DNS 隐蔽隧道、MSSQL 深度利用',
            'tag' => 'offsec'
        ],
        'oswe' => [
            'name' => 'OSWE 高级代码审计与白盒利用',
            'icon' => '🔍',
            'description' => '数据流污点分析、反序列化 POP 链、SSTI 沙箱逃逸、原型链污染、盲注脚本化',
            'tag' => 'offsec'
        ],
        'osed' => [
            'name' => 'OSED 二进制漏洞利用与逆向',
            'icon' => '🔬',
            'description' => 'x86 汇编自定义 Shellcode、ROP 链构造、DEP/ASLR 绕过、格式化字符串读写',
            'tag' => 'offsec'
        ],
    ];

    /**
     * @var array<string, ChallengeInterface>
     */
    private array $registeredChallenges = [];

    public function register(ChallengeInterface $challenge): void
    {
        $this->registeredChallenges[$challenge->getId()] = $challenge;
    }

    public function get(string $id): ?ChallengeInterface
    {
        return $this->registeredChallenges[$id] ?? null;
    }

    /**
     * @return array<string, ChallengeInterface>
     */
    public function all(): array
    {
        return $this->registeredChallenges;
    }

    /**
     * @return array<string, array{name: string, icon: string, description: string, tag: string}>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function count(): int
    {
        return count($this->registeredChallenges);
    }
}
