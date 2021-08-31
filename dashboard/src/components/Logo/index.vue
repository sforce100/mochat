<template>
  <img v-if="!sideCollapsed" style="width: 50px;height: 50px" :src="src"/>
</template>

<script>
import { mapState } from 'vuex'
export default {
  data () {
    return {
      src: require('@/assets/title.png')
    }
  },
  computed: {
    ...mapState({
      sideCollapsed: state => state.app.sideCollapsed
    })
  }
}
</script>

<style>

</style>
