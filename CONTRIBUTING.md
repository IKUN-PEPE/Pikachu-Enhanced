# Pikachu-Enhanced 开发者贡献指南 (Contributing Guide)

感谢你关注并有意向为 **Pikachu-Enhanced** 贡献代码！  
为了保持项目最严苛的现代化软件工程质量标准，请在提交代码前仔细阅读本指南。

---

## 一、本地开发环境准备

### 1. 基础环境需求
* **PHP**：>= 8.0（推荐 8.2+）
* **MySQL**：>= 8.0（默认字符集 `utf8mb4`）
* **Python**：>= 3.8（用于执行自动化质量审计器）
* **Docker & Docker Compose**（可选，容器化调试推荐）

### 2. 快速启动开发环境
```bash
# 1. 克隆代码仓库
git clone https://github.com/IKUN-PEPE/Pikachu-Enhanced.git
cd Pikachu-Enhanced

# 2. 从模板复制环境变量配置文件
cp .env.example .env

# 3. 使用 Docker Compose 一键启动靶场编排
docker compose up -d

# 4. 浏览器访问测试
http://localhost:8765
```

---

## 二、5 分钟新增一个现代化靶标关卡

Pikachu-Enhanced v3.0 采用接口契约设计。如果你想新增一个现代化关卡，只需遵循以下标准流程：

### 1. 继承 `AbstractChallenge` 基础类
在 `src/Challenge/` 或特定模块目录下新建类：
```php
<?php

declare(strict_types=1);

namespace Pikachu\Challenge;

class MyNewVulnerabilityChallenge extends AbstractChallenge
{
    public function __construct()
    {
        $this->id = 'my_vuln_l1';
        $this->title = '新型安全漏洞实战演练';
        $this->category = 'cloud';
        $this->difficulty = 'Intermediate';
        $this->owaspCategory = 'A01:2021-Broken Access Control';
        $this->points = 150;
        $this->description = '本关卡演示新型鉴权混淆漏洞...';
        $this->hints = [
            '检查请求 Header 中的鉴权凭证字段',
            '尝试通过特定编码绕过前置网关校验',
        ];
        $this->mitigation = '应在服务端使用强类型严格比较并部署全局统一的鉴权拦截器。';
        $this->expectedFlag = 'flag{Pikachu_My_New_Vuln_Done}';
    }
}
```

### 2. 注册关卡到 `ChallengeRegistry`
在 [`ChallengeRegistry.php`](file:///C:/Users/Administrator/VScode/Pikachu-Enhanced/src/Challenge/ChallengeRegistry.php) 中注册新关卡实例：
```php
$registry->register(new MyNewVulnerabilityChallenge());
```

---

## 三、代码工艺与质量门禁 (Quality Gates)

在提交任何代码（Pull Request）前，必须在本地通过所有自动化质量测试：

### 1. 运行企业级测试套件
```bash
# 运行综合质量套件 (支持任意无 PHP 环境的宿主机)
python test_runner.py

# 或者在容器/PHP 环境下运行单元测试
php tests/run_tests.php
```

### 2. PSR-12 代码风格检查与修复
所有 `src/` 与 `tests/` 目录下的代码必须严格遵循 PSR-12 规范：
```bash
# 检查风格
composer phpcs

# 自动修复格式
composer phpcs-fix
```

### 3. PHPStan 静态类型分析
```bash
composer phpstan
```

---

## 四、Git 提交信息规范 (Conventional Commits)

本项目强制使用符合业界标准的约定式提交（Conventional Commits）：

| 前缀 | 说明 | 示例 |
| :--- | :--- | :--- |
| `feat:` | 新增功能或新靶标关卡 | `feat(challenge): add GraphQL depth limit bypass level` |
| `fix:` | 修复已知 Bug 或环境错误 | `fix(auth): resolve JWT algorithm none verification bypass` |
| `style:` | 样式统一、排版或 UI 微调 | `style(sidebar): unify active pill and chevron transitions` |
| `refactor:` | 代码重构（不改变现有行为） | `refactor(db): migrate connection manager to dual-engine DAL` |
| `test:` | 新增或更新单元/集成测试 | `test(router): add route dispatch assertion cases` |
| `docs:` | 文档更新 | `docs(readme): add enterprise architecture diagram and badges` |

---

## 五、Pull Request 流程

1. Fork 本仓库并基于 `main` 分支创建特性分支（如 `feature/cloud-challenge`）；
2. 编写代码并编写对应的单元测试；
3. 运行 `python test_runner.py` 确保测试 100% 通过（0 errors）；
4. 提交清晰规范的 Commit 并推送到你的 Fork 仓库；
5. 在 GitHub 发起 Pull Request，描述你的改动内容、影响面及测试结果；
6. 等待 CI 自动化流水线（GitHub Actions）全绿通过后，主理人将进行代码审查并合入。
