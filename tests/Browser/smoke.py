from playwright.sync_api import sync_playwright
import json
from pathlib import Path
import os
from urllib.parse import urlparse
base=os.getenv('P4I_SMOKE_URL', 'http://127.0.0.1:8099')
assert urlparse(base).hostname in ['127.0.0.1', 'localhost'], 'Smoke tests require a loopback test server.'
output=Path(os.getenv('P4I_SMOKE_OUTPUT', '/tmp/p4i-browser-smoke'))
(output/'screenshots').mkdir(parents=True,exist_ok=True)
results=[]
with sync_playwright() as p:
    browser=p.chromium.launch(executable_path='/usr/bin/chromium',headless=True,args=['--no-sandbox'])
    for width,height,label in [(1440,1000,'desktop'),(390,844,'mobile')]:
        context=browser.new_context(viewport={'width':width,'height':height})
        context.route('**/*', lambda route: route.continue_() if route.request.url.startswith(base) else route.abort())
        page=context.new_page()
        errors=[]
        page.on('pageerror',lambda error: errors.append(str(error)))
        failed_assets=[]
        page.on('response',lambda r: failed_assets.append(r.url) if r.status>=400 and ('security-build/' in r.url) else None)
        for path in ['/','/library','/library/smoke-publication','/login','/register']:
            r=page.goto(base+path,wait_until='networkidle')
            assert r.status==200,(path,r.status)
            overflow=page.evaluate('document.documentElement.scrollWidth > innerWidth + 2')
            assert not overflow,(label,path,'horizontal overflow')
            results.append({'viewport':label,'path':path,'http':r.status,'overflow':overflow})
        assert not failed_assets,failed_assets
        assert not errors,errors
        page.goto(base+'/library',wait_until='networkidle')
        page.screenshot(path=str(output/'screenshots'/f'{label}-library.png'),full_page=True)
        for role,paths in [('reader',['/my-library','/my-orders','/profile']),('author',['/author/dashboard','/author/submissions','/author/books']),('admin',['/admin/library','/admin/payments','/admin/users','/admin/reports','/admin/curation'])]:
            page.goto(base+'/login',wait_until='networkidle')
            page.locator('input[name=email]').fill(role+'@example.test')
            page.locator('input[name=password]').fill('synthetic-test-password')
            page.locator('button[type=submit]').click()
            page.wait_for_load_state('networkidle')
            assert '/login' not in page.url,('login failed',role)
            for path in paths:
                r=page.goto(base+path,wait_until='networkidle')
                assert r.status==200,(role,path,r.status)
                assert not page.evaluate('document.documentElement.scrollWidth > innerWidth + 2'),(role,label,path,'overflow')
                results.append({'viewport':label,'role':role,'path':path,'http':r.status})
            if role=='reader':
                r=page.goto(base+'/admin/library',wait_until='networkidle')
                assert r.status==403
                results.append({'viewport':label,'role':role,'path':'/admin/library','http':403})
            page.goto(base+'/my-library',wait_until='networkidle')
            with page.expect_navigation(wait_until='networkidle'):
                page.locator('form[action$="/logout"]').first.evaluate('(form)=>form.submit()')
        context.close()
    browser.close()
(output/'browser-smoke.json').write_text(json.dumps(results,indent=2))
print('Browser smoke PASS:',len(results),'checks; desktop/mobile; built assets; synthetic roles only.')
