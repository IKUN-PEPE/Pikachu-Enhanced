$baseUrl = "http://127.0.0.1:8765"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

function Test-Endpoint {
    param($Name, $Url, $Method="GET", $Body=$null, $ContentType="application/x-www-form-urlencoded", $ExpectedString)
    
    try {
        if ($Method -eq "POST") {
            $response = Invoke-WebRequest -Uri "$baseUrl$Url" -Method POST -Body $Body -ContentType $ContentType -WebSession $session -UseBasicParsing
        } else {
            $response = Invoke-WebRequest -Uri "$baseUrl$Url" -Method GET -WebSession $session -UseBasicParsing
        }
        
        $content = $response.Content
        if ($content -match $ExpectedString) {
            Write-Host "[PASS] $Name" -ForegroundColor Green
        } else {
            Write-Host "[FAIL] $Name - Expected string not found" -ForegroundColor Red
            Write-Host "Response summary: " ($content.Substring(0, [math]::Min(200, $content.Length)))
        }
    } catch {
        Write-Host "[FAIL] $Name - Exception: $_" -ForegroundColor Red
    }
}

Write-Host "--- Start Verification ---"

# 1. BOLA
Test-Endpoint -Name "BOLA ID=1 (User)" -Url "/vul/api_security/bola.php?action=get_user&id=1" -ExpectedString "vince"
Test-Endpoint -Name "BOLA ID=2 (Admin)" -Url "/vul/api_security/bola.php?action=get_user&id=2" -ExpectedString "admin"

# 2. Mass Assignment
$jsonBody = '{"email":"hacker@test.com","age":99,"is_admin":true}'
Test-Endpoint -Name "Mass Assignment (Set Admin)" -Url "/vul/api_security/mass_assignment.php" -Method "POST" -Body $jsonBody -ContentType "application/json" -ExpectedString "hacker@test.com"

# Verify Mass Assignment Result (Page should show success banner)
Test-Endpoint -Name "Mass Assignment (Verify Admin UI)" -Url "/vul/api_security/mass_assignment.php" -ExpectedString "你已成功利用批量赋值漏洞成为管理员"

# 3. Price Tampering
# First visit to initialize session
Invoke-WebRequest -Uri "$baseUrl/vul/logic/price_tamper.php?action=reset" -WebSession $session -UseBasicParsing | Out-Null
Test-Endpoint -Name "Price Tampering (Buy with 1 yuan)" -Url "/vul/logic/price_tamper.php" -Method "POST" -Body "buy=1&product_id=101&price=1" -ExpectedString "你花了 1 元买到了"

# 4. SSRF Cloud Metadata
$ssrfBody = "url=http://169.254.169.254/latest/meta-data/iam/security-credentials/admin&submit=1"
Test-Endpoint -Name "SSRF Cloud Metadata" -Url "/vul/ssrf/ssrf_cloud.php" -Method "POST" -Body $ssrfBody -ExpectedString "ASIAIOSFODNN7EXAMPLE"

# 5. DOM Clobbering
$clobberBody = "content=<a id=`"configURL`" href=`"data:text/plain,alert(1)`"></a>&submit=1"
Test-Endpoint -Name "DOM Clobbering" -Url "/vul/frontend/dom_clobbering.php" -Method "POST" -Body $clobberBody -ExpectedString "id=.configURL."

# 6. Defense Mode
Test-Endpoint -Name "Defense Mode UI" -Url "/vul/defense/defense.php" -ExpectedString "蓝队防守模式"

# 7. Docker Action (Test missing template)
Test-Endpoint -Name "Docker Lab Action (Missing ID)" -Url "/vul/dockerlab/dockerlab_action.php" -Method "POST" -Body "action=start&id=invalid-id" -ExpectedString "模板 ID 非法"

Write-Host "--- End Verification ---"
