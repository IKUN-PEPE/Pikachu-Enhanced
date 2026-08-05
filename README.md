<p align="center">
  <img src="https://img.shields.io/badge/Web%20Security-v2.0%20Enhanced-blue" alt="Web Security Lab" />
  <img src="https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.0%2B-777BB4" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-required-orange" alt="MySQL" />
  <img src="https://img.shields.io/badge/Docker%20%26%20K8s-Lab%20v2-cyan" alt="Docker Lab" />
  <img src="https://img.shields.io/badge/AD%20Security-Intranet-purple" alt="AD Security" />
  <img src="https://img.shields.io/badge/Use-Local%20Only-red" alt="Local Only" />
</p>

<h1 align="center">⚡ Pikachu Enhanced v2.0</h1>

<p align="center">
  面向现代 Web 安全、云原生/容器逃逸、AI 大模型安全、内网 AD 域安全与主动防御演练的综合性综合漏洞靶场平台。
</p>

> “如果你想搞懂一个漏洞，比较好的方法是：先自己制造出这个漏洞，再利用它，最后再修复它。”

---

## 📌 项目简介

`Pikachu Enhanced v2.0` 是基于经典 Pikachu 演练平台深度增强改造的综合型靶场。原版 Pikachu 侧重基础 `PHP + MySQL` 经典 Web 漏洞，本 2.0 增强版在此基础上重构了 **全局现代化 230px 宽版 UI** 与 **黑夜/白天双主题引擎**，并将全站 30+ 漏洞大类升格为带有 **4 步可视化工作流演进图谱** 的交互大厅。

此外，v2.0 拓展了涵盖 **云原生与 Docker/K8s 逃逸**、**AI 大模型与 Prompt 越狱**、**OWASP API 安全**、**JWT 算法混淆**、**蓝队 WAF/RASP 主动防御** 以及 **内网与 Active Directory 域安全体系大纲** 的前沿关卡。

---

## 🧭 目录

