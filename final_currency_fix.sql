-- Final fix for currency constraint issues

-- Step 1: Check what currencies actually exist
SELECT '=== CURRENT CURRENCIES ===' as info;
SELECT id, code, name, symbol, is_active, is_default FROM currencies;

-- Step 2: Find the TSh currency ID
SET @tsh_id = (SELECT id FROM currencies WHERE code = 'TZS' LIMIT 1);

-- Step 3: Make sure TSh is active and default
UPDATE currencies SET is_active = TRUE, is_default = TRUE WHERE code = 'TZS';

-- Step 4: Make all other currencies inactive
UPDATE currencies SET is_active = FALSE WHERE code != 'TZS';

-- Step 5: Update products to use TSh currency
UPDATE products SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id != @tsh_id;

-- Step 6: Update sales
UPDATE sales SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id != @tsh_id;

-- Step 7: Update purchases  
UPDATE purchases SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id != @tsh_id;

-- Step 8: Update sale_items if table exists
UPDATE sale_items SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id != @tsh_id;

-- Step 9: Update purchase_items if table exists
UPDATE purchase_items SET currency_id = @tsh_id WHERE currency_id IS NULL OR currency_id != @tsh_id;

-- Step 10: Final verification
SELECT '=== FINAL VERIFICATION ===' as info;
SELECT 'TSh currency ID:' as info, @tsh_id as tsh_id;
SELECT 'Products updated:' as table_name, currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
SELECT 'Sales updated:' as table_name, currency_id, COUNT(*) as count FROM sales GROUP BY currency_id;
SELECT 'Final currencies:' as table_name, id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
