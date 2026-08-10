<?php
include_once '../../inc/config.inc.php';
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[250] = 'active open';  // parent menu index
$ACTIVE[282] = 'active';         // this page index
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';
$correct_flag = 'flag{OSEP_L10_DLL_Reflect_ProcessHollow_Inject}';
$flag_msg = '';
if (isset($_POST['check_flag'])) {
    if (trim($_POST['user_flag']) === $correct_flag) {
        $_SESSION['osep_flags']['flag10'] = true;
        $flag_msg = '<div class="alert alert-success" style="border-radius:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.5);color:#fff;">✅ 恭喜通关！您已成功掌握进程注入与镂空技术。</div>';
    } else {
        $flag_msg = '<div class="alert alert-danger" style="border-radius:8px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.5);color:#fff;">❌ Flag 错误，请检查输入！</div>';
    }
}
?>
<style>
/* dark theme with module color */
.ctf-stage-header { background: var(--bg-card); border-radius: 14px; padding: 25px 30px; margin-bottom: 25px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.step-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:22px; }
.step-title { font-size:16px; font-weight:700; color:var(--text-primary); margin-top:0; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
.step-num { background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; width:26px; height:26px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
.cmd-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; color: var(--text-primary); margin: 12px 0; overflow-x: auto; line-height: 1.8; }
.cmd-box .comment { color:#64748b; }
.cmd-box .flag-text { color:#fbbf24; font-weight:bold; }
.highlight-box { background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.25); border-radius:8px; padding:14px 18px; margin:12px 0; font-size:13px; color:var(--text-secondary); line-height:1.7; }
.flag-submit-area { background:var(--bg-card); border:2px dashed rgba(168,85,247,0.4); border-radius:12px; padding:24px; margin-top:25px; text-align:center; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-nav { background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; text-decoration: none; }
.btn-nav:hover { background: #a855f7; color: white; text-decoration: none; }
</style>

<div class="main-content"><div class="main-content-inner"><div class="page-content">

    <div class="ctf-stage-header">
        <h2 style="margin-top:0; font-size:24px; font-weight:bold; display:flex; align-items:center; gap:10px;">
            <i class="fa fa-cogs"></i> 关卡 2: 进程注入与进程镂空技术研究
        </h2>
        <p style="margin-bottom:0; opacity:0.9;">250 PTS · 探索逃避检测的高级内存执行手段。</p>
    </div>

    <?= $flag_msg ?>

    <div class="step-box">
        <div class="step-title"><span class="step-num">1</span> 经典 DLL 注入原理</div>
        <p>将恶意 DLL 注入到正常进程中，以其身份执行代码。这通常用于维持权限和逃避基于进程的检测。</p>
        <div class="cmd-box">
            <span class="comment">// 经典的 DLL 注入 API 调用链</span><br>
            OpenProcess(PROCESS_ALL_ACCESS, FALSE, targetPID);<br>
            VirtualAllocEx(hProcess, NULL, pathLen, MEM_COMMIT | MEM_RESERVE, PAGE_READWRITE);<br>
            WriteProcessMemory(hProcess, pMem, dllPath, pathLen, NULL);<br>
            CreateRemoteThread(hProcess, NULL, 0, (LPTHREAD_START_ROUTINE)LoadLibraryA, pMem, 0, NULL);<br>
        </div>
        <p>这种方式的缺点是强依赖于 <code>LoadLibraryA</code>，且 DLL 文件必须落地到目标系统的磁盘上，极易被静态特征查杀。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">2</span> 反射式 DLL 注入（Reflective DLL Injection）</div>
        <p>为了解决传统 DLL 注入需文件落地的问题，Stephen Fewer 提出了反射式 DLL 注入。它允许通过内存直接加载 DLL，无需调用操作系统的加载器。</p>
        <div class="highlight-box">
            核心思想：<br>
            注入带有 <code>ReflectiveLoader</code> 导出函数的特殊 DLL 载荷。<br>
            这个函数具备解析 PE 文件头、分配内存空间、处理重定位表和导入表的能力，本质上相当于在内存中实现了一个微型的 <code>LoadLibrary</code>。
        </div>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">3</span> 进程镂空（Process Hollowing）原理</div>
        <p>进程镂空技术通过创建一个挂起的正常进程，将其内存中合法代码卸载，替换为恶意代码后再恢复执行。</p>
        <div class="cmd-box">
            <span class="comment">// 进程镂空流程</span><br>
            CreateProcess(..., CREATE_SUSPENDED, ...);<br>
            ZwUnmapViewOfSection(hProcess, baseAddress); <span class="comment">// 卸载原 PE 内容</span><br>
            VirtualAllocEx(...); WriteProcessMemory(...); <span class="comment">// 写入新 PE</span><br>
            SetThreadContext(...); <span class="comment">// 恢复入口点 EAX/RCX</span><br>
            ResumeThread(hThread); <span class="comment">// 恢复线程，执行恶意代码</span><br>
        </div>
        <p>这样在任务管理器中，只会看到一个合法的进程（如 <code>svchost.exe</code>），但其实质已经被替换。</p>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">4</span> Shellcode 宿主进程选择</div>
        <p>选择注入或镂空的目标进程非常关键。常见选择包括 <code>notepad.exe</code>, <code>svchost.exe</code>, 或 <code>explorer.exe</code>。</p>
        <p>为了避免被 EDR 拦截，高级行动中通常会寻找那些：</p>
        <ul style="color:var(--text-secondary); margin-bottom:0;">
            <li>没有被 EDR 注入检测钩子（Hook）的白名单进程。</li>
            <li>具有微软合法签名（Signed Binary）的进程。</li>
            <li>部分情况下，会尝试利用或绕过 PPL（Protected Process Light）机制进行操作。</li>
        </ul>
    </div>

    <div class="step-box">
        <div class="step-title"><span class="step-num">5</span> 防御检测方法</div>
        <p>防守方可以通过多种手段检测进程注入行为：</p>
        <div class="highlight-box">
            • <b>内存扫描</b>: 扫描进程内存，寻找异常的 RWX (可读可写可执行) 内存区域。<br>
            • <b>PE 映射比对</b>: 检查内存中映射的 PE 模块是否在磁盘上有对应的合法文件。<br>
            • <b>线程分析</b>: 分析线程的起始地址是否位于已知的合法模块之外。<br>
            • <b>行为监控日志</b>: 监控 Sysmon Event 8 (CreateRemoteThread) 等高危行为日志。<br>
        </div>
    </div>

    <div class="flag-submit-area">
        <h4 style="color:var(--text-primary); margin-bottom: 20px;">🚩 提交您的 Flag</h4>
        <form method="POST" style="max-width: 500px; margin: 0 auto; display: flex; gap: 10px;">
            <input type="text" name="user_flag" class="form-control" placeholder="输入 Flag，例如：flag{...}" style="flex: 1; border-radius: 8px; border: 1px solid rgba(168,85,247,0.3); background: var(--bg-card); color: var(--text-primary);">
            <button type="submit" name="check_flag" class="btn btn-nav" style="border-radius: 8px;">验证</button>
        </form>
        <div style="margin-top:20px; font-family:monospace; font-size:12px; color:var(--text-secondary);">
            实验环境提示：本关 Flag 已隐写在下方字符串中：<br>
            <span style="color:#64748b;">$flag = "flag{OSEP_L10_DLL_Reflect_ProcessHollow_Inject}";</span>
        </div>
    </div>

    <div class="nav-buttons">
        <a href="osep_l9_office_macro.php" class="btn-nav"><i class="fa fa-arrow-left"></i> 上一关：L9 Office 宏</a>
        <a href="osep_l11_amsi_bypass.php" class="btn-nav">下一关：L11 AMSI 绕过 <i class="fa fa-arrow-right"></i></a>
    </div>

</div></div></div>
<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
