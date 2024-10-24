<template>
  <app-layout>
    <template #header>
      <h4 class="font-semibold text-xl text-gray-800 leading-tight">
        撿料倉 - 補料 - 補料儲位綁定
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
              <wx-label for="storageBox">
                <template>
                  <sup class="text-red-500 font-bold">*</sup>
                  貨箱條碼
                  <span
                    v-if="designatedLocation"
                    class="text-blue-800 font-bold text-base"
                    >(指定儲位: {{ designatedLocation }})</span
                  >
                </template>
              </wx-label>
              <wx-input
                id="storageBox"
                class="block mt-1 w-full"
                type="text"
                :value="storageBox"
                @update="(val) => (storageBox = val)"
                required
                autofocus
                @keypress="getLocation"
                ref="storageBox"
              />
            </div>

            <div class="mt-4">
              <wx-label for="storageBox">
                <template>
                  <sup class="text-red-500 font-bold">*</sup>
                  下架貨箱條碼
                </template>
              </wx-label>
              <wx-checkbox
                v-for="(box, boxIndex) in boxes"
                :key="boxIndex"
                :checked="box.release"
                :value="box.release"
                @update="(val) => (box.release = val)"
                :text="box.barcode"
                class="ml-3"
              ></wx-checkbox>
            </div>

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
                @keypress="checkLocation"
                ref="location"
              />
            </div>

            <div class="flex items-center justify-end mt-4">
              <wx-button
                class="ml-3"
                color="blue"
                :disabled="
                  storageBox.length === 0 ||
                  location.length === 0 ||
                  designatedLocation !== location
                "
                @click="bindLocation"
                >綁定</wx-button
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

import WxCommon from "@/Mixins/Common";

export default {
  name: "pickingAreaRefillPick",

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
  },

  data() {
    return {
      boxes: [],
      designatedLocation: "",
      location: "",
      refillId: -1,
      storageBox: ""
    };
  },

  beforeRouteEnter(to, from, next) {
    if (!window.WMS.isLoggedIn) {
      window.location.href = "/login";
    }

    next();
  },

  methods: {
    async bindLocation() {
      try {
        this.loader = true;

        const releaseBoxes = this.boxes.filter(box => box.release === true).map(box => box.barcode);
        if (
                (this.boxes.length > 0 && this.location.substring(0, 2) === 'AB' && releaseBoxes.length === 0) ||
                (this.boxes.length > 1 && this.location.substring(0, 2) === 'AA' && releaseBoxes.length === 0)
         ) {
            alert('請選擇要下架的貨箱。');
            this.loader = false;
            return;
        }

        await this.axios.get("/sanctum/csrf-cookie");
        const result = await this.axios.post(
          "/api/picking-area/refill/location",
          {
            location: this.location,
            releaseBoxes: releaseBoxes,
            storageBox: this.storageBox,
          }
        );

        this.reset();
        this.$refs.location.focus();
        this.displayValidation(true, [], "綁定成功。");
        this.loader = false;
      } catch (error) {
        this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
        this.loader = false;
      }
    },

    checkLocation(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        if (this.designatedLocation !== e.target.value.trim()) {
          this.displayValidation(
            true,
            { location: ["儲位錯誤。"] },
            "錯誤訊息。"
          );
          this.$refs.location.focus();
        } else {
          this.displayValidation(false, [], "");
        }
      }
    },

    async getLocation(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 0) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          const result = await this.axios.get(
            `/api/picking-area/refill/location/${e.target.value.trim()}`
          );

          this.refillId = result.data.id;
          this.designatedLocation = result.data.designatedLocation;
          this.boxes = result.data.boxes;

          if (this.boxes.length > 0) {
            this.boxes[0].release = true;
          }
          this.displayValidation(false, [], "");
          this.loader = false;
        } catch (error) {
          this.refillId = -1;
          this.designatedLocation = "";
          this.boxes = [];

          this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
          this.loader = false;
        }
      }
    },

    reset() {
      this.storageBox = "";
      this.location = "";
      this.designatedLocation = "";
      this.refillId = -1;
      this.boxes = [];
    },
  },
};
</script>
