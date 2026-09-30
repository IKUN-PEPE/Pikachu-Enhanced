<?php
/**
 * Pikachu-Enhanced v2.0 - 核心智能侧边栏与导航路由引擎
 * 自动根据当前执行脚本路径 ($rel_path) 计算活跃的 Category、Module 与 Leaf 项
 * 彻底杜绝 $ACTIVE 数组索引冲突、跨分类误展开以及多菜单重叠高亮等历史问题
 */

function pika_get_current_route() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
    
    if (($pos = strpos($script, '/vul/')) !== false) {
        $rel = substr($script, $pos + 1);
    } elseif (($pos = strpos($script, '/pkxss/')) !== false) {
        $rel = substr($script, $pos + 1);
    } else {
        $rel = basename($script);
    }
    
    // 去除 query 参数
    $rel = explode('?', $rel)[0];
    return $rel;
}

function pika_get_all_routes() {
    static $routes = null;
    if ($routes !== null) {
        return $routes;
    }
    $routes = [
        'index.php' => ['cat' => null, 'mod' => null, 'leaf' => 0, 'parent' => null],
        'install.php' => ['cat' => null, 'mod' => null, 'leaf' => null, 'parent' => null],
        'intro.php' => ['cat' => null, 'mod' => null, 'leaf' => 330, 'parent' => null],
        'pkxss/index.php' => ['cat' => 'classic', 'mod' => 'pkxss', 'leaf' => 121, 'parent' => 120],
        'vul/ad_security/ad_ctf_acl.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 248, 'parent' => null],
        'vul/ad_security/ad_ctf_adcs.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 243, 'parent' => null],
        'vul/ad_security/ad_ctf_asrep.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 240, 'parent' => null],
        'vul/ad_security/ad_ctf_delegation.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 244, 'parent' => null],
        'vul/ad_security/ad_ctf_esc8.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 246, 'parent' => null],
        'vul/ad_security/ad_ctf_hub.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 238, 'parent' => null],
        'vul/ad_security/ad_ctf_kerberoast.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 241, 'parent' => null],
        'vul/ad_security/ad_ctf_mssql.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 242, 'parent' => null],
        'vul/ad_security/ad_ctf_rbcd.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 245, 'parent' => null],
        'vul/ad_security/ad_ctf_recon.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 239, 'parent' => null],
        'vul/ad_security/ad_ctf_shadow_cred.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 247, 'parent' => null],
        'vul/ad_security/ad_env_check.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 237, 'parent' => null],
        'vul/ad_security/ad_lab_setup.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 236, 'parent' => null],
        'vul/ad_security/ad_security.php' => ['cat' => 'ad', 'mod' => null, 'leaf' => 231, 'parent' => null],
        'vul/ai_security/ai_security.php' => ['cat' => 'ai', 'mod' => 'ai_security', 'leaf' => 166, 'parent' => 165],
        'vul/ai_security/llm_data_leakage.php' => ['cat' => 'ai', 'mod' => 'ai_security', 'leaf' => 202, 'parent' => 165],
        'vul/ai_security/llm_plugin_rce.php' => ['cat' => 'ai', 'mod' => 'ai_security', 'leaf' => 203, 'parent' => 165],
        'vul/ai_security/llm_xss.php' => ['cat' => 'ai', 'mod' => 'ai_security', 'leaf' => 204, 'parent' => 165],
        'vul/ai_security/prompt_injection.php' => ['cat' => 'ai', 'mod' => 'ai_security', 'leaf' => 167, 'parent' => 165],
        'vul/api_security/api.php' => ['cat' => 'cloud', 'mod' => 'api_security', 'leaf' => 145, 'parent' => 144],
        'vul/api_security/bola.php' => ['cat' => 'cloud', 'mod' => 'api_security', 'leaf' => 146, 'parent' => 144],
        'vul/api_security/mass_assignment.php' => ['cat' => 'cloud', 'mod' => 'api_security', 'leaf' => 147, 'parent' => 144],
        'vul/burteforce/bf_client.php' => ['cat' => 'classic', 'mod' => 'burteforce', 'leaf' => 5, 'parent' => 1],
        'vul/burteforce/bf_form.php' => ['cat' => 'classic', 'mod' => 'burteforce', 'leaf' => 3, 'parent' => 1],
        'vul/burteforce/bf_server.php' => ['cat' => 'classic', 'mod' => 'burteforce', 'leaf' => 4, 'parent' => 1],
        'vul/burteforce/bf_token.php' => ['cat' => 'classic', 'mod' => 'burteforce', 'leaf' => 6, 'parent' => 1],
        'vul/burteforce/burteforce.php' => ['cat' => 'classic', 'mod' => 'burteforce', 'leaf' => 2, 'parent' => 1],
        'vul/clickjacking/attacker.php' => ['cat' => 'classic', 'mod' => 'clickjacking', 'leaf' => 139, 'parent' => 136],
        'vul/clickjacking/clickjacking.php' => ['cat' => 'classic', 'mod' => 'clickjacking', 'leaf' => 137, 'parent' => 136],
        'vul/clickjacking/target.php' => ['cat' => 'classic', 'mod' => 'clickjacking', 'leaf' => 138, 'parent' => 136],
        'vul/cloud_storage/cloud_storage.php' => ['cat' => 'proto', 'mod' => 'cloud_storage', 'leaf' => 189, 'parent' => 188],
        'vul/cloud_storage/oss_bucket_unauth.php' => ['cat' => 'proto', 'mod' => 'cloud_storage', 'leaf' => 311, 'parent' => 188],
        'vul/cors/cors.php' => ['cat' => 'classic', 'mod' => 'cors', 'leaf' => 133, 'parent' => 132],
        'vul/cors/cors_credential.php' => ['cat' => 'classic', 'mod' => 'cors', 'leaf' => 135, 'parent' => 132],
        'vul/cors/cors_reflect.php' => ['cat' => 'classic', 'mod' => 'cors', 'leaf' => 134, 'parent' => 132],
        'vul/csrf/csrf.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 26, 'parent' => 25],
        'vul/csrf/csrf_double_cookie/csrf_double_cookie.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 33, 'parent' => 25],
        'vul/csrf/csrf_json/csrf_json.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 32, 'parent' => 25],
        'vul/csrf/csrf_referer/csrf_referer.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 30, 'parent' => 25],
        'vul/csrf/csrf_samesite/csrf_samesite.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 34, 'parent' => 25],
        'vul/csrf/csrf_token_pool/csrf_token_pool.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 31, 'parent' => 25],
        'vul/csrf/csrfget/csrf_get_login.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 27, 'parent' => 25],
        'vul/csrf/csrfpost/csrf_post_login.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 28, 'parent' => 25],
        'vul/csrf/csrftoken/token_get_login.php' => ['cat' => 'classic', 'mod' => 'csrf', 'leaf' => 29, 'parent' => 25],
        'vul/defense/defense.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 221, 'parent' => null],
        'vul/defense/defense_honeypot.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 225, 'parent' => null],
        'vul/defense/defense_log_forensics.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 224, 'parent' => null],
        'vul/defense/defense_rasp.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 223, 'parent' => null],
        'vul/defense/defense_siem.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 226, 'parent' => null],
        'vul/defense/defense_waf.php' => ['cat' => 'defense', 'mod' => null, 'leaf' => 222, 'parent' => null],
        'vul/dir/dir.php' => ['cat' => 'classic', 'mod' => 'dir', 'leaf' => 81, 'parent' => 80],
        'vul/dir/dir_list.php' => ['cat' => 'classic', 'mod' => 'dir', 'leaf' => 82, 'parent' => 80],
        'vul/dockerlab/docker_caps_escape.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 212, 'parent' => 140],
        'vul/dockerlab/docker_cve_escape.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 213, 'parent' => 140],
        'vul/dockerlab/docker_fastjson_lab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 215, 'parent' => 140],
        'vul/dockerlab/docker_flask_ssti_lab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 217, 'parent' => 140],
        'vul/dockerlab/docker_log4j2_lab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 216, 'parent' => 140],
        'vul/dockerlab/docker_mysql_lab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 218, 'parent' => 140],
        'vul/dockerlab/docker_privileged_escape.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 210, 'parent' => 140],
        'vul/dockerlab/docker_redis_lab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 214, 'parent' => 140],
        'vul/dockerlab/docker_sock_escape.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 211, 'parent' => 140],
        'vul/dockerlab/dockerlab.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 141, 'parent' => 140],
        'vul/dockerlab/dockerlab_center.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 143, 'parent' => 140],
        'vul/dockerlab/dockerlab_check.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 142, 'parent' => 140],
        'vul/dockerlab/k8s_token_escape.php' => ['cat' => 'cloud', 'mod' => 'dockerlab', 'leaf' => 207, 'parent' => 140],
        'vul/fileinclude/fi_local.php' => ['cat' => 'classic', 'mod' => 'fileinclude', 'leaf' => 57, 'parent' => 55],
        'vul/fileinclude/fi_remote.php' => ['cat' => 'classic', 'mod' => 'fileinclude', 'leaf' => 58, 'parent' => 55],
        'vul/fileinclude/fileinclude.php' => ['cat' => 'classic', 'mod' => 'fileinclude', 'leaf' => 56, 'parent' => 55],
        'vul/frontend/dom_clobbering.php' => ['cat' => 'cloud', 'mod' => 'frontend', 'leaf' => 153, 'parent' => 151],
        'vul/frontend/frontend.php' => ['cat' => 'cloud', 'mod' => 'frontend', 'leaf' => 152, 'parent' => 151],
        'vul/frontend/prototype_pollution.php' => ['cat' => 'cloud', 'mod' => 'frontend', 'leaf' => 156, 'parent' => 151],
        'vul/graphql/graphql.php' => ['cat' => 'cloud', 'mod' => 'graphql', 'leaf' => 161, 'parent' => 160],
        'vul/grpc/grpc.php' => ['cat' => 'proto', 'mod' => 'grpc', 'leaf' => 193, 'parent' => 192],
        'vul/grpc/grpc_auth_bypass.php' => ['cat' => 'proto', 'mod' => 'grpc', 'leaf' => 313, 'parent' => 192],
        'vul/hostheader/hostheader.php' => ['cat' => 'classic', 'mod' => 'hostheader', 'leaf' => 126, 'parent' => 125],
        'vul/hostheader/trust.php' => ['cat' => 'classic', 'mod' => 'hostheader', 'leaf' => 127, 'parent' => 125],
        'vul/http_smuggling/cl_te.php' => ['cat' => 'proto', 'mod' => 'http_smuggling', 'leaf' => 185, 'parent' => 183],
        'vul/http_smuggling/http_smuggling.php' => ['cat' => 'proto', 'mod' => 'http_smuggling', 'leaf' => 184, 'parent' => 183],
        'vul/infoleak/findabc.php' => ['cat' => 'classic', 'mod' => 'infoleak', 'leaf' => 87, 'parent' => 85],
        'vul/infoleak/infoleak.php' => ['cat' => 'classic', 'mod' => 'infoleak', 'leaf' => 86, 'parent' => 85],
        'vul/java_unserialize/fastjson_rce.php' => ['cat' => 'classic', 'mod' => 'unserilization', 'leaf' => 94, 'parent' => 90],
        'vul/java_unserialize/java_unserialize.php' => ['cat' => 'classic', 'mod' => 'unserilization', 'leaf' => 220, 'parent' => 90],
        'vul/java_unserialize/native_unser.php' => ['cat' => 'classic', 'mod' => 'unserilization', 'leaf' => 98, 'parent' => 90],
        'vul/jwt/jwt.php' => ['cat' => 'cloud', 'mod' => 'jwt', 'leaf' => 158, 'parent' => 157],
        'vul/jwt/jwt_key_confusion.php' => ['cat' => 'cloud', 'mod' => 'jwt', 'leaf' => 317, 'parent' => 157],
        'vul/jwt/jwt_login.php' => ['cat' => 'cloud', 'mod' => 'jwt', 'leaf' => 124, 'parent' => 157],
        'vul/jwt/jwt_none.php' => ['cat' => 'cloud', 'mod' => 'jwt', 'leaf' => 159, 'parent' => 157],
        'vul/jwt/jwt_weak_secret.php' => ['cat' => 'cloud', 'mod' => 'jwt', 'leaf' => 316, 'parent' => 157],
        'vul/logic/logic.php' => ['cat' => 'cloud', 'mod' => 'logic', 'leaf' => 149, 'parent' => 148],
        'vul/logic/price_tamper.php' => ['cat' => 'cloud', 'mod' => 'logic', 'leaf' => 150, 'parent' => 148],
        'vul/mfa_bypass/mfa_bypass.php' => ['cat' => 'proto', 'mod' => 'mfa_bypass', 'leaf' => 201, 'parent' => 200],
        'vul/mfa_bypass/mfa_logic_bypass.php' => ['cat' => 'proto', 'mod' => 'mfa_bypass', 'leaf' => 315, 'parent' => 200],
        'vul/misconfig/env_leak.php' => ['cat' => 'proto', 'mod' => 'misconfig', 'leaf' => 198, 'parent' => 196],
        'vul/misconfig/git_leak.php' => ['cat' => 'proto', 'mod' => 'misconfig', 'leaf' => 199, 'parent' => 196],
        'vul/misconfig/misconfig.php' => ['cat' => 'proto', 'mod' => 'misconfig', 'leaf' => 197, 'parent' => 196],
        'vul/misconfig/swagger_unauth.php' => ['cat' => 'proto', 'mod' => 'misconfig', 'leaf' => 318, 'parent' => 196],
        'vul/nosql/mongo_bypass.php' => ['cat' => 'cloud', 'mod' => 'nosql', 'leaf' => 164, 'parent' => 162],
        'vul/nosql/mongo_operator.php' => ['cat' => 'cloud', 'mod' => 'nosql', 'leaf' => 319, 'parent' => 162],
        'vul/nosql/nosql.php' => ['cat' => 'cloud', 'mod' => 'nosql', 'leaf' => 163, 'parent' => 162],
        'vul/oauth/oauth.php' => ['cat' => 'ai', 'mod' => 'oauth', 'leaf' => 169, 'parent' => 168],
        'vul/oauth/state_bypass.php' => ['cat' => 'ai', 'mod' => 'oauth', 'leaf' => 170, 'parent' => 168],
        'vul/osed/osed_hub.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 271, 'parent' => null],
        'vul/osed/osed_l10_wpm_bypass.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 302, 'parent' => null],
        'vul/osed/osed_l1_fuzzing.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 272, 'parent' => null],
        'vul/osed/osed_l2_seh.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 273, 'parent' => null],
        'vul/osed/osed_l3_dep_bypass.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 274, 'parent' => null],
        'vul/osed/osed_l4_aslr.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 275, 'parent' => null],
        'vul/osed/osed_l5_egghunter.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 276, 'parent' => null],
        'vul/osed/osed_l6_rop.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 277, 'parent' => null],
        'vul/osed/osed_l7_asm_shellcode.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 278, 'parent' => null],
        'vul/osed/osed_l8_format_string.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 279, 'parent' => null],
        'vul/osed/osed_l9_proto_reverse.php' => ['cat' => 'osed', 'mod' => null, 'leaf' => 301, 'parent' => null],
        'vul/osep/osep_hub.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 250, 'parent' => null],
        'vul/osep/osep_l10_process_inject.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 282, 'parent' => null],
        'vul/osep/osep_l11_amsi_bypass.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 283, 'parent' => null],
        'vul/osep/osep_l12_applocker.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 284, 'parent' => null],
        'vul/osep/osep_l13_net_evasion.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 285, 'parent' => null],
        'vul/osep/osep_l14_cred_attack.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 286, 'parent' => null],
        'vul/osep/osep_l15_mssql.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 287, 'parent' => null],
        'vul/osep/osep_l16_kiosk_escape.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 288, 'parent' => null],
        'vul/osep/osep_l17_linux_postex.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 289, 'parent' => null],
        'vul/osep/osep_l18_ad_deep.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 299, 'parent' => null],
        'vul/osep/osep_l1_enum.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 252, 'parent' => null],
        'vul/osep/osep_l2_phishing.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 253, 'parent' => null],
        'vul/osep/osep_l3_lateral.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 254, 'parent' => null],
        'vul/osep/osep_l4_pivot.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 255, 'parent' => null],
        'vul/osep/osep_l5_av_evasion.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 256, 'parent' => null],
        'vul/osep/osep_l6_persistence.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 257, 'parent' => null],
        'vul/osep/osep_l7_exfil.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 258, 'parent' => null],
        'vul/osep/osep_l8_win_api.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 280, 'parent' => null],
        'vul/osep/osep_l9_office_macro.php' => ['cat' => 'osep', 'mod' => null, 'leaf' => 281, 'parent' => null],
        'vul/oswe/oswe_hub.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 261, 'parent' => null],
        'vul/oswe/oswe_l10_java_rce.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 292, 'parent' => null],
        'vul/oswe/oswe_l11_proto_pollution.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 293, 'parent' => null],
        'vul/oswe/oswe_l12_dotnet_deser.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 294, 'parent' => null],
        'vul/oswe/oswe_l13_ssrf_rce.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 295, 'parent' => null],
        'vul/oswe/oswe_l14_csrf_cors.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 296, 'parent' => null],
        'vul/oswe/oswe_l1_whitebox.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 262, 'parent' => null],
        'vul/oswe/oswe_l2_auth_bypass.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 263, 'parent' => null],
        'vul/oswe/oswe_l3_sqli_auth.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 264, 'parent' => null],
        'vul/oswe/oswe_l4_deser.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 265, 'parent' => null],
        'vul/oswe/oswe_l5_ssti.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 266, 'parent' => null],
        'vul/oswe/oswe_l6_xxe_oob.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 267, 'parent' => null],
        'vul/oswe/oswe_l7_rce_chain.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 268, 'parent' => null],
        'vul/oswe/oswe_l8_sqli_blind.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 290, 'parent' => null],
        'vul/oswe/oswe_l9_type_juggling.php' => ['cat' => 'oswe', 'mod' => null, 'leaf' => 291, 'parent' => null],
        'vul/overpermission/modern/op_bola.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 77, 'parent' => 73],
        'vul/overpermission/modern/op_jwt.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 79, 'parent' => 73],
        'vul/overpermission/modern/op_mass_assign.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 78, 'parent' => 73],
        'vul/overpermission/op.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 74, 'parent' => 73],
        'vul/overpermission/op1/op1_login.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 75, 'parent' => 73],
        'vul/overpermission/op2/op2_login.php' => ['cat' => 'classic', 'mod' => 'overpermission', 'leaf' => 76, 'parent' => 73],
        'vul/phar/phar.php' => ['cat' => 'ai', 'mod' => 'phar', 'leaf' => 181, 'parent' => 180],
        'vul/phar/phar_unserialize.php' => ['cat' => 'ai', 'mod' => 'phar', 'leaf' => 182, 'parent' => 180],
        'vul/race_condition/gift_card.php' => ['cat' => 'ai', 'mod' => 'race_condition', 'leaf' => 173, 'parent' => 171],
        'vul/race_condition/race_condition.php' => ['cat' => 'ai', 'mod' => 'race_condition', 'leaf' => 172, 'parent' => 171],
        'vul/rce/rce.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 51, 'parent' => 50],
        'vul/rce/rce_blind.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 227, 'parent' => 50],
        'vul/rce/rce_bypass.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 54, 'parent' => 50],
        'vul/rce/rce_eval.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 53, 'parent' => 50],
        'vul/rce/rce_ping.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 52, 'parent' => 50],
        'vul/rce/rce_ssti.php' => ['cat' => 'classic', 'mod' => 'rce', 'leaf' => 228, 'parent' => 50],
        'vul/serverless/lambda_env_leak.php' => ['cat' => 'proto', 'mod' => 'serverless', 'leaf' => 312, 'parent' => 190],
        'vul/serverless/serverless.php' => ['cat' => 'proto', 'mod' => 'serverless', 'leaf' => 191, 'parent' => 190],
        'vul/sessionfixation/fixation_login.php' => ['cat' => 'classic', 'mod' => 'sessionfixation', 'leaf' => 130, 'parent' => 128],
        'vul/sessionfixation/fixation_profile.php' => ['cat' => 'classic', 'mod' => 'sessionfixation', 'leaf' => 131, 'parent' => 128],
        'vul/sessionfixation/sessionfixation.php' => ['cat' => 'classic', 'mod' => 'sessionfixation', 'leaf' => 129, 'parent' => 128],
        'vul/sqli/sqli.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 36, 'parent' => 35],
        'vul/sqli/sqli_blind_b.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 44, 'parent' => 35],
        'vul/sqli/sqli_blind_t.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 45, 'parent' => 35],
        'vul/sqli/sqli_del.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 42, 'parent' => 35],
        'vul/sqli/sqli_header/sqli_header_login.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 43, 'parent' => 35],
        'vul/sqli/sqli_id.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 37, 'parent' => 35],
        'vul/sqli/sqli_iu/sqli_login.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 41, 'parent' => 35],
        'vul/sqli/sqli_search.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 39, 'parent' => 35],
        'vul/sqli/sqli_str.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 38, 'parent' => 35],
        'vul/sqli/sqli_widebyte.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 46, 'parent' => 35],
        'vul/sqli/sqli_x.php' => ['cat' => 'classic', 'mod' => 'sqli', 'leaf' => 40, 'parent' => 35],
        'vul/sso_saml/saml_xsw.php' => ['cat' => 'proto', 'mod' => 'sso_saml', 'leaf' => 310, 'parent' => 186],
        'vul/sso_saml/sso_saml.php' => ['cat' => 'proto', 'mod' => 'sso_saml', 'leaf' => 187, 'parent' => 186],
        'vul/ssrf/ssrf.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 106, 'parent' => 105],
        'vul/ssrf/ssrf_cloud.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 109, 'parent' => 105],
        'vul/ssrf/ssrf_curl.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 107, 'parent' => 105],
        'vul/ssrf/ssrf_dns_rebinding.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 219, 'parent' => 105],
        'vul/ssrf/ssrf_fgc.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 108, 'parent' => 105],
        'vul/ssrf/ssrf_gopher_redis.php' => ['cat' => 'classic', 'mod' => 'ssrf', 'leaf' => 209, 'parent' => 105],
        'vul/unsafedownload/down_nba.php' => ['cat' => 'classic', 'mod' => 'unsafedownload', 'leaf' => 62, 'parent' => 60],
        'vul/unsafedownload/unsafedownload.php' => ['cat' => 'classic', 'mod' => 'unsafedownload', 'leaf' => 61, 'parent' => 60],
        'vul/unsafeupload/clientcheck.php' => ['cat' => 'classic', 'mod' => 'unsafeupload', 'leaf' => 67, 'parent' => 65],
        'vul/unsafeupload/getimagesize.php' => ['cat' => 'classic', 'mod' => 'unsafeupload', 'leaf' => 69, 'parent' => 65],
        'vul/unsafeupload/servercheck.php' => ['cat' => 'classic', 'mod' => 'unsafeupload', 'leaf' => 68, 'parent' => 65],
        'vul/unsafeupload/upload.php' => ['cat' => 'classic', 'mod' => 'unsafeupload', 'leaf' => 66, 'parent' => 65],
        'vul/unsafeupload/zip_slip.php' => ['cat' => 'classic', 'mod' => 'unsafeupload', 'leaf' => 208, 'parent' => 65],
        'vul/unserilization/unser.php' => ['cat' => 'classic', 'mod' => 'unserilization', 'leaf' => 92, 'parent' => 90],
        'vul/unserilization/unserilization.php' => ['cat' => 'classic', 'mod' => 'unserilization', 'leaf' => 91, 'parent' => 90],
        'vul/urlredirect/unsafere.php' => ['cat' => 'classic', 'mod' => 'urlredirect', 'leaf' => 101, 'parent' => 100],
        'vul/urlredirect/urlredirect.php' => ['cat' => 'classic', 'mod' => 'urlredirect', 'leaf' => 102, 'parent' => 100],
        'vul/web_cache/cache_deception.php' => ['cat' => 'ai', 'mod' => 'web_cache', 'leaf' => 176, 'parent' => 174],
        'vul/web_cache/web_cache.php' => ['cat' => 'ai', 'mod' => 'web_cache', 'leaf' => 175, 'parent' => 174],
        'vul/webhook/webhook.php' => ['cat' => 'proto', 'mod' => 'webhook', 'leaf' => 195, 'parent' => 194],
        'vul/webhook/webhook_ssrf.php' => ['cat' => 'proto', 'mod' => 'webhook', 'leaf' => 314, 'parent' => 194],
        'vul/websocket/cswsh.php' => ['cat' => 'ai', 'mod' => 'websocket', 'leaf' => 179, 'parent' => 177],
        'vul/websocket/websocket.php' => ['cat' => 'ai', 'mod' => 'websocket', 'leaf' => 178, 'parent' => 177],
        'vul/websocket/ws_sqli.php' => ['cat' => 'ai', 'mod' => 'websocket', 'leaf' => 205, 'parent' => 177],
        'vul/websocket/ws_unauth_stream.php' => ['cat' => 'ai', 'mod' => 'websocket', 'leaf' => 206, 'parent' => 177],
        'vul/xss/xss.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 8, 'parent' => 7],
        'vul/xss/xss_01.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 14, 'parent' => 7],
        'vul/xss/xss_02.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 15, 'parent' => 7],
        'vul/xss/xss_03.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 16, 'parent' => 7],
        'vul/xss/xss_04.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 17, 'parent' => 7],
        'vul/xss/xss_dom.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 12, 'parent' => 7],
        'vul/xss/xss_dom_x.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 21, 'parent' => 7],
        'vul/xss/xss_reflected_get.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 9, 'parent' => 7],
        'vul/xss/xss_stored.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 11, 'parent' => 7],
        'vul/xss/xssblind/xss_blind.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 13, 'parent' => 7],
        'vul/xss/xsspost/post_login.php' => ['cat' => 'classic', 'mod' => 'xss', 'leaf' => 10, 'parent' => 7],
        'vul/xxe/xxe.php' => ['cat' => 'classic', 'mod' => 'xxe', 'leaf' => 96, 'parent' => 95],
        'vul/xxe/xxe_1.php' => ['cat' => 'classic', 'mod' => 'xxe', 'leaf' => 97, 'parent' => 95],
    ];
    return $routes;
}

