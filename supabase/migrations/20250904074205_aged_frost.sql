-- Update payments table to support enhanced M-Pesa tracking
-- Run this SQL to update your existing payments table

ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS merchant_request_id VARCHAR(100),
ADD COLUMN IF NOT EXISTS mpesa_receipt VARCHAR(100),
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2),
ADD COLUMN IF NOT EXISTS transaction_date VARCHAR(20),
ADD COLUMN IF NOT EXISTS phone_number VARCHAR(15),
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'completed', 'failed', 'cancelled', 'timeout') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS result_desc TEXT,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS idx_payments_mpesa_code ON payments(mpesa_code);
CREATE INDEX IF NOT EXISTS idx_payments_merchant_request ON payments(merchant_request_id);
CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status);

-- Update orders table to include updated_at if not exists
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;