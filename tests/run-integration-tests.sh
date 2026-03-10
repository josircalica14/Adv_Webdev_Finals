#!/bin/bash

# Integration Test Runner for Multi-User Portfolio Platform
# This script runs all integration, security, and performance tests

echo "=========================================="
echo "Multi-User Portfolio Platform Test Suite"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PHPUnit is installed
if ! command -v phpunit &> /dev/null; then
    echo -e "${RED}Error: PHPUnit is not installed${NC}"
    echo "Please install PHPUnit: composer require --dev phpunit/phpunit"
    exit 1
fi

# Check if test database is configured
if [ -z "$TEST_DB_HOST" ]; then
    echo -e "${YELLOW}Warning: TEST_DB_HOST not set, using default 'localhost'${NC}"
    export TEST_DB_HOST="localhost"
fi

if [ -z "$TEST_DB_NAME" ]; then
    echo -e "${YELLOW}Warning: TEST_DB_NAME not set, using default 'portfolio_test'${NC}"
    export TEST_DB_NAME="portfolio_test"
fi

echo "Test Configuration:"
echo "  Database Host: $TEST_DB_HOST"
echo "  Database Name: $TEST_DB_NAME"
echo ""

# Function to run a test suite
run_test_suite() {
    local suite_name=$1
    local test_path=$2
    
    echo -e "${YELLOW}Running $suite_name...${NC}"
    echo "----------------------------------------"
    
    if phpunit --colors=always "$test_path"; then
        echo -e "${GREEN}✓ $suite_name PASSED${NC}"
        echo ""
        return 0
    else
        echo -e "${RED}✗ $suite_name FAILED${NC}"
        echo ""
        return 1
    fi
}

# Track test results
total_suites=0
passed_suites=0
failed_suites=0

# Run End-to-End Integration Tests
total_suites=$((total_suites + 1))
if run_test_suite "End-to-End Integration Tests" "tests/Integration/EndToEndIntegrationTest.php"; then
    passed_suites=$((passed_suites + 1))
else
    failed_suites=$((failed_suites + 1))
fi

# Run Security Audit Tests
total_suites=$((total_suites + 1))
if run_test_suite "Security Audit Tests" "tests/Security/SecurityAuditTest.php"; then
    passed_suites=$((passed_suites + 1))
else
    failed_suites=$((failed_suites + 1))
fi

# Run Performance Tests
total_suites=$((total_suites + 1))
if run_test_suite "Performance Tests" "tests/Performance/PerformanceTest.php"; then
    passed_suites=$((passed_suites + 1))
else
    failed_suites=$((failed_suites + 1))
fi

# Print summary
echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo "Total Test Suites: $total_suites"
echo -e "${GREEN}Passed: $passed_suites${NC}"
if [ $failed_suites -gt 0 ]; then
    echo -e "${RED}Failed: $failed_suites${NC}"
else
    echo "Failed: $failed_suites"
fi
echo ""

# Exit with appropriate code
if [ $failed_suites -gt 0 ]; then
    echo -e "${RED}Some tests failed. Please review the output above.${NC}"
    exit 1
else
    echo -e "${GREEN}All tests passed successfully!${NC}"
    exit 0
fi
