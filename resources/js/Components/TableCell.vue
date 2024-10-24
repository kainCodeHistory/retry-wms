<template>
  <th class="px-2 py-2 border-b border-gray-200 bg-white text-sm">
    <span v-if="value !== null && value !== undefined">{{ value }}</span>
    <span v-else><slot></slot></span>
  </th>
</template>

<script>
export default {
  props: ["value"],
};
</script>
