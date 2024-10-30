<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        B2B查詢 - EAN / SKU
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
                <wx-label for="eanSku">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup> EAN /
                    SKU</template
                  >
                </wx-label>
                <wx-input
                  id="eanSku"
                  class="block mt-1 w-full"
                  type="text"
                  :value="eanSku"
                  @update="(val) => (eanSku = val)"
                  required
                  autofocus
                  ref="eanSku"
                  @keypress="getLocations"
                />
              </div>

              <div class="mt-4" v-if="sku !== ''">
                <wx-label for="sku" value="SKU"></wx-label>
                <wx-label id="sku">
                  <span class="text-blue-800 font-bold text-base">{{
                    sku
                  }}</span>
                </wx-label>
              </div>

              <div
                class="mt-2"
                v-if="productTitle !== null && productTitle !== ''"
              >
                <wx-label for="productTitle" value="品名"></wx-label>
                <wx-label id="productTitle">
                  <span class="text-blue-800 font-bold text-base">{{
                    productTitle
                  }}</span>
                </wx-label>
              </div>

              <div class="mt-2" v-if="ean !== null && ean !== ''">
                <wx-label for="ean" value="EAN"></wx-label>
                <wx-label id="ean">
                  <span class="text-blue-800 font-bold text-base">{{
                    ean
                  }}</span>
                </wx-label>
              </div>


              <div class="mt-2">
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
                      style="width: 800px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="倉庫"></wx-table-header>
                          <wx-table-header value="儲位"></wx-table-header>
                          <wx-table-header value="貨箱條碼"></wx-table-header>
                          <wx-table-header value="數量"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="storageBox in storageBoxes"
                          :key="storageBox.id"
                        >
                          <wx-table-cell>
                            <template>
                              {{
                                storageBox.warehouse === null ||
                                storageBox.warehouse === ""
                                  ? "未入庫"
                                  : storageBox.warehouse
                              }}
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>
                            <template>
                              {{
                                storageBox.location === null ||
                                storageBox.location === ""
                                  ? "未入庫"
                                  : storageBox.location
                              }}
                            </template>
                          </wx-table-cell>
                          <wx-table-cell
                            :value="storageBox.storage_box"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="storageBox.quantity"
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
  name: "5FEanSkuSearch",

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
      ean: "",
      eanSku: "",
      productTitle: "",
      sku: "",
      storageBoxes: [],
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async getLocations(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/b2b/query/ean-sku/${e.target.value.trim().toUpperCase()}`
          );

          this.storageBoxes = result.data.storageBoxes;
          this.ean = result.data.ean;
          this.productTitle = result.data.productTitle;
          this.sku = result.data.sku;
          this.eanSku = "";
          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.reset();
          this.loader = false;
        }
      }
    },

    reset() {
      this.storageBoxes = [];
      this.ean = "";
      this.productTitle = "";
      this.sku = "";
      this.eanSku = "";
    },
  },
};
</script>
