<?php
/**
 * Pikachu-Enhanced 智能自适应导航与路由状态解析引擎
 * 自动计算当前高亮菜单项、二级下拉展开、一级主分类展开，彻底解决侧边栏跳转与分类冲突
 */

function pika_resolve_navigation(&$ACTIVE, &$CAT_ACTIVE, $pika_root_dir = '') {
    $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
    
    // 归一化路径：获取相对于 Pikachu 根目录的相对路径
    $rel_path = '';
    $vul_pos = strpos($script_name, '/vul/');
    $pkxss_pos = strpos($script_name, '/pkxss/');
    
    if ($vul_pos !== false) {
        $rel_path = substr($script_name, $vul_pos + 1);
    } elseif ($pkxss_pos !== false) {
        $rel_path = substr($script_name, $pkxss_pos + 1);
    } else {
        $base = basename($script_name);
        if ($base == 'index.php') {
            $rel_path = 'index.php';
        } elseif ($base == 'intro.php') {
            $rel_path = 'intro.php';
        } elseif ($base == 'install.php') {
            $rel_path = 'install.php';
        } else {
            $rel_path = $base;
        }
    }

    // 全路由精准映射表 (相对路径 => [分类, 二级下拉索引, 三级叶子索引])
    $route_map = [
        'index.php' => ['cat' => 'intro_root', 'parent' => null, 'leaf' => '0'],
        'intro.php' => ['cat' => 'intro', 'parent' => null, 'leaf' => '219'],
        'pkxss/index.php' => ['cat' => 'classic', 'parent' => '120', 'leaf' => '121'],
        'vul/ad_security/ad_ctf_acl.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '248'],
        'vul/ad_security/ad_ctf_adcs.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '243'],
        'vul/ad_security/ad_ctf_asrep.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '240'],
        'vul/ad_security/ad_ctf_coerce.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '249'],
        'vul/ad_security/ad_ctf_delegation.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '244'],
        'vul/ad_security/ad_ctf_domain_trust.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '233'],
        'vul/ad_security/ad_ctf_esc8.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '246'],
        'vul/ad_security/ad_ctf_forest_trust.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '234'],
        'vul/ad_security/ad_ctf_gpo.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '235'],
        'vul/ad_security/ad_ctf_hub.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '238'],
        'vul/ad_security/ad_ctf_kerberoast.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '241'],
        'vul/ad_security/ad_ctf_mssql.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '242'],
        'vul/ad_security/ad_ctf_nopac.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '232'],
        'vul/ad_security/ad_ctf_rbcd.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '245'],
        'vul/ad_security/ad_ctf_recon.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '239'],
        'vul/ad_security/ad_ctf_shadow_cred.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '247'],
        'vul/ad_security/ad_env_check.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '237'],
        'vul/ad_security/ad_lab_setup.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '236'],
        'vul/ad_security/ad_security.php' => ['cat' => 'ad', 'parent' => '200', 'leaf' => '231'],
        'vul/ai_security/ai_security.php' => ['cat' => 'ai', 'parent' => '165', 'leaf' => '166'],
        'vul/ai_security/llm_data_leakage.php' => ['cat' => 'ai', 'parent' => '165', 'leaf' => '202'],
        'vul/ai_security/llm_plugin_rce.php' => ['cat' => 'ai', 'parent' => '165', 'leaf' => '203'],
        'vul/ai_security/llm_xss.php' => ['cat' => 'ai', 'parent' => '165', 'leaf' => '204'],
        'vul/ai_security/prompt_injection.php' => ['cat' => 'ai', 'parent' => '165', 'leaf' => '167'],
        'vul/api_security/api.php' => ['cat' => 'cloud', 'parent' => '144', 'leaf' => '145'],
        'vul/api_security/bola.php' => ['cat' => 'cloud', 'parent' => '144', 'leaf' => '146'],
        'vul/api_security/mass_assignment.php' => ['cat' => 'cloud', 'parent' => '144', 'leaf' => '147'],
        'vul/burteforce/bf_client.php' => ['cat' => 'classic', 'parent' => '1', 'leaf' => '5'],
        'vul/burteforce/bf_form.php' => ['cat' => 'classic', 'parent' => '1', 'leaf' => '3'],
        'vul/burteforce/bf_server.php' => ['cat' => 'classic', 'parent' => '1', 'leaf' => '4'],
        'vul/burteforce/bf_token.php' => ['cat' => 'classic', 'parent' => '1', 'leaf' => '6'],
        'vul/burteforce/burteforce.php' => ['cat' => 'classic', 'parent' => '1', 'leaf' => '2'],
        'vul/clickjacking/attacker.php' => ['cat' => 'classic', 'parent' => '136', 'leaf' => '139'],
        'vul/clickjacking/clickjacking.php' => ['cat' => 'classic', 'parent' => '136', 'leaf' => '137'],
        'vul/clickjacking/target.php' => ['cat' => 'classic', 'parent' => '136', 'leaf' => '138'],
        'vul/cloud_storage/cloud_storage.php' => ['cat' => 'proto', 'parent' => '188', 'leaf' => '189'],
        'vul/cloud_storage/oss_bucket_unauth.php' => ['cat' => 'proto', 'parent' => '188', 'leaf' => null],
        'vul/cors/cors.php' => ['cat' => 'classic', 'parent' => '132', 'leaf' => '133'],
        'vul/cors/cors_credential.php' => ['cat' => 'classic', 'parent' => '132', 'leaf' => '135'],
        'vul/cors/cors_reflect.php' => ['cat' => 'classic', 'parent' => '132', 'leaf' => '134'],
        'vul/csrf/csrf.php' => ['cat' => 'classic', 'parent' => '25', 'leaf' => '26'],
        'vul/csrf/csrfget/csrf_get_login.php' => ['cat' => 'classic', 'parent' => '25', 'leaf' => '27'],
        'vul/csrf/csrfpost/csrf_post_login.php' => ['cat' => 'classic', 'parent' => '25', 'leaf' => '28'],
        'vul/csrf/csrftoken/token_get_login.php' => ['cat' => 'classic', 'parent' => '25', 'leaf' => '29'],
        'vul/defense/defense.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '221'],
        'vul/defense/defense_honeypot.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '225'],
        'vul/defense/defense_log_forensics.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '224'],
        'vul/defense/defense_rasp.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '223'],
        'vul/defense/defense_siem.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '226'],
        'vul/defense/defense_waf.php' => ['cat' => 'defense', 'parent' => '200', 'leaf' => '222'],
        'vul/dir/dir.php' => ['cat' => 'classic', 'parent' => '80', 'leaf' => '81'],
        'vul/dir/dir_list.php' => ['cat' => 'classic', 'parent' => '80', 'leaf' => '82'],
        'vul/dockerlab/docker_caps_escape.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '212'],
        'vul/dockerlab/docker_cve_escape.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '213'],
        'vul/dockerlab/docker_privileged_escape.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '210'],
        'vul/dockerlab/docker_sock_escape.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '211'],
        'vul/dockerlab/dockerlab.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '141'],
        'vul/dockerlab/dockerlab_center.php' => ['cat' => 'intro', 'parent' => null, 'leaf' => '143'],
        'vul/dockerlab/dockerlab_check.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '142'],
        'vul/dockerlab/k8s_token_escape.php' => ['cat' => 'cloud', 'parent' => '140', 'leaf' => '207'],
        'vul/fileinclude/fi_local.php' => ['cat' => 'classic', 'parent' => '55', 'leaf' => '57'],
        'vul/fileinclude/fi_remote.php' => ['cat' => 'classic', 'parent' => '55', 'leaf' => '58'],
        'vul/fileinclude/fileinclude.php' => ['cat' => 'classic', 'parent' => '55', 'leaf' => '56'],
        'vul/frontend/dom_clobbering.php' => ['cat' => 'cloud', 'parent' => '151', 'leaf' => '153'],
        'vul/frontend/frontend.php' => ['cat' => 'cloud', 'parent' => '151', 'leaf' => '152'],
        'vul/frontend/prototype_pollution.php' => ['cat' => 'cloud', 'parent' => '151', 'leaf' => '156'],
        'vul/graphql/graphql.php' => ['cat' => 'cloud', 'parent' => '160', 'leaf' => '161'],
        'vul/grpc/grpc.php' => ['cat' => 'proto', 'parent' => '192', 'leaf' => '193'],
        'vul/grpc/grpc_auth_bypass.php' => ['cat' => 'proto', 'parent' => '192', 'leaf' => null],
        'vul/hostheader/hostheader.php' => ['cat' => 'classic', 'parent' => '125', 'leaf' => '126'],
        'vul/hostheader/trust.php' => ['cat' => 'classic', 'parent' => '125', 'leaf' => '127'],
        'vul/http_smuggling/cl_te.php' => ['cat' => 'proto', 'parent' => '183', 'leaf' => '185'],
        'vul/http_smuggling/http_smuggling.php' => ['cat' => 'proto', 'parent' => '183', 'leaf' => '184'],
        'vul/infoleak/findabc.php' => ['cat' => 'classic', 'parent' => '85', 'leaf' => '87'],
        'vul/infoleak/infoleak.php' => ['cat' => 'classic', 'parent' => '85', 'leaf' => '86'],
        'vul/java_unserialize/java_unserialize.php' => ['cat' => 'classic', 'parent' => '90', 'leaf' => '93'],
        'vul/jwt/jwt.php' => ['cat' => 'cloud', 'parent' => '157', 'leaf' => '158'],
        'vul/jwt/jwt_key_confusion.php' => ['cat' => 'cloud', 'parent' => '157', 'leaf' => '217'],
        'vul/jwt/jwt_login.php' => ['cat' => 'cloud', 'parent' => '157', 'leaf' => '124'],
        'vul/jwt/jwt_none.php' => ['cat' => 'cloud', 'parent' => '157', 'leaf' => '159'],
        'vul/jwt/jwt_weak_secret.php' => ['cat' => 'cloud', 'parent' => '157', 'leaf' => '216'],
        'vul/logic/logic.php' => ['cat' => 'cloud', 'parent' => '148', 'leaf' => '149'],
        'vul/logic/price_tamper.php' => ['cat' => 'cloud', 'parent' => '148', 'leaf' => '150'],
        'vul/mfa_bypass/mfa_bypass.php' => ['cat' => 'proto', 'parent' => '200', 'leaf' => '201'],
        'vul/mfa_bypass/mfa_logic_bypass.php' => ['cat' => 'proto', 'parent' => '200', 'leaf' => null],
        'vul/misconfig/env_leak.php' => ['cat' => 'proto', 'parent' => '196', 'leaf' => '198'],
        'vul/misconfig/git_leak.php' => ['cat' => 'proto', 'parent' => '196', 'leaf' => '199'],
        'vul/misconfig/misconfig.php' => ['cat' => 'proto', 'parent' => '196', 'leaf' => '197'],
        'vul/misconfig/swagger_unauth.php' => ['cat' => 'proto', 'parent' => '196', 'leaf' => null],
        'vul/nosql/mongo_bypass.php' => ['cat' => 'cloud', 'parent' => '162', 'leaf' => '164'],
        'vul/nosql/nosql.php' => ['cat' => 'cloud', 'parent' => '162', 'leaf' => '163'],
        'vul/oauth/oauth.php' => ['cat' => 'ai', 'parent' => '168', 'leaf' => '169'],
        'vul/oauth/state_bypass.php' => ['cat' => 'ai', 'parent' => '168', 'leaf' => '170'],
        'vul/osed/osed_hub.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '271'],
        'vul/osed/osed_l10_wpm_bypass.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '302'],
        'vul/osed/osed_l1_fuzzing.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '272'],
        'vul/osed/osed_l2_seh.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '273'],
        'vul/osed/osed_l3_dep_bypass.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '274'],
        'vul/osed/osed_l4_aslr.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '275'],
        'vul/osed/osed_l5_egghunter.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '276'],
        'vul/osed/osed_l6_rop.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '277'],
        'vul/osed/osed_l7_asm_shellcode.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '278'],
        'vul/osed/osed_l8_format_string.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '279'],
        'vul/osed/osed_l9_proto_reverse.php' => ['cat' => 'osed', 'parent' => '200', 'leaf' => '301'],
        'vul/osep/osep_hub.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '250'],
        'vul/osep/osep_l10_process_inject.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '282'],
        'vul/osep/osep_l11_amsi_bypass.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '283'],
        'vul/osep/osep_l12_applocker.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '284'],
        'vul/osep/osep_l13_net_evasion.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '285'],
        'vul/osep/osep_l14_cred_attack.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '286'],
        'vul/osep/osep_l15_mssql.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '287'],
        'vul/osep/osep_l16_kiosk_escape.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '288'],
        'vul/osep/osep_l17_linux_postex.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '289'],
        'vul/osep/osep_l18_ad_deep.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '299'],
        'vul/osep/osep_l1_enum.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '252'],
        'vul/osep/osep_l2_phishing.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '253'],
        'vul/osep/osep_l3_lateral.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '254'],
        'vul/osep/osep_l4_pivot.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '255'],
        'vul/osep/osep_l5_av_evasion.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '256'],
        'vul/osep/osep_l6_persistence.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '257'],
        'vul/osep/osep_l7_exfil.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '258'],
        'vul/osep/osep_l8_win_api.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '280'],
        'vul/osep/osep_l9_office_macro.php' => ['cat' => 'osep', 'parent' => '200', 'leaf' => '281'],
        'vul/oswe/oswe_hub.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '261'],
        'vul/oswe/oswe_l10_java_rce.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '292'],
        'vul/oswe/oswe_l11_proto_pollution.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '293'],
        'vul/oswe/oswe_l12_dotnet_deser.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '294'],
        'vul/oswe/oswe_l13_ssrf_rce.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '295'],
        'vul/oswe/oswe_l14_csrf_cors.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '296'],
        'vul/oswe/oswe_l1_whitebox.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '262'],
        'vul/oswe/oswe_l2_auth_bypass.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '263'],
        'vul/oswe/oswe_l3_sqli_auth.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '264'],
        'vul/oswe/oswe_l4_deser.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '265'],
        'vul/oswe/oswe_l5_ssti.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '266'],
        'vul/oswe/oswe_l6_xxe_oob.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '267'],
        'vul/oswe/oswe_l7_rce_chain.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '268'],
        'vul/oswe/oswe_l8_sqli_blind.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '290'],
        'vul/oswe/oswe_l9_type_juggling.php' => ['cat' => 'oswe', 'parent' => '200', 'leaf' => '291'],
        'vul/overpermission/modern/op_bola.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '77'],
        'vul/overpermission/modern/op_jwt.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '79'],
        'vul/overpermission/modern/op_mass_assign.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '78'],
        'vul/overpermission/op.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '74'],
        'vul/overpermission/op1/op1_login.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '75'],
        'vul/overpermission/op2/op2_login.php' => ['cat' => 'classic', 'parent' => '73', 'leaf' => '76'],
        'vul/phar/phar.php' => ['cat' => 'ai', 'parent' => '180', 'leaf' => '181'],
        'vul/phar/phar_unserialize.php' => ['cat' => 'ai', 'parent' => '180', 'leaf' => '182'],
        'vul/race_condition/gift_card.php' => ['cat' => 'ai', 'parent' => '171', 'leaf' => '173'],
        'vul/race_condition/race_condition.php' => ['cat' => 'ai', 'parent' => '171', 'leaf' => '172'],
        'vul/rce/rce.php' => ['cat' => 'classic', 'parent' => '50', 'leaf' => '51'],
        'vul/rce/rce_eval.php' => ['cat' => 'classic', 'parent' => '50', 'leaf' => '53'],
        'vul/rce/rce_ping.php' => ['cat' => 'classic', 'parent' => '50', 'leaf' => '52'],
        'vul/serverless/lambda_env_leak.php' => ['cat' => 'proto', 'parent' => '190', 'leaf' => null],
        'vul/serverless/serverless.php' => ['cat' => 'proto', 'parent' => '190', 'leaf' => '191'],
        'vul/sessionfixation/fixation_login.php' => ['cat' => 'classic', 'parent' => '128', 'leaf' => '130'],
        'vul/sessionfixation/fixation_profile.php' => ['cat' => 'classic', 'parent' => '128', 'leaf' => '131'],
        'vul/sessionfixation/sessionfixation.php' => ['cat' => 'classic', 'parent' => '128', 'leaf' => '129'],
        'vul/sqli/sqli.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '36'],
        'vul/sqli/sqli_blind_b.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '44'],
        'vul/sqli/sqli_blind_t.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '45'],
        'vul/sqli/sqli_del.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '42'],
        'vul/sqli/sqli_header/sqli_header_login.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '43'],
        'vul/sqli/sqli_id.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '37'],
        'vul/sqli/sqli_iu/sqli_login.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '41'],
        'vul/sqli/sqli_search.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '39'],
        'vul/sqli/sqli_str.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '38'],
        'vul/sqli/sqli_widebyte.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '46'],
        'vul/sqli/sqli_x.php' => ['cat' => 'classic', 'parent' => '35', 'leaf' => '40'],
        'vul/sso_saml/saml_xsw.php' => ['cat' => 'proto', 'parent' => '186', 'leaf' => null],
        'vul/sso_saml/sso_saml.php' => ['cat' => 'proto', 'parent' => '186', 'leaf' => '187'],
        'vul/ssrf/ssrf.php' => ['cat' => 'classic', 'parent' => '105', 'leaf' => '106'],
        'vul/ssrf/ssrf_cloud.php' => ['cat' => 'classic', 'parent' => '105', 'leaf' => '109'],
        'vul/ssrf/ssrf_curl.php' => ['cat' => 'classic', 'parent' => '105', 'leaf' => '107'],
        'vul/ssrf/ssrf_fgc.php' => ['cat' => 'classic', 'parent' => '105', 'leaf' => '108'],
        'vul/ssrf/ssrf_gopher_redis.php' => ['cat' => 'classic', 'parent' => '105', 'leaf' => '209'],
        'vul/unsafedownload/down_nba.php' => ['cat' => 'classic', 'parent' => '60', 'leaf' => '62'],
        'vul/unsafedownload/unsafedownload.php' => ['cat' => 'classic', 'parent' => '60', 'leaf' => '61'],
        'vul/unsafeupload/clientcheck.php' => ['cat' => 'classic', 'parent' => '65', 'leaf' => '67'],
        'vul/unsafeupload/getimagesize.php' => ['cat' => 'classic', 'parent' => '65', 'leaf' => '69'],
        'vul/unsafeupload/servercheck.php' => ['cat' => 'classic', 'parent' => '65', 'leaf' => '68'],
        'vul/unsafeupload/upload.php' => ['cat' => 'classic', 'parent' => '65', 'leaf' => '66'],
        'vul/unsafeupload/zip_slip.php' => ['cat' => 'classic', 'parent' => '65', 'leaf' => '208'],
        'vul/unserilization/unser.php' => ['cat' => 'classic', 'parent' => '90', 'leaf' => '92'],
        'vul/unserilization/unserilization.php' => ['cat' => 'classic', 'parent' => '90', 'leaf' => '91'],
        'vul/urlredirect/unsafere.php' => ['cat' => 'classic', 'parent' => '100', 'leaf' => '101'],
        'vul/urlredirect/urlredirect.php' => ['cat' => 'classic', 'parent' => '100', 'leaf' => '102'],
        'vul/web_cache/cache_deception.php' => ['cat' => 'ai', 'parent' => '174', 'leaf' => '176'],
        'vul/web_cache/web_cache.php' => ['cat' => 'ai', 'parent' => '174', 'leaf' => '175'],
        'vul/webhook/webhook.php' => ['cat' => 'proto', 'parent' => '194', 'leaf' => '195'],
        'vul/webhook/webhook_ssrf.php' => ['cat' => 'proto', 'parent' => '194', 'leaf' => null],
        'vul/websocket/cswsh.php' => ['cat' => 'ai', 'parent' => '177', 'leaf' => '179'],
        'vul/websocket/websocket.php' => ['cat' => 'ai', 'parent' => '177', 'leaf' => '178'],
        'vul/websocket/ws_sqli.php' => ['cat' => 'ai', 'parent' => '177', 'leaf' => '205'],
        'vul/websocket/ws_unauth_stream.php' => ['cat' => 'ai', 'parent' => '177', 'leaf' => '206'],
        'vul/xss/xss.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '8'],
        'vul/xss/xss_01.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '14'],
        'vul/xss/xss_02.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '15'],
        'vul/xss/xss_03.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '16'],
        'vul/xss/xss_04.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '17'],
        'vul/xss/xss_dom.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '12'],
        'vul/xss/xss_dom_x.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '21'],
        'vul/xss/xss_reflected_get.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '9'],
        'vul/xss/xss_stored.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '11'],
        'vul/xss/xssblind/xss_blind.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '13'],
        'vul/xss/xsspost/post_login.php' => ['cat' => 'classic', 'parent' => '7', 'leaf' => '10'],
        'vul/xxe/xxe.php' => ['cat' => 'classic', 'parent' => '95', 'leaf' => '96'],
        'vul/xxe/xxe_1.php' => ['cat' => 'classic', 'parent' => '95', 'leaf' => '97'],
    ];

    // 初始化分类激活状态
    $CAT_ACTIVE = [
        'intro'   => false,
        'classic' => false,
        'cloud'   => false,
        'ai'      => false,
        'proto'   => false,
        'defense' => false,
        'ad'      => false,
        'osep'    => false,
        'oswe'    => false,
        'osed'    => false,
    ];

    // 重置并初始化 ACTIVE 数组，消除任何错误残留
    $ACTIVE = array_fill(0, 350, '');

    // 特殊处理根目录 index.php
    if ($rel_path === 'index.php') {
        $ACTIVE[0] = 'active';
        return true;
    }

    // 精准匹配当前页面
    if (isset($route_map[$rel_path])) {
        $match = $route_map[$rel_path];
        $cat = $match['cat'];
        $parent = $match['parent'];
        $leaf = $match['leaf'];

        if ($cat && isset($CAT_ACTIVE[$cat])) {
            $CAT_ACTIVE[$cat] = true;
        }
        if ($parent !== null) {
            $ACTIVE[$parent] = 'active open';
        }
        if ($leaf !== null) {
            $ACTIVE[$leaf] = 'active';
        }
        return true;
    }

    // 容错前缀匹配（如子目录下的嵌套页面）
    $rel_dir = dirname($rel_path);
    if ($rel_dir !== '.' && $rel_dir !== '') {
        foreach ($route_map as $r_path => $match) {
            if (dirname($r_path) === $rel_dir) {
                $cat = $match['cat'];
                $parent = $match['parent'];
                if ($cat && isset($CAT_ACTIVE[$cat])) {
                    $CAT_ACTIVE[$cat] = true;
                }
                if ($parent !== null) {
                    $ACTIVE[$parent] = 'active open';
                }
                return true;
            }
        }
    }

    return false;
}
