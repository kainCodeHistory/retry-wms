<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        貨箱 - 出庫 - 2.貨箱出庫
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
                <wx-label for="storageBox"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup> 貨箱條碼</slot
                  ></wx-label
                >
                <wx-input
                  id="storageBox"
                  class="block mt-1 w-full"
                  type="text"
                  :value="storageBox"
                  @update="(val) => (storageBox = val)"
                  required
                  autofocus
                  @keypress="resetLocation"
                  ref="storageBox"
                />
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
  name: "storageBoxDisconnect",

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
    async resetLocation(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          await this.axios.post("/api/storage-box/output/reset-location", {
            storageBox: this.storageBox,
          });

          this.displayValidation(true, [], `貨箱 (${this.storageBox}) 已出庫。`);
          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }

        this.$refs.storageBox.focus();
        this.storageBox = "";
      }
    },
  },
};
</script>
