<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old trigger first
        DB::unprepared('DROP TRIGGER IF EXISTS before_election_status_insert');

        // Rename the table from election_status to elections
        Schema::rename('election_status', 'elections');

        // Recreate the trigger with the new table name
        DB::unprepared('
            CREATE TRIGGER before_elections_insert 
            BEFORE INSERT ON elections 
            FOR EACH ROW 
            BEGIN
                IF NEW.control_number IS NULL OR NEW.control_number = "" THEN
                    SET NEW.control_number = CONCAT(
                        "ELEC-",
                        DATE_FORMAT(NOW(), "%Y%m%d"),
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
        // Drop the new trigger
        DB::unprepared('DROP TRIGGER IF EXISTS before_elections_insert');

        // Rename back to election_status
        Schema::rename('elections', 'election_status');

        // Recreate the old trigger
        DB::unprepared('
            CREATE TRIGGER before_election_status_insert 
            BEFORE INSERT ON election_status 
            FOR EACH ROW 
            BEGIN
                IF NEW.control_number IS NULL OR NEW.control_number = "" THEN
                    SET NEW.control_number = CONCAT(
                        "ELEC-",
                        DATE_FORMAT(NOW(), "%Y%m%d"),
                        "-",
                        LPAD(FLOOR(RAND() * 10000), 4, "0")
                    );
                END IF;
            END
        ');
    }
};
