# Integration Testing Guide

## Overview

This guide covers the comprehensive integration testing, security auditing, and performance testing for the Multi-User Portfolio Platform. These tests validate the entire system end-to-end, ensuring all components work together correctly, securely, and efficiently.

## Test Suites

### 1. End-to-End Integration Tests (`tests/Integration/EndToEndIntegrationTest.php`)

Tests complete user workflows from start to finish:

#### Test 1: Complete Registration and Login Flow
- User registration with validation
- Account creation in database
- Login with credentials
- Session creation and validation
- Logout and session termination

**Validates:** Requirements 1.1-1.7, 2.1-2.6, 13.1-13.7

#### Test 2: Complete Portfolio Management Flow
- Portfolio item creation
- Item editing and updates
- Item deletion with cascade cleanup
- Access control verification

**Validates:** Requirements 4.1-4.7, 5.1-5.7

#### Test 3: Complete Customization Flow
- Default settings retrieval
- Customization updates
- CSS generation from settings
- Reset to defaults

**Validates:** Requirements 6.1-6.7

#### Test 4: Complete PDF Export Flow
- PDF generation with all items
- Selective item export
- Customization styling application
- Performance validation (30-second target)

**Validates:** Requirements 8.1-8.7

#### Test 5: Complete Showcase Browsing and Search Flow
- Public portfolio listing
- Pagination (20 per page)
- Search by keyword
- Filter by program
- Sort portfolios

**Validates:** Requirements 9.1-9.7, 10.1-10.7

#### Test 6: Complete Admin Moderation Flow
- Admin portfolio access
- Content flagging
- Content hiding/unhiding
- Action logging

**Validates:** Requirements 15.1-15.7

### 2. Security Audit Tests (`tests/Security/SecurityAuditTest.php`)

Comprehensive security testing covering all attack vectors:

#### SQL Injection Prevention Tests
- **Registration Fields**: Tests SQL injection patterns in email, password, and name fields
- **Login Credentials**: Attempts to bypass authentication with SQL injection
- **Search Queries**: Tests injection in portfolio search functionality
- **Validation**: Ensures prepared statements prevent all SQL injection attempts

**Attack Patterns Tested:**
- `admin' OR '1'='1`
- `admin'--`
- `'; DROP TABLE users--`
- `1' UNION SELECT NULL--`

**Validates:** Requirements 18.1

#### XSS Prevention Tests
- **Profile Fields**: Tests XSS injection in bio and profile data
- **Portfolio Content**: Tests XSS in portfolio item titles and descriptions
- **Output Escaping**: Verifies all user content is properly escaped

**Attack Patterns Tested:**
- `<script>alert("XSS")</script>`
- `<img src=x onerror=alert("XSS")>`
- `<svg onload=alert("XSS")>`
- `javascript:alert("XSS")`
- Event handlers (onload, onerror, onfocus)

**Validates:** Requirements 18.2

#### CSRF Protection Tests
- Token generation and validation
- Invalid token rejection
- Missing token rejection
- Token regeneration after use

**Validates:** Requirements 13.7

#### Access Control Tests
- **Portfolio Item Modification**: Verifies users cannot edit/delete others' items
- **Private Portfolio Access**: Ensures private portfolios are not publicly accessible
- **Ownership Validation**: Tests access control enforcement

**Validates:** Requirements 5.5, 7.2, 18.5

#### Rate Limiting Tests
- **Login Attempts**: Tests brute force protection (5 attempts per 15 minutes)
- **File Uploads**: Tests upload rate limiting (20 per hour)
- **Effectiveness**: Verifies rate limits are properly enforced

**Validates:** Requirements 2.7, 18.7

#### Session Security Tests
- **HTTP-Only Cookies**: Verifies secure cookie configuration
- **Token Regeneration**: Tests session token regeneration on login
- **Expiration**: Validates 24-hour session expiration

**Validates:** Requirements 13.2, 13.4, 2.6

### 3. Performance Tests (`tests/Performance/PerformanceTest.php`)

Load and performance testing under various conditions:

#### Test 1: Showcase Load with 100+ Portfolios
- Creates 150 portfolios with 3 items each
- Measures showcase page load time
- Tests pagination performance
- **Target**: Load within 3 seconds

**Validates:** Requirements 17.1, 17.6

#### Test 2: PDF Generation with Large Portfolios
- Creates portfolio with 50 items
- Measures PDF generation time
- Tests customization styling application
- **Target**: Generate within 30 seconds

**Validates:** Requirements 8.3, 17.3

#### Test 3: Concurrent User Sessions
- Simulates 50 concurrent user logins
- Tests session management under load
- Validates all sessions remain valid
- Measures average login time

**Validates:** Requirements 13.1, 13.3

#### Test 4: Database Query Performance
- Tests with 100 users and 500 portfolio items
- Validates index effectiveness
- Measures query execution times
- Tests:
  - User lookup by email
  - Portfolio items retrieval
  - Public portfolio filtering
  - Tag search performance

**Validates:** Requirements 17.4, 17.5

#### Test 5: Image Loading and Caching
- Uploads 10 test images
- Measures upload performance
- Tests file retrieval speed
- Validates thumbnail generation
- **Target**: Retrieval < 100ms, Thumbnail < 1 second

**Validates:** Requirements 17.2, 17.7

#### Test 6: Search Performance with Complex Queries
- Creates 50 portfolios with varied data
- Tests simple keyword search
- Tests program filtering
- Tests combined search and filter
- **Target**: All searches < 500ms

**Validates:** Requirements 10.1-10.7, 17.4

