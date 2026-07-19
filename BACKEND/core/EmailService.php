<?php

declare(strict_types=1);

namespace App\Core;

/**
 * EBP Core - Email Service
 *
 * Handles email notifications for various events:
 * - Reservation confirmations
 * - Order confirmations
 * - Password reset
 * - Welcome emails
 * - Loyalty reward notifications
 *
 * @package EBP\App\Core
 * @version 1.0.0
 */
class EmailService
{
    private ?Database $db;
    private string $fromEmail;
    private string $fromName;
    private bool $smtpEnabled;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $smtpEncryption;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@ebp-restaurant.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'EBP Restaurant';
        $this->smtpEnabled = strtolower(getenv('MAIL_SMTP_ENABLED') ?: 'false') === 'true';
        $this->smtpHost = getenv('MAIL_SMTP_HOST') ?: 'localhost';
        $this->smtpPort = (int)(getenv('MAIL_SMTP_PORT') ?: 587);
        $this->smtpUsername = getenv('MAIL_SMTP_USERNAME') ?: '';
        $this->smtpPassword = getenv('MAIL_SMTP_PASSWORD') ?: '';
        $this->smtpEncryption = getenv('MAIL_SMTP_ENCRYPTION') ?: 'tls';
    }

    /**
     * Send reservation confirmation email
     */
    public function sendReservationConfirmation(array $data): bool
    {
        $subject = "Reservation Confirmation - {$data['restaurant_name']}";
        $body = $this->renderTemplate('reservation_confirmation', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(array $data): bool
    {
        $subject = "Order Confirmation #{$data['order_number']}";
        $body = $this->renderTemplate('order_confirmation', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(array $data): bool
    {
        $subject = "Password Reset Request";
        $body = $this->renderTemplate('password_reset', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send welcome email for new registration
     */
    public function sendWelcome(array $data): bool
    {
        $subject = "Welcome to {$data['restaurant_name']}!";
        $body = $this->renderTemplate('welcome', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send loyalty reward notification
     */
    public function sendLoyaltyReward(array $data): bool
    {
        $subject = "You've earned a reward!";
        $body = $this->renderTemplate('loyalty_reward', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send daily Z-report to managers
     */
    public function sendDailyReport(array $data): bool
    {
        $subject = "Daily Report - {$data['date']}";
        $body = $this->renderTemplate('daily_report', $data);
        return $this->send($data['email'], $subject, $body);
    }

    /**
     * Send email using PHP mail() or SMTP
     */
    public function send(string $to, string $subject, string $body): bool
    {
        // Log the email attempt
        $this->logEmail($to, $subject, $body, 'pending');

        if ($this->smtpEnabled && function_exists('stream_socket_client')) {
            $result = $this->sendSMTP($to, $subject, $body);
        } else {
            // Fallback to PHP mail()
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: PHP/' . phpversion()
            ];

            $result = mail($to, $subject, $body, implode("\r\n", $headers));
        }

        $this->logEmail($to, $subject, $body, $result ? 'sent' : 'failed');
        return $result;
    }

    /**
     * Send via SMTP
     */
    private function sendSMTP(string $to, string $subject, string $body): bool
    {
        $remote = ($this->smtpEncryption === 'ssl' ? 'ssl://' : '') . $this->smtpHost . ':' . $this->smtpPort;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }

        $response = fgets($socket, 515);
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            return false;
        }

        // EHLO
        fwrite($socket, "EHLO " . gethostname() . "\r\n");
        fgets($socket, 515);

        // STARTTLS if TLS
        if ($this->smtpEncryption === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            fgets($socket, 515);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($socket, "EHLO " . gethostname() . "\r\n");
            fgets($socket, 515);
        }

        // AUTH LOGIN
        if ($this->smtpUsername) {
            fwrite($socket, "AUTH LOGIN\r\n");
            fgets($socket, 515);
            fwrite($socket, base64_encode($this->smtpUsername) . "\r\n");
            fgets($socket, 515);
            fwrite($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (strpos($response, '235') !== 0) {
                fclose($socket);
                return false;
            }
        }

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$this->fromEmail}>\r\n");
        fgets($socket, 515);

        // RCPT TO
        fwrite($socket, "RCPT TO:<{$to}>\r\n");
        fgets($socket, 515);

        // DATA
        fwrite($socket, "DATA\r\n");
        fgets($socket, 515);

        $headers = [
            "From: {$this->fromName} <{$this->fromEmail}>",
            "To: <{$to}>",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            ""
        ];

        fwrite($socket, implode("\r\n", $headers) . "\r\n" . $body . "\r\n.\r\n");
        $response = fgets($socket, 515);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return strpos($response, '250') === 0;
    }

    /**
     * Render email template
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templates = [
            'reservation_confirmation' => $this->reservationConfirmationTemplate($data),
            'order_confirmation' => $this->orderConfirmationTemplate($data),
            'password_reset' => $this->passwordResetTemplate($data),
            'welcome' => $this->welcomeTemplate($data),
            'loyalty_reward' => $this->loyaltyRewardTemplate($data),
            'daily_report' => $this->dailyReportTemplate($data),
        ];

        return $templates[$template] ?? "No template found for: $template";
    }

    private function reservationConfirmationTemplate(array $data): string
    {
        $date = $data['reservation_date'] ?? '';
        $time = $data['reservation_time'] ?? '';
        $partySize = $data['party_size'] ?? '';
        $restaurantName = $data['restaurant_name'] ?? 'Our Restaurant';
        $customerName = $data['customer_name'] ?? 'Valued Customer';

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:#f8f9fa;padding:20px;border-radius:10px'>
            <h2 style='color:#2c3e50'>Reservation Confirmed!</h2>
            <p>Dear {$customerName},</p>
            <p>Your reservation at <strong>{$restaurantName}</strong> has been confirmed.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Date</strong></td><td style='padding:8px;border:1px solid #ddd'>{$date}</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Time</strong></td><td style='padding:8px;border:1px solid #ddd'>{$time}</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Party Size</strong></td><td style='padding:8px;border:1px solid #ddd'>{$partySize} guests</td></tr>
            </table>
            <p>We look forward to serving you!</p>
            <hr><p style='color:#999;font-size:12px'>This is an automated email. Please do not reply.</p>
        </div></body></html>";
    }

    private function orderConfirmationTemplate(array $data): string
    {
        $orderNumber = $data['order_number'] ?? '';
        $items = $data['items'] ?? [];
        $total = $data['total'] ?? 0;
        $orderType = $data['order_type'] ?? 'dine_in';
        $customerName = $data['customer_name'] ?? 'Valued Customer';

        $itemsHtml = '';
        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            $qty = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $itemsHtml .= "<tr><td style='padding:8px'>{$name} x{$qty}</td><td style='padding:8px;text-align:right'>Rp " . number_format($price * $qty, 0, ',', '.') . "</td></tr>";
        }

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:#f8f9fa;padding:20px;border-radius:10px'>
            <h2 style='color:#2c3e50'>Order Confirmation #{$orderNumber}</h2>
            <p>Dear {$customerName},</p>
            <p>Thank you for your order! Here are the details:</p>
            <p><strong>Order Type:</strong> {$orderType}</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr style='background:#2c3e50;color:white'><th style='padding:8px'>Item</th><th style='padding:8px;text-align:right'>Price</th></tr>
                {$itemsHtml}
                <tr style='font-weight:bold'><td style='padding:8px'>Total</td><td style='padding:8px;text-align:right'>Rp " . number_format($total, 0, ',', '.') . "</td></tr>
            </table>
            <p>We'll notify you when your order is ready!</p>
        </div></body></html>";
    }

    private function passwordResetTemplate(array $data): string
    {
        $resetLink = $data['reset_link'] ?? '';
        $userName = $data['username'] ?? 'User';

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:#f8f9fa;padding:20px;border-radius:10px'>
            <h2 style='color:#e74c3c'>Password Reset Request</h2>
            <p>Dear {$userName},</p>
            <p>You have requested to reset your password. Click the link below to proceed:</p>
            <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;background:#3498db;color:white;text-decoration:none;border-radius:5px'>Reset Password</a></p>
            <p style='color:#999'>This link will expire in 30 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
        </div></body></html>";
    }

    private function welcomeTemplate(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Valued Customer';
        $restaurantName = $data['restaurant_name'] ?? 'Our Restaurant';

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px;border-radius:10px;color:white'>
            <h1>Welcome to {$restaurantName}!</h1>
            <p>Dear {$customerName},</p>
            <p>Thank you for joining us! Your account has been created successfully.</p>
            <p>Start exploring our menu, place orders, and earn loyalty points!</p>
            <a href='{$data['app_url']}' style='display:inline-block;padding:12px 30px;background:white;color:#764ba2;text-decoration:none;border-radius:5px;font-weight:bold'>Get Started</a>
        </div></body></html>";
    }

    private function loyaltyRewardTemplate(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Valued Customer';
        $rewardName = $data['reward_name'] ?? 'a special reward';
        $points = $data['points'] ?? 0;

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:#f8f9fa;padding:20px;border-radius:10px'>
            <h2 style='color:#f39c12'>You've Earned a Reward!</h2>
            <p>Dear {$customerName},</p>
            <p>Congratulations! You've earned <strong>{$rewardName}</strong> with {$points} loyalty points!</p>
            <p>Visit us to redeem your reward.</p>
        </div></body></html>";
    }

    private function dailyReportTemplate(array $data): string
    {
        $date = $data['date'] ?? date('Y-m-d');
        $totalSales = $data['total_sales'] ?? 0;
        $totalTransactions = $data['total_transactions'] ?? 0;
        $netSales = $data['net_sales'] ?? 0;

        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto'>
        <div style='background:#f8f9fa;padding:20px;border-radius:10px'>
            <h2 style='color:#2c3e50'>Daily Report - {$date}</h2>
            <table style='width:100%;border-collapse:collapse;margin:20px 0'>
                <tr><td style='padding:8px;border:1px solid #ddd'>Total Transactions</td><td style='padding:8px;border:1px solid #ddd'>{$totalTransactions}</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'>Gross Sales</td><td style='padding:8px;border:1px solid #ddd'>Rp " . number_format($totalSales, 0, ',', '.') . "</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'>Net Sales</td><td style='padding:8px;border:1px solid #ddd'>Rp " . number_format($netSales, 0, ',', '.') . "</td></tr>
            </table>
        </div></body></html>";
    }

    /**
     * Log email to database
     */
    private function logEmail(string $to, string $subject, string $body, string $status): void
    {
        try {
            $pdo = $this->db->connect();
            $stmt = $pdo->prepare("
                INSERT INTO email_logs (recipient, subject, body_preview, status, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $preview = substr($body, 0, 500);
            $stmt->execute([$to, $subject, $preview, $status]);
        } catch (\Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
}
