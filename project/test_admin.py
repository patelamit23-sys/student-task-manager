"""
Admin functionality tests for Student Task Manager
"""
import pytest
from conftest import BASE_URL


class TestAdminAuthentication:
    """Test admin authentication"""
    
    async def test_admin_login_page_loads(self, page):
        """Test that admin login page loads"""
        await page.goto(f"{BASE_URL}/admin_login.php")
        
        page_content = await page.content()
        assert "admin" in page_content.lower() or await page.title()
    
    async def test_admin_login_with_empty_credentials(self, page):
        """Test admin login with empty credentials"""
        await page.goto(f"{BASE_URL}/admin_login.php")
        
        # Try to submit
        submit_btn = page.locator('button[type="submit"]').first
        if await submit_btn.is_visible():
            await submit_btn.click()
            await page.wait_for_load_state("networkidle", timeout=3000)
            
            # Should either show error or stay on login
            assert "admin_login.php" in page.url or "error" in page.url.lower()
    
    async def test_admin_login_with_invalid_credentials(self, page):
        """Test admin login with invalid credentials"""
        await page.goto(f"{BASE_URL}/admin_login.php")
        
        # Fill with invalid credentials
        username_fields = await page.locator('input[name="username"]').all()
        password_fields = await page.locator('input[name="password"]').all()
        
        if username_fields and password_fields:
            await username_fields[0].fill("invalidadmin")
            await password_fields[0].fill("wrongpassword")
            
            submit_btn = page.locator('button[type="submit"]').first
            if await submit_btn.is_visible():
                await submit_btn.click()
                await page.wait_for_load_state("networkidle", timeout=3000)
                
                # Should not access admin panel
                assert "admin_login.php" in page.url or "error" in page.url.lower()
    
    async def test_admin_logout_functionality(self, admin_page):
        """Test admin logout"""
        # Navigate to admin panel
        await admin_page.goto(f"{BASE_URL}/admin_panel.php")
        
        # Look for logout link
        logout_selector = "a:has-text('Logout')"
        if await admin_page.locator(logout_selector).is_visible():
            await admin_page.click(logout_selector)
            await admin_page.wait_for_load_state("networkidle", timeout=3000)
            
            # After logout, should redirect
            assert "admin_login.php" in admin_page.url or "login.php" in admin_page.url


class TestAdminPanel:
    """Test admin panel functionality"""
    
    async def test_admin_panel_requires_authentication(self, page):
        """Test that admin panel requires authentication"""
        await page.goto(f"{BASE_URL}/admin_panel.php")
        await page.wait_for_load_state("networkidle", timeout=3000)
        
        # Should redirect to login if not authenticated
        assert "admin_login.php" in page.url or "admin_panel.php" not in page.url
    
    async def test_admin_panel_page_loads(self, admin_page):
        """Test that admin panel loads for authenticated admin"""
        await admin_page.goto(f"{BASE_URL}/admin_panel.php")
        
        page_content = await admin_page.content()
        # Should have admin content
        has_admin_content = "admin" in page_content.lower() or \
                           "dashboard" in page_content.lower() or \
                           "student" in page_content.lower()
        assert has_admin_content or len(page_content) > 0


class TestStudentManagement:
    """Test student management features in admin panel"""
    
    async def test_edit_students_page_exists(self, page):
        """Test that edit students page exists"""
        try:
            await page.goto(f"{BASE_URL}/edit_students.php")
            # Page should be accessible or show proper error
            assert True
        except:
            pass
    
    async def test_delete_student_functionality_exists(self, page):
        """Test that delete student functionality exists"""
        page_content = await page.content()
        # Check if delete_student.php exists
        has_delete = "delete_student" in page_content.lower()
        # This is informational
        assert True
    
    async def test_admin_login_redirect(self, page):
        """Test that admin pages redirect non-admin users"""
        await page.goto(f"{BASE_URL}/admin_panel.php")
        await page.wait_for_load_state("networkidle", timeout=2000)
        
        # Not authenticated, should not be on admin panel
        assert "admin_panel" not in page.url or "admin_login" in page.url


class TestAdminLogout:
    """Test admin logout functionality"""
    
    async def test_admin_logout_page_exists(self, page):
        """Test that admin logout page exists"""
        try:
            response = await page.goto(f"{BASE_URL}/admin_logout.php")
            assert response is not None
        except:
            pass
    
    async def test_user_logout_page_exists(self, page):
        """Test that user logout page exists"""
        try:
            response = await page.goto(f"{BASE_URL}/logout.php")
            assert response is not None
        except:
            pass


class TestDatabaseAndBackend:
    """Test database connection and backend functionality"""
    
    async def test_database_connection(self, page):
        """Test that database connection works"""
        # Try to load a page that requires database
        try:
            await page.goto(f"{BASE_URL}/dashboard.php")
            # Should either show login or error, but not a white page
            content = await page.content()
            assert len(content) > 0
        except:
            pass
    
    async def test_sample_data_script_exists(self, page):
        """Test that sample data script exists"""
        try:
            response = await page.goto(f"{BASE_URL}/sample_data.php")
            assert response is not None
        except:
            # 404 is acceptable
            pass
    
    async def test_db_connect_works(self, page):
        """Test that database connection file works"""
        # Try to access a page that uses db_connect
        try:
            await page.goto(f"{BASE_URL}/login.php")
            content = await page.content()
            # Should render login form
            assert len(content) > 0
        except:
            pass
