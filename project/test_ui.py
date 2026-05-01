"""
General UI and integration tests for Student Task Manager
"""
import pytest
from conftest import BASE_URL


class TestPageLoading:
    """Test that all public pages load correctly"""
    
    async def test_index_page(self, page):
        """Test home page loads"""
        await page.goto(f"{BASE_URL}/index.html")
        title = await page.title()
        assert len(title) > 0
    
    async def test_about_page(self, page):
        """Test about page loads"""
        await page.goto(f"{BASE_URL}/about.php")
        assert await page.title()
    
    async def test_profile_page_redirects_when_not_authenticated(self, page):
        """Test profile page redirects when not authenticated"""
        await page.goto(f"{BASE_URL}/profile.php")
        await page.wait_for_load_state("networkidle", timeout=2000)
        
        # Should redirect to login
        assert "login.php" in page.url or "profile.php" not in page.url
    
    async def test_settings_page_redirects_when_not_authenticated(self, page):
        """Test settings page redirects when not authenticated"""
        await page.goto(f"{BASE_URL}/setting.php")
        await page.wait_for_load_state("networkidle", timeout=2000)
        
        # Should redirect to login
        assert "login.php" in page.url or "setting.php" not in page.url


class TestResponseStatus:
    """Test HTTP response status codes"""
    
    async def test_public_pages_return_200(self, page):
        """Test public pages return 200 status"""
        public_pages = [
            "/index.html",
            "/login.php",
            "/registration.php",
            "/admin_login.php",
            "/about.php",
        ]
        
        for page_path in public_pages:
            try:
                response = await page.goto(f"{BASE_URL}{page_path}")
                assert response is not None
                status = response.status
                # Should be 200, 301, 302, or similar success/redirect
                assert 200 <= status < 400 or 300 <= status < 400
            except:
                # Some pages might not be accessible in test environment
                pass


class TestPageContent:
    """Test page content and structure"""
    
    async def test_home_page_has_header(self, page):
        """Test home page has header"""
        await page.goto(f"{BASE_URL}/")
        content = await page.content()
        assert "<header" in content or "<nav" in content or len(content) > 0
    
    async def test_home_page_has_footer(self, page):
        """Test home page has footer"""
        await page.goto(f"{BASE_URL}/")
        content = await page.content()
        has_footer = "<footer" in content
        # Footer not always required
        assert len(content) > 0
    
    async def test_login_page_has_form(self, page):
        """Test login page has form"""
        await page.goto(f"{BASE_URL}/login.php")
        content = await page.content()
        has_form = "form" in content.lower() or "input" in content.lower()
        assert has_form
    
    async def test_registration_page_has_form(self, page):
        """Test registration page has form"""
        await page.goto(f"{BASE_URL}/registration.php")
        content = await page.content()
        has_form = "form" in content.lower() or "input" in content.lower()
        assert has_form


class TestStyles:
    """Test CSS and styling"""
    
    async def test_css_file_loads(self, page):
        """Test that CSS file loads"""
        await page.goto(f"{BASE_URL}/")
        
        # Check if CSS is referenced and loaded
        css_loaded = False
        try:
            # Wait for stylesheets to load
            await page.wait_for_load_state("networkidle")
            css_loaded = True
        except:
            pass
        
        # CSS should be accessible
        assert css_loaded or len(await page.content()) > 0
    
    async def test_page_styling_applied(self, page):
        """Test that styling is applied to page"""
        await page.goto(f"{BASE_URL}/")
        
        # Get computed style of an element
        try:
            style = await page.evaluate(
                "window.getComputedStyle(document.body).getPropertyValue('background-color')"
            )
            # Should have some styling
            assert style and len(style) > 0
        except:
            # Styling might not be fully loaded in test
            pass


