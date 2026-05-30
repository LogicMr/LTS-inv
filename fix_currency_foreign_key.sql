-- Check current currencies and fix foreign key issues
-- This script will help diagnose and fix the currency constraint problem

-- Check what currencies exist
SELECT 'Current currencies:' as info;
SELECT * FROM currencies;

-- Check what currency_id values exist in products
SELECT 'Products currency distribution:' as info;
SELECT currency_id, COUNT(*) as count FROM products GROUP BY currency_id ORDER BY currency_id;

-- If TSh exists, get its ID
SELECT 'TSh currency ID:' as info;
SELECT id, code, symbol FROM currencies WHERE code = 'TZS';

-- If no currencies exist, create TSh currency
INSERT INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    SET name = 'Tanzanian Shilling', 
        symbol = 'TSh', 
        exchange_rate = 1.000000, 
        is_active = TRUE, 
        is_default = TRUE,
        updated_at = CURRENT_TIMESTAMP();

-- Set TSh as default (in case it's not)
UPDATE currencies SET is_default = TRUE WHERE code = 'TZS';

-- Make all other currencies inactive
UPDATE currencies SET is_active = FALSE WHERE code != 'TZS';

-- Update all products to use TSh currency (ID 1)
UPDATE products SET currency_id = 1 WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Update all sales to use TSh currency
UPDATE sales SET currency_id = 1 WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Update all purchases to use TSh currency
UPDATE purchases SET currency_id = 1 WHERE currency_id IS NULL OR currency_id NOT IN (SELECT id FROM currencies WHERE is_active = TRUE);

-- Verify the fix
SELECT '=== FOREIGN KEY FIX COMPLETE ===' as status;
SELECT 'Products after fix:' as info;
SELECT currency_id, COUNT(*) as count FROM products GROUP BY currency_id;

SELECT 'Currencies after fix:' as info;
SELECT id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
