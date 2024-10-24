<template>
  <div>
    <wx-banner />

    <div class="min-h-screen bg-gray-100">
      <nav class="bg-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="flex-shrink-0 flex items-center">
                <a href="/">
                  <wx-application-mark class="block h-9 w-auto" />
                </a>
              </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <!--撿料倉 -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        撿料倉

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          熱賣區
                        </div>

                        <wx-dropdown-link
                          href="/picking-area-refill-add-location"
                          :active="
                            currentRoute === 'pickingAreaRefillAddLocation'
                          "
                        >
                          1. 新增需補料儲位
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/picking-area-refill-pick"
                          :active="currentRoute === 'pickingAreaRefillPick'"
                        >
                          2. 預備倉補料作業
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/picking-area-refill-bind-location"
                          :active="
                            currentRoute === 'pickingAreaRefillBindLocation'
                          "
                        >
                          3. 補料儲位綁定
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/picking-area-refill-feed-location"
                          :active="
                            currentRoute === 'pickingAreaRefillFeedLocation'
                          "
                        >
                          4. 雙箱區補料
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/picking-area-refill-processing-list"
                          :active="
                            currentRoute === 'pickingAreaRefillProcessingList'
                          "
                        >
                          5. 在途補料清單
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          罕見區
                        </div>

                        <wx-dropdown-link
                          href="/picking-area-ac-zone"
                          :active="currentRoute === 'pickingAreaACZone'"
                        >
                          罕見品區儲位上架
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/picking-area-refill-ac-mn-location"
                          :active="
                            currentRoute === 'pickingAreaRefillACMNLocation'
                          "
                        >
                          特殊箱補料
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          轉倉紀錄
                        </div>

                        <wx-dropdown-link
                          href="/product-rollover-record"
                          :active="currentRoute === 'productRolloverRecord'"
                        >
                          成品轉倉紀錄
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!-- 貨箱 -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        貨箱

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          入庫
                        </div>

                        <wx-dropdown-link
                          href="/storage-box-input-bind-material"
                          :active="
                            currentRoute === 'storageBoxInputBindMaterial'
                          "
                        >
                          1. 物料綁定 (製造用)
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/storage-box-input-bind-location"
                          :active="
                            currentRoute === 'storageBoxInputBindLocation'
                          "
                        >
                          2. 儲位綁定
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/adjust-inventory?event=transfer_input"
                          :active="currentRoute === 'adjustInventory'"
                        >
                          3. 轉倉入庫
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/adjust-inventory?event=item_return"
                          :active="currentRoute === 'adjustInventory'"
                        >
                          4. 品項復歸
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          出庫
                        </div>

                        <wx-dropdown-link
                          href="/storage-box-reset-location"
                          :active="currentRoute === 'storageBoxResetLocation'"
                        >
                          1. 貨箱出庫
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/adjust-inventory?event=transfer_output"
                          :active="currentRoute === 'adjustInventory'"
                        >
                          2. 轉倉出庫
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          查詢
                        </div>

                        <wx-dropdown-link
                          href="/query-ean-sku"
                          :active="currentRoute === 'queryEanSku'"
                        >
                          EAN / SKU
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/query-storage-box"
                          :active="currentRoute === 'queryStorageBox'"
                        >
                          貨箱
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/view-sku-record"
                          :active="currentRoute === 'viewSkuRecord'"
                        >
                          查詢SKU時間
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/b2c-stock-logs"
                          :active="currentRoute === 'b2cStockLogs'"
                        >
                          查詢總表
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          其他
                        </div>

                        <wx-dropdown-link
                          href="/storage-box-reset"
                          :active="currentRoute === 'storageBoxReset'"
                        >
                          移除綁定
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/adjust-inventory-quantity"
                          :active="currentRoute === 'AdjustInventoryQuantity'"
                        >
                          庫存調整
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/batch-return-inventory-report"
                          :active="
                            currentRoute === 'batchReturnInventoryReport'
                          "
                        >
                          品項批次復歸檔
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/insert-new-storage-box"
                          :active="currentRoute === 'insertNewStorageBox'"
                        >
                          列印貨箱條碼
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!-- 儲位CRUD -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        儲位新增/調整

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <wx-dropdown-link
                          href="/locations-CRUD"
                          :active="currentRoute === 'locationCRUD'"
                        >
                          單筆新增/調整
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/locations-batch-in-out"
                          :active="currentRoute === 'locationBatchInOut'"
                        >
                          批次新增/調整
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!--盤點-->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        盤點

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <wx-dropdown-link
                          href="/first-inventory"
                          :active="currentRoute === 'firstInventory'"
                        >
                          初次盤點
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/check-inventory"
                          :active="currentRoute === 'checkInventory'"
                        >
                          二次盤點
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/output-inventory"
                          :active="currentRoute === 'outputInventory'"
                        >
                          匯出盤點紀錄
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!-- 異常處理 -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        異常處理

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          特殊規則配箱
                        </div>

                        <wx-dropdown-link
                          href="/bind-one-grid-box"
                          :active="currentRoute === 'bindOneGridBox'"
                        >
                          1. 綁定單格箱
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/bind-picking-car"
                          :active="currentRoute === 'bindPickingCar'"
                        >
                          2. 綁定撿料車
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/allocate-B-customized"
                          :active="currentRoute === 'allocateBCustomized'"
                        >
                          3. B類分箱
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/search-allocate-B-customized"
                          :active="currentRoute === 'searchAllocateBCustomized'"
                        >
                          4. 查詢B類分箱
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/reset-allocate-box"
                          :active="currentRoute === 'resetAllocateBox'"
                        >
                          5. 清空分類箱
                        </wx-dropdown-link>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                          其他
                        </div>

                        <wx-dropdown-link
                          href="/view-picking-box"
                          :active="currentRoute === 'viewPickingBox'"
                        >
                          檢視撿料箱
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/view-picking-car"
                          :active="currentRoute === 'viewPickingCar'"
                        >
                          檢視撿料車
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/out-of-stock-items"
                          :active="currentRoute === 'outOfStockItems'"
                        >
                          缺貨品項管理
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!--B2B 倉-->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        B2B 倉

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          入庫
                        </div>
                        <!-- <wx-dropdown-link
                          href="/b2b-input"
                          :active="currentRoute === 'b2bInput'"
                        >
                          製造轉成品倉入庫
                        </wx-dropdown-link> -->

                        <wx-dropdown-link
                          href="/b2b-input-list?transactionDate="
                          :active="currentRoute === 'b2bInputList'"
                        >
                          製造轉成品倉入庫清單
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/b2b-5f-input?inputId=0"
                          :active="currentRoute === 'b2b5fInput'"
                        >
                          五樓入庫用
                        </wx-dropdown-link>

                        <wx-dropdown-link
                          href="/b2b-5f-input-list?manufacturingDate="
                          :active="currentRoute === 'b2b5fInputList'"
                        >
                          五樓入庫清單
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          入庫
                        </div>
                        <wx-dropdown-link
                          href="/b2b-5f-binding-picking-box"
                          :active="currentRoute === 'b2b5fBindingPickingBox'"
                        >
                          B2B綁定撿料箱
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-binding-location"
                          :active="currentRoute === 'b2b5fBindingLocation'"
                        >
                          B2B儲位綁定
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-binding-XB-location"
                          :active="currentRoute === 'b2b5fBindingXBLocation'"
                        >
                          B2B綁定XB儲位
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-item-inventory?event=transfer_input"
                          :active="currentRoute === 'b2b5fItemInventory'"
                        >
                          B2B品項轉入
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          出庫
                        </div>
                        <wx-dropdown-link
                          href="/b2b-5f-item-inventory?event=transfer_output"
                          :active="currentRoute === 'b2b5fItemInventory'"
                        >
                          B2B品項出庫
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          補料
                        </div>
                        <wx-dropdown-link
                          href="/b2b-5f-refill-material"
                          :active="currentRoute === 'b2b5fRefillMaterial'"
                        >
                          貨箱補料
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          盤點
                        </div>
                        <wx-dropdown-link
                          href="/b2b-5f-inventory"
                          :active="currentRoute === 'b2b5fInventory'"
                        >
                          五樓庫存初次盤點
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-double-inventory"
                          :active="currentRoute === 'b2b5fDoubleInventory'"
                        >
                          五樓庫存二次盤點
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          查詢
                        </div>
                        <wx-dropdown-link
                          href="/b2b-5f-ean-sku-search"
                          :active="currentRoute === 'b2b5fEanSkuSearch'"
                        >
                          B2B EAN/SKU 查詢
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-storage-box-location-search"
                          :active="currentRoute === 'b2b5fStorageBoxLocationSearch'"
                        >
                          B2B 貨箱 查詢
                        </wx-dropdown-link>
                         <wx-dropdown-link
                          href="/b2b-5f-location-search"
                          :active="currentRoute === 'b2b5fLocationSearch'"
                        >
                          B2B 儲位 查詢
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-stock-logs"
                          :active="currentRoute === 'b2bStockLogs'"
                        >
                          查詢總表
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/b2b-5f-picked-items-search"
                          :active="currentRoute === 'b2b5fPickedItemsSearch'"
                        >
                          B2B撿料記錄查詢
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="48">
                  <template #trigger>
                    <button
                      v-if="false"
                      class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                    >
                      <img
                        class="h-8 w-8 rounded-full object-cover"
                        :src="user.profile_photo_url"
                        :alt="user.name"
                      />
                    </button>

                    <span v-else class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition"
                      >
                        {{ user.name }}

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <!-- Authentication -->
                    <form @submit.prevent="logout">
                      <wx-dropdown-link as="button"> 登出 </wx-dropdown-link>
                    </form>
                  </template>
                </wx-dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
              <div v-if="false" class="flex-shrink-0 mr-3">
                <img
                  class="h-10 w-10 rounded-full object-cover"
                  :src="user.profile_photo_url"
                  :alt="user.name"
                />
              </div>

              <div>
                <div class="font-medium text-base text-gray-800">
                  {{ user.name }}
                </div>
                <div class="font-medium text-sm text-gray-500">
                  {{ user.email }}
                </div>
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <!-- Authentication -->
              <form method="POST" @submit.prevent="logout">
                <wx-responsive-nav-link as="button">
                  登出
                </wx-responsive-nav-link>
              </form>

              <template>
                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">
                  撿料倉 - AA/AB 區
                </div>

                <wx-responsive-nav-link
                  href="/picking-area-refill-add-location"
                  :active="currentRoute === 'pickingAreaRefillAddLocation'"
                >
                  補料 - 1. 新增需補料儲位
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/picking-area-refill-pick"
                  :active="currentRoute === 'pickingAreaRefillPick'"
                >
                  補料 - 2. 預備倉補料作業
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/picking-area-refill-bind-location"
                  :active="currentRoute === 'pickingAreaRefillBindLocation'"
                >
                  補料 - 3. 補料儲位綁定
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/picking-area-refill-feed-location"
                  :active="currentRoute === 'pickingAreaRefillFeedLocation'"
                >
                  補料 - 4. 雙箱區補料
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/picking-area-refill-processing-list"
                  :active="currentRoute === 'pickingAreaRefillProcessingList'"
                >
                  補料 - 5. 在途補料清單
                </wx-responsive-nav-link>

                <div class="block px-4 py-2 text-xs text-gray-400">
                  撿料倉 - AC 區
                </div>

                <wx-responsive-nav-link
                  href="/picking-area-ac-zone"
                  :active="currentRoute === 'pickingAreaACZone'"
                >
                  上架 - 罕見品區儲位上架
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/picking-area-refill-ac-mn-location"
                  :active="currentRoute === 'pickingAreaRefillACMNLocation'"
                >
                  補料 - 特殊箱補料
                </wx-responsive-nav-link>

                <div class="block px-4 py-2 text-xs text-gray-400">
                  撿料倉 - 轉倉紀錄
                </div>

                <wx-responsive-nav-link
                  href="/product-rollover-record"
                  :active="currentRoute === 'productRolloverRecord'"
                >
                  撿料倉 - 成品轉倉紀錄
                </wx-responsive-nav-link>

                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">貨箱</div>

                <wx-responsive-nav-link
                  href="/storage-box-input-bind-material"
                  :active="currentRoute === 'storageBoxInputBindMaterial'"
                >
                  入庫 - 1. 物料綁定
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/storage-box-input-bind-location"
                  :active="currentRoute === 'storageBoxInputBindLocation'"
                >
                  入庫 - 2. 儲位綁定
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/adjust-inventory?event=transfer_input"
                  :active="currentRoute === 'adjustInventory'"
                >
                  入庫 - 3. 轉倉入庫
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/adjust-inventory?event=item_return"
                  :active="currentRoute === 'adjustInventory'"
                >
                  入庫 - 4. 品項復歸
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/storage-box-reset-location"
                  :active="currentRoute === 'storageBoxResetLocation'"
                >
                  出庫 - 1. 貨箱出庫
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/adjust-inventory?event=transfer_output"
                  :active="currentRoute === 'adjustInventory'"
                >
                  出庫 - 2. 轉倉出庫
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/query-ean-sku"
                  :active="currentRoute === 'queryEanSku'"
                >
                  查詢 - EAN / SKU
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/query-storage-box"
                  :active="currentRoute === 'queryStorageBox'"
                >
                  查詢 - 貨箱
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/view-sku-record"
                  :active="currentRoute === 'viewSkuRecord'"
                >
                  其他 - 查詢SKU時間
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/storage-box-reset"
                  :active="currentRoute === 'storageBoxReset'"
                >
                  其他 - 移除綁定
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/adjust-inventory-quantity"
                  :active="currentRoute === 'AdjustInventoryQuantity'"
                >
                  其他 - 庫存調整
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/batch-return-inventory-report"
                  :active="currentRoute === 'batchReturnInventoryReport'"
                >
                  其他 - 品項批次復歸檔
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/insert-new-storage-box"
                  :active="currentRoute === 'insertNewStorageBox'"
                >
                  其他 - 列印貨箱條碼
                </wx-responsive-nav-link>

                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">
                  儲位新增調整
                </div>

                <wx-responsive-nav-link
                  href="/locations-CRUD"
                  :active="currentRoute === 'locationCRUD'"
                >
                  儲位 - 單筆新增/調整
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/locations-batch-in-out"
                  :active="currentRoute === 'locationBatchInOut'"
                >
                  儲位 - 批次新增/調整
                </wx-responsive-nav-link>

                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">盤點</div>

                <wx-responsive-nav-link
                  href="/first-inventory"
                  :active="currentRoute === 'firstInventory'"
                >
                  盤點 - 初次盤點
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/check-inventory"
                  :active="currentRoute === 'checkInventory'"
                >
                  盤點 - 二次盤點
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/output-inventory"
                  :active="currentRoute === 'outputInventory'"
                >
                  盤點 - 匯出盤點紀錄
                </wx-responsive-nav-link>

                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">
                  異常處理
                </div>

                <wx-responsive-nav-link
                  href="/bind-one-grid-box"
                  :active="currentRoute === 'bindOneGridBox'"
                >
                  配箱 - 1. 綁定單格箱
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/bind-picking-car"
                  :active="currentRoute === 'bindPickingCar'"
                >
                  配箱 - 2. 綁定撿料車
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/allocate-B-customized"
                  :active="currentRoute === 'allocateBCustomized'"
                >
                  配箱 - 3. B類分箱
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/search-allocate-B-customized"
                  :active="currentRoute === 'searchAllocateBCustomized'"
                >
                  配箱 - 4. 查詢B類分箱
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/reset-allocate-box"
                  :active="currentRoute === 'resetAllocateBox'"
                >
                  配箱 - 5. 清空分類箱
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/view-picking-box"
                  :active="currentRoute === 'viewPickigBox'"
                >
                  其他 - 檢視撿料箱
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/view-picking-car"
                  :active="currentRoute === 'viewPickigCar'"
                >
                  其他 - 檢視撿料車
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/out-of-stock-items"
                  :active="currentRoute === 'out-of-stock-items'"
                >
                  其他 - 缺貨品項管理
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/end-of-life-items"
                  :active="currentRoute === 'endOfLifeItems'"
                >
                  其他 - 缺貨品項管理
                </wx-responsive-nav-link>

                <div class="block px-4 py-2 text-xs text-gray-400">B2B 倉</div>

                <!-- <wx-responsive-nav-link
                  href="/b2b-input"
                  :active="currentRoute === 'b2bInput'"
                >
                  製造轉成品倉入庫
                </wx-responsive-nav-link-->

                <wx-responsive-nav-link
                  href="/b2b-input-list?transactionDate="
                  :active="currentRoute === 'b2bInputList'"
                >
                  製造轉成品倉入庫
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/b2b-5f-input?inputId=0"
                  :active="currentRoute === 'b2b5fInput'"
                >
                  B2B - 五樓入庫用
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/b2b-5f-binding-picking-box"
                  :active="currentRoute === 'b2b5fBindingPickingBox'"
                >
                  B2B - 綁定撿料箱
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-binding-location"
                  :active="currentRoute === 'b2b5fBindingLocation'"
                >
                  B2B - 綁定儲位
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-binding-XB-location"
                  :active="currentRoute === 'b2b5fBindingXBLocation'"
                >
                  B2B - 綁定XB儲位
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-refill-material"
                  :active="currentRoute === 'b2b5fRefillMaterial'"
                >
                  B2B - 貨箱補料
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/b2b-5f-input-list?manufacturingDate="
                  :active="currentRoute === 'b2b5fInputList'"
                >
                  B2B - 五樓入庫清單
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-item-inventory?event=transfer_input"
                  :active="currentRoute === 'b2b5fItemInventory'"
                >
                  B2B - 品項入庫
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-item-inventory?event=transfer_output"
                  :active="currentRoute === 'b2b5fItemInventory'"
                >
                  B2B - 品項出庫
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-inventory"
                  :active="currentRoute === 'b2b5fInventory'"
                >
                  B2B - 五樓庫存初次盤點
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-double-inventory"
                  :active="currentRoute === 'b2b5fDoubleInventory'"
                >
                  B2B - 五樓庫存二次盤點
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-ean-sku-search"
                  :active="currentRoute === 'b2b5fEanSkuSearch'"
                >
                  B2B - EAN/SKU 查詢
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-storage-box-location-search"
                  :active="currentRoute === 'b2b5fStorageBoxLocationSearch'"
                >
                  B2B - 貨箱 查詢
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-location-search"
                  :active="currentRoute === 'b2b5fLocationSearch'"
                >
                  B2B - 儲位 查詢
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-stock-logs"
                  :active="currentRoute === 'b2bStockLogs'"
                >
                  B2B - 查詢總表
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/b2b-5f-picked-items-search"
                  :active="currentRoute === 'b2b5fPickedItemsSearch'"
                >
                  B2B - 撿料記錄查詢
                </wx-responsive-nav-link>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header"></slot>
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script>
import WxApplicationMark from "@/Components/ApplicationMark";
import WxBanner from "@/Components/Banner";
import WxDropdown from "@/Components/Dropdown";
import WxDropdownLink from "@/Components/DropdownLink";
import WxNavLink from "@/Components/NavLink";
import WxResponsiveNavLink from "@/Components/ResponsiveNavLink";

export default {
  components: {
    WxApplicationMark,
    WxBanner,
    WxDropdown,
    WxDropdownLink,
    WxNavLink,
    WxResponsiveNavLink,
  },

  data() {
    return {
      showingNavigationDropdown: false,
      user: null,
    };
  },

  created() {
    if (window.WMS.user) {
      this.user = window.WMS.user;
    }
  },

  computed: {
    currentRoute() {
      return this.$route.name;
    },
  },

  methods: {
    async logout(e) {
      e.preventDefault();
      try {
        await this.axios.get("/sanctum/csrf-cookie");
        await this.axios.post("/api/logout");

        window.location.href = "/login";
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>
