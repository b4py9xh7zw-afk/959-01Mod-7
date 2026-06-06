<?php
/**
 * License Model
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Project.php';
require_once __DIR__ . '/Plugin.php';
require_once __DIR__ . '/UserSeat.php';
require_once __DIR__ . '/ActivityLog.php';
require_once __DIR__ . '/LicenseFeature.php';

class License {
    private $db;
    private $projectModel;
    private $pluginModel;
    private $userSeatModel;
    private $activityLogModel;
    private $licenseFeatureModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->projectModel = new Project();
        $this->pluginModel = new Plugin();
        $this->userSeatModel = new UserSeat();
        $this->activityLogModel = new ActivityLog();
        $this->licenseFeatureModel = new LicenseFeature();
    }
    
    public function create($data) {
        $sql = "INSERT INTO licenses (license_key, user_id, product_name, type, tier, status, expires_at, trial_ends_at, max_seats, max_projects, created_at) 
                VALUES (:license_key, :user_id, :product_name, :type, :tier, :status, :expires_at, :trial_ends_at, :max_seats, :max_projects, NOW())";
        
        $type = $data['type'] ?? 'trial';
        $tier = $data['tier'] ?? 'basic';
        
        $trialEndsAt = null;
        if ($type === 'trial') {
            $trialDays = $data['trial_days'] ?? 14;
            $trialEndsAt = date('Y-m-d H:i:s', time() + ($trialDays * 86400));
        }
        
        $params = [
            ':license_key' => $this->generateLicenseKey(),
            ':user_id' => $data['user_id'],
            ':product_name' => $data['product_name'],
            ':type' => $type,
            ':tier' => $tier,
            ':status' => $data['status'] ?? 'active',
            ':expires_at' => $data['expires_at'] ?? null,
            ':trial_ends_at' => $trialEndsAt,
            ':max_seats' => $data['max_seats'] ?? ($type === 'trial' ? 3 : 10),
            ':max_projects' => $data['max_projects'] ?? ($type === 'trial' ? 2 : 10)
        ];
        
        $this->db->execute($sql, $params);
        $licenseId = $this->db->lastInsertId();
        
        $this->licenseFeatureModel->initializeDefaultFeatures($licenseId, $tier);
        
        $this->logActivity($licenseId, $data['user_id'], 'create_license', 'config_change', [
            'action' => 'created',
            'type' => $type,
            'tier' => $tier
        ]);
        
        return $licenseId;
    }
    
    public function convertTrialToPaid($trialLicenseId, $data = []) {
        $trialLicense = $this->findById($trialLicenseId);
        
        if (!$trialLicense) {
            throw new Exception('试用许可证不存在');
        }
        
        if ($trialLicense['type'] !== 'trial') {
            throw new Exception('该许可证不是试用许可证');
        }
        
        if ($trialLicense['status'] === 'converted') {
            throw new Exception('该试用许可证已转换为正式许可证');
        }
        
        $newLicenseId = $this->create([
            'user_id' => $trialLicense['user_id'],
            'product_name' => $trialLicense['product_name'],
            'type' => 'paid',
            'tier' => $data['tier'] ?? 'premium',
            'status' => 'active',
            'expires_at' => $data['expires_at'] ?? null,
            'max_seats' => $data['max_seats'] ?? 10,
            'max_projects' => $data['max_projects'] ?? 10
        ]);
        
        $this->projectModel->transfer($trialLicenseId, $newLicenseId);
        $this->pluginModel->transfer($trialLicenseId, $newLicenseId);
        $this->userSeatModel->transfer($trialLicenseId, $newLicenseId);
        $this->activityLogModel->transfer($trialLicenseId, $newLicenseId);
        $this->licenseFeatureModel->transfer($trialLicenseId, $newLicenseId);
        
        $this->licenseFeatureModel->enableAllFeatures($newLicenseId);
        
        $sql = "UPDATE licenses SET 
                    status = 'converted',
                    converted_at = NOW(),
                    converted_to_license_id = :converted_to
                WHERE id = :id";
        $this->db->execute($sql, [
            ':converted_to' => $newLicenseId,
            ':id' => $trialLicenseId
        ]);
        
        $this->logActivity($newLicenseId, $trialLicense['user_id'], 'convert_trial_to_paid', 'config_change', [
            'action' => 'converted',
            'from_license_id' => $trialLicenseId,
            'to_license_id' => $newLicenseId,
            'tier' => $data['tier'] ?? 'premium'
        ]);
        
        return [
            'success' => true,
            'new_license_id' => $newLicenseId,
            'old_license_id' => $trialLicenseId,
            'message' => '试用转正式成功，所有配置已保留'
        ];
    }
    
    public function handleTrialExpiry($licenseId) {
        $license = $this->findById($licenseId);
        
        if (!$license) {
            throw new Exception('许可证不存在');
        }
        
        if ($license['type'] !== 'trial') {
            throw new Exception('该许可证不是试用许可证');
        }
        
        if ($license['trial_ends_at'] && strtotime($license['trial_ends_at']) > time()) {
            throw new Exception('试用期尚未结束');
        }
        
        $this->licenseFeatureModel->disableAdvancedFeatures($licenseId);
        $this->pluginModel->disablePremiumPlugins($licenseId);
        
        $this->licenseFeatureModel->setFeatureAllowed($licenseId, 'export_data', 1);
        $this->licenseFeatureModel->setFeatureAllowed($licenseId, 'view_projects', 1);
        
        $sql = "UPDATE licenses SET status = 'expired' WHERE id = :id";
        $this->db->execute($sql, [':id' => $licenseId]);
        
        $this->logActivity($licenseId, $license['user_id'], 'trial_expired', 'config_change', [
            'action' => 'expired',
            'advanced_features_disabled' => true,
            'export_allowed' => true
        ]);
        
        return [
            'success' => true,
            'message' => '试用期已结束，高级功能已禁用，基础数据仍可导出'
        ];
    }
    
    public function checkFeatureAccess($licenseId, $featureCode) {
        $license = $this->findById($licenseId);
        
        if (!$license) {
            return ['allowed' => false, 'message' => '许可证不存在'];
        }
        
        if ($license['status'] === 'converted') {
            if ($license['converted_to_license_id']) {
                return $this->checkFeatureAccess($license['converted_to_license_id'], $featureCode);
            }
        }
        
        if ($license['status'] !== 'active') {
            if ($featureCode === 'export_data' || $featureCode === 'view_projects') {
                return ['allowed' => true, 'message' => '基础功能可用'];
            }
            return ['allowed' => false, 'message' => '许可证未激活或已过期'];
        }
        
        if ($license['type'] === 'trial' && $license['trial_ends_at'] && strtotime($license['trial_ends_at']) < time()) {
            if ($featureCode === 'export_data' || $featureCode === 'view_projects') {
                return ['allowed' => true, 'message' => '基础功能可用'];
            }
            return ['allowed' => false, 'message' => '试用期已结束，请购买正式版'];
        }
        
        $hasAccess = $this->licenseFeatureModel->checkFeature($licenseId, $featureCode);
        
        if (!$hasAccess) {
            return ['allowed' => false, 'message' => '此功能需要升级到高级版本'];
        }
        
        return ['allowed' => true, 'message' => '权限正常'];
    }
    
    public function getConversionStats($licenseId) {
        $license = $this->findById($licenseId);
        
        if (!$license) {
            return null;
        }
        
        $readiness = $this->activityLogModel->getConversionReadiness($licenseId);
        $projectCount = $this->projectModel->countByLicenseId($licenseId);
        $pluginCount = $this->pluginModel->countByLicenseId($licenseId);
        $seatCount = $this->userSeatModel->countByLicenseId($licenseId);
        $activityCount = $this->activityLogModel->countByLicenseId($licenseId);
        $dailyActivity = $this->activityLogModel->getDailyActiveUsers($licenseId, 30);
        
        $trialDaysLeft = null;
        if ($license['type'] === 'trial' && $license['trial_ends_at']) {
            $trialDaysLeft = max(0, floor((strtotime($license['trial_ends_at']) - time()) / 86400));
        }
        
        return [
            'license' => $license,
            'readiness' => $readiness,
            'project_count' => $projectCount,
            'plugin_count' => $pluginCount,
            'seat_count' => $seatCount,
            'activity_count' => $activityCount,
            'daily_activity' => $dailyActivity,
            'trial_days_left' => $trialDaysLeft,
            'can_convert' => $license['type'] === 'trial' && $license['status'] === 'active',
            'is_expired_trial' => $license['type'] === 'trial' && $license['trial_ends_at'] && strtotime($license['trial_ends_at']) < time()
        ];
    }
    
    public function exportBasicData($licenseId) {
        $access = $this->checkFeatureAccess($licenseId, 'export_data');
        
        if (!$access['allowed']) {
            throw new Exception($access['message']);
        }
        
        $projects = $this->projectModel->findByLicenseId($licenseId);
        $plugins = $this->pluginModel->findByLicenseId($licenseId);
        $seats = $this->userSeatModel->findByLicenseId($licenseId);
        $license = $this->findById($licenseId);
        
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'license' => [
                'license_key' => $license['license_key'],
                'product_name' => $license['product_name'],
                'type' => $license['type'],
                'tier' => $license['tier'],
                'status' => $license['status'],
                'created_at' => $license['created_at'],
                'trial_ends_at' => $license['trial_ends_at']
            ],
            'projects' => [],
            'plugins' => [],
            'user_seats' => []
        ];
        
        foreach ($projects as $project) {
            $exportData['projects'][] = [
                'name' => $project['name'],
                'description' => $project['description'],
                'config' => $project['config'],
                'created_at' => $project['created_at']
            ];
        }
        
        foreach ($plugins as $plugin) {
            $exportData['plugins'][] = [
                'name' => $plugin['name'],
                'plugin_code' => $plugin['plugin_code'],
                'version' => $plugin['version'],
                'is_enabled' => $plugin['is_enabled'],
                'config' => $plugin['config'],
                'installed_at' => $plugin['installed_at']
            ];
        }
        
        foreach ($seats as $seat) {
            $exportData['user_seats'][] = [
                'user_email' => $seat['user_email'],
                'user_name' => $seat['user_name'],
                'role' => $seat['role'],
                'is_active' => $seat['is_active'],
                'invited_at' => $seat['invited_at']
            ];
        }
        
        $this->logActivity($licenseId, null, 'export_data', 'export', [
            'action' => 'exported',
            'project_count' => count($projects),
            'plugin_count' => count($plugins),
            'seat_count' => count($seats)
        ]);
        
        return $exportData;
    }
    
    public function findById($id) {
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByKey($key) {
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.license_key = :key";
        return $this->db->fetchOne($sql, [':key' => $key]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.user_id = :user_id 
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function findAllTrialsForSales($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.type = 'trial'
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        $licenses = $this->db->fetchAll($sql);
        
        $result = [];
        foreach ($licenses as $license) {
            $stats = $this->getConversionStats($license['id']);
            $result[] = array_merge($license, [
                'conversion_stats' => $stats
            ]);
        }
        
        return $result;
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM licenses";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
    
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE status = :status";
        $result = $this->db->fetchOne($sql, [':status' => $status]);
        return $result['count'] ?? 0;
    }
    
    public function countByType($type) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE type = :type";
        $result = $this->db->fetchOne($sql, [':type' => $type]);
        return $result['count'] ?? 0;
    }
    
    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE user_id = :user_id";
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['product_name'])) {
            $fields[] = "product_name = :product_name";
            $params[':product_name'] = $data['product_name'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        if (isset($data['expires_at'])) {
            $fields[] = "expires_at = :expires_at";
            $params[':expires_at'] = $data['expires_at'];
        }
        if (isset($data['user_id'])) {
            $fields[] = "user_id = :user_id";
            $params[':user_id'] = $data['user_id'];
        }
        if (isset($data['type'])) {
            $fields[] = "type = :type";
            $params[':type'] = $data['type'];
        }
        if (isset($data['tier'])) {
            $fields[] = "tier = :tier";
            $params[':tier'] = $data['tier'];
        }
        if (isset($data['max_seats'])) {
            $fields[] = "max_seats = :max_seats";
            $params[':max_seats'] = $data['max_seats'];
        }
        if (isset($data['max_projects'])) {
            $fields[] = "max_projects = :max_projects";
            $params[':max_projects'] = $data['max_projects'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE licenses SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM licenses WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    private function generateLicenseKey() {
        return strtoupper(
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8)
        );
    }
    
    public function validate($licenseKey) {
        $license = $this->findByKey($licenseKey);
        if (!$license) {
            return ['valid' => false, 'message' => 'License key not found'];
        }
        
        if ($license['status'] !== 'active') {
            return ['valid' => false, 'message' => 'License is not active'];
        }
        
        if ($license['type'] === 'trial' && $license['trial_ends_at'] && strtotime($license['trial_ends_at']) < time()) {
            return ['valid' => false, 'message' => 'Trial period has expired'];
        }
        
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'License has expired'];
        }
        
        return ['valid' => true, 'license' => $license];
    }
    
    private function logActivity($licenseId, $userId, $action, $actionType, $metadata = []) {
        try {
            $this->activityLogModel->create([
                'license_id' => $licenseId,
                'user_id' => $userId,
                'action' => $action,
                'action_type' => $actionType,
                'metadata' => $metadata,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }
    
    public function getTrialConversionSummary() {
        $sql = "SELECT 
                    COUNT(*) as total_trials,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                    SUM(CASE WHEN status = 'active' AND trial_ends_at > NOW() THEN 1 ELSE 0 END) as active_trials
                FROM licenses 
                WHERE type = 'trial'";
        
        return $this->db->fetchOne($sql);
    }
}
