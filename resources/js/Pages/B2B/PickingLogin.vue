<template>
  <wx-authentication-card>
    <wx-validation-messages
      :display="validation.display"
      :errors="validation.errors"
      :title="validation.title"
    />

    <wx-label for="employeeNo" value="工號" />
    <wx-input
      id="employeeNo"
      type="text"
      class="mt-1 block w-full"
      v-model="employeeNo"
      @update="(val) => (employeeNo = val)"
      required
      autofocus
      @keypress="checkEmployee"
    />

    <wx-spinner :display="loader" />
  </wx-authentication-card>
</template>

<script>
import WxAuthenticationCard from "@/Components/AuthenticationCard";
import WxAuthenticationCardLogo from "@/Components/AuthenticationCardLogo";
import WxButton from "@/Components/Button";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxValidationMessages from "@/Components/ValidationMessages";
import WxCommon from "@/Mixins/Common";
import {shippingServer}from '@/config'

export default {
  components: {
    WxAuthenticationCard,
    WxAuthenticationCardLogo,
    WxButton,
    WxInput,
    WxLabel,
    WxSpinner,
    WxValidationMessages,
  },
  mixins: [WxCommon],
  data() {
    return {
      errors: [],
      employeeNo: "",
      loader: false,
    };
  },

  methods: {
    async checkEmployee(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          await this.axios.post(`${shippingServer}/api/employee/search`, {
            employee_no: this.employeeNo,
          });
          this.displayValidation(false);
          this.loader = false;
          await this.$store.dispatch("setEmployee", this.employeeNo);
          this.$router.push({
            name: "b2bPickingOrderLists",
          });
        } catch (error) {
            console.log(error);
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.loader = false;
        }
      }
    },
  },
};
</script>
