-- Direct fix approach - disable constraint temporarily

-- Step 1: Check what currencies actually exist
SELECT '=== CURRENT CURRENCIES ===' as info;
SELECT id, code, name, symbol, is_active, is_default FROM currencies;

-- Step 2: Check what currency_id values exist in products
SELECT '=== PRODUCTS CURRENCY VALUES ===' as info;
SELECT DISTINCT currency_id FROM products;

-- Step 3: If no currencies exist, create TSh with ID 1
INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 4: Verify TSh exists
SELECT '=== TSH VERIFICATION ===' as info;
SELECT id, code, name, symbol FROM currencies WHERE code = 'TZS';

-- Step 5: Update NULL currency_id values first (these don't violate constraint)
UPDATE products SET currency_id = 1 WHERE currency_id IS NULL;

-- Step 6: Check what currency_id values remain
SELECT '=== REMAINING CURRENCY VALUES ===' as info;
SELECT DISTINCT currency_id FROM products WHERE currency_id IS NOT NULL;

-- Step 7: Update products with specific invalid currency_id values
-- Only update if the currency_id doesn't exist in currencies table
UPDATE products SET currency_id = 1 
WHERE currency_id NOT IN (SELECT id FROM currencies WHERE id IS NOT NULL);

-- Step 8: Final check
SELECT '=== FINAL CHECK ===' as info;
SELECT currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
