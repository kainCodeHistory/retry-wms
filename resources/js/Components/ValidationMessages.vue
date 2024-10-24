<template>
  <div
    class="p-4 rounded flex items-start my-4 shadow-lg mx-auto"
    :class="{
      'bg-yellow-50': hasErrors,
      'bg-green-50': !hasErrors,
      'text-yellow-600': hasErrors,
      'text-green-600': !hasErrors,
    }"
    v-if="hasErrors || display"
  >
    <div class="px-3">
      <h3
        class="text-yellow-800 font-semibold tracking-wider"
        :class="{ 'text-yellow-800': hasErrors, 'text-green-800': !hasErrors }"
      >
        {{ title }}
      </h3>
      <p class="pt-2 text-yellow-700" v-for="(error, key) in errors" :key="key">

        {{ Array.isArray(error) ? error.join(",") : error }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    display: {
      type: Boolean,
      default: false,
    },
    errors: {
      type: Array | Object,
      default: [],
    },
    title: {
      type: String,
      default: "儲存成功。",
    },
  },
  computed: {
    hasErrors() {
      return Object.keys(this.errors).length > 0;
    },
  },
};
</script>
