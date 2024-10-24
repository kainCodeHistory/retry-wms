<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        查詢 - 貨箱
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
                <wx-label for="storageBox">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup>
                    貨箱/儲位條碼</template
                  >
                </wx-label>
                <wx-input
                  id="storageBox"
                  class="block mt-1 w-full"
                  type="text"
                  :value="storageBox"
                  @update="(val) => (storageBox = val)"
                  required
                  autofocus
                  ref="storageBox"
                  @keypress="getMaterial"
                />
              </div>

              <div class="mt-4" v-if="currentStorage !== ''">
                <wx-label for="currentStorage" value="貨箱"></wx-label>
                <wx-label id="currentStorage">
                  <span class="text-blue-800 font-bold text-base">{{
                    currentStorage
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-4" v-if="location !== ''">
                <wx-label for="location" value="儲位"></wx-label>
                <wx-label id="location">
                  <span class="text-blue-800 font-bold text-base">{{
                    location
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== ''">
                <wx-label for="sku" value="SKU"></wx-label>
                <wx-label id="sku">
                  <span class="text-blue-800 font-bold text-base">{{
                    sku
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== '' && productTitle !== ''">
                <wx-label for="productTitle" value="品名"></wx-label>
                <wx-label id="productTitle">
                  <span class="text-blue-800 font-bold text-base">{{
                    productTitle
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== '' && ean !== ''">
                <wx-label for="ean" value="EAN"></wx-label>
                <wx-label id="ean">
                  <span class="text-blue-800 font-bold text-base">{{
                    ean
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== ''">
                <wx-label for="quantity" value="數量"></wx-label>
                <wx-label id="quantity">
                  <span class="text-blue-800 font-bold text-base">{{
                    quantity
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== '' && batchNo !== ''">
                <wx-label for="batchNo" value="Batch No"></wx-label>
                <wx-label id="batchNo">
                  <span class="text-blue-800 font-bold text-base">{{
                    batchNo
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-4" v-if="location !== ''">
               <table
                      class="min-w-full leading-normal"
                      style="width: 300px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="預備倉儲位"></wx-table-header>
                          <wx-table-header value="預備倉貨箱"></wx-table-header>
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
                            :value="storageBox.quantity"
                          ></wx-table-cell>
                        </tr>
                      </tbody>
                      </table>
              </div>

              <div class="mt-2" v-if="isEmpty === 0 && sku !== ''">
                  <img :src="`http://10.60.1.113/material-image/${sku}.png`">
              </div>

              <div class="mt-4" v-if="isEmpty === 1">
                <div class="px-3">
                  <h3 class="text-red-800 font-semibold tracking-wider">
                    此貨箱未綁定物料。
                  </h3>
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
  name: "pickingAreaRefillAddLocation",

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
      batchNo: "",
      ean: "",
      location: "",
      isEmpty: 0,
      productTitle: "",
      quantity: 0,
      sku: "",
      storageBox: "",
      currentStorage:"",
      storageLocation:[],
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async getMaterial(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/query/storage-box/${e.target.value.trim().toUpperCase()}`
          );

          if (result.data.storageDetail.length > 0) {

            this.batchNo = result.data.storageDetail[0].batch_no === null ? '' : result.data.storageDetail[0].batch_no;
            this.ean = result.data.storageDetail[0].ean === null ? '' : result.data.storageDetail[0].ean;
            this.location = result.data.storageDetail[0].location;
            this.isEmpty = result.data.storageDetail[0].is_empty;
            this.sku = result.data.storageDetail[0].material_sku === null ? '' : result.data.storageDetail[0].material_sku;
            this.productTitle = result.data.storageDetail[0].material_name === null ? '' : result.data.storageDetail[0].material_name;
            this.quantity = result.data.storageDetail[0].quantity;
            this.currentStorage = this.storageBox;
            this.storageBoxLocation = result.data.skuDetail;
            this.storageBox = '';

          } else {
              this.reset();
          }

          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.reset();
          this.loader = false;
        }
      }
    },

    reset() {
      this.batchNo = "";
      this.location = "";
      this.isEmpty = 1;
      this.ean = "";
      this.productTitle = "";
      this.quantity = 0;
      this.sku = "";
      this.storageBox = "";
      this.storageLocation = [];
      this.storageBoxNumber = [];
    },
  },
};
</script>
