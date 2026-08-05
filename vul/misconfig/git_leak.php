<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[196] = 'active open';
$ACTIVE[199] = 'active';
$ACTIVE[196] = 'active open';
$ACTIVE[199] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$git_output = "";
$step_mode = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['git_action'] ?? '';
    
    if ($action === 'config') {
        $step_mode = "config";
        $git_output = "[core]\n\trepositoryformatversion = 0\n\tfilemode = true\n\tbare = false\n\tlogallrefupdates = true\n" .
                      "[remote \"origin\"]\n\turl = https://github.com/pikachu-sec/pikachu-enhanced-enterprise.git\n\tfetch = +refs/heads/*:refs/remotes/origin/*\n" .
                      "[branch \"master\"]\n\tremote = origin\n\tmerge = refs/heads/master";
    } else if ($action === 'logs') {
        $step_mode = "logs";
        $git_output = "0000000000000000000000000000000000000000 e8a123f456... root <root@pikachu.local> 1780000000 +0000\tclone: from https://github.com...\n" .
                      "e8a123f456... c9b876d543... dev_user <dev@pikachu.local> 1780000100 +0000\tcommit: feat: updated homepage design\n" .
                      "c9b876d543... a7f654e321... admin <admin@pikachu.local> 1780000500 +0000\tcommit: CRITICAL: removed hardcoded backdoor & master flag from auth.php";
    } else if ($action === 'githack') {
        $step_mode = "githack";
        $git_output = "=== [Simulating GitHack: git show a7f654e321...] ===\n\n" .
                      "commit a7f654e32188990011223344556677889900aabb\n" .
                      "Author: admin <admin@pikachu.local>\n" .
                      "Date:   Wed Jun 30 15:30:22 2026 +0800\n\n" .
                      "    CRITICAL: removed hardcoded backdoor & master flag from auth.php\n\n" .
                      "diff --git a/auth.php b/auth.php\n" .
                      "index b10a8db..c9b876d 100644\n" .
                      "--- a/auth.php\n" .
                      "+++ b/auth.php\n" .
                      "@@ -15,7 +15,7 @@ function verify_user(\$username, \$password) {\n" .
                      "-    if (\$username === 'super_admin' && \$password === 'FLAG{GIT_REPOSITORY_SOURCE_CODE_LEAK_EXPLOITED}') {\n" .
                      "-        return true; // DEBUG BACKDOOR\n" .
                      "-    }\n" .
                      "+    // Backdoor removed for production security\n" .
                      "     return db_query_auth(\$username, \$password);\n" .
                      " }";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="misconfig.php">配置泄露与调试监控</a></li>
                <li class="active">.git 源码历史泄露</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>🐱 .git 版本仓库源码与历史提交记录泄露 (Git Repository Leakage)</h2>
                <p>在开发和上线流程中，部分工程师为了方便在线上服务器执行 <code>git pull</code> 来更新代码，直接将整个 Git 工作区（包括核心隐藏目录 <code>.git/</code>）同步到了 Web 服务器的静态根目录中。</p>
                <p>由于 Web 服务器未对 <code>/.git/</code> 路径做访问限制，攻击者可以通过自动化渗透工具（如 <b>GitHack、GitExtractor、dvcs-ripper</b>）下载整个 <code>.git</code> 目录并执行 <code>git reset --hard</code> 还原整站源代码！更可怕的是，即便开发者已经在当前代码中删除了密码或硬编码后门，攻击者依然可以通过查看 <code>git log</code> 历史提交差异（Diff），找回被删除的敏感凭证！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-git-square"></i> 模拟 Git 泄露侦察与历史追踪</h4>
                        <form method="POST">
                            <div class="list-group">
                                <button type="submit" name="git_action" value="config" class="list-group-item <?php echo $step_mode==='config'?'active':''; ?>">
                                    <h4 class="list-group-item-heading"><i class="fa fa-info-circle"></i> 步骤 1：访问 /.git/config 验证漏洞存在</h4>
                                    <p class="list-group-item-text">获取 Git 远程仓库地址、分支配置与代码归属指纹。</p>
                                </button>
                                <button type="submit" name="git_action" value="logs" class="list-group-item <?php echo $step_mode==='logs'?'active':''; ?>" style="margin-top:8px;">
                                    <h4 class="list-group-item-heading"><i class="fa fa-list-ul"></i> 步骤 2：下载 /.git/logs/HEAD 提交日志</h4>
                                    <p class="list-group-item-text">查看全部 Commit 历史记录，寻找“删除密码”、“移除后门”等关键提交说明。</p>
                                </button>
                                <button type="submit" name="git_action" value="githack" class="list-group-item <?php echo $step_mode==='githack'?'active':''; ?>" style="margin-top:8px; background-color:#d9534f; color:#fff;">
                                    <h4 class="list-group-item-heading"><i class="fa fa-bug"></i> 步骤 3：模拟 GitHack 还原历史 Diff 找回 Flag！</h4>
                                    <p class="list-group-item-text" style="color:#ffe;">通过 git diff 比对历史 Commit，还原被管理员在提交历史中尝试删除的超级后门密码。</p>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-terminal"></i> 渗透工具或浏览器响应内容展示</h4>
                        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; font-family: monospace; min-height: 260px; max-height: 480px; overflow-y: auto;">
                            <?php if (!empty($git_output)) {
                                $color = "#50fa7b";
                                if ($step_mode === 'githack') $color = "#ff5555";
                                echo "<pre style='background:transparent; color:" . $color . "; border:none; margin:0; padding:0;'>" . htmlspecialchars($git_output) . "</pre>";
                            } else { ?>
                                <span style="color: #6a9955;">// [Target Domain: http://pikachu.enhanced.local/.git/]</span><br/>
                                <span style="color: #6a9955;">// 请在左侧依次点击步骤 1、2、3，体验从发现 .git 暴露到使用 GitHack 恢复删除代码的全过程。</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


