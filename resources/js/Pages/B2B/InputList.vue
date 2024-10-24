<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        製造轉成品倉入庫清單
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <wx-validation-messages
              :display="validation.display"
              :errors="validation.errors"
              :title="validation.title"
            />

            <div>
              <div class="mt-4">
                <wx-label for="manufacturingDate"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>
                    製造日期</template
                  ></wx-label
                >
                <wx-input
                  id="manufacturingDate"
                  class="block mt-1 w-full"
                  type="date"
                  :value="transactionDate"
                  @update="(val) => (transactionDate = val)"
                  required
                  autofocus
                  @change="inputChanged"
                  ref="transactionDate"
                />
              </div>

              <div class="mt-2">
                <wx-label for="eanSku"
                  ><template> SKU / EAN</template></wx-label
                >
                <wx-input
                  id="eanSku"
                  class="block mt-1 w-full"
                  type="text"
                  :value="eanSku"
                  @update="(val) => (eanSku = val)"
                  autofocus
                  @keypress="inputKeypress"
                />
              </div>

              <div class="mt-2 flex items-center justify-end">
                <wx-button class="ml-3" color="blue" @click="exportExcel"
                  >匯出</wx-button
                >
              </div>

              <div class="mt-2">
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-auto">
                  <div
                    class="inline-block min-w-full shadow rounded-lg"
                    style="height: 800px"
                  >
                    <table
                      class="min-w-full leading-normal"
                      style="width: 960px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="紙箱條碼"></wx-table-header>
                          <wx-table-header value="SKU"></wx-table-header>
                          <wx-table-header value="EAN"></wx-table-header>
                          <wx-table-header value="數量"></wx-table-header>
                          <wx-table-header value="品名"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in inputs" :key="item.id">
                          <wx-table-cell :value="item.event_key"></wx-table-cell>
                          <wx-table-cell
                            :value="item.sku"
                          ></wx-table-cell>
                          <wx-table-cell :value="item.ean"></wx-table-cell>
                          <wx-table-cell :value="item.quantity"></wx-table-cell>
                          <wx-table-cell
                            :value="item.display_name"
                          ></wx-table-cell>
                        </tr>
                        <tr v-if="inputs.length === 0">
                          <td colspan="6">
                            <p class="text-center text-md mt-4 mb-4">
                              {{ transactionDate }} 無入庫資料。
                            </p>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div
                class="flex flex-col items-center"
                v-if="paginator.total > 0"
              >
                <div class="inline-flex mt-2 xs:mt-0">
                  <!-- Buttons -->
                  <button
                    class="inline-flex items-center py-2 px-4 text-sm font-medium bg-gray-800 rounded-l border-0 border-r border-gray-700 hover:bg-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                    :class="{
                      'text-white': paginator.page > 1,
                      'text-gray-600': paginator.page === 1,
                      'cursor-not-allowed': paginator.page === 1,
                      'cursor-pointer': paginator.page > 1,
                    }"
                    :disabled="paginator.page === 1"
                    @click="previousPage"
                  >
                    <svg
                      aria-hidden="true"
                      class="mr-2 w-5 h-5"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                    上一頁
                  </button>
                  <div
                    class="inline-flex items-center py-2 px-4 text-sm font-medium text-white bg-gray-800 hover:bg-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400"
                  >
                    {{ paginator.page }} of {{ paginator.lastPage }}
                  </div>
                  <button
                    class="inline-flex items-center py-2 px-4 text-sm font-medium text-white bg-gray-800 rounded-r border-0 border-l border-gray-700 hover:bg-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                    :class="{
                      'text-white': paginator.page < paginator.lastPage,
                      'text-gray-600': paginator.page >= paginator.lastPage,
                      'cursor-not-allowed':
                        paginator.page >= paginator.lastPage,
                      'cursor-pointer': paginator.page < paginator.lastPage,
                    }"
                    :disabled="paginator.page === paginator.lastPage"
                    @click="nextPage"
                  >
                    下一頁
                    <svg
                      aria-hidden="true"
                      class="ml-2 w-5 h-5"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </button>
                </div>
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
import AppLayout from "@/Layouts/AppLayout";

import WxButton from "@/Components/Button";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxSpinner from "@/Components/Spinner";
import WxValidationMessages from "@/Components/ValidationMessages";

import WxCommon from "@/Mixins/Common";

export default {
  name: "storageBoxInputBindMaterial",

  mixins: [WxCommon],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxTableCell,
    WxTableHeader,
    WxSpinner,
    WxValidationMessages,
  },

  data() {
    return {
      paginator: {
        page: 1,
        perPage: 50,
        lastPage: 0,
        total: 0,
      },
      eanSku: "",
      inputs: [],
      transactionDate: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  async mounted() {
    this.transactionDate = this.$route.query.transactionDate;
    if (this.transactionDate === "") {
      this.transactionDate = new Date().toISOString().slice(0, 10);
    }

    await this.getList(this.transactionDate, this.eanSku, true);
  },

  methods: {
    async inputChanged() {
      await this.getList(this.transactionDate, this.eanSku, true);
    },

    async inputKeypress(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        await this.getList(this.transactionDate, e.target.value.trim(), true);
      }
    },

    async previousPage() {
      this.paginator.page = parseInt(this.paginator.page) - 1;
      await this.getList(this.transactionDate, this.eanSku, false);
    },

    async nextPage() {
      this.paginator.page = parseInt(this.paginator.page) + 1;
      await this.getList(this.transactionDate, this.eanSku, false);
    },

    async getList(transactionDate, eanSku, flag) {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.get("/api/b2b/input-list", {
          params: {
            transactionDate,
            eanSku,
            page: flag ? 1 : this.paginator.page,
            perPage: flag ? 50 : this.paginator.perPage,
            lastPage: flag ? 0 : this.paginator.lastPage,
            total: flag ? 0 : this.paginator.total,
          },
        });

        if (result.data.inputs.length > 0) {
          this.inputs = result.data.inputs;
          this.paginator.page = result.data.paginator.page;
          this.paginator.perPage = result.data.paginator.perPage;
          this.paginator.lastPage = result.data.paginator.lastPage;
          this.paginator.total = result.data.paginator.total;
        } else {
          this.inputs = [];
          this.paginator.page = 1;
          this.paginator.perPage = 50;
          this.paginator.lastPage = 0;
          this.paginator.total = 0;
        }

        this.eanSku = "";

        this.loader = false;
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
        this.loader = false;
      }
    },

    async deleteInput(inputId) {
      if (confirm("是否要刪除此筆紀錄?")) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          await this.axios.delete(`/api/b2b/input/${inputId}`);

          await this.getList(this.transactionDate, this.eanSku, false);
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );

          this.loader = false;
        }
      }
    },

    async exportExcel() {
      try {
        window.open(`/api/b2b/export/${this.transactionDate}`);
        this.displayValidation(true, [], "匯出成功");
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
        this.loader = false;
      }
    },
  },
};
</script>