/**
 * 核心导航解析函数
 * @param array &$ACTIVE 传统全局 ACTIVE 数组（兼容旧版各模块直接调用）
 * @param array &$CAT_CLASS 各主分类 active open 状态数组
 * @param array &$MOD_CLASS 各二级模块 active open 状态数组
 * @param array &$LEAF_CLASS 各叶子关卡 active 状态数组
 */

/**
 * 智能目录回退推断引擎
 * 当新增关卡尚未在静态路由表中登记时，自动根据其所在目录结构推断 Category 与 Module
 * 保证即便开发者或 AI 忘记注册路由，也绝不会发生侧边栏错位或跨大类误展开！
 */
function pika_infer_route($rel_path) {
    $dir = dirname($rel_path);
    if ($dir === '.' || $dir === '') {
        return null;
    }
    
    // 一级与二级目录智能特征库
    static $dir_map = [
        // 核心实战大类 (无二级折叠)
        'vul/osep'        => ['cat' => 'osep',    'mod' => null],
        'vul/oswe'        => ['cat' => 'oswe',    'mod' => null],
        'vul/osed'        => ['cat' => 'osed',    'mod' => null],
        'vul/ad_security' => ['cat' => 'ad',      'mod' => null],
        'vul/defense'     => ['cat' => 'defense', 'mod' => null],
        
        // 经典 Web 大类
        'vul/burteforce'     => ['cat' => 'classic', 'mod' => 'burteforce'],
        'vul/xss'            => ['cat' => 'classic', 'mod' => 'xss'],
        'vul/csrf'           => ['cat' => 'classic', 'mod' => 'csrf'],
        'vul/sqli'           => ['cat' => 'classic', 'mod' => 'sqli'],
        'vul/rce'            => ['cat' => 'classic', 'mod' => 'rce'],
        'vul/fileinclude'    => ['cat' => 'classic', 'mod' => 'fileinclude'],
        'vul/unsafedownload' => ['cat' => 'classic', 'mod' => 'unsafedownload'],
        'vul/unsafeupload'   => ['cat' => 'classic', 'mod' => 'unsafeupload'],
        'vul/overpermission' => ['cat' => 'classic', 'mod' => 'overpermission'],
        'vul/dir'            => ['cat' => 'classic', 'mod' => 'dir'],
        'vul/infoleak'       => ['cat' => 'classic', 'mod' => 'infoleak'],
        'vul/unserilization' => ['cat' => 'classic', 'mod' => 'unserilization'],
        'vul/java_unserialize' => ['cat' => 'classic', 'mod' => 'unserilization'],
        'vul/xxe'            => ['cat' => 'classic', 'mod' => 'xxe'],
        'vul/urlredirect'    => ['cat' => 'classic', 'mod' => 'urlredirect'],
        'vul/ssrf'           => ['cat' => 'classic', 'mod' => 'ssrf'],
        'pkxss'              => ['cat' => 'classic', 'mod' => 'pkxss'],
        'vul/hostheader'     => ['cat' => 'classic', 'mod' => 'hostheader'],
        'vul/sessionfixation'=> ['cat' => 'classic', 'mod' => 'sessionfixation'],
        'vul/cors'           => ['cat' => 'classic', 'mod' => 'cors'],
        'vul/clickjacking'   => ['cat' => 'classic', 'mod' => 'clickjacking'],
        
        // 云原生与现代架构
        'vul/dockerlab'      => ['cat' => 'cloud', 'mod' => 'dockerlab'],
        'vul/api_security'   => ['cat' => 'cloud', 'mod' => 'api_security'],
        'vul/logic'          => ['cat' => 'cloud', 'mod' => 'logic'],
        'vul/frontend'       => ['cat' => 'cloud', 'mod' => 'frontend'],
        'vul/jwt'            => ['cat' => 'cloud', 'mod' => 'jwt'],
        'vul/graphql'        => ['cat' => 'cloud', 'mod' => 'graphql'],
        'vul/nosql'          => ['cat' => 'cloud', 'mod' => 'nosql'],
        
        // AI 与前沿应用
        'vul/ai_security'    => ['cat' => 'ai', 'mod' => 'ai_security'],
        'vul/oauth'          => ['cat' => 'ai', 'mod' => 'oauth'],
        'vul/race_condition' => ['cat' => 'ai', 'mod' => 'race_condition'],
        'vul/web_cache'      => ['cat' => 'ai', 'mod' => 'web_cache'],
        'vul/websocket'      => ['cat' => 'ai', 'mod' => 'websocket'],
        'vul/phar'           => ['cat' => 'ai', 'mod' => 'phar'],
        
        // 前沿协议与数据安全
        'vul/http_smuggling' => ['cat' => 'proto', 'mod' => 'http_smuggling'],
        'vul/sso_saml'       => ['cat' => 'proto', 'mod' => 'sso_saml'],
        'vul/cloud_storage'  => ['cat' => 'proto', 'mod' => 'cloud_storage'],
        'vul/serverless'     => ['cat' => 'proto', 'mod' => 'serverless'],
        'vul/grpc'           => ['cat' => 'proto', 'mod' => 'grpc'],
        'vul/webhook'        => ['cat' => 'proto', 'mod' => 'webhook'],
        'vul/misconfig'      => ['cat' => 'proto', 'mod' => 'misconfig'],
        'vul/mfa_bypass'     => ['cat' => 'proto', 'mod' => 'mfa_bypass'],
    ];
    
    // 支持逐级向上寻找匹配前缀（例如嵌套的子目录 vul/xss/xsspost/ -> 匹配 vul/xss）
    $check_dir = $dir;
    while ($check_dir !== '.' && $check_dir !== '') {
        if (isset($dir_map[$check_dir])) {
            return array_merge($dir_map[$check_dir], ['leaf' => null, 'parent' => null]);
        }
        $parent_dir = dirname($check_dir);
        if ($parent_dir === $check_dir) break;
        $check_dir = $parent_dir;
    }
    
    return null;
}

