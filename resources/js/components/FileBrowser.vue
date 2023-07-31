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