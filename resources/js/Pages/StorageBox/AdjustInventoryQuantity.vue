<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        其他 - 庫存調整
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <wx-validation-messages :display="validation.display" :errors="validation.errors" :title="validation.title" />

            <div>
              <div class="mt-4">
                <wx-label for="eanSku">
                  <template><sup class="text-red-500 font-bold">*</sup> EAN /
                    SKU</template>
                </wx-label>
                <wx-input id="box" class="block mt-1 w-full" type="text" :value="eanSku" @update="(val) => (eanSku = val)"
                  required autofocus @keypress="getStorageBoxes" ref="eanSku" />
              </div>

              <div class="mt-2" v-if="sku !== ''">
                <wx-label value="sku"></wx-label>
                <wx-label :value="sku" style="color: blue; font-weight: bold"></wx-label>
              </div>

              <div class="mt-2" v-if="sku !== ''">
                <wx-label value="品名"></wx-label>
                <wx-label :value="productTitle" style="color: blue; font-weight: bold"></wx-label>
              </div>

              <div class="mt-2" v-if="quantity !== undefined">
                <wx-label value="目前數量"></wx-label>
                <wx-label :value="quantity" style="color: blue; font-weight: bold"></wx-label>
              </div>

              <div class="mt-2">
                <wx-label value="調整數量"></wx-label>
                <wx-input id="adjustQuantity" class="block mt-1 w-full" type="number" :value="adjustQuantity"
                  @update="(val) => (adjustQuantity = val)" required autofocus ref="adjustQuantity" />
              </div>

              <div class="mt-2">
                <wx-label for="note" value="備註"></wx-label>
                <wx-input id="box" class="block mt-1 w-full" type="text" :value="note" @update="(val) => (note = val)"
                  required autofocus ref="note" />
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" color="blue" @click="updateQuantity">儲存</wx-button>
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
import AppLayout from "@/Layouts/AppLayout"

import WxButton from "@/Components/Button"
import WxInput from "@/Components/Input"
import WxInputError from "@/Components/InputError"
import WxLabel from "@/Components/Label"
import WxSelection from "@/Components/Selection"
import WxSpinner from "@/Components/Spinner"
import WxTableCell from "@/Components/TableCell"
import WxTableHeader from "@/Components/TableHeader"
import WxValidationMessages from "@/Components/ValidationMessages"

import WxCommon from "@/Mixins/Common"

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
      note: "",
      noteId: 0,
      productTitle: "",
      quantity: undefined,
      sku: ""
    }
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login"
    }

    next()
  },

  methods: {
    async getStorageBoxes(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true

          await this.axios.get("/sanctum/csrf-cookie")
          const result = await this.axios.get(
            `/api/stock/${e.target.value.trim().toUpperCase()}`
          )
          this.productTitle = result.data.product_title
          this.sku = result.data.sku
          this.quantity = result.data?.current_quantity || 0

        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          )
          this.reset()
        } finally {
          this.loader = false
        }
      }
    },

    reset() {
      this.adjustQuantity = 0
      this.eanSku = ""
      this.note = ""
      this.noteId = 0
      this.productTitle = ""
      this.quantity = 0
      this.sku = ""
      this.storageBox = null
      this.storageBoxId = 0
      this.storageBoxes = []
    },

    async updateQuantity() {
      if (this.sku === null || this.sku === "") {
        this.displayValidation(true, [`請輸入 EAN / SKU。`], "錯誤訊息。")
        return
      } else {
        this.displayValidation(false, [], "")
      }

      try {
        this.loader = true

        await this.axios.get("/sanctum/csrf-cookie")
        const result = await this.axios.put(
          `api/stock/${this.eanSku}`,
          {
            current_quantity: this.quantity,
            adjusted_quantity: this.adjustQuantity,
            ean_sku: this.eanSku,
            note: this.note,
          }
        )

        if (result.data?.hasError) {
          this.displayValidation(
            true,
            [result.data?.errorMessage || '發生不明錯誤！'],
            "錯誤訊息。"
          )
        } else {
          this.displayValidation(
            true,
            [],
            `儲存成功！庫存數量調整為 ${this.adjustQuantity}。`
          )
        }
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        )
      } finally {
        this.reset()
        this.loader = false
      }


    },
  },
}
</script>
