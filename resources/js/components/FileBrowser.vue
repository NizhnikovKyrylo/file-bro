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
      <SideBySideComponent/>
    </template>
  </div>
</template>

<script>
import axios from "axios";
import {storage} from "../storage.js";
import PanelButton from "./PanelButton.vue";
import SideBySideComponent from "./SideBySide/SideBySideComponent.vue";

export default {
  components: {SideBySideComponent, PanelButton},
  data() {
    return {
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
      defaultView: 'sideBySide'
    };
  },
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

    // Init "side by side" view options
    if (null !== storage.get('side-by-side')) {
      storage.set('side-by-side', {active: 0, left: 0, right: 0})
    }

    this.request(Object.assign(this.routes.list, {data: {path: '/'}})).then(response => {
      // Init files storage
      storage.set('files', {left: response.data, right: response.data})
    });
  },
  methods: {
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
        "content-type": props.method === "patch" || props.method === "delete"
          ? "application/x-www-form-urlencoded"
          : "multipart/form-data"
      }, props.headers || {});

      return axios(props)
    }
  }
};
</script>