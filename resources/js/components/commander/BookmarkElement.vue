<template>
  <li :class="{active: active}" :title="title">
    <em v-if="locked">*</em><span>{{ name }}</span>
  </li>
</template>

<script>
export default {
  emits: ['switchBookmark'],
  props: {
    active: {
      type: Boolean,
      default: false
    },
    locked: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    }
  }
}
</script>