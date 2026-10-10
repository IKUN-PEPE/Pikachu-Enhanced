# Pikachu-Enhanced 更新日志 (Changelog)

本项目遵循 [语义化版本 2.0.0 (Semantic Versioning)](https://semver.org/lang/zh-CN/) 规范。

---

## [3.0.0] - 2026-10-10

### 🚀 现代化架构与全量重构 (Major Architecture Refactoring)

#### 🌟 核心引擎与架构革新 (Core Architecture)
- **双引擎架构 (Dual-Engine Architecture)**：创新性地设计了面向对象的现代内核（`src/`）与传统脆弱性靶标（`vul/`）无缝并存的双引擎体系，兼顾现代化软件工程质量与 100% 真实的漏洞演练还原度；
- **PSR-4 自动装载机制**：引入遵循 PSR-4 规范的自动加载系统，并内置零外部依赖的纯 PHP 回退装载器（[`src/Autoloader.php`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/src/Autoloader.php)），无需联网运行 `composer install` 即可开箱即用；
- **12-Factor 应用配置**：支持 `.env` 环境变量驱动配置（`Dotenv` + `Config` 单例管理），标准化数据库主机/端口/密钥/调试参数，并向下兼容原有常量声明；
- **双轨数据库驱动 (DatabaseManager)**：构建统一的数据访问层（DAL），集成现代安全 PDO 连接与传统 mysqli 注入实验桥，支持连接池缓存、故障转移与健康心跳；
- **挑战关卡契约体系 (Challenge Contract)**：定义标准化的 `ChallengeInterface` 与 `AbstractChallenge` 基类，支持关卡元数据（难度、OWASP 分类、分值、解析、防御方案、Flag 验证）；
- **密码学动态 Flag 引擎 (FlagEngine)**：支持基于 HMAC-SHA256 的用户个性化动态 Flag 派发与验证，杜绝批量抄袭，完美适配 CTF 比赛模式。

#### 🐳 容器化与基础设施加固 (DevOps & Docker)
- **开箱即用零接触启动 (Zero-Touch Boot)**：新增 [`docker/mysql/init.sql`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/docker/mysql/init.sql)，在容器初次启动时由 MySQL 官方镜像机制全自动建表并灌入 6 个核心表数据，免除人工点击 `install.php` 的繁琐操作；
- **增强型健康检查探针 (Healthcheck Probes)**：在 `docker-compose.yml` 中为 `web` 与 `db` 服务增加双向心跳检测与重启策略；
- **环境变量动态绑定**：全量支持从 `.env` 动态继承端口与数据库配置，避免端口冲突。

#### 🧪 质量保障与 CI/CD 自动化 (Quality & Tooling)
- **自动化测试套件**：引入多层级自动化测试体系，包含单元测试（`ConfigTest`, `FlagEngineTest`, `RouterTest`, `SanitizerTest`）与集成测试（`SidebarRouteTest` 覆盖全站 226+ 物理页面）；
- **双轨测试运行器**：支持 PHP 原生测试套件（`tests/run_tests.php`）与跨平台宿主机测试套件（`test_runner.py`）；
- **PSR-12 与静态分析规约**：制定严格的 `phpcs.xml`、`phpstan.neon`（Level 5）与 `.editorconfig` 规范；
- **GitHub Actions 流水线**：新建 [`.github/workflows/ci.yml`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/.github/workflows/ci.yml)，集成代码风格检查、静态分析、测试套件运行与 Docker 构建验证。

#### 🎨 视觉系统与交互优化 (UI & UX)
- **侧边栏设计系统统一**：彻底根除 Ace Admin 历史伪元素导致的弯曲括号噪点，统一胶囊药丸选中态（Active Pill）、平滑顺时针 90° 旋转箭头以及 272px 黄金排版宽度，100% 杜绝中文长标题单字掉行；
- **全站 226 关卡 0 死链保证**：通过解耦的状态机路由机制，彻底消除了历史版本中新增关卡时侧边栏跳转错位的问题。
