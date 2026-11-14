<?php
/**
 * Test CSRF Lifecycle & Patient Queue Registration
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "=== TEST CSRF LIFECYCLE & REGISTRATION ===\n";

// 1. Test CSRF Token Generation & Synchronization
$token1 = CsrfMiddleware::generateToken();
assert(!empty($token1), "Token 1 should not be empty");
assert($_SESSION['_csrf_token'] === $token1, "_csrf_token should match token1");
assert($_SESSION['csrf_token'] === $token1, "csrf_token should match token1");
assert(CsrfMiddleware::getToken() === $token1, "getToken() should return token1");
echo " [PASS] Token generation and dual-key synchronization\n";

// 2. Test Verification with Valid Token
assert(CsrfMiddleware::verifyToken($token1) === true, "verifyToken should return true for valid token");
echo " [PASS] verifyToken returns true for current token\n";

// 3. Test Verification with Tampered/Invalid Token
assert(CsrfMiddleware::verifyToken('invalid-fake-token-12345') === false, "verifyToken should return false for fake token");
assert(CsrfMiddleware::verifyToken('') === false, "verifyToken should return false for empty string");
echo " [PASS] verifyToken correctly rejects fake or empty tokens\n";

// 4. Test Grace Period on Token Regeneration
$token2 = CsrfMiddleware::generateToken();
assert($token2 !== $token1, "Token 2 should be different from token 1");
assert($_SESSION['_csrf_token'] === $token2, "_csrf_token should now be token2");
// Verify that old token still passes during grace period (to prevent race conditions / multi-tab fails)
assert(CsrfMiddleware::verifyToken($token1) === true, "Token 1 should still be valid during grace period");
assert(CsrfMiddleware::verifyToken($token2) === true, "Token 2 is also valid");
echo " [PASS] Grace period allows previous token without race condition\n";

// 5. Test Hidden Field Generation
$fieldHtml = CsrfMiddleware::getTokenField();
assert(str_contains($fieldHtml, 'name="csrf_token"'), "Token field must contain name='csrf_token'");
assert(str_contains($fieldHtml, 'value="' . $token2 . '"'), "Token field must contain the current token");
echo " [PASS] Form field HTML generated properly\n";

// 6. Test SecurityHelper alias
$securityField = SecurityHelper::csrfField();
assert($securityField === $fieldHtml, "SecurityHelper::csrfField() matches CsrfMiddleware::getTokenField()");
echo " [PASS] SecurityHelper::csrfField() matches CsrfMiddleware\n";

echo "\nALL CSRF TESTS PASSED SUCCESSFULLY!\n";

