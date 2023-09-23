<template>
  <div class="file-info-modal-wrap modal-wrap" v-if="show">
    <div class="modal-title">
      <span>Properties</span>
      <div class="modal-close" @click="close">
        <i class="file-browser-icon close-icon"></i>
      </div>
    </div>

    <div class="modal-body">
      <table class="file-info-properties" v-if="file">
        <tr>
          <td class="file-info-image"><i :class="`file-browser-icon ${fileIcon(file)}`"></i></td>
          <td>{{ file.basename }}</td>
        </tr>
        <tr>
          <td><span>Path:</span></td>
          <td><span>{{ file.path }}</span></td>
        </tr>
        <tr>
          <td><span>Type:</span></td>
          <td><span>{{ file.isDir ? 'Folder' : 'File' }}</span></td>
        </tr>
        <tr>
          <td><span>Size:</span></td>
          <td><span>{{ file.size }}</span></td>
        </tr>
        <tr v-if="file.isDir">
          <td><span>Contains:</span></td>
          <td><span>folders: {{ file.folders }};&nbsp;&nbsp;files: {{ file.files }};</span></td>
        </tr>
        <tr>
          <td><span>Last access:</span></td>
          <td><span>{{ formatDate(file.atime) }}</span></td>
        </tr>
        <tr>
          <td><span>Last modification:</span></td>
          <td><span>{{ formatDate(file.mtime) }}</span></td>
        </tr>
        <tr>
          <td><span>Created at:</span></td>
          <td><span>{{ formatDate(file.ctime) }}</span></td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      file: null,
      show: false
    };
  },
  methods: {
    /**
     * Reset modal values and hide
     */
    close() {
      this.data = {};
      this.show = false;
    }
  },
  inject: {
    fileIcon: 'fileIcon',
    formatDate: 'formatDate'
  },
};
</script>