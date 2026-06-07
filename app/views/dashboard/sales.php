<?php
$pageTitle = '销售转化分析 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$likelihoodColors = [
    'high' => 'bg-green-100 text-green-800',
    'medium' => 'bg-yellow-100 text-yellow-800',
    'low' => 'bg-red-100 text-red-800'
];
$likelihoodLabels = [
    'high' => '高',
    'medium' => '中',
    'low' => '低'
];
$statusLabels = ['active' => '活跃', 'inactive' => '未激活', 'expired' => '已过期', 'converted' => '已转换'];
$statusClasses = [
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-100 text-gray-800',
    'expired' => 'bg-red-100 text-red-800',
    'converted' => 'bg-blue-100 text-blue-800'
];
?>

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            销售转化分析
        </h1>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">试用许可证总数</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $conversionSummary['total_trials'] ?? 0; ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">活跃试用</p>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $conversionSummary['active_trials'] ?? 0; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">已转换</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $conversionSummary['converted'] ?? 0; ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">已过期未转化</p>
                    <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $conversionSummary['expired'] ?? 0; ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conversion Rate -->
    <?php if ($conversionSummary && $conversionSummary['total_trials'] > 0): ?>
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">总体转化率</h2>
        <div class="flex items-center space-x-8">
            <div class="flex-1">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">转化率</span>
                    <span class="text-sm font-bold text-purple-600">
                        <?php echo round(($conversionSummary['converted'] / $conversionSummary['total_trials']) * 100, 1); ?>%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="h-4 rounded-full bg-gradient-to-r from-blue-600 to-purple-600" 
                        style="width: <?php echo min(100, ($conversionSummary['converted'] / $conversionSummary['total_trials']) * 100); ?>%">
                    </div>
                </div>
            </div>
            <div class="text-center">
                <p class="text-4xl font-bold text-purple-600">
                    <?php echo $conversionSummary['converted']; ?>
                    <span class="text-lg text-gray-500">/ <?php echo $conversionSummary['total_trials']; ?></span>
                </p>
                <p class="text-sm text-gray-500">已转换 / 总数</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Trial List -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">试用许可证列表</h2>
            <p class="text-sm text-gray-500 mt-1">按转化可能性排序，优先跟进高意向客户</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">客户</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">产品</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">试用天数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">转化可能性</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用情况</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($trialLicenses)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">暂无试用许可证数据</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        usort($trialLicenses, function($a, $b) {
                            $scoreA = $a['conversion_stats']['readiness']['conversion_score'] ?? 0;
                            $scoreB = $b['conversion_stats']['readiness']['conversion_score'] ?? 0;
                            return $scoreB - $scoreA;
                        });
                        ?>
                        <?php foreach ($trialLicenses as $license): ?>
                            <?php $stats = $license['conversion_stats']; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold">
                                                <?php echo strtoupper(substr($license['username'] ?? $license['email'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($license['username'] ?? '未命名'); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($license['email'] ?? ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($license['product_name']); ?></div>
                                    <div class="text-xs text-gray-500 font-mono"><?php echo htmlspecialchars(substr($license['license_key'], 0, 12)); ?>...</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClasses[$license['status']]; ?>">
                                        <?php echo $statusLabels[$license['status']]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($stats && $stats['trial_days_left'] !== null): ?>
                                        <?php if ($stats['is_expired_trial']): ?>
                                            <span class="text-red-600 font-medium">已过期</span>
                                        <?php else: ?>
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">
                                                    剩余 <?php echo $stats['trial_days_left']; ?> 天
                                                </span>
                                            </div>
                                            <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-1">
                                                <div class="h-1.5 rounded-full bg-blue-500" 
                                                    style="width: <?php echo min(100, $stats['trial_days_left'] / 14 * 100); ?>%">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($stats && $stats['readiness']): ?>
                                        <div>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $likelihoodColors[$stats['readiness']['conversion_likelihood']]; ?>">
                                                <?php echo $likelihoodLabels[$stats['readiness']['conversion_likelihood']]; ?>
                                            </span>
                                            <div class="flex items-center mt-2">
                                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                    <div class="h-1.5 rounded-full <?php 
                                                        echo $stats['readiness']['conversion_score'] >= 70 ? 'bg-green-500' : 
                                                            ($stats['readiness']['conversion_score'] >= 40 ? 'bg-yellow-500' : 'bg-red-500'); 
                                                    ?>" style="width: <?php echo $stats['readiness']['conversion_score']; ?>%">
                                                    </div>
                                                </div>
                                                <span class="ml-2 text-xs text-gray-500">
                                                    <?php echo $stats['readiness']['conversion_score']; ?>分
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($stats): ?>
                                        <div class="text-sm text-gray-600">
                                            <?php if (!empty($stats['is_converted'])): ?>
                                                <div class="mb-1">
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        ✅ 已转换
                                                    </span>
                                                    <a href="/licenses/view?id=<?php echo $stats['converted_to_license_id']; ?>" 
                                                        class="text-xs text-blue-600 hover:underline ml-1">
                                                        查看正式版 →
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <p>📁 <?php echo $stats['project_count']; ?> 项目</p>
                                            <p>🔌 <?php echo $stats['plugin_count']; ?> 插件</p>
                                            <p>👤 <?php echo $stats['seat_count']; ?> 席位</p>
                                            <p>📊 <?php echo $stats['activity_count']; ?> 次活跃</p>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="/licenses/view?id=<?php echo $license['id']; ?>" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">查看详情</a>
                                    <?php if ($stats && $stats['can_convert']): ?>
                                        <a href="#" 
                                            onclick="quickConvert(<?php echo $license['id']; ?>); return false;"
                                            class="text-purple-600 hover:text-purple-900">快速转化</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    第 <?php echo $page; ?> 页，共 <?php echo $totalPages; ?> 页
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">上一页</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">下一页</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Conversion Tips -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">💡 转化建议</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">高转化可能性客户</h3>
                <p class="text-sm text-gray-600">转化得分 ≥ 70分的客户转化意愿强烈，建议立即联系，提供专属优惠促进转化。</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">中等转化可能性客户</h3>
                <p class="text-sm text-gray-600">转化得分 40-69分的客户需要培养，可发送使用技巧、案例分享等内容。</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">低转化可能性客户</h3>
                <p class="text-sm text-gray-600">转化得分 &lt; 40分的客户活跃度低，可减少跟进频率，重点维护高意向客户。</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Convert Modal -->
<div id="quickConvertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">快速转化为正式版</h3>
        <form method="POST" action="/licenses/convert" class="space-y-4">
            <input type="hidden" name="id" id="quickConvertId" value="">
            
            <div>
                <label for="quick_tier" class="block text-sm font-medium text-gray-700 mb-2">选择版本</label>
                <select 
                    id="quick_tier" 
                    name="tier"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="standard">标准版 - ¥999/年</option>
                    <option value="premium" selected>高级版 - ¥2999/年</option>
                </select>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-700">
                    <strong>✅ 配置保留：</strong>项目、插件、用户席位等所有配置将自动迁移，无需重新配置。
                </p>
            </div>
            
            <div class="flex space-x-4 pt-4">
                <button 
                    type="button"
                    onclick="document.getElementById('quickConvertModal').classList.add('hidden')"
                    class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                >
                    取消
                </button>
                <button 
                    type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg"
                >
                    确认转化
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function quickConvert(id) {
    document.getElementById('quickConvertId').value = id;
    document.getElementById('quickConvertModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
