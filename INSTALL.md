# Pikachu-Enhanced v3.0 企业级网络安全靶场安装与部署指南

本文档提供了 Pikachu-Enhanced v3.0 的全面安装指南，总结了目前支持的所有可行安装方案，您可以根据自己的使用习惯选择最适合的方式。

---

## 方案一：Docker Compose 现代化极速部署（推荐，开箱即用）

v3.0 版本现已集成 **零接触数据库初始化 (Zero-Touch Boot)** 与 **12-Factor .env 配置**，无需手动执行繁琐建表步骤。

### 1. 环境准备
- 操作系统：Windows / Linux / macOS 均可。
- 前置软件：安装了 **Docker** 和 **Docker Compose**。

### 2. 配置与一键拉起
```bash
# 1. 复制环境变量配置文件
cp .env.example .env

# 2. 一键拉起微服务集群
docker compose up -d
```

### 3. 访问与演练
- 容器初次启动时，MySQL 官方容器会自动执行 `docker/mysql/init.sql` 装载所有核心表及 CTF 靶标数据。
- 稍等 10~15 秒等待健康检查通过后，浏览器直接访问：[http://127.0.0.1:8765](http://127.0.0.1:8765)。
- 直接开启 226+ 个靶标关卡的实战攻防演练！

---

## 方案二：基于源代码使用 PhpStudy / XAMPP 物理部署（传统方式）

如果您更习惯原生的 PHP 物理运行环境，也可以直接通过集成环境进行部署。

### 1. 环境准备
- 下载安装 **PhpStudy** (小皮面板) 或 **XAMPP**。
- 环境要求：`PHP 7.4+` (推荐 PHP 8.0+) + `MySQL 5.7/8.0`。
- PHP 扩展要求：必须开启 `mysqli`, `pdo_mysql`, `curl`, `sockets`, `zip`, `gd`, `bcmath`。

### 2. 部署代码
1. 将 Pikachu-Enhanced 整个源代码文件夹拷贝到 PhpStudy 的 `WWW` 目录，或者 XAMPP 的 `htdocs` 目录。
2. 打开 PhpStudy / XAMPP，启动 Apache/Nginx 和 MySQL 服务。

### 3. 配置数据库密码
打开 `inc/config.inc.php`，根据您本地 MySQL 的实际密码修改以下两项（默认是 root / root）：
```php
define('DBUSER', 'root');
define('DBPW', 'root');
```

### 4. 访问与初始化
- 浏览器访问：`http://127.0.0.1/Pikachu-Enhanced/install.php` (路径视您放置的目录名而定)。
- 点击“一键初始化数据库”。

> **⚠️ 注意事项：** 物理环境部署下，`Docker Lab`（云原生相关漏洞靶场）、Java 反序列化靶场等依赖独立容器环境的关卡将无法正常演示真实效果，仅能查看前端页面。

---

## 方案三：基于源代码手动构建 Docker 镜像（适合二次开发）

如果您有 GitHub 上的完整源代码包，并希望自己从头编译 Docker 镜像。

### 1. 运行 Docker Compose
在代码根目录下打开命令行，执行：
```bash
docker-compose build
docker-compose up -d
```
Docker 会自动读取 `Dockerfile` 构建包含当前 PHP 代码库的 Apache 镜像，并拉取 MySQL 和 Java 容器。

### 2. 开发模式说明
默认的 `docker-compose.yml` 中配置了 `volumes: - ./:/var/www/html`。
这意味着宿主机的代码目录直接挂载到了容器内。当您在宿主机修改了 PHP 代码，刷新浏览器即可立即生效，非常适合靶场二次开发。

---

## FAQ 常见问题

**Q: 点击初始化数据库报错 "MySQL 连接失败"？**
A: 请检查 MySQL 容器是否启动成功，或者物理环境的 `inc/config.inc.php` 中的账密是否与实际数据库匹配。Docker 部署下，请确保 `PIKACHU_DB_HOST` 环境变量指向了正确的服务名（默认是 `db`）。

**Q: 容器启动了但是无法访问 8765 端口？**
A: 请检查本地是否已有其他服务占用了 `8765` 端口。可以在 `docker-compose.yml` 中将 `8765:80` 修改为 `8888:80` 然后重新启动。

**Q: Docker Lab 的部分命令执行失败或者无法操作容器？**
A: Docker 容器靶场涉及到真实的提权与逃逸，`docker-compose.yml` 中已配置挂载 `/var/run/docker.sock`。如果是 Linux 宿主机，请确保 `/var/run/docker.sock` 的权限允许容器内的 `www-data` 用户访问。
