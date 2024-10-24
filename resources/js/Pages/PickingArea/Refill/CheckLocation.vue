<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        撿料倉 - 補料 - 檢視補料清單
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
                      style="width: 1200px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="#"></wx-table-header>
                          <wx-table-header value="儲位"></wx-table-header>
                          <wx-table-header value="當前貨箱"></wx-table-header>
                          <wx-table-header value="料號"></wx-table-header>
                          <wx-table-header value="新貨箱"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(record, row) in records" :key="row">
                          <wx-table-cell>
                            <template>
                              <wx-button
                                class="ml-3"
                                color="red"
                                @click="deleteRecord(record.id)"
                                >刪除</wx-button
                              >
                            </template>
                          </wx-table-cell>
                          <wx-table-cell
                            :value="record.location"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="record.storage_boxes"
                          ></wx-table-cell>
                          <wx-table-cell :value="record.material_sku"></wx-table-cell>
                          <wx-table-cell :value="record.repl_storage_box"></wx-table-cell>
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
  name: "pickingAreaRefillAddLocation",

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
      location: "",
      records: [],
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },
   mounted() {
    this.getRecord();
  },

  methods: {
    async deleteRecord(id) {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        await this.axios.delete(`/api/picking-area/refill/${id}`);

        const records = this.records;
        const index = records.findIndex((record) => record.id === id);
        records.splice(index, 1);
        this.records = records;

        this.displayValidation(true, [], "刪除成功。");
        this.loader = false;
      } catch (error) {
        this.displayValidation(true, record.id, "錯誤訊息。");
        this.loader = false;
      }
    },

    async getRecord() {

        try {
          this.loader = true;
          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(`/api/picking-area/get-refill-list`);


          this.records = result.data;;
          this.loader = false;
        } catch (error) {
         this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "提示訊息。"
        );
          this.loader = false;
        }

    },
  },
};
</script>
