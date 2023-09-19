<template>
  <div class="commander-file-list-row" :class="{inserted: inserted, selected: selected}" @click="selectRow">
    <div class="commander-file-list-column" :title="`${file.filename}.${file.ext}`">
      <i :class="`file-browser-icon ${ fileIcon(file) }`"></i>
      <span>{{ file.filename }}</span>
    </div>
    <div class="commander-file-list-column">
      <span>{{ file.ext }}</span>
    </div>
    <div class="commander-file-list-column">
      <span>{{ fileSize(file.size) }}</span>
    </div>
    <div class="commander-file-list-column">
      <span>{{ formatDate(file.mtime) }}</span>
    </div>
  </div>
</template>

<script>
export default {
  emits: ['selectRow'],
  props: {
    file: {
      type: Object,
      required: true
    },
    index: {
      type: Number,
      required: true
    },
    inserted: {
      type: Boolean,
      default: false
    },
    panel: {
      type: String,
      required: true
    },
    selected: {
      type: Boolean,
      default: false
    }
  },
  inject: {
    fileIcon: 'fileIcon',
    fileSize: 'fileSize',
    formatDate: 'formatDate'
  },
  methods: {
    /**
     * Click row event
     */
    selectRow() {
      this.$emit('selectRow', {i: this.index, panel: this.panel})
    }
  }
}
</script>