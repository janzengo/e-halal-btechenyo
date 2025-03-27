<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';

class Course {
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

    /**
     * Get all courses with voter counts
     * @return array
     */
    public function getAllCoursesWithCounts() {
        $sql = "SELECT c.*, COUNT(v.id) as voter_count 
                FROM courses c 
                LEFT JOIN voters v ON c.id = v.course_id 
                GROUP BY c.id 
                ORDER BY c.description";
        $result = $this->db->query($sql);
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return $courses;
    }

    /**
     * Get course by ID
     * @param int $id
     * @return array|false
     */
    public function getCourse($id) {
        $sql = "SELECT * FROM courses WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Add new course
     * @param string $description
     * @return bool
     * @throws Exception
     */
    public function addCourse($description) {
        $sql = "INSERT INTO courses (description) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $description);
        if (!$stmt->execute()) {
            throw new Exception("Failed to add course: " . $stmt->error);
        }
        return true;
    }

    /**
     * Update course
     * @param int $id
     * @param string $description
     * @return bool
     * @throws Exception
     */
    public function updateCourse($id, $description) {
        $sql = "UPDATE courses SET description = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $description, $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update course: " . $stmt->error);
        }
        return true;
    }

    /**
     * Delete course
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteCourse($id) {
        // Check if course is being used by any voters
        $sql = "SELECT COUNT(*) as count FROM voters WHERE course_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            throw new Exception("Cannot delete course because it is being used by " . $row['count'] . " voter(s)");
        }

        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete course: " . $stmt->error);
        }
        return true;
    }

    /**
     * Get the number of voters in a course
     * @param int $courseId
     * @return int
     */
    public function getVoterCount($courseId) {
        $query = "SELECT COUNT(*) as count FROM voters WHERE course_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
} 