<?php
/**
 * License Controller
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/License.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Plugin.php';
require_once __DIR__ . '/../models/UserSeat.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class LicenseController {
    private $authController;
    private $licenseModel;
    private $userModel;
    private $projectModel;
    private $pluginModel;
    private $userSeatModel;
    private $activityLogModel;
    
    public function __construct() {
        $this->authController = new AuthController();
        $this->licenseModel = new License();
        $this->userModel = new User();
        $this->projectModel = new Project();
        $this->pluginModel = new Plugin();
        $this->userSeatModel = new UserSeat();
        $this->activityLogModel = new ActivityLog();
    }
    
    public function create() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productName = $_POST['product_name'] ?? '';
            $userId = $_POST['user_id'] ?? $_SESSION['user_id'];
            $status = $_POST['status'] ?? 'active';
            $expiresAt = $_POST['expires_at'] ?? null;
            $type = $_POST['type'] ?? 'trial';
            $tier = $_POST['tier'] ?? 'basic';
            $trialDays = $_POST['trial_days'] ?? 14;
            $maxSeats = $_POST['max_seats'] ?? null;
            $maxProjects = $_POST['max_projects'] ?? null;
            
            if (empty($productName)) {
                $_SESSION['error'] = '产品名称是必填项';
                header('Location: /licenses/create');
                exit;
            }
            
            if ($userId != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
                $_SESSION['error'] = '访问被拒绝';
                header('Location: /dashboard');
                exit;
            }
            
            try {
                $licenseData = [
                    'user_id' => $userId,
                    'product_name' => $productName,
                    'status' => $status,
                    'expires_at' => $expiresAt ?: null,
                    'type' => $type,
                    'tier' => $tier
                ];
                
                if ($type === 'trial') {
                    $licenseData['trial_days'] = $trialDays;
                }
                
                if ($maxSeats) {
                    $licenseData['max_seats'] = $maxSeats;
                }
                if ($maxProjects) {
                    $licenseData['max_projects'] = $maxProjects;
                }
                
                $licenseId = $this->licenseModel->create($licenseData);
                
                $_SESSION['success'] = $type === 'trial' ? '试用许可证创建成功' : '正式许可证创建成功';
                header('Location: /licenses/view?id=' . $licenseId);
                exit;
            } catch (Exception $e) {
                error_log("License creation error: " . $e->getMessage());
                $_SESSION['error'] = '创建许可证失败，请重试';
                header('Location: /licenses/create');
                exit;
            }
        }
        
        $users = [];
        if ($_SESSION['role'] === 'admin') {
            $users = $this->userModel->findAll(1000, 0);
        }
        
        require_once __DIR__ . '/../views/licenses/create.php';
    }
    
    public function view() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($id);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $conversionStats = $this->licenseModel->getConversionStats($id);
        $projects = $this->projectModel->findByLicenseId($id);
        $plugins = $this->pluginModel->findByLicenseId($id);
        $seats = $this->userSeatModel->findByLicenseId($id);
        $activityLogs = $this->activityLogModel->findByLicenseId($id, 20, 0);
        
        $features = $this->licenseModel->licenseFeatureModel->findByLicenseId($id);
        
        require_once __DIR__ . '/../views/licenses/view.php';
    }
    
    public function update() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($id);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝，需要管理员权限';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        try {
            $data = [];
            if (isset($_POST['product_name'])) {
                $data['product_name'] = $_POST['product_name'];
            }
            if (isset($_POST['status'])) {
                $data['status'] = $_POST['status'];
            }
            if (isset($_POST['expires_at'])) {
                $data['expires_at'] = $_POST['expires_at'] ?: null;
            }
            if (isset($_POST['user_id'])) {
                $data['user_id'] = $_POST['user_id'];
            }
            if (isset($_POST['type'])) {
                $data['type'] = $_POST['type'];
            }
            if (isset($_POST['tier'])) {
                $data['tier'] = $_POST['tier'];
            }
            if (isset($_POST['max_seats'])) {
                $data['max_seats'] = $_POST['max_seats'];
            }
            if (isset($_POST['max_projects'])) {
                $data['max_projects'] = $_POST['max_projects'];
            }
            
            $this->licenseModel->update($id, $data);
            $_SESSION['success'] = '许可证更新成功';
            header('Location: /licenses/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("License update error: " . $e->getMessage());
            $_SESSION['error'] = '更新许可证失败，请重试';
            header('Location: /licenses/view?id=' . $id);
            exit;
        }
    }
    
    public function delete() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        try {
            $this->licenseModel->delete($id);
            $_SESSION['success'] = '许可证删除成功';
            header('Location: /dashboard/licenses');
            exit;
        } catch (Exception $e) {
            error_log("License deletion error: " . $e->getMessage());
            $_SESSION['error'] = '删除许可证失败，请重试';
            header('Location: /dashboard/licenses');
            exit;
        }
    }
    
    public function convert() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($id);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        try {
            $tier = $_POST['tier'] ?? 'premium';
            $maxSeats = $_POST['max_seats'] ?? 10;
            $maxProjects = $_POST['max_projects'] ?? 10;
            $expiresAt = $_POST['expires_at'] ?? null;
            
            $result = $this->licenseModel->convertTrialToPaid($id, [
                'tier' => $tier,
                'max_seats' => $maxSeats,
                'max_projects' => $maxProjects,
                'expires_at' => $expiresAt ?: null
            ]);
            
            $_SESSION['success'] = $result['message'];
            header('Location: /licenses/view?id=' . $result['new_license_id']);
            exit;
        } catch (Exception $e) {
            error_log("Trial conversion error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /licenses/view?id=' . $id);
            exit;
        }
    }
    
    public function handleExpiry() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        try {
            $result = $this->licenseModel->handleTrialExpiry($id);
            $_SESSION['success'] = $result['message'];
            header('Location: /licenses/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Trial expiry handling error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /licenses/view?id=' . $id);
            exit;
        }
    }
    
    public function export() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($id);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        try {
            $exportData = $this->licenseModel->exportBasicData($id);
            
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="license_export_' . $license['license_key'] . '_' . date('YmdHis') . '.json"');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Data export error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /licenses/view?id=' . $id);
            exit;
        }
    }
    
    public function sales() {
        $this->authController->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $trialLicenses = $this->licenseModel->findAllTrialsForSales($limit, $offset);
        $total = $this->licenseModel->countByType('trial');
        $totalPages = ceil($total / $limit);
        
        $conversionSummary = $this->licenseModel->getTrialConversionSummary();
        
        require_once __DIR__ . '/../views/dashboard/sales.php';
    }
    
    public function addProject() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $licenseId = $_POST['license_id'] ?? null;
        if (!$licenseId) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($licenseId);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $access = $this->licenseModel->checkFeatureAccess($licenseId, 'create_projects');
        if (!$access['allowed']) {
            $_SESSION['error'] = $access['message'];
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
        
        try {
            $projectId = $this->projectModel->create([
                'license_id' => $licenseId,
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'config' => !empty($_POST['config']) ? json_decode($_POST['config'], true) : null
            ]);
            
            $this->activityLogModel->create([
                'license_id' => $licenseId,
                'user_id' => $_SESSION['user_id'],
                'action' => '创建项目',
                'action_type' => 'project_create',
                'metadata' => ['project_id' => $projectId, 'project_name' => $_POST['name']],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            $_SESSION['success'] = '项目创建成功';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        } catch (Exception $e) {
            error_log("Project creation error: " . $e->getMessage());
            $_SESSION['error'] = '创建项目失败，请重试';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
    }
    
    public function addPlugin() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $licenseId = $_POST['license_id'] ?? null;
        if (!$licenseId) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($licenseId);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $requiresPremium = isset($_POST['requires_premium']) && $_POST['requires_premium'] == 1;
        if ($requiresPremium) {
            $access = $this->licenseModel->checkFeatureAccess($licenseId, 'advanced_plugins');
            if (!$access['allowed']) {
                $_SESSION['error'] = '高级插件需要升级到正式版';
                header('Location: /licenses/view?id=' . $licenseId);
                exit;
            }
        }
        
        try {
            $pluginId = $this->pluginModel->create([
                'license_id' => $licenseId,
                'name' => $_POST['name'] ?? '',
                'plugin_code' => $_POST['plugin_code'] ?? '',
                'version' => $_POST['version'] ?? null,
                'requires_premium' => $requiresPremium ? 1 : 0,
                'config' => !empty($_POST['config']) ? json_decode($_POST['config'], true) : null
            ]);
            
            $this->activityLogModel->create([
                'license_id' => $licenseId,
                'user_id' => $_SESSION['user_id'],
                'action' => '启用插件',
                'action_type' => 'plugin_enable',
                'metadata' => ['plugin_id' => $pluginId, 'plugin_name' => $_POST['name'], 'plugin_code' => $_POST['plugin_code']],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            $_SESSION['success'] = '插件添加成功';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        } catch (Exception $e) {
            error_log("Plugin addition error: " . $e->getMessage());
            $_SESSION['error'] = '添加插件失败，请重试';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
    }
    
    public function addSeat() {
        $this->authController->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $licenseId = $_POST['license_id'] ?? null;
        if (!$licenseId) {
            $_SESSION['error'] = '许可证ID是必填项';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $license = $this->licenseModel->findById($licenseId);
        if (!$license) {
            $_SESSION['error'] = '许可证不存在';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        if ($license['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = '访问被拒绝';
            header('Location: /dashboard/licenses');
            exit;
        }
        
        $access = $this->licenseModel->checkFeatureAccess($licenseId, 'team_management');
        if (!$access['allowed']) {
            $_SESSION['error'] = $access['message'];
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
        
        $currentSeats = $this->userSeatModel->countByLicenseId($licenseId);
        if ($currentSeats >= $license['max_seats']) {
            $_SESSION['error'] = '已达到最大用户席位数，请升级许可证';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
        
        try {
            $seatId = $this->userSeatModel->create([
                'license_id' => $licenseId,
                'user_email' => $_POST['user_email'] ?? '',
                'user_name' => $_POST['user_name'] ?? null,
                'role' => $_POST['role'] ?? 'member'
            ]);
            
            $this->activityLogModel->create([
                'license_id' => $licenseId,
                'user_id' => $_SESSION['user_id'],
                'action' => '添加用户席位',
                'action_type' => 'seat_add',
                'metadata' => ['seat_id' => $seatId, 'user_email' => $_POST['user_email']],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            $_SESSION['success'] = '用户席位添加成功';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        } catch (Exception $e) {
            error_log("Seat addition error: " . $e->getMessage());
            $_SESSION['error'] = '添加用户席位失败，请重试';
            header('Location: /licenses/view?id=' . $licenseId);
            exit;
        }
    }
}
