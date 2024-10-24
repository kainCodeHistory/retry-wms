<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        B2B - 查詢SKU扣帳紀錄
      </h4>
      <wx-button
          v-if="records.length > 0"
          class="ml-3 float-right"
          color="primary"
          @click="exportLogs"
          type="button"
          >匯出</wx-button
        >
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
                <wx-label for="startDate"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>
                    起始日期</template
                  ></wx-label
                >

                <wx-input
                  id="startDate"
                  class="block mt-1 w-full"
                  type="date"
                  :value="startDate"
                  @update="(val) => (startDate = val)"
                  required
                  autofocus
                />
                <span
                  v-if="errors.length > 0 && !this.startDate"
                  class="inline-flex text-sm text-red-700"
                >
                  必填
                </span>
              </div>
              <div class="mt-4">
                <wx-label for="endDate"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>
                    結束日期</template
                  ></wx-label
                >

                <wx-input
                  id="endDate"
                  class="block mt-1 w-full"
                  type="date"
                  :value="endDate"
                  @update="(val) => (endDate = val)"
                  required
                  autofocus
                />
                <span
                  v-if="errors.length > 0 && !this.endDate"
                  class="inline-flex text-sm text-red-700"
                >
                  必填
                </span>
              </div>
              <div class="mt-4">
                <wx-label for="searchSku">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup>SKU</template
                  >
                </wx-label>
                <wx-input
                  id="searchSku"
                  class="block mt-1 w-full"
                  type="text"
                  :value="searchSku"
                  @update="(val) => (searchSku = val)"
                  required
                  autofocus
                  ref="searchSku"
                />
                <span
                  v-if="errors.length > 0 && !this.searchSku"
                  class="inline-flex text-sm text-red-700"
                >
                  必填
                </span>
              </div>

              <div class="mt-2">
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4">
                  <div
                    class="
                      block
                      min-w-full
                      shadow
                      rounded-lg
                      max-h-96
                      overflow-y-auto
                    "
                  >
                    <table
                      class="min-w-full leading-normal"
                      style="width: 800px"
                    >
                      <thead class="sticky top-0">
                        <tr>
                          <wx-table-header
                            value="工號"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="SKU"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="單號"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="撿料數量"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="修改數量"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="撿料時間"
                            :align="'text-center'"
                            />
                          <wx-table-header
                            value="#"
                            :align="'text-center'"
                            />
                        </tr>
                      </thead>
                      <tbody class="h-96">
                        <tr v-for="(record, row) in records" :key="row">
                          <wx-table-cell
                            :value="record.employee_no"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.sku"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.order_number"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.quantity"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.fixed_quantity === 0 ? record.fixed_quantity : record.fixed_quantity * -1"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.created_at"
                          ></wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <wx-button
                                class="ml-3"
                                color="blue"
                                @click="editItem(record)"
                                type="button"
                                id="editButton"
                                >修正數量</wx-button
                              >
                            </template>
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
      </div>
    </div>

    <wx-spinner :display="loader" />
    <wx-dialog-modal :show="edit">
      <template #content>
        <div>
          <wx-label class="text-lg font-semibold" for="location" value="撿料溢出" />
        </div>

        <div class="mt-4">
          <wx-label for="editQuantity" value="數量" />
          <wx-input
            id="editQuantity"
            :value="editQuantity"
            type="number"
            min="1"
            class="mt-1 block w-full"
            @update="(val) => editQuantity = val"
          />
          <span
            v-if="editQuantityError"
            class="inline-flex text-sm text-red-700"
          >
            {{ editQuantityError }}
          </span>
        </div>
      </template>

      <template #footer>
        <wx-button
          class="ml-3"
          color="red"
          @click="edit = !edit"
          type="button"
          id="cancelButton"
          >取消</wx-button
        >
        <wx-button
          class="ml-3"
          color="primary"
          @click="editSelectItem"
          type="button"
          id="editButton"
          >送出</wx-button
        >
      </template>
    </wx-dialog-modal>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";

