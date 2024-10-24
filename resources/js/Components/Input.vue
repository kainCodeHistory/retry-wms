<template>
  <input
    class="
      border-gray-300
      focus:border-indigo-300
      focus:ring
      focus:ring-indigo-200
      focus:ring-opacity-50
      rounded-md
      shadow-sm
    "
    v-model="inputValue"
    @blur="$emit('blur', $event.target.value, row)"
    @keypress="$emit('keypress', $event, row)"
    @change="$emit('change', $event.target.value, row)"
    ref="input"
  />
</template>

<script>
export default {
  props: ["row", "value"],

  computed: {
    inputValue: {
      get() {
        return this.value;
      },

      set(val) {
        this.$emit("update", val);
      },
    },
  },

  methods: {
    focus() {
      this.$refs.input.focus();
    },
  },
};
</script>
