<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        缺貨品項管理
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

            <div v-if="locations.length > 0">
              <div class="mt-2">
                <wx-label for="checkSku" value="SKU"></wx-label>
                <wx-label id="checkSku">
                  <span class="text-blue-800 font-bold text-base">{{
                    checkSku
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label for="productTitle" value="品名"></wx-label>
                <wx-label id="productTitle">
                  <span class="text-blue-800 font-bold text-base">{{
                    productTitle
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label for="totalQuantity" value="總數量"></wx-label>
                <wx-label id="totalQuantity">
                  <span class="text-blue-800 font-bold text-base">{{
                    totalQuantity
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label for="inStock" value="In Stock 數量"></wx-label>
                <wx-label id="inStock">
                  <span class="text-blue-800 font-bold text-base">{{
                    inStock
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label for="hold" value="Hold 數量"></wx-label>
                <wx-label id="hold">
                  <span class="text-blue-800 font-bold text-base">{{
                    hold
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label for="outOfStock" value="Out Of Stock 數量"></wx-label>
                <wx-label id="outOfStock">
                  <span class="text-blue-800 font-bold text-base">{{
                    outOfStock
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2">
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-auto">
                  <div class="inline-block min-w-full shadow rounded-lg">
                    <table class="min-w-full leading-normal">
                      <thead>
                        <tr>
                          <wx-table-header
                            value="儲位"
                            style="width: 35%"
                          ></wx-table-header>
                          <wx-table-header
                            value="數量"
                            class="sticky"
                            style="width: 30%"
                          ></wx-table-header>
                          <wx-table-header
                            value="實際數量"
                            style="width: 35%"
                          ></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in locations" :key="item.id">
                          <wx-table-cell :value="item.location">
                          </wx-table-cell>
                          <wx-table-cell :value="item.quantity"></wx-table-cell>
                          <wx-table-cell>
                            <template>
                              <wx-input
                                class="block mt-1 w-full"
                                type="number"
                                :value="item.countedQuantity"
                                @update="(val) => (item.countedQuantity = val)"
                                required
                                autofocus
                              />
                            </template>
                          </wx-table-cell>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="flex items-center mt-4">
                <wx-button class="ml-3" color="blue" @click="updateQuantity"
                  >更新</wx-button
                >
              </div>
            </div>

            <div v-else>
              <p class="text-center text-md mt-4 mb-4">無缺貨品項盤點任務。</p>
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
  name: "pickingAreaAddLocation",

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
      checkSku: "",
      hold: 0,
      inStock: 0,
      locations: [],
      outOfStock: 0,
      productTitle: "",
      totalQuantity: 0,
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  async mounted() {
    await this.getOutOfStockItem();
  },

  methods: {
    async getOutOfStockItem() {
      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.get(
          "/api/shipping-server/out-of-stock/item"
        );

        if (result.data.locations.length === 0) {
          this.reset();
        } else {
          this.checkSku = result.data.checkSku;
          this.productTitle = result.data.productTitle;
          this.totalQuantity = result.data.totalQuantity;
          this.inStock = result.data.inStock;
          this.hold = result.data.hold;
          this.outOfStock = result.data.outOfStock;
          this.locations = result.data.locations;
        }

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

    async updateQuantity() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/shipping-server/out-of-stock/item",
          {
            checkSku: this.checkSku,
            locations: this.locations,
          }
        );

        this.loader = false;

        await this.getOutOfStockItem();
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
        this.loader = false;
      }
    },

    reset() {
      this.checkSku = "";
      this.productTitle = "";
      this.totalQuantity = 0;
      this.inStock = 0;
      this.hold = 0;
      this.outOfStock = 0;
      this.locations = [];
    },
  },
};
</script>
