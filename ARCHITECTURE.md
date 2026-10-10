# Pikachu-Enhanced 系统架构设计规范 (System Architecture Specification)

> **版本**：v3.0.0 (Enterprise Architecture Edition)  
> **设计哲学**：非破坏性脆弱性保留 (Non-Destructive Vulnerability Preservation) + 现代化工程治理 (Modern Engineering Governance)

---

## 一、架构设计背景与挑战

Pikachu-Enhanced 是业界领先的综合性全技术栈网络安全与红蓝对抗靶场，涵盖 **47 大类漏洞域**与 **226+ 个独立攻防演练靶标**。

在传统 Web 安全靶场向现代化研发流程演进的过程中，存在一个**核心工程悖论**：
* **业务靶标特性**：靶场关卡本身必须包含真实、无衰减的脆弱代码（如反序列化、SQL 盲注、内存破坏、模板注入、原型链污染等），不能被安全静态扫描和重构逻辑误杀修复；
* **底座工程要求**：承载靶场的基础设施、路由分发、配置管理、Flag 检验、数据库连接池及 CI/CD 流程必须符合最严苛的现代化软件工程规范（PSR-4、PSR-12、12-Factor App、Docker 容器编排）。

为此，Pikachu-Enhanced v3.0 独创并全面落地了**双引擎架构 (Dual-Engine Architecture)**。

---

## 二、双引擎架构图谱 (Dual-Engine Architecture)

```
+-----------------------------------------------------------------------------------+
|                        Pikachu-Enhanced v3.0 系统总体架构                          |
+-----------------------------------------------------------------------------------+
                                          |
                        +-----------------+-----------------+
                        |                                   |
              [HTTP / Web Client]                 [API / Automated Scanners]
                        |                                   |
                        +-----------------+-----------------+
                                          |
                      +-------------------+-------------------+
                      |   Modern Core Gateway (bootstrap.php) |
                      +-------------------+-------------------+
                                          |
         +--------------------------------+--------------------------------+
         |                                                                 |
         v                                                                 v
+-------------------------------+                         +---------------------------------+
|   Modern OOP Engine (`src/`)  |                         |   Legacy Lab Bridge (`vul/`)    |
+-------------------------------+                         +---------------------------------+
| 1. Core                       |                         | 1. OWASP Top 10 核心靶场        |
|    - App (Kernel & Container) |                         | 2. 现代云原生与微服务安全       |
|    - Config (12-Factor .env)  |                         | 3. 现代身份认证 (OAuth/JWT/SAML)|
|    - Dotenv (Environment)     |                         | 4. 新型协议 (走私/gRPC/Webhook) |
| 2. Database                   |  Bridge Compatibility   | 5. AI 与大模型应用安全          |
|    - DatabaseManager (Dual)   |<=======================>| 6. 蓝队防守与实战防御           |
|    - PDO (Safe Prepared DAL)  |                         | 7. 内网与 Active Directory      |
|    - mysqli (Vulnerable DAL)  |                         | 8. OSEP 内网穿透与红队评估      |
| 3. Challenge Engine           |                         | 9. OSWE 高级代码审计白盒利用    |
|    - ChallengeInterface       |                         | 10. OSED 二进制漏洞逆向利用     |
|    - AbstractChallenge        |                         +---------------------------------+
|    - ChallengeRegistry        |
| 4. Security & Flag Engine     |
|    - FlagEngine (HMAC / Crypt)|
|    - Sanitizer (Defense Diff) |
| 5. Http & View                |
|    - Request / Response       |
|    - Router / Layout          |
+-------------------------------+
```

---

## 三、核心模块与职责划分

### 1. 自动装载与命名空间 (PSR-4 Autoloader)
* **根命名空间**：`Pikachu\` 映射至 `src/`；
* **测试命名空间**：`Pikachu\Tests\` 映射至 `tests/`；
* **零依赖独立运行**：内置 [`src/Autoloader.php`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/src/Autoloader.php)，即使在完全离线或未预装 Composer 的受限宿主机/隔离靶机中，也能 100% 原生自动解析并装载所有面向对象类文件。

### 2. 十二要素配置系统 (12-Factor App & Config)
* **环境驱动**：所有核心配置（数据库主机/端口/密码、应用密钥、调试模式）优先从系统环境变量或 `.env` 文件读取；
* **向后兼容**：自动兼容声明 `DBHOST`、`DBUSER`、`DBPW`、`DBNAME`、`DBPORT` 等旧式全局常量，确保历史代码 0 修改无缝运行。

### 3. 双轨数据库驱动 (Dual-Engine DatabaseManager)
* **PDO 引擎**：用于现代化系统功能、用户鉴权、动态 Flag 结算与诊断报告，采用严格预编译与异常机制，杜绝平台自身被未授权提权；
* **mysqli 兼容桥**：用于传统 SQL 注入（宽字节注入、二次注入、堆叠注入、报错注入等）教学实验，保证教学靶标环境 100% 贴近实战。

### 4. 靶标契约化管理 (Challenge Interface Contract)
所有新增靶标遵循统一的接口契约规范：
```php
namespace Pikachu\Challenge;

interface ChallengeInterface
{
    public function getId(): string;
    public function getTitle(): string;
    public function getCategory(): string;
    public function getDifficulty(): string;
    public function getOwaspCategory(): string;
    public function getPoints(): int;
    public function getDescription(): string;
    public function getHints(): array;
    public function getMitigation(): string;
    public function verifyFlag(string $flag): bool;
}
```
通过 [`ChallengeRegistry`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/src/Challenge/ChallengeRegistry.php) 集中编目注册，支持元数据查询、知识库导出及进度自动化审计。

### 5. 密码学动态 Flag 引擎 (Security FlagEngine)
* **HMAC 动态靶标**：支持基于 `HMAC-SHA256(challenge_id + user_session, secret)` 动态派生个性化 Flag，防止队伍间直接抄袭 Flag；
* **静态 Flag 兼容**：兼容 OffSec（OSEP/OSWE/OSED）与传统 CTF 的固定 Flag 验证逻辑，会话级状态持久化。

---

## 四、容器化与多服务拓扑

系统基于 Docker & Docker Compose 实现自动化多容器编排：
1. **`pikachu-web`** (PHP 8.2 + Apache)：核心应用与前端展示，集成 `docker.sock` 挂载以支撑 Docker 逃逸实验；
2. **`pikachu-db`** (MySQL 8.0 / utf8mb4)：自动化数据持久化，通过 `docker-entrypoint-initdb.d/init.sql` 实现**零接触开箱即用 (Zero-Touch Setup)**；
3. **`pikachu-java`** (Java 8/17 Microservice)：独立 Java 反序列化与 RMI/JDWP 实验容器；
4. **`goad-ad`** (Active Directory 靶场网络)：外挂内网域控与多机器域森林渗透拓扑。

---

## 五、持续集成与代码质量门禁 (CI/CD Quality Gates)

GitHub Actions 自动化流水线（[`.github/workflows/ci.yml`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/.github/workflows/ci.yml)）强制执行以下门禁：
1. **PSR-12 编码风格校验**：执行 `phpcs --standard=PSR12`；
2. **PHPStan 静态分析**：执行 `phpstan analyse --level=5`；
3. **自动化单元测试与集成测试**：执行 `tests/run_tests.php`，验证全站 226+ 内部路由无死链；
4. **容器镜像构建与健康检查探针**：自动启动 Docker Compose 并断言 HTTP 200 响应。
