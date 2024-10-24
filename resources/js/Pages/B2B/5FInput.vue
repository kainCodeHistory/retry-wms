<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        B2B - 五樓入庫用 - {{ inputId > 0 ? "修改" : "新增" }}紀錄
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
                <wx-label for="eanSku"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup> 輸入 EAN 或
                    SKU</template
                  ></wx-label
                >
                <wx-input
                  id="eanSku"
                  class="block mt-1 w-full"
                  type="text"
                  :value="eanSku"
                  @update="(val) => (eanSku = val)"
                  required
                  autofocus
                  @keypress="getMaterial"
                  ref="eanSku"
                />
              </div>

              <div class="mt-2" v-if="sku != ''">
                <wx-label value="SKU"></wx-label>
                <wx-label>
                  <template>
                    <span class="text-blue-800 font-bold text-base">{{
                      sku
                    }}</span>
                  </template>
                </wx-label>
              </div>

              <div class="mt-2" v-if="sku != ''">
                <wx-label value="EAN"></wx-label>
                <wx-label>
                  <template>
                    <span class="text-blue-800 font-bold text-base">{{
                      ean
                    }}</span>
                  </template>
                </wx-label>
              </div>

              <div class="mt-2" v-if="sku != ''">
                <wx-label value="產品名稱"></wx-label>
                <wx-label>
                  <template>
                    <span class="text-blue-800 font-bold text-base">{{
                      productTitle
                    }}</span>
                  </template>
                </wx-label>
              </div>

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
                  :value="manufacturingDate"
                  @update="(val) => (manufacturingDate = val)"
                  required
                  autofocus
                />
              </div>

              <div class="mt-2" v-if="inputId > 0">
                <wx-label value="項次"></wx-label>
                <wx-label>
                  <template>
                    <span class="text-blue-800 font-bold text-base">{{
                      itemNumber
                    }}</span>
                  </template>
                </wx-label>
              </div>

              <div class="mt-4">
                <wx-label for="quantity"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup> 數量</template
                  ></wx-label
                >
                <wx-input
                  id="quantity"
                  class="block mt-1 w-full"
                  type="number"
                  :value="quantity"
                  @update="(val) => (quantity = val)"
                  required
                  autofocus
                />
              </div>

              <div class="mt-4">
                <wx-label for="note" value="備註"></wx-label>
                <wx-input
                  id="note"
                  class="block mt-1 w-full"
                  type="text"
                  :value="note"
                  @update="(val) => (note = val)"
                  autofocus
                />
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" @click="upsertInput" color="blue">
                  <template>
                    {{ inputId > 0 ? "儲存" : "新增" }}
                  </template>
                </wx-button>

                <wx-button
                  class="ml-3"
                  @click="deleteInput"
                  color="red"
                  v-if="inputId > 0"
                >
                  <template>刪除</template>
                </wx-button>
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
import WxValidationMessages from "@/Components/ValidationMessages";

import WxCommon from "@/Mixins/Common";
import { throwStatement } from "@babel/types";

export default {
  name: "storageBoxInputBindMaterial",

  mixins: [WxCommon],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxSpinner,
    WxValidationMessages,
  },

  data() {
    return {
      ean: "",
      eanSku: "",
      inputId: 0,
      itemNumber: 0,
      manufacturingDate: "",
      note: "",
      productTitle: "",
      quantity: 0,
      sku: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  async mounted() {
    const inputId = parseInt(this.$route.query.inputId);
    if (inputId === 0) {
      this.reset();
      this.getCurrentDate();
    } else {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.get(`/api/b2b-5f/input/${inputId}`);

        if (result.data.length === 0) {
          this.displayValidation(true, [], `查無紀錄或已刪除。`);
          this.reset();
          this.getCurrentDate();
        } else {
          this.ean = result.data.ean;
          this.inputId = result.data.id;
          this.itemNumber = result.data.item_number;
          this.manufacturingDate = result.data.manufacturing_date;
          this.note = result.data.note;
          this.productTitle = result.data.product_title;
          this.quantity = result.data.quantity;
          this.sku = result.data.material_sku;
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
    }
  },

  methods: {
    async upsertInput() {
      if (this.sku === "") {
        alert("請輸入 SKU 或 EAN。");
        return;
      } else if (this.quantity === 0) {
        alert("請輸入數量。");
        return;
      }

      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post("/api/b2b-5f/input", {
          inputId: this.inputId,
          manufacturingDate: this.manufacturingDate,
          sku: this.sku,
          quantity: this.quantity,
          note: this.note,
        });

        this.loader = false;

        if (this.inputId === 0) {
          this.displayValidation(
            true,
            [],
            `已新增一筆紀錄 (SKU: ${result.data.material_sku}，製造日期: ${result.data.manufacturing_date}，項次: ${result.data.item_number})。`
          );

          this.reset();
          this.$refs.eanSku.focus();
        } else {
          this.$router.push(
            `/b2b-5f-input-list?manufacturingDate=${this.manufacturingDate}`
          );
        }
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
        this.loader = false;
      }
    },

    async deleteInput() {
      if (confirm("確認刪除此筆紀錄?")) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          await this.axios.delete(`/api/b2b-5f/input/${this.inputId}`);

          this.displayValidation(
            true,
            [],
            `已刪除一筆紀錄 (SKU: ${this.sku}，製造日期: ${this.manufacturingDate}，項次: ${this.itemNumber})。`
          );

          this.loader = false;

          this.$router.push(
            `/b2b-5f-input-list?manufacturingDate=${this.manufacturingDate}`
          );
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

    async getMaterial(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/material/${e.target.value.trim()}`
          );

          this.loader = false;
          if (result.data.sku === "") {
            this.displayValidation(
              true,
              [],
              `無此料號或 SKU (${e.target.value.trim()})。`
            );
          } else {
            this.displayValidation(false);
          }

          this.productTitle = result.data.name;
          this.sku = result.data.sku;
          this.ean = result.data.ean;
          this.eanSku = "";
        } catch (error) {
          this.loader = false;

          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );

          this.productTitle = "";
          this.ean = "";
          this.sku = "";
          this.eanSku = "";
        }
      }
    },

    getCurrentDate() {
      this.manufacturingDate = new Date().toISOString().slice(0, 10);
    },

    reset() {
      this.inputId = 0;
      this.ean = "";
      this.eanSku = "";
      this.itemNumber = 0;
      this.note = "";
      this.productTitle = "";
      this.quantity = 0;
      this.sku = "";
    },
  },
};
</script>
