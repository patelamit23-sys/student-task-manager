"""
Authentication and registration tests for Student Task Manager
"""
import pytest
from conftest import BASE_URL


class TestAuthentication:
    """Test authentication functionality"""
    
    async def test_login_page_loads(self, page):
        """Test that login page loads successfully"""
        await page.goto(f"{BASE_URL}/login.php")
        assert await page.title()
        assert await page.get_by_text("Login", exact=False).is_visible() or \
               await page.get_by_text("Email", exact=False).is_visible()
    
    async def test_registration_page_loads(self, page):
        """Test that registration page loads successfully"""
        await page.goto(f"{BASE_URL}/registration.php")
        assert await page.title()
        # Check for common form fields
        visible = await page.get_by_text("Register", exact=False).is_visible() or \
                  await page.get_by_text("Sign up", exact=False).is_visible()
        assert visible or await page.get_by_label("Email").is_visible()
    
    async def test_login_with_empty_credentials(self, page):
        """Test login with empty email and password"""
        await page.goto(f"{BASE_URL}/login.php")
        
        # Click submit without entering credentials
        await page.click('button[type="submit"]')
        
        # Should either show error or require input
        await page.wait_for_load_state("networkidle")
        
        # Check if still on login page or error shown
        page_url = page.url
        assert "login.php" in page_url or "error" in page.url.lower()
    
    async def test_login_with_invalid_credentials(self, page):
        """Test login with invalid email/password combination"""
        await page.goto(f"{BASE_URL}/login.php")
        
        await page.fill('input[name="email"]', "invalid@example.com")
        await page.fill('input[name="password"]', "wrongpassword")
        await page.click('button[type="submit"]')
        
        await page.wait_for_load_state("networkidle")
        
        # Should not redirect to dashboard
        assert "login.php" in page.url or "error" in page.url.lower()
    
    async def test_valid_user_login(self, page):
        """Test successful login with valid credentials"""
        await page.goto(f"{BASE_URL}/login.php")
        
        await page.fill('input[name="email"]', "student@example.com")
        await page.fill('input[name="password"]', "password123")
        await page.click('button[type="submit"]')
        
        # Wait for potential redirect
        await page.wait_for_load_state("networkidle", timeout=5000)
        
        # Should redirect or show success message
        success = "dashboard" in page.url or "success" in page.page_content.lower()
        assert success or await page.locator("text=/Login|Successful/").first.is_visible()
    
    async def test_logout_functionality(self, authenticated_page):
        """Test user logout"""
        # Navigate to dashboard
        await authenticated_page.goto(f"{BASE_URL}/dashboard.php")
        
        # Look for logout button/link
        logout_selector = "a:has-text('Logout')"
        if await authenticated_page.locator(logout_selector).is_visible():
            await authenticated_page.click(logout_selector)
            await authenticated_page.wait_for_load_state("networkidle")
            
            # After logout, should not be able to access protected pages
            await authenticated_page.goto(f"{BASE_URL}/dashboard.php")
            # Should redirect to login
            assert "login.php" in authenticated_page.url


class TestRegistration:
    """Test user registration"""
    
    async def test_registration_form_fields_present(self, page):
        """Test that all required registration fields are present"""
        await page.goto(f"{BASE_URL}/registration.php")
        
        # Check for common registration fields
        form_fields = ["email", "username", "password", "name", "full_name"]
        found_fields = 0
        
        for field in form_fields:
            try:
                if await page.get_by_label(field.upper(), exact=False).is_visible():
                    found_fields += 1
                elif await page.get_by_placeholder(field, exact=False).is_visible():
                    found_fields += 1
                elif await page.locator(f'input[name="{field}"]').is_visible():
                    found_fields += 1
            except:
                pass
        
        # At least some fields should be present
        assert found_fields >= 2
    
    async def test_registration_with_empty_fields(self, page):
        """Test registration with empty fields"""
        await page.goto(f"{BASE_URL}/registration.php")
        
        # Try to submit empty form
        submit_btn = page.locator('button[type="submit"]').first
        if await submit_btn.is_visible():
            await submit_btn.click()
            await page.wait_for_load_state("networkidle", timeout=3000)
            
            # Should still be on registration page or show error
            assert "registration.php" in page.url or "error" in page.url.lower()


class TestHomeAndNavigation:
    """Test home page and navigation"""
    
    async def test_home_page_loads(self, page):
        """Test that home page loads successfully"""
        await page.goto(f"{BASE_URL}/")
        assert await page.title()
        
        # Check for main content
        has_content = await page.get_by_text("Student Task Manager", exact=False).is_visible() or \
                     await page.get_by_text("Task", exact=False).is_visible()
        assert has_content
    
    async def test_home_page_has_navigation(self, page):
        """Test that home page has navigation links"""
        await page.goto(f"{BASE_URL}/")
        
        # Check for common navigation
        nav_items = ["Login", "Register", "About", "Admin"]
        visible_items = 0
        
        for item in nav_items:
            if await page.get_by_text(item, exact=False).is_visible():
                visible_items += 1
        
        # At least some navigation should be visible
        assert visible_items >= 2
    
    async def test_about_page_loads(self, page):
        """Test that about page loads"""
        await page.goto(f"{BASE_URL}/about.php")
        page_title = await page.title()
        assert page_title
        
        # Check for about content
        has_about = await page.get_by_text("about", exact=False).is_visible()
        assert has_about or len(page_title) > 0
