-- Fix foreign key constraints using existing TSh currency

-- Step 1: Check what currencies exist
SELECT '=== EXISTING CURRENCIES ===' as info;
SELECT id, code, name, symbol, is_active, is_default FROM currencies;

-- Step 2: Get the TSh currency ID
SET @tsh_id = (SELECT id FROM currencies WHERE code = 'TZS' LIMIT 1);

-- Step 3: Make TSh the default and active currency
UPDATE currencies SET is_default = TRUE, is_active = TRUE WHERE code = 'TZS';

-- Step 4: Make all other currencies inactive
UPDATE currencies SET is_active = FALSE WHERE code != 'TZS';

-- Step 5: Update products with NULL currency_id
UPDATE products SET currency_id = @tsh_id WHERE currency_id IS NULL;

-- Step 6: Update products with invalid currency_id
UPDATE products SET currency_id = @tsh_id WHERE currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Step 7: Update sales
UPDATE sales SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Step 8: Update purchases  
UPDATE purchases SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Step 9: Update sale_items if table exists
UPDATE sale_items SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Step 10: Update purchase_items if table exists
UPDATE purchase_items SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Step 11: Verify the fix
SELECT '=== FIX COMPLETE ===' as info;
SELECT 'Products:' as table_name, currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
SELECT 'Sales:' as table_name, currency_id, COUNT(*) as count FROM sales GROUP BY currency_id;
SELECT 'Purchases:' as table_name, currency_id, COUNT(*) as count FROM purchases GROUP BY currency_id;
SELECT 'Final currencies:' as table_name, id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
