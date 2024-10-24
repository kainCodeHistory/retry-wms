<template>
  <label>
    <input
      type="radio"
      :value="value"
      :checked="isChecked"
      class="
        rounded
        border-gray-300
        text-indigo-600
        shadow-sm
        focus:border-indigo-300
        focus:ring focus:ring-indigo-200 focus:ring-opacity-50
      "
      @change="$emit('change', $event.target.value)"
    />
    <span class="font-medium text-sm text-gray-700 ml-1">{{ text }}</span>
  </label>
</template>

<script>
export default {
  props: {
    modelValue: {
      default: "",
    },
    value: {
      default: undefined,
    },
    text: {
      type: String,
      default: "",
    },
  },

  computed: {
    isChecked() {
        return this.modelValue === this.value;
    }
  },
};
</script>
