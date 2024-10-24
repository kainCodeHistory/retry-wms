<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        檢視撿料箱
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
                <wx-label for="pickingBox"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup>
                    撿料箱條碼</slot
                  ></wx-label
                >
                <wx-input
                  id="stationNo"
                  class="block mt-1 w-full"
                  type="text"
                  :value="pickingBox"
                  @update="(val) => (pickingBox = val)"
                  required
                  autofocus
                  @keypress="getBatchItems"
                  ref="pickingBox"
                />
              </div>

              <div class="mt-4">
                <div
                  class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                  role="alert"
                  v-if="batchItems.length > 0"
                >
                  <p class="font-bold text-white">
                    Batch ID: {{ batchItems[0].batchId }}, Shipping Station No: {{ batchItems[0].shippingStation }}
                  </p>
                </div>

                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                  <div
                    class="
                      inline-block
                      min-w-full
                      shadow
                      rounded-lg
                      overflow-hidden
                    "
                  >
                    <table
                      class="min-w-full leading-normal"
                      style="width: 1200px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="環保標籤"></wx-table-header>
                          <wx-table-header value="位置"></wx-table-header>
                          <wx-table-header value="狀態"></wx-table-header>
                          <wx-table-header value="SKU"></wx-table-header>
                          <wx-table-header value="品名"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="batchItem in batchItems" :key="batchItem.id">
                          <wx-table-cell>
                            <template>
                              <wx-button
                                class="ml-3"
                                color="red"
                                v-if="batchItem.isEcoGreenPackage === 1"
                                @click="printEcoGreenPackageLabel(batchItem)"
                                >補印</wx-button
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>{{
                            parseInt(batchItem.gridCount) === 8 ? (parseInt(batchItem.sequence) + 1) : batchItem.sequence
                          }}</wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <span class="text-gray-500">{{
                                batchItem.status === "packed"
                                  ? "已包裝"
                                  : "未包裝"
                              }}</span>
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <sup
                                class="text-red-600"
                                v-if="batchItem.isCustomized === 1"
                                >客</sup
                              >
                              {{ batchItem.sku }}
                              <span
                                class="text-gray-500"
                                v-if="batchItem.isCustomized === 1"
                                >({{ batchItem.sourceItemId }})</span
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>{{
                            batchItem.productTitle
                          }}</wx-table-cell>
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
  name: "storageBoxReset",

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
      pickingBox: "",
      batchItems: []
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async getBatchItems(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/shipping-server/picking-box/get-current-packing-batch/${e.target.value.trim()}`
          );

          this.batchItems = result.data;
          this.displayValidation(false);

          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }

        this.$refs.pickingBox.focus();
        this.pickingBox = "";
      }
    },

    async printEcoGreenPackageLabel(batchItem) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            '/api/shipping-station/print/eco-green-package-label',
            {
                lang: batchItem.lang,
                stationNo: batchItem.shippingStation,
                shipmentItem: `${batchItem.sourceType}_${batchItem.sourceId}_${batchItem.sourceItemId}`,
                printQuantity: 1,
                sku: batchItem.sku,
                productTitle: batchItem.productTitle
            }
          );

          this.displayValidation(true, [], "補印成功。");

          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }
    },
  },
};
</script>
