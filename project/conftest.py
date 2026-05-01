"""
Pytest configuration and fixtures for Student Task Manager tests
"""
import pytest
from playwright.async_api import async_playwright
import asyncio


BASE_URL = "http://localhost/project"


@pytest.fixture(scope="session")
def event_loop():
    """Create event loop for async tests"""
    loop = asyncio.get_event_loop_policy().new_event_loop()
    yield loop
    loop.close()


@pytest.fixture
async def browser():
    """Create a browser instance for each test"""
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        yield browser
        await browser.close()


@pytest.fixture
async def page(browser):
    """Create a new page for each test"""
    page = await browser.new_page()
    yield page
    await page.close()


@pytest.fixture
async def authenticated_page(browser):
    """Create an authenticated page with user logged in"""
    page = await browser.new_page()
    await page.goto(f"{BASE_URL}/login.php")
    
    # Login with test user
    await page.fill('input[name="email"]', "test@student.com")
    await page.fill('input[name="password"]', "Test@1234")
    await page.click('button[type="submit"]')
    
    # Wait for redirect to dashboard
    await page.wait_for_load_state("networkidle")
    
    yield page
    await page.close()


@pytest.fixture
async def admin_page(browser):
    """Create an authenticated admin page"""
    page = await browser.new_page()
    await page.goto(f"{BASE_URL}/admin_login.php")
    
    # Login with admin credentials
    await page.fill('input[name="username"]', "admin")
    await page.fill('input[name="password"]', "admin123")
    await page.click('button[type="submit"]')
    
    # Wait for redirect to admin panel
    await page.wait_for_load_state("networkidle")
    
    yield page
    await page.close()