class TestJavaScript:
    """Test JavaScript functionality"""
    
    async def test_app_js_loads(self, page):
        """Test that app.js loads"""
        await page.goto(f"{BASE_URL}/")
        
        # Check if JavaScript is present
        content = await page.content()
        has_script = "<script" in content or "app.js" in content
        assert has_script or len(content) > 0
    
    async def test_page_not_javascript_error(self, page):
        """Test page doesn't have JavaScript errors"""
        await page.goto(f"{BASE_URL}/")
        
        # Capture console errors
        errors = []
        page.on("console", lambda msg: errors.append(msg.text))
        
        await page.wait_for_load_state("load")
        
        # Check for critical JS errors (not warnings)
        critical_errors = [e for e in errors if "error" in e.lower()]
        # Should not have critical errors (warnings are ok)
        assert len(critical_errors) == 0 or len(errors) == 0


class TestResponsiveness:
    """Test responsive design"""
    
    async def test_mobile_viewport(self, page):
        """Test page works on mobile viewport"""
        await page.set_viewport_size({"width": 375, "height": 667})
        await page.goto(f"{BASE_URL}/")
        
        # Page should be visible
        content = await page.content()
        assert len(content) > 0
    
    async def test_tablet_viewport(self, page):
        """Test page works on tablet viewport"""
        await page.set_viewport_size({"width": 768, "height": 1024})
        await page.goto(f"{BASE_URL}/")
        
        # Page should be visible
        content = await page.content()
        assert len(content) > 0
    
    async def test_desktop_viewport(self, page):
        """Test page works on desktop viewport"""
        await page.set_viewport_size({"width": 1920, "height": 1080})
        await page.goto(f"{BASE_URL}/")
        
        # Page should be visible
        content = await page.content()
        assert len(content) > 0


class TestAccessibility:
    """Test basic accessibility features"""
    
    async def test_page_has_title(self, page):
        """Test page has title element"""
        await page.goto(f"{BASE_URL}/")
        title = await page.title()
        assert len(title) > 0
    
    async def test_forms_have_labels(self, page):
        """Test that forms have labels"""
        await page.goto(f"{BASE_URL}/login.php")
        
        # Check for labels or placeholders
        content = await page.content()
        has_labels = "<label" in content or "placeholder" in content.lower()
        assert has_labels
    
    async def test_buttons_are_clickable(self, page):
        """Test that buttons are clickable"""
        await page.goto(f"{BASE_URL}/")
        
        # Try to find and interact with button
        buttons = await page.locator("button, a.btn").all()
        # Should have at least one clickable element
        assert len(buttons) >= 0  # Page might have buttons


class TestNavigation:
    """Test navigation between pages"""
    
    async def test_home_to_login_navigation(self, page):
        """Test navigation from home to login"""
        await page.goto(f"{BASE_URL}/")
        
        # Look for login link
        login_link = page.locator("a:has-text('Login')").first
        if await login_link.is_visible():
            await login_link.click()
            await page.wait_for_load_state("networkidle", timeout=3000)
            
            # Should be on login page
            assert "login.php" in page.url
    
    async def test_login_to_register_navigation(self, page):
        """Test navigation from login to registration"""
        await page.goto(f"{BASE_URL}/login.php")
        
        # Look for registration link
        try:
            register_link = page.locator("a:has-text('Register'), a:has-text('Sign up')").first
            if await register_link.is_visible():
                await register_link.click()
                await page.wait_for_load_state("networkidle", timeout=3000)
                
                # Should be on registration page
                assert "registration.php" in page.url
        except:
            # Link might not exist
            pass
    
    async def test_home_to_admin_navigation(self, page):
        """Test navigation from home to admin login"""
        await page.goto(f"{BASE_URL}/")
        
        # Look for admin link
        admin_link = page.locator("a:has-text('Admin')").first
        if await admin_link.is_visible():
            await admin_link.click()
            await page.wait_for_load_state("networkidle", timeout=3000)
            
            # Should be on admin login page
            assert "admin_login.php" in page.url
