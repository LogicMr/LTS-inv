-- Final fix with correct suppliers table structure

-- Step 1: Create TSh currency if it doesn't exist
INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 2: Create a default supplier with correct columns
INSERT IGNORE INTO suppliers (id, name, contact_person, phone, email, address, created_at) 
VALUES (1, 'Default Supplier', 'Default Contact', '0000000000', 'default@supplier.com', 'Default Address', CURRENT_TIMESTAMP());

-- Step 3: Verify both exist
SELECT '=== VERIFICATION ===' as info;
SELECT 'TSh Currency:' as item, id, code, name FROM currencies WHERE code = 'TZS'
UNION ALL
SELECT 'Default Supplier:' as item, id, name, contact_person FROM suppliers WHERE id = 1;

-- Step 4: Update products with NULL supplier_id to use default supplier
UPDATE products SET supplier_id = 1 WHERE supplier_id IS NULL;

-- Step 5: Update products with NULL currency_id to use TSh currency
UPDATE products SET currency_id = 1 WHERE currency_id IS NULL;

-- Step 6: Try to add a test product with both supplier_id = 1 and currency_id = 1
INSERT INTO products (name, category_id, barcode, cost_price, selling_price, quantity_in_stock, reorder_level, supplier_id, expiry_date, batch_number, description, is_active, currency_id) 
VALUES ('Test Product', 1, 'TEST123', 100.00, 150.00, 50, 10, 1, NULL, NULL, 'Test product for currency and supplier fix', 1, 1);

-- Step 7: Verify test product was added
SELECT '=== TEST PRODUCT ADDED ===' as info;
SELECT id, name, cost_price, selling_price, supplier_id, currency_id FROM products WHERE name = 'Test Product';

-- Step 8: Final verification of all products
SELECT '=== ALL PRODUCTS STATUS ===' as info;
SELECT supplier_id, currency_id, COUNT(*) as count FROM products GROUP BY supplier_id, currency_id;