## Running the Tests

### Prerequisites

1. **PHPUnit Installation**
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. **Test Database Setup**
   ```bash
   # Create test database
   mysql -u root -p -e "CREATE DATABASE portfolio_test;"
   
   # Run migrations on test database
   php database/migrate.php --database=portfolio_test
   ```

3. **Environment Configuration**
   ```bash
   export TEST_DB_HOST="localhost"
   export TEST_DB_NAME="portfolio_test"
   export TEST_DB_USER="your_user"
   export TEST_DB_PASS="your_password"
   ```

### Running All Tests

Use the provided test runner script:

```bash
chmod +x tests/run-integration-tests.sh
./tests/run-integration-tests.sh
```

### Running Individual Test Suites

**Integration Tests:**
```bash
phpunit tests/Integration/EndToEndIntegrationTest.php
```

**Security Tests:**
```bash
phpunit tests/Security/SecurityAuditTest.php
```

**Performance Tests:**
```bash
phpunit tests/Performance/PerformanceTest.php
```

### Running Specific Tests

```bash
# Run a specific test method
phpunit --filter testCompleteRegistrationAndLoginFlow tests/Integration/EndToEndIntegrationTest.php

# Run with verbose output
phpunit --verbose tests/Security/SecurityAuditTest.php

# Run with code coverage
phpunit --coverage-html coverage/ tests/
```

## Test Output

### Integration Tests
- Validates complete user workflows
- Reports success/failure for each flow
- Provides detailed error messages on failures

### Security Tests
- Lists all attack patterns tested
- Reports which security measures are effective
- Highlights any vulnerabilities found

### Performance Tests
- Displays timing information for each test
- Shows average times and throughput
- Compares against performance targets
- Example output:
  ```
  [Performance Test] Creating 150 portfolios for load testing...
    Created 50 portfolios...
    Created 100 portfolios...
    Created 150 portfolios...
    Setup completed in 12.34 seconds
  
  [Performance Test] Testing showcase page load...
    Showcase page loaded in 1.234 seconds
  ```

## Interpreting Results

### Success Criteria

**Integration Tests:**
- ✓ All user flows complete successfully
- ✓ Data persists correctly across operations
- ✓ Access control is enforced
- ✓ Sessions are managed properly

**Security Tests:**
- ✓ All SQL injection attempts are blocked
- ✓ All XSS attempts are sanitized
- ✓ CSRF protection is enforced
- ✓ Rate limiting prevents abuse
- ✓ Access control prevents unauthorized access

**Performance Tests:**
- ✓ Showcase loads in < 3 seconds with 100+ portfolios
- ✓ PDF generates in < 30 seconds for 50 items
- ✓ Concurrent sessions are handled efficiently
- ✓ Database queries use indexes effectively
- ✓ Image operations are fast

### Failure Investigation

If tests fail:

1. **Check Test Output**: Read the detailed error messages
2. **Review Logs**: Check application logs for errors
3. **Database State**: Verify database schema and data
4. **Configuration**: Ensure test environment is configured correctly
5. **Dependencies**: Verify all required services are running

## Continuous Integration

### CI Pipeline Integration

Add to your CI configuration (e.g., `.github/workflows/tests.yml`):

```yaml
name: Integration Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: portfolio_test
        ports:
          - 3306:3306
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
          extensions: pdo, pdo_mysql, gd
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Migrations
        run: php database/migrate.php --database=portfolio_test
        env:
          TEST_DB_HOST: 127.0.0.1
          TEST_DB_NAME: portfolio_test
          TEST_DB_USER: root
          TEST_DB_PASS: root
      
      - name: Run Integration Tests
        run: ./tests/run-integration-tests.sh
        env:
          TEST_DB_HOST: 127.0.0.1
          TEST_DB_NAME: portfolio_test
          TEST_DB_USER: root
          TEST_DB_PASS: root
```

## Maintenance

### Adding New Tests

When adding new features:

1. Add integration tests for complete user workflows
2. Add security tests for any new input fields
3. Add performance tests if the feature impacts load times
4. Update this documentation

### Test Data Management

- Tests use isolated test database
- Database is cleaned before and after each test
- Test files are stored in temporary directories
- All test data is automatically cleaned up

## Troubleshooting

### Common Issues

**Issue: Database connection failed**
- Solution: Verify TEST_DB_* environment variables are set
- Check database server is running
- Verify user has permissions on test database

**Issue: File upload tests fail**
- Solution: Ensure uploads directory is writable
- Check disk space availability
- Verify GD extension is installed for image processing

**Issue: Performance tests timeout**
- Solution: Increase PHP max_execution_time
- Check database performance and indexes
- Verify sufficient system resources

**Issue: Security tests report vulnerabilities**
- Solution: Review InputSanitizer implementation
- Check output escaping in views
- Verify prepared statements are used everywhere

## Best Practices

1. **Run tests before committing**: Catch issues early
2. **Run full suite before releases**: Ensure system integrity
3. **Monitor performance trends**: Track if performance degrades over time
4. **Update tests with features**: Keep tests in sync with code
5. **Review security tests regularly**: Stay current with attack patterns

## Support

For issues or questions about testing:
- Review test output and error messages
- Check application logs
- Consult the main README.md
- Review the design document for requirements

## Summary

These comprehensive test suites provide:
- **Confidence**: All features work together correctly
- **Security**: Protection against common attacks
- **Performance**: System meets speed requirements
- **Regression Prevention**: Catch issues before production
- **Documentation**: Tests serve as executable specifications

Run these tests regularly to maintain system quality and reliability.
