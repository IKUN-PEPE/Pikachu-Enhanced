# Repository Guidelines

## 项目结构与模块组织
仓库是一个基于 `PHP + MySQL` 的漏洞练习平台。首页与公共布局位于根目录：`index.php`、`header.php`、`footer.php`、`install.php`。公共配置和函数放在 `inc/`，包括数据库连接、验证码、上传和通用辅助函数。漏洞模块集中在 `vul/`，按类别拆分目录，例如 `vul/sqli/`、`vul/xss/`、`vul/jwt/`。静态资源位于 `assets/`，XSS 管理后台在 `pkxss/`，示例与杂项文件在 `test/`、`wiki/`。

## 构建、测试与开发命令
本地开发以 PHP 运行环境为主，不依赖 `npm` 或 Composer。

```powershell
php -S 127.0.0.1:8080
```

在当前目录启动内置服务器，适合快速查看页面。

```powershell
php -l .\index.php
php -l .\vul\jwt\jwt_login.php
```

执行 PHP 语法检查；修改页面或模块后至少检查受影响文件。

```powershell
docker build -t pikachu .
docker run -p 80:80 pikachu
```

按仓库内 `Dockerfile` 构建和运行容器环境。

## 代码风格与命名约定
沿用现有 PHP 风格：4 空格缩进，文件名小写，模块目录使用语义化名称，例如 `sessionfixation`、`hostheader`。新增页面优先保持同目录命名模式，如 `xxx.php`、`xxx_login.php`、`xxx_admin.php`。避免大规模重构公共文件；优先做最小范围修改，保持教学漏洞逻辑可复现。

## 测试要求
仓库当前没有 PHPUnit 测试套件，不要编造测试命令。提交前至少完成三项检查：`php -l` 语法检查、页面入口手工访问、受影响漏洞链路手工复现。若修改登录、跳转、Cookie、Session 或响应头，需额外验证输出顺序和浏览器行为。

## 提交与 Pull Request 要求
历史提交同时存在英文短句和中文说明，建议继续使用“简短祈使句 + 明确范围”，例如 `fix jwt cookie path` 或 `修复 cors 演示页说明`。PR 需要写清变更模块、复现步骤、验证方式；若改动页面交互或教学文案，附截图更合适。不要把压缩包、环境缓存或临时文件一并提交，例如 `pikachu.7z`、`.DS_Store`。

## 侧边栏与新增关卡规范 (Sidebar & Navigation Guidelines)
每次新增漏洞靶场关卡、模块或修改侧边栏导航时，**必须严格遵循以下三步规范，严禁随意臆造数字编号**：
1. **关卡文件编写**：关卡头部使用标准 `include_once $PIKA_ROOT_DIR . 'header.php';`，由导航解析器自动判定状态。严禁硬编码未分配的 `$ACTIVE[n]` 魔法数字。
2. **侧边栏代码 (`header.php`)**：
   - 关卡链接使用 `pika_is_active(...)` 函数：
     `<li class="<?php echo pika_is_active('vul/your_dir/your_file.php'); ?>"><a href="<?php echo $PIKA_ROOT_DIR;?>vul/your_dir/your_file.php">...</a></li>`
   - 大类展开使用 `$CAT_CLASS['classic'|'cloud'|'ai'|'proto'|'defense'|'ad'|'osep'|'oswe'|'osed']`。严禁使用 `foreach (range(...))` 循环判定！
3. **路由注册 (`inc/nav.inc.php`)**：在 `pika_get_all_routes()` 中追加该路径（如 forgot 登记，`pika_infer_route` 亦会根据目录智能兜底推断）。
4. **提交前自动化验证**：每次修改后必须运行 `python validate_sidebar.py` 确保 0 错误、0 死链。详细规范参见 `.agents/rules/sidebar_navigation.md`。

## 安全与配置提示
这是故意保留漏洞的靶场，不要把练习模块改造成安全产品。配置变更优先放在本地环境或容器中验证；数据库连接以 `inc/config.inc.php` 为准，初始化流程通过 `install.php` 完成。
