-- Drop all possible sales foreign key constraints

-- Step 1: Check if TSh currency exists
SELECT '=== TSH CURRENCY CHECK ===' as info;
SELECT id, code, name, symbol, is_active, is_default FROM currencies WHERE code = 'TZS';

-- Step 2: Drop all possible sales foreign key constraints
ALTER TABLE sales DROP FOREIGN KEY IF EXISTS sales_ibfk_1;
ALTER TABLE sales DROP FOREIGN KEY IF EXISTS sales_ibfk_2;
ALTER TABLE sales DROP FOREIGN KEY IF EXISTS sales_ibfk_3;

-- Step 3: Update all sales to use TSh currency
UPDATE sales SET currency_id = 1;

-- Step 4: Recreate the sales foreign key constraint
ALTER TABLE sales 
ADD CONSTRAINT sales_ibfk_3 
FOREIGN KEY (currency_id) REFERENCES currencies(id);

-- Step 5: Verify the fix
SELECT '=== SALES CONSTRAINT FIXED ===' as info;
SELECT currency_id, COUNT(*) as count FROM sales GROUP BY currency_id;

-- Step 6: Test adding a sale
INSERT INTO sales (customer_name, total_amount, discount_amount, final_amount, payment_method, currency_id, created_at, updated_at) 
VALUES ('Test Customer', 100.00, 0.00, 100.00, 'cash', 1, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());

-- Step 7: Verify test sale was added
SELECT '=== TEST SALE SUCCESS ===' as info;
SELECT id, customer_name, total_amount, final_amount, currency_id FROM sales WHERE customer_name = 'Test Customer';

-- Step 8: Clean up test sale
DELETE FROM sales WHERE customer_name = 'Test Customer';
