<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        成品倉 - 補料 - 貨箱補料
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
              <wx-label for="inputStorageBox">
                <template>
                  <sup class="text-red-500 font-bold">*</sup>
                  缺料貨箱
                </template>
              </wx-label>
              <wx-input
                id="inputStorageBox"
                class="block mt-1 w-full"
                type="text"
                :value="inputStorageBox"
                @update="(val) => (inputStorageBox = val)"
                required
                autofocus
                @keypress="getMaterial"
                ref="inputStorageBox"
              />
            </div>

            <div class="mt-4" v-if="location !== ''">
              <wx-label for="location" value="儲位"></wx-label>
              <wx-label id="location">
                <span class="text-blue-800 font-bold text-base">{{
                  location
                }}</span>
              </wx-label>
            </div>

            <div class="mt-2" v-if="isEmpty === 0 && sku !== ''">
              <wx-label for="sku" value="SKU"></wx-label>
              <wx-label id="sku">
                <span class="text-blue-800 font-bold text-base">{{ sku }}</span>
              </wx-label>
            </div>

            <div
              class="mt-2"
              v-if="isEmpty === 0 && sku !== '' && productTitle !== ''"
            >
              <wx-label for="productTitle" value="品名"></wx-label>
              <wx-label id="productTitle">
                <span class="text-blue-800 font-bold text-base">{{
                  productTitle
                }}</span>
              </wx-label>
            </div>

            <div
              class="mt-2"
              v-if="isEmpty === 0 && sku !== '' && firstQuantity !== ''"
            >
              <wx-label for="firstQuantity" value="數量"></wx-label>
              <wx-label id="firstQuantity">
                <span class="text-blue-800 font-bold text-base">{{
                  firstQuantity
                }}</span>
              </wx-label>
            </div>

            <div class="mt-2" v-if="options.storageBoxes.length > 0">
              <wx-label value="請選擇轉出貨箱"></wx-label>
              <wx-selection
                class="block mt-1 w-full"
                :options="options.storageBoxes"
                default_option="請選擇轉出貨箱"
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

            <div class="mt-2">
              <wx-label for="outputQuantity">
                <template>
                  <sup class="text-red-500 font-bold">*</sup>
                  數量
                </template>
              </wx-label>
              <wx-input
                id="quantity"
                class="block mt-1 w-full"
                type="number"
                :value="quantity"
                @update="(val) => (quantity = val)"
                required
                autofocus
                ref="quantity"
              />
            </div>

            <div class="flex items-center justify-end mt-4">
              <wx-button
                class="ml-3"
                color="blue"
                :disabled="quantity.length === 0"
                @click="updatQuantity"
                >送出</wx-button
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
import WxCheckbox from "@/Components/Checkbox";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";
import WxSelection from "@/Components/Selection";

import WxCommon from "@/Mixins/Common";

export default {
  name: "b2b5fRefillMaterial",

  mixins: [WxCommon],

  components: {
    AppLayout,
    WxButton,
    WxCheckbox,
    WxInput,
    WxLabel,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxValidationMessages,
    WxSelection,
  },

  data() {
    return {
      inputStorageBox: "",
      ean: "",
      location: "",
      isEmpty: 0,
      productTitle: "",
      firstQuantity: 0,
      sku: "",
      options: {
        storageBoxes: [],
        transfer: [],
      },
      quantity: 0,
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

  methods: {
    async getMaterial(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/b2b/query/storage-box/${e.target.value.trim().toUpperCase()}`
          );

          if (result.data.storageDetail.length > 0) {
            this.batchNo =
              result.data.storageDetail[0].batch_no === null
                ? ""
                : result.data.storageDetail[0].batch_no;
            this.ean =
              result.data.storageDetail[0].ean === null
                ? ""
                : result.data.storageDetail[0].ean;
            this.location = result.data.storageDetail[0].location;
            this.isEmpty = result.data.storageDetail[0].is_empty;
            this.sku =
              result.data.storageDetail[0].material_sku === null
                ? ""
                : result.data.storageDetail[0].material_sku;
            this.productTitle =
              result.data.storageDetail[0].material_name === null
                ? ""
                : result.data.storageDetail[0].material_name;
            this.firstQuantity = result.data.storageDetail[0].quantity;
            this.currentStorage = this.inputStorageBox;
            this.inputStorageBox = this.inputStorageBox;
            this.storageBoxLocation = result.data.skuDetail;
          } else {
            this.reset();
          }

          await this.axios.get("/sanctum/csrf-cookie");
          const result1 = await this.axios.get(
            `/api/query/storage-box-sku/${this.currentStorage}`
          );

          this.storageBoxes = result1.data.storageBoxes;

          this.options.storageBoxes = result1.data.storageBoxes.reduce(
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

    async updatQuantity() {
      try {
        this.loader = true;

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/b2b-5f/picking-area/refill/storage-box",
          {
            inputStorageBox: this.inputStorageBox,
            quantity: this.quantity,
            outputStorageBox: this.storageBox.storage_box,
          }
        );

        this.$refs.inputStorageBox.focus();
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
              `貨箱 (${this.inputStorageBox}) 數量調整為 ${result.data.newInputQuantity}。`,
              `貨箱 (${this.storageBox.storage_box}) 數量調整為 ${result.data.newOutputQuantity}。`,
              ,
            ],
            "儲存成功。"
          );
        }
        this.reset();

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
      this.inputStorageBox = "";
      this.firstQuantity = 0;
      this.secondQuantity = 0;
      this.currentStorage = "";
      this.ean = "";
      this.location = "";
      this.isEmpty = 0;
      this.productTitle = "";
      this.quantity = 0;
      this.sku = "";
      this.options.storageBoxes = [];
      this.storageBox = null;
      this.storageBoxId = 0;
      this.storageBoxes = [];
    },
  },
};
</script>