import WxButton from "@/Components/Button";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";
import WxDialogModal from "@/Components/DialogModal";
import WxCommon from "@/Mixins/Common";

export default {
  name: "pickedItemsSearch",

  mixins: [WxCommon],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
    WxDialogModal,
  },

  data() {
    return {
      edit: false,
      searchSku: undefined,
      location: "",
      storageBox: "",
      editQuantity: undefined,
      editRecordId: undefined,
      startDate: undefined,
      endDate: undefined,
      records: [],
      errors: [],
      editQuantityError: undefined
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  mounted () {
    window.addEventListener('keydown', this.getPickedItemLogs)
  },

  beforeDestroy () {
    window.removeEventListener('keydown', this.getPickedItemLogs)
  },

  methods: {
    async getPickedItemLogs(e) {
      if (e.key.toUpperCase() === "ENTER") {
        try {
          if (!this.validateSearch()) {
            return this.displayValidation(
              true,
              this.errors,
              "錯誤訊息。"
            )
          }
          const payload = {
            start_date: this.startDate,
            end_date: this.endDate,
            sku: this.searchSku

          }
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            "api/b2b/picked-items/record",
            payload
          );

          this.records = result.data
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.reset();

        } finally {
          this.loader = false;
        }
      }
    },

    editItem(record) {
      this.edit = true;
      this.editRecordId = record.id;
    },

    async editSelectItem() {
      if (!this.validateEditQuantity()) {
        return
      }
      try {

          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post("api/b2b/picked-items/fix-quantity",{
            id: this.editRecordId,
            fixed_quantity: this.editQuantity
          });

          const rowIndex = this.records.findIndex(
            (record) => record.id === this.editRecordId
          );

          this.records[rowIndex].fixed_quantity = this.editQuantity;

          this.displayValidation(true, [], "更改成功");
          this.editRecordId = undefined
          this.editQuantity = undefined

      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
      } finally {
        this.edit = false;
        this.loader = false;
      }

    },

    reset() {
      this.records = [];
      this.searchSku = "";
    },

    validateSearch () {
      this.errors = []
      this.displayValidation(
            false,
            []
          );
      if (!this.startDate) {
        this.errors.push("起始日起必選！")
      }

      if (!this.endDate) {
        this.errors.push("結束日期必選！")
      }

      if (!this.searchSku) {
        this.errors.push("SKU必填！")
      }

      return this.startDate && this.endDate && this.searchSku
    },

    validateEditQuantity () {
      this.editQuantityError = undefined
      if (!this.editQuantity) {
        this.editQuantityError = "欄位必填"
      }

      if (this.editQuantity < 1) {
        this.editQuantityError = "數字不可小於1"
      }

      return this.editQuantity && this.editQuantity > 0
    },

    validateDate () {
      this.dateError = undefined
      const startDate = new Date(this.startDate)
      const endDate = new Date(this.endDate)

      if (endDate.getTime() < startDate.getTime()) {
        this.errors.push("起始時間不可小於結束時間。")
        return false
      }

      const oneMonth = this.addMonths(startDate, 1)
      if (endDate.getTime() >= oneMonth.getTime()) {
        this.errors.push("時間範圍不可超過一個月。")
        return false
      }

      return true
    },

    addMonths(date, months) {
      var d = date.getDate()
      date.setMonth(date.getMonth() + +months)
      if (date.getDate() != d) {
        date.setDate(0)
      }
      return date
    },

    async exportLogs () {
      try {
          if (!this.validateDate()) {
            return this.displayValidation(
              true,
              this.errors,
              "錯誤訊息。"
            )
          }

          const payload = {
            start_date: this.startDate,
            end_date: this.endDate,
            sku: this.searchSku

          }
          window.open(`/api/b2b/picked-items/export/${this.startDate}/${this.endDate}/${this.searchSku}`);
          this.displayValidation(true, [], "下載成功");
      } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
      }
    }
  },
};
</script>
