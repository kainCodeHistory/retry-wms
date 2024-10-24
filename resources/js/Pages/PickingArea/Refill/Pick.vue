<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        撿料倉 - 補料 - 預備倉補料作業
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

            <div v-if="source.id === -1">
              <div class="flex items-center justify-center mt-4">
                <wx-button class="ml-3" type="button" @click="getReplaceRecord"
                  >重新整理</wx-button
                >
              </div>
            </div>
            <div v-else>
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
                  @keypress="updateReplaceRecord"
                  ref="storageBox"
                />
              </div>
            </div>

            <div v-if="source.id > 0">
              <div class="mt-2">
                <wx-label>
                  <template>
                    SKU:
                    <span class="text-blue-800 font-bold text-base">{{
                      source.sku
                    }}</span>
                  </template>
                </wx-label>
              </div>

              <div class="mt-2">
                <wx-label>
                  <template>
                    品名:
                    <span class="text-blue-800 font-bold text-base">{{
                      source.name
                    }}</span>
                  </template>
                </wx-label>
              </div>

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
                      style="width: 600px"
                    >
                      <thead>
                        <tr>
                          <wx-table-header value="倉庫"></wx-table-header>
                          <wx-table-header value="儲位"></wx-table-header>
                          <wx-table-header value="貨箱"></wx-table-header>
                          <wx-table-header value="數量"></wx-table-header>
                          <wx-table-header value="綁定日期"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(location, row) in source.locations"
                          :key="row"
                        >
                          <wx-table-cell :value="location.warehouse">
                          </wx-table-cell>
                          <wx-table-cell
                            :value="location.location"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="location.storageBox"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="location.quantity"
                          ></wx-table-cell>
                          <wx-table-cell
                            :value="location.boundAt"
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
  name: "pickingAreaRefillPick",

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
      outputQuantity: 0,
      source: {
        id: -1,
      },
      storageBox: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  mounted() {
    this.getReplaceRecord();
  },

  methods: {
    async getReplaceRecord() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.get("/api/picking-area/refill");

        this.source = result.data;
        this.displayValidation(false);
        this.loader = false;
      } catch (error) {
        this.source = {
          id: -1,
        };

        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
        this.loader = false;
      }
    },

    async updateRecord(refillId, storageBox, outputQuantity) {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        await this.axios.put("/api/picking-area/refill/quantity", {
          id: refillId,
          outputQuantity,
          storageBox,
        });

        this.storageBox = "";
        this.outputQuantity = 0;
        this.source = {
          id: -1,
        };
        this.displayValidation(true, [], "儲存成功。");
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

    async updateReplaceRecord(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        const location = this.source.locations.find(
          (location) => location.storageBox === e.target.value.trim()
        );
        if (location) {
          await this.updateRecord(
            this.source.id,
            location.storageBox,
            location.quantity
          );

          if (this.source.id === -1) {
            await this.getReplaceRecord();
          }
        } else {
          this.displayValidation(true, ["貨箱條碼錯誤。"], "錯誤訊息。");
          this.storageBox = "";
          this.$refs.storageBox.focus();
        }
      }
    },
  },
};
</script>
