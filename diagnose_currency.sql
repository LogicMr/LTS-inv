-- Diagnose the foreign key constraint issue

-- Step 1: Check if currencies table exists and what's in it
SELECT '=== CURRENCIES TABLE CHECK ===' as info;
SELECT COUNT(*) as total_currencies FROM currencies;
SELECT * FROM currencies;

-- Step 2: Check what currency_id values exist in products
SELECT '=== PRODUCTS CURRENCY CHECK ===' as info;
SELECT currency_id, COUNT(*) as count FROM products GROUP BY currency_id;

-- Step 3: Check if there are any NULL currency_id in products
SELECT '=== NULL CURRENCY CHECK ===' as info;
SELECT COUNT(*) as null_currency_count FROM products WHERE currency_id IS NULL;

-- Step 4: Check if TSh currency exists and get its ID
SELECT '=== TSH CURRENCY CHECK ===' as info;
SELECT id, code, name, symbol FROM currencies WHERE code = 'TZS';

-- Step 5: Try to find what the valid currency_id should be
SELECT '=== VALID CURRENCY IDs ===' as info;
SELECT id, code, name FROM currencies WHERE is_active = TRUE;

-- Step 6: Check if we have at least one valid currency to use
SELECT '=== FIRST VALID CURRENCY ===' as info;
SELECT id, code, name FROM currencies LIMIT 1;

-- Step 7: If no currencies exist, create a simple TSh currency
INSERT IGNORE INTO currencies (code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES ('TZS', 'Tanzanian Shilling', 'TSh', 1.0, TRUE, TRUE, NOW(), NOW());

-- Step 8: Verify TSh exists now
SELECT '=== FINAL TSH CHECK ===' as info;
SELECT id, code, name, symbol FROM currencies WHERE code = 'TZS';
