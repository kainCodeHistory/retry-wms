<template>
  <select
    class="
      rounded
      border-gray-300
      text-indigo-600
      shadow-sm
      focus:border-indigo-300
      focus:ring
      focus:ring-indigo-200
      focus:ring-opacity-50
    "
    :value="value"
    @change="$emit('update', $event.target.value)"
  >
    <option :value="null">{{ default_option }}</option>
    <option v-for="option in options" :value="option.id" :key="option.id">
      {{ option.name }}
    </option>
  </select>
</template>

<script>
export default {
  props: {
    value: null,
    options: {
      type: Array,
      required: true,
    },
    default_option: {
      type: String,
      required: true,
      default: null,
    },
  },
};
</script>
