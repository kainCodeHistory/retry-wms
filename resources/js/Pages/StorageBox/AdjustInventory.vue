<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ header }}
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
                <wx-label for="eanSku">
                  <template
                    ><sup class="text-red-500 font-bold">*</sup> EAN /
                    SKU</template
                  >
                </wx-label>
                <wx-input
                  id="box"
                  class="block mt-1 w-full"
                  type="text"
                  :value="eanSku"
                  @update="(val) => (eanSku = val)"
                  required
                  autofocus
                  @keypress="getStorageBoxes"
                  ref="eanSku"
                />
              </div>

              <div class="mt-2" v-if="sku !== ''">
                <wx-label value="sku"></wx-label>
                <wx-label
                  :value="sku"
                  style="color: blue; font-weight: bold"
                ></wx-label>
              </div>

              <div class="mt-2" v-if="sku !== ''">
                <wx-label value="品名"></wx-label>
                <wx-label
                  :value="productTitle"
                  style="color: blue; font-weight: bold"
                ></wx-label>
              </div>

              <div class="mt-2" v-if="options.storageBoxes.length > 0">
                <wx-label value="請選擇貨箱"></wx-label>
                <wx-selection
                  class="block mt-1 w-full"
                  :options="options.storageBoxes"
                  default_option="請選擇貨箱"
                  :value="storageBoxId"
                  @update="selectStorageBox"
                ></wx-selection>
              </div>
              <div
                class="mt-2"
                v-if="sku !== '' && options.storageBoxes.length === 0"
              >
                <wx-label>
                  <slot>
                    <template
                      ><span class="text-red-500 font-bold"
                        >無綁定貨箱</span
                      ></template
                    >
                  </slot>
                </wx-label>
              </div>

              <div class="mt-2" v-if="storageBox !== null">
                <wx-label value="目前數量"></wx-label>
                <wx-label
                  :value="storageBox.quantity"
                  style="color: blue; font-weight: bold"
                ></wx-label>
              </div>

              <div class="mt-2">
                <wx-label :value="quantityLabel"></wx-label>
                <wx-input
                  id="adjustQuantity"
                  class="block mt-1 w-full"
                  type="number"
                  :value="adjustQuantity"
                  @update="(val) => (adjustQuantity = val)"
                  required
                  autofocus
                  ref="adjustQuantity"
                />
              </div>

              <div class="mt-2" v-if="options.transfer.length > 0">
                <wx-label><slot>常用備註</slot></wx-label>
                <wx-selection
                  class="block mt-1 w-full"
                  :options="options.transfer"
                  default_option="常用備註"
                  :value="noteId"
                  @update="selectNote"
                ></wx-selection>
              </div>

              <div class="mt-2">
                <wx-label for="note" value="備註"></wx-label>
                <wx-input
                  id="box"
                  class="block mt-1 w-full"
                  type="text"
                  :value="note"
                  @update="(val) => (note = val)"
                  required
                  autofocus
                  ref="note"
                />
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" color="blue" @click="updateQuantity"
                  >儲存</wx-button
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
import WxInputError from "@/Components/InputError";
import WxLabel from "@/Components/Label";
import WxSelection from "@/Components/Selection";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";

import WxCommon from "@/Mixins/Common";

