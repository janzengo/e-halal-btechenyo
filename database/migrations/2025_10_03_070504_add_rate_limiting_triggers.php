<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create trigger for OTP requests rate limiting
        DB::unprepared('
            CREATE TRIGGER before_otp_insert 
            BEFORE INSERT ON otp_requests 
            FOR EACH ROW 
            BEGIN
                DECLARE attempt_count INT;
                SELECT COUNT(*) INTO attempt_count 
                FROM otp_requests 
                WHERE student_number = NEW.student_number 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
                
                IF attempt_count >= 5 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Rate limit exceeded. Please try again later.";
                END IF;
            END
        ');

        // Create trigger for OTP max attempts
        DB::unprepared('
            CREATE TRIGGER tr_delete_max_attempts 
            AFTER UPDATE ON otp_requests 
            FOR EACH ROW 
            BEGIN
                IF NEW.attempts >= 5 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "MAX_ATTEMPTS_REACHED";
                END IF;
            END
        ');

        // Create trigger for admin OTP requests rate limiting
        DB::unprepared('
            CREATE TRIGGER before_admin_otp_insert 
            BEFORE INSERT ON admin_otp_requests 
            FOR EACH ROW 
            BEGIN
                DECLARE attempt_count INT;
                SELECT COUNT(*) INTO attempt_count 
                FROM admin_otp_requests 
                WHERE email = NEW.email 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
                
                IF attempt_count >= 5 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Rate limit exceeded. Please try again later.";
                END IF;
            END
        ');

        // Create trigger for admin OTP max attempts
        DB::unprepared('
            CREATE TRIGGER tr_delete_admin_max_attempts 
            AFTER UPDATE ON admin_otp_requests 
            FOR EACH ROW 
            BEGIN
                IF NEW.attempts >= 5 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "MAX_ATTEMPTS_REACHED";
                END IF;
            END
        ');

        // Note: Password reset rate limiting is handled by Laravel's built-in system
        // using password_reset_tokens table with Laravel's throttling middleware

        // Create trigger for election control number auto-generation
        DB::unprepared('
            CREATE TRIGGER before_election_status_insert 
            BEFORE INSERT ON election_status 
            FOR EACH ROW 
            BEGIN
                IF NEW.control_number IS NULL OR NEW.control_number = "" THEN
                    SET NEW.control_number = CONCAT(
                        "E-",
                        YEAR(CURRENT_TIMESTAMP()),
                        "-",
                        LPAD(FLOOR(RAND() * 10000), 4, "0")
                    );
                END IF;
            END
        ');

        // Create trigger for vote reference auto-generation
        DB::unprepared('
            CREATE TRIGGER before_vote_insert 
            BEFORE INSERT ON votes 
            FOR EACH ROW 
            BEGIN
                IF NEW.vote_ref IS NULL OR NEW.vote_ref = "" THEN
                    SET NEW.vote_ref = CONCAT(
                        "VOTE-",
                        DATE_FORMAT(NOW(), "%y%m%d"),
                        "-",
                        LPAD(FLOOR(RAND() * 10000), 4, "0")
                    );
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_otp_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_delete_max_attempts');
        DB::unprepared('DROP TRIGGER IF EXISTS before_admin_otp_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_delete_admin_max_attempts');
        DB::unprepared('DROP TRIGGER IF EXISTS before_election_status_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS before_vote_insert');
    }
};
