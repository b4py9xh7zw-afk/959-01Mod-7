<?php
/**
 * Project Model
 */

require_once __DIR__ . '/../config/database.php';

class Project {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO projects (license_id, name, description, config, is_active) 
                VALUES (:license_id, :name, :description, :config, :is_active)";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':config' => isset($data['config']) ? json_encode($data['config']) : null,
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM projects WHERE id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $id]);
        if ($result && $result['config']) {
            $result['config'] = json_decode($result['config'], true);
        }
        return $result;
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT * FROM projects WHERE license_id = :license_id ORDER BY created_at DESC";
        $results = $this->db->fetchAll($sql, [':license_id' => $licenseId]);
        foreach ($results as &$result) {
            if ($result['config']) {
                $result['config'] = json_decode($result['config'], true);
            }
        }
        return $results;
    }
    
    public function countByLicenseId($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM projects WHERE license_id = :license_id";
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        return $result['count'] ?? 0;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        if (isset($data['config'])) {
            $fields[] = "config = :config";
            $params[':config'] = json_encode($data['config']);
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = :is_active";
            $params[':is_active'] = $data['is_active'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM projects WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    public function transfer($fromLicenseId, $toLicenseId) {
        $sql = "UPDATE projects SET license_id = :to_license_id WHERE license_id = :from_license_id";
        $this->db->execute($sql, [
            ':to_license_id' => $toLicenseId,
            ':from_license_id' => $fromLicenseId
        ]);
        return true;
    }
}
