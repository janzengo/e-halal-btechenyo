<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/ImageProcessor.php';

class Candidate {
    private $db;
    private static $instance = null;
    private const UPLOAD_PATH = 'assets/images/candidates/';
    private const DEFAULT_PHOTO = 'assets/images/profile.jpg';

    private function __construct() {
        $this->db = Database::getInstance();
        
        // Create candidates directory if it doesn't exist
        $fullPath = __DIR__ . '/../../' . self::UPLOAD_PATH;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function generatePhotoFilename($lastname, $firstname) {
        // Clean and normalize the names
        $lastname = preg_replace('/[^a-z0-9]/i', '_', strtolower($lastname));
        $firstname = preg_replace('/[^a-z0-9]/i', '_', strtolower($firstname));
        return 'candidate_' . $lastname . '_' . $firstname . '.jpg';
    }

    private function processPhoto($file, $lastname, $firstname) {
        if (empty($file['tmp_name'])) {
            return ['error' => false, 'photo' => self::DEFAULT_PHOTO];
        }

        // Validate image
        $validation = ImageProcessor::validateImage($file);
        if ($validation['error']) {
            return ['error' => true, 'message' => $validation['message']];
        }

        // Generate filename and path
        $filename = $this->generatePhotoFilename($lastname, $firstname);
        $targetPath = __DIR__ . '/../../' . self::UPLOAD_PATH . $filename;

        // Process and save image
        if (!ImageProcessor::processImage($file, $targetPath)) {
            return ['error' => true, 'message' => 'Failed to process image'];
        }

        return ['error' => false, 'photo' => self::UPLOAD_PATH . $filename];
    }

    public function getAllCandidates() {
        $query = "SELECT c.*, p.description as position, p.priority, pl.name as partylist_name 
                 FROM candidates c 
                 LEFT JOIN positions p ON p.id = c.position_id 
                 LEFT JOIN partylists pl ON pl.id = c.partylist_id 
                 ORDER BY p.priority ASC, c.lastname ASC, c.firstname ASC";
        $result = $this->db->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['photo'])) {
                $row['photo'] = self::DEFAULT_PHOTO;
            }
            $candidates[] = $row;
        }
        return $candidates;
    }

    public function getCandidate($id) {
        $id = (int)$id;
        $query = "SELECT c.*, p.description as position_name 
                 FROM candidates c 
                 LEFT JOIN positions p ON p.id = c.position_id 
                 WHERE c.id = $id";
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            $candidate = $result->fetch_assoc();
            if (empty($candidate['photo'])) {
                $candidate['photo'] = self::DEFAULT_PHOTO;
            }
            return $candidate;
        }
        return false;
    }

    public function getCandidatesByPosition($position_id) {
        $position_id = (int)$position_id;
        $query = "SELECT c.* FROM candidates c 
                 WHERE c.position_id = $position_id 
                 ORDER BY c.lastname ASC, c.firstname ASC";
        $result = $this->db->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        return $candidates;
    }

    public function getCandidateCount() {
        $query = "SELECT COUNT(*) as count FROM candidates";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function addCandidate($firstname, $lastname, $position_id, $platform, $photo = null, $partylist_id = null) {
        // If photo is a string (path), use it directly
        $photoPath = $photo ?: self::DEFAULT_PHOTO;

        $query = "INSERT INTO candidates (firstname, lastname, position_id, platform, photo, partylist_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssissi", $firstname, $lastname, $position_id, $platform, $photoPath, $partylist_id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateCandidate($id, $firstname, $lastname, $position_id, $platform, $photo = null, $partylist_id = null) {
        // Get current candidate data
        $current = $this->getCandidate($id);
        if (!$current) {
            return false;
        }

        // If photo is a string (path), use it directly
        $photoPath = $photo ?: $current['photo'];

        $query = "UPDATE candidates 
                 SET firstname = ?, lastname = ?, position_id = ?, platform = ?, photo = ?, partylist_id = ? 
                 WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssissii", $firstname, $lastname, $position_id, $platform, $photoPath, $partylist_id, $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteCandidate($id) {
        // Get candidate photo before deleting
        $candidate = $this->getCandidate($id);
        if ($candidate && $candidate['photo'] !== self::DEFAULT_PHOTO) {
            $photoPath = __DIR__ . '/../../' . $candidate['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $query = "DELETE FROM candidates WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}