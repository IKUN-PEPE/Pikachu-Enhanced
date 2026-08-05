# 📋 Pikachu-Enhanced v2.0 实施级施工流程与版本演进总大纲

> 适用项目：Pikachu-Enhanced 漏洞练习平台  
> 适用环境：Windows / Linux / Docker Desktop / PHP 7.4+ / MySQL  
> 文档用途：项目整体开发落地、模块扩充、施工约束与验收标准说明  
> 最新状态：**全站 UI 重构完成、30+ 漏洞概述工作流上线、内网 AD 域安全纲领部署完成**

---

## 0. 总体版本演进与施工成果

Pikachu-Enhanced v2.0 已完成从传统 PHP 练习靶场向**现代全栈网络安全与攻防演练平台**的全面升级。施工记录如下：

```text
阶段一：基础 UI 与侧边栏重构 (完成)
- 侧边栏拓宽至 230px，全局引入 pika_unified.css 双主题 CSS 变量引擎
- 全局导航数组 $ACTIVE[0..250] 重新规划，跑通自动化高亮路由

阶段二：RCE 与经典 Web 漏洞扩充 (完成)
- RCE 模块扩展 WAF 绕过 (rce_bypass.php)、SSTI 模板注入 (rce_ssti.php)、无回显盲注 (rce_blind.php)
- 彻底解决 xss_dom 与 xss_dom_x 的导航索引冲突与页面容器缺失问题

阶段三：全站 30+ 漏洞概述页面可视化升级 (完成)
- 覆盖 13 个经典 Web 漏洞 + 17 个 2.0 前沿漏洞大类
- 统一替换为 Hero 渐变横幅 + 4 步攻击流程图谱 + 特性卡片 + 直达关卡入口

阶段四：内网与 AD 域安全知识体系与 Web 展现 (完成)
- 新增 vul/ad_security/ 模块
- 上线 内网 6 大知识体系全景页 (ad_knowledge.php) 与 AD 域靶场 5 阶段搭建蓝图 (ad_lab_setup.php)
- 上线 WS-Management (winrm.php)、Kerberos 票据 (kerberos.php) 与横向移动 (ad_lateral.php) 演示关卡

阶段五：仓库结构清洗与规范化 (完成)
- 彻底清理全项目 .DS_Store、废弃 test_*.php / verify*.sh / 100MB+ zip 垃圾文件
- 归档设计文档至 docs/ 目录，代码库同步提交 Git
```

---

## 1. 核心开发与边界约束硬规则

在对平台进行增量开发时，必须严格遵守以下施工铁律：

### 1.1 前端设计与主题规则
1. **不要硬编码像素样式**：必须使用 `var(--bg-primary)`, `var(--bg-card)`, `var(--text-primary)`, `var(--border-color)` 等 CSS 变量，确保白天/黑夜模式无缝切换。
2. **严禁破坏侧边栏展开**：任何新增或修改的页面，必须在头部显式声明：
   ```php
   $ACTIVE = array_fill(0, 250, '');
   $ACTIVE[parent_id] = 'active open';
   $ACTIVE[child_id] = 'active';
   ```
3. **保留提示按钮 (Tips)**：页面中点一下提示按钮必须绑定 `a[data-toggle="popover"]`，在 CSS 中已被全局渲染为右上角的 💡 胶囊按钮。

### 1.2 Docker Lab 与受控命令安全规则
1. 只允许内置白名单模板（位于 `vul/dockerlab/templates/`）。
2. 不允许用户自定义 `image` / `command` / `volume` / `privileged`。
3. 容器强制打上 `pikachu.lab=true` 标签，命令执行严格使用 `escapeshellarg()` 过滤。
4. 所有改变状态的操作必须走 POST 请求并校验 CSRF Token。

---

## 2. 新增模块施工清单与文件映射

### 2.1 2.0 前沿漏洞大类清单

| 模块目录 | 核心功能与关卡文件 | 说明 |
| :--- | :--- | :--- |
| `vul/dockerlab/` | `dockerlab.php`, `k8s_token_escape.php` | Docker 容器安全与 K8s SA Token 提取 |
| `vul/api_security/` | `api.php`, `bola.php`, `mass_assignment.php` | OWASP Modern API BOLA 与批量赋值 |
| `vul/ai_security/` | `ai_security.php`, `prompt_injection.php`, `llm_xss.php`, `llm_plugin_rce.php`, `llm_data_leakage.php` | AI & LLM Prompt 越狱、插件 RCE 与系统提示词泄露 |
| `vul/jwt/` | `jwt.php`, `jwt_weak_secret.php`, `jwt_none.php`, `jwt_key_confusion.php` | JWT 弱密钥离线爆破、Alg None 与算法混淆 |
| `vul/defense/` | `defense.php` | 蓝队防守模式与 WAF/RASP 实时攻击审计 |
| `vul/frontend/` | `frontend.php`, `prototype_pollution.php`, `dom_clobbering.php` | 原型链污染与 DOM Clobbering |
| `vul/grpc/` | `grpc.php`, `grpc_auth_bypass.php` | gRPC 未授权访问与认证绕过 |
| `vul/http_smuggling/` | `http_smuggling.php`, `cl_te.php` | HTTP 请求走私 (CL.TE) |
| `vul/java_unserialize/` | `java_unserialize.php`, `JavaUnserializeHandler.java` | Java 原生反序列化与 Fastjson 利用链 |
| `vul/phar/` | `phar.php`, `phar_unserialize.php`, `evil_payload.jpg` | Phar 伪协议反序列化 |
| `vul/race_condition/` | `race_condition.php`, `gift_card.php` | 并发竞争与余额扣减漏洞 |
| `vul/ssrf/` | `ssrf_cloud.php`, `ssrf_gopher_redis.php` | 云元数据提取与 Gopher 协议打 Redis |
| `vul/ad_security/` | `ad_security.php`, `ad_knowledge.php`, `ad_lab_setup.php`, `winrm.php`, `kerberos.php`, `ad_lateral.php` | 内网与 AD 域安全 6 大体系、5 阶段搭建蓝图及 WinRM/Kerberos 演示 |

---

## 3. 验收与质量检查清单 (Q/A Checklist)

任何后续新增的代码修改均需通过以下测试：

1. **侧边栏展开测试**：点击任意菜单，侧边栏保持正确展开，当前关卡高亮，不大类合退。
2. **主题切换测试**：点击右上角 🌙/☀️ 按钮，页面背景与所有文字、卡片颜色实时平滑切换，无失真或白块。
3. **概述工作流显示**：进入任意 `vul/<module>/<module>.php` 概述页面，确认包含 Hero Banner、4 步流程链与快捷卡片。
4. **代码库干净度**：无 `.DS_Store` 垃圾文件，无过时 `test_*.php` 临时文件。
