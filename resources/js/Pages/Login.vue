<template>
  <wx-authentication-card>
    <template #logo>
      <wx-authentication-card-logo />
    </template>

    <wx-validation-messages class="mb-4" :errors="errors" title="錯誤訊息" />

    <form>
      <div>
        <wx-label for="account" value="帳號" />
        <wx-input
          id="account"
          type="email"
          class="mt-1 block w-full"
          v-model="form.account"
          @update="(val) => (form.account = val)"
          required
          autofocus
        />
      </div>

      <div class="mt-4">
        <wx-label for="password" value="密碼" />
        <wx-input
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          @update="(val) => (form.password = val)"
          required
          autofocus
        />
      </div>

      <div class="flex items-center justify-end mt-4">
        <wx-button
          class="ml-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="handleSubmit"
        >
          登入
        </wx-button>
      </div>
    </form>

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
  data() {
    return {
      errors: [],
      form: {
        account: "",
        password: "",
      },
      loader: false,
    };
  },

  methods: {
    async handleSubmit(e) {
      e.preventDefault();
      if (this.form.password.length > 0) {
        this.loader = true;
        try {
          await this.axios.get("/sanctum/csrf-cookie");
          await this.axios.post("/api/login", {
            account: this.form.account,
            password: this.form.password,
          });

          this.loader = false;
          this.$router.go("/");
        } catch (error) {
          const result = JSON.parse(error.request.response);
          this.errors = result.errors;
          this.form.password = "";
          this.loader = false;
        }
      }
    },
  },

  beforeRouteEnter(to, from, next) {
    if (window.WMS.isLoggedIn) {
      return next("/");
    }
    next();
  },
};
</script>
