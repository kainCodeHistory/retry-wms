<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
      入庫 - 綁定儲位
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
                    貨箱條碼</template
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
                  @keypress="getBinding"
                  ref="storageBox"
                />
              </div>

              <div class="mt-4">
                <wx-label for="location"
                  ><template
                    ><sup class="text-red-500 font-bold">*</sup>
                    儲位條碼</template
                  ></wx-label
                >
                <wx-input
                  id="location"
                  class="block mt-1 w-full"
                  type="text"
                  :value="location"
                  @update="(val) => (location = val)"
                  required
                  autofocus
                  ref="location"
                />
              </div>
                <div class="mt-2">
                <wx-label>
                  <template>
                    建議儲位:
                    <span class="text-blue-800 font-bold text-base">{{
                      recommendPickLocation
                    }}</span>
                  </template>
                </wx-label>
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" @click="bindStorageBox" color="blue"
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
import WxValidationMessages from "@/Components/ValidationMessages";

import WxCommon from "@/Mixins/Common";

export default {
  name: "storageBoxInput5FBindLocation",

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
      location: "",
      recommendPickLocation:""
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async bindStorageBox(e) {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/b2b-5f/storage-box/input/bind-location",
          {
            location: this.location,
            storageBox: this.storageBox,
          }
        );

        this.reset();
        this.$refs.storageBox.focus();
        this.displayValidation(true, [], "儲存成功。");
        this.loader = false;
      } catch (error) {
        this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
        this.loader = false;
      }
    },

    async getBinding(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/storage-box/input/binding/${e.target.value.trim()}`
          );

          this.recommendLocation = result.data.location;
          this.recommendPickLocation = result.data.pickLocation;
           this.$refs.location.focus();
          this.displayValidation(false);
          this.loader = false;
        } catch (error) {
          this.recommendLocation = "";
          this.recommendPickLocation = "";

          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }
      }
    },

    reset() {
      this.storageBox = "";
      this.location = "";
      this.recommendPickLocation ="";
    },
  },
};
</script>
