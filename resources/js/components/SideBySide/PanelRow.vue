<template>
  <tr :title="`${file.basename}\n Modification date/time: ${formatDate}\n Size: ${file.isDir ? 0 : fileSize}`" @click="selectRow">
    <td><i :class="`file-browser-icon ${ fileIcon }`"></i></td>
    <td><span>{{ file.filename }}</span></td>
    <td><span>{{ file.ext }}</span></td>
    <td><span>{{ file.isDir ? '&lt;DIR&gt;' : fileSize }}</span></td>
    <td><span>{{ formatDate }}</span></td>
  </tr>
</template>

<script>
export default {
  computed: {
    /**
     * Format bytes as human-readable text.
     * @returns {string}
     */
    fileSize() {
      const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      const value = Math.floor(Math.log(Math.abs(this.file.size)) / Math.log(1024));
      return (this.file.size / Math.pow(1024, value)).toFixed(1) + ' ' + units[value];
    },
    /**
     * Get file icon by file properties
     * @returns {string}
     */
    fileIcon() {
      if (this.file.isDir) {
        return 'folder-icon';
      } else {
        const mimeTypes = this.$parent.$parent.$parent.mimeTypes
        const icon = Object.keys(mimeTypes).find(icon => mimeTypes[icon].includes(this.file['mime-type']));
        return icon ? icon : 'file-regular';
      }
    },
    /**
     * Convert Unix timestamp to date format j.M.Y H:i:s
     *
     * @returns {string}
     */
    formatDate() {
      const date = new Date(this.file.mtime * 1000);
      return `${date.getDate()}.${date.toLocaleString('default', {month: 'short'})}.${date.getFullYear()}` + ` ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
    }
  },
  methods: {
    selectRow() {
      if (null !== this.$el.closest('tbody')) {
        const nodes = this.$el.closest('.file-browser-panels-wrap').querySelectorAll('tr')
        for (let i = 0, n = nodes.length; i < n; i++) {
          nodes[i].classList.remove('select')
        }
        this.$el.classList.add('select')
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