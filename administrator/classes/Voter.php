<?php
require_once __DIR__ . '/../../classes/Database.php';

class Voter {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getAllVoters() {
        $query = "SELECT v.*, c.description as course_name 
                 FROM voters v 
                 LEFT JOIN courses c ON v.course_id = c.id 
                 ORDER BY v.student_number ASC";
        $result = $this->db->query($query);
        $voters = [];
        while ($row = $result->fetch_assoc()) {
            $voters[] = $row;
        }
        return $voters;
    }

    public function getVoter($id) {
        $id = (int)$id;
        $query = "SELECT v.*, c.description as course_name 
                 FROM voters v 
                 LEFT JOIN courses c ON v.course_id = c.id 
                 WHERE v.id = $id";
        $result = $this->db->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getAllCourses() {
        $query = "SELECT id, description FROM courses ORDER BY description ASC";
        $result = $this->db->query($query);
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return $courses;
    }

    public function getVoterCount() {
        $query = "SELECT COUNT(*) as count FROM voters";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getVotersWhoVoted() {
        try {
            $query = "SELECT COUNT(*) as count FROM voters WHERE has_voted = 1";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            return $row['count'];
        } catch (Exception $e) {
            // If there's an error (like missing column), return 0
            return 0;
        }
    }

    public function addVoter($student_number, $course_id) {
        $student_number = $this->db->escape($student_number);
        $course_id = (int)$course_id;
        
        // Check if voter with this student number already exists
        $check_query = "SELECT id FROM voters WHERE student_number = '$student_number'";
        $check_result = $this->db->query($check_query);
        
        if ($check_result->num_rows > 0) {
            return false; // Voter already exists
        }
        
        $query = "INSERT INTO voters (student_number, course_id) VALUES ('$student_number', $course_id)";
        return $this->db->query($query);
    }

    public function updateVoter($id, $student_number, $course_id) {
        $id = (int)$id;
        $student_number = $this->db->escape($student_number);
        $course_id = (int)$course_id;
        
        // Check if another voter with this student number already exists
        $check_query = "SELECT id FROM voters WHERE student_number = '$student_number' AND id != $id";
        $check_result = $this->db->query($check_query);
        
        if ($check_result->num_rows > 0) {
            return false; // Another voter with this student number exists
        }
        
        $query = "UPDATE voters SET student_number = '$student_number', course_id = $course_id WHERE id = $id";
        return $this->db->query($query);
    }

    public function deleteVoter($id) {
        $id = (int)$id;
        $query = "DELETE FROM voters WHERE id = $id AND has_voted = 0";
        return $this->db->query($query);
    }

    /**
     * Bulk delete voters who haven't voted yet
     * @param array $ids Array of voter IDs to delete
     * @return int Number of voters deleted
     */
    public function bulkDeleteVoters($ids) {
        try {
            // Convert array of IDs to comma-separated string of integers
            $ids = array_map('intval', $ids);
            $idList = implode(',', $ids);
            
            // First check how many voters are eligible for deletion
            $checkQuery = "SELECT COUNT(*) as count FROM voters WHERE id IN ($idList) AND has_voted = 0";
            $checkResult = $this->db->query($checkQuery);
            $row = $checkResult->fetch_assoc();
            $eligibleCount = $row['count'];

            if ($eligibleCount === 0) {
                return 0;
            }
            
            // Delete voters who haven't voted yet
            $query = "DELETE FROM voters WHERE id IN ($idList) AND has_voted = 0";
            $result = $this->db->query($query);
            
            if ($result) {
                return $eligibleCount;
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error in bulk delete voters: " . $e->getMessage());
            throw new Exception("Database error occurred while deleting voters");
        }
    }

    public function markVoterAsVoted($id) {
        $id = (int)$id;
        $query = "UPDATE voters SET has_voted = 1 WHERE id = $id";
        return $this->db->query($query);
    }
}