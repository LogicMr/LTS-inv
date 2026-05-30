-- Disable foreign key constraint and fix currency issue

-- Step 1: Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Create TSh currency if it doesn't exist
INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 3: Update all products to use TSh currency
UPDATE products SET currency_id = 1;

-- Step 4: Update all sales to use TSh currency
UPDATE sales SET currency_id = 1;

-- Step 5: Update all purchases to use TSh currency
UPDATE purchases SET currency_id = 1;

-- Step 6: Update sale_items if table exists
UPDATE sale_items SET currency_id = 1;

-- Step 7: Update purchase_items if table exists
UPDATE purchase_items SET currency_id = 1;

-- Step 8: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Step 9: Verify the fix
SELECT '=== CONSTRAINT FIX COMPLETE ===' as info;
SELECT 'Products:' as table_name, currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
SELECT 'Sales:' as table_name, currency_id, COUNT(*) as count FROM sales GROUP BY currency_id;
SELECT 'Purchases:' as table_name, currency_id, COUNT(*) as count FROM purchases GROUP BY currency_id;
SELECT 'Currencies:' as table_name, id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
