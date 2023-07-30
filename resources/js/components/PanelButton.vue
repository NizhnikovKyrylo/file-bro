<template>
  <button :name="name" :class="{active: isActive}" type="button" :title="title">
    <i :class="`file-browser-icon ${icon}`"></i>
  </button>
</template>

<script>
export default {
  props: {
    icon: {
      type: String,
      required: true
    },
    isActive: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: null
    }
  }
}
</script>