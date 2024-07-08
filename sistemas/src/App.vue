<template>
  <router-view></router-view>
</template>

<script>
import router from './router';

export default {
  data() {
    return {
    };
  },
  methods: {
    created() {
      router.push('/Login');
    }
  }
};
</script>
