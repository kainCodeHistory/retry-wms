<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        查詢 - 儲位
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
                <wx-label for="searchLocation">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup>
                    儲位條碼</template
                  >
                </wx-label>
                <wx-input
                  id="searchLocation"
                  class="block mt-1 w-full"
                  type="text"
                  :value="searchLocation"
                  @update="(val) => (searchLocation = val)"
                  required
                  autofocus
                  ref="searchLocation"
                  @keypress="getStorageBox"
                />
              </div>

              <div class="mt-4" v-if="storageBoxLocation !== ''">
                <table class="min-w-full leading-normal" style="width: 300px">
                  <thead>
                    <tr>
                      <wx-table-header value="成品倉儲位"></wx-table-header>
                      <wx-table-header value="成品倉貨箱"></wx-table-header>
                      <wx-table-header value="SKU"></wx-table-header>
                      <wx-table-header value="數量"></wx-table-header>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="storageBox in storageBoxLocation"
                      :key="storageBox.barcode"
                    >
                      <wx-table-cell
                        :value="storageBox.location"
                      ></wx-table-cell>
                      <wx-table-cell
                        :value="storageBox.barcode"
                      ></wx-table-cell>
                       <wx-table-cell
                        :value="storageBox.sku"
                      ></wx-table-cell>
                      <wx-table-cell
                        :value="storageBox.initial_quantity"
                      ></wx-table-cell>
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
import AppLayout from "@/Layouts/AppLayout";

import WxButton from "@/Components/Button";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";

import WxCommon from "@/Mixins/Common";

export default {
  name: "5FLocationSearch",

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
  },

  data() {
    return {
      searchLocation:"",
      isEmpty: 0,
      storageBoxLocation: [],
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async getStorageBox(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/b2b/query/location/${e.target.value.trim().toUpperCase()}`
          );

          if (result.data.storageDetail.length > 0) {
            console.log(result.data.storageDetail[0]);
            this.storageBoxLocation = result.data.storageDetail[0];
            this.searchLocation = "";
            this.displayValidation(false, []);
          } else {
            this.reset();
          }

          this.loader = false;
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.reset();
          this.loader = false;
        }
      }
    },

    reset() {
      this.searchLocation = "";
      this.storageBoxLocation = [];
    },
  },
};
</script>
