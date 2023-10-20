<template>
  <ul class="context-menu" v-if="show" :style="{left: left + 'px'}" v-click-out-side="() => {show = false}">
    <li class="separator">
      <span>Open</span>
    </li>
    <li>
      <span>Move</span>
    </li>
    <li>
      <span>Delete</span>
    </li>
    <li class="separator">
      <span>Rename</span>
    </li>
    <li>
      <span>Cut</span>
    </li>
    <li>
      <span>Copy</span>
    </li>
    <li class="separator">
      <span>Paste</span>
    </li>
    <li>
      <span>Properties</span>
    </li>
  </ul>
</template>

<script>
import clickOutSide from "../mixins/click-outside.js";

export default {
  directives: {
    clickOutSide
  },
  data() {
    return {
      bookmark: 0,
      index: 0,
      left: 10,
      panel: 'left',
      show: false
    };
  }
}
</script>