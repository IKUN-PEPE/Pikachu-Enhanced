import requests
import warnings
from urllib3.exceptions import InsecureRequestWarning
warnings.simplefilter('ignore', InsecureRequestWarning)

base_url = "http://127.0.0.1:8765"
session = requests.Session()

def print_result(name, success, details=""):
    status = "\033[92m[PASS]\033[0m" if success else "\033[91m[FAIL]\033[0m"
    if details:
        print(f"{status} {name} - {details}")
    else:
        print(f"{status} {name}")

tests = [
    # 1. burteforce
    {"name": "Burteforce (Form)", "url": "/vul/burteforce/bf_form.php", "method": "POST", "data": {"username": "admin", "password": "password", "submit": "Login"}, "expect": "admin"},
    
    # 2. clickjacking
    {"name": "Clickjacking", "url": "/vul/clickjacking/clickjacking.php", "method": "GET", "expect": "200"},
    
    # 3. cors
    {"name": "CORS Misconfig", "url": "/vul/cors/cors_api.php", "method": "GET", "headers": {"Origin": "http://evil.com"}, "expect": "200"},
    
    # 4. csrf
    {"name": "CSRF (GET)", "url": "/vul/csrf/csrfget/csrf_get_edit.php?sex=boy&phonenum=123&add=nyc&email=test@test.com&submit=submit", "method": "GET", "expect": "200"},
    
    # 5. infoleak
    {"name": "Info Leak", "url": "/vul/infoleak/infoleak.php", "method": "GET", "expect": "200"},
    
    # 6. logic
    {"name": "Logic Flaw (Price Tamper)", "url": "/vul/logic/price_tamper.php", "method": "POST", "data": {"price": "0.01", "submit": "Buy"}, "expect": "0.01"},
    
    # 7. unserilization
    {"name": "PHP Unserialization", "url": "/vul/unserilization/unser.php", "method": "POST", "data": {"o": 'O:1:"S":1:{s:4:"test";s:29:"<script>alert(\'xss\')</script>";}'}, "expect": "alert('xss')"},
    
    # 8. urlredirect
    {"name": "URL Redirect", "url": "/vul/urlredirect/urlredirect.php?url=http://www.google.com", "method": "GET", "expect_header": "Location", "expect_header_val": "http://www.google.com"},
]

passed = 0
failed = 0

print("--- Starting Missing Category Tests ---")
for t in tests:
    try:
        if t["method"] == "GET":
            headers = t.get("headers", {})
            resp = session.get(base_url + t["url"], headers=headers, allow_redirects=False)
        else:
            resp = session.post(base_url + t["url"], data=t.get("data", {}), allow_redirects=False)
            
        success = False
        details = ""
        
        if "expect" in t:
            if t["expect"] == "200":
                success = (resp.status_code == 200)
            else:
                success = (t["expect"] in resp.text)
        elif "expect_header" in t:
            val = resp.headers.get(t["expect_header"], "")
            success = (t["expect_header_val"] in val)
            
        if success:
            print_result(t["name"], True)
            passed += 1
        else:
            print_result(t["name"], False, f"Status: {resp.status_code}")
            failed += 1
    except Exception as e:
        print_result(t["name"], False, str(e))
        failed += 1

print("\n--- Summary ---")
print(f"Total Tests: {len(tests)}")
print(f"Passed: {passed}")
print(f"Failed: {failed}")
