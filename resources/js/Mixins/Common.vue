<script>
export default {
  data() {
    return {
      loader: false,
      validation: {
        display: false,
        errors: [],
        title: "儲存成功。",
      },
    };
  },

  methods: {
    displayValidation(display, errors = [], title = "") {
      this.validation.display = display;
      this.validation.errors = errors;
      this.validation.title = title;
    },

    handleAxiosResponseErrorMessages(resp) {
      if (resp.status === 422) {
        return resp.data.errors;
      } else if (resp.status === 404) {
        return [`API URI: ${resp.config.url} Not Found.`];
      } else {
        return [resp.data.messages];
      }
    },
  },
};
</script>

