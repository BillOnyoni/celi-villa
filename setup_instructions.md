# M-Pesa Integration Setup Instructions

## 1. Get M-Pesa Credentials

### For Sandbox (Testing):
1. Visit [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Create an account and login
3. Create a new app and select "Lipa Na M-Pesa Online"
4. Get your Consumer Key and Consumer Secret
5. Use sandbox shortcode: `174379`
6. Use sandbox passkey: `bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919`

### For Production (Live):
1. Contact Safaricom to get your Paybill number
2. Apply for Lipa Na M-Pesa Online API access
3. Get your production Consumer Key, Consumer Secret, and Passkey
4. Use your actual Paybill number as the shortcode

## 2. Configure Your Application

1. Copy `config/env.example.php` to `config/env.php`
2. Fill in your actual M-Pesa credentials:
   ```php
   $_ENV['MPESA_ENVIRONMENT'] = 'sandbox'; // or 'production'
   $_ENV['MPESA_CONSUMER_KEY'] = 'your_actual_consumer_key';
   $_ENV['MPESA_CONSUMER_SECRET'] = 'your_actual_consumer_secret';
   $_ENV['MPESA_SHORTCODE'] = 'your_paybill_number';
   $_ENV['MPESA_PASSKEY'] = 'your_actual_passkey';
   $_ENV['SITE_URL'] = 'https://yourdomain.com';
   ```

## 3. Database Setup

Run the SQL commands in `sql/update_payments_table.sql` to update your database schema.

## 4. Server Configuration

1. Ensure your server has HTTPS enabled (required for production)
2. Make sure the `logs/` directory is writable by your web server
3. Ensure `callback.php` is publicly accessible at `https://yourdomain.com/callback.php`

## 5. Callback URL Configuration

In your Safaricom Developer Portal:
1. Set your callback URL to: `https://yourdomain.com/callback.php`
2. Make sure this URL is publicly accessible and returns a 200 response

## 6. Testing

### Sandbox Testing:
- Use test phone number: `254708374149`
- Use any amount between 1-1000 KES
- The sandbox will simulate the payment flow

### Production Testing:
- Use real Kenyan phone numbers (254XXXXXXXXX format)
- Test with small amounts first
- Monitor the logs in the admin panel

## 7. Security Considerations

1. **HTTPS Required**: M-Pesa requires HTTPS for production
2. **Validate Callbacks**: Always verify callback authenticity
3. **Log Everything**: Keep detailed logs for debugging and auditing
4. **Error Handling**: Implement proper error handling and user feedback
5. **Rate Limiting**: Consider implementing rate limiting for payment requests

## 8. Monitoring

- Check M-Pesa logs in the admin panel at `/admin/mpesa_logs.php`
- Monitor payment status in `/admin/payments.php`
- Set up alerts for failed payments

## 9. Common Issues

1. **Invalid Phone Number**: Ensure phone numbers are in 254XXXXXXXXX format
2. **Callback Not Received**: Check if your callback URL is publicly accessible
3. **SSL Certificate**: Ensure your SSL certificate is valid
4. **Firewall**: Make sure Safaricom can reach your callback URL

## 10. Support

For M-Pesa API issues, contact Safaricom support or check their documentation at:
https://developer.safaricom.co.ke/docs