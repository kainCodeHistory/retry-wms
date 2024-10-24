<template>
  <wx-authentication-card>
    <wx-validation-messages
      :display="validation.display"
      :errors="validation.errors"
      :title="validation.title"
    />
    <template #logo>
        <wx-button class="ml-3" style="float:right" color="blue" @click="logout">登出</wx-button>
    </template>
    <div class="mt-4">

      <wx-label for="orderList" value="總表單號" />
      <wx-input
        id="orderList"
        type="text"
        class="mt-1 block w-full"
        v-model="orderList"
        @update="(val) => (orderList = val)"
        required
        ref="orderList"
        @keypress="startPickingItems"
      />
    </div>

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
      orderList: "",
      loader: false,
    };
  },
  beforeRouteEnter(to, from, next) {
    next();
  },

  methods: {
    async startPickingItems(e) {
      if (e.key.toUpperCase() === "ENTER" && e.target.value.trim().length > 3) {
        try {
          this.loader = true;

          await this.axios.get("/sanctum/csrf-cookie");
          await this.$store.dispatch("setOrderList", this.orderList);
          this.$router.push({
            name: "b2bPickingItems",
          });
        } catch (error) {
          this.displayValidation(
            true,
            this.handleAxiosResponseErrorMessages(error),
            "錯誤訊息。"
          );
          this.loader = false;
        }
      }
    },

    async logout() {
      try {
        this.$store.dispatch("clearEmployee");
        this.$router.push({
          name: "b2bPickingLogin",
        });
      } catch (error) {
        this.displayValidation(
          true,
          this.handleAxiosResponseErrorMessages(error),
          "錯誤訊息。"
        );
      }
    },
  },
};
</script>
