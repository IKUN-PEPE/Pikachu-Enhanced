---
trigger: always_on
description: Pikachu-Enhanced 侧边栏导航规范与新增关卡长期记忆指南
---

# Pikachu-Enhanced 侧边栏导航架构与新增关卡规范

本规范适用于所有在 `Pikachu-Enhanced` 项目中开发、扩展新漏洞靶场或修改现有导航菜单的开发者与 AI Agent。

---

## 1. 核心架构与原理 (为什么曾经频繁出现跳转故障？)

### 历史根本原因总结
1. **多重编号冲突**：早期版本为每个菜单分配数字索引 `$ACTIVE[n]`。随着项目膨胀至 170+ 关卡，不同开发者/Agent 各自挑选编号，导致 12 组以上严重重叠（如 Docker Lab 的 210~218 与 SSO/SAML、对象存储 OSS、gRPC、Webhook、MFA 完全冲突），访问一个关卡导致多个无关大类强行展开并高亮。
2. **粗暴的范围遍历**：旧版在 `header.php` 中使用 `range(140, 220)` 判定云原生大类，但该区间直接覆盖了 AI 安全 (165~182)、前沿协议 (183~201)、蓝队防守 (220)，导致只要进入任何 AI 或协议关卡，云原生大类始终无法收拢。
3. **缺乏单一事实来源**：页面端、`header.php`、`inc/nav.inc.php` 三处状态不同步。

### 当前重构后的统一自动化机制
- **单一事实源**：`inc/nav.inc.php` 作为全站导航解析中枢，核心根据 `$_SERVER['SCRIPT_NAME']`（当前实际访问脚本）毫秒级计算活跃项。
- **状态完全解耦**：
  - `$CAT_CLASS['classic' | 'auth' | 'business' | 'cloud' | 'proto' | 'ai' | 'defense' | 'ad' | 'osep' | 'oswe' | 'osed']`：控制 11 大主分类展开。
  - `$MOD_CLASS['burteforce' | 'jwt' | 'logic' | 'dockerlab' | ...]`：控制二级折叠菜单展开。
  - `pika_is_active('vul/xxx/yyy.php')`：基于相对路径精确高亮叶子节点。
- **智能目录推断兜底 (Zero-Maintenance Fallback)**：`pika_infer_route()` 能够根据文件所在目录自动归类。即使新增关卡时**完全忘记**在路由表中注册，系统也会自动展开正确的分类并高亮，绝对不会跨类误展开！

---

## 2. 新增关卡/页面的标准开发流程 (严格准则)

当需要新增漏洞页面、CTF 关卡或实验室环境时，**必须且仅需遵循以下三步**：

### 步骤一：创建关卡 PHP 文件 (`vul/<category>/<module>/<filename>.php`)
在关卡头部**不需要随意臆造数字编号**，标准结构如下：

```php
<?php
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
// 引入全局头部 (头部会自动调用 nav.inc.php 计算当前关卡导航状态)
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<!-- 你的关卡主体 HTML / 业务代码 -->

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
```

*(注意：若为兼容极老代码，设置 `$ACTIVE = array_fill(0, 500, '');` 亦可，导航引擎会自动处理)*

---

### 步骤二：在 `header.php` 对应位置添加侧边栏链接

找到对应的分类或二级菜单 `<ul>` 内部，使用现代标准辅助函数 `pika_is_active(...)`，**严禁使用任何魔法数字编号**：

```html
<li class="<?php echo pika_is_active('vul/your_dir/your_file.php'); ?>">
    <a href="<?php echo $PIKA_ROOT_DIR;?>vul/your_dir/your_file.php">
        🚩 关卡标题 / 描述
    </a>
    <b class="arrow"></b>
</li>
```

---

### 步骤三：在 `inc/nav.inc.php` 中登记精确路由 (推荐标准)

打开 `inc/nav.inc.php`，在 `pika_get_all_routes()` 返回的数组中追加该路径（按首字母排序）：

```php
'vul/your_dir/your_file.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => null, 'parent' => 140],
```
- `cat`：对应 11 大分类 ID 之一 (`classic` / `auth` / `business` / `cloud` / `proto` / `ai` / `defense` / `ad` / `osep` / `oswe` / `osed`)
- `mod`：二级模块别名（如 `dockerlab`、`jwt`、`sqli`；若属于 OSEP/OSWE 等单层大类，则填 `null`）
- `leaf`：填 `null` 即可（使用路径精确匹配）
- `parent`：对应模块父级 ID（如 140）

---

## 3. 严格禁止事项 (Anti-Patterns)

1. ❌ **禁止在 `header.php` 中编写 `foreach (range(...))` 循环判定**：
   所有分类标签必须严格使用 `<li class="<?php echo !empty($CAT_CLASS['cat_id']) ? $CAT_CLASS['cat_id'] : ''; ?>">`。
2. ❌ **禁止私自发明新的 `$ACTIVE` 数字索引**：
   禁止随意写 `$ACTIVE[365]` 等无法溯源的数字，所有新关卡一律使用相对 URL 路径绑定。
3. ❌ **禁止在 `<a>` 标签中编写硬编码绝对路径**：
   一律使用 `<?php echo $PIKA_ROOT_DIR;?>vul/...` 格式。

---

## 4. 验证命令

每次修改或新增内容后，必须运行自动化校验工具：
```bash
python validate_sidebar.py
```
确保所有 220+ 链接全部有效、零死链、零语法错误且路由树同步！
