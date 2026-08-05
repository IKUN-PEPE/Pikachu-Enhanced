<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[171] = 'active open';
$ACTIVE[173] = 'active';
$ACTIVE[171] = 'active open';
$ACTIVE[173] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

// 初始化用户余额和礼品卡状态
// 在真实场景中，这应该存在数据库里，这里用文件锁模拟高并发环境下的全局共享资源
$db_file = 'db_balance.json';

if (!file_exists($db_file) || isset($_POST['reset'])) {
    $initial_data = [
        'balance' => 0,
        'card_used' => false
    ];
    file_put_contents($db_file, json_encode($initial_data));
}

$db_data = json_decode(file_get_contents($db_file), true);
$message = "";

if (isset($_POST['redeem'])) {
    // 关键！在 PHP 中，如果不提早关闭 session 锁，同一个用户的并发请求会被强制排队串行化
    // 这行代码模拟了微服务/无状态/或者未开启 Session 强锁定的真实并发业务环境
    session_write_close(); 

    // 重新从共享存储读取最新状态
    $current_data = json_decode(file_get_contents($db_file), true);

    // 第一步：Check (检查条件)
    if ($current_data['card_used'] === false) {
        
        // 模拟真实业务中，查库之后与更新数据之间的微小延迟 (如查询余额、记账、发短信等耗时操作)
        // 这个 0.5 秒的延迟是并发攻击的黄金窗口！
        usleep(500000); 

        // 第二步：Use (执行逻辑)
        // 为了在页面上更直观地展示并发结果（如突破到 1000 元），
        // 我们在写回前重新读取一次当时的余额（模拟无锁 DB UPDATE 的自增效果）
        $latest_data = json_decode(file_get_contents($db_file), true);
        $latest_data['balance'] += 100;
        // 销毁卡片
        $latest_data['card_used'] = true;

        // 写入数据库
        file_put_contents($db_file, json_encode($latest_data));
        
        $message = "<div class='alert alert-success'>✅ 兑换成功！获得了 100 元。</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ 兑换失败：该礼品卡已被使用。</div>";
    }

    // 重新获取最新数据用于渲染
    $db_data = json_decode(file_get_contents($db_file), true);
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="race_condition.php">业务并发安全</a></li>
                <li class="active">并发竞争兑换</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>⏱️ 礼品卡并发兑换 (Race Condition)</h2>
                <p>系统为您分配了一张面值 <strong>100元</strong> 的专属礼品卡。</p>
                <p>正常情况下，点击兑换，系统会检查卡片是否已用 -> 余额+100 -> 将卡片标记为已用。您最多只能兑换 1 次。</p>
                <p><strong>攻击挑战</strong>：请尝试利用工具（如 Burp Suite 的 Turbo Intruder，或直接在命令行使用 curl 并发），在极短时间内同时发送数十个 POST 请求。由于系统在检查状态和更新状态之间存在 0.5 秒的时间差且未加互斥锁，看看你能薅走多少钱！</p>
                <hr>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">我的账户</div>
                            <div class="panel-body">
                                <h3>当前余额: <span class="label label-xlg label-primary"><?php echo $db_data['balance']; ?> 元</span></h3>
                                <p>礼品卡状态: 
                                    <?php if($db_data['card_used']): ?>
                                        <span class="label label-danger">已使用</span>
                                    <?php else: ?>
                                        <span class="label label-success">未使用</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">操作面板</div>
                            <div class="panel-body">
                                <form method="POST" style="display:inline-block; margin-right: 10px;">
                                    <input type="hidden" name="redeem" value="1">
                                    <button type="submit" class="btn btn-warning btn-lg" <?php echo $db_data['card_used'] ? 'disabled' : ''; ?>>
                                        <i class="ace-icon fa fa-gift"></i> 兑换礼品卡 (单点测试)
                                    </button>
                                </form>

                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="reset" value="1">
                                    <button type="submit" class="btn btn-default btn-sm">
                                        <i class="ace-icon fa fa-refresh"></i> 重置账户与卡片
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="well">
                            <h5>🔨 并发利用命令参考：</h5>
                            <pre><code># 使用 Bash 后台并发执行 10 次 curl
for i in {1..10}; do 
  curl -s -d "redeem=1" http://127.0.0.1:8765/vul/race_condition/gift_card.php > /dev/null & 
done
wait</code></pre>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>


