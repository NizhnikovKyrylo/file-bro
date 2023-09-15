<template>
  <div class="input-modal-wrap" v-if="show">
    <div class="input-modal-title">
      <span>{{ title }}</span>
      <div class="input-modal-close" @click="close">
        <i class="file-browser-icon close-icon"></i>
      </div>
    </div>
    <div class="input-modal-body">
      <div class="input-modal-caption"><span>{{ caption }}:</span></div>
      <div class="input-modal-text">
        <input autocomplete="off" required v-model="value" @keyup.enter="apply">
      </div>
      <div class="input-modal-controls">
        <button name="yes" type="button" @click="apply"><span>OK</span></button>
        <button name="no" type="button" @click="close"><span>Cancel</span></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      caption: null,
      data: {},
      show: false,
      title: '',
      value: ''
    };
  },
  emits: ['apply'],
  methods: {
    /**
     * Press OK button
     */
    apply() {
      this.$emit('apply', Object.assign(this.data, {value: this.value}))
      this.close()
    },
    /**
     * Reset modal values and hide
     */
    close() {
      this.caption = null;
      this.data = {};
      this.title = '';
      this.value = '';
      this.show = false;
    }
  }
};
</script>