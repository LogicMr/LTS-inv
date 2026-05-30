-- Final solution - drop constraint, fix data, recreate constraint

-- Step 1: Check current constraint name
SELECT '=== CHECKING CONSTRAINTS ===' as info;
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE 
    TABLE_SCHEMA = 'inventory_system' 
    AND TABLE_NAME = 'products' 
    AND COLUMN_NAME = 'currency_id'
    AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Step 2: Drop the foreign key constraint
ALTER TABLE products DROP FOREIGN KEY products_ibfk_3;

-- Step 3: Ensure TSh currency exists
INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 4: Update all products to use TSh currency
UPDATE products SET currency_id = 1;

-- Step 5: Update all related tables
UPDATE sales SET currency_id = 1;
UPDATE purchases SET currency_id = 1;
UPDATE sale_items SET currency_id = 1;
UPDATE purchase_items SET currency_id = 1;

-- Step 6: Recreate the foreign key constraint
ALTER TABLE products 
ADD CONSTRAINT products_ibfk_3 
FOREIGN KEY (currency_id) REFERENCES currencies(id);

-- Step 7: Verify everything is fixed
SELECT '=== CONSTRAINT RECREATION COMPLETE ===' as info;
SELECT 'Products:' as table_name, currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
SELECT 'Sales:' as table_name, currency_id, COUNT(*) as count FROM sales GROUP BY currency_id;
SELECT 'Currencies:' as table_name, id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
