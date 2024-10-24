<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        批次品項復歸檔
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <div style="border-bottom: 2px solid #eaeaea">
              <ul class="flex cursor-pointer">
                <li class="py-2 px-6 bg-white rounded-t-lg">上傳檔案</li>
              </ul>
            </div>

            <wx-validation-messages
              :display="validation.display"
              :errors="validation.errors"
              :title="validation.title"
            />

            <div>
              <div
                class="
                  flex
                  w-full
                  items-center
                  justify-center
                  bg-grey-lighter
                  mt-4
                "
              >
                <label
                  class="
                    w-64
                    flex flex-col
                    items-center
                    px-4
                    py-6
                    bg-white
                    text-blue
                    rounded-lg
                    shadow-lg
                    tracking-wide
                    uppercase
                    border border-blue
                    cursor-pointer
                  "
                >
                  <svg
                    class="w-8 h-8"
                    fill="currentColor"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                  >
                    <path
                      d="M16.88 9.1A4 4 0 0 1 16 17H5a5 5 0 0 1-1-9.9V7a3 3 0 0 1 4.52-2.59A4.98 4.98 0 0 1 17 8c0 .38-.04.74-.12 1.1zM11 11h3l-4-4-4 4h3v3h2v-3z"
                    />
                  </svg>
                  <span class="mt-2 text-base leading-normal">選擇檔案</span>
                  <input
                    type="file"
                    :value="file"
                    class="hidden"
                    @change="uploadFile"
                  />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
import WxDialogModal from "@/Components/DialogModal";

import WxCommon from "@/Mixins/Common";

export default {
  name: "pickingAreaRefillFeedLocation",

  mixins: [WxCommon],

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
    WxDialogModal,
  },

  data() {
    return {
      file: "",
      errorMessage: "",
      tag: "file",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async uploadFile(e) {
      if (e.target.files.length > 0) {
        try {
          this.loader = true;

          let form = new FormData();
          form.append("file", e.target.files[0]);

          const result = await this.axios.post(
            "/api/sku-bind-location-upload",
            form,
            {
              headers: {
                "content-type": "multipart/form-data",
              },
            }
          );

          this.skuData = result.data;

          this.displayValidation(true, [], `檔案上傳成功`);

          this.loader = false;
          window.open(`/api/sku-bind-location-download/${this.skuData}`);
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }
      }
    },
  },
};
</script>
