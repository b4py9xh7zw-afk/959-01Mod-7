<?php
/**
 * UserSeat Model
 */

require_once __DIR__ . '/../config/database.php';

class UserSeat {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO user_seats (license_id, user_email, user_name, role, is_active, joined_at) 
                VALUES (:license_id, :user_email, :user_name, :role, :is_active, :joined_at)";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':user_email' => $data['user_email'],
            ':user_name' => $data['user_name'] ?? null,
            ':role' => $data['role'] ?? 'member',
            ':is_active' => $data['is_active'] ?? 1,
            ':joined_at' => $data['joined_at'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM user_seats WHERE id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT * FROM user_seats WHERE license_id = :license_id ORDER BY invited_at DESC";
        return $this->db->fetchAll($sql, [':license_id' => $licenseId]);
    }
    
    public function countByLicenseId($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM user_seats WHERE license_id = :license_id AND is_active = 1";
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        return $result['count'] ?? 0;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['user_email'])) {
            $fields[] = "user_email = :user_email";
            $params[':user_email'] = $data['user_email'];
        }
        if (isset($data['user_name'])) {
            $fields[] = "user_name = :user_name";
            $params[':user_name'] = $data['user_name'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = :is_active";
            $params[':is_active'] = $data['is_active'];
        }
        if (isset($data['joined_at'])) {
            $fields[] = "joined_at = :joined_at";
            $params[':joined_at'] = $data['joined_at'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE user_seats SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM user_seats WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    public function transfer($fromLicenseId, $toLicenseId) {
        $sql = "UPDATE user_seats SET license_id = :to_license_id WHERE license_id = :from_license_id";
        $this->db->execute($sql, [
            ':to_license_id' => $toLicenseId,
            ':from_license_id' => $fromLicenseId
        ]);
        return true;
    }
}
