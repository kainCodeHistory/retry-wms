<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">B類分箱-Beta</h4>
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
              <div
                class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                role="alert"
                v-if="tag == 'allocateB'"
              >
                <p class="font-bold text-white" style="font-size:24px" v-if="tag === 'allocateB'">
                  B類配箱
                  <span v-if="tag === 'allocateB'">{{ box }}</span>
                </p>
              </div>
            </div>

            <div class="mt-4">
              <wx-label for="keyword"><slot> 客製品標籤</slot></wx-label>
              <wx-input
                id="keyword"
                class="block mt-1 w-full"
                type="text"
                :value="keyword"
                @update="(val) => (keyword = val)"
                required
                autofocus
                @keypress="getAllocateRule"
                ref="keyword"
              />
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
import WxSelection from "@/Components/Selection";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";

export default {
  name: "getAllocateRule",

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxSelection,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
  },

  data() {
    return {
      batchId: 0,
      boxNo: "",
      ecnOrderNumber: "",
      ecnShipments: [],
      loader: false,
      keyword: "",
      message: "",
      items: [],
      tag: "",
      validation: {
        display: false,
        errors: [],
        title: "成功。",
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
    async getAllocateRule(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;
          const result = await this.axios.post("/api/allocate-b-customized", {
            keyword: this.keyword,
          });
          this.displayValidation(false);
          this.loader = false;
          this.tag = "allocateB";
          this.keyword = "";
          this.box = result.data;
          this.$refs.keyword.focus();
        } catch (error) {
          const result = JSON.parse(error.request.response);
          this.displayValidation(true, result.errors, "錯誤訊息。");
          this.tag = "";
          this.keyword = "";
          this.$refs.keyword.focus();
          this.loader = false;
        }
      }
    },

    reset() {
      this.batchId = 0;
      this.boxNo = "";
      this.keyword = "";
      this.message = "";
      this.items = [];
      this.shipmentId = 0;
      this.tag = "";
    },
  },
};
</script>
