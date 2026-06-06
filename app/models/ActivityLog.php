<?php
/**
 * ActivityLog Model
 */

require_once __DIR__ . '/../config/database.php';

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO activity_logs (license_id, user_id, action, action_type, metadata, ip_address, user_agent) 
                VALUES (:license_id, :user_id, :action, :action_type, :metadata, :ip_address, :user_agent)";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':user_id' => $data['user_id'] ?? null,
            ':action' => $data['action'],
            ':action_type' => $data['action_type'],
            ':metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            ':ip_address' => $data['ip_address'] ?? null,
            ':user_agent' => $data['user_agent'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByLicenseId($licenseId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT al.*, u.username 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.license_id = :license_id 
                ORDER BY al.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        $results = $this->db->fetchAll($sql, [':license_id' => $licenseId]);
        foreach ($results as &$result) {
            if ($result['metadata']) {
                $result['metadata'] = json_decode($result['metadata'], true);
            }
        }
        return $results;
    }
    
    public function getActivityStats($licenseId, $startDate = null, $endDate = null) {
        $whereClause = "WHERE license_id = :license_id";
        $params = [':license_id' => $licenseId];
        
        if ($startDate) {
            $whereClause .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $whereClause .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $sql = "SELECT 
                    action_type,
                    COUNT(*) as total,
                    DATE(created_at) as activity_date
                FROM activity_logs 
                {$whereClause}
                GROUP BY action_type, DATE(created_at)
                ORDER BY activity_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getDailyActiveUsers($licenseId, $days = 30) {
        $sql = "SELECT 
                    DATE(created_at) as activity_date,
                    COUNT(DISTINCT user_id) as active_users,
                    COUNT(*) as total_actions
                FROM activity_logs 
                WHERE license_id = :license_id 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY activity_date DESC";
        
        return $this->db->fetchAll($sql, [
            ':license_id' => $licenseId,
            ':days' => $days
        ]);
    }
    
    public function getConversionReadiness($licenseId) {
        $sql = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT CASE WHEN action_type = 'project_create' THEN id END) as projects_created,
                    COUNT(DISTINCT CASE WHEN action_type = 'plugin_enable' THEN id END) as plugins_enabled,
                    COUNT(DISTINCT CASE WHEN action_type = 'api_call' THEN id END) as api_calls,
                    MAX(created_at) as last_activity
                FROM activity_logs 
                WHERE license_id = :license_id";
        
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        
        $score = 0;
        if ($result) {
            if ($result['total_activities'] > 50) $score += 25;
            elseif ($result['total_activities'] > 20) $score += 15;
            elseif ($result['total_activities'] > 5) $score += 5;
            
            if ($result['unique_users'] >= 3) $score += 25;
            elseif ($result['unique_users'] >= 2) $score += 15;
            elseif ($result['unique_users'] >= 1) $score += 5;
            
            if ($result['projects_created'] >= 3) $score += 20;
            elseif ($result['projects_created'] >= 1) $score += 10;
            
            if ($result['plugins_enabled'] >= 2) $score += 15;
            elseif ($result['plugins_enabled'] >= 1) $score += 5;
            
            if ($result['api_calls'] > 100) $score += 15;
            elseif ($result['api_calls'] > 10) $score += 5;
            
            if ($result['last_activity']) {
                $daysSinceLast = (time() - strtotime($result['last_activity'])) / 86400;
                if ($daysSinceLast < 1) $score += 10;
                elseif ($daysSinceLast < 3) $score += 5;
            }
        }
        
        return [
            'stats' => $result,
            'conversion_score' => min(100, $score),
            'conversion_likelihood' => $score >= 70 ? 'high' : ($score >= 40 ? 'medium' : 'low')
        ];
    }
    
    public function transfer($fromLicenseId, $toLicenseId) {
        $sql = "UPDATE activity_logs SET license_id = :to_license_id WHERE license_id = :from_license_id";
        $this->db->execute($sql, [
            ':to_license_id' => $toLicenseId,
            ':from_license_id' => $fromLicenseId
        ]);
        return true;
    }
    
    public function countByLicenseId($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM activity_logs WHERE license_id = :license_id";
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        return $result['count'] ?? 0;
    }
}
