# 📋 Pikachu-Enhanced v2.0 全局系统设计与 2.0 靶场架构设计文档

- 📊 报告类型：系统架构与实施级设计总纲
- 👤 适用项目：Pikachu-Enhanced 漏洞练习平台 (v2.0 增强版)
- 🕐 最新更新时间：2026-08-05
- 🏷️ 当前状态：v2.0 核心功能与 30+ 漏洞大类界面升级完成，内网 AD 域安全大纲上线
- 📎 设计边界：PHP 7.4/8.0+、MySQL、Windows/Linux/Docker 运行环境、现代前端 CSS 变量主题引擎

---

## 1. 📊 方案摘要与整体架构概览

Pikachu-Enhanced v2.0 是在传统 Pikachu 平台基础上的重大重构与前沿拓展版本。系统保持了经典的“单体 PHP 页面 + 统一包含头尾 (`header.php` / `footer.php`) + 模块化目录 (`vul/<module>/`)”架构，引入了全新的 **全局现代化视觉系统**、**深色/浅色双主题切换引擎**、**30+ 漏洞大类 4 步可视化工作流概述** 以及 **内网与 AD 域安全体系**。

```mermaid
flowchart TD
    A[用户访问 Pikachu-Enhanced 平台] --> B[加载 header.php & pika_unified.css]
    B --> C{路由选择 (Level 1~3 $ACTIVE 数组)}
    C -->|系统主页| D[index.php 系统介绍与说明]
    C -->|全局图鉴| E[intro.php 全局漏洞图鉴 v2.0]
    C -->|经典 Web 攻防| F[13 个传统漏洞大类 & 工作流 Hub]
    C -->|云原生与前沿| G[Docker Lab / K8s / API / AI / JWT / 蓝队防守]
    C -->|内网 & AD 域安全| H[ad_security.php 知识体系与 5 阶段靶场大纲]
```

---

## 2. 🎨 全局 UI/UX 与布局重构设计

### 2.1 230px 现代化侧边栏与导航路由机制 (`$ACTIVE`)
- **侧边栏拓宽**：侧边栏宽度设为 `230px`，去除了老旧 Ace Admin 的小图标伪元素与点状链接线。
- **三层导航路由控制**：
  - **Level 1 (大类)**：通过 `header.php` 动态区间循环（如 `range(230, 240)`）判定任意子关卡激活即赋予顶级 `active open`。
  - **Level 2 (模块标题)**：子页面显式设置 `$ACTIVE[parent_id] = 'active open'`。
  - **Level 3 (关卡)**：子页面显式设置 `$ACTIVE[child_id] = 'active'`。
- **提示按钮 (Tips) 精准显示**：隐藏了顶部冗余面包屑链接 (`ul.breadcrumb`)，保留外部 `.breadcrumbs` 容器并将其渲染为右上角的 **💡 胶囊样式提示按钮**。

### 2.2 双主题色彩系统 (`pika_unified.css`)
支持数据属性 `data-theme="light"` 与 `data-theme="dark"` 实时切换，所有卡片、表格与按钮均绑定 CSS 自定义变量：
- `--bg-primary`: 主背景色 (`#f8fafc` / `#0f172a`)
- `--bg-card`: 卡片与容器背景色 (`#ffffff` / `#1e293b`)
- `--cat-ai`, `--cat-cloud`, `--cat-auth`, `--cat-proto`, `--cat-classic`: 5 大主题大类专属渐变色。

---

## 3. 🛡️ 全局 30+ 漏洞大类“概述”页面可视化标准

全站所有漏洞大类（如 `rce.php`, `xss.php`, `sqli.php`, `burteforce.php`, `csrf.php`, `dockerlab.php`, `api.php`, `ai_security.php`, `jwt.php`, `logic.php` 等）均重构为统一的 4 板块交互大厅：

1. **Hero 渐变横幅 (Hero Header)**：包含漏洞权威分类 Badge 与 OWASP 风险描述。
2. **4 步骤攻击演进流程图谱 (Visual Attack Workflow)**：卡片化展现 Step 1 探测 ➔ Step 2 载荷构造 ➔ Step 3 触发执行 ➔ Step 4 权限控制。
3. **分栏特性卡片 (Categorization Cards)**：对比漏洞子类型，高亮底层高危函数（如 `system()`, `eval()`, `unserialize()`）。
4. **快捷关卡入口 (Interactive Lab Shortcuts)**：带有悬停微上浮效果的关卡直达卡片。

