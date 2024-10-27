const routes = [
    {
        path: '/',
        redirect: '/binding-picking-box'
    },
    {
        path: '/login',
        component: () => import('@/Pages/Login.vue'),
        name: 'login'
    },
    {
        path: '/inventory',
        component: () => import('@/Pages/5FInventory.vue'),
        name: 'b2b5fInventory'
    },
    {
        path: '/double-inventory',
        component: () => import('@/Pages/5FDoubleInventory.vue'),
        name: 'b2b5fDoubleInventory'
    },
    {
        path: '/binding-picking-box',
        component: () => import('@/Pages/StorageBox/Input/5FBindingPickingBox.vue'),
        name: 'b2b5fBindingPickingBox'
    },
    {
        path: '/binding-location',
        component: () => import('@/Pages/StorageBox/Input/5FBindingLocation.vue'),
        name: 'b2b5fBindingLocation'
    },
    {
        path: '/binding-XB-location',
        component: () => import('@/Pages/StorageBox/Input/5FBindingXBStorageBox.vue'),
        name: 'b2b5fBindingXBLocation'
    },
    {
        path: '/refill-material',
        component: () => import('@/Pages/Refill/5FRefillMaterial.vue'),
        name: 'b2b5fRefillMaterial'
    },
    {
        path: '/ean-sku-search',
        component: () => import('@/Pages/5FEanSkuSearch.vue'),
        name: 'b2b5fEanSkuSearch'
    },
    {
        path: '/storage-box-location-search',
        component: () => import('@/Pages/5FStorageBoxLocationSearch.vue'),
        name: 'b2b5fStorageBoxLocationSearch'
    },
    {
        path: '/location-search',
        component: () => import('@/Pages/5FLocationSearch.vue'),
        name: 'b2b5fLocationSearch'
    },
    {
        path: '/stock-logs',
        component: () => import('@/Pages/B2BStockLog.vue'),
        name: 'b2bStockLogs'
    },
    {
        path: '/item-inventory',
        component: () => import('@/Pages/5FItemInventory.vue'),
        name: 'b2b5fItemInventory'
    },

]

export default routes
