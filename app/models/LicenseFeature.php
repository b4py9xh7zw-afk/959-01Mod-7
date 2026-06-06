<?php
/**
 * LicenseFeature Model
 */

require_once __DIR__ . '/../config/database.php';

class LicenseFeature {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO license_features (license_id, feature_code, feature_name, is_allowed, is_advanced) 
                VALUES (:license_id, :feature_code, :feature_name, :is_allowed, :is_advanced)
                ON DUPLICATE KEY UPDATE 
                    feature_name = VALUES(feature_name),
                    is_allowed = VALUES(is_allowed),
                    is_advanced = VALUES(is_advanced)";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':feature_code' => $data['feature_code'],
            ':feature_name' => $data['feature_name'],
            ':is_allowed' => $data['is_allowed'] ?? 1,
            ':is_advanced' => $data['is_advanced'] ?? 0
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT * FROM license_features WHERE license_id = :license_id";
        return $this->db->fetchAll($sql, [':license_id' => $licenseId]);
    }
    
    public function checkFeature($licenseId, $featureCode) {
        $sql = "SELECT is_allowed FROM license_features WHERE license_id = :license_id AND feature_code = :feature_code";
        $result = $this->db->fetchOne($sql, [
            ':license_id' => $licenseId,
            ':feature_code' => $featureCode
        ]);
        return $result ? (bool)$result['is_allowed'] : false;
    }
    
    public function setFeatureAllowed($licenseId, $featureCode, $isAllowed) {
        $sql = "UPDATE license_features SET is_allowed = :is_allowed WHERE license_id = :license_id AND feature_code = :feature_code";
        $this->db->execute($sql, [
            ':license_id' => $licenseId,
            ':feature_code' => $featureCode,
            ':is_allowed' => $isAllowed
        ]);
        return true;
    }
    
    public function disableAdvancedFeatures($licenseId) {
        $sql = "UPDATE license_features SET is_allowed = 0 WHERE license_id = :license_id AND is_advanced = 1";
        $this->db->execute($sql, [':license_id' => $licenseId]);
        return true;
    }
    
    public function enableAllFeatures($licenseId) {
        $sql = "UPDATE license_features SET is_allowed = 1 WHERE license_id = :license_id";
        $this->db->execute($sql, [':license_id' => $licenseId]);
        return true;
    }
    
    public function transfer($fromLicenseId, $toLicenseId) {
        $sql = "INSERT INTO license_features (license_id, feature_code, feature_name, is_allowed, is_advanced)
                SELECT :to_license_id, feature_code, feature_name, is_allowed, is_advanced
                FROM license_features 
                WHERE license_id = :from_license_id
                ON DUPLICATE KEY UPDATE 
                    feature_name = VALUES(feature_name),
                    is_allowed = VALUES(is_allowed),
                    is_advanced = VALUES(is_advanced)";
        $this->db->execute($sql, [
            ':to_license_id' => $toLicenseId,
            ':from_license_id' => $fromLicenseId
        ]);
        return true;
    }
    
    public function initializeDefaultFeatures($licenseId, $tier = 'basic') {
        $features = [
            ['code' => 'export_data', 'name' => '数据导出', 'advanced' => false],
            ['code' => 'view_projects', 'name' => '查看项目', 'advanced' => false],
            ['code' => 'create_projects', 'name' => '创建项目', 'advanced' => false],
            ['code' => 'edit_projects', 'name' => '编辑项目', 'advanced' => false],
            ['code' => 'basic_plugins', 'name' => '基础插件', 'advanced' => false],
            ['code' => 'advanced_plugins', 'name' => '高级插件', 'advanced' => true],
            ['code' => 'api_access', 'name' => 'API访问', 'advanced' => true],
            ['code' => 'priority_support', 'name' => '优先支持', 'advanced' => true],
            ['code' => 'custom_branding', 'name' => '自定义品牌', 'advanced' => true],
            ['code' => 'analytics', 'name' => '数据分析', 'advanced' => true],
            ['code' => 'team_management', 'name' => '团队管理', 'advanced' => true],
            ['code' => 'unlimited_projects', 'name' => '无限项目', 'advanced' => true],
        ];
        
        foreach ($features as $feature) {
            $isAllowed = $feature['advanced'] ? ($tier !== 'basic') : true;
            $this->create([
                'license_id' => $licenseId,
                'feature_code' => $feature['code'],
                'feature_name' => $feature['name'],
                'is_allowed' => $isAllowed,
                'is_advanced' => $feature['advanced'] ? 1 : 0
            ]);
        }
    }
}
