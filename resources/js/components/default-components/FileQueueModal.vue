<template>
  <div class="file-queue-modal modal-wrap" v-if="show">
    <div class="modal-title">
      <span>{{ title }}</span>
    </div>
    <div class="modal-body">
      <div class="modal-caption"><span v-html="caption"></span></div>
      <ul class="file-queue-modal-list">
        <li v-for="file in files">
          <div class="file-queue-modal-filename">{{ file.name }}</div>
          <div class="file-queue-modal-progress">
            <div class="file-queue-modal-progress-bar" :style="{width: file.progress + '%'}"></div>
            <div class="file-queue-modal-progress-text">{{ file.progress }}%</div>
          </div>
        </li>
      </ul>

      <div class="modal-controls">
        <button type="button" @click="close"><span>{{ showOk ? 'Ok' : 'Close' }}</span></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      caption: '',
      files: [],
      show: false,
      showOk: false,
      title: ''
    }
  },
  methods: {
    cancel() {

    },
    /**
     * Reset modal values and hide
     */
    close() {
      this.caption = '';
      this.files = [];
      this.title = '';
      this.show = false;
    }
  }
}
</script>