export default {
  name: "locationBatchInOut",

  mixins: [WxCommon],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxInputError,
    WxLabel,
    WxSelection,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
  },

  data() {
    return {
      adjustQuantity: 0,
      eanSku: "",
      eventKey: "adjust",
      note: "",
      noteId: 0,
      productTitle: "",
      options: {
        storageBoxes: [],
        transfer: [],
      },
      quantity: 0,
      sku: "",
      storageBox: null,
      storageBoxId: 0,
      storageBoxes: [],
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  mounted() {
    this.eventKey = this.$route.query.event;
    if (this.eventKey === "transfer_input") {
      this.options.transfer = [
        {
          id: 1,
          name: "製造轉預備",
        },
        {
          id: 2,
          name: "成品轉預備",
        },
        {
          id: 3,
          name: "E1",
        },
        {
          id: 4,
          name: "AC轉入",
        },
        {
          id: 5,
          name: "其他",
        },
      ];

      this.noteId = 1;
      this.note = "製造轉預備";
    } else if (this.eventKey === "transfer_output") {
      this.options.transfer = [
        {
          id: 1,
          name: "預備轉製造",
        },
        {
          id: 2,
          name: "預備轉成品",
        },
        {
          id: 3,
          name: "其他",
        },
      ];

      this.noteId = 1;
      this.note = "預備轉製造";
    } else {
      this.options.transfer = [];
    }
  },

  computed: {
    header() {
      if (this.eventKey === "adjust") {
        return "其他 - 庫存調整";
      } else if (this.eventKey === "item_return") {
        return "入庫 - 品項復歸";
      } else if (this.eventKey === "transfer_input") {
        return "入庫 - 轉倉入庫";
      } else {
        return "出庫 - 轉倉出庫";
      }
    },

    quantityLabel() {
      if (this.eventKey === "adjust") {
        return "調整後數量";
      } else if (this.eventKey === "item_return") {
        return "復歸數量";
      } else if (this.eventKey === "transfer_input") {
        return "入庫數量";
      } else {
        return "出庫數量";
      }
    },
  },

  methods: {
    async getStorageBoxes(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/query/ean-sku/${e.target.value.trim().toUpperCase()}`
          );

          this.storageBoxes = result.data.storageBoxes;

          this.options.storageBoxes = result.data.storageBoxes.reduce(
            (arr, storageBox) => {
              arr.push({
                id: storageBox.id,
                name: `儲位：${
                  storageBox.location === "" ? "未綁定" : storageBox.location
                }，箱號：${storageBox.storage_box}，數量：${
                  storageBox.quantity
                }`,
              });
              return arr;
            },
            []
          );
          this.storageBox = null;
          this.storageBoxId = 0;

          if (this.options.transfer.length > 0) {
            this.noteId = this.options.transfer[0].id;
            this.note = this.options.transfer[0].name;
          } else {
            this.noteId = 0;
            this.note = "";
          }

          this.productTitle = result.data.productTitle;
          this.sku = result.data.sku;
          this.eanSku = "";
          this.quantity = 0;
          this.loader = false;
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.reset();
          this.loader = false;
        }
      }
    },

    selectNote(noteId) {
      if (noteId === "") {
        this.noteId = 0;
        this.note = "";
        return;
      }

      const note = this.options.transfer.find(
        (option) => parseInt(option.id) === parseInt(noteId)
      );
      this.note = note.name;
    },

    selectStorageBox(boxId) {
      if (boxId === "") {
        this.storageBoxId = 0;
        this.storageBox = null;
        return;
      }

      this.storageBoxId = boxId;
      this.storageBox = this.storageBoxes.find(
        (storageBox) => parseInt(storageBox.id) === parseInt(boxId)
      );
    },

    reset() {
      this.adjustQuantity = 0;
      this.eanSku = "";
      this.note = "";
      this.noteId = 0;
      this.productTitle = "";
      this.options.storageBoxes = [];
      this.quantity = 0;
      this.sku = "";
      this.storageBox = null;
      this.storageBoxId = 0;
      this.storageBoxes = [];
    },

    async updateQuantity() {
      if (this.storageBox === null) {
        this.displayValidation(true, ["請選擇貨箱。"], "錯誤訊息。");
        return;
      } else if (this.sku === null || this.sku === "") {
        this.displayValidation(true, [`請輸入 EAN / SKU。`], "錯誤訊息。");
        return;
      } else if (this.adjustQuantity === 0 && this.eventKey !== "adjust") {
        let text = "入庫數量";
        if (this.eventKey === "return_item") {
          text = "復歸數量";
        } else if (this.eventKey === "transfer_output") {
          text = "出庫數量";
        }

        this.displayValidation(true, [`請輸入${text}。`], "錯誤訊息。");
        return;
      } else {
        this.displayValidation(false, [], "");
      }

      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/storage-box/input/quantity",
          {
            adjustQuantity: this.adjustQuantity,
            event: this.eventKey,
            storageBox: this.storageBox.storage_box,
            note: this.note,
          }
        );

        if (result.data.hasError) {
          this.displayValidation(
            true,
            [result.data.errorMessage],
            "錯誤訊息。"
          );
        } else {
          this.displayValidation(
            true,
            [
              `貨箱 (${this.storageBox.storage_box}) 數量調整為 ${result.data.quantity}。`,
            ],
            "儲存成功。"
          );
        }
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
      }

      this.reset();
      this.loader = false;
    },
  },
};
</script>
