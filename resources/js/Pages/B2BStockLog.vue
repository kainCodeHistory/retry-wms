<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        查詢 - B2B總表
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <wx-validation-messages :display="validation.display" :errors="validation.errors" :title="validation.title" />

            <div class="grid md:grid-cols-2 sm:grid-cols-1 gap-2">
              <div class="mt-4">
                <wx-label for="eanSku">
                  <template><sup class="text-red-500 font-bold">*</sup>SKU</template>
                </wx-label>
                <wx-input id="eanSku" class="block mt-1 w-full" type="text" :value="eanSku" required autofocus
                  ref="eanSku" @update="updateEanSku"  @keyup="handleKeyUp" />
                <ul v-if="searchSku.length && searchSku[0] !== eanSku"
                  class="w-48 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white z-10"
                  style="position:absolute">
                  <li v-for="sku in searchSku" :key="sku"
                    class="w-full px-4 py-2 border-b border-gray-200 rounded-t-lg dark:border-gray-600 hover:bg-gray-100 cursor-pointer"
                    @click="selectSku(sku)">
                    {{ sku }}
                  </li>
                </ul>
              </div>

              <div class="mt-4">
                <wx-label for="date">
                  <template>日期</template>
                </wx-label>
                <wx-input class="block mt-1 w-full" type="date" :value="date" @change="updateDate" />
              </div>

            </div>
            <div class="mt-2">
              <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                <div class="block min-w-full shadow rounded-lg max-h-96 overflow-y-auto" ref="table"
                  @scroll="handleScroll">
                  <table class="min-w-full leading-normal">
                    <thead>
                      <tr class="sticky top-0 text-center">
                        <!-- <wx-table-header class="text-center" value="ID" /> -->
                        <wx-table-header class="text-center" value="Sku" />
                        <wx-table-header class="text-center" value="異動數量" />
                        <wx-table-header class="text-center" value="總數" />
                        <wx-table-header class="text-center" value="事件" />
                        <wx-table-header class="text-center" value="箱號" />
                        <wx-table-header class="text-center" value="備註" />
                        <wx-table-header class="text-center" value="紀錄日期" />
                        <wx-table-header class="text-center" value="操作人員" />
                      </tr>
                    </thead>
                    <tbody class="max-h-96">
                      <tr v-for="skuLog in skuLogs" :key="skuLog.id">
                        <!-- <wx-table-cell :value="skuLog.id"></wx-table-cell> -->
                        <wx-table-cell :value="skuLog.sku"></wx-table-cell>
                        <wx-table-cell :value="skuLog.quantity"></wx-table-cell>
                        <wx-table-cell :value="skuLog.balance"></wx-table-cell>
                        <wx-table-cell :value="skuLog.stock_log_events[skuLog.event] || skuLog.event"></wx-table-cell>
                        <wx-table-cell :value="skuLog.event !== 'item_pick' ? skuLog.event_key : ''"></wx-table-cell>
                        <wx-table-cell :value="skuLog.note"></wx-table-cell>
                        <wx-table-cell :value="skuLog.working_day"></wx-table-cell>
                        <wx-table-cell :value="skuLog.user_name"></wx-table-cell>
                      </tr>
                    </tbody>
                  </table>

                </div>
                <table class="min-w-full leading-normal">
                  <tbody>
                    <tr>
                      <wx-table-cell ref="loadingRow" colspan="5" class="text-center">
                        <div :class="footerMsg">
                          {{ msg }}
                        </div>
                      </wx-table-cell>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <wx-spinner :display="loader" />
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout"

import WxButton from "@/Components/Button"
import WxInput from "@/Components/Input"
import WxLabel from "@/Components/Label"
import WxSpinner from "@/Components/Spinner"
import WxTableCell from "@/Components/TableCell"
import WxTableHeader from "@/Components/TableHeader"
import WxValidationMessages from "@/Components/ValidationMessages"
import KeyboardListener from "@/Mixins/KeyboardListenerMixin"
import WxCommon from "@/Mixins/Common"

