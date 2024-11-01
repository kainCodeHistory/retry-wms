<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        成品倉 - XB區儲位上架
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
                <wx-label for="location">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup>
                    儲位條碼</template
                  >
                </wx-label>
                <wx-input
                  id="location"
                  class="block mt-1 w-full"
                  type="text"
                  :value="location"
                  @update="(val) => (location = val)"
                  required
                  autofocus
                  @keypress="getLocation"
                  ref="location"
                />
              </div>

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
                   ref="quantity"
                />
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" color="blue" @click="bindLocation"
                  >綁定</wx-button
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
      batchNo: "",
      location: "",
      materialName: "",
      quantity: 0,
      sku: "",
      storageBox: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async bindLocation() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/b2b/storage-box/input/xb",
          {
            location: this.location,
            quantity: this.quantity,
            sku: this.sku,
            storageBox: this.storageBox,
          }
        );

        this.reset();
        this.$refs.location.focus();
        this.displayValidation(true, [], "綁定成功。");
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

    async getLocation(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/picking-area/ac/${e.target.value.trim()}`
          );

          if (result.data.sku === "") {
            this.location = result.data.location;
            this.storageBox = result.data.storageBox;
            this.displayValidation(false);
            this.$refs.sku.focus();
          } else {
            this.displayValidation(
              true,
              [`此儲位已綁定料號 ${result.data.sku}，無法重複綁定。`],
              "錯誤訊息。"
            );
            this.reset;
            this.$refs.location.focus();
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

    async getMaterialName(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/material/${e.target.value.trim()}`
          );
          this.sku = result.data.sku;
          this.materialName = result.data.name;
          this.$refs.quantity.focus();
          this.loader = false;
        } catch (error) {
          this.loader = false;
        }

        this.displayValidation(false);
      }
    },

    reset() {
      this.batchNo = "";
      this.location = "";
      this.materialName = "";
      this.newQuantity = 0;
      this.quantity = 0;
      this.sku = "";
      this.storageBox = "";
    },
  },
};
</script>
