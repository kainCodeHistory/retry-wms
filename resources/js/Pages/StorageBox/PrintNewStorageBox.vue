<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        貨箱 - 列印貨箱條碼
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

            <div class="mt-4">
              <wx-label for="b2cAddNewStorageBox">
                <template
                  ><sup class="text-red-500 font-bold">*</sup>
                  B2C貨箱-新增貨箱條碼</template
                >
              </wx-label>
              <wx-input
                id="b2cAddNewStorageBox"
                class="block mt-1 w-full"
                type="number"
                :value="b2cAddNewStorageBox"
                @update="(val) => (b2cAddNewStorageBox = val)"
                required
                autofocus
              />
            </div>

            <div class="mt-4">
              <wx-label for="b2bAddNewCartonLabel">
                <template
                  ><sup class="text-red-500 font-bold">*</sup>
                  B2B貨箱-新增紙箱條碼</template
                >
              </wx-label>
              <wx-input
                id="b2bAddNewCartonLabel"
                class="block mt-1 w-full"
                type="number"
                :value="b2bAddNewCartonLabel"
                @update="(val) => (b2bAddNewCartonLabel = val)"
                required
                autofocus
              />
            </div>

            <div class="flex items-center justify-end mt-4">
              <wx-button class="ml-3" color="blue" @click="createLabels"
                >新增</wx-button
              >
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
import WxInputError from "@/Components/InputError";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";

export default {
  name: "printNewStorageBox",

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxInputError,
    WxLabel,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
  },

  data() {
    return {
      b2cAddNewStorageBox: 0,
      b2bAddNewCartonLabel: 0,
      errorMessage: "",
      loader: false,
      validation: {
        display: false,
        errors: [],
        title: "儲存成功。",
      },
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    displayValidation(display, errors = [], title = "") {
      this.validation.display = display;
      this.validation.errors = errors;
      this.validation.title = title;
    },

    reset() {
      (this.b2cAddNewStorageBox = 0),
        (this.b2bAddNewCartonLabel = 0),
        (this.b2bAddBigBlueBox = 0),
        (this.b2bAddWhiteBox = 0);
    },

    async createLabels(e) {
      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
       const result = await this.axios.post("/api/print/new-storage-box", {
          b2cAddNewStorageBox: this.b2cAddNewStorageBox,
          b2bAddNewCartonLabel: this.b2bAddNewCartonLabel,
        });

        this.reset();
        this.displayValidation(
          true,
          [],
          `新增成功，共 ${result.data.quantity} 筆。`
        );
        this.loader = false;
      } catch (error) {
        const result = JSON.parse(error.request.response);
        this.displayValidation(true, result.errors, "錯誤訊息。");
        this.loader = false;
      }
    },
  },
};
</script>
