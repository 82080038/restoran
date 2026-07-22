<?php

use PHPUnit\Framework\TestCase;
use App\Core\Database;

class ConsumerOtpTest extends TestCase
{
    private $pdo;
    private $testPhone;

    protected function setUp(): void
    {
        $this->pdo = Database::getInstance()->connect();
        $this->testPhone = '62812' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    protected function tearDown(): void
    {
        // Clean up test OTPs
        $stmt = $this->pdo->prepare("DELETE FROM otp_verifications WHERE phone = ?");
        $stmt->execute([$this->testPhone]);
    }

    // ==================== OTP GENERATION ====================

    public function testOtpTableExists()
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'otp_verifications'");
        $result = $stmt->fetch();
        $this->assertNotFalse($result);
    }

    public function testOtpGenerationCreatesRecord()
    {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, expires_at)
            VALUES (?, ?, 'LOGIN', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        $stmt->execute([$this->testPhone, $otp]);
        $id = $this->pdo->lastInsertId();

        $this->assertGreaterThan(0, (int)$id);

        // Verify record
        $stmt = $this->pdo->prepare("SELECT * FROM otp_verifications WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($this->testPhone, $row['phone']);
        $this->assertEquals($otp, $row['otp_code']);
        $this->assertEquals('pending', $row['status']);
        $this->assertEquals('LOGIN', $row['purpose']);
    }

    public function testOtpExpiry()
    {
        // Insert an already-expired OTP
        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, expires_at)
            VALUES (?, '123456', 'LOGIN', 'pending', DATE_SUB(NOW(), INTERVAL 10 MINUTE))
        ");
        $stmt->execute([$this->testPhone]);
        $id = $this->pdo->lastInsertId();

        // Check if expired (use MySQL comparison to avoid timezone mismatch)
        $stmt = $this->pdo->prepare("SELECT (expires_at < NOW()) as is_expired FROM otp_verifications WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, (int)$row['is_expired']);
    }

    public function testOtpVerificationSuccess()
    {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, expires_at)
            VALUES (?, ?, 'LOGIN', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        $stmt->execute([$this->testPhone, $otp]);
        $id = $this->pdo->lastInsertId();

        // Simulate verification
        $stmt = $this->pdo->prepare("UPDATE otp_verifications SET status = 'verified', verified_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        // Verify
        $stmt = $this->pdo->prepare("SELECT status, verified_at FROM otp_verifications WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('verified', $row['status']);
        $this->assertNotNull($row['verified_at']);
    }

    public function testOtpMaxAttemptsExceeded()
    {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, attempts, max_attempts, expires_at)
            VALUES (?, ?, 'LOGIN', 'pending', 3, 3, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        $stmt->execute([$this->testPhone, $otp]);
        $id = $this->pdo->lastInsertId();

        // Check attempts exceeded
        $stmt = $this->pdo->prepare("SELECT attempts, max_attempts FROM otp_verifications WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertGreaterThanOrEqual((int)$row['max_attempts'], (int)$row['attempts']);
    }

    public function testOtpPreviousPendingExpiredOnNewRequest()
    {
        // Insert first OTP
        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, expires_at)
            VALUES (?, '111111', 'LOGIN', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        $stmt->execute([$this->testPhone]);
        $firstId = $this->pdo->lastInsertId();

        // Expire previous pending OTPs
        $stmt = $this->pdo->prepare("UPDATE otp_verifications SET status = 'expired' WHERE phone = ? AND status = 'pending'");
        $stmt->execute([$this->testPhone]);

        // Insert new OTP
        $stmt = $this->pdo->prepare("
            INSERT INTO otp_verifications (phone, otp_code, purpose, status, expires_at)
            VALUES (?, '222222', 'LOGIN', 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))
        ");
        $stmt->execute([$this->testPhone]);
        $secondId = $this->pdo->lastInsertId();

        // Verify first is expired, second is pending
        $stmt = $this->pdo->prepare("SELECT status FROM otp_verifications WHERE id = ?");
        $stmt->execute([$firstId]);
        $this->assertEquals('expired', $stmt->fetch(PDO::FETCH_ASSOC)['status']);

        $stmt = $this->pdo->prepare("SELECT status FROM otp_verifications WHERE id = ?");
        $stmt->execute([$secondId]);
        $this->assertEquals('pending', $stmt->fetch(PDO::FETCH_ASSOC)['status']);
    }
}