- [项目简介](#-项目简介)
- [安全声明](#️-安全声明)
- [v2.0 核心亮点与增强特性](#-v20-核心亮点与增强特性)
- [漏洞模块全景目录](#-漏洞模块全景目录)
- [🌐 内网与 Active Directory 域安全](#-内网与-active-directory-域安全)
- [云原生 Docker Lab & K8s 逃逸](#-云原生-docker-lab--k8s-逃逸)
- [干净整洁的 8 大核心目录结构](#-干净整洁的-8-大核心目录结构)
- [部署与使用指南](#-部署与使用指南)

---

## ⚠️ 安全声明

> 本项目包含大量**故意设计的漏洞与攻击载荷演示**，请务必只在本地、授权、隔离环境中使用。

- ✅ 仅限本地学习、授权测试、安全研究和教学演示
- ❌ 严禁部署到公网或生产环境
- ❌ 严禁将任何演练端口或 Docker/K8s 入口暴露给不可信网络
- ⚠️ 任何在非授权环境中的非法使用风险由使用者自行承担

---

## ✨ v2.0 核心亮点与增强特性

1. **🎨 230px 现代化侧边栏与 CSS 变量双主题引擎**：
   - 全局支持 `data-theme="light"` 与 `data-theme="dark"` 实时平滑切换。
   - 彻底优化侧边栏展开逻辑与三层导航路由高亮数组 (`$ACTIVE[0..250]`)。
   - 修复右上角悬浮提示按钮，渲染为现代化的 **💡 小灯泡胶囊 Tips 按钮**。

2. **📊 全站 30+ 漏洞大类 4 步可视化工作流 Hub**：
   - 每一个漏洞分类主页均包含 **Hero 渐变横幅**、**4 步骤攻击演进流程图**（探测 ➔ 构造 ➔ 触发 ➔ 控制）、**高危函数卡片** 以及 **快捷关卡直达入口**。

3. **🤖 2026 前沿 AI 大模型与智能体安全关卡**：
   - 包含 Prompt 注入越狱 (`prompt_injection.php`)、LLM XSS 渲染 (`llm_xss.php`)、AI Agent 工具 RCE 劫持 (`llm_plugin_rce.php`) 以及系统提示词与密钥提取 (`llm_data_leakage.php`)。

4. **🌐 独立大类：内网与 Active Directory 域安全体系**：
   - 包含 6 大知识体系全景图 (`ad_knowledge.php`) 与 5 阶段 AD 靶场搭建实战蓝图 (`ad_lab_setup.php`)，并提供 WinRM 探测、Kerberos 票据攻击及 Pass-the-Hash 演练。

---

## 🗺️ 漏洞模块全景目录

### 一、 经典 Web 攻防演练 (Classic Web)
- 🔐 **Brute Force (暴力破解)**：表单爆破、服务端验证码未销毁绕过、前端 JS 校验绕过、Anti-Token 爆破。
- 🧨 **XSS (跨站脚本)**：反射型 (GET/POST)、存储型、DOM 型 (DOM-x)、百打/过滤/href/js 输出绕过。
- 🔁 **CSRF (跨站伪造)**：GET 型、POST 型、Anti-CSRF Token 防御与绕过。
- 🧬 **SQL Injection (SQL 注入)**：数字/字符型、搜索/XX型、报错注入、盲注 (布尔/时间)、Wide-Byte 宽字节、Http Header 注入。
- 💻 **RCE (远程代码/命令执行)**：exec/eval 注入、WAF 规则绕过 (`rce_bypass.php`)、SSTI 模板注入 (`rce_ssti.php`)、无回显盲注 (`rce_blind.php`)。
- 📂 **File Inclusion (文件包含)**：本地文件包含 (LFI)、远程文件包含 (RFI)、PHP 伪协议 (`php://filter`, `data://`)。
- 📥 **Unsafe File Download (文件下载)**：任意文件读取与目录跨越下载。
- 📤 **Unsafe File Upload (文件上传)**：前端检查绕过、MIME 校验伪造、getimagesize() 图片马、Zip Slip 压缩包解压穿透 (`zip_slip.php`)。
- 🧱 **Over Permission (越权)**：水平越权、垂直越权、现代 RESTful BOLA 越权。
- 🗂️ **Directory Traversal (目录遍历)**：路径穿越与目录索引。
- 🕵️ **Information Leakage (敏感信息泄露)**：报错信息泄露、前端硬编码与泄露。
- 🧪 **PHP Unserialize (PHP 反序列化)**：魔术方法与 POP 链演练。

### 二、 云原生与容器安全 (Cloud Native)
- 🐳 **Docker Container Security**：容器逃逸、Docker Socket 敏感挂载、Privileged 特权模式逃逸。
- ☸️ **Kubernetes Security**：K8s Service Account Token 提取与集群接管 (`k8s_token_escape.php`)。
- ☁️ **Cloud Storage Security**：阿里云 OSS / AWS S3 Bucket 未授权访问与盲取 (`cloud_storage.php`)。

### 三、 现代认证、API 与协议安全 (Modern Auth & Protocols)
- 🔑 **OWASP API Security**：BOLA 对象级越权 (`bola.php`)、Mass Assignment 批量赋值 (`mass_assignment.php`)。
- 🎟️ **JWT Security**：弱密钥离线爆破 (`jwt_weak_secret.php`)、Alg None 签名剥离 (`jwt_none.php`)、RS256➔HS256 公钥算法混淆 (`jwt_key_confusion.php`)。
- 🌐 **Modern Frontend**：PostMessage 消息劫持 (`postmessage.php`)、CORS 跨域反射读取 (`cors_demo.php`)、原型链污染 (`prototype_pollution.php`)、DOM Clobbering。
- ⚡ **Advanced Protocols**：GraphQL 全量 Schema 检索与未授权、gRPC 接口测试 (`grpc_auth_bypass.php`)、HTTP 请求走私 CL.TE (`cl_te.php`)、NoSQL 盲注、OAuth State 缺失绕过 (`state_bypass.php`)、Phar 伪协议反序列化 (`phar_unserialize.php`)、Serverless Lambda 环境变量泄露 (`lambda_env_leak.php`)、Web Cache Deception 缓存投毒 (`cache_deception.php`)、Webhook SSRF (`webhook_ssrf.php`)、WebSocket CSWSH / SQLi (`cswsh.php`)。

### 四、 蓝队主动防御与日志审计 (Blue Team Defense)
- 🛡️ **Active Defense (defense.php)**：WAF 规则特征拦截分析、RASP 运行时字节码插桩原理与 HTTP 审计日志取证还原。

---

## 🌐 内网与 Active Directory 域安全

在 `vul/ad_security/` 目录下上线了全新的知识体系与实战蓝图展示大厅：

1. **内网 6 大知识体系全景 (`ad_knowledge.php`)**：
   - 基础侦察 ➔ 域认证机制 (NTLM / Kerberos) ➔ 高危攻击 (黄金/白银票据、委派、Kerberoasting、DCSync、ZeroLogon) ➔ 横向移动 (WMI/WinRM/PtH) ➔ 权限维持 ➔ 蓝队 EDR/SIEM 审计。
2. **AD 域靶场搭建 5 阶段蓝图 (`ad_lab_setup.php`)**：
   - STAGE 01 环境规划 ➔ STAGE 02 基础设施部署 ➔ STAGE 03 漏洞场景配置 ➔ STAGE 04 辅助设施与快照 ➔ STAGE 05 闭环复盘。
3. **3 个演示关卡**：
   - WS-Management (WinRM 5985/5986) 探测 (`winrm.php`)
   - Kerberos 协议攻击与 Kerberoasting 模拟 (`kerberos.php`)
   - AD 域内横向移动与 Pass-the-Hash 传递 (`ad_lateral.php`)

---

## 📁 干净整洁的 8 大核心目录结构

```text
Pikachu-Enhanced/
├── assets/         # CSS 主题引擎 (pika_unified.css)、字体与 JS 库
├── docker/         # Docker Lab 与 Java 容器镜像构建文件
├── docs/           # 系统设计与实施级施工文档
├── inc/            # 后端配置 (config.inc.php)、数据库连接与上传函数库
├── pkxss/          # XSS 接收后台与 Payload 管理平台
├── test/           # 文件包含与上传使用的合法测试 Payload (phpinfo.txt)
├── vul/            # 全站 30+ 漏洞大类与演练关卡
├── wiki/           # 架构图示与项目维基
├── docker-compose.yml
├── Dockerfile
├── header.php / footer.php / index.php / intro.php
├── install.php
└── README.md
```

---

## 🚀 部署与使用指南

### 方式一：经典 PHP + MySQL 环境部署 (如 XAMPP / PhpStudy)
1. 将项目源码克隆或复制到 Web 根目录（如 `www` 或 `htdocs`）。
2. 在浏览器中访问 `http://127.0.0.1/Pikachu-Enhanced/install.php`。
3. 点击 **“安装/初始化数据库”**，系统将自动创建数据库与所有演练数据。

### 方式二：Docker Compose 一键启动
```bash
# 启动 Web 与 MySQL 数据库容器
docker-compose up -d

# 浏览器访问地址:
http://localhost:8000
```
