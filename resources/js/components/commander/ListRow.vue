<template>
  <div class="commander-file-list-row">
    <div class="commander-file-list-column">
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
  props: {
    file: {
      type: Object,
      required: true
    }
  },
  inject: {
    fileIcon: 'fileIcon',
    fileSize: 'fileSize',
    formatDate: 'formatDate'
  }
}
</script>