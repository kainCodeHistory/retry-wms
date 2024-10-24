<template>
  <label>
    <input
      type="checkbox"
      :value="value"
      v-model="proxyChecked"
      class="
        rounded
        border-gray-300
        text-indigo-600
        shadow-sm
        focus:border-indigo-300
        focus:ring focus:ring-indigo-200 focus:ring-opacity-50
      "
    />
    <span class="font-medium text-sm text-gray-700 ml-1">{{ text }}</span>
  </label>
</template>

<script>
export default {
  props: {
    checked: {
      type: [Array, Boolean],
      default: false,
    },
    value: {
      default: null,
    },
    text: {
      type: String,
      default: "",
    },
  },

  computed: {
    proxyChecked: {
      get() {
        return this.checked;
      },

      set(val) {
        this.$emit("update", val);
      },
    },
  },
};
</script>
