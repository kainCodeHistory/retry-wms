export default {
  data() {
    return {
      keyboardInput: ''
    }
  },
  methods: {
    addKeyupListener() {
      window.addEventListener('keydown', this.handleKeyup)
    },

    removeKeyupListener() {
      window.removeEventListener('keydown', this.handleKeyup)
    },

    resetKeyboardInput() {
      this.keyboardInput = ''
    },

    handleKeyup(e) {
      if (e.key === 'Enter') {
        try {
          this.$emit('keyboard-input', this.keyboardInput)
        } finally {
          this.resetKeyboardInput()
        }
      } else if (e.key.length === 1) {
        this.keyboardInput += e.key
      }
    }
  }
}
