<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckMailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:check {--send-test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check mail configuration and optionally send test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Mail Configuration...');
        $this->newLine();

        // Check current mailer
        $mailer = config('mail.default');
        $this->info("Current Mailer: {$mailer}");

        if ($mailer === 'log') {
            $this->warn('⚠️  Mail is set to LOG mode (emails not actually sent)');
            $this->newLine();
            $this->displayConfiguration();
            return Command::SUCCESS;
        }

        // Display mail configuration
        $config = config('mail.mailers.' . $mailer);

        $this->table(
            ['Setting', 'Value'],
            [
                ['Mailer', $mailer],
                ['Host', $config['host'] ?? 'N/A'],
                ['Port', $config['port'] ?? 'N/A'],
                ['Username', $config['username'] ?? 'N/A'],
                ['Password', $config['password'] ? '***' : 'Not set'],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );

        $this->newLine();

        // Offer to send test email
        if ($this->option('send-test')) {
            $email = $this->ask('Enter test email address');

            if ($email) {
                $this->info('📧 Sending test email...');

                try {
                    Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($email) {
                        $message->to($email)
                            ->subject('Test Email - ' . config('app.name'));
                    });

                    $this->info('✅ Test email sent successfully to: ' . $email);
                    $this->info('💡 Check your inbox (and spam folder)');
                } catch (\Exception $e) {
                    $this->error('❌ Failed to send test email!');
                    $this->error('Error: ' . $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }

    private function displayConfiguration()
    {
        $this->info('📝 To enable real email sending, update your .env file:');
        $this->newLine();

        $this->line('For Gmail:');
        $this->line('─────────────────────────────────────────');
        $this->line('MAIL_MAILER=smtp');
        $this->line('MAIL_HOST=smtp.gmail.com');
        $this->line('MAIL_PORT=587');
        $this->line('MAIL_USERNAME=your-email@gmail.com');
        $this->line('MAIL_PASSWORD=your-app-password');
        $this->line('MAIL_ENCRYPTION=tls');
        $this->line('MAIL_FROM_ADDRESS=your-email@gmail.com');
        $this->newLine();

        $this->line('For Mailtrap (Testing):');
        $this->line('─────────────────────────────────────────');
        $this->line('MAIL_MAILER=smtp');
        $this->line('MAIL_HOST=sandbox.smtp.mailtrap.io');
        $this->line('MAIL_PORT=2525');
        $this->line('MAIL_USERNAME=your-mailtrap-username');
        $this->line('MAIL_PASSWORD=your-mailtrap-password');
        $this->newLine();

        $this->warn('After updating .env, run: php artisan config:cache');
        $this->newLine();

        $this->info('💡 Get Gmail App Password: https://support.google.com/accounts/answer/185833');
        $this->info('💡 Get Mailtrap account: https://mailtrap.io (Free)');
    }
}
