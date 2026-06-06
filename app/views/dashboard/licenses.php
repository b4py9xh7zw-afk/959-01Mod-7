<?php
$pageTitle = '许可证管理 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            许可证管理
        </h1>
        <a href="/licenses/create" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
            + 创建许可证
        </a>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证密钥</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">产品名称</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">类型</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">过期时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($licenses)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">暂无许可证</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($licenses as $license): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?php echo htmlspecialchars($license['product_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($license['username'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                        echo ($license['type'] ?? 'trial') === 'trial' ? 'bg-yellow-100 text-yellow-800' : 'bg-purple-100 text-purple-800'; 
                                    ?>">
                                        <?php 
                                        echo ($license['type'] ?? 'trial') === 'trial' ? '试用版' : '正式版'; 
                                        ?>
                                    </span>
                                    <?php if (isset($license['tier']) && $license['tier'] !== 'basic'): ?>
                                    <span class="ml-1 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        <?php echo $license['tier'] === 'premium' ? '高级' : '标准'; ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                        echo $license['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                            ($license['status'] === 'expired' ? 'bg-red-100 text-red-800' : 
                                            ($license['status'] === 'converted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')); 
                                    ?>">
                                        <?php 
                                        echo $license['status'] === 'active' ? '活跃' : 
                                            ($license['status'] === 'expired' ? '已过期' : 
                                            ($license['status'] === 'converted' ? '已转换' : '未激活')); 
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php 
                                    if (isset($license['type']) && $license['type'] === 'trial' && !empty($license['trial_ends_at'])) {
                                        echo date('Y-m-d', strtotime($license['trial_ends_at'])) . ' (试用)';
                                    } elseif (!empty($license['expires_at'])) {
                                        echo date('Y-m-d', strtotime($license['expires_at']));
                                    } else {
                                        echo '永不过期';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <?php echo date('Y-m-d', strtotime($license['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="/licenses/view?id=<?php echo $license['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">查看</a>
                                    <?php if (isset($license['type']) && $license['type'] === 'trial' && $license['status'] === 'active'): ?>
                                        <a href="#" onclick="quickConvertFromList(<?php echo $license['id']; ?>); return false;" 
                                            class="text-purple-600 hover:text-purple-900">升级</a>
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
</div>

<!-- Quick Convert Modal -->
<div id="quickConvertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">升级为正式版</h3>
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
                    确认升级
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function quickConvertFromList(id) {
    document.getElementById('quickConvertId').value = id;
    document.getElementById('quickConvertModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
