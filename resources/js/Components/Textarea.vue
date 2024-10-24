<template>
  <textarea
    class="
      border-gray-300
      focus:border-indigo-300
      focus:ring focus:ring-indigo-200 focus:ring-opacity-50
      rounded-md
      shadow-sm
    "
    :rows="rows"
    v-model="inputValue"
    ref="input"
  ></textarea>
</template>

<script>
export default {
  props: ["rows", "value"],

  computed: {
      inputValue: {
          get() {
              return this.value
          },

          set(val) {
              this.$emit('update', val)
          }
      }
  },

  methods: {
    focus() {
      this.$refs.input.focus();
    },
  },
};
</script>
