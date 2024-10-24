<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        製造轉成品倉入庫
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
                <wx-label for="box"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>
                    紙箱條碼</template
                  ></wx-label
                >
                <wx-input
                  id="box"
                  class="block mt-1 w-full"
                  type="text"
                  :value="box"
                  @update="(val) => (box = val)"
                  required
                  autofocus
                  ref="box"
                />
              </div>

              <div class="mt-2">
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
                  @keypress="getMaterialName"
                  ref="sku"
                />
              </div>

              <div class="mt-2">
                <wx-label :value="materialName"></wx-label>
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

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" @click="addInput" color="blue"
                  >儲存</wx-button
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
    WxSpinner,
    WxValidationMessages,
  },

  data() {
    return {
      box: "",
      materialName: "",
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
    async addInput() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post("/api/b2b/input", {
          box: this.box,
          sku: this.sku,
          quantity: this.quantity,
        });

        const inputId = result.data.inputId;
        this.reset();
        this.$refs.box.focus();
        this.displayValidation(true, [], `已新增一筆紀錄 (ID: ${inputId})。`);
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

    async getMaterialName(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/material/${e.target.value.trim()}`
          );

          this.materialName = result.data.name;
          this.sku = result.data.sku;
          this.loader = false;
        } catch (error) {
          this.loader = false;
        }

        this.displayValidation(false);
      }
    },

    reset() {
      this.box = "";
      this.materialName = "";
      this.quantity = 0;
      this.sku = "";
    },
  },
};
</script>