/**
 * 链接活跃度极速判定辅助函数 (无需关心任何数字索引!)
 * 在页面模板或 header.php 中可直接调用：
 * class="<?php echo pika_is_active('vul/dockerlab/docker_sock_escape.php', 211); ?>"
 */
function pika_is_active($target_rel, $fallback_idx = null) {
    global $ACTIVE, $LEAF_CLASS;
    $current = pika_get_current_route();
    
    // 1. URL 直接对比 (精确无碰撞)
    if ($current === $target_rel) {
        return 'active';
    }
    // 2. LEAF_CLASS 数组查找
    if (!empty($LEAF_CLASS[$target_rel])) {
        return 'active';
    }
    // 3. 兼容性数字索引判定
    if ($fallback_idx !== null && !empty($ACTIVE[$fallback_idx]) && strpos($ACTIVE[$fallback_idx], 'active') !== false) {
        return 'active';
    }
    return '';
}

function pika_resolve_navigation(&$ACTIVE, &$CAT_CLASS, &$MOD_CLASS = null, &$LEAF_CLASS = null) {
    $current_rel = pika_get_current_route();
    
    // 初始化旧版 ACTIVE 数组 (容量扩至 500)
    $ACTIVE = array_fill(0, 500, '');
    
    // 初始化 9 大分类状态
    $CAT_CLASS = [
        'classic' => '',
        'cloud'   => '',
        'ai'      => '',
        'proto'   => '',
        'defense' => '',
        'ad'      => '',
        'osep'    => '',
        'oswe'    => '',
        'osed'    => '',
    ];
    
    $MOD_CLASS = [];
    $LEAF_CLASS = [];
    
    // 首页与全局漏洞图鉴特殊处理
    if ($current_rel === 'index.php') {
        $ACTIVE[0] = 'active';
        $LEAF_CLASS['index.php'] = 'active';
        return;
    }
    if ($current_rel === 'intro.php') {
        $ACTIVE[330] = 'active';
        // intro uses 330
        $LEAF_CLASS['intro.php'] = 'active';
        return;
    }
    
    $routes = pika_get_all_routes();
    $matched_route = null;
    $matched_key = null;
    
    // 1. 精准路径匹配 (已注册的 221+ 关卡)
    if (isset($routes[$current_rel])) {
        $matched_route = $routes[$current_rel];
        $matched_key = $current_rel;
    } else {
        // 2. 目录模糊匹配 (已注册目录下的同名/相关关卡)
        $current_dir = dirname($current_rel);
        if ($current_dir !== '.' && $current_dir !== '') {
            foreach ($routes as $path => $r) {
                if (dirname($path) === $current_dir) {
                    $matched_route = $r;
                    $matched_key = $current_rel; // 使用实际请求路径作为叶子激活标识
                    break;
                }
            }
        }
        
        // 3. 全局智能特征库推断 (针对未来任何新增但未手动登记的全新关卡)
        if ($matched_route === null) {
            $inferred = pika_infer_route($current_rel);
            if ($inferred !== null) {
                $matched_route = $inferred;
                $matched_key = $current_rel;
            }
        }
    }
    
    // 执行状态激活
    if ($matched_route !== null) {
        $cat = $matched_route['cat'];
        $mod = $matched_route['mod'];
        $leaf = $matched_route['leaf'];
        $parent = $matched_route['parent'];
        
        // 激活所属分类 (唯一激活，绝对不会跨分类展开)
        if ($cat && isset($CAT_CLASS[$cat])) {
            $CAT_CLASS[$cat] = 'active open';
        }
        
        // 激活所属二级模块
        if ($mod) {
            $MOD_CLASS[$mod] = 'active open';
        }
        
        // 激活对应叶子项
        if ($matched_key) {
            $LEAF_CLASS[$matched_key] = 'active';
        }
        
        // 填充兼容性 ACTIVE 索引
        if ($parent !== null) {
            $ACTIVE[$parent] = 'active open';
        }
        if ($leaf !== null) {
            $ACTIVE[$leaf] = 'active';
        }
    }
}
