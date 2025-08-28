<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    tableStats: {
        type: Array,
        required: true,
        default: () => []
    },
    summary: {
        type: Object,
        required: true,
        default: () => ({})
    },
    health: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const getRowClass = (status) => {
    switch (status) {
        case 'total':
            return 'bg-blue-50 font-semibold';
        case 'error':
            return 'bg-red-50';
        default:
            return 'hover:bg-gray-50';
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'error':
            return 'bg-red-100 text-red-800';
        case 'total':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'active':
            return 'Active';
        case 'error':
            return 'Error';
        case 'total':
            return 'Total';
        default:
            return 'Unknown';
    }
};

const getHealthStatusClass = (status) => {
    switch (status) {
        case 'excellent':
            return 'bg-green-100 text-green-800';
        case 'good':
            return 'bg-blue-100 text-blue-800';
        case 'fair':
            return 'bg-yellow-100 text-yellow-800';
        case 'poor':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getHealthStatusText = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getHealthBarClass = (status) => {
    switch (status) {
        case 'excellent':
            return 'bg-green-500';
        case 'good':
            return 'bg-blue-500';
        case 'fair':
            return 'bg-yellow-500';
        case 'poor':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
};

const formatNumber = (number) => {
    return new Intl.NumberFormat().format(number);
};

const getLargestTableDisplay = () => {
    if (!props.summary?.largest_table) {
        return 'N/A';
    }
    
    const largest = props.summary.largest_table;
    if (largest.name && largest.count) {
        return `${largest.name} (${largest.count})`;
    }
    
    return 'N/A';
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Health Status Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Database Health</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Overall Health</span>
                                    <span class="text-sm font-medium text-gray-900">{{ health?.percentage || 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div :class="getHealthBarClass(health?.status || 'unknown')" 
                                         class="h-2 rounded-full transition-all duration-300"
                                         :style="{ width: (health?.percentage || 0) + '%' }"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span :class="getHealthStatusClass(health?.status || 'unknown')" 
                                      class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
                                    {{ getHealthStatusText(health?.status || 'unknown') }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-4 text-sm text-gray-600">
                            <div>Active Tables: {{ health?.active_count || 0 }}</div>
                            <div>Error Tables: {{ health?.error_count || 0 }}</div>
                            <div>Total Tables: {{ health?.total_count || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Database Statistics Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Database Statistics</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-6 py-3 border-b border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Table Name
                                        </th>
                                        <th class="px-6 py-3 border-b border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th class="px-6 py-3 border-b border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Record Count
                                        </th>
                                        <th class="px-6 py-3 border-b border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-for="stat in tableStats" :key="stat.table_name" 
                                        :class="getRowClass(stat.status)">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ stat.table || 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ stat.description || 'No description available' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-mono">{{ stat.count || '0' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(stat.status)" 
                                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ getStatusText(stat.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="text-sm font-medium text-blue-800">Total Records</h4>
                        <p class="text-3xl font-bold text-blue-900">
                            {{ formatNumber(summary?.total_records || 0) }}
                        </p>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h4 class="text-sm font-medium text-green-800">Active Tables</h4>
                        <p class="text-3xl font-bold text-green-900">
                            {{ summary?.active_tables || 0 }}
                        </p>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h4 class="text-sm font-medium text-yellow-800">Largest Table</h4>
                        <p class="text-lg font-semibold text-yellow-900">
                            {{ getLargestTableDisplay() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