export default {
  name: "B2BStockLog",

  mixins: [WxCommon, KeyboardListener],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
  },

  data() {
    return {
      eanSku: undefined,
      sku: "",
      skuLogs: [],
      skuList: [],
      date: new Date().toISOString().split('T')[0],
      tableLoading: false,
      paginator: {
        page: 1,
        total: 0
      },
      originalHeight: 0
    }
  },

  computed: {
    searchSku() {
      if (this.eanSku?.length === 0) {
        return []
      }
      let matches = 0
      return this.skuList.filter(sku => {
        if (sku.includes(this.eanSku?.toUpperCase()) && matches < 15) {
          matches++
          return sku
        }
      })
    },

    rowCount() {
      return this.paginator.total
    },

    footerMsg() {
      return this.tableLoading === true ?
        "px-3 py-1 text-xs font-medium leading-none text-center text-blue-800 rounded-full animate-pulse dark:bg-blue-900 dark:text-blue-200" :
        ""
    },

    msg() {
      return this.tableLoading === true ?
        "loading..." : `共 ${this.skuLogs.length}/${this.rowCount} 筆`
    }
  },

  async mounted() {
    this.originalHeight = this.$refs.table.scrollHeight
    this.addKeyupListener()
    this.$on('keyboard-input', this.handleSkuSearch)
    await this.getSkuList()
  },

  methods: {
    async getSkuList() {
      try {
        this.loader = true
        await this.axios.get("/sanctum/csrf-cookie")
        const result = await this.axios.get(
          "/api/b2b/stock/sku-list"
        )
        this.skuList = result.data.data
        console.log(result)
      } catch (e) {
        this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。")
      } finally {
        this.loader = false
      }
    },
    handleKeyUp(event) {
      if (event.key === 'Enter') {
        this.handleSkuSearch();
          }
        },
    async handleSkuSearch() {
      this.skuLogs = [];
      if (this.eanSku) {
         this.loader = true;
         await this.getSkuLog();
      }
        },



    async getSkuLog() {
      try {
        if (this.eanSku?.trim().length > 0) {
          await this.axios.get("/sanctum/csrf-cookie")
          const params = {
            start_date: this.date,
            page: this.paginator.page,
            perPage: 15
          }
          console.log(params)
          const result = await this.axios.get(
            `/api/b2b/stock-logs/${this.eanSku.trim().toUpperCase()}`,
            { params }
          )
          const data = result.data.data
          if (data.length > 0) {
            this.paginator.page = result.data.paginator.page
            this.paginator.total = result.data.paginator.total
            this.skuLogs = [
              ...this.skuLogs,
              ...data
            ]
            console.log('array appended')
            this.sku = data[0].sku
          }
        }
      } catch (error) {
        this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。")
        this.reset()

      } finally {
        this.loader = false
        this.tableLoading = false
      }

    },

    selectSku(sku) {
      this.eanSku = sku
      this.$refs.eanSku.focus()
    },

    updateEanSku(sku) {
      this.eanSku = sku
    },

    updateDate(date) {
      this.date = date
      if (this.eanSku) {
        this.skuLogs = []
        this.paginator.set('page', 1)
        this.loader = true
        return this.getSkuLog()
      }
    },

    reset() {
      this.skuLogs = []
      this.productTitle = ""
      this.sku = ""
      this.eanSku = ""
    },

    handleScroll(e) {
      const { target } = e
      if (
        target.scrollHeight > this.originalHeight &&
        Math.ceil(target.scrollTop) >= target.scrollHeight - target.offsetHeight
      ) {
        this.tableLoading = true
        this.incrementPage()
        return this.getSkuLog()
      }
    },

    incrementPage() {
      this.paginator.page = +this.paginator.page + 1
    }
  },

  beforeDestroy() {
    this.removeKeyupListener()
  }

}
</script>
