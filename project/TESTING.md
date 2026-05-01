# Student Task Manager - Playwright Test Suite

This test suite provides comprehensive automated testing for the Student Task Manager application using Playwright and Pytest.

## Setup Instructions

### Prerequisites
- Python 3.7 or higher
- XAMPP or similar PHP/MySQL server running
- The Student Task Manager application accessible at `http://localhost/project`

### Installation

1. **Install dependencies from requirements.txt:**
   ```bash
   cd c:\xampp\htdocs\project
   pip install -r requirements.txt
   ```

2. **Install Playwright browsers:**
   ```bash
   python -m playwright install
   ```

3. **Verify installation:**
   ```bash
   pip list | findstr playwright
   ```

## Test Files Overview

### conftest.py
Pytest configuration and fixtures for:
- Browser instance creation
- Page fixtures
- Authenticated user fixtures
- Admin fixtures
- Base URL configuration

### test_auth.py
Authentication and registration tests:
- Login page loading
- Registration page loading
- Empty credential validation
- Invalid credential handling
- Valid user login
- Logout functionality
- Registration form validation
- Home page and navigation
- About page access

### test_tasks.py
Task management tests:
- Task page authentication
- Dashboard display
- Add task functionality
- Task form validation
- Task list display
- Edit/delete operations
- Study features (timer, notes, goals)
- Schedule management

### test_admin.py
Admin panel tests:
- Admin authentication
- Admin panel access
- Student management
- Admin logout
- Database connectivity
- Backend functionality

### test_ui.py
General UI and integration tests:
- Page loading verification
- Response status codes
- Page content structure
- CSS styling
- JavaScript functionality
- Responsive design (mobile, tablet, desktop)
- Accessibility features
- Navigation flows

## Running Tests

### Run all tests:
```bash
cd c:\xampp\htdocs\project
pytest
```

### Run specific test file:
```bash
pytest test_auth.py -v
pytest test_tasks.py -v
pytest test_admin.py -v
pytest test_ui.py -v
```

### Run specific test class:
```bash
pytest test_auth.py::TestAuthentication -v
```

### Run specific test:
```bash
pytest test_auth.py::TestAuthentication::test_login_page_loads -v
```

### Run tests with verbose output:
```bash
pytest -v
```

### Run tests with detailed output:
```bash
pytest -vv
```

### Run tests with short summary:
```bash
pytest --tb=short
```

### Run tests in headless mode (default):
```bash
pytest
```

### Run tests with browser visible (debug mode):
Edit `conftest.py` and change `headless=True` to `headless=False`

### Run tests with specific markers:
```bash
pytest -m asyncio
```

## Test Configuration

### Modifying Base URL
Edit `conftest.py`:
```python
BASE_URL = "http://localhost/project"  # Change this if needed
```

### Adjusting Timeout
Edit `conftest.py` fixture timeouts or `pytest.ini`:
```ini
timeout = 300  # 5 minutes
```

## Important Notes

### Database Setup
Tests assume the following database structure exists:
- Database: `practicaldb`
- Tables: `users`, `tasks`, `notes`, `goals`, etc.

### Test Credentials
Update test credentials in `conftest.py` if needed:
- Test user email: `test@student.com`
- Test user password: `Test@1234`
- Admin username: `admin`
- Admin password: `admin123`

### Server Requirements
- XAMPP/PHP server must be running
- MySQL/MariaDB must be accessible
- Application must be accessible at `http://localhost/project`

### Browser Automation
Tests run in Chromium browser by default. To test with Firefox or WebKit:

1. Modify `conftest.py`:
   ```python
   # Change this line:
   browser = await p.chromium.launch(headless=True)
   
   # To one of these:
   browser = await p.firefox.launch(headless=True)
   browser = await p.webkit.launch(headless=True)
   ```

## Troubleshooting

### Issue: "Connection refused" error
- **Solution**: Ensure XAMPP is running and application is accessible at the configured URL

### Issue: Tests timeout
- **Solution**: Increase timeout value in `pytest.ini` or `conftest.py`

### Issue: Database errors
- **Solution**: Verify database is running, check `db_connect.php` for correct credentials

### Issue: Tests fail on JavaScript errors
- **Solution**: This might indicate actual issues in the application - check browser console logs

### Issue: Playwright not found
- **Solution**: Run `pip install playwright` and `python -m playwright install`

## Test Results

Tests will generate output showing:
- Number of passed/failed tests
- Test execution time
- Any errors or failures with details
- Coverage statistics (if configured)

Example output:
```
collected 45 items

test_auth.py::TestAuthentication::test_login_page_loads PASSED
test_auth.py::TestAuthentication::test_registration_page_loads PASSED
test_auth.py::TestAuthentication::test_login_with_empty_credentials PASSED
...

==================== 45 passed in 12.34s ====================
```

## Advanced Usage

### Parallel execution (requires pytest-xdist):
```bash
pip install pytest-xdist
pytest -n auto
```

### Generate HTML report (requires pytest-html):
```bash
pip install pytest-html
pytest --html=report.html
```

### Generate JUnit XML report:
```bash
pytest --junit-xml=report.xml
```

### Run tests with coverage (requires pytest-cov):
```bash
pip install pytest-cov
pytest --cov
```

## CI/CD Integration

These tests can be integrated into CI/CD pipelines. Example for GitHub Actions:

```yaml
name: Playwright Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-python@v2
        with:
          python-version: 3.9
      - run: pip install -r requirements.txt
      - run: python -m playwright install --with-deps
      - run: pytest
```

## Support

For issues or questions:
1. Check test output for specific error messages
2. Review the test file that failed
3. Check if the application functionality is working manually
4. Verify database and server configuration
5. Check Playwright documentation: https://playwright.dev/python/

## License

These tests are part of the Student Task Manager project.
