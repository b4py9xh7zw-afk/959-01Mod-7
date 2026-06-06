<?php
$pageTitle = '许可证详情 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$typeLabels = ['trial' => '试用版', 'paid' => '正式版'];
$tierLabels = ['basic' => '基础版', 'standard' => '标准版', 'premium' => '高级版'];
$statusLabels = ['active' => '活跃', 'inactive' => '未激活', 'expired' => '已过期', 'converted' => '已转换'];
$statusClasses = [
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-100 text-gray-800',
    'expired' => 'bg-red-100 text-red-800',
    'converted' => 'bg-blue-100 text-blue-800'
];
$typeClasses = [
    'trial' => 'bg-yellow-100 text-yellow-800',
    'paid' => 'bg-purple-100 text-purple-800'
];
?>

<div class="max-w-6xl mx-auto space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                许可证详情
            </h1>
            <div class="flex space-x-3 mt-2">
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $typeClasses[$license['type']]; ?>">
                    <?php echo $typeLabels[$license['type']]; ?>
                </span>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                    <?php echo $tierLabels[$license['tier']]; ?>
                </span>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $statusClasses[$license['status']]; ?>">
                    <?php echo $statusLabels[$license['status']]; ?>
                </span>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="/dashboard/licenses" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                ← 返回许可证列表
            </a>
            <a href="/licenses/export?id=<?php echo $license['id']; ?>" class="px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors">
                📥 导出数据
            </a>
            <?php if ($conversionStats && $conversionStats['can_convert']): ?>
                <button onclick="document.getElementById('convertModal').classList.remove('hidden')" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg">
                    🚀 升级为正式版
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($conversionStats && $conversionStats['is_expired_trial'] && $license['status'] !== 'converted'): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <strong>试用期已结束。</strong> 高级功能已被禁用，但您仍可以查看项目数据和导出基础数据。
                        <a href="#" onclick="document.getElementById('convertModal').classList.remove('hidden')" class="font-semibold underline">立即升级为正式版</a> 以恢复所有功能。
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($conversionStats && $license['type'] === 'trial' && !$conversionStats['is_expired_trial']): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>试用期剩余 <?php echo $conversionStats['trial_days_left']; ?> 天。</strong>
                            试用期间您可以完整体验所有功能，试用结束后请购买正式版以继续使用。
                        </p>
                    </div>
                </div>
                <button onclick="document.getElementById('convertModal').classList.remove('hidden')" 
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold hover:bg-yellow-600 transition-colors text-sm">
                    立即升级
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($license['status'] === 'converted' && $license['converted_to_license_id']): ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>此试用许可证已转换为正式版。</strong>
                        所有配置已迁移到新的正式许可证。
                        <a href="/licenses/view?id=<?php echo $license['converted_to_license_id']; ?>" class="font-semibold underline">查看正式许可证</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">基本信息</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">许可证密钥</label>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <code class="text-lg font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">产品名称</label>
                        <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['product_name']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">分配用户</label>
                        <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['username'] ?? 'N/A'); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($license['email'] ?? ''); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">配额</label>
                        <p class="text-lg text-gray-800">
                            <?php echo $conversionStats['project_count']; ?> / <?php echo $license['max_projects']; ?> 项目
                        </p>
                        <p class="text-sm text-gray-600">
                            <?php echo $conversionStats['seat_count']; ?> / <?php echo $license['max_seats']; ?> 席位
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">创建时间</label>
                        <p class="text-lg text-gray-800"><?php echo date('Y-m-d H:i:s', strtotime($license['created_at'])); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2"><?php echo $license['type'] === 'trial' ? '试用到期时间' : '过期时间'; ?></label>
                        <p class="text-lg text-gray-800">
                            <?php 
                            if ($license['type'] === 'trial' && $license['trial_ends_at']) {
                                echo date('Y-m-d H:i:s', strtotime($license['trial_ends_at']));
                            } elseif ($license['expires_at']) {
                                echo date('Y-m-d H:i:s', strtotime($license['expires_at']));
                            } else {
                                echo '永不过期';
                            }
                            ?>
                        </p>
                    </div>
                    
                    <?php if ($license['converted_at']): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">转换时间</label>
                        <p class="text-lg text-gray-800"><?php echo date('Y-m-d H:i:s', strtotime($license['converted_at'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">项目配置</h2>
                    <?php if ($license['status'] === 'active' || $license['status'] === 'converted'): ?>
                        <button onclick="document.getElementById('addProjectModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                            + 添加项目
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (empty($projects)): ?>
                    <p class="text-gray-500 text-center py-8">暂无项目配置</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($projects as $project): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($project['name']); ?></p>
                                    <?php if ($project['description']): ?>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($project['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500 mt-1">创建于 <?php echo date('Y-m-d', strtotime($project['created_at'])); ?></p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $project['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $project['is_active'] ? '已启用' : '已禁用'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">插件配置</h2>
                    <?php if ($license['status'] === 'active' || $license['status'] === 'converted'): ?>
                        <button onclick="document.getElementById('addPluginModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                            + 添加插件
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (empty($plugins)): ?>
                    <p class="text-gray-500 text-center py-8">暂无插件配置</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($plugins as $plugin): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($plugin['name']); ?>
                                        <?php if ($plugin['requires_premium']): ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded bg-purple-100 text-purple-800">高级</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($plugin['plugin_code']); ?>
                                        <?php if ($plugin['version']): ?>
                                            · v<?php echo htmlspecialchars($plugin['version']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">安装于 <?php echo date('Y-m-d', strtotime($plugin['installed_at'])); ?></p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $plugin['is_enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $plugin['is_enabled'] ? '已启用' : '已禁用'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">用户席位</h2>
                    <?php if ($license['status'] === 'active' || $license['status'] === 'converted'): ?>
                        <button onclick="document.getElementById('addSeatModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                            + 添加席位
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (empty($seats)): ?>
                    <p class="text-gray-500 text-center py-8">暂无用户席位</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($seats as $seat): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($seat['user_name'] ?? $seat['user_email']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($seat['user_email']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">邀请于 <?php echo date('Y-m-d', strtotime($seat['invited_at'])); ?></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $seat['role'] === 'admin' ? '管理员' : ($seat['role'] === 'member' ? '成员' : '查看者'); ?>
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $seat['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $seat['is_active'] ? '活跃' : '未激活'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">功能权限</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($features as $feature): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <?php if ($feature['is_allowed']): ?>
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                <?php endif; ?>
                                <span class="text-gray-800"><?php echo htmlspecialchars($feature['feature_name']); ?></span>
                            </div>
                            <?php if ($feature['is_advanced']): ?>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-purple-100 text-purple-800">高级</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-800">基础</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">最近活动</h2>
                <?php if (empty($activityLogs)): ?>
                    <p class="text-gray-500 text-center py-8">暂无活动记录</p>
                <?php else: ?>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        <?php foreach ($activityLogs as $log): ?>
                            <div class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                                <div class="flex-shrink-0 mt-1">
                                    <?php
                                    $logIcons = [
                                        'login' => '🔐',
                                        'project_create' => '📁',
                                        'project_update' => '📝',
                                        'plugin_enable' => '🔌',
                                        'plugin_disable' => '🚫',
                                        'seat_add' => '👤',
                                        'seat_remove' => '❌',
                                        'api_call' => '🔄',
                                        'export' => '📥',
                                        'config_change' => '⚙️'
                                    ];
                                    ?>
                                    <span class="text-xl"><?php echo $logIcons[$log['action_type']] ?? '📋'; ?></span>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-800">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                        <?php if ($log['username']): ?>
                                            <span class="text-gray-500">· <?php echo htmlspecialchars($log['username']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="space-y-6">
            <?php if ($conversionStats && $_SESSION['role'] === 'admin'): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">转化分析</h2>
                    
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">转化可能性</span>
                            <span class="text-sm font-bold <?php 
                                echo $conversionStats['readiness']['conversion_likelihood'] === 'high' ? 'text-green-600' : 
                                    ($conversionStats['readiness']['conversion_likelihood'] === 'medium' ? 'text-yellow-600' : 'text-red-600'); 
                            ?>">
                                <?php 
                                echo $conversionStats['readiness']['conversion_likelihood'] === 'high' ? '高' : 
                                    ($conversionStats['readiness']['conversion_likelihood'] === 'medium' ? '中' : '低'); 
                                ?>
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="h-3 rounded-full transition-all duration-500 <?php 
                                echo $conversionStats['readiness']['conversion_score'] >= 70 ? 'bg-green-500' : 
                                    ($conversionStats['readiness']['conversion_score'] >= 40 ? 'bg-yellow-500' : 'bg-red-500'); 
                            ?>" style="width: <?php echo $conversionStats['readiness']['conversion_score']; ?>%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 text-right">得分: <?php echo $conversionStats['readiness']['conversion_score']; ?>/100</p>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">总活跃次数</span>
                            <span class="font-semibold text-gray-800"><?php echo $conversionStats['activity_count']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">创建项目数</span>
                            <span class="font-semibold text-gray-800"><?php echo $conversionStats['readiness']['stats']['projects_created']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">启用插件数</span>
                            <span class="font-semibold text-gray-800"><?php echo $conversionStats['readiness']['stats']['plugins_enabled']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">独立用户数</span>
                            <span class="font-semibold text-gray-800"><?php echo $conversionStats['readiness']['stats']['unique_users']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">API调用次数</span>
                            <span class="font-semibold text-gray-800"><?php echo $conversionStats['readiness']['stats']['api_calls']; ?></span>
                        </div>
                        <?php if ($conversionStats['readiness']['stats']['last_activity']): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">最后活跃</span>
                            <span class="font-semibold text-gray-800"><?php echo date('Y-m-d', strtotime($conversionStats['readiness']['stats']['last_activity'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">管理员操作</h2>
                <div class="space-y-3">
                    <button 
                        onclick="document.getElementById('updateForm').classList.toggle('hidden')"
                        class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        编辑许可证
                    </button>
                    
                    <?php if ($license['type'] === 'trial' && $license['status'] === 'active'): ?>
                    <form method="POST" action="/licenses/handle-expiry" class="w-full">
                        <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                        <button 
                            type="submit"
                            onclick="return confirm('确定要标记此试用许可证为已过期吗？这将禁用高级功能，但保留数据导出能力。');"
                            class="w-full px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
                        >
                            处理试用到期
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <form method="POST" action="/licenses/delete" onsubmit="return confirm('确定要删除此许可证吗？');" class="w-full">
                        <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                        <button 
                            type="submit"
                            class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                        >
                            删除许可证
                        </button>
                    </form>
                </div>
                
                <form id="updateForm" method="POST" action="/licenses/update" class="hidden mt-6 space-y-4 bg-gray-50 p-6 rounded-lg">
                    <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                    
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">产品名称</label>
                        <input 
                            type="text" 
                            id="product_name" 
                            name="product_name" 
                            value="<?php echo htmlspecialchars($license['product_name']); ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">类型</label>
                        <select 
                            id="type" 
                            name="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="trial" <?php echo $license['type'] === 'trial' ? 'selected' : ''; ?>>试用版</option>
                            <option value="paid" <?php echo $license['type'] === 'paid' ? 'selected' : ''; ?>>正式版</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="tier" class="block text-sm font-medium text-gray-700 mb-2">层级</label>
                        <select 
                            id="tier" 
                            name="tier"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="basic" <?php echo $license['tier'] === 'basic' ? 'selected' : ''; ?>>基础版</option>
                            <option value="standard" <?php echo $license['tier'] === 'standard' ? 'selected' : ''; ?>>标准版</option>
                            <option value="premium" <?php echo $license['tier'] === 'premium' ? 'selected' : ''; ?>>高级版</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">状态</label>
                        <select 
                            id="status" 
                            name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="active" <?php echo $license['status'] === 'active' ? 'selected' : ''; ?>>活跃</option>
                            <option value="inactive" <?php echo $license['status'] === 'inactive' ? 'selected' : ''; ?>>未激活</option>
                            <option value="expired" <?php echo $license['status'] === 'expired' ? 'selected' : ''; ?>>已过期</option>
                            <option value="converted" <?php echo $license['status'] === 'converted' ? 'selected' : ''; ?>>已转换</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">过期时间</label>
                        <input 
                            type="date" 
                            id="expires_at" 
                            name="expires_at"
                            value="<?php echo $license['expires_at'] ? date('Y-m-d', strtotime($license['expires_at'])) : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="max_seats" class="block text-sm font-medium text-gray-700 mb-2">最大席位</label>
                        <input 
                            type="number" 
                            id="max_seats" 
                            name="max_seats"
                            value="<?php echo $license['max_seats']; ?>"
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="max_projects" class="block text-sm font-medium text-gray-700 mb-2">最大项目</label>
                        <input 
                            type="number" 
                            id="max_projects" 
                            name="max_projects"
                            value="<?php echo $license['max_projects']; ?>"
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div class="flex space-x-4">
                        <button 
                            type="submit"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            更新许可证
                        </button>
                        <button 
                            type="button"
                            onclick="document.getElementById('updateForm').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            取消
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Convert Modal -->
    <div id="convertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">升级为正式版</h3>
            <form method="POST" action="/licenses/convert" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                
                <div>
                    <label for="tier" class="block text-sm font-medium text-gray-700 mb-2">选择版本</label>
                    <select 
                        id="tier" 
                        name="tier"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="standard">标准版 - ¥999/年</option>
                        <option value="premium" selected>高级版 - ¥2999/年</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="max_seats" class="block text-sm font-medium text-gray-700 mb-2">用户席位</label>
                        <input 
                            type="number" 
                            id="max_seats" 
                            name="max_seats"
                            value="10"
                            min="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label for="max_projects" class="block text-sm font-medium text-gray-700 mb-2">项目数量</label>
                        <input 
                            type="number" 
                            id="max_projects" 
                            name="max_projects"
                            value="10"
                            min="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">过期时间（可选）</label>
                    <input 
                        type="date" 
                        id="expires_at" 
                        name="expires_at"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <strong>✅ 所有配置将被保留：</strong>项目、插件、用户席位等配置将自动迁移到新的正式许可证中，无需重新配置。
                    </p>
                </div>
                
                <div class="flex space-x-4 pt-4">
                    <button 
                        type="button"
                        onclick="document.getElementById('convertModal').classList.add('hidden')"
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                    >
                        取消
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg"
                    >
                        确认升级
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Project Modal -->
    <div id="addProjectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">添加项目</h3>
            <form method="POST" action="/licenses/add-project" class="space-y-4">
                <input type="hidden" name="license_id" value="<?php echo $license['id']; ?>">
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">项目名称 *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="请输入项目名称"
                    >
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">项目描述</label>
                    <textarea 
                        id="description" 
                        name="description"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="请输入项目描述"
                    ></textarea>
                </div>
                
                <div class="flex space-x-4 pt-4">
                    <button 
                        type="button"
                        onclick="document.getElementById('addProjectModal').classList.add('hidden')"
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                    >
                        取消
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                    >
                        添加项目
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Plugin Modal -->
    <div id="addPluginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">添加插件</h3>
            <form method="POST" action="/licenses/add-plugin" class="space-y-4">
                <input type="hidden" name="license_id" value="<?php echo $license['id']; ?>">
                
                <div>
                    <label for="plugin_name" class="block text-sm font-medium text-gray-700 mb-2">插件名称 *</label>
                    <input 
                        type="text" 
                        id="plugin_name" 
                        name="name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="请输入插件名称"
                    >
                </div>
                
                <div>
                    <label for="plugin_code" class="block text-sm font-medium text-gray-700 mb-2">插件代码 *</label>
                    <input 
                        type="text" 
                        id="plugin_code" 
                        name="plugin_code" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="例如: payment-gateway"
                    >
                </div>
                
                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700 mb-2">版本号</label>
                    <input 
                        type="text" 
                        id="version" 
                        name="version"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="例如: 1.0.0"
                    >
                </div>
                
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="requires_premium" 
                        name="requires_premium"
                        value="1"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="requires_premium" class="ml-2 block text-sm text-gray-700">
                        这是高级插件（需要正式版才能使用）
                    </label>
                </div>
                
                <div class="flex space-x-4 pt-4">
                    <button 
                        type="button"
                        onclick="document.getElementById('addPluginModal').classList.add('hidden')"
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                    >
                        取消
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                    >
                        添加插件
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Seat Modal -->
    <div id="addSeatModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg mx-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">添加用户席位</h3>
            <form method="POST" action="/licenses/add-seat" class="space-y-4">
                <input type="hidden" name="license_id" value="<?php echo $license['id']; ?>">
                
                <div>
                    <label for="user_email" class="block text-sm font-medium text-gray-700 mb-2">用户邮箱 *</label>
                    <input 
                        type="email" 
                        id="user_email" 
                        name="user_email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="user@example.com"
                    >
                </div>
                
                <div>
                    <label for="user_name" class="block text-sm font-medium text-gray-700 mb-2">用户姓名</label>
                    <input 
                        type="text" 
                        id="user_name" 
                        name="user_name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="请输入用户姓名"
                    >
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">角色</label>
                    <select 
                        id="role" 
                        name="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="member">成员</option>
                        <option value="admin">管理员</option>
                        <option value="viewer">查看者</option>
                    </select>
                </div>
                
                <div class="flex space-x-4 pt-4">
                    <button 
                        type="button"
                        onclick="document.getElementById('addSeatModal').classList.add('hidden')"
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                    >
                        取消
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                    >
                        添加席位
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('type')?.addEventListener('change', function(e) {
    var trialDaysDiv = document.getElementById('trial_days_div');
    if (trialDaysDiv) {
        trialDaysDiv.style.display = e.target.value === 'trial' ? 'block' : 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
