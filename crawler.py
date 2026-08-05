import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse

start_url = "http://127.0.0.1:8765/index.php"
base_netloc = urlparse(start_url).netloc

visited = set()
to_visit = [start_url]

errors_found = []

# Mock a session to keep cookies if any
session = requests.Session()

while to_visit:
    url = to_visit.pop(0)
    if url in visited:
        continue
    
    visited.add(url)
    
    try:
        response = session.get(url, timeout=5)
        # Check for errors in HTML
        html = response.text
        
        # PHP error keywords
        error_keywords = ["<b>Warning</b>:", "<b>Notice</b>:", "<b>Fatal error</b>:", "<b>Parse error</b>:", "<b>Deprecated</b>:"]
        
        found = []
        for kw in error_keywords:
            if kw in html:
                found.append(kw)
        
        # Plain text keywords if html tags are stripped
        plain_keywords = ["Warning: ", "Notice: ", "Fatal error: ", "Parse error: ", "Deprecated: "]
        for kw in plain_keywords:
            if kw in html and kw.replace(": ", "</b>:") not in html:
                found.append(kw)
                
        if found:
            errors_found.append({"url": url, "errors": found})
            print(f"[!] Error found at {url}: {found}")
        else:
            print(f"[+] Clean: {url}")
            
        # Parse links
        soup = BeautifulSoup(html, "html.parser")
        links = soup.find_all("a", href=True)
        print(f"Found {len(links)} links on {url}")
        for a in links:
            href = a["href"]
            if href.startswith("javascript:") or href.startswith("mailto:") or href.startswith("#"):
                continue
            full_url = urljoin(url, href)
            parsed = urlparse(full_url)
            
            # Strip fragment
            full_url = full_url.split('#')[0]
            
            if parsed.netloc == base_netloc and full_url not in visited and full_url not in to_visit:
                # Ignore logout or destructive links that might ruin the session or hang
                if "logout" not in full_url.lower() and "reset" not in full_url.lower() and "delete" not in full_url.lower():
                    to_visit.append(full_url)
                        
    except Exception as e:
        print(f"[-] Request failed for {url}: {e}")

print("\n--- Summary ---")
if errors_found:
    print(f"Total pages with errors: {len(errors_found)}")
    for item in errors_found:
        print(f"{item['url']} -> {item['errors']}")
else:
    print("ALL CLEAR! No PHP errors found on crawled pages.")
