<template>
  <button
    :disabled="disabled"
    :type="type"
    class="
      bg-blue-500
      hover:bg-blue-700
      text-white
      font-bold
      py-2
      px-4
      rounded
      focus:outline-none
      focus:shadow-outline
    "
    :class="[
        `bg-${color}-500`,
        `hover:bg-${color}-700`
    ]"
    @click="callback($event)"
  >
    <slot></slot>
  </button>
</template>

<script>
export default {
  props: {
    color: {
        type: String,
        default: 'gray'
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    type: {
      type: String,
      default: "submit",
    },
  },
  methods: {
    callback: async function (e) {
      await this.$emit("click", e);
    },
  },
};
</script>
