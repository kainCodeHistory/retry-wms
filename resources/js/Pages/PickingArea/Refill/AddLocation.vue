<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        撿料倉 - 補料 - 新增需補料儲位
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
                  @keypress="addRecord"
                  ref="location"
                />
              </div>

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
                          <wx-table-header value="貨箱"></wx-table-header>
                          <wx-table-header value="料號"></wx-table-header>
                          <wx-table-header value="品名"></wx-table-header>
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
                            :value="record.storageBoxes.join(',')"
                          ></wx-table-cell>
                          <wx-table-cell :value="record.sku"></wx-table-cell>
                          <wx-table-cell :value="record.name"></wx-table-cell>
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

        this.$refs.location.focus();
        this.displayValidation(true, [], "刪除成功。");
        this.loader = false;
      } catch (error) {
        this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
        this.loader = false;
      }
    },

    async addRecord(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.post(`/api/picking-area/refill`, {
            location: e.target.value.trim(),
          });

          const records = this.records;
          const recordId = result.data.id;
          const index = records.findIndex((record) => record.id === recordId);
          if (index === -1) {
            records.push(result.data);
          } else {
            records[index] = result.data;
          }
          this.records = records;
          this.location = "";
          this.$refs.location.focus();
          this.displayValidation(true, [], "新增成功。");
          this.loader = false;
        } catch (error) {
          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }
      }
    },
  },
};
</script>
