<template>
  <th
    class="
      px-5
      py-3
      border-b-2 border-gray-200
      bg-gray-100
      text-xs
      font-semibold
      text-gray-600
      uppercase
      tracking-wider
    "
    :class="textAlign"
  >
    <span v-if="value">{{ value }}</span>
    <span v-else><slot></slot></span>
  </th>
</template>

<script>
export default {
  props: ["value", "align"],

  computed: {
    textAlign: function() {
      console.log(this.align)
      return this?.align ? this.align : "text-left"
    }
  }
};
</script>
