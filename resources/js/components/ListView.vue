<template>
  <div class="file-browser-list-view-wrap">
    <ul>
      <li v-for="file in files.list">
        <div class="file-wrap">
          {{ file.filename }}
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      files: {
        list: [],
        selected: []
      }
    };
  },
  props: {
    routes: {
      type: Object,
      required: true
    }
  },
  inject: {
    request: 'request',
    sortFiles: 'sortFiles'
  },
  beforeMount() {
    this.request(Object.assign(this.routes.list, {data: {path: '/'}})).then(response => {
      if (200 === response.status) {
        // Retrieve file fist from request
        const files = response.data;
        // Sort files by name
        files.sort((a, b) => this.sortFiles(a, b));
        this.files.list = files
      }
    })
  }
}
</script>