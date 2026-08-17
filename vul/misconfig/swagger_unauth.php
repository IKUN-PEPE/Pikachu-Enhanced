<?php
$ACTIVE = array_fill(0, 300, '');
$ACTIVE[218] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

$api_resp = "";
$api_path = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api = $_POST['api_endpoint'] ?? '';
    $api_path = $api;
    
    if ($api === '/api/v1/users/list') {
        $api_resp = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n[\n  { \"id\": 101, \"username\": \"pikachu\", \"department\": \"Security\" },\n  { \"id\": 102, \"username\": \"lucy\", \"department\": \"HR\" }\n]";
    } else if ($api === '/api/v1/admin/system_secrets') {
        $api_resp = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n{\n  \"system_status\": \"Healthy\",\n  \"admin_jwt_secret\": \"Pikachu_JWT_Secret_2026\",\n  \"master_flag\": \"FLAG{SWAGGER_UI_ACTUATOR_UNAUTH_API_EXPLORER_MASTER}\",\n  \"warning\": \"This endpoint is intended for internal developers only.\"\n}";
    } else if ($api === '/actuator/env') {
        $api_resp = "HTTP/1.1 200 OK\nContent-Type: application/vnd.spring-boot.actuator.v3+json\n\n{\n  \"activeProfiles\": [ \"prod\" ],\n  \"propertySources\": [\n    {\n      \"name\": \"server.ports\",\n      \"properties\": { \"local.server.port\": { \"value\": 8080 } }\n    },\n    {\n      \"name\": \"systemEnvironment\",\n      \"properties\": {\n        \"FLAG_KEY\": { \"value\": \"FLAG{SWAGGER_UI_ACTUATOR_UNAUTH_API_EXPLORER_MASTER}\" }\n      }\n    }\n  ]\n}";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="misconfig.php">配置泄露与调试监控</a></li>
                <li class="active">Swagger / Actuator 未授权</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📗 Swagger UI 与 Spring Boot Actuator 未授权接口调试 (Unauth API Explorer)</h2>
                <p>在前后端分离和微服务开发中，研发团队通常会引入 <b>Swagger UI (OpenAPI)</b> 来自动生成交互式 RESTful API 接口文档，或引入 <b>Spring Boot Actuator</b> 提供运行时的系统监控与健康检查。</p>
                <p>上线生产环境时，如果不慎未将 <code>/swagger-ui.html</code>、<code>/v2/api-docs</code> 或 <code>/actuator/*</code> 等调试路径配置进 Spring Security 拦截器或 Nginx 黑名单，任何外网访客就能直接打开 API 文档，通过“开箱即用”的 <code>Try it out</code> 功能在线调试并越权调用企业内部核心管理接口！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-5">
                        <h4><i class="fa fa-book"></i> 模拟 Swagger API 文档调试控制台</h4>
                        <form method="POST">
                            <div class="panel panel-info">
                                <div class="panel-heading"><b>Pikachu-Enhanced Open API Controller v2.0</b></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>选择调试接口端点 (Endpoint)：</label>
                                        <div class="radio">
                                            <label><input type="radio" name="api_endpoint" value="/api/v1/users/list" <?php if(($POST['api_endpoint']??'/api/v1/users/list')==='/api/v1/users/list') echo 'checked'; ?>> <span class="label label-success">GET</span> <code>/api/v1/users/list</code> (公开用户列表)</label>
                                        </div>
                                        <div class="radio">
                                            <label><input type="radio" name="api_endpoint" value="/api/v1/admin/system_secrets" <?php if(($POST['api_endpoint']??'')==='/api/v1/admin/system_secrets') echo 'checked'; ?>> <span class="label label-danger">POST</span> <code>/api/v1/admin/system_secrets</code> (内部机密查询)</label>
                                        </div>
                                        <div class="radio">
                                            <label><input type="radio" name="api_endpoint" value="/actuator/env" <?php if(($POST['api_endpoint']??'')==='/actuator/env') echo 'checked'; ?>> <span class="label label-warning">GET</span> <code>/actuator/env</code> (Spring Actuator 环境变量)</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-play"></i> Try it out! (发送免签测试调用)</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h4><i class="fa fa-exchange"></i> API 调试工具实时响应体 (Response Box)</h4>
                        <div class="panel <?php echo ($api_path!=='/api/v1/users/list' && !empty($api_resp)) ? 'panel-danger' : 'panel-default'; ?>" style="margin-top: 0;">
                            <div class="panel-heading"><b>请求端点：<?php echo !empty($api_path) ? htmlspecialchars($api_path) : '未发起'; ?></b></div>
                            <div class="panel-body" style="padding:0;">
                                <pre style="background:#111; color:#50fa7b; margin:0; border:none; border-radius:0; font-family:monospace; min-height:220px;"><?php echo !empty($api_resp) ? htmlspecialchars($api_resp) : "// 点击左侧 Try it out 发送接口调用，体验从未授权 Swagger/Actuator 中获取系统最高机密。"; ?></pre>
                            </div>
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


