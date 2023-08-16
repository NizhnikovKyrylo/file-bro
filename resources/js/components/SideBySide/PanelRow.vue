<template>
  <div
    class="file-browser-panel-content-body-row"
    :title="`${file.basename}\n Modification date/time: ${formatDate(file.mtime)}\n Size: ${file.isDir ? 0 : fileSize(file.size)}`"
    @click="selectRowEvent"
  >
    <div class="file-browser-panel-content-column">
      <i :class="`file-browser-icon ${ fileIcon(file) }`"></i>
      <span>{{ file.filename }}</span>
    </div>
    <div class="file-browser-panel-content-column">
      <span>{{ file.ext }}</span>
    </div>
    <div class="file-browser-panel-content-column">
      <span>{{ file.isDir && !file.hasOwnProperty('recognized') ? '&lt;DIR&gt;' : fileSize(file.size) }}</span>
    </div>
    <div class="file-browser-panel-content-column">
      <span>{{ formatDate(file.mtime) }}</span>
    </div>
  </div>
</template>

<script>
import {FileHelper} from "../../mixin/file-helper.js";

export default {
  data() {
    return {
      event: null,
      timer: null
    }
  },
  mixins: [FileHelper],
  emits: ['openFile', 'selectRow'],
  methods: {
    selectRowEvent(e) {
      if (e.detail === 1) {
        this.$emit('selectRow', this.$el)
      }
      if (e.detail > 1) {
        this.$emit('openFile', this.$el)
      }
    }
  },
  props: {
    file: {
      type: Object,
      required: true
    },
    index: {
      type: Number,
      required: true
    },
    side: {
      type: Number,
      required: true
    }
  }
}
</script>