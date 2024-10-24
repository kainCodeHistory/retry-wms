<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        查詢B類分箱-Beta
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

            <div
                class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                role="alert"
                v-if="tag == 'allocateB'"
              >
                <p class="font-bold text-white" style="font-size:24px" v-if="tag === 'allocateB'">

                  <span v-if="tag === 'allocateB'">箱號{{ box }} 數量 {{quantity}}</span>
                </p>
              </div>

            <div>
              <div class="mt-4">
                <wx-label for="storageBox"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup> 貨箱條碼</slot
                  ></wx-label
                >
              </div>
              <wx-input
                id="storageBox"
                class="block mt-1 w-full"
                type="text"
                :value="storageBox"
                @update="(val) => (storageBox = val)"
                required
                autofocus
                @keypress="searchStorageBox"
                ref="storageBox"
              />

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
                          <wx-table-header value="sourceId"></wx-table-header>
                          <wx-table-header
                            value="sourceItemId"
                          ></wx-table-header>
                          <wx-table-header value="box"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="allocateBox in allocateBoxes"
                          :key="allocateBox.id"
                        >
                          <wx-table-cell v-if="allocateBox.box == ''" class="bg-red-300"
                            :value="allocateBox.source_id"
                          ></wx-table-cell>
                          <wx-table-cell  v-else
                            :value="allocateBox.source_id"
                          ></wx-table-cell>
                          <wx-table-cell v-if="allocateBox.box == ''" class="bg-red-300"
                            :value="allocateBox.source_item_id"
                          ></wx-table-cell>
                          <wx-table-cell  v-else
                            :value="allocateBox.source_item_id"
                          ></wx-table-cell>
                          <wx-table-cell v-if="allocateBox.box == ''" class="bg-red-300"
                            :value="allocateBox.box"
                          ></wx-table-cell>
                          <wx-table-cell v-else
                            :value="allocateBox.box"
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
  name: "searchAllocateBox",

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
      storageBox: "",
      allocateBoxes: [],
      tag:"",
      box:"",
      quantity:"",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async searchStorageBox(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;
          const result = await this.axios.get(
            `/api/allocate-box/search/${e.target.value.trim().toUpperCase()}`
          );
          this.displayValidation(false);
          this.allocateBoxes = result.data.items;
          this.tag = "allocateB";
          this.box = e.target.value.trim().toUpperCase();
          this.quantity = result.data.quantity;
          this.loader = false;
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.loader = false;
          this.allocateBoxes = [];
        }

        this.$refs.storageBox.focus();
        this.storageBox = "";
      }
    },
  },
};
</script>
