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
        path: '/ean-sku-search',
        component: () => import('@/Pages/5FEanSkuSearch.vue'),
        name: 'b2b5fEanSkuSearch'
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
    {
        path: '/reset-storage-box',
        component: () => import('@/Pages/ResetStorageBox.vue'),
        name: 'resetStorageBox'
    },

]

export default routes
