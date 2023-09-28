<template>
  <th :class="{active: getBookmark(panel)?.filters?.order?.by === type}" @click="order">
    <div>
      <span>{{ name }}</span>
      <i class="file-browser-icon arrow-up" :class="{rotate: getBookmark(panel)?.filters?.order?.dir}"></i>
    </div>
  </th>
</template>

<script>
export default {
  props: {
    name: {
      type: String,
      required: true
    },
    panel: {
      type: String,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  emits: ['setOrder'],
  inject: {
    getBookmark: 'getBookmark'
  },
  methods: {
    order() {
      this.$emit('setOrder', this.type)
    }
  }
}
</script>