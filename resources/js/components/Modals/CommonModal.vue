<template>
  <div class="file-browser-modal-wrap" :class="{active: show}">
    <div class="file-browser-modal-close" @click="closeModal">&times;</div>

    <div class="file-browser-modal-content-wrap">
      <div class="file-browser-modal-content-file-property">
        <span><i :class="`file-browser-icon ${file.icon}`"></i></span>
        <span>{{ file.name }}</span>
      </div>

      <div class="file-browser-modal-content-file-property">
        <span>Path:</span>
        <span>{{ file.path }}</span>
      </div>
      <div class="file-browser-modal-content-file-property">
        <span>Type:</span>
        <span>{{ file.isDir ? 'Folder' : 'File' }}</span>
      </div>
      <div class="file-browser-modal-content-file-property">
        <span>Size:</span>
        <span>{{ file.size }}</span>
      </div>
      <div class="file-browser-modal-content-file-property" v-if="file.isDir">
        <span>Files:</span>
        <span>{{ file.files }}</span>
      </div>
      <div class="file-browser-modal-content-file-property" v-if="file.isDir">
        <span>Folders:</span>
        <span>{{ file.folders }}</span>
      </div>
      <div class="file-browser-modal-content-file-property">
        <span>Last access:</span>
        <span>{{ file.access }}</span>
      </div>
      <div class="file-browser-modal-content-file-property">
        <span>Last modification:</span>
        <span>{{ file.mod }}</span>
      </div>
      <div class="file-browser-modal-content-file-property">
        <span>Last change:</span>
        <span>{{ file.changed }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show: false
    }
  },
  props: ['file'],
  methods: {
    closeModal() {
      this.show = false
    }
  }
}
</script>