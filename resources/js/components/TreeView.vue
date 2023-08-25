<template>
  <div class="file-browser-tree-view-wrap">
    <div class="file-browser-tree-folder-list">
      <ul>
        <li v-for="folder in folders.list" :title="folder.path + folder.filename">
          <div class="folder-wrap" @click="getFolderContent">
            <i class="close file-browser-icon folder-icon"></i>
            <i class="open file-browser-icon folder-open-icon"></i>
            <div class="file-name">
              {{ folder.filename }}
            </div>
          </div>
        </li>
      </ul>
    </div>

    <div class="file-browser-tree-file-list">
      <ul>
        <li v-for="file in files.list">
          <div class="file-name">
            {{ file.filename }}
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      files: {
        list: [],
        selected: []
      },
      folders: {
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
  methods: {
    getFolderContent(e) {
      e.target.closest('li').classList.toggle('active');
    }
  },
  beforeMount() {
    this.request(Object.assign(this.routes.list, {data: {path: '/'}})).then(response => {
      if (200 === response.status) {
        // Retrieve file fist from request
        const files = response.data;
        // Sort files by name
        files.sort((a, b) => this.sortFiles(a, b));
        // Separate files and folders
        for (let i = 0, n = files.length; i < n; i++) {
          if (files[i].isDir) {
            this.folders.list.push(files[i]);
          } else {
            this.files.list.push(files[i]);
          }
        }
      }
    });
  }
};
</script>