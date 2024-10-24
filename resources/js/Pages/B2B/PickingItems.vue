<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
          <wx-validation-messages :display="validation.display" :errors="validation.errors" :title="validation.title" />

          <div>
            <div class="mt-4">
              <wx-label for="orderList"><template><sup class="text-red-500 font-bold">*</sup>總表單號</template></wx-label>
              <wx-label id="orderList" class="block mt-1 w-full" type="text" :value="orderList" required />
            </div>
            <div class="mt-4">
              <wx-label for="sku"><template><sup class="text-red-500 font-bold">*</sup> SKU/EAN</template></wx-label>
              <wx-input id="sku" class="block mt-1 w-full" type="text" :value="sku" @update="(val) => (sku = val)"
                required autofocus readonly ref="sku" />
            </div>

            <div class="mt-2">
              <wx-label :value="materialName"></wx-label>
            </div>

            <div class="mt-4">
              <wx-label for="quantity"><template><sup class="text-red-500 font-bold">*</sup> 數量</template></wx-label>
              <wx-input id="quantity" class="block mt-1 w-full" type="number" :value="quantity"
                @update="(val) => (quantity = val)" required />
            </div>

            <div class="mt-4">
              <wx-button class="ml-3" color="red" @click="finishOrder">完成撿料</wx-button>
              <wx-button class="ml-3" style="float: right" color="blue" @click="updateQuantity">儲存</wx-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <wx-spinner :display="loader" />
  </div>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout"

import WxButton from "@/Components/Button"
import WxInput from "@/Components/Input"
import WxLabel from "@/Components/Label"
import WxSpinner from "@/Components/Spinner"
import WxTableCell from "@/Components/TableCell"
import WxTableHeader from "@/Components/TableHeader"
import WxValidationMessages from "@/Components/ValidationMessages"
import WxSelection from "@/Components/Selection"

import WxCommon from "@/Mixins/Common"
import KeyBoardListenser from "@/Mixins/KeyboardListenerMixin"

export default {
  name: "b2bPickingItems",

  mixins: [WxCommon, KeyBoardListenser],

  components: {
    AppLayout,
    WxButton,
    WxInput,
    WxLabel,
    WxSpinner,
    WxTableCell,
    WxTableHeader,
    WxSelection,
    WxValidationMessages,
  },

  data() {
    return {
      materialName: "",
      quantity: "",
      sku: "",
      employee: "",
      orderList: localStorage.getItem("orderList"),
      loader: false,
    }
  },

  beforeRouteEnter(to, from, next) {
    next()
  },
  async mounted() {
    await this.$on("keyboard-input", async (sku) => {
      this.getMaterialName(sku)
    })
    this.addKeyupListener()
  },

  methods: {
    async getMaterialName(sku) {
      try {
        this.loader = true

        await this.axios.get("/sanctum/csrf-cookie")
        const result = await this.axios.get(`/api/material/${sku}`)
        this.materialName = result.data.name
        this.sku = result.data.sku

        this.loader = false
      } catch (error) {
        this.loader = false
        this.displayValidation(false)
      }
    },

    reset() {
      this.materialName = ""
      this.quantity = ""
      this.sku = ""
      this.resetKeyboardInput()
    },

    async updateQuantity() {
      try {
        this.loader = true
        this.employee = localStorage.getItem("employee")
        this.orderList = localStorage.getItem("orderList")

        await this.axios.get("/sanctum/csrf-cookie")

        const result = await this.axios.post(
          `api/b2b/paper/picked-item`,
          {
            employee: this.employee,
            orderList: this.orderList,
            sku: this.sku,
            quantity: this.quantity,
          }
        )

        this.$refs.sku.focus()
        this.displayValidation(true, ["SKU：" + result.data.sku, "數量：" + result.data.quantity], "儲存成功。")

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

    async finishOrder() {
      try {
        this.$store.dispatch("clearOrderList")
        this.$router.push({
          name: "b2bPickingOrderLists",
        })
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        )
      }
    },
  },
}
</script>