---

## 4. 🌐 内网与 Active Directory 域安全大纲设计 (`vul/ad_security/`)

为响应前沿攻防演练需求，新增了 `🌐 内网与 AD 域安全` 大类，包含：

### 4.1 6 大知识体系全景展示 (`ad_knowledge.php`)
1. **基础侦察与信息收集**：主机探测、域控制器/域用户定位、BloodHound / PowerView 工具链。
2. **域内认证机制**：NTLM Challenge/Response、NTLM Relay、Kerberos AS/TGS/AP 阶段机制。
3. **域内高危攻击手法**：黄金/白银/钻石票据、委派攻击 (Unconstrained/Constrained/RBCD)、Kerberoasting、DCSync、ZeroLogon (CVE-2020-1472)、NoPac、GPO & ACL 滥用。
4. **横向移动**：WMI、PsExec、WinRM (5985/5986)、Pass-the-Hash (PtH)、Pass-the-Ticket (PtT)。
5. **权限维持**：Skeleton Key 万能密码、DSRM 密码利用、SSP 内存后门。
6. **防御与检测**：Windows 事件日志 (4624/4768/4769) 审计、蜜罐 SPN 诱捕、Tier 模型分层防护与 SIEM/EDR 检测。

### 4.2 AD 域漏洞靶场搭建 5 阶段蓝图 (`ad_lab_setup.php`)
- **STAGE 01 环境规划**：VMware / VirtualBox / Proxmox 虚拟化与三层网段隔离。
- **STAGE 02 基础环境部署**：Windows Server 2019/2022 AD DS 域控、成员服务器、Win10/11 客户端与 Kali 攻击机。
- **STAGE 03 漏洞场景配置**：SPN 绑定、委派场景、RBCD、ACL 错配与 CVE 漏洞环境。
- **STAGE 04 辅助设施**：ELK / Winlogbeat 日志审计与多节点快照策略。
- **STAGE 05 练习闭环**：攻击链撰写、BloodHound 路径复盘与 SIEM 攻防校验。

---

## 5. 🐳 Docker Lab 靶场编排中心设计架构

### 5.1 受控安全边界 (Security Rules)
- 仅允许内置 JSON 白名单模板（位于 `vul/dockerlab/templates/`）。
- 严禁用户自定义 `image`, `command`, `volume`, `privileged`, `--network host`。
- 所有容器强制添加 `pikachu.lab=true` 标签，服务端控制层只允许管理带此标签的受控容器。
- 所有控制动作走 POST 请求与 CSRF Token 校验。

### 5.2 目录与库模块设计
```text
vul/dockerlab/
├── dockerlab.php             # Docker Lab 概述与工作流主页
├── k8s_token_escape.php      # K8s Service Account Token 逃逸实战关卡
├── dockerlab_center.php      # 靶场控制中心面板
├── dockerlab_action.php      # 容器启动/停止/重启/删除 POST 处理
├── dockerlab_logs.php        # 受控日志回显
├── dockerlab_lib.php         # Docker CLI 命令组装与白名单校验库
└── templates/                # 静态白名单 JSON 模板
```

---

## 6. 📂 项目标准文件目录结构

仓库保持了高度整洁的 8 大核心目录体系：
- `assets/`：全站主题 CSS、FontAwesome 字体与图像资源
- `docker/`：Java 容器与 Kubernetes 模拟镜像构建文件
- `docs/`：系统设计与实施级施工文档（本目录）
- `inc/`：后端配置与核心函数库 (`config.inc.php`, `uploadfunction.php`)
- `pkxss/`：XSS 平台与数据接收后台
- `test/`：文件包含/上传测试 Payload (`phpinfo.txt`, `yijuhua.txt`)
- `vul/`：30+ 漏洞大类演练关卡与概述 Hub
- `wiki/`：项目相关架构图示与维基文档
