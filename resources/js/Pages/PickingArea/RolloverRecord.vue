<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        紀錄 - 五樓成品倉轉倉
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
                <wx-label for="sku"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup> SKU</template
                  ></wx-label
                >
                <wx-input
                  id="sku"
                  class="block mt-1 w-full"
                  type="text"
                  :value="sku"
                  @update="(val) => (sku = val)"
                  required
                  autofocus
                  @keypress="getSku"
                  ref="sku"
                />
              </div>

              <div class="mt-4">
                <wx-label for="quantity"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>數量</template
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
                <wx-button class="ml-3" color="green" @click="downloadRecord"
                  >下載今日轉倉紀錄</wx-button
                >
                <wx-button class="ml-3" color="blue" @click="sendRecord"
                  >送出</wx-button
                >
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
      note: "",
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

  methods: {
    async getSku(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/rollover/${e.target.value.trim()}`
          );

          if (result.data.sku !== "") {
            this.sku = result.data.sku;
          } else {
            this.displayValidation(true, [`無此EAN`], "錯誤訊息。");
            this.reset;
            this.$refs.sku.focus();
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
    async sendRecord() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post("/api/rollover-record", {
          note: this.note,
          sku: this.sku,
          quantity: this.quantity,
        });

        this.reset();
        this.$refs.sku.focus();
        this.displayValidation(
          true,
          [`SKU: ${result.data.sku} 數量: ${result.data.quantity}。`],
          "儲存成功。"
        );
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

    async downloadRecord() {
      try {
        window.open("/api/rollover-record/report");
        this.displayValidation(true, [], "下載成功");
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
      this.note = "";
      this.quantity = 0;
      this.sku = "";
    },
  },
};
</script>
