<template>
  <div class="input-modal-wrap modal-wrap" v-if="show">
    <div class="modal-title">
      <span>{{ title }}</span>
      <div class="modal-close" @click="close">
        <i class="file-browser-icon close-icon"></i>
      </div>
    </div>
    <div class="modal-body">
      <div class="modal-caption"><span v-html="caption"></span></div>
      <div class="input-modal-text">
        <input autocomplete="off" :required="!hideInput" v-model="value" @keyup.enter="apply" v-if="!hideInput">
      </div>
      <div class="modal-controls">
        <button name="yes" type="button" @click="apply" @keyup.enter="apply"><span>OK</span></button>
        <button name="no" type="button" @click="close"><span>Cancel</span></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      caption: '',
      data: {},
      show: false,
      value: ''
    };
  },
  emits: ['apply'],
  props: {
    title: {
      type: String,
      default: ''
    },
    hideInput: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    /**
     * Press OK button
     */
    apply() {
      let allow = true
      if (!this.hideInput) {
        allow = typeof this.value === 'string' ? this.value.trim().length > 0 : false
      }
      if (allow) {
        this.$emit('apply', Object.assign(this.data, {value: this.value}))
        this.close()
      }
    },
    /**
     * Reset modal values and hide
     */
    close() {
      this.caption = null;
      this.data = {};
      this.value = '';
      this.show = false;
    }
  }
};
</script>