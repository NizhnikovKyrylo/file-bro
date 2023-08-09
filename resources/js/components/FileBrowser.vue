<template>
  <div class="file-browser-wrap">
    <div class="file-browser-actions-wrap">
      <ul>
        <template v-for="items in panel">
          <li v-for="(button, name) in items">
            <PanelButton :icon="button.icon" :name="name" :title="button.title" :is-active="name === defaultView"/>
          </li>
          <li class="separator-tile"></li>
        </template>
      </ul>
    </div>

    <template v-if="defaultView === 'sideBySide'">
      <SideBySideComponent :routes="routes" @showFileInfo="openFileInfoPopup"/>
    </template>

    <CommonModal ref="fileOverviewModal" :file="overviewFile"/>
  </div>
</template>

<script>
import {storage} from "../storage.js";
import {Requests} from "../mixin/requests.js";
import {FileHelper} from "../mixin/file-helper.js";
import CommonModal from "./Modals/CommonModal.vue";
import PanelButton from "./PanelButton.vue";
import SideBySideComponent from "./SideBySide/SideBySideComponent.vue";

export default {
  components: {CommonModal, SideBySideComponent, PanelButton},
  data() {
    return {
      overviewFile: {
        access: 0,
        changed: 0,
        files: 0,
        folders: 0,
        icon: '',
        isDir: false,
        mod: 0,
        name: '',
        path: '',
        size: 0
      },
      defaultView: 'sideBySide'
    };
  },
  mixins: [FileHelper, Requests],
  name: "FileBrowser",
  props: {
    viewType: {
      type: String,
      default: 'sideBySide'
    },
    panel: {
      type: Array,
      default: [
        {
          refresh: {title: 'Refresh', icon: 'refresh-icon'}
        },
        {
          treeView: {title: 'Switch to the tree view', icon: 'tree-view-icon'},
          listView: {title: 'Switch to the list view', icon: 'list-view-icon'},
          sideBySide: {title: 'Switch to the two panel view', icon: 'side-by-side-view-icon'}
        },
        {
          selectAll: {title: 'Select all files', icon: 'select-all-icon'},
          unselectAll: {title: 'Unselect all files', icon: 'unselect-all-icon'}
        }
      ]
    },
    routes: {
      type: Object,
      default: {
        copy: {method: 'post', url: '/file-browser/copy'},
        create: {method: 'post', url: '/file-browser/create'},
        info: {method: 'post', url: '/file-browser/info'},
        list: {method: 'post', url: '/file-browser/list'},
        move: {method: 'post', url: '/file-browser/move'},
        remove: {method: 'post', url: '/file-browser/remove'},
        search: {method: 'post', url: '/file-browser/search'},
        size: {method: 'post', url: '/file-browser/size'},
        upload: {method: 'post', url: '/file-browser/upload'}
      }
    }
  },
  beforeMount() {
    this.defaultView = this.viewType

    sessionStorage.removeItem('side-by-side')
    // Init "side by side" view options
    if (null === storage.get('side-by-side')) {
      storage.set('side-by-side', {active: 0, left: 0, right: 0})
    }

    this.request(Object.assign(this.routes.list, {data: {path: '/'}})).then(response => {
      // Init files storage
      storage.set('files', {left: response.data, right: response.data})
    });
  },
  methods: {
    openFileInfoPopup(file) {
      let data = {
        access: this.formatDate(file.atime),
        changed: this.formatDate(file.ctime),
        icon: this.fileIcon(file),
        isDir: file.isDir,
        mod: this.formatDate(file.mtime),
        name: file.basename,
        path: file.path + file.basename,
        size: file.recognized ? file.size : 0
      }
      if (file.isDir) {
        data.files = file.files;
        data.folders = file.folders;
      }
      this.overviewFile = data;
      this.$refs.fileOverviewModal.show = true;
    }
  }
};
</script>