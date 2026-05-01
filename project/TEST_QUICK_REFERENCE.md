# Quick Test Reference Card

## Installation Verification
```bash
pip list | findstr playwright
pip list | findstr pytest
python -m playwright --version
```

## Collect Tests
```bash
python -m pytest --collect-only
```

## Run All Tests
```bash
python -m pytest
```

## Run with Options
```bash
# Verbose output
python -m pytest -v

# Very verbose
python -m pytest -vv

# Stop on first failure
python -m pytest -x

# Show print statements
python -m pytest -s

# Quiet mode
python -m pytest -q

# Short traceback
python -m pytest --tb=short
```

## Run Specific Tests
```bash
# Single test file
python -m pytest test_auth.py

# Single test class
python -m pytest test_auth.py::TestAuthentication

# Single test
python -m pytest test_auth.py::TestAuthentication::test_login_page_loads

# Multiple files
python -m pytest test_auth.py test_tasks.py

# By keyword
python -m pytest -k "login"

# By marker
python -m pytest -m asyncio
```

## Debug Mode
```bash
# Run with visible browser (headless=False in conftest.py)
python -m pytest -s -vv

# With extra debugging
python -m pytest --tb=long -vv
```

## Reports
```bash
# HTML report (install: pip install pytest-html)
python -m pytest --html=report.html

# XML report
python -m pytest --junit-xml=report.xml

# JSON report (install: pip install pytest-json-report)
python -m pytest --json-report

# Coverage report (install: pip install pytest-cov)
python -m pytest --cov
```

## Run in Parallel
```bash
# (install: pip install pytest-xdist)
python -m pytest -n auto
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Module not found" | Run `pip install -r requirements.txt` |
| Playwright errors | Run `python -m playwright install` |
| Connection refused | Ensure XAMPP is running |
| Timeout errors | Increase timeout or check app performance |
| Database errors | Verify database exists and MySQL running |
| "Unknown config option" | Remove unknown options from pytest.ini |

## Test Files Overview

| File | Tests | Purpose |
|------|-------|---------|
| test_auth.py | 11 | Login, registration, authentication |
| test_tasks.py | 15 | Task management, CRUD operations |
| test_admin.py | 14 | Admin panel, permissions |
| test_ui.py | 21 | General UI, navigation, accessibility |
| **Total** | **61** | **Full application testing** |

## Key Files

- `conftest.py` - Test configuration & fixtures
- `pytest.ini` - Pytest settings
- `requirements.txt` - Python dependencies
- `TESTING.md` - Detailed testing guide
- `PLAYWRIGHT_SETUP.md` - Setup information

## Typical Workflow

1. Start XAMPP: `Start XAMPP`
2. Verify app: `http://localhost/project`
3. Run tests: `python -m pytest -v`
4. Review results and fix issues
5. Re-run: `python -m pytest`

## Environment Check

```bash
# Show Python version
python --version

# Show installed packages
pip list

# Show pytest info
python -m pytest --version

# Show Playwright info
python -m playwright --version
```

---

For more details, see **TESTING.md** and **PLAYWRIGHT_SETUP.md**
