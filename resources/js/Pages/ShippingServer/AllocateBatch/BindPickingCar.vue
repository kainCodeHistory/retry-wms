<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        綁定撿料車
      </h4>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <div>
              <div class="mt-4" v-if="statusCode > 0">
                <div
                  class="bg-indigo-400 border-l-4 border-indigo-600 p-4"
                  role="alert"
                  v-if="statusCode === 200"
                >
                  <p class="font-bold text-white">綁定成功。</p>
                </div>
                <div
                  v-else
                  class="bg-red-400 border-l-4 border-red-600 p-4"
                  role="alert"
                >
                  <p class="font-bold text-white">綁定失敗。</p>
                </div>
              </div>

              <div class="mt-4">
                <wx-label for="pickingCar"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup>撿料車編號</slot
                  ></wx-label
                >
                <wx-input
                  id="pickingCar"
                  class="block mt-1 w-full"
                  type="text"
                  :value="pickingCar"
                  @update="(val) => (pickingCar = val)"
                  autofocus
                  required
                  ref="pickingCar"
                />
              </div>

              <div class="mt-4">
                <wx-label for="pickingCar"
                  ><slot
                    ><sup class="text-red-500 font-bold">*</sup>單格箱條碼</slot
                  ></wx-label
                >
                <wx-input
                  id="wareNo"
                  class="block mt-1 w-full"
                  type="text"
                  :value="wareNo"
                  @update="(val) => (wareNo = val)"
                  autofocus
                  required
                  ref="wareNo"
                  @keypress="addWare"
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
                    <table class="min-w-full leading-normal">
                      <thead>
                        <tr>
                          <wx-table-header value="#"></wx-table-header>
                          <wx-table-header value="單格箱"></wx-table-header>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(ware, index) in wares" :key="index">
                          <wx-table-cell :value="index + 1"></wx-table-cell>
                          <wx-table-cell :value="ware"></wx-table-cell>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-4">
                <wx-button class="ml-3" color="red" @click="reset"
                  >重設</wx-button
                >

                <wx-button class="ml-3" color="blue" @click="bindPickingCar"
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
    WxSpinner,
    WxTableCell,
    WxTableHeader,
  },

  data() {
    return {
      pickingCar: "",
      statusCode: 0,
      wareNo: "",
      wares: [],
      loader: false,
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    addWare(e) {
      if (this.wares.length === 4) {
        alert("一台撿料車最多只能綁定 4 個單格箱。");
        return;
      }

      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        const wareNo = e.target.value.trim();
        if (this.wares.indexOf(wareNo) !== -1) {
          alert(`重複的單格箱(${wareNo}。`);
          return;
        }

        this.wares.push(wareNo);
        this.wareNo = "";
      }
    },

    async bindPickingCar() {
      if (this.pickingCar === "") {
        alert("請掃描撿料車號碼。");
        return;
      }

      if (this.wares.length === 0) {
        alert("請掃描單格箱條碼。");
        return;
      }

      try {
        this.loader = true;
        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/shipping-server/batch/bind-boxes-with-picking-car",
          {
            pickingCar: this.pickingCar,
            wares: this.wares,
          }
        );

        this.pickingCar = "";
        this.statusCode = result.status;
        this.wareNo = "";
        this.wares = [];

        this.loader = false;
      } catch (error) {
        this.statusCode = error.response.status;
        this.loader = false;
      }
    },

    reset() {
      this.pickingCar = "";
      this.statusCode = 0;
      this.wareNo = "";
      this.wares = [];
    },
  },
};
</script>
