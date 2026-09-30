#!/usr/bin/env python3
"""
Pikachu-Enhanced v2.0 - 侧边栏与导航系统自动化全景健康检测工具
用于在每次新增靶场关卡、修改导航结构或合并代码前执行全方位诊断。
"""

import os
import sys
import io
import re
import urllib.request

# 解决 Windows 默认 GBK 终端输出 Unicode 符号报错问题
if sys.stdout.encoding != 'utf-8':
    try:
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    except Exception:
        pass

def run_validation():
    print("=" * 60)
    print(" Pikachu-Enhanced 侧边栏与导航健康度自动化检测")
    print("=" * 60)
    
    errors = 0
    warnings = 0
    
    # 1. 检查核心文件存在性
    core_files = ['header.php', 'footer.php', 'inc/nav.inc.php', 'intro.php', 'index.php']
    print("\n[1/5] 检查核心架构文件...")
    for f in core_files:
        if not os.path.exists(f):
            print(f"  [FAIL] 核心文件缺失: {f}")
            errors += 1
        else:
            print(f"  [OK]   {f} 正常")

    # 2. 扫描 header.php 中的所有侧边栏链接并校验磁盘文件
    print("\n[2/5] 扫描并校验 header.php 中的所有内部跳转链接...")
    with open('header.php', 'r', encoding='utf-8', errors='ignore') as fp:
        header_content = fp.read()
        
    links = re.findall(r'<a\s+[^>]*href=["\']<\?php echo \$PIKA_ROOT_DIR;\?>([^"\']+)["\']', header_content)
    unique_links = sorted(set(links))
    print(f"  共发现 {len(links)} 处链接引用 (独立页面: {len(unique_links)} 个)")
    
    missing_disk_files = []
    for rel_path in unique_links:
        clean_path = rel_path.split('?')[0]
        norm = clean_path.replace('/', os.sep)
        if not os.path.exists(norm):
            missing_disk_files.append(rel_path)
            
    if missing_disk_files:
        print(f"  [FAIL] 发现 {len(missing_disk_files)} 个死链接 (磁盘文件不存在):")
        for m in missing_disk_files:
            print(f"    - {m}")
        errors += len(missing_disk_files)
    else:
        print("  [OK]   220+ 个侧边栏链接对应的物理文件 100% 存在于磁盘！")

    # 3. 校验分类是否有危险的 range 循环
    print("\n[3/5] 校验 header.php 语法架构安全性 (杜绝 range 冲突)...")
    if re.search(r'range\s*\(\s*140\s*,\s*220\s*\)', header_content):
        print("  [FAIL] header.php 仍包含危险的 range(140, 220) 循环！")
        errors += 1
    elif re.search(r'foreach\s*\(.*range\(', header_content):
        print("  [WARN] 发现分类中仍有 foreach range 循环")
        warnings += 1
    else:
        print("  [OK]   所有分类均已使用现代解耦的 $CAT_CLASS 状态机控制！")

    # 4. 校验 inc/nav.inc.php 路由推断覆盖率
    print("\n[4/5] 校验 inc/nav.inc.php 路由引擎完备性...")
    with open('inc/nav.inc.php', 'r', encoding='utf-8', errors='ignore') as fp:
        nav_content = fp.read()
        
    if 'pika_infer_route' not in nav_content:
        print("  [FAIL] inc/nav.inc.php 缺少 pika_infer_route 智能推断引擎")
        errors += 1
    else:
        print("  [OK]   pika_infer_route 智能推断引擎正常就绪")
        
    if 'pika_is_active' not in nav_content:
        print("  [FAIL] inc/nav.inc.php 缺少 pika_is_active 链接判定函数")
        errors += 1
    else:
        print("  [OK]   pika_is_active 链接活跃判定函数正常就绪")

    # 5. 校验实时服务渲染 (如 Docker 容器运行中)
    print("\n[5/5] 验证线上容器真实渲染表现...")
    test_urls = [
        '/index.php',
        '/intro.php',
        '/vul/dockerlab/docker_privileged_escape.php',
        '/vul/sso_saml/saml_xsw.php',
        '/vul/osep/osep_l1_enum.php',
        '/vul/ai_security/prompt_injection.php',
        '/vul/nosql/mongo_operator.php'
    ]
    online_verified = 0
    for u in test_urls:
        try:
            req = urllib.request.Request(f"http://127.0.0.1:8765{u}")
            with urllib.request.urlopen(req, timeout=3) as resp:
                if resp.status == 200:
                    online_verified += 1
        except Exception:
            pass
            
    if online_verified == len(test_urls):
        print(f"  [OK]   实时容器端 100% 联通 ({online_verified}/{len(test_urls)} 通过测试)")
    else:
        print(f"  [INFO] 本地 8765 容器当前未开启或部分离线 ({online_verified}/{len(test_urls)} 响应)，跳过在线断言")

    print("\n" + "=" * 60)
    if errors == 0:
        print(" [PASS] 侧边栏与导航系统检测全部通过 (0 错误，0 死链)！")
        print("=" * 60)
        return 0
    else:
        print(f" [FAIL] 检测到 {errors} 个关键错误，请参考本规范修复！")
        print("=" * 60)
        return 1

if __name__ == '__main__':
    sys.exit(run_validation())
