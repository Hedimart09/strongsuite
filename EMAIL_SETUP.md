# Email Configuration Guide for Production

## Problem
Emails are not being sent in production because:
1. Queue worker was not running to process queued emails
2. Email driver needs to be configured with a real mail service

## ⚠️ Important: Mailtrap Confusion

**DON'T USE** regular Mailtrap (smtp.mailtrap.io) in production - it's a testing sandbox that catches emails and prevents them from reaching real users!

**DO USE** one of these for production:
- ✅ **Mailtrap Send** (live.smtp.mailtrap.io) - Production service from Mailtrap
- ✅ **Mailgun** - Recommended
- ✅ **SendGrid** - Popular alternative
- ✅ **Resend** - Modern option
- ✅ **Gmail SMTP** - Quick testing only

## Solution Implemented

### 1. Queue Worker Setup ✅
Added Supervisor to run both the web server and queue worker simultaneously:
- **File**: `supervisord.conf` - Configures both processes
- **Updated**: `Dockerfile` - Installs supervisor and uses it as the main process

The queue worker will now automatically process queued jobs including emails.

### 2. Email Service Configuration

You need to configure a real email service in your production environment variables. Here are the recommended options:

---

## Option 1: Gmail SMTP (Easiest for Testing)

**Environment Variables:**
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # Not your regular password!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="StrongSuite"
```

**Setup Steps:**
1. Go to Google Account settings
2. Enable 2-Factor Authentication
3. Generate an "App Password" (16-character code)
4. Use the app password as `MAIL_PASSWORD`

---

## Option 2: Mailgun (Recommended for Production)

**Environment Variables:**
```bash
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="StrongSuite"
```

**Setup Steps:**
1. Sign up at https://www.mailgun.com (Free: 5,000 emails/month)
2. Verify your domain or use Mailgun's sandbox domain
3. Get your API key from the dashboard
4. Add DNS records if using custom domain

---

## Option 3: SendGrid

**Environment Variables:**
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="StrongSuite"
```

**Setup Steps:**
1. Sign up at https://sendgrid.com (Free: 100 emails/day)
2. Create an API key
3. Verify sender identity
4. Use "apikey" as username and your API key as password

---

## Option 4: Mailtrap Send (For Production)

⚠️ **Note**: This is "Mailtrap Send" (production), NOT regular "Mailtrap" (testing sandbox)

**Environment Variables:**
```bash
MAIL_MAILER=smtp
MAIL_HOST=live.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="StrongSuite"
```

**Setup Steps:**
1. Sign up at https://mailtrap.io (Free: 1,000 emails/month)
2. Go to **Sending Domains** (not Email Testing)
3. Verify your domain or use their sandbox domain
4. Get SMTP credentials from the **Sending** section
5. Use the "live.smtp.mailtrap.io" host (NOT "smtp.mailtrap.io")

---

## Option 5: Resend (Modern Alternative)

**Environment Variables:**
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=your-resend-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="StrongSuite"
```

**Setup Steps:**
1. Sign up at https://resend.com (Free: 3,000 emails/month)
2. Verify your domain
3. Generate an API key
4. Use "resend" as username

---

## Railway/Production Deployment

### Set Environment Variables on Railway:

1. Go to your Railway project
2. Click on your service → **Variables** tab
3. Add the email configuration variables based on your chosen provider
4. Example for Gmail:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=yourapp@gmail.com
   MAIL_PASSWORD=your-16-char-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=yourapp@gmail.com
   MAIL_FROM_NAME=StrongSuite
   ```

5. **Redeploy** your application

---

## Testing Email in Production

### 1. Check Queue is Running
SSH into your container or check logs:
```bash
# Check supervisor status
supervisorctl status

# You should see both processes running:
# php                             RUNNING
# queue                           RUNNING
```

### 2. Test Email Sending
- Register a new member in the application
- Check the logs for any email errors:
  ```bash
  tail -f storage/logs/laravel.log
  ```

### 3. Monitor Queue Jobs
```bash
# Check if jobs are in the queue
php artisan queue:monitor

# Process a specific job manually (testing)
php artisan queue:work --once
```

---

## Troubleshooting

### Emails Still Not Sending?

1. **Check Queue Worker Logs:**
   ```bash
   tail -f /var/log/supervisor/queue-*.log
   ```

2. **Verify Environment Variables:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   php artisan tinker
   >>> config('mail.mailers.smtp')
   ```

3. **Test Mail Configuration:**
   ```bash
   php artisan tinker
   >>> Mail::raw('Test email', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```

4. **Check Queue Table:**
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count()
   # If > 0, jobs are queued but not processing
   ```

5. **Common Issues:**
   - Wrong credentials → Check API keys/passwords
   - Firewall blocking port 587/465 → Try different ports
   - FROM address not verified → Verify sender in email service
   - Gmail blocking → Enable "Less secure app access" or use App Password

---

## For Ghana-Based Deployment

If deploying in Ghana, consider:
1. **Mailgun** - Good global delivery
2. **SendGrid** - Reliable in Africa
3. **Resend** - Fast and modern

Avoid Gmail SMTP for high-volume production use.

---

## Queue Configuration (Already Set)

The application uses database-based queues:
```bash
QUEUE_CONNECTION=database
```

Jobs are stored in the `jobs` table and processed by the supervisor queue worker.

---

## Need Help?

- Check Laravel mail docs: https://laravel.com/docs/11.x/mail
- Supervisor logs: `/var/log/supervisor/`
- Laravel logs: `storage/logs/laravel.log`
