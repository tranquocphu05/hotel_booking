<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? config('mail.from.address');
        $debug = $this->option('debug');

        $this->info('================================================================================');
        $this->info('                           EMAIL CONFIGURATION TEST');
        $this->info('================================================================================');
        $this->newLine();

        // Display current configuration
        $this->info('Current Configuration:');
        $this->line('  Mailer: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  Username: ' . config('mail.mailers.smtp.username'));
        $this->line('  Encryption: ' . config('mail.mailers.smtp.transport'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        $this->info("Sending test email to: {$email}");
        $this->newLine();

        try {
            $startTime = microtime(true);

            Mail::raw(
                "🎉 Congratulations!\n\n" .
                "This is a test email from Hotel Booking System.\n\n" .
                "If you received this email, your mail configuration is working correctly.\n\n" .
                "Configuration Details:\n" .
                "- Mailer: " . config('mail.default') . "\n" .
                "- Host: " . config('mail.mailers.smtp.host') . "\n" .
                "- Port: " . config('mail.mailers.smtp.port') . "\n" .
                "- Sent at: " . now()->format('d/m/Y H:i:s') . "\n\n" .
                "Thank you for using Hotel Booking System!\n\n" .
                "---\n" .
                "Hotel Booking Team",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('✅ Test Email - Hotel Booking System - ' . now()->format('d/m/Y H:i:s'));
                }
            );

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->info("   Duration: {$duration}ms");
            $this->newLine();
            $this->info('📬 Check your inbox at: ' . $email);
            $this->info('💡 If you don\'t see it, check your spam/junk folder');
            $this->newLine();

            if (config('mail.default') === 'log') {
                $this->warn('⚠️  You are using "log" mailer.');
                $this->info('   Check storage/logs/laravel.log to see the email content');
            }

            if (config('mail.default') === 'smtp' && str_contains(config('mail.mailers.smtp.host'), 'mailtrap')) {
                $this->info('📧 You are using Mailtrap.');
                $this->info('   Login to https://mailtrap.io to see the email');
            }

            $this->newLine();
            $this->info('================================================================================');
            $this->info('                              TEST COMPLETED');
            $this->info('================================================================================');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send email!');
            $this->newLine();
            $this->error('Error Message:');
            $this->line('  ' . $e->getMessage());
            $this->newLine();

            if ($debug) {
                $this->error('Stack Trace:');
                $this->line($e->getTraceAsString());
                $this->newLine();
            }

            // Common solutions
            $this->warn('Common Solutions:');
            $this->line('  1. Check your .env file has correct MAIL_* settings');
            $this->line('  2. Run: php artisan config:clear');
            $this->line('  3. For Gmail: Make sure you use App Password, not regular password');
            $this->line('  4. Check MAIL_HOST and MAIL_PORT are correct');
            $this->line('  5. Try different MAIL_ENCRYPTION: tls or ssl');
            $this->line('  6. Check firewall/antivirus not blocking SMTP ports');
            $this->newLine();

            $this->info('For more help, see: MAIL_SETUP_GUIDE.md');
            $this->newLine();

            $this->info('================================================================================');
            $this->info('                              TEST FAILED');
            $this->info('================================================================================');

            return Command::FAILURE;
        }
    }
}




