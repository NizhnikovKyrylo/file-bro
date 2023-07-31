<template>
  <tr
    :title="`${file.basename}\n Modification date/time: ${formatDate(file.mtime)}\n Size: ${file.isDir ? 0 : fileSize(file.size)}`"
    @click="selectRowEvent"
  >
    <td><i :class="`file-browser-icon ${ fileIcon(file.isDir, file['mime-type']) }`"></i></td>
    <td><span>{{ file.filename }}</span></td>
    <td><span>{{ file.ext }}</span></td>
    <td><span>{{ file.isDir ? '&lt;DIR&gt;' : fileSize(file.size) }}</span></td>
    <td><span>{{ formatDate(file.mtime) }}</span></td>
  </tr>
</template>

<script>
import {FileHelper} from "../../mixin/file-helper.js";

export default {
  mixins: [FileHelper],
  emits: ['selectRow'],
  methods: {
    selectRowEvent() {
      this.$emit('selectRow', this.$el)
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