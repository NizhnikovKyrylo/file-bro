<template>
  <div class="file-browser-wrap">
    <input
      class="ready"
      multiple
      ref="fileUpload"
      style="display: none"
      type="file"
      @cancel="onFileChanged"
      @change="onFileChanged"
    >
    <div class="top-panel-wrap">
      <ul>
        <li v-for="(button, name) in topPanelButtons" :class="{active: name === view.active}">
          <button
            tabindex="-1"
            type="button"
            :name="name"
            :title="button.name"
            @click="button.callback"
          >
            <i :class="`file-browser-icon ${button.icon}`"></i>
          </button>
        </li>
      </ul>

      <input class="path-input" name="path" value="/" v-if="view.active === 'list'"/>
    </div>

    <TreeView v-if="view.active === 'tree'" :routes="routes" ref="treeView"/>

    <ListView v-if="view.active === 'list'" :routes="routes" ref="listView"/>

    <CommanderView v-if="view.active === 'commander'" :routes="routes" ref="commanderView"/>
  </div>
</template>

<script>
import axios from "axios";
import TreeView from "./components/TreeView.vue";
import ListView from "./components/ListView.vue";
import CommanderView from "./components/CommanderView.vue";

export default {
  components: {CommanderView, ListView, TreeView},
  data() {
    return {
      config: {},
      mimeTypes: {
        // archive
        'file-archive': ['application/gzip', 'application/java-archive', 'application/rar', 'application/zip', 'application/x-bzip2'],
        // audio
        'file-audio': ['audio/aac', 'audio/ogg', 'audio/flac', 'audio/midi', 'audio/mpeg', 'audio/x-wav', 'audio/aifc', 'audio/x-aiff'],
        // database
        'file-database': [
          'text/csv',
          'application/csv',
          'application/vnd.sun.xml.base',
          'application/vnd.oasis.opendocument.base',
          'application/vnd.oasis.opendocument.database',
          'application/sql'
        ],
        //font
        'file-text': ['font/ttf', 'font/woff', 'font/woff2', 'font/opentype', 'application/vnd.ms-fontobject'],
        //images
        'file-image': ['image/gif', 'image/jp2', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/tiff', 'image/bmp'],
        // pdf
        'file-pdf': ['application/pdf'],
        // scripts
        'file-code': [
          'application/ecmascript',
          'application/hta',
          'application/xhtml+xml',
          'application/xml',
          'application/xslt+xml',
          'text/css',
          'text/x-csrc',
          'text/x-c++src',
          'application/x-asp',
          'text/x-python'
        ],
        // spreadsheet
        'file-cell': [
          'application/vnd.ms-excel',
          'application/vnd.ms-excel.sheet.macroEnabled.12',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'application/vnd.oasis.opendocument.spreadsheet'
        ],
        // text
        'file-alt': ['text/plain', 'text/html', 'text/markdown', 'application/json', 'application/x-x509-ca-cert'],
        // text processor
        'file-word': [
          'application/msword',
          'application/rtf',
          'text/rtf',
          'text/richtext',
          'application/vnd.ms-word.document.macroEnabled.12',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'application/vnd.oasis.opendocument.text',
          'application/vnd.oasis.opendocument.text-master',
          'application/abiword'
        ],
        // video
        'file-video': [
          'video/avi',
          'video/mpeg',
          'video/mp4',
          'video/quicktime',
          'video/ogg',
          'video/webm',
          'video/x-flv',
          'video/x-msvideo',
          'video/x-matroska',
          'video/x-ms-wmv'
        ]
      },
      routes: {
        config: {method: 'post', url: '/file-browser/config'},
        copy: {method: 'post', url: '/file-browser/copy'},
        create: {method: 'post', url: '/file-browser/create'},
        info: {method: 'post', url: '/file-browser/info'},
        list: {method: 'post', url: '/file-browser/list'},
        move: {method: 'post', url: '/file-browser/move'},
        remove: {method: 'post', url: '/file-browser/remove'},
        search: {method: 'post', url: '/file-browser/search'},
        size: {method: 'post', url: '/file-browser/size'},
        upload: {method: 'post', url: '/file-browser/upload'}
      },
      topPanelButtons: {
        refresh: {
          name: 'Refresh',
          icon: 'refresh-icon',
          callback: e => console.log(e)
        },
        tree: {
          name: 'Switch to the tree view',
          icon: 'tree-view-icon',
          callback: () => {
            this.view.active = 'tree';
          }
        },
        list: {
          name: 'Switch to the list view',
          icon: 'list-view-icon',
          callback: () => {
            this.view.active = 'list';
          }
        },
        commander: {
          name: 'Switch to the two panel view',
          icon: 'side-by-side-view-icon',
          callback: () => {
            this.view.active = 'commander';
          }
        },
        selectAll: {
          name: 'Select all files',
          icon: 'select-all-icon',
          callback: e => console.log(e)
        },
        unselectAll: {
          name: 'Unselect all files',
          icon: 'unselect-all-icon',
          callback: e => console.log(e)
        }
      },
      view: {
        active: 'commander'
      }
    };
  },
  methods: {
    /**
     * Count files or folders
     * @param files
     * @param {boolean} checkFolders
     * @returns {*|number}
     */
    countFiles(files, checkFolders = true) {
      return Array.isArray(files) ? files.filter(i => i.isDir === checkFolders).length : 0;
    },
    /**
     * Get file icon by file properties
     * @param file
     * @returns {string|string|string}
     */
    fileIcon(file) {
      if (file.isDir) {
        return 'folder-icon';
      } else {
        const icon = Object.keys(this.mimeTypes).find(icon => this.mimeTypes[icon].includes(file['mime-type']));
        return icon ? icon : 'file-regular';
      }
    },
    /**
     * Format bytes as human-readable text.
     * @param {int} size
     * @returns {string}
     */
    fileSize(size) {
      const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      const value = size > 0 ? Math.floor(Math.log(Math.abs(size)) / Math.log(1024)) : 0;
      const result = (size > 0 ? (size / Math.pow(1024, value)) : 0);
      return (units[value] === 'B' ? result : result.toFixed(1)) + ' ' + units[value];
    },
    /**
     * Convert Unix timestamp to date format j.M.Y H:i:s
     * @param {int} time
     * @returns {string}
     */
    formatDate(time) {
      const date = new Date(time * 1000);
      const res = {
        y: date.getFullYear(),
        m: date.toLocaleString('default', {month: 'short'}),
        d: this.padDate(date.getDate()),
        h: this.padDate(date.getHours()),
        i: this.padDate(date.getMinutes()),
        s: this.padDate(date.getSeconds())
      };

      return `${res.d}.${res.m}.${res.y} ${res.h}:${res.i}:${res.s}`;
    },
    /**
     * Get configuration
     * @returns {object}
     */
    getConfig() {
      return this.config;
    },
    /**
     * File upload event listener
     * @param e
     */
    onFileChanged(e) {
      this.$refs.fileUpload.classList.add('ready');
      const files = e.target.files;

      if (this.view.active === 'commander') {
        files.length && this.$refs.commanderView.fileUploadHandler(files);
      }
    },
    /**
     * @param val
     * @returns {string}
     */
    padDate(val) {
      return ('' + val).padStart(2, '0');
    },
    /**
     * XHR request
     * @param props
     * @returns {Promise<unknown>}
     */
    request(props) {
      if (!props.hasOwnProperty('url')) {
        throw new ReferenceError('The request requires url endpoint.');
      }
      // Check if method exists
      if (!props.hasOwnProperty("method")) {
        props.method = "get";
      }
      // Set default headers
      props.headers = Object.assign({
        accept: "application/json",
        "content-type": props.method === "patch" || props.method === "delete" ? "application/x-www-form-urlencoded" : "multipart/form-data"
      }, props.headers || {});

      return axios(props);
    },
    /**
     * File array sort function
     * @param a
     * @param b
     * @param {string} field
     * @returns {number}
     */
    sortFiles(a, b, field = 'name') {
      if (a[field] < b[field]) return -1;
      if (a[field] > b[field]) return 1;
      return 0;
    }
  },
  provide() {
    return {
      getConfig: this.getConfig,
      countFiles: this.countFiles,
      fileIcon: this.fileIcon,
      fileSize: this.fileSize,
      formatDate: this.formatDate,
      request: this.request,
      sortFiles: this.sortFiles
    };
  },
  beforeMount() {
    this.request(this.routes.config).then(response => 200 === response.status && (this.config = response.data));
  }
};
</script>