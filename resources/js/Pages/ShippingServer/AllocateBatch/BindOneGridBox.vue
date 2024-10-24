<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        綁定單格箱
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <div>
              <div class="mt-4" v-if="tag !== ''">
                <div
                  class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                  role="alert"
                  v-if="tag !== 'error'"
                >
                  <p class="font-bold text-white" v-if="tag === 'ecn'">
                    ECN 配箱
                    <span v-if="batchId > 0">(Batch ID: {{ batchId }})</span>
                  </p>
                  <p
                    class="font-bold text-white"
                    v-else-if="tag === 'customized-special'"
                  >
                    客製品特殊規則配箱
                    <span v-if="batchId > 0">(Batch ID: {{ batchId }})</span>
                  </p>
                  <p
                    class="font-bold text-white"
                    v-else-if="tag === 'customized-normal'"
                  >
                    客製品一般規則配箱
                  </p>
                  <p class="font-bold text-white" v-else>一般品配箱</p>
                </div>
                <div
                  v-else
                  class="bg-red-400 border-l-4 border-red-600 p-4"
                  role="alert"
                >
                  <p class="font-bold text-white">
                    {{ message }}
                  </p>
                </div>
              </div>

              <div class="mt-4">
                <wx-label for="keyword"
                  ><slot> 客製品標籤/訂單號碼</slot></wx-label
                >
                <wx-input
                  id="keyword"
                  class="block mt-1 w-full"
                  type="text"
                  :value="keyword"
                  @update="(val) => (keyword = val)"
                  autofocus
                  @keypress="getAllocateRule"
                  ref="keyword"
                />
              </div>

              <div class="mt-4" v-if="ecnShipments.length > 0">
                <wx-label><slot>ECN 訂單尚未配箱清單</slot></wx-label>
                <wx-selection
                    class="block mt-1 w-full"
                    :options="ecnShipments"
                    default_option="請選擇 ECN 訂單"
                    :value="shipmentId"
                    @update="selectShipment"
                ></wx-selection>
              </div>

              <div
                class="mt-4"
                v-if="tag === 'customized-special' || tag === 'ecn'"
              >
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
                    <table class="min-w-full leading-normal">
                      <thead>
                        <tr>
                          <wx-table-header value="#"></wx-table-header>
                          <wx-table-header value="SKU"></wx-table-header>
                          <wx-table-header
                            value="EAN"
                            v-if="tag === 'ecn'"
                          ></wx-table-header>
                          <wx-table-header value="數量"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in items" :key="item.sku">
                          <wx-table-cell>
                            <template>
                              <div v-if="item.pickedQuantity === item.quantity">
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="h-5 w-5"
                                  viewBox="0 0 20 20"
                                  fill="currentColor"
                                >
                                  <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                  />
                                </svg>
                              </div>
                            </template>
                          </wx-table-cell>
                          <wx-table-cell>{{ item.sku }}</wx-table-cell>
                          <wx-table-cell v-if="tag === 'ecn'">{{
                            item.ean
                          }}</wx-table-cell>
                          <wx-table-cell>
                            <template>
                              {{ item.pickedQuantity }}
                              /
                              {{ item.quantity }}
                            </template>
                          </wx-table-cell>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="mt-4" v-if="isAllPicked">
                <wx-label for="boxNo"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup>
                    單格箱條碼</slot
                  ></wx-label
                >
                <wx-input
                  id="boxNo"
                  class="block mt-1 w-full"
                  type="text"
                  :value="boxNo"
                  @update="(val) => (boxNo = val)"
                  required
                  autofocus
                />
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" color="red" @click="reset"
                  >重設</wx-button
                >

                <wx-button
                  class="ml-3"
                  color="blue"
                  v-if="isAllPicked"
                  @click="bindBox"
                  >綁定</wx-button
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
import WxLabel from "@/Components/Label";
import WxSelection from "@/Components/Selection";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";

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
  },

  data() {
    return {
      batchId: 0,
      boxNo: "",
      ecnOrderNumber: '',
      ecnShipments: [],
      loader: false,
      keyword: "",
      message: "",
      items: [],
      shipmentId: 0,
      tag: "",
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  async mounted() {
    await this.getECNShipments();
  },

  computed: {
    isAllPicked: {
      get() {
        const flag = this.items.some(
          (item) => item.pickedQuantity < item.quantity
        );
        return (
          (this.tag === "customized-special" || this.tag === "ecn") && !flag
        );
      },
    },
  },

  methods: {
    async getAllocateRule(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        if (this.tag === "") {
            await this.getRules(e.target.value.trim());
        } else if (this.tag === "customized-special" || this.tag === "ecn") {
          const itemIndex = this.items.findIndex(
            (item) => item.source === e.target.value.trim()
          );
          if (itemIndex === -1) {
            alert(`此客製品不屬於此 Shipment (${e.target.value.trim()})。`);
          } else {
            if (
              this.items[itemIndex].pickedQuantity + 1 >
              this.items[itemIndex].quantity
            ) {
              alert(`重複的客製品 (${e.target.value.trim()})。`);
            } else {
              this.items[itemIndex].pickedQuantity += 1;
            }
          }
        }

        this.keyword = "";
        this.$refs.keyword.focus();
      }
    },

    async bindBox() {
      if (this.shipmentId === 0) {
        alert("請掃描客製品標籤或輸入訂單號碼。");
        return;
      }

      if (this.boxNo === "") {
        alert("請掃描單格箱條碼。");
        return;
      }

      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/shipping-server/batch/allocate-one-grid-box",
          {
            shipmentId: this.shipmentId,
            boxNo: this.boxNo,
          }
        );

        if (result.data.isError) {
          this.tag = "error";
          this.meesage = result.data.message;
        } else {
          this.batchId = result.data.batchId;

          if (this.ecnShipments.length > 0) {
              const idx = this.ecnShipments.findIndex(ecnShipment => ecnShipment.id === this.shipmentId)
              if (idx > -1) {
                  this.ecnShipments.splice(idx, 1);
              }
          }
        }

        this.loader = false;
      } catch (error) {
        this.loader = false;
      }
    },

    async getRules(keyword) {
      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/shipping-server/shipment/get-allocate-rule",
          {
            keyword
          }
        );

        this.tag = result.data.rule.tag;
        this.message = result.data.rule.message;
        this.items = result.data.rule.items;
        this.shipmentId = result.data.rule.shipmentId;

        // 輸入訂單號碼
        if (keyword.includes("#") && this.tag === "customized-special") {
          for (let i = 0; i < this.items.length; i++) {
            this.items[i].pickedQuantity = this.items[i].quantity;
          }
        }

        this.loader = false;
      } catch (error) {
        this.loader = false;
      }
    },

    async getECNShipments() {
      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.get(
          "/api/shipping-server/ecn/shipments"
        );

        this.ecnShipments = result.data.shipments.reduce((arr, shipment) => {
           arr.push({
               id: shipment.shipmentId,
               name: shipment.orderNumber
           });
           return arr;
        }, []);

        this.loader = false;
      } catch (error) {
        this.loader = false;
      }
    },

    async selectShipment(shipmentId) {
        const shipment = this.ecnShipments.find(ecnShipment => parseInt(ecnShipment.id) === parseInt(shipmentId));

        if (shipment) {
            this.ecnOrderNumber = shipment.name;
            await this.getRules(shipment.name);
        } else {
            this.ecnOrderNumber = '';
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
