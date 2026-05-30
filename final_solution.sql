-- Final solution - no schema checks needed

-- Step 1: Create TSh currency if it doesn't exist
INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 2: Try to add currency_id column if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS currency_id INT DEFAULT 1;

-- Step 3: Try to add foreign key constraint if it doesn't exist
ALTER TABLE products 
ADD CONSTRAINT IF NOT EXISTS products_ibfk_3 
FOREIGN KEY (currency_id) REFERENCES currencies(id);;

-- Step 4: Verify everything is working
SELECT '=== FINAL VERIFICATION ===' as info;
SELECT 'Products:' as table_name, currency_id, COUNT(*) as count FROM products GROUP BY currency_id;
SELECT 'Currencies:' as table_name, id, code, name, symbol, is_active, is_default FROM currencies ORDER BY is_default DESC;
