<?php
/**
 * Plugin Model
 */

require_once __DIR__ . '/../config/database.php';

class Plugin {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO plugins (license_id, name, plugin_code, version, is_enabled, requires_premium, config) 
                VALUES (:license_id, :name, :plugin_code, :version, :is_enabled, :requires_premium, :config)";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':name' => $data['name'],
            ':plugin_code' => $data['plugin_code'],
            ':version' => $data['version'] ?? null,
            ':is_enabled' => $data['is_enabled'] ?? 1,
            ':requires_premium' => $data['requires_premium'] ?? 0,
            ':config' => isset($data['config']) ? json_encode($data['config']) : null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM plugins WHERE id = :id";
        $result = $this->db->fetchOne($sql, [':id' => $id]);
        if ($result && $result['config']) {
            $result['config'] = json_decode($result['config'], true);
        }
        return $result;
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT * FROM plugins WHERE license_id = :license_id ORDER BY installed_at DESC";
        $results = $this->db->fetchAll($sql, [':license_id' => $licenseId]);
        foreach ($results as &$result) {
            if ($result['config']) {
                $result['config'] = json_decode($result['config'], true);
            }
        }
        return $results;
    }
    
    public function countByLicenseId($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM plugins WHERE license_id = :license_id";
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
        if (isset($data['version'])) {
            $fields[] = "version = :version";
            $params[':version'] = $data['version'];
        }
        if (isset($data['is_enabled'])) {
            $fields[] = "is_enabled = :is_enabled";
            $params[':is_enabled'] = $data['is_enabled'];
        }
        if (isset($data['config'])) {
            $fields[] = "config = :config";
            $params[':config'] = json_encode($data['config']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE plugins SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM plugins WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    public function transfer($fromLicenseId, $toLicenseId) {
        $sql = "UPDATE plugins SET license_id = :to_license_id WHERE license_id = :from_license_id";
        $this->db->execute($sql, [
            ':to_license_id' => $toLicenseId,
            ':from_license_id' => $fromLicenseId
        ]);
        return true;
    }
    
    public function disablePremiumPlugins($licenseId) {
        $sql = "UPDATE plugins SET is_enabled = 0 WHERE license_id = :license_id AND requires_premium = 1";
        $this->db->execute($sql, [':license_id' => $licenseId]);
        return true;
    }
}
