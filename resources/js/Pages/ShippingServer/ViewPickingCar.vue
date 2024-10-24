<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        檢視撿料車
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
                <wx-label for="pickingCar"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup>
                    撿料車編號</slot
                  ></wx-label
                >
                <wx-input
                  id="pickingCar"
                  class="block mt-1 w-full"
                  type="text"
                  :value="pickingCar"
                  @update="(val) => (pickingCar = val)"
                  required
                  autofocus
                  @keypress="getBatches"
                  ref="pickingCar"
                />
              </div>

              <div class="mt-4" v-for="batch in batches" :key="batch.boxNo">
                <div
                  class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                  role="alert"
                >
                  <p class="font-bold text-white">
                    No.{{ batch.sequence }} - {{ batch.boxNo }}
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
                      style="width: 1500px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="抽單"></wx-table-header>
                          <wx-table-header value="缺料"></wx-table-header>
                          <wx-table-header value="位置"></wx-table-header>
                          <wx-table-header value="狀態"></wx-table-header>
                          <wx-table-header value="SKU"></wx-table-header>
                          <wx-table-header value="儲位"></wx-table-header>
                          <wx-table-header value="品名"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in batch.items" :key="item.batchItemId">
                          <wx-table-cell>
                            <template>
                              <wx-button
                                class="ml-3"
                                color="red"
                                v-if="item.firstItem"
                                @click="
                                  deleteShipment(
                                    batch.boxNo,
                                    item.batchId,
                                    item.shipmentId
                                  )
                                "
                                >抽單</wx-button
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <wx-button
                                class="ml-3"
                                color="red"
                                v-if="item.status === null"
                                @click="
                                  deleteShipmentSetOutOfStock(
                                    batch.boxNo,
                                    item.batchId,
                                    item.shipmentId,
                                    item.sourceItemId,
                                    item.sku
                                  )
                                "
                                >缺料</wx-button
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>{{ item.sequence }}</wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <wx-button
                                v-if="item.status === null"
                                class="ml-3"
                                color="blue"
                                @click="
                                  bypass(
                                    batch.boxNo,
                                    item.batchItemId,
                                    item.shipmentItemId
                                  )
                                "
                                >Pass</wx-button
                              >
                              <span class="text-gray-500" v-else>已撿料</span>
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <sup
                                class="text-red-600"
                                v-if="item.isCustomized === 1"
                                >客</sup
                              >
                              {{ item.sku }}
                              <span
                                class="text-gray-500"
                                v-if="item.isCustomized === 1"
                                >({{ item.sourceItemId }})</span
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <span
                                class="text-gray-500"
                                v-if="item.isCustomized === 0"
                                >{{ item.location }}</span
                              >
                              <wx-button
                                v-if="
                                  item.status === null &&
                                  item.isCustomized === 0
                                "
                                class="ml-3"
                                color="blue"
                                @click="
                                  resetLocation(
                                    batch.boxNo,
                                    item.batchItemId,
                                    item.sku,
                                    item.location
                                  )
                                "
                                >重設</wx-button
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>{{ item.productTitle }}</wx-table-cell>
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
  name: "viewPickingCar",

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
      batches: [],
      pickingCar: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async bypass(boxNo, batchItemId, shipmentItemId) {
      if (confirm("確認是否要Bypass?")) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            "/api/shipping-server/batch/bypass-batch-item",
            {
              batchItemId,
              shipmentItemId,
            }
          );

          const batchIndex = this.batches.findIndex(
            (batch) => batch.boxNo === boxNo
          );
          const batchItemIndex = this.batches[batchIndex].items.findIndex(
            (item) => item.batchItemId === batchItemId
          );
          this.batches[batchIndex].items[batchItemIndex].status = "picked";

          this.displayValidation(false);
          this.loader = false;
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

    async deleteShipment(boxNo, batchId, shipmentId) {
      if (confirm("確認是否要刪除?")) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            "/api/shipping-server/batch/delete-shipment",
            {
              batchId,
              shipmentId,
            }
          );

          const batchIndex = this.batches.findIndex(
            (batch) => batch.boxNo === boxNo
          );
          const batchItems = this.batches[batchIndex].items.filter(
            (item) => item.shipmentId !== shipmentId
          );

          this.batches[batchIndex].items = batchItems;

          this.displayValidation(false);
          this.loader = false;
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

    async deleteShipmentSetOutOfStock(boxNo, batchId, shipmentId,sourceItemId,checkSku) {
      if (confirm("確認是否要刪除並設定為缺料?")) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            "/api/shipping-server/batch/delete-shipment-set-out-of-stock",
            {
              batchId,
              shipmentId,
              sourceItemId,
              checkSku
            }
          );

          const batchIndex = this.batches.findIndex(
            (batch) => batch.boxNo === boxNo
          );
          const batchItems = this.batches[batchIndex].items.filter(
            (item) => item.shipmentId !== shipmentId
          );

          this.batches[batchIndex].items = batchItems;

          this.displayValidation(false);
          this.loader = false;
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

    async getBatches(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(
            "/api/shipping-server/picking-car/current-batches",
            {
              car_no: this.pickingCar,
            }
          );

          this.batches = result.data;
          this.loader = false;
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.loader = false;
        }

        this.pickingCar = "";
        this.$refs.pickingCar.focus();
      }
    },

    async resetLocation(boxNo, batchItemId, checkSku, location) {
      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/shipping-server/batch/reset-location",
          {
            batchItemId,
            checkSku,
            location,
          }
        );

        const batchIndex = this.batches.findIndex(
          (batch) => batch.boxNo === boxNo
        );

        const itemIndex = this.batches[batchIndex].items.findIndex(
          (item) => item.batchItemId === batchItemId
        );

        this.batches[batchIndex].items[itemIndex].location =
          result.data.location;

        this.displayValidation(false);
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
  },
};
</script>
