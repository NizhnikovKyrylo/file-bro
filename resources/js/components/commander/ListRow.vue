<template>
  <tr
    :class="{inserted: inserted, selected: selected}"
    @click="select"
    @dblclick="open"
  >
    <td :title="`${file.filename}.${file.ext}`">
      <div>
        <i :class="`file-browser-icon ${ fileIcon(file) }`"></i>
        <span>{{ file.filename }}</span>
      </div>
    </td>
    <td>
      <span>{{ file.ext }}</span>
    </td>
    <td>
      <span>{{ fileSize(file.size) }}</span>
    </td>
    <td>
      <span>{{ formatDate(file.mtime) }}</span>
    </td>
  </tr>
</template>

<script>
export default {
  emits: ['openDir', 'openFile', 'selectRow'],
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
     * Double click event
     */
    open() {
      this.file.isDir && this.$emit('openDir', {i: this.index, panel: this.panel})
      !this.file.isDir && this.$emit('openFile', {i: this.index, panel: this.panel})
    },
    /**
     * Click row event
     * @param e
     */
    select(e) {
      this.$emit('selectRow', {i: this.index, panel: this.panel, shift: e.shiftKey, ctrl: e.ctrlKey})
    }
  }
}
</script>