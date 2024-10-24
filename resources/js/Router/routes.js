const routes = [
    {
        path: '/',
        redirect: '/storage-box-input-bind-material'
    },
    {
        path: '/login',
        component: () => import('@/Pages/Login.vue'),
        name: 'login'
    },
    /** 貨箱 */
    {
        path: '/storage-box-input-bind-material',
        component: () => import('@/Pages/StorageBox/Input/BindMaterial.vue'),
        name: 'storageBoxInputBindMaterial'
    },
    {
        path: '/storage-box-input-bind-location',
        component: () => import('@/Pages/StorageBox/Input/BindLocation.vue'),
        name: 'storageBoxInputBindLocation'
    },
    {
        path: '/storage-box-reset',
        component: () => import('@/Pages/StorageBox/Reset.vue'),
        name: 'storageBoxReset'
    },
    {
        path: '/storage-box-reset-location',
        component: () => import('@/Pages/StorageBox/ResetLocation.vue'),
        name: 'storageBoxResetLocation'
    },
    {
        path: '/query-ean-sku',
        component: () => import('@/Pages/StorageBox/Query/EanSku.vue'),
        name: 'queryEanSku'
    },
    {
        path: '/query-storage-box',
        component: () => import('@/Pages/StorageBox/Query/StorageBox.vue'),
        name: 'queryStorageBox'
    },
    {
        path: '/adjust-inventory',
        component: () => import('@/Pages/StorageBox/AdjustInventory.vue'),
        name: 'AdjustInventory'
    },
    {
        path: '/adjust-inventory-quantity',
        component: () => import('@/Pages/StorageBox/AdjustInventoryQuantity.vue'),
        name: 'AdjustInventoryQuantity'
    },
    {
        path: '/batch-return-inventory-report',
        component: () => import('@/Pages/StorageBox/BatchReturnInventoryReport.vue'),
        name: 'batchReturnInventoryReport'
    },
    {
        path: '/insert-new-storage-box',
        component: () => import('@/Pages/StorageBox/PrintNewStorageBox.vue'),
        name: 'insertNewStorageBox'
    },
    {
        path: '/b2c-stock-logs',
        component: () => import('@/Pages/StorageBox/Query/B2CStockLog.vue'),
        name: 'b2cStockLogs'
    },
    /**查詢SKU綁定時間 */
    {
        path: '/view-sku-record',
        component: () => import('@/Pages/StorageBox/Query/ViewSkuRecord.vue'),
        name: 'viewSkuRecord'
    },
    /**撿料倉 */
    {
        path: '/picking-area-refill-add-location',
        component: () => import('@/Pages/PickingArea/Refill/AddLocation.vue'),
        name: 'pickingAreaRefillAddLocation'
    },
    {
        path: '/picking-area-refill-pick',
        component: () => import('@/Pages/PickingArea/Refill/Pick.vue'),
        name: 'pickingAreaRefillPick'
    },
    {
        path: '/picking-area-refill-bind-location',
        component: () => import('@/Pages/PickingArea/Refill/BindLocation.vue'),
        name: 'pickingAreaRefillBindLocation'
    },
    {
        path: '/picking-area-refill-feed-location',
        component: () => import('@/Pages/PickingArea/Refill/FeedLocation.vue'),
        name: 'pickingAreaRefillFeedLocation'
    },
    {
        path: '/picking-area-refill-processing-list',
        component: () => import('@/Pages/PickingArea/Refill/CheckLocation.vue'),
        name: 'pickingAreaRefillProcessingList'
    },
    {
        path: '/picking-area-ac-zone',
        component: () => import('@/Pages/PickingArea/ACZone.vue'),
        name: 'pickingAreaACZone'
    },
    {
        path: '/picking-area-refill-ac-mn-location',
        component: () => import('@/Pages/PickingArea/Refill/MNACLocation.vue'),
        name: 'pickingAreaRefillACMNLocation'
    },
    {
        path: '/product-rollover-record',
        component: () => import('@/Pages/PickingArea/RolloverRecord.vue'),
        name: 'productRolloverRecord'
    },

    /** Shipping Server 相關 */
    {
        path: '/view-picking-box',
        component: () => import('@/Pages/ShippingServer/ViewPickingBox.vue'),
        name: 'viewPickingBox'
    },
    {
        path: '/view-picking-car',
        component: () => import('@/Pages/ShippingServer/ViewPickingCar.vue'),
        name: 'viewPickingCar'
    },
    {
        path: '/bind-one-grid-box',
        component: () => import('@/Pages/ShippingServer/AllocateBatch/BindOneGridBox.vue'),
        name: 'bindOneGridBox'
    },
    {
        path: '/bind-picking-car',
        component: () => import('@/Pages/ShippingServer/AllocateBatch/BindPickingCar.vue'),
        name: 'bindPickingCar'
    },
    {
        path: '/allocate-B-customized',
        component: () => import('@/Pages/ShippingServer/AllocateBatch/AllocateBCustomized.vue'),
        name: 'allocateBCustomized'
    },
    {
        path: '/search-allocate-B-customized',
        component: () => import('@/Pages/ShippingServer/AllocateBatch/SearchAllocateBCustomized.vue'),
        name: 'searchAllocateBCustomized'
    },
    {
        path: '/reset-allocate-box',
        component: () => import('@/Pages/ShippingServer/AllocateBatch/ResetAllocateBox.vue'),
        name: 'resetAllocateBox'
    },
    {
        path: '/out-of-stock-items',
        component: () => import('@/Pages/ShippingServer/OutOfStock/Item.vue'),
        name: 'outOfStockItems'
    },

    /** 儲位設定 */
    {
        path: '/locations-CRUD',
        component: () => import('@/Pages/LocationCRUD/LocationCRUD.vue'),
        name: 'locationCRUD'
    },
    {
        path: '/locations-batch-in-out',
        component: () => import('@/Pages/LocationCRUD/LocationBatchInOut.vue'),
        name: 'locationBatchInOut'
    },
    /** 盤點 */
    {
        path: '/first-inventory',
        component: () => import('@/Pages/Inventory/TakeFirstInventory.vue'),
        name: 'TakeFirstInventory'
    },
    {
        path: '/check-inventory',
        component: () => import('@/Pages/Inventory/TakeCheckInventory.vue'),
        name: 'TakeCheckInventory'
    },
    {
        path: '/output-inventory',
        component: () => import('@/Pages/Inventory/OutputInventory.vue'),
        name: 'OutputInventory'
    },
    /** B2B */
    {
        path: '/b2b-input',
        component: () => import('@/Pages/B2B/Input.vue'),
        name: 'b2bInput'
    },

    {
        path: '/b2b-input-list',
        component: () => import('@/Pages/B2B/InputList.vue'),
        name: 'b2bInputList'
    },

    {
        path: '/b2b-5f-input',
        component: () => import('@/Pages/B2B/5FInput.vue'),
        name: 'b2b5fInput'
    },

    {
        path: '/b2b-5f-input-list',
        component: () => import('@/Pages/B2B/5FInputList.vue'),
        name: 'b2b5fInputList'
    },
    {
        path: '/b2b-5f-inventory',
        component: () => import('@/Pages/B2B/5FInventory.vue'),
        name: 'b2b5fInventory'
    },
    {
        path: '/b2b-5f-double-inventory',
        component: () => import('@/Pages/B2B/5FDoubleInventory.vue'),
        name: 'b2b5fDoubleInventory'
    },
    {
        path: '/b2b-5f-binding-picking-box',
        component: () => import('@/Pages/B2B/StorageBox/Input/5FBindingPickingBox.vue'),
        name: 'b2b5fBindingPickingBox'
    },
    {
        path: '/b2b-5f-binding-location',
        component: () => import('@/Pages/B2B/StorageBox/Input/5FBindingLocation.vue'),
        name: 'b2b5fBindingLocation'
    },
    {
        path: '/b2b-5f-binding-XB-location',
        component: () => import('@/Pages/B2B/StorageBox/Input/5FBindingXBStorageBox.vue'),
        name: 'b2b5fBindingXBLocation'
    },
    {
        path: '/b2b-5f-refill-material',
        component: () => import('@/Pages/B2B/Refill/5FRefillMaterial.vue'),
        name: 'b2b5fRefillMaterial'
    },
    {
        path: '/b2b-5f-picked-items-search',
        component: () => import('@/Pages/B2B/PickedItemsSearch.vue'),
        name: 'b2b5fPickedItemsSearch'
    },
    {
        path: '/b2b-5f-ean-sku-search',
        component: () => import('@/Pages/B2B/5FEanSkuSearch.vue'),
        name: 'b2b5fEanSkuSearch'
    },
    {
        path: '/b2b-5f-storage-box-location-search',
        component: () => import('@/Pages/B2B/5FStorageBoxLocationSearch.vue'),
        name: 'b2b5fStorageBoxLocationSearch'
    },
    {
        path: '/b2b-5f-location-search',
        component: () => import('@/Pages/B2B/5FLocationSearch.vue'),
        name: 'b2b5fLocationSearch'
    },
    {
        path: '/b2b-stock-logs',
        component: () => import('@/Pages/B2B/B2BStockLog.vue'),
        name: 'b2bStockLogs'
    },
    {
        path: '/b2b-5f-item-inventory',
        component: () => import('@/Pages/B2B/5FItemInventory.vue'),
        name: 'b2b5fItemInventory'
    },

    //b2b撿料
    {
        path: '/b2b-picking-login',
        component: () => import('@/Pages/B2B/PickingLogin.vue'),
        name: 'b2bPickingLogin',
        meta: {requiresAuth:false}
    },
    {
        path: '/b2b-picking-order-lists',
        component: () => import('@/Pages/B2B/PickingOrderLists.vue'),
        name: 'b2bPickingOrderLists',
        meta: {requiresAuth:false}
    },
    {
        path: '/b2b-picking-items',
        component: () => import('@/Pages/B2B/PickingItems.vue'),
        name: 'b2bPickingItems',
        meta: {requiresAuth:false}
    },
]

export default routes
