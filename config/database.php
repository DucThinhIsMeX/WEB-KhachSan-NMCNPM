<?php
class Database {
    private $db_file;
    private $conn;

    public function __construct() {
        $this->db_file = __DIR__ . '/../database/hotel.db';
    }

    public function connect() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            // Tạo thư mục database nếu chưa có
            $db_dir = dirname($this->db_file);
            if (!is_dir($db_dir)) {
                mkdir($db_dir, 0777, true);
            }
            
            $this->conn = new PDO('sqlite:' . $this->db_file);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Bật foreign key constraints cho SQLite
            $this->conn->exec('PRAGMA foreign_keys = ON');
            // Tăng hiệu suất
            $this->conn->exec('PRAGMA journal_mode = WAL');
            $this->conn->exec('PRAGMA synchronous = NORMAL');
            
        } catch(PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }
        
        return $this->conn;
    }
    
    // Hàm lấy tham số từ bảng THAMSO
    public function getThamSo($tenThamSo) {
        try {
            $stmt = $this->conn->prepare("SELECT GiaTri FROM THAMSO WHERE TenThamSo = ?");
            $stmt->execute([$tenThamSo]);
            $result = $stmt->fetch();
            return $result ? $result['GiaTri'] : null;
        } catch(PDOException $e) {
            error_log("Error getThamSo: " . $e->getMessage());
            return null;
        }
    }
    
    // Hàm cập nhật tham số
    public function updateThamSo($tenThamSo, $giaTri) {
        try {
            $stmt = $this->conn->prepare("UPDATE THAMSO SET GiaTri = ? WHERE TenThamSo = ?");
            return $stmt->execute([$giaTri, $tenThamSo]);
        } catch(PDOException $e) {
            error_log("Error updateThamSo: " . $e->getMessage());
            return false;
        }
    }

    // Hàm lấy tất cả tham số
    public function getAllThamSo() {
        try {
            $stmt = $this->conn->query("SELECT * FROM THAMSO ORDER BY TenThamSo");
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getAllThamSo: " . $e->getMessage());
            return [];
        }
    }

    // Kiểm tra database có tồn tại không
    public function isDatabaseInitialized() {
        return file_exists($this->db_file);
    }

    // Lấy thông tin database
    public function getDatabaseInfo() {
        try {
            $info = [
                'file' => $this->db_file,
                'size' => file_exists($this->db_file) ? filesize($this->db_file) : 0,
                'tables' => []
            ];

            $stmt = $this->conn->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            $info['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $info;
        } catch(PDOException $e) {
            error_log("Error getDatabaseInfo: " . $e->getMessage());
            return null;
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        $this->conn = null;
    }
}
?>